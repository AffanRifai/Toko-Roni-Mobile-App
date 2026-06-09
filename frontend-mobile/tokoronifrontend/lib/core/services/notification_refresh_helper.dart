import 'dart:async';

import '../offline/notification_local_repository.dart';
import '../offline/sync_manager.dart';
import '../offline/sync_queue_repository.dart';
import '../offline/sync_types.dart';
import '../state/app_state.dart';
import 'device_notification_service.dart';

class NotificationRefreshHelper {
  static Future<void> refreshSafely() async {
    try {
      await AppState.instance.refreshNotifications();
    } catch (_) {}
  }

  static Future<void> notifyLocalAction({
    required String title,
    required String message,
    required String type,
    String priority = 'normal',
    bool important = false,
    bool enqueueSync = false,
  }) async {
    try {
      final notif = await NotificationLocalRepository.instance
          .addLocalActionNotification(
            title: title,
            message: message,
            type: type,
            priority: priority,
            important: important,
            enqueueSync: enqueueSync,
          );

      if (enqueueSync) {
        await SyncQueueRepository.instance.enqueue(
          entityType: LocalEntityType.notification,
          entityLocalId: notif.id,
          operation: SyncOperation.create,
          payload: {
            'action': 'create_local',
            'title': title,
            'message': message,
            'type': type,
            'priority': priority,
            'important': important,
          },
        );
        unawaited(SyncManager.instance.triggerSync());
      }

      await DeviceNotificationService.showFromNotifItem(notif);
      await AppState.instance.refreshNotifications(force: true);
    } catch (_) {}
  }
}
