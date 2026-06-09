import 'package:flutter/material.dart';
import 'package:sqflite/sqflite.dart';

import '../services/notifikasi_service.dart';
import 'local_database.dart';
import 'offline_utils.dart';
import 'sync_types.dart';

class NotificationLocalRepository {
  NotificationLocalRepository._();
  static final NotificationLocalRepository instance =
      NotificationLocalRepository._();

  Future<void> cacheServerNotifications(List<NotifItem> items) async {
    final db = await LocalDatabase.instance.db;
    await db.transaction((txn) async {
      for (final item in items) {
        final dedupe = _dedupeKey(
          title: item.judul,
          message: item.pesan,
          type: item.tipe,
        );
        await txn.insert(
          LocalDatabase.tableNotifications,
          {
            'local_id': 'srv-${item.id}',
            'server_id': item.id,
            'dedupe_key': dedupe,
            'source': 'server',
            'title': item.judul,
            'message': item.pesan,
            'type': item.tipe,
            'priority': item.priority,
            'is_important': item.isImportant ? 1 : 0,
            'is_read': item.sudahDibaca ? 1 : 0,
            'sync_status': SyncStatus.synced.value,
            'payload_json': null,
            'created_at': nowIsoUtc(),
            'updated_at': nowIsoUtc(),
          },
          conflictAlgorithm: ConflictAlgorithm.replace,
        );
      }
    });
  }

  Future<NotifItem?> upsertServerNotificationFromRaw(
    Map<String, dynamic> raw,
  ) async {
    final item = NotifItem.fromJson(raw);
    if (item.id.trim().isEmpty) return null;
    final db = await LocalDatabase.instance.db;
    final dedupe = _dedupeKey(
      title: item.judul,
      message: item.pesan,
      type: item.tipe,
    );
    await db.insert(
      LocalDatabase.tableNotifications,
      {
        'local_id': 'srv-${item.id}',
        'server_id': item.id,
        'dedupe_key': dedupe,
        'source': 'server',
        'title': item.judul,
        'message': item.pesan,
        'type': item.tipe,
        'priority': item.priority,
        'is_important': item.isImportant ? 1 : 0,
        'is_read': item.sudahDibaca ? 1 : 0,
        'sync_status': SyncStatus.synced.value,
        'payload_json': null,
        'created_at': nowIsoUtc(),
        'updated_at': nowIsoUtc(),
      },
      conflictAlgorithm: ConflictAlgorithm.replace,
    );
    return item;
  }

  Future<NotifItem> addLocalActionNotification({
    required String title,
    required String message,
    required String type,
    String priority = 'normal',
    bool important = false,
    bool enqueueSync = false,
  }) async {
    final db = await LocalDatabase.instance.db;
    final localId = 'loc-${DateTime.now().microsecondsSinceEpoch}';
    final dedupe = _dedupeKey(title: title, message: message, type: type);
    final now = nowIsoUtc();

    await db.insert(LocalDatabase.tableNotifications, {
      'local_id': localId,
      'server_id': null,
      'dedupe_key': dedupe,
      'source': 'local',
      'title': title,
      'message': message,
      'type': type,
      'priority': priority,
      'is_important': important ? 1 : 0,
      'is_read': 0,
      'sync_status': enqueueSync
          ? SyncStatus.pendingCreate.value
          : SyncStatus.synced.value,
      'payload_json': null,
      'created_at': now,
      'updated_at': now,
    });

    return NotifItem(
      id: localId,
      icon: _iconFromType(type),
      iconColor: _colorFromType(type),
      judul: title,
      pesan: message,
      waktu: 'Baru saja',
      tipe: type,
      priority: priority,
      isImportant: important,
      sudahDibaca: false,
    );
  }

  Future<bool> exists(String id) async {
    final db = await LocalDatabase.instance.db;
    final rows = await db.query(
      LocalDatabase.tableNotifications,
      columns: const ['local_id'],
      where: 'server_id = ? OR local_id = ?',
      whereArgs: [id, id],
      limit: 1,
    );
    return rows.isNotEmpty;
  }

  Future<List<NotifItem>> getNotifications() async {
    final db = await LocalDatabase.instance.db;
    final rows = await db.query(
      LocalDatabase.tableNotifications,
      orderBy: 'created_at DESC',
      limit: 200,
    );
    final seenDedupe = <String>{};
    final items = <NotifItem>[];

    for (final row in rows) {
      final dedupe = row['dedupe_key']?.toString() ?? '';
      if (dedupe.isNotEmpty && seenDedupe.contains(dedupe)) continue;
      if (dedupe.isNotEmpty) seenDedupe.add(dedupe);

      final type = row['type']?.toString() ?? 'default';
      items.add(
        NotifItem(
          id: row['server_id']?.toString() ?? row['local_id']?.toString() ?? '',
          icon: _iconFromType(type),
          iconColor: _colorFromType(type),
          judul: row['title']?.toString() ?? 'Notifikasi',
          pesan: row['message']?.toString() ?? '-',
          waktu: 'Baru saja',
          tipe: type,
          priority: row['priority']?.toString() ?? 'normal',
          isImportant: ((row['is_important'] as num?)?.toInt() ?? 0) == 1,
          sudahDibaca: ((row['is_read'] as num?)?.toInt() ?? 0) == 1,
        ),
      );
    }
    return items;
  }

  Future<void> markRead(String id) async {
    final db = await LocalDatabase.instance.db;
    await db.update(
      LocalDatabase.tableNotifications,
      {
        'is_read': 1,
        'updated_at': nowIsoUtc(),
      },
      where: 'server_id = ? OR local_id = ?',
      whereArgs: [id, id],
    );
  }

  Future<void> markAllRead() async {
    final db = await LocalDatabase.instance.db;
    await db.update(
      LocalDatabase.tableNotifications,
      {
        'is_read': 1,
        'updated_at': nowIsoUtc(),
      },
    );
  }

  Future<void> deleteOne(String id) async {
    final db = await LocalDatabase.instance.db;
    await db.delete(
      LocalDatabase.tableNotifications,
      where: 'server_id = ? OR local_id = ?',
      whereArgs: [id, id],
    );
  }

  Future<void> clearAll() async {
    final db = await LocalDatabase.instance.db;
    await db.delete(LocalDatabase.tableNotifications);
  }

  Future<void> markSynced(String localId) async {
    final db = await LocalDatabase.instance.db;
    await db.update(
      LocalDatabase.tableNotifications,
      {
        'sync_status': SyncStatus.synced.value,
        'updated_at': nowIsoUtc(),
      },
      where: 'local_id = ?',
      whereArgs: [localId],
    );
  }

  String _dedupeKey({
    required String title,
    required String message,
    required String type,
  }) {
    return '${type.trim().toLowerCase()}|${title.trim().toLowerCase()}|${message.trim().toLowerCase()}';
  }

  IconData _iconFromType(String type) {
    switch (type) {
      case 'product':
        return Icons.inventory_2_rounded;
      case 'category':
        return Icons.label_rounded;
      case 'transaction':
        return Icons.receipt_long_rounded;
      case 'delivery':
        return Icons.local_shipping_rounded;
      case 'member':
        return Icons.people_alt_rounded;
      case 'vehicle':
        return Icons.directions_car_rounded;
      case 'user':
        return Icons.person_rounded;
      default:
        return Icons.notifications_rounded;
    }
  }

  Color _colorFromType(String type) {
    switch (type) {
      case 'product':
        return const Color(0xFF48BB78);
      case 'category':
        return const Color(0xFFD69E2E);
      case 'transaction':
        return const Color(0xFF4169E1);
      case 'delivery':
        return const Color(0xFF3182CE);
      case 'member':
        return const Color(0xFF38B2AC);
      case 'vehicle':
        return const Color(0xFF2B6CB0);
      case 'user':
        return const Color(0xFF6B5CE7);
      default:
        return const Color(0xFF718096);
    }
  }
}
