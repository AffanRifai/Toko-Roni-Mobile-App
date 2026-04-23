import '../state/app_state.dart';

class NotificationRefreshHelper {
  static Future<void> refreshSafely() async {
    try {
      await AppState.instance.refreshNotifications();
    } catch (_) {}
  }
}
