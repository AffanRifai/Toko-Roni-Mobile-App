import 'package:sqflite/sqflite.dart';

import 'local_database.dart';
import 'offline_utils.dart';

class CartLocalRepository {
  CartLocalRepository._();
  static final CartLocalRepository instance = CartLocalRepository._();

  Future<void> saveActiveCart({
    required String userId,
    required List<Map<String, dynamic>> items,
  }) async {
    if (userId.trim().isEmpty) return;
    final db = await LocalDatabase.instance.db;
    final cartId = 'cart-$userId';
    final now = nowIsoUtc();

    await db.transaction((txn) async {
      await txn.insert(LocalDatabase.tableCart, {
        'local_id': cartId,
        'user_id': userId,
        'status': 'active',
        'updated_at': now,
      }, conflictAlgorithm: ConflictAlgorithm.replace);

      await txn.delete(
        LocalDatabase.tableCartItems,
        where: 'cart_local_id = ?',
        whereArgs: [cartId],
      );

      for (final item in items) {
        await txn.insert(LocalDatabase.tableCartItems, {
          'local_id': 'ci-${DateTime.now().microsecondsSinceEpoch}',
          'cart_local_id': cartId,
          'product_local_id': item['product_local_id']?.toString(),
          'product_server_id': item['product_server_id'],
          'product_name': item['product_name']?.toString() ?? '',
          'product_code': item['product_code']?.toString() ?? '',
          'qty': item['qty'] ?? 1,
          'price': item['price'] ?? 0,
          'updated_at': now,
        });
      }
    });
  }

  Future<List<Map<String, dynamic>>> loadActiveCart(String userId) async {
    if (userId.trim().isEmpty) return const [];
    final db = await LocalDatabase.instance.db;
    final cartId = 'cart-$userId';
    final rows = await db.query(
      LocalDatabase.tableCartItems,
      where: 'cart_local_id = ?',
      whereArgs: [cartId],
      orderBy: 'updated_at ASC',
    );
    return rows;
  }

  Future<void> clearActiveCart(String userId) async {
    if (userId.trim().isEmpty) return;
    final db = await LocalDatabase.instance.db;
    final cartId = 'cart-$userId';
    await db.transaction((txn) async {
      await txn.delete(
        LocalDatabase.tableCartItems,
        where: 'cart_local_id = ?',
        whereArgs: [cartId],
      );
      await txn.delete(
        LocalDatabase.tableCart,
        where: 'local_id = ?',
        whereArgs: [cartId],
      );
    });
  }
}
