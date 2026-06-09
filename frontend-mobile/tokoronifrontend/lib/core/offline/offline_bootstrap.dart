import 'package:shared_preferences/shared_preferences.dart';

import 'local_database.dart';
import 'session_cache_repository.dart';
import 'sync_manager.dart';

class OfflineBootstrap {
  OfflineBootstrap._();
  static final OfflineBootstrap instance = OfflineBootstrap._();

  bool _initialized = false;

  Future<void> init() async {
    if (_initialized) return;
    _initialized = true;

    await LocalDatabase.instance.db;
    await _mirrorSessionFromPrefs();
    await SyncManager.instance.start();
  }

  Future<void> _mirrorSessionFromPrefs() async {
    final prefs = await SharedPreferences.getInstance();
    final repo = SessionCacheRepository.instance;
    final keys = <String>[
      'auth_token',
      'user_id',
      'user_name',
      'user_email',
      'user_role',
      'user_photo',
      'user_phone',
      'user_address',
      'user_joined_at',
    ];
    for (final key in keys) {
      final value = prefs.getString(key) ?? '';
      await repo.upsert(key, value);
    }
  }
}
