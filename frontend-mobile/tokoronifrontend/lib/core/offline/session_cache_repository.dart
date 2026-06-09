import 'package:sqflite/sqflite.dart';

import 'local_database.dart';
import 'offline_utils.dart';

class SessionCacheRepository {
  SessionCacheRepository._();
  static final SessionCacheRepository instance = SessionCacheRepository._();

  Future<void> upsert(String key, String value) async {
    final db = await LocalDatabase.instance.db;
    final now = nowIsoUtc();
    await db.insert(LocalDatabase.tableAppSession, {
      'key': key,
      'value': value,
      'updated_at': now,
    }, conflictAlgorithm: ConflictAlgorithm.replace);
  }

  Future<String?> get(String key) async {
    final db = await LocalDatabase.instance.db;
    final rows = await db.query(
      LocalDatabase.tableAppSession,
      where: 'key = ?',
      whereArgs: [key],
      limit: 1,
    );
    if (rows.isEmpty) return null;
    return rows.first['value']?.toString();
  }
}
