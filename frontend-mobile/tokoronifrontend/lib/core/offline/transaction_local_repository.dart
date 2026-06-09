import 'dart:convert';

import '../../models/transaction_api_model.dart';
import 'local_database.dart';
import 'offline_utils.dart';
import 'sync_types.dart';

class TransactionLocalRepository {
  TransactionLocalRepository._();
  static final TransactionLocalRepository instance =
      TransactionLocalRepository._();

  Future<void> cacheRemoteTransactions(
    List<Map<String, dynamic>> rawTransactions,
  ) async {
    final db = await LocalDatabase.instance.db;
    final now = nowIsoUtc();
    await db.transaction((txn) async {
      for (final raw in rawTransactions) {
        final trx = TransactionApiItem.fromJson(raw);
        final existing = await txn.query(
          LocalDatabase.tableTransactionDrafts,
          where: 'server_id = ?',
          whereArgs: [trx.id],
          limit: 1,
        );

        final row = _buildRowFromRemote(raw, now);
        if (existing.isNotEmpty) {
          await txn.update(
            LocalDatabase.tableTransactionDrafts,
            row,
            where: 'server_id = ?',
            whereArgs: [trx.id],
          );
        } else {
          await txn.insert(LocalDatabase.tableTransactionDrafts, row);
        }
      }
    });
  }

  Future<void> cacheRemoteTransactionDetail(
    Map<String, dynamic> rawTransaction,
  ) async {
    await cacheRemoteTransactions([rawTransaction]);
  }

  Future<List<TransactionApiItem>> getTransactionsFromCache() async {
    final db = await LocalDatabase.instance.db;
    final rows = await db.query(
      LocalDatabase.tableTransactionDrafts,
      where: 'deleted_at IS NULL AND sync_status != ?',
      whereArgs: [SyncStatus.pendingDelete.value],
      orderBy: 'updated_at DESC',
    );
    return rows
        .map((row) => decodeJsonObject(row['payload_json']?.toString()))
        .where((json) => json.isNotEmpty)
        .map(TransactionApiItem.fromJson)
        .toList(growable: false);
  }

  Future<TransactionApiItem?> getTransactionDetailFromCache(int anyId) async {
    final db = await LocalDatabase.instance.db;
    final rows = await db.query(
      LocalDatabase.tableTransactionDrafts,
      where: '(server_id = ? OR temp_id = ?) AND deleted_at IS NULL',
      whereArgs: [anyId, anyId],
      limit: 1,
    );
    if (rows.isEmpty) return null;
    final json = decodeJsonObject(rows.first['payload_json']?.toString());
    if (json.isEmpty) return null;
    return TransactionApiItem.fromJson(json);
  }

  Future<({String localId, TransactionApiItem item})> savePendingCreate(
    CreateTransactionPayload payload,
  ) async {
    final db = await LocalDatabase.instance.db;
    final now = DateTime.now().toUtc();
    final localId = 'trx-${now.microsecondsSinceEpoch}';
    final tempId = generateTempId();
    final invoice = 'OFF-${now.millisecondsSinceEpoch}';

    final itemsJson = <Map<String, dynamic>>[];
    for (final line in payload.items) {
      final product = await _findProductByServerId(line.productId);
      final productCode =
          product?['code']?.toString() ?? 'PRD-${line.productId}';
      final productName =
          product?['name']?.toString() ?? 'Produk #${line.productId}';
      final categoryName = product?['category_name']?.toString() ?? '-';
      final price = line.price ?? ((product?['price'] as num?)?.toInt() ?? 0);

      itemsJson.add({
        'product_id': line.productId,
        'qty': line.qty,
        'price': price,
        'subtotal': line.qty * price,
        'product_code': productCode,
        'product_name': productName,
        'category_name': categoryName,
      });
    }

    final subtotal = itemsJson.fold<int>(
      0,
      (sum, e) => sum + ((e['subtotal'] as num?)?.toInt() ?? 0),
    );
    final total = subtotal - payload.discountAmount;
    final nowIso = now.toIso8601String();
    final paymentStatus = payload.paymentMethod.toLowerCase().contains('hutang')
        ? 'BELUM LUNAS'
        : 'LUNAS';

    final rawJson = <String, dynamic>{
      'id': tempId,
      'invoice_number': invoice,
      'created_at': nowIso,
      'customer_name': payload.customerName,
      'customer_phone': payload.customerPhone,
      'member_id': payload.memberId,
      'member': payload.memberId == null
          ? null
          : {'id': payload.memberId, 'name': '', 'nama': '', 'alamat': ''},
      'user': {'name': ''},
      'payment_method': payload.paymentMethod,
      'payment_status': paymentStatus,
      'total_amount': total,
      'discount': payload.discountAmount,
      'discount_percent': payload.discountPercent,
      'cash_received': payload.cashReceived,
      'change': (payload.cashReceived - total).clamp(0, 999999999),
      'items': itemsJson,
    };

    await db.insert(LocalDatabase.tableTransactionDrafts, {
      'local_id': localId,
      'server_id': null,
      'temp_id': tempId,
      'invoice_number': invoice,
      'customer_name': payload.customerName,
      'customer_phone': payload.customerPhone,
      'payment_method': payload.paymentMethod,
      'payment_status': paymentStatus,
      'total_amount': total,
      'payload_json': jsonEncode(rawJson),
      'sync_status': SyncStatus.pendingCreate.value,
      'local_revision': 1,
      'updated_at': nowIso,
      'deleted_at': null,
      'last_error': null,
    });

    return (localId: localId, item: TransactionApiItem.fromJson(rawJson));
  }

  Future<String?> findLocalIdByAnyId(int id) async {
    final db = await LocalDatabase.instance.db;
    final rows = await db.query(
      LocalDatabase.tableTransactionDrafts,
      columns: ['local_id'],
      where: 'server_id = ? OR temp_id = ?',
      whereArgs: [id, id],
      limit: 1,
    );
    if (rows.isEmpty) return null;
    return rows.first['local_id']?.toString();
  }

  Future<Map<String, dynamic>?> findRowByLocalId(String localId) async {
    final db = await LocalDatabase.instance.db;
    final rows = await db.query(
      LocalDatabase.tableTransactionDrafts,
      where: 'local_id = ?',
      whereArgs: [localId],
      limit: 1,
    );
    if (rows.isEmpty) return null;
    return rows.first;
  }

  Future<void> markSyncedFromServer({
    required String localId,
    required Map<String, dynamic> rawServerTransaction,
  }) async {
    final db = await LocalDatabase.instance.db;
    final row = _buildRowFromRemote(rawServerTransaction, nowIsoUtc());
    await db.update(
      LocalDatabase.tableTransactionDrafts,
      {
        ...row,
        'sync_status': SyncStatus.synced.value,
        'local_revision': 1,
        'deleted_at': null,
        'last_error': null,
      },
      where: 'local_id = ?',
      whereArgs: [localId],
    );
  }

  Future<void> markPendingDelete(int id) async {
    final db = await LocalDatabase.instance.db;
    final rows = await db.query(
      LocalDatabase.tableTransactionDrafts,
      where: 'server_id = ? OR temp_id = ?',
      whereArgs: [id, id],
      limit: 1,
    );
    if (rows.isEmpty) return;
    final row = rows.first;
    final status = SyncStatus.fromValue(row['sync_status']?.toString());
    if (status == SyncStatus.pendingCreate) {
      await db.delete(
        LocalDatabase.tableTransactionDrafts,
        where: 'local_id = ?',
        whereArgs: [row['local_id']],
      );
      return;
    }
    await db.update(
      LocalDatabase.tableTransactionDrafts,
      {
        'sync_status': SyncStatus.pendingDelete.value,
        'updated_at': nowIsoUtc(),
        'deleted_at': nowIsoUtc(),
        'local_revision': ((row['local_revision'] as num?)?.toInt() ?? 0) + 1,
      },
      where: 'local_id = ?',
      whereArgs: [row['local_id']],
    );
  }

  Future<void> removeByLocalId(String localId) async {
    final db = await LocalDatabase.instance.db;
    await db.delete(
      LocalDatabase.tableTransactionDrafts,
      where: 'local_id = ?',
      whereArgs: [localId],
    );
  }

  Future<void> markSyncError(String localId, String error) async {
    final db = await LocalDatabase.instance.db;
    await db.update(
      LocalDatabase.tableTransactionDrafts,
      {
        'sync_status': SyncStatus.failed.value,
        'last_error': error,
        'updated_at': nowIsoUtc(),
      },
      where: 'local_id = ?',
      whereArgs: [localId],
    );
  }

  Map<String, dynamic> _buildRowFromRemote(
    Map<String, dynamic> raw,
    String now,
  ) {
    final trx = TransactionApiItem.fromJson(raw);
    return {
      'local_id': 'trx-server-${trx.id}',
      'server_id': trx.id,
      'temp_id': null,
      'invoice_number': trx.invoiceNumber,
      'customer_name': trx.customerName,
      'customer_phone': trx.customerPhone,
      'payment_method': trx.paymentMethod,
      'payment_status': trx.paymentStatus,
      'total_amount': trx.totalAmount,
      'payload_json': jsonEncode(raw),
      'sync_status': SyncStatus.synced.value,
      'local_revision': 1,
      'updated_at': raw['updated_at']?.toString() ?? now,
      'deleted_at': raw['deleted_at']?.toString(),
      'last_error': null,
    };
  }

  Future<Map<String, dynamic>?> _findProductByServerId(int serverId) async {
    final db = await LocalDatabase.instance.db;
    final rows = await db.query(
      LocalDatabase.tableProducts,
      where: 'server_id = ?',
      whereArgs: [serverId],
      limit: 1,
    );
    if (rows.isEmpty) return null;
    return rows.first;
  }
}
