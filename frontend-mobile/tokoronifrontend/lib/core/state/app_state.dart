// ============================================================
// lib/core/app_state.dart
//
// TANPA POLLING — pakai WebSocket untuk real-time update
// ============================================================

import 'dart:async';
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../services/auth_service.dart';
import '../config/api_config.dart';
import '../services/notifikasi_service.dart';

class AppState with WidgetsBindingObserver {
  AppState._();
  static final AppState instance = AppState._();

  // ── User data ─────────────────────────────────────────────────────────────
  final ValueNotifier<String> userName = ValueNotifier('');
  final ValueNotifier<String> userEmail = ValueNotifier('');
  final ValueNotifier<String> userRole = ValueNotifier('');
  final ValueNotifier<String?> userPhoto = ValueNotifier(null);

  // ── Notifikasi ────────────────────────────────────────────────────────────
  final ValueNotifier<List<NotifItem>> notifications = ValueNotifier([]);
  final ValueNotifier<int> unreadCount = ValueNotifier(0);
  final ValueNotifier<bool> notifLoading = ValueNotifier(false);

  // ── Dashboard refresh trigger ─────────────────────────────────────────────
  final ValueNotifier<int> dashboardRefreshTick = ValueNotifier(0);

  bool _initialized = false;
  StreamSubscription? _wsSub;

  // ════════════════════════════════════════════════════════════════════════
  // INIT
  // ════════════════════════════════════════════════════════════════════════
  Future<void> init() async {
    if (_initialized) return;

    // 1. Load dari cache dulu (cepat)
    await _loadFromCache();

    // 2. Cek login
    final loggedIn = await AuthService.isLoggedIn();
    if (!loggedIn) return;

    _initialized = true;
    WidgetsBinding.instance.addObserver(this);

    // 3. Fetch fresh dari API (sekali saja)
    await Future.wait([refreshProfile(), refreshNotifications()]);
  }

  // ════════════════════════════════════════════════════════════════════════
  // APP LIFECYCLE
  // ════════════════════════════════════════════════════════════════════════
  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.resumed) {
      _onAppResumed();
    }
  }

  Future<void> _onAppResumed() async {
    final loggedIn = await AuthService.isLoggedIn();
    if (!loggedIn) return;

    // Saat resume: refresh sekali
    await Future.wait([refreshProfile(), refreshNotifications()]);
    triggerDashboardRefresh();
  }

  // ════════════════════════════════════════════════════════════════════════
  // PROFILE — fetch dari /api/v1/auth/profile
  // ════════════════════════════════════════════════════════════════════════
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
          final name = data['name']?.toString() ?? '';
          final email = data['email']?.toString() ?? '';
          final role = data['role']?.toString() ?? '';
          final photo = data['avatar']?.toString();

          userName.value = name;
          userEmail.value = email;
          userRole.value = role;
          userPhoto.value = (photo != null && photo.isNotEmpty) ? photo : null;

          // Simpan ke cache
          final prefs = await SharedPreferences.getInstance();
          await prefs.setString('user_name', name);
          await prefs.setString('user_email', email);
          await prefs.setString('user_role', role);
          await prefs.setString('user_photo', photo ?? '');

          // Simpan user_id untuk WebSocket channel subscription
          final userId = data['id']?.toString() ?? '';
          if (userId.isNotEmpty) {
            await prefs.setString('user_id', userId);
          }
        }
      }
    } catch (_) {
      // Gagal network — pakai cache
    }
  }

  // ════════════════════════════════════════════════════════════════════════
  // NOTIFIKASI — fetch sekali, update via WebSocket
  // ════════════════════════════════════════════════════════════════════════
  Future<void> refreshNotifications() async {
    if (notifLoading.value) return;
    notifLoading.value = true;
    try {
      final list = await NotifikasiService.getAll();
      notifications.value = list;
      unreadCount.value = list.where((n) => !n.sudahDibaca).length;
    } catch (_) {
    } finally {
      notifLoading.value = false;
    }
  }

  Future<void> markNotifRead(String id) async {
    await NotifikasiService.markAsRead(id);
    final updated = notifications.value.map((n) {
      if (n.id == id) n.sudahDibaca = true;
      return n;
    }).toList();
    notifications.value = List.from(updated);
    unreadCount.value = updated.where((n) => !n.sudahDibaca).length;
  }

  Future<void> markAllRead() async {
    await NotifikasiService.markAllAsRead();
    final updated = notifications.value.map((n) {
      n.sudahDibaca = true;
      return n;
    }).toList();
    notifications.value = List.from(updated);
    unreadCount.value = 0;
  }

  Future<void> deleteNotif(String id) async {
    await NotifikasiService.delete(id);
    notifications.value = notifications.value.where((n) => n.id != id).toList();
    unreadCount.value = notifications.value.where((n) => !n.sudahDibaca).length;
  }

  Future<void> clearAllNotif() async {
    await NotifikasiService.clearAll();
    notifications.value = [];
    unreadCount.value = 0;
  }

  // ════════════════════════════════════════════════════════════════════════
  // DASHBOARD TRIGGER
  // ════════════════════════════════════════════════════════════════════════
  void triggerDashboardRefresh() => dashboardRefreshTick.value++;

  // ════════════════════════════════════════════════════════════════════════
  // LOGOUT
  // ════════════════════════════════════════════════════════════════════════
  Future<void> logout() async {
    _wsSub?.cancel();
    WidgetsBinding.instance.removeObserver(this);
    await AuthService.logout();
    userName.value = '';
    userEmail.value = '';
    userRole.value = '';
    userPhoto.value = null;
    notifications.value = [];
    unreadCount.value = 0;
    _initialized = false;
  }

  // ════════════════════════════════════════════════════════════════════════
  // CACHE
  // ════════════════════════════════════════════════════════════════════════
  Future<void> _loadFromCache() async {
    userName.value = await AuthService.getUserName();
    userEmail.value = await AuthService.getUserEmail();
    userRole.value = await AuthService.getUserRole();
    try {
      final photo = await AuthService.getUserPhoto();
      userPhoto.value = (photo != null && photo.isNotEmpty) ? photo : null;
    } catch (_) {}
  }

  void dispose() {
    _wsSub?.cancel();
    WidgetsBinding.instance.removeObserver(this);
    userName.dispose();
    userEmail.dispose();
    userRole.dispose();
    userPhoto.dispose();
    notifications.dispose();
    unreadCount.dispose();
    notifLoading.dispose();
    dashboardRefreshTick.dispose();
  }
}
