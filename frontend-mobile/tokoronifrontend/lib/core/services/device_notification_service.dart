import 'package:flutter/material.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';

import 'notifikasi_service.dart';

class DeviceNotificationService {
  static final FlutterLocalNotificationsPlugin _plugin =
      FlutterLocalNotificationsPlugin();
  // Android small notification icon is a drawable resource name (no @prefix).
  static const String _androidNotificationIcon = 'icon_notification';
  static const Color _androidNotificationColor = Color(0xFF4169E1);

  static const AndroidNotificationChannel _channel = AndroidNotificationChannel(
    'tokoroni_notifications',
    'Toko Roni Notifications',
    description: 'Notifikasi aktivitas aplikasi Toko Roni',
    importance: Importance.max,
    playSound: true,
  );

  static bool _initialized = false;
  static final Map<String, DateTime> _shownAtByNotifId =
      <String, DateTime>{};
  static const Duration _dedupeWindow = Duration(minutes: 2);

  static Future<void> init() async {
    if (_initialized) return;

    const androidSettings = AndroidInitializationSettings(
      _androidNotificationIcon,
    );
    const iosSettings = DarwinInitializationSettings();
    const initSettings = InitializationSettings(
      android: androidSettings,
      iOS: iosSettings,
    );

    await _plugin.initialize(initSettings);

    final androidPlugin = _plugin
        .resolvePlatformSpecificImplementation<
          AndroidFlutterLocalNotificationsPlugin
        >();
    if (androidPlugin != null) {
      await androidPlugin.createNotificationChannel(_channel);
      await androidPlugin.requestNotificationsPermission();
    }

    final iosPlugin = _plugin
        .resolvePlatformSpecificImplementation<
          IOSFlutterLocalNotificationsPlugin
        >();
    await iosPlugin?.requestPermissions(alert: true, badge: true, sound: true);

    _initialized = true;
  }

  static Future<void> showFromNotifItem(NotifItem item) async {
    if (!_initialized) return;

    final notifId = item.id.trim();
    if (notifId.isNotEmpty && _isDuplicateWithinWindow(notifId)) {
      return;
    }

    final androidDetails = AndroidNotificationDetails(
      _channel.id,
      _channel.name,
      channelDescription: _channel.description,
      importance: Importance.max,
      priority: Priority.high,
      icon: _androidNotificationIcon,
      color: _androidNotificationColor,
      colorized: true,
      ticker: 'Toko Roni',
    );

    const iosDetails = DarwinNotificationDetails(
      presentAlert: true,
      presentBadge: true,
      presentSound: true,
    );

    final details = NotificationDetails(
      android: androidDetails,
      iOS: iosDetails,
    );

    final id = item.id.hashCode & 0x7fffffff;
    await _plugin.show(id, item.judul, item.pesan, details);
  }

  static bool _isDuplicateWithinWindow(String notifId) {
    final now = DateTime.now();

    _shownAtByNotifId.removeWhere(
      (_, shownAt) => now.difference(shownAt) > _dedupeWindow,
    );

    final lastShownAt = _shownAtByNotifId[notifId];
    if (lastShownAt != null && now.difference(lastShownAt) <= _dedupeWindow) {
      return true;
    }

    _shownAtByNotifId[notifId] = now;
    return false;
  }
}
