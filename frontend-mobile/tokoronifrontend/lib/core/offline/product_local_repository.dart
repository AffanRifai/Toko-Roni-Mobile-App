import '../../models/produk_model.dart';
import 'local_database.dart';
import 'offline_utils.dart';
import 'sync_types.dart';

class ProductLocalRepository {
  ProductLocalRepository._();
  static final ProductLocalRepository instance = ProductLocalRepository._();

  Future<void> cacheRemoteProducts(
    List<Map<String, dynamic>> rawProducts,
  ) async {
    final db = await LocalDatabase.instance.db;
    final now = nowIsoUtc();

    await db.transaction((txn) async {
      for (final raw in rawProducts) {
        final serverId = _toIntOrNull(raw['id']);
        if (serverId == null) continue;

        final existing = await txn.query(
          LocalDatabase.tableProducts,
          where: 'server_id = ?',
          whereArgs: [serverId],
          limit: 1,
        );

        final incomingUpdatedAt = _bestUpdatedAt(
          raw['updated_at']?.toString(),
          fallback: now,
        );

        if (existing.isNotEmpty) {
          final row = existing.first;
          final status = SyncStatus.fromValue(row['sync_status']?.toString());
          final localUpdated = _bestUpdatedAt(
            row['updated_at']?.toString(),
            fallback: now,
          );

          final localIsDirty = status != SyncStatus.synced;
          final keepLocal =
              localIsDirty && localUpdated.isAfter(incomingUpdatedAt);
          if (keepLocal) continue;

          final values = _buildRowFromRemote(
            raw,
            row['local_id']?.toString(),
            now,
          );
          await txn.update(
            LocalDatabase.tableProducts,
            values,
            where: 'server_id = ?',
            whereArgs: [serverId],
          );
          continue;
        }

        final localId = _newLocalId('prd');
        final values = _buildRowFromRemote(raw, localId, now);
        await txn.insert(LocalDatabase.tableProducts, values);
      }
    });
  }

  Future<List<ProdukItem>> getProducts({
    bool includePendingDelete = false,
  }) async {
    final db = await LocalDatabase.instance.db;
    final whereParts = <String>['deleted_at IS NULL'];
    final args = <Object?>[];
    if (!includePendingDelete) {
      whereParts.add('sync_status != ?');
      args.add(SyncStatus.pendingDelete.value);
    }
    final rows = await db.query(
      LocalDatabase.tableProducts,
      where: whereParts.join(' AND '),
      whereArgs: args,
      orderBy: 'name COLLATE NOCASE ASC',
    );
    return rows.map(_toProdukItem).toList(growable: false);
  }

  Future<String?> findLocalIdByAnyId(int id) async {
    final db = await LocalDatabase.instance.db;
    final rows = await db.query(
      LocalDatabase.tableProducts,
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
      LocalDatabase.tableProducts,
      where: 'local_id = ?',
      whereArgs: [localId],
      limit: 1,
    );
    if (rows.isEmpty) return null;
    return rows.first;
  }

  Future<ProdukItem?> findProductByServerId(int serverId) async {
    final db = await LocalDatabase.instance.db;
    final rows = await db.query(
      LocalDatabase.tableProducts,
      where: 'server_id = ? AND deleted_at IS NULL',
      whereArgs: [serverId],
      limit: 1,
    );
    if (rows.isEmpty) return null;
    return _toProdukItem(rows.first);
  }

  Future<({String localId, ProdukItem item})> savePendingCreate({
    required Map<String, dynamic> payload,
    required String categoryName,
  }) async {
    final db = await LocalDatabase.instance.db;
    final now = nowIsoUtc();
    final localId = _newLocalId('prd');
    final tempId = generateTempId();

    await db.insert(LocalDatabase.tableProducts, {
      'local_id': localId,
      'server_id': null,
      'temp_id': tempId,
      'code': (payload['code'] ?? '').toString(),
      'name': (payload['name'] ?? '').toString(),
      'category_id': _toIntOrNull(payload['category_id']),
      'category_name': categoryName,
      'description': (payload['description'] ?? '').toString(),
      'stock': _toInt(payload['stock']),
      'min_stock': _toInt(payload['min_stock'], fallback: 10),
      'unit': (payload['unit'] ?? 'Pcs').toString(),
      'barcode': (payload['barcode'] ?? '').toString(),
      'weight': _toDoubleOrNull(payload['weight']),
      'dimensions': (payload['dimensions'] ?? '').toString(),
      'expiry_date': payload['expiry_date']?.toString(),
      'is_active': _toBoolInt(payload['is_active'], fallback: 1),
      'price': _toInt(payload['price']),
      'cost_price': _toDoubleOrNull(payload['cost_price']),
      'sync_status': SyncStatus.pendingCreate.value,
      'local_revision': 1,
      'updated_at': now,
      'deleted_at': null,
      'last_error': null,
    });

    final item = ProdukItem(
      id: tempId,
      kode: (payload['code'] ?? '').toString(),
      nama: (payload['name'] ?? '').toString(),
      kategori: categoryName,
      deskripsi: (payload['description'] ?? '').toString(),
      jenis: (payload['unit'] ?? 'Pcs').toString(),
      harga: _toInt(payload['price']),
      hargaModal: _toInt(payload['cost_price']),
      stok: _toInt(payload['stock']),
      stokMinimum: _toInt(payload['min_stock'], fallback: 10),
      barcode: (payload['barcode'] ?? '').toString(),
      berat: payload['weight']?.toString() ?? '',
      dimensi: (payload['dimensions'] ?? '').toString(),
      kadaluarsa: _displayDate(payload['expiry_date']?.toString()),
      aktif: _toBoolInt(payload['is_active'], fallback: 1) == 1,
    );
    return (localId: localId, item: item);
  }

  Future<void> savePendingUpdate({
    required int productId,
    required Map<String, dynamic> payload,
    required String categoryName,
  }) async {
    final db = await LocalDatabase.instance.db;
    final rows = await db.query(
      LocalDatabase.tableProducts,
      where: 'server_id = ? OR temp_id = ?',
      whereArgs: [productId, productId],
      limit: 1,
    );
    if (rows.isEmpty) return;

    final row = rows.first;
    final currentStatus = SyncStatus.fromValue(row['sync_status']?.toString());
    final nextStatus = currentStatus == SyncStatus.pendingCreate
        ? SyncStatus.pendingCreate
        : SyncStatus.pendingUpdate;

    final revision = ((row['local_revision'] as num?)?.toInt() ?? 0) + 1;
    await db.update(
      LocalDatabase.tableProducts,
      {
        'code': (payload['code'] ?? row['code']).toString(),
        'name': (payload['name'] ?? row['name']).toString(),
        'category_id': _toIntOrNull(payload['category_id']),
        'category_name': categoryName,
        'description': (payload['description'] ?? '').toString(),
        'stock': _toInt(payload['stock']),
        'min_stock': _toInt(payload['min_stock'], fallback: 10),
        'unit': (payload['unit'] ?? 'Pcs').toString(),
        'barcode': (payload['barcode'] ?? '').toString(),
        'weight': _toDoubleOrNull(payload['weight']),
        'dimensions': (payload['dimensions'] ?? '').toString(),
        'expiry_date': payload['expiry_date']?.toString(),
        'is_active': _toBoolInt(payload['is_active'], fallback: 1),
        'price': _toInt(payload['price']),
        'cost_price': _toDoubleOrNull(payload['cost_price']),
        'sync_status': nextStatus.value,
        'local_revision': revision,
        'updated_at': nowIsoUtc(),
        'last_error': null,
      },
      where: 'local_id = ?',
      whereArgs: [row['local_id']],
    );
  }

  Future<void> savePendingDelete(int productId) async {
    final db = await LocalDatabase.instance.db;
    final rows = await db.query(
      LocalDatabase.tableProducts,
      where: 'server_id = ? OR temp_id = ?',
      whereArgs: [productId, productId],
      limit: 1,
    );
    if (rows.isEmpty) return;

    final row = rows.first;
    final currentStatus = SyncStatus.fromValue(row['sync_status']?.toString());
    if (currentStatus == SyncStatus.pendingCreate) {
      await db.delete(
        LocalDatabase.tableProducts,
        where: 'local_id = ?',
        whereArgs: [row['local_id']],
      );
      return;
    }

    final revision = ((row['local_revision'] as num?)?.toInt() ?? 0) + 1;
    await db.update(
      LocalDatabase.tableProducts,
      {
        'sync_status': SyncStatus.pendingDelete.value,
        'local_revision': revision,
        'updated_at': nowIsoUtc(),
        'deleted_at': nowIsoUtc(),
      },
      where: 'local_id = ?',
      whereArgs: [row['local_id']],
    );
  }

  Future<void> markSyncedFromServer({
    required String localId,
    required Map<String, dynamic> rawServerProduct,
  }) async {
    final db = await LocalDatabase.instance.db;
    final values = _buildRowFromRemote(rawServerProduct, localId, nowIsoUtc());
    await db.update(
      LocalDatabase.tableProducts,
      {
        ...values,
        'sync_status': SyncStatus.synced.value,
        'local_revision': 1,
        'deleted_at': null,
        'last_error': null,
      },
      where: 'local_id = ?',
      whereArgs: [localId],
    );
  }

  Future<void> removeByLocalId(String localId) async {
    final db = await LocalDatabase.instance.db;
    await db.delete(
      LocalDatabase.tableProducts,
      where: 'local_id = ?',
      whereArgs: [localId],
    );
  }

  Future<void> markSyncError(String localId, String error) async {
    final db = await LocalDatabase.instance.db;
    await db.update(
      LocalDatabase.tableProducts,
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
    String? localId,
    String now,
  ) {
    final product = ProdukItem.fromJson(raw);
    final categoryRaw = raw['category'];
    final categoryName = categoryRaw is Map
        ? (categoryRaw['name'] ?? categoryRaw['nama'] ?? product.kategori)
              .toString()
        : product.kategori;

    return {
      'local_id': localId ?? _newLocalId('prd'),
      'server_id': _toIntOrNull(raw['id']),
      'temp_id': null,
      'code': product.kode,
      'name': product.nama,
      'category_id': _toIntOrNull(raw['category_id']),
      'category_name': categoryName,
      'description': product.deskripsi,
      'stock': product.stok,
      'min_stock': product.stokMinimum,
      'unit': product.jenis,
      'barcode': product.barcode,
      'weight': _toDoubleOrNull(raw['weight']),
      'dimensions': product.dimensi,
      'expiry_date': raw['expiry_date']?.toString(),
      'is_active': product.aktif ? 1 : 0,
      'price': product.harga,
      'cost_price':
          _toDoubleOrNull(raw['cost_price']) ?? product.hargaModal.toDouble(),
      'sync_status': SyncStatus.synced.value,
      'local_revision': 1,
      'updated_at': raw['updated_at']?.toString() ?? now,
      'deleted_at': raw['deleted_at']?.toString(),
      'last_error': null,
    };
  }

  ProdukItem _toProdukItem(Map<String, dynamic> row) {
    final serverId = (row['server_id'] as num?)?.toInt();
    final tempId = (row['temp_id'] as num?)?.toInt();
    return ProdukItem(
      id: serverId ?? tempId,
      kode: row['code']?.toString() ?? '',
      nama: row['name']?.toString() ?? '',
      kategori: row['category_name']?.toString() ?? '-',
      deskripsi: row['description']?.toString() ?? '',
      jenis: row['unit']?.toString() ?? 'Pcs',
      harga: (row['price'] as num?)?.toInt() ?? 0,
      hargaModal: ((row['cost_price'] as num?)?.toDouble() ?? 0).round(),
      stok: (row['stock'] as num?)?.toInt() ?? 0,
      stokMinimum: (row['min_stock'] as num?)?.toInt() ?? 10,
      barcode: row['barcode']?.toString() ?? '',
      berat: _weightDisplay(row['weight']),
      dimensi: row['dimensions']?.toString() ?? '',
      kadaluarsa: _displayDate(row['expiry_date']?.toString()),
      aktif: ((row['is_active'] as num?)?.toInt() ?? 1) == 1,
    );
  }

  String _displayDate(String? isoLike) {
    if (isoLike == null || isoLike.trim().isEmpty) return '-';
    final dt = DateTime.tryParse(isoLike);
    if (dt == null) return isoLike;
    final dd = dt.day.toString().padLeft(2, '0');
    final mm = dt.month.toString().padLeft(2, '0');
    return '$dd-$mm-${dt.year}';
  }

  String _weightDisplay(dynamic value) {
    if (value == null) return '';
    final n = (value as num?)?.toDouble();
    if (n == null || n == 0) return '';
    if (n % 1 == 0) return n.toInt().toString();
    return n.toString();
  }

  DateTime _bestUpdatedAt(String? raw, {required String fallback}) {
    return DateTime.tryParse(raw ?? '')?.toUtc() ??
        DateTime.tryParse(fallback)?.toUtc() ??
        DateTime.now().toUtc();
  }

  String _newLocalId(String prefix) {
    final micros = DateTime.now().microsecondsSinceEpoch;
    return '$prefix-$micros';
  }

  int _toInt(dynamic value, {int fallback = 0}) {
    if (value == null) return fallback;
    if (value is int) return value;
    if (value is num) return value.toInt();
    return int.tryParse(value.toString()) ?? fallback;
  }

  int? _toIntOrNull(dynamic value) {
    if (value == null) return null;
    if (value is int) return value;
    if (value is num) return value.toInt();
    return int.tryParse(value.toString());
  }

  double? _toDoubleOrNull(dynamic value) {
    if (value == null) return null;
    if (value is double) return value;
    if (value is num) return value.toDouble();
    return double.tryParse(value.toString().replaceAll(',', '.'));
  }

  int _toBoolInt(dynamic value, {required int fallback}) {
    if (value == null) return fallback;
    if (value is bool) return value ? 1 : 0;
    if (value is num) return value.toInt() == 0 ? 0 : 1;
    final raw = value.toString().toLowerCase().trim();
    if (raw == 'true' || raw == '1') return 1;
    if (raw == 'false' || raw == '0') return 0;
    return fallback;
  }
}
