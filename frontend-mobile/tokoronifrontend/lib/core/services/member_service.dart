import 'dart:async';
import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;
import '../config/api_config.dart';
import '../offline/member_local_repository.dart';
import '../offline/offline_utils.dart';
import '../offline/sync_manager.dart';
import '../offline/sync_queue_repository.dart';
import '../offline/sync_types.dart';
import 'auth_service.dart';
import 'notification_refresh_helper.dart';

class MemberRecord {
  final int id;
  final String kodeMember;
  final String nama;
  final String email;
  final String noTelepon;
  final String alamat;
  final String tipeMember;
  final double limitKredit;
  final double totalPiutang;
  final bool isActive;
  final String tanggalRegistrasiRaw;
  final String createdAtRaw;
  final String updatedAtRaw;

  const MemberRecord({
    required this.id,
    required this.kodeMember,
    required this.nama,
    required this.email,
    required this.noTelepon,
    required this.alamat,
    required this.tipeMember,
    required this.limitKredit,
    required this.totalPiutang,
    required this.isActive,
    required this.tanggalRegistrasiRaw,
    required this.createdAtRaw,
    required this.updatedAtRaw,
  });

  factory MemberRecord.fromJson(Map<String, dynamic> json) {
    return MemberRecord(
      id: _toInt(json['id']),
      kodeMember: (json['kode_member'] ?? json['kode'] ?? '').toString().trim(),
      nama: (json['nama'] ?? json['name'] ?? '').toString().trim(),
      email: (json['email'] ?? '').toString().trim(),
      noTelepon: (json['no_telepon'] ?? json['phone'] ?? '').toString().trim(),
      alamat: (json['alamat'] ?? json['address'] ?? '').toString().trim(),
      tipeMember: (json['tipe_member'] ?? json['member_type'] ?? '')
          .toString()
          .trim(),
      limitKredit: _toDouble(json['limit_kredit']),
      totalPiutang: _toDouble(json['total_piutang']),
      isActive: _toBool(json['is_active'], defaultValue: true),
      tanggalRegistrasiRaw: (json['tanggal_registrasi'] ?? '')
          .toString()
          .trim(),
      createdAtRaw: (json['created_at'] ?? '').toString().trim(),
      updatedAtRaw: (json['updated_at'] ?? '').toString().trim(),
    );
  }
}

class MemberReceivableSummary {
  final String noPiutang;
  final String invoiceNumber;
  final String tanggalTransaksiRaw;
  final String jatuhTempoRaw;
  final String status;
  final double totalPiutang;
  final double sisaPiutang;
  final double totalLimit;
  final double sisaLimit;
  final int totalTransaksiKredit;

  const MemberReceivableSummary({
    required this.noPiutang,
    required this.invoiceNumber,
    required this.tanggalTransaksiRaw,
    required this.jatuhTempoRaw,
    required this.status,
    required this.totalPiutang,
    required this.sisaPiutang,
    required this.totalLimit,
    required this.sisaLimit,
    required this.totalTransaksiKredit,
  });

  bool get hasData =>
      noPiutang.isNotEmpty ||
      invoiceNumber.isNotEmpty ||
      totalPiutang > 0 ||
      totalTransaksiKredit > 0;
}

class MemberService {
  static Future<List<MemberRecord>> getMembers({
    String search = '',
    String status = 'Semua Status',
    String tipe = 'Semua Tipe',
    int perPage = 200,
  }) async {
    final query = <String, String>{'per_page': '$perPage'};

    final cleanSearch = search.trim();
    if (cleanSearch.isNotEmpty) query['search'] = cleanSearch;

    final statusApi = _statusApiFromLabel(status);
    if (statusApi.isNotEmpty) query['status'] = statusApi;

    final tipeApi = _tipeMemberApiFromLabel(tipe);
    if (tipeApi.isNotEmpty) query['tipe'] = tipeApi;

    final uri = Uri.parse(
      ApiConfig.memberIndex,
    ).replace(queryParameters: query);

    try {
      final response = await _performRequest(
        () async => http
            .get(uri, headers: await AuthService.authHeaders())
            .timeout(const Duration(seconds: 20)),
      );

      final json = _decode(
        response,
        fallbackMessage: 'Gagal memuat data member',
        allowEmptyBody: false,
      );

      final list = _extractList(json['data']).isNotEmpty
          ? _extractList(json['data'])
          : _extractList(json);
      final rows = list.map(_asMap).where((e) => e.isNotEmpty).toList();
      await MemberLocalRepository.instance.cacheMembers(rows);
      return rows.map(MemberRecord.fromJson).toList(growable: false);
    } catch (error) {
      if (!isNetworkReachabilityError(error)) rethrow;
      var local = await MemberLocalRepository.instance.getMembers();
      final q = cleanSearch.toLowerCase();
      if (q.isNotEmpty) {
        local = local
            .where(
              (m) =>
                  m.nama.toLowerCase().contains(q) ||
                  m.kodeMember.toLowerCase().contains(q) ||
                  m.email.toLowerCase().contains(q),
            )
            .toList(growable: false);
      }
      return local;
    }
  }

  static Future<MemberRecord> createMember({
    required String nama,
    required String tipeMember,
    required double limitKredit,
    required bool isActive,
    String email = '',
    String noTelepon = '',
    String alamat = '',
  }) async {
    final payload = <String, dynamic>{
      'nama': nama.trim(),
      'tipe_member': tipeMember.trim(),
      'limit_kredit': limitKredit,
      'is_active': isActive ? 1 : 0,
    };

    if (email.trim().isNotEmpty) payload['email'] = email.trim();
    if (noTelepon.trim().isNotEmpty) payload['no_telepon'] = noTelepon.trim();
    if (alamat.trim().isNotEmpty) payload['alamat'] = alamat.trim();

    try {
      final response = await _performRequest(
        () => _sendJson(
          method: 'POST',
          uri: Uri.parse(ApiConfig.memberIndex),
          body: payload,
        ).timeout(const Duration(seconds: 20)),
      );

      final parsed = _decode(
        response,
        fallbackMessage: 'Gagal menambah member',
        allowEmptyBody: true,
      );
      final data = _asMap(parsed['data']);
      final row = data.isNotEmpty
          ? data
          : {
              'id': 0,
              'kode_member': '',
              'nama': payload['nama'],
              'email': payload['email'] ?? '',
              'no_telepon': payload['no_telepon'] ?? '',
              'alamat': payload['alamat'] ?? '',
              'tipe_member': payload['tipe_member'],
              'limit_kredit': payload['limit_kredit'],
              'total_piutang': 0,
              'is_active': payload['is_active'],
              'tanggal_registrasi': DateTime.now().toIso8601String(),
              'created_at': DateTime.now().toIso8601String(),
              'updated_at': DateTime.now().toIso8601String(),
            };
      await MemberLocalRepository.instance.upsertLocalRow({
        ...row,
        'local_id': 'srv-${row['id'] ?? DateTime.now().microsecondsSinceEpoch}',
        'server_id': row['id'],
        'sync_status': SyncStatus.synced.value,
      });
      final created = MemberRecord.fromJson(row);
      await NotificationRefreshHelper.notifyLocalAction(
        title: 'Member berhasil ditambahkan',
        message: 'Data member baru telah disimpan.',
        type: 'member',
      );
      return created;
    } catch (error) {
      if (!isNetworkReachabilityError(error)) rethrow;
      final localId = 'loc-${DateTime.now().microsecondsSinceEpoch}';
      final row = {
        'id': generateTempId(),
        'local_id': localId,
        'server_id': null,
        'kode_member': 'OFF-${DateTime.now().millisecondsSinceEpoch}',
        'nama': payload['nama'],
        'email': payload['email'] ?? '',
        'no_telepon': payload['no_telepon'] ?? '',
        'alamat': payload['alamat'] ?? '',
        'tipe_member': payload['tipe_member'],
        'limit_kredit': payload['limit_kredit'],
        'total_piutang': 0,
        'is_active': payload['is_active'],
        'tanggal_registrasi': DateTime.now().toIso8601String(),
        'created_at': DateTime.now().toIso8601String(),
        'updated_at': DateTime.now().toIso8601String(),
        'sync_status': SyncStatus.pendingCreate.value,
      };
      await MemberLocalRepository.instance.upsertLocalRow(row);
      await SyncQueueRepository.instance.enqueue(
        entityType: LocalEntityType.member,
        entityLocalId: localId,
        operation: SyncOperation.create,
        payload: payload,
      );
      unawaited(SyncManager.instance.triggerSync());
      await NotificationRefreshHelper.notifyLocalAction(
        title: 'Member disimpan offline',
        message: 'Akan sinkron otomatis saat online.',
        type: 'member',
        priority: 'high',
        important: true,
        enqueueSync: true,
      );
      return MemberRecord.fromJson(row);
    }
  }

  static Future<MemberRecord> updateMember({
    required int memberId,
    required String nama,
    required String tipeMember,
    required double limitKredit,
    required bool isActive,
    String email = '',
    String noTelepon = '',
    String alamat = '',
  }) async {
    final payload = <String, dynamic>{
      'nama': nama.trim(),
      'tipe_member': tipeMember.trim(),
      'limit_kredit': limitKredit,
      'is_active': isActive ? 1 : 0,
      'email': email.trim(),
      'no_telepon': noTelepon.trim(),
      'alamat': alamat.trim(),
    };

    try {
      final response = await _performRequest(
        () => _sendJson(
          method: 'PUT',
          uri: Uri.parse(ApiConfig.memberDetail(memberId)),
          body: payload,
        ).timeout(const Duration(seconds: 20)),
      );

      final parsed = _decode(
        response,
        fallbackMessage: 'Gagal memperbarui member',
        allowEmptyBody: true,
      );
      final data = _asMap(parsed['data']);
      final row = data.isNotEmpty
          ? data
          : {
              'id': memberId,
              'kode_member': '',
              'nama': payload['nama'],
              'email': payload['email'],
              'no_telepon': payload['no_telepon'],
              'alamat': payload['alamat'],
              'tipe_member': payload['tipe_member'],
              'limit_kredit': payload['limit_kredit'],
              'total_piutang': 0,
              'is_active': payload['is_active'],
              'created_at': DateTime.now().toIso8601String(),
              'updated_at': DateTime.now().toIso8601String(),
            };
      await MemberLocalRepository.instance.upsertLocalRow({
        ...row,
        'local_id': 'srv-$memberId',
        'server_id': memberId,
        'sync_status': SyncStatus.synced.value,
      });
      final updated = MemberRecord.fromJson(row);
      await NotificationRefreshHelper.notifyLocalAction(
        title: 'Member berhasil diperbarui',
        message: 'Perubahan data member telah disimpan.',
        type: 'member',
      );
      return updated;
    } catch (error) {
      if (!isNetworkReachabilityError(error)) rethrow;
      final row = {
        'id': memberId,
        'local_id': 'srv-$memberId',
        'server_id': memberId,
        'nama': payload['nama'],
        'email': payload['email'],
        'no_telepon': payload['no_telepon'],
        'alamat': payload['alamat'],
        'tipe_member': payload['tipe_member'],
        'limit_kredit': payload['limit_kredit'],
        'is_active': payload['is_active'],
        'updated_at': DateTime.now().toIso8601String(),
        'sync_status': SyncStatus.pendingUpdate.value,
      };
      await MemberLocalRepository.instance.upsertLocalRow(row);
      await SyncQueueRepository.instance.enqueue(
        entityType: LocalEntityType.member,
        entityLocalId: 'srv-$memberId',
        operation: SyncOperation.update,
        payload: payload,
      );
      unawaited(SyncManager.instance.triggerSync());
      await NotificationRefreshHelper.notifyLocalAction(
        title: 'Perubahan member disimpan offline',
        message: 'Akan sinkron otomatis saat online.',
        type: 'member',
        priority: 'high',
        important: true,
        enqueueSync: true,
      );
      return MemberRecord.fromJson(row);
    }
  }

  static Future<bool> toggleMemberStatus({required int memberId}) async {
    try {
      final response = await _performRequest(
        () => _sendJson(
          method: 'POST',
          uri: Uri.parse(ApiConfig.memberToggleStatus(memberId)),
          body: const {},
        ).timeout(const Duration(seconds: 20)),
      );

      final parsed = _decode(
        response,
        fallbackMessage: 'Gagal mengubah status member',
        allowEmptyBody: true,
      );
      final data = _asMap(parsed['data']);

      if (data.containsKey('is_active')) {
        final value = _toBool(data['is_active'], defaultValue: false);
        await NotificationRefreshHelper.notifyLocalAction(
          title: 'Status member diperbarui',
          message: 'Status member berhasil diubah.',
          type: 'member',
        );
        return value;
      }

      final message = (parsed['message'] ?? '').toString().toLowerCase();
      if (message.contains('diaktifkan')) {
        await NotificationRefreshHelper.notifyLocalAction(
          title: 'Status member diperbarui',
          message: 'Member berhasil diaktifkan.',
          type: 'member',
        );
        return true;
      }
      if (message.contains('dinonaktifkan')) {
        await NotificationRefreshHelper.notifyLocalAction(
          title: 'Status member diperbarui',
          message: 'Member berhasil dinonaktifkan.',
          type: 'member',
        );
        return false;
      }
      throw Exception(
        'Status member tidak dapat dipastikan dari respons server.',
      );
    } catch (error) {
      if (!isNetworkReachabilityError(error)) rethrow;
      final members = await MemberLocalRepository.instance.getMemberRows();
      final idx = members.indexWhere((m) => _toInt(m['id']) == memberId);
      if (idx < 0) {
        throw Exception('Data member lokal tidak ditemukan.');
      }
      final current = _toBool(members[idx]['is_active'], defaultValue: true);
      members[idx] = {
        ...members[idx],
        'is_active': current ? 0 : 1,
        'sync_status': SyncStatus.pendingUpdate.value,
        'updated_at': DateTime.now().toIso8601String(),
      };
      await MemberLocalRepository.instance.upsertLocalRow(members[idx]);
      await SyncQueueRepository.instance.enqueue(
        entityType: LocalEntityType.member,
        entityLocalId: (members[idx]['local_id'] ?? 'srv-$memberId').toString(),
        operation: SyncOperation.update,
        payload: {'action': 'toggle_status', 'member_id': memberId},
      );
      unawaited(SyncManager.instance.triggerSync());
      await NotificationRefreshHelper.notifyLocalAction(
        title: 'Status member disimpan offline',
        message: 'Perubahan akan sinkron otomatis saat online.',
        type: 'member',
        priority: 'high',
        important: true,
        enqueueSync: true,
      );
      return !current;
    }
  }

  static Future<MemberReceivableSummary> getMemberReceivableSummary({
    required int memberId,
  }) async {
    final uri = Uri.parse(
      ApiConfig.memberReceivables(memberId),
    ).replace(queryParameters: const {'per_page': '1'});

    try {
      final response = await _performRequest(
        () async => http
            .get(uri, headers: await AuthService.authHeaders())
            .timeout(const Duration(seconds: 20)),
      );

      final parsed = _decode(
        response,
        fallbackMessage: 'Gagal memuat detail piutang member',
        allowEmptyBody: false,
      );

      final data = _asMap(parsed['data']);
      final stats = _asMap(data['stats']);
      final receivables = _extractList(data['receivables']);
      final latest = receivables.isNotEmpty
          ? _asMap(receivables.first)
          : <String, dynamic>{};

      final summary = MemberReceivableSummary(
        noPiutang: (latest['no_piutang'] ?? '').toString().trim(),
        invoiceNumber: (latest['invoice_number'] ?? '').toString().trim(),
        tanggalTransaksiRaw:
            (latest['tanggal_transaksi'] ?? latest['created_at'] ?? '')
                .toString()
                .trim(),
        jatuhTempoRaw: (latest['jatuh_tempo'] ?? '').toString().trim(),
        status: (latest['status'] ?? '').toString().trim(),
        totalPiutang: _toDouble(
          latest['total_piutang'] ?? stats['total_piutang'],
        ),
        sisaPiutang: _toDouble(
          latest['sisa_piutang'] ?? stats['total_piutang'],
        ),
        totalLimit: _toDouble(stats['limit_kredit']),
        sisaLimit: _toDouble(stats['sisa_limit']),
        totalTransaksiKredit: _toInt(stats['jumlah_piutang']),
      );
      await MemberLocalRepository.instance.cacheReceivableSummary(memberId, {
        'no_piutang': summary.noPiutang,
        'invoice_number': summary.invoiceNumber,
        'tanggal_transaksi_raw': summary.tanggalTransaksiRaw,
        'jatuh_tempo_raw': summary.jatuhTempoRaw,
        'status': summary.status,
        'total_piutang': summary.totalPiutang,
        'sisa_piutang': summary.sisaPiutang,
        'total_limit': summary.totalLimit,
        'sisa_limit': summary.sisaLimit,
        'total_transaksi_kredit': summary.totalTransaksiKredit,
      });
      return summary;
    } catch (error) {
      if (!isNetworkReachabilityError(error)) rethrow;
      final cached = await MemberLocalRepository.instance.getReceivableSummary(
        memberId,
      );
      if (cached != null) return cached;
      rethrow;
    }
  }

  static String _statusApiFromLabel(String label) {
    switch (label.trim().toLowerCase()) {
      case 'aktif':
        return 'active';
      case 'nonaktif':
        return 'inactive';
      default:
        return '';
    }
  }

  static String _tipeMemberApiFromLabel(String label) {
    switch (label.trim().toLowerCase()) {
      case 'biasa':
        return 'biasa';
      case 'gold':
        return 'gold';
      case 'platinum':
        return 'platinum';
      default:
        return '';
    }
  }

  static Future<http.Response> _sendJson({
    required String method,
    required Uri uri,
    required Map<String, dynamic> body,
  }) async {
    final headers = await AuthService.authHeaders();
    final req = http.Request(method, uri)
      ..headers.addAll(headers)
      ..body = jsonEncode(body);
    final streamed = await req.send();
    return http.Response.fromStream(streamed);
  }

  static Future<http.Response> _performRequest(
    Future<http.Response> Function() request,
  ) async {
    try {
      return await request();
    } on TimeoutException {
      throw Exception(
        'Koneksi ke server timeout. Periksa koneksi internet lalu coba lagi.',
      );
    } on SocketException {
      throw Exception(
        'Tidak ada koneksi internet atau server tidak dapat dijangkau.',
      );
    } on http.ClientException {
      throw Exception(
        'Tidak dapat terhubung ke server. Periksa koneksi internet Anda.',
      );
    }
  }

  static Map<String, dynamic> _decode(
    http.Response response, {
    required String fallbackMessage,
    required bool allowEmptyBody,
  }) {
    final body = _safeDecodeBody(response.body);

    if (response.statusCode == 401) {
      throw Exception('Sesi login habis, silakan login ulang');
    }

    if (response.statusCode < 200 || response.statusCode >= 300) {
      throw Exception(
        _buildErrorMessage(body, fallbackMessage, response.statusCode),
      );
    }

    if (body.isEmpty && !allowEmptyBody) {
      throw Exception(fallbackMessage);
    }

    final success = body['success'];
    if (success is bool && !success) {
      throw Exception(
        _buildErrorMessage(body, fallbackMessage, response.statusCode),
      );
    }

    return body;
  }

  static Map<String, dynamic> _safeDecodeBody(String raw) {
    try {
      final decoded = jsonDecode(raw);
      if (decoded is Map<String, dynamic>) return decoded;
      if (decoded is List) return {'data': decoded};
    } catch (_) {}
    return {};
  }

  static String _buildErrorMessage(
    Map<String, dynamic> body,
    String fallbackMessage,
    int statusCode,
  ) {
    final message = body['message']?.toString() ?? '';
    final error = body['error']?.toString() ?? '';
    final errors = body['errors'];

    final validationMessages = <String>[];
    if (errors is Map) {
      for (final value in errors.values) {
        if (value is List && value.isNotEmpty) {
          validationMessages.add(value.first.toString());
        } else if (value != null) {
          validationMessages.add(value.toString());
        }
      }
    }

    final sqlRaw = '$message $error'.toLowerCase();
    if (sqlRaw.contains('sqlstate') || sqlRaw.contains('unknown column')) {
      return 'Terjadi kesalahan database di server. Silakan hubungi administrator atau coba lagi nanti.';
    }

    final details = validationMessages.join(' | ').trim();
    if (details.isNotEmpty && message.isNotEmpty) return '$message: $details';
    if (details.isNotEmpty) return details;
    if (error.isNotEmpty) return error;
    if (message.isNotEmpty) return message;
    return '$fallbackMessage (HTTP $statusCode)';
  }

  static List<dynamic> _extractList(dynamic raw) {
    if (raw is List) return raw;
    if (raw is Map<String, dynamic>) {
      for (final key in [
        'data',
        'items',
        'members',
        'receivables',
        'results',
      ]) {
        final nested = raw[key];
        if (nested is List) return nested;
      }
    }
    return const [];
  }

  static Map<String, dynamic> _asMap(dynamic raw) {
    if (raw is Map<String, dynamic>) return raw;
    if (raw is Map) {
      return raw.map((k, v) => MapEntry(k.toString(), v));
    }
    return {};
  }
}

int _toInt(dynamic value) {
  if (value == null) return 0;
  if (value is int) return value;
  if (value is num) return value.toInt();
  return int.tryParse(value.toString()) ?? 0;
}

double _toDouble(dynamic value) {
  if (value == null) return 0;
  if (value is double) return value;
  if (value is num) return value.toDouble();
  return double.tryParse(value.toString()) ?? 0;
}

bool _toBool(dynamic value, {required bool defaultValue}) {
  if (value == null) return defaultValue;
  if (value is bool) return value;
  if (value is num) return value != 0;
  final raw = value.toString().toLowerCase().trim();
  if (raw == 'true' || raw == '1' || raw == 'yes' || raw == 'aktif') {
    return true;
  }
  if (raw == 'false' || raw == '0' || raw == 'no' || raw == 'nonaktif') {
    return false;
  }
  return defaultValue;
}
