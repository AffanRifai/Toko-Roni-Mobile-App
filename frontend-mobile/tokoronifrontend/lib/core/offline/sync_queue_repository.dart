import 'package:sqflite/sqflite.dart';

import 'local_database.dart';
import 'offline_utils.dart';
import 'sync_types.dart';

class SyncQueueItem {
  final int id;
  final LocalEntityType entityType;
  final String entityLocalId;
  final SyncOperation operation;
  final Map<String, dynamic> payload;
  final int retryCount;
  final String? nextRetryAt;
  final String? lastError;
  final String createdAt;

  const SyncQueueItem({
    required this.id,
    required this.entityType,
    required this.entityLocalId,
    required this.operation,
    required this.payload,
    required this.retryCount,
    required this.nextRetryAt,
    required this.lastError,
    required this.createdAt,
  });

  factory SyncQueueItem.fromMap(Map<String, dynamic> row) {
    return SyncQueueItem(
      id: (row['id'] as num).toInt(),
      entityType: LocalEntityType.fromValue(row['entity_type']?.toString()),
      entityLocalId: row['entity_local_id']?.toString() ?? '',
      operation: SyncOperation.fromValue(row['operation']?.toString()),
      payload: decodeJsonObject(row['payload_json']?.toString()),
      retryCount: (row['retry_count'] as num?)?.toInt() ?? 0,
      nextRetryAt: row['next_retry_at']?.toString(),
      lastError: row['last_error']?.toString(),
      createdAt: row['created_at']?.toString() ?? nowIsoUtc(),
    );
  }
}

class SyncQueueRepository {
  SyncQueueRepository._();
  static final SyncQueueRepository instance = SyncQueueRepository._();

  Future<void> enqueue({
    required LocalEntityType entityType,
    required String entityLocalId,
    required SyncOperation operation,
    required Map<String, dynamic> payload,
  }) async {
    final db = await LocalDatabase.instance.db;

    final existing = await db.query(
      LocalDatabase.tableSyncQueue,
      where: 'entity_type = ? AND entity_local_id = ?',
      whereArgs: [entityType.value, entityLocalId],
      orderBy: 'id DESC',
      limit: 1,
    );

    final now = nowIsoUtc();
    if (existing.isEmpty) {
      await db.insert(LocalDatabase.tableSyncQueue, {
        'entity_type': entityType.value,
        'entity_local_id': entityLocalId,
        'operation': operation.value,
        'payload_json': encodeJson(payload),
        'retry_count': 0,
        'next_retry_at': null,
        'last_error': null,
        'created_at': now,
        'updated_at': now,
      });
      return;
    }

    final row = existing.first;
    final existingId = (row['id'] as num).toInt();
    final existingOp = SyncOperation.fromValue(row['operation']?.toString());

    final mergedOp = _mergeOperation(existingOp, operation);
    if (mergedOp == null) {
      await db.delete(
        LocalDatabase.tableSyncQueue,
        where: 'id = ?',
        whereArgs: [existingId],
      );
      return;
    }

    await db.update(
      LocalDatabase.tableSyncQueue,
      {
        'operation': mergedOp.value,
        'payload_json': encodeJson(payload),
        'retry_count': 0,
        'next_retry_at': null,
        'last_error': null,
        'updated_at': now,
      },
      where: 'id = ?',
      whereArgs: [existingId],
    );
  }

  SyncOperation? _mergeOperation(
    SyncOperation current,
    SyncOperation incoming,
  ) {
    if (current == SyncOperation.create && incoming == SyncOperation.delete) {
      return null;
    }
    if (current == SyncOperation.create && incoming == SyncOperation.update) {
      return SyncOperation.create;
    }
    if (incoming == SyncOperation.delete) return SyncOperation.delete;
    return incoming;
  }

  Future<List<SyncQueueItem>> dueItems({int limit = 50}) async {
    final db = await LocalDatabase.instance.db;
    final now = nowIsoUtc();
    final rows = await db.query(
      LocalDatabase.tableSyncQueue,
      where: 'next_retry_at IS NULL OR next_retry_at <= ?',
      whereArgs: [now],
      orderBy: 'created_at ASC',
      limit: limit,
    );
    return rows.map(SyncQueueItem.fromMap).toList(growable: false);
  }

  Future<void> markSuccess(int queueId) async {
    final db = await LocalDatabase.instance.db;
    await db.delete(
      LocalDatabase.tableSyncQueue,
      where: 'id = ?',
      whereArgs: [queueId],
    );
  }

  Future<void> markFailure(
    int queueId,
    String error, {
    int retryCount = 0,
  }) async {
    final db = await LocalDatabase.instance.db;
    final now = DateTime.now().toUtc();
    final delaySeconds = _backoffSeconds(retryCount + 1);
    final nextRetryAt = now
        .add(Duration(seconds: delaySeconds))
        .toIso8601String();
    await db.update(
      LocalDatabase.tableSyncQueue,
      {
        'retry_count': retryCount + 1,
        'next_retry_at': nextRetryAt,
        'last_error': error,
        'updated_at': nowIsoUtc(),
      },
      where: 'id = ?',
      whereArgs: [queueId],
    );
  }

  int _backoffSeconds(int retryCount) {
    final pow = retryCount < 1 ? 1 : retryCount;
    final seconds = (1 << (pow > 7 ? 7 : pow));
    return seconds * 5;
  }

  Future<bool> hasPendingQueue() async {
    final db = await LocalDatabase.instance.db;
    final rows = await db.rawQuery(
      'SELECT COUNT(1) AS total FROM ${LocalDatabase.tableSyncQueue}',
    );
    final total = (rows.first['total'] as num?)?.toInt() ?? 0;
    return total > 0;
  }

  Future<void> runInTransaction(
    Future<void> Function(Transaction txn) task,
  ) async {
    final db = await LocalDatabase.instance.db;
    await db.transaction(task);
  }
}
