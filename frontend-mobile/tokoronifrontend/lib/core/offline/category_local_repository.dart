import '../services/category_service.dart';
import 'local_database.dart';
import 'offline_utils.dart';
import 'sync_types.dart';

class CategoryLocalRepository {
  CategoryLocalRepository._();
  static final CategoryLocalRepository instance = CategoryLocalRepository._();

  Future<void> cacheRemoteCategories(
    List<Map<String, dynamic>> rawCategories,
  ) async {
    final db = await LocalDatabase.instance.db;
    final now = nowIsoUtc();
    await db.transaction((txn) async {
      for (final raw in rawCategories) {
        final category = CategoryRecord.fromJson(raw);
        final serverId = category.id;

        final existing = await txn.query(
          LocalDatabase.tableCategories,
          where: 'server_id = ?',
          whereArgs: [serverId],
          limit: 1,
        );

        final incomingUpdated = _updatedAtFromRaw(
          raw['updated_at']?.toString(),
          now,
        );
        if (existing.isNotEmpty) {
          final row = existing.first;
          final status = SyncStatus.fromValue(row['sync_status']?.toString());
          final localUpdated = _updatedAtFromRaw(
            row['updated_at']?.toString(),
            now,
          );
          final localIsDirty = status != SyncStatus.synced;
          if (localIsDirty && localUpdated.isAfter(incomingUpdated)) continue;

          await txn.update(
            LocalDatabase.tableCategories,
            _buildRowFromRemote(raw, row['local_id']?.toString(), now),
            where: 'server_id = ?',
            whereArgs: [serverId],
          );
          continue;
        }

        await txn.insert(
          LocalDatabase.tableCategories,
          _buildRowFromRemote(raw, _newLocalId(), now),
        );
      }
    });
  }

  Future<List<CategoryRecord>> getCategories({
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
      LocalDatabase.tableCategories,
      where: whereParts.join(' AND '),
      whereArgs: args,
      orderBy: 'name COLLATE NOCASE ASC',
    );
    return rows.map(_toCategoryRecord).toList(growable: false);
  }

  Future<({String localId, CategoryRecord item})> savePendingCreate({
    required String nama,
    required String slug,
    required String deskripsi,
    required bool aktif,
  }) async {
    final db = await LocalDatabase.instance.db;
    final tempId = generateTempId();
    final localId = _newLocalId();
    final now = nowIsoUtc();
    await db.insert(LocalDatabase.tableCategories, {
      'local_id': localId,
      'server_id': null,
      'temp_id': tempId,
      'name': nama,
      'slug': slug,
      'description': deskripsi,
      'is_active': aktif ? 1 : 0,
      'products_count': 0,
      'sync_status': SyncStatus.pendingCreate.value,
      'local_revision': 1,
      'updated_at': now,
      'deleted_at': null,
      'last_error': null,
    });

    final item = CategoryRecord(
      id: tempId,
      nama: nama,
      slug: slug,
      deskripsi: deskripsi,
      aktif: aktif,
      totalProduk: 0,
      terakhirDiperbarui: _displayDate(now),
    );
    return (localId: localId, item: item);
  }

  Future<void> savePendingUpdate({
    required int categoryId,
    required String nama,
    required String slug,
    required String deskripsi,
    required bool aktif,
  }) async {
    final db = await LocalDatabase.instance.db;
    final rows = await db.query(
      LocalDatabase.tableCategories,
      where: 'server_id = ? OR temp_id = ?',
      whereArgs: [categoryId, categoryId],
      limit: 1,
    );
    if (rows.isEmpty) return;

    final row = rows.first;
    final status = SyncStatus.fromValue(row['sync_status']?.toString());
    final nextStatus = status == SyncStatus.pendingCreate
        ? SyncStatus.pendingCreate
        : SyncStatus.pendingUpdate;

    await db.update(
      LocalDatabase.tableCategories,
      {
        'name': nama,
        'slug': slug,
        'description': deskripsi,
        'is_active': aktif ? 1 : 0,
        'sync_status': nextStatus.value,
        'local_revision': ((row['local_revision'] as num?)?.toInt() ?? 0) + 1,
        'updated_at': nowIsoUtc(),
        'last_error': null,
      },
      where: 'local_id = ?',
      whereArgs: [row['local_id']],
    );
  }

  Future<void> savePendingDelete(int categoryId) async {
    final db = await LocalDatabase.instance.db;
    final rows = await db.query(
      LocalDatabase.tableCategories,
      where: 'server_id = ? OR temp_id = ?',
      whereArgs: [categoryId, categoryId],
      limit: 1,
    );
    if (rows.isEmpty) return;

    final row = rows.first;
    final status = SyncStatus.fromValue(row['sync_status']?.toString());
    if (status == SyncStatus.pendingCreate) {
      await db.delete(
        LocalDatabase.tableCategories,
        where: 'local_id = ?',
        whereArgs: [row['local_id']],
      );
      return;
    }

    await db.update(
      LocalDatabase.tableCategories,
      {
        'sync_status': SyncStatus.pendingDelete.value,
        'local_revision': ((row['local_revision'] as num?)?.toInt() ?? 0) + 1,
        'updated_at': nowIsoUtc(),
        'deleted_at': nowIsoUtc(),
      },
      where: 'local_id = ?',
      whereArgs: [row['local_id']],
    );
  }

  Future<String?> findLocalIdByAnyId(int id) async {
    final db = await LocalDatabase.instance.db;
    final rows = await db.query(
      LocalDatabase.tableCategories,
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
      LocalDatabase.tableCategories,
      where: 'local_id = ?',
      whereArgs: [localId],
      limit: 1,
    );
    if (rows.isEmpty) return null;
    return rows.first;
  }

  Future<void> markSyncedFromServer({
    required String localId,
    required Map<String, dynamic> rawServerCategory,
  }) async {
    final db = await LocalDatabase.instance.db;
    await db.update(
      LocalDatabase.tableCategories,
      {
        ..._buildRowFromRemote(rawServerCategory, localId, nowIsoUtc()),
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
      LocalDatabase.tableCategories,
      where: 'local_id = ?',
      whereArgs: [localId],
    );
  }

  Future<void> markSyncError(String localId, String error) async {
    final db = await LocalDatabase.instance.db;
    await db.update(
      LocalDatabase.tableCategories,
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
    final category = CategoryRecord.fromJson(raw);
    return {
      'local_id': localId ?? _newLocalId(),
      'server_id': category.id,
      'temp_id': null,
      'name': category.nama,
      'slug': category.slug,
      'description': category.deskripsi,
      'is_active': category.aktif ? 1 : 0,
      'products_count': category.totalProduk,
      'sync_status': SyncStatus.synced.value,
      'local_revision': 1,
      'updated_at': raw['updated_at']?.toString() ?? now,
      'deleted_at': raw['deleted_at']?.toString(),
      'last_error': null,
    };
  }

  CategoryRecord _toCategoryRecord(Map<String, dynamic> row) {
    final serverId = (row['server_id'] as num?)?.toInt();
    final tempId = (row['temp_id'] as num?)?.toInt();
    final updatedAt = row['updated_at']?.toString() ?? nowIsoUtc();
    return CategoryRecord(
      id: serverId ?? tempId ?? 0,
      nama: row['name']?.toString() ?? '',
      slug: row['slug']?.toString() ?? '',
      deskripsi: row['description']?.toString() ?? '',
      aktif: ((row['is_active'] as num?)?.toInt() ?? 1) == 1,
      totalProduk: (row['products_count'] as num?)?.toInt() ?? 0,
      terakhirDiperbarui: _displayDate(updatedAt),
    );
  }

  DateTime _updatedAtFromRaw(String? raw, String fallback) {
    return DateTime.tryParse(raw ?? '')?.toUtc() ??
        DateTime.tryParse(fallback)?.toUtc() ??
        DateTime.now().toUtc();
  }

  String _displayDate(String raw) {
    final dt = DateTime.tryParse(raw);
    if (dt == null) return '-';
    final dd = dt.day.toString().padLeft(2, '0');
    final mm = dt.month.toString().padLeft(2, '0');
    return '$dd-$mm-${dt.year}';
  }

  String _newLocalId() => 'cat-${DateTime.now().microsecondsSinceEpoch}';
}
