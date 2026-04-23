import 'dart:async';
import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

import '../config/api_config.dart';
import '../services/auth_service.dart';
import '../services/device_notification_service.dart';
import '../services/notifikasi_service.dart';
import '../services/realtime_notification_service.dart';

class AppState with WidgetsBindingObserver {
  AppState._();
  static final AppState instance = AppState._();

  final ValueNotifier<String> userName = ValueNotifier('');
  final ValueNotifier<String> userEmail = ValueNotifier('');
  final ValueNotifier<String> userRole = ValueNotifier('');
  final ValueNotifier<String> userPhone = ValueNotifier('');
  final ValueNotifier<String> userAddress = ValueNotifier('');
  final ValueNotifier<String> userJoinedAt = ValueNotifier('');
  final ValueNotifier<String?> userPhoto = ValueNotifier(null);

  final ValueNotifier<List<NotifItem>> notifications = ValueNotifier([]);
  final ValueNotifier<int> unreadCount = ValueNotifier(0);
  final ValueNotifier<bool> notifLoading = ValueNotifier(false);

  final ValueNotifier<int> dashboardRefreshTick = ValueNotifier(0);

  bool _initialized = false;
  Set<String> _knownNotifIds = <String>{};
  DateTime? _lastNotifFetchedAt;
  Future<void>? _notifRefreshInFlight;
  static const Duration _minNotifRefreshInterval = Duration(seconds: 8);

  Future<void> init() async {
    if (_initialized) return;

    await _loadFromCache();
    final loggedIn = await AuthService.isLoggedIn();
    if (!loggedIn) return;

    _initialized = true;
    WidgetsBinding.instance.addObserver(this);

    await DeviceNotificationService.init();
    await Future.wait([refreshProfile(), refreshNotifications(force: true)]);
    await _connectRealtimeNotifications();
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.resumed) {
      _onAppResumed();
      return;
    }

    if (state == AppLifecycleState.inactive ||
        state == AppLifecycleState.paused ||
        state == AppLifecycleState.hidden ||
        state == AppLifecycleState.detached) {
      RealtimeNotificationService.instance.disconnect();
    }
  }

  Future<void> _onAppResumed() async {
    final loggedIn = await AuthService.isLoggedIn();
    if (!loggedIn) return;

    await Future.wait([
      refreshProfile(),
      refreshNotifications(force: true),
      _connectRealtimeNotifications(),
    ]);
    triggerDashboardRefresh();
  }

  Future<void> refreshProfile() async {
    try {
      final res = await http
          .get(
            Uri.parse(ApiConfig.profile),
            headers: await AuthService.authHeaders(),
          )
          .timeout(const Duration(seconds: 10));

      if (res.statusCode == 200) {
        final json = jsonDecode(res.body) as Map<String, dynamic>;
        if (json['success'] == true) {
          final data = (json['data'] as Map<String, dynamic>?) ?? {};
          final nestedUser = _asMap(data['user']);
          final name = _firstNonEmpty([
            data['name'],
            nestedUser['name'],
            data['email'],
            nestedUser['email'],
          ]);
          final email = _firstNonEmpty([data['email'], nestedUser['email']]);
          final role = _firstNonEmpty([data['role'], nestedUser['role']]);
          final phone = _firstNonEmpty([
            data['phone'],
            data['phone_number'],
            data['no_telp'],
            data['telepon'],
            nestedUser['phone'],
            nestedUser['phone_number'],
            nestedUser['no_telp'],
            nestedUser['telepon'],
          ]);
          final address = _firstNonEmpty([
            data['address'],
            data['alamat'],
            nestedUser['address'],
            nestedUser['alamat'],
          ]);
          final joinedAt = _firstNonEmpty([
            data['joined_at'],
            data['created_at'],
            data['registered_at'],
            nestedUser['joined_at'],
            nestedUser['created_at'],
            nestedUser['registered_at'],
          ]);
          final photo = _firstNonEmpty([
            data['avatar'],
            nestedUser['avatar'],
            data['photo'],
            nestedUser['photo'],
          ]);

          userName.value = name;
          userEmail.value = email;
          userRole.value = role;
          userPhone.value = phone;
          userAddress.value = address;
          userJoinedAt.value = joinedAt;
          userPhoto.value = photo.isNotEmpty ? photo : null;

          final prefs = await SharedPreferences.getInstance();
          await prefs.setString('user_name', name);
          await prefs.setString('user_email', email);
          await prefs.setString('user_role', role);
          await prefs.setString('user_phone', phone);
          await prefs.setString('user_address', address);
          await prefs.setString('user_joined_at', joinedAt);
          await prefs.setString('user_photo', photo);
        }
      }
    } catch (_) {}
  }

  Future<void> refreshNotifications({bool force = false}) async {
    final inFlight = _notifRefreshInFlight;
    if (inFlight != null) return inFlight;

    final now = DateTime.now();
    final lastFetchedAt = _lastNotifFetchedAt;
    final tooSoon =
        lastFetchedAt != null &&
        now.difference(lastFetchedAt) < _minNotifRefreshInterval;
    if (!force && tooSoon) return;

    final completer = Completer<void>();
    _notifRefreshInFlight = completer.future;

    notifLoading.value = true;
    try {
      final list = await NotifikasiService.getAll(perPage: 100);
      notifications.value = list;
      unreadCount.value = list.where((n) => !n.sudahDibaca).length;
      _knownNotifIds = list.map((n) => n.id).toSet();
      _lastNotifFetchedAt = DateTime.now();
    } catch (_) {
    } finally {
      notifLoading.value = false;
      _notifRefreshInFlight = null;
      if (!completer.isCompleted) completer.complete();
    }
  }

  Future<void> markNotifRead(String id) async {
    final ok = await NotifikasiService.markAsRead(id);
    if (!ok) {
      await refreshNotifications(force: true);
      return;
    }
    final updated = notifications.value.map((n) {
      if (n.id == id) n.sudahDibaca = true;
      return n;
    }).toList();
    notifications.value = List.from(updated);
    unreadCount.value = updated.where((n) => !n.sudahDibaca).length;
  }

  Future<void> markAllRead() async {
    final ok = await NotifikasiService.markAllAsRead();
    if (!ok) {
      await refreshNotifications(force: true);
      return;
    }
    final updated = notifications.value.map((n) {
      n.sudahDibaca = true;
      return n;
    }).toList();
    notifications.value = List.from(updated);
    unreadCount.value = 0;
  }

  Future<void> deleteNotif(String id) async {
    final ok = await NotifikasiService.delete(id);
    if (!ok) {
      await refreshNotifications(force: true);
      return;
    }
    notifications.value = notifications.value.where((n) => n.id != id).toList();
    unreadCount.value = notifications.value.where((n) => !n.sudahDibaca).length;
    _knownNotifIds.remove(id);
  }

  Future<void> clearAllNotif() async {
    final ok = await NotifikasiService.clearAll();
    if (!ok) {
      await refreshNotifications(force: true);
      return;
    }
    notifications.value = [];
    unreadCount.value = 0;
    _knownNotifIds.clear();
  }

  Future<void> _connectRealtimeNotifications() async {
    final userId = await AuthService.getUserId();
    if (userId.trim().isEmpty) return;

    await RealtimeNotificationService.instance.connect(
      userId: userId,
      onNotification: _onRealtimeNotification,
    );
  }

  Future<void> _onRealtimeNotification(Map<String, dynamic> raw) async {
    final item = NotifItem.fromJson(raw);
    if (item.id.trim().isEmpty || _knownNotifIds.contains(item.id)) {
      return;
    }

    _knownNotifIds.add(item.id);
    notifications.value = [item, ...notifications.value];
    unreadCount.value = notifications.value.where((n) => !n.sudahDibaca).length;
    await DeviceNotificationService.showFromNotifItem(item);
  }

  void triggerDashboardRefresh() => dashboardRefreshTick.value++;

  Future<void> logout() async {
    await RealtimeNotificationService.instance.disconnect();
    WidgetsBinding.instance.removeObserver(this);

    await AuthService.logout();
    userName.value = '';
    userEmail.value = '';
    userRole.value = '';
    userPhone.value = '';
    userAddress.value = '';
    userJoinedAt.value = '';
    userPhoto.value = null;
    notifications.value = [];
    unreadCount.value = 0;
    _knownNotifIds.clear();
    _lastNotifFetchedAt = null;
    _initialized = false;
  }

  Future<void> _loadFromCache() async {
    final prefs = await SharedPreferences.getInstance();
    userName.value = await AuthService.getUserName();
    userEmail.value = await AuthService.getUserEmail();
    userRole.value = await AuthService.getUserRole();
    userPhone.value = prefs.getString('user_phone') ?? '';
    userAddress.value = prefs.getString('user_address') ?? '';
    userJoinedAt.value = prefs.getString('user_joined_at') ?? '';
    try {
      final photo = await AuthService.getUserPhoto();
      userPhoto.value = (photo != null && photo.isNotEmpty) ? photo : null;
    } catch (_) {}
  }

  void dispose() {
    RealtimeNotificationService.instance.disconnect();
    WidgetsBinding.instance.removeObserver(this);
    userName.dispose();
    userEmail.dispose();
    userRole.dispose();
    userPhone.dispose();
    userAddress.dispose();
    userJoinedAt.dispose();
    userPhoto.dispose();
    notifications.dispose();
    unreadCount.dispose();
    notifLoading.dispose();
    dashboardRefreshTick.dispose();
  }

  Map<String, dynamic> _asMap(dynamic value) {
    if (value is Map<String, dynamic>) return value;
    if (value is Map) {
      return value.map((key, val) => MapEntry(key.toString(), val));
    }
    return <String, dynamic>{};
  }

  String _firstNonEmpty(List<dynamic> values) {
    for (final value in values) {
      final text = (value ?? '').toString().trim();
      if (text.isNotEmpty && text.toLowerCase() != 'null') return text;
    }
    return '';
  }
}
