import '../services/member_service.dart';
import 'entity_cache_repository.dart';
import 'offline_utils.dart';
import 'sync_types.dart';

class MemberLocalRepository {
  MemberLocalRepository._();
  static final MemberLocalRepository instance = MemberLocalRepository._();

  static const _kMembers = 'member:list';

  String _receivableKey(int memberId) => 'member:receivable:$memberId';

  Future<void> cacheMembers(List<Map<String, dynamic>> rows) {
    final normalized = rows.map(_normalizeServerRow).toList(growable: false);
    return EntityCacheRepository.instance.saveList(_kMembers, normalized);
  }

  Future<List<MemberRecord>> getMembers() async {
    final rows = await getMemberRows();
    return rows.map(MemberRecord.fromJson).toList(growable: false);
  }

  Future<List<Map<String, dynamic>>> getMemberRows() async {
    final rows = await EntityCacheRepository.instance.getList(_kMembers);
    return rows.map((e) => Map<String, dynamic>.from(e)).toList(growable: true);
  }

  Future<Map<String, dynamic>?> findRowByLocalId(String localId) async {
    final rows = await getMemberRows();
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
    final rows = await getMemberRows();
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
    await EntityCacheRepository.instance.saveList(_kMembers, rows);
  }

  Future<void> markSyncedFromServer({
    required String localId,
    required Map<String, dynamic> rawServerMember,
  }) async {
    final rows = await getMemberRows();
    final normalized = _normalizeServerRow(rawServerMember);
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
    await EntityCacheRepository.instance.saveList(_kMembers, rows);
  }

  Future<void> removeByLocalId(String localId) async {
    final rows = await getMemberRows();
    rows.removeWhere((row) => (row['local_id'] ?? '').toString() == localId);
    await EntityCacheRepository.instance.saveList(_kMembers, rows);
  }

  Future<void> cacheReceivableSummary(
    int memberId,
    Map<String, dynamic> payload,
  ) {
    return EntityCacheRepository.instance.saveMap(_receivableKey(memberId), payload);
  }

  Future<MemberReceivableSummary?> getReceivableSummary(int memberId) async {
    final payload = await EntityCacheRepository.instance.getMap(
      _receivableKey(memberId),
    );
    if (payload.isEmpty) return null;
    return MemberReceivableSummary(
      noPiutang: (payload['no_piutang'] ?? '').toString(),
      invoiceNumber: (payload['invoice_number'] ?? '').toString(),
      tanggalTransaksiRaw: (payload['tanggal_transaksi_raw'] ?? '').toString(),
      jatuhTempoRaw: (payload['jatuh_tempo_raw'] ?? '').toString(),
      status: (payload['status'] ?? '').toString(),
      totalPiutang: _toDouble(payload['total_piutang']),
      sisaPiutang: _toDouble(payload['sisa_piutang']),
      totalLimit: _toDouble(payload['total_limit']),
      sisaLimit: _toDouble(payload['sisa_limit']),
      totalTransaksiKredit: _toInt(payload['total_transaksi_kredit']),
    );
  }

  double _toDouble(dynamic value) {
    if (value is num) return value.toDouble();
    return double.tryParse(value?.toString() ?? '') ?? 0;
  }

  int _toInt(dynamic value) {
    if (value is num) return value.toInt();
    return int.tryParse(value?.toString() ?? '') ?? 0;
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
