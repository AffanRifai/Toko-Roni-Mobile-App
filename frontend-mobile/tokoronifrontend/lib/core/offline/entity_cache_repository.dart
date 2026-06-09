import 'dart:convert';

import 'package:sqflite/sqflite.dart';

import 'local_database.dart';
import 'offline_utils.dart';

class EntityCacheRepository {
  EntityCacheRepository._();
  static final EntityCacheRepository instance = EntityCacheRepository._();

  Future<void> saveMap(String key, Map<String, dynamic> payload) async {
    final db = await LocalDatabase.instance.db;
    await db.insert(
      LocalDatabase.tableEntityCache,
      {
        'cache_key': key,
        'payload_json': jsonEncode(payload),
        'updated_at': nowIsoUtc(),
      },
      conflictAlgorithm: ConflictAlgorithm.replace,
    );
  }

  Future<void> saveList(String key, List<Map<String, dynamic>> payload) async {
    final db = await LocalDatabase.instance.db;
    await db.insert(
      LocalDatabase.tableEntityCache,
      {
        'cache_key': key,
        'payload_json': jsonEncode(payload),
        'updated_at': nowIsoUtc(),
      },
      conflictAlgorithm: ConflictAlgorithm.replace,
    );
  }

  Future<Map<String, dynamic>> getMap(String key) async {
    final db = await LocalDatabase.instance.db;
    final rows = await db.query(
      LocalDatabase.tableEntityCache,
      columns: ['payload_json'],
      where: 'cache_key = ?',
      whereArgs: [key],
      limit: 1,
    );
    if (rows.isEmpty) return {};
    final raw = rows.first['payload_json']?.toString();
    return decodeJsonObject(raw);
  }

  Future<List<Map<String, dynamic>>> getList(String key) async {
    final db = await LocalDatabase.instance.db;
    final rows = await db.query(
      LocalDatabase.tableEntityCache,
      columns: ['payload_json'],
      where: 'cache_key = ?',
      whereArgs: [key],
      limit: 1,
    );
    if (rows.isEmpty) return const [];
    final raw = rows.first['payload_json']?.toString();
    final list = decodeJsonList(raw);
    return list
        .whereType<Map>()
        .map((e) => e.map((k, v) => MapEntry(k.toString(), v)))
        .toList(growable: false);
  }
}

