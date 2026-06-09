import '../../models/pengiriman_model.dart';
import 'entity_cache_repository.dart';
import 'offline_utils.dart';
import 'sync_types.dart';

class DeliveryLocalRepository {
  DeliveryLocalRepository._();
  static final DeliveryLocalRepository instance = DeliveryLocalRepository._();

  static const _kDeliveries = 'delivery:list';
  static const _kDrivers = 'delivery:drivers';
  static const _kVehicles = 'delivery:vehicles';
  static const _kInvoices = 'delivery:invoices';

  Future<void> cacheDeliveries(List<Map<String, dynamic>> rows) {
    final normalized = rows.map(_normalizeServerRow).toList(growable: false);
    return EntityCacheRepository.instance.saveList(_kDeliveries, normalized);
  }

  Future<List<PengirimanItem>> getDeliveries() async {
    final rows = await getDeliveryRows();
    return rows.map(PengirimanItem.fromJson).toList(growable: false);
  }

  Future<List<Map<String, dynamic>>> getDeliveryRows() async {
    final rows = await EntityCacheRepository.instance.getList(_kDeliveries);
    return rows.map((e) => Map<String, dynamic>.from(e)).toList(growable: true);
  }

  Future<PengirimanItem?> getDeliveryDetail(int deliveryId) async {
    final list = await getDeliveries();
    for (final item in list) {
      if (item.id == deliveryId) return item;
    }
    return null;
  }

  Future<int?> findServerIdByLocalId(String localId) async {
    final rows = await getDeliveryRows();
    for (final row in rows) {
      if ((row['local_id'] ?? '').toString() == localId) {
        final server = row['server_id'];
        if (server is num) return server.toInt();
        final id = row['id'];
        if (id is num && id.toInt() > 0) return id.toInt();
      }
    }
    return null;
  }

  Future<Map<String, dynamic>?> findRowByLocalId(String localId) async {
    final rows = await getDeliveryRows();
    for (final row in rows) {
      if ((row['local_id'] ?? '').toString() == localId) return row;
    }
    return null;
  }

  Future<void> upsertLocalRow(Map<String, dynamic> row) async {
    final rows = await getDeliveryRows();
    final normalized = _normalizeLocalRow(row);
    final localId = normalized['local_id']?.toString() ?? '';
    final serverId = (normalized['server_id'] as num?)?.toInt() ??
        (normalized['id'] as num?)?.toInt();

    final idx = rows.indexWhere((item) {
      final itemLocalId = (item['local_id'] ?? '').toString();
      if (localId.isNotEmpty && itemLocalId == localId) return true;
      final itemServerId = (item['server_id'] as num?)?.toInt() ??
          (item['id'] as num?)?.toInt();
      return serverId != null && serverId > 0 && itemServerId == serverId;
    });

    if (idx >= 0) {
      rows[idx] = {...rows[idx], ...normalized, 'updated_at': nowIsoUtc()};
    } else {
      rows.insert(0, normalized);
    }
    await EntityCacheRepository.instance.saveList(_kDeliveries, rows);
  }

  Future<void> markSyncedFromServer({
    required String localId,
    required Map<String, dynamic> rawServerDelivery,
  }) async {
    final rows = await getDeliveryRows();
    final normalized = _normalizeServerRow(rawServerDelivery);
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
    await EntityCacheRepository.instance.saveList(_kDeliveries, rows);
  }

  Future<void> removeByLocalId(String localId) async {
    final rows = await getDeliveryRows();
    rows.removeWhere((row) => (row['local_id'] ?? '').toString() == localId);
    await EntityCacheRepository.instance.saveList(_kDeliveries, rows);
  }

  Future<void> cacheDrivers(List<Map<String, dynamic>> rows) {
    return EntityCacheRepository.instance.saveList(_kDrivers, rows);
  }

  Future<List<DeliveryDriverOption>> getDrivers() async {
    final rows = await EntityCacheRepository.instance.getList(_kDrivers);
    return rows.map(DeliveryDriverOption.fromJson).toList(growable: false);
  }

  Future<void> cacheVehicles(List<Map<String, dynamic>> rows) {
    return EntityCacheRepository.instance.saveList(_kVehicles, rows);
  }

  Future<List<DeliveryVehicleOption>> getVehicles() async {
    final rows = await EntityCacheRepository.instance.getList(_kVehicles);
    return rows.map(DeliveryVehicleOption.fromJson).toList(growable: false);
  }

  Future<void> cacheInvoices(List<Map<String, dynamic>> rows) {
    return EntityCacheRepository.instance.saveList(_kInvoices, rows);
  }

  Future<List<DeliveryInvoiceOption>> getInvoices() async {
    final rows = await EntityCacheRepository.instance.getList(_kInvoices);
    return rows.map(DeliveryInvoiceOption.fromJson).toList(growable: false);
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
