import '../../models/kendaraan_model.dart';
import 'entity_cache_repository.dart';
import 'offline_utils.dart';
import 'sync_types.dart';

class VehicleLocalRepository {
  VehicleLocalRepository._();
  static final VehicleLocalRepository instance = VehicleLocalRepository._();

  static const _kVehicles = 'vehicle:list';

  Future<void> cacheVehicles(List<Map<String, dynamic>> rows) {
    final normalized = rows.map(_normalizeServerRow).toList(growable: false);
    return EntityCacheRepository.instance.saveList(_kVehicles, normalized);
  }

  Future<List<KendaraanItem>> getVehicles() async {
    final rows = await getVehicleRows();
    return rows.map(KendaraanItem.fromJson).toList(growable: false);
  }

  Future<List<Map<String, dynamic>>> getVehicleRows() async {
    final rows = await EntityCacheRepository.instance.getList(_kVehicles);
    return rows.map((e) => Map<String, dynamic>.from(e)).toList(growable: true);
  }

  Future<KendaraanItem?> getVehicleDetail(int vehicleId) async {
    final list = await getVehicles();
    for (final item in list) {
      if (item.id == vehicleId) return item;
    }
    return null;
  }

  Future<Map<String, dynamic>?> findRowByLocalId(String localId) async {
    final rows = await getVehicleRows();
    for (final row in rows) {
      if ((row['local_id'] ?? '').toString() == localId) return row;
    }
    return null;
  }

  Future<int?> findServerIdByLocalId(String localId) async {
    final row = await findRowByLocalId(localId);
    if (row == null) return null;
    final id = row['server_id'] ?? row['id'];
    if (id is num) return id.toInt();
    return int.tryParse(id?.toString() ?? '');
  }

  Future<void> upsertLocalRow(Map<String, dynamic> row) async {
    final rows = await getVehicleRows();
    final normalized = _normalizeLocalRow(row);
    final localId = normalized['local_id']?.toString() ?? '';
    final serverId = (normalized['server_id'] as num?)?.toInt() ??
        (normalized['id'] as num?)?.toInt();

    final idx = rows.indexWhere((item) {
      if ((item['local_id'] ?? '').toString() == localId) return true;
      final itemServerId =
          (item['server_id'] as num?)?.toInt() ?? (item['id'] as num?)?.toInt();
      return serverId != null && serverId > 0 && itemServerId == serverId;
    });
    if (idx >= 0) {
      rows[idx] = {...rows[idx], ...normalized, 'updated_at': nowIsoUtc()};
    } else {
      rows.insert(0, normalized);
    }
    await EntityCacheRepository.instance.saveList(_kVehicles, rows);
  }

  Future<void> markSyncedFromServer({
    required String localId,
    required Map<String, dynamic> rawServerVehicle,
  }) async {
    final rows = await getVehicleRows();
    final normalized = _normalizeServerRow(rawServerVehicle);
    final idx = rows.indexWhere(
      (item) => (item['local_id'] ?? '').toString() == localId,
    );
    if (idx >= 0) {
      rows[idx] = {
        ...rows[idx],
        ...normalized,
        'local_id': localId,
        'sync_status': SyncStatus.synced.value,
        'updated_at': nowIsoUtc(),
      };
    } else {
      rows.insert(0, {
        ...normalized,
        'local_id': localId,
        'sync_status': SyncStatus.synced.value,
        'updated_at': nowIsoUtc(),
      });
    }
    await EntityCacheRepository.instance.saveList(_kVehicles, rows);
  }

  Future<void> removeByLocalId(String localId) async {
    final rows = await getVehicleRows();
    rows.removeWhere((row) => (row['local_id'] ?? '').toString() == localId);
    await EntityCacheRepository.instance.saveList(_kVehicles, rows);
  }

  Map<String, dynamic> _normalizeServerRow(Map<String, dynamic> row) {
    return {
      ...row,
      'local_id': (row['local_id'] ?? 'srv-${row['id'] ?? nowIsoUtc()}')
          .toString(),
      'server_id': row['server_id'] ?? row['id'],
      'sync_status': SyncStatus.synced.value,
      'updated_at': nowIsoUtc(),
    };
  }

  Map<String, dynamic> _normalizeLocalRow(Map<String, dynamic> row) {
    final localId = (row['local_id'] ?? 'loc-${DateTime.now().microsecondsSinceEpoch}')
        .toString();
    return {
      ...row,
      'local_id': localId,
      'sync_status':
          (row['sync_status'] ?? SyncStatus.pendingCreate.value).toString(),
      'updated_at': nowIsoUtc(),
    };
  }
}
