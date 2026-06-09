import 'dart:async';
import 'dart:convert';
import 'dart:io';

import 'package:http/http.dart' as http;

import '../../models/kendaraan_model.dart';
import '../config/api_config.dart';
import '../offline/offline_utils.dart';
import '../offline/sync_manager.dart';
import '../offline/sync_queue_repository.dart';
import '../offline/sync_types.dart';
import '../offline/vehicle_local_repository.dart';
import 'auth_service.dart';
import 'notification_refresh_helper.dart';

class VehicleService {
  static Future<List<KendaraanItem>> getVehicles({
    String search = '',
    String statusLabel = 'Semua',
    String jenisLabel = 'Semua Jenis',
    int perPage = 300,
  }) async {
    final query = <String, String>{'per_page': '$perPage'};

    final cleanSearch = search.trim();
    if (cleanSearch.isNotEmpty) query['search'] = cleanSearch;

    if (statusLabel != 'Semua') {
      query['status'] = kendaraanStatusApiFromLabel(statusLabel);
    }

    if (jenisLabel != 'Semua Jenis') {
      query['type'] = kendaraanTypeApiFromLabel(jenisLabel);
    }

    final uri = Uri.parse(
      ApiConfig.vehicleIndex,
    ).replace(queryParameters: query);

    try {
      final response = await _performRequest(
        () async => http
            .get(uri, headers: await AuthService.authHeaders())
            .timeout(const Duration(seconds: 20)),
      );

      final parsed = _decode(
        response,
        fallbackMessage: 'Gagal memuat data kendaraan',
        allowEmptyBody: false,
      );

      final list = _extractList(parsed['data']).isNotEmpty
          ? _extractList(parsed['data'])
          : _extractList(parsed);
      final rows = list.map(_asMap).where((e) => e.isNotEmpty).toList();
      await VehicleLocalRepository.instance.cacheVehicles(rows);
      return rows.map(KendaraanItem.fromJson).toList(growable: false);
    } catch (error) {
      if (!isNetworkReachabilityError(error)) rethrow;
      var local = await VehicleLocalRepository.instance.getVehicles();
      final query = cleanSearch.toLowerCase();
      if (query.isNotEmpty) {
        local = local
            .where(
              (v) =>
                  v.nama.toLowerCase().contains(query) ||
                  v.platNomor.toLowerCase().contains(query),
            )
            .toList(growable: false);
      }
      return local;
    }
  }

  static Future<KendaraanItem> getVehicleDetail({
    required int vehicleId,
  }) async {
    try {
      final response = await _performRequest(
        () async => http
            .get(
              Uri.parse(ApiConfig.vehicleDetail(vehicleId)),
              headers: await AuthService.authHeaders(),
            )
            .timeout(const Duration(seconds: 20)),
      );

      final parsed = _decode(
        response,
        fallbackMessage: 'Gagal memuat detail kendaraan',
        allowEmptyBody: false,
      );

      final data = _asMap(parsed['data']);
      if (data.isEmpty) {
        throw Exception('Data kendaraan tidak ditemukan.');
      }
      await VehicleLocalRepository.instance.upsertLocalRow({
        ...data,
        'local_id': 'srv-$vehicleId',
        'server_id': vehicleId,
        'sync_status': SyncStatus.synced.value,
      });
      return KendaraanItem.fromJson(data);
    } catch (error) {
      if (!isNetworkReachabilityError(error)) rethrow;
      final local = await VehicleLocalRepository.instance.getVehicleDetail(
        vehicleId,
      );
      if (local != null) return local;
      rethrow;
    }
  }

  static Future<KendaraanItem> createVehicle({
    required String nama,
    required String platNomor,
    required String jenisLabel,
    required String statusLabel,
    required double kapasitasBerat,
    required double kapasitasVolume,
    String tanggalMaintenance = '',
    String catatan = '',
  }) async {
    final payload = <String, dynamic>{
      'name': nama.trim(),
      'license_plate': platNomor.trim().toUpperCase(),
      'type': kendaraanTypeApiFromLabel(jenisLabel),
      'status': kendaraanStatusApiFromLabel(statusLabel),
      'capacity_weight': kapasitasBerat,
      'capacity_volume': kapasitasVolume,
      if (formatDateForApi(tanggalMaintenance) != null)
        'last_maintenance': formatDateForApi(tanggalMaintenance),
      if (catatan.trim().isNotEmpty) 'notes': catatan.trim(),
    };

    try {
      final response = await _performRequest(
        () => _sendJson(
          method: 'POST',
          uri: Uri.parse(ApiConfig.vehicleIndex),
          body: payload,
        ).timeout(const Duration(seconds: 20)),
      );

      final parsed = _decode(
        response,
        fallbackMessage: 'Gagal menambah kendaraan',
        allowEmptyBody: true,
      );

      final data = _asMap(parsed['data']);
      final row = data.isNotEmpty ? data : {'id': 0, ...payload};
      await VehicleLocalRepository.instance.upsertLocalRow({
        ...row,
        'local_id': 'srv-${row['id'] ?? DateTime.now().microsecondsSinceEpoch}',
        'server_id': row['id'],
        'sync_status': SyncStatus.synced.value,
      });
      final created = KendaraanItem.fromJson(row);
      await NotificationRefreshHelper.notifyLocalAction(
        title: 'Kendaraan berhasil ditambahkan',
        message: 'Data armada baru telah disimpan.',
        type: 'vehicle',
      );
      return created;
    } catch (error) {
      if (!isNetworkReachabilityError(error)) rethrow;
      final localId = 'loc-${DateTime.now().microsecondsSinceEpoch}';
      final row = {
        'id': generateTempId(),
        ...payload,
        'local_id': localId,
        'server_id': null,
        'sync_status': SyncStatus.pendingCreate.value,
      };
      await VehicleLocalRepository.instance.upsertLocalRow(row);
      await SyncQueueRepository.instance.enqueue(
        entityType: LocalEntityType.vehicle,
        entityLocalId: localId,
        operation: SyncOperation.create,
        payload: payload,
      );
      unawaited(SyncManager.instance.triggerSync());
      await NotificationRefreshHelper.notifyLocalAction(
        title: 'Kendaraan disimpan offline',
        message: 'Akan sinkron otomatis saat online.',
        type: 'vehicle',
        priority: 'high',
        important: true,
        enqueueSync: true,
      );
      return KendaraanItem.fromJson(row);
    }
  }

  static Future<KendaraanItem> updateVehicle({
    required int vehicleId,
    required String nama,
    required String platNomor,
    required String jenisLabel,
    required String statusLabel,
    required double kapasitasBerat,
    required double kapasitasVolume,
    String tanggalMaintenance = '',
    String catatan = '',
  }) async {
    final payload = <String, dynamic>{
      'name': nama.trim(),
      'license_plate': platNomor.trim().toUpperCase(),
      'type': kendaraanTypeApiFromLabel(jenisLabel),
      'status': kendaraanStatusApiFromLabel(statusLabel),
      'capacity_weight': kapasitasBerat,
      'capacity_volume': kapasitasVolume,
      if (formatDateForApi(tanggalMaintenance) != null)
        'last_maintenance': formatDateForApi(tanggalMaintenance),
      if (catatan.trim().isNotEmpty) 'notes': catatan.trim(),
    };

    try {
      final response = await _performRequest(
        () => _sendJson(
          method: 'PUT',
          uri: Uri.parse(ApiConfig.vehicleDetail(vehicleId)),
          body: payload,
        ).timeout(const Duration(seconds: 20)),
      );

      final parsed = _decode(
        response,
        fallbackMessage: 'Gagal memperbarui kendaraan',
        allowEmptyBody: true,
      );

      final data = _asMap(parsed['data']);
      final row = data.isNotEmpty ? data : {'id': vehicleId, ...payload};
      await VehicleLocalRepository.instance.upsertLocalRow({
        ...row,
        'local_id': 'srv-$vehicleId',
        'server_id': vehicleId,
        'sync_status': SyncStatus.synced.value,
      });
      final updated = KendaraanItem.fromJson(row);
      await NotificationRefreshHelper.notifyLocalAction(
        title: 'Kendaraan berhasil diperbarui',
        message: 'Perubahan data armada tersimpan.',
        type: 'vehicle',
      );
      return updated;
    } catch (error) {
      if (!isNetworkReachabilityError(error)) rethrow;
      final row = {
        'id': vehicleId,
        ...payload,
        'local_id': 'srv-$vehicleId',
        'server_id': vehicleId,
        'sync_status': SyncStatus.pendingUpdate.value,
      };
      await VehicleLocalRepository.instance.upsertLocalRow(row);
      await SyncQueueRepository.instance.enqueue(
        entityType: LocalEntityType.vehicle,
        entityLocalId: 'srv-$vehicleId',
        operation: SyncOperation.update,
        payload: payload,
      );
      unawaited(SyncManager.instance.triggerSync());
      await NotificationRefreshHelper.notifyLocalAction(
        title: 'Perubahan kendaraan disimpan offline',
        message: 'Akan sinkron otomatis saat online.',
        type: 'vehicle',
        priority: 'high',
        important: true,
        enqueueSync: true,
      );
      return KendaraanItem.fromJson(row);
    }
  }

  static Future<void> deleteVehicle({required int vehicleId}) async {
    try {
      final response = await _performRequest(
        () async => http
            .delete(
              Uri.parse(ApiConfig.vehicleDetail(vehicleId)),
              headers: await AuthService.authHeaders(),
            )
            .timeout(const Duration(seconds: 20)),
      );

      _decode(
        response,
        fallbackMessage: 'Gagal menghapus kendaraan',
        allowEmptyBody: true,
      );
      await VehicleLocalRepository.instance.removeByLocalId('srv-$vehicleId');
      await NotificationRefreshHelper.notifyLocalAction(
        title: 'Kendaraan berhasil dihapus',
        message: 'Data armada telah dihapus.',
        type: 'vehicle',
      );
    } catch (error) {
      if (!isNetworkReachabilityError(error)) rethrow;
      await VehicleLocalRepository.instance.removeByLocalId('srv-$vehicleId');
      await SyncQueueRepository.instance.enqueue(
        entityType: LocalEntityType.vehicle,
        entityLocalId: 'srv-$vehicleId',
        operation: SyncOperation.delete,
        payload: {'vehicle_id': vehicleId},
      );
      unawaited(SyncManager.instance.triggerSync());
      await NotificationRefreshHelper.notifyLocalAction(
        title: 'Hapus kendaraan disimpan offline',
        message: 'Akan sinkron otomatis saat online.',
        type: 'vehicle',
        priority: 'high',
        important: true,
        enqueueSync: true,
      );
    }
  }

  static Future<KendaraanItem> setMaintenance({required int vehicleId}) {
    return _setStatus(
      uri: Uri.parse(ApiConfig.vehicleSetMaintenance(vehicleId)),
      fallbackMessage: 'Gagal mengubah status kendaraan ke servis',
      fallbackData: {'id': vehicleId, 'status': 'maintenance'},
    );
  }

  static Future<KendaraanItem> setAvailable({required int vehicleId}) {
    return _setStatus(
      uri: Uri.parse(ApiConfig.vehicleSetAvailable(vehicleId)),
      fallbackMessage: 'Gagal mengubah status kendaraan ke tersedia',
      fallbackData: {'id': vehicleId, 'status': 'available'},
    );
  }

  static Future<KendaraanItem> _setStatus({
    required Uri uri,
    required String fallbackMessage,
    required Map<String, dynamic> fallbackData,
  }) async {
    try {
      final response = await _performRequest(
        () => _sendJson(
          method: 'POST',
          uri: uri,
          body: const {},
        ).timeout(const Duration(seconds: 20)),
      );

      final parsed = _decode(
        response,
        fallbackMessage: fallbackMessage,
        allowEmptyBody: true,
      );

      final data = _asMap(parsed['data']);
      final result = KendaraanItem.fromJson(
        data.isNotEmpty ? data : fallbackData,
      );
      await VehicleLocalRepository.instance.upsertLocalRow({
        ...(data.isNotEmpty ? data : fallbackData),
        'local_id': 'srv-${fallbackData['id'] ?? 0}',
        'server_id': fallbackData['id'],
        'sync_status': SyncStatus.synced.value,
      });
      await NotificationRefreshHelper.notifyLocalAction(
        title: 'Status kendaraan diperbarui',
        message: 'Perubahan status armada berhasil disimpan.',
        type: 'vehicle',
      );
      return result;
    } catch (error) {
      if (!isNetworkReachabilityError(error)) rethrow;
      final vehicleId = _toInt(fallbackData['id']);
      final status = (fallbackData['status'] ?? '').toString();
      await VehicleLocalRepository.instance.upsertLocalRow({
        ...fallbackData,
        'local_id': 'srv-$vehicleId',
        'server_id': vehicleId,
        'sync_status': SyncStatus.pendingUpdate.value,
      });
      final payload = _statusPayloadFromFallback(status);
      await SyncQueueRepository.instance.enqueue(
        entityType: LocalEntityType.vehicle,
        entityLocalId: 'srv-$vehicleId',
        operation: SyncOperation.update,
        payload: payload,
      );
      unawaited(SyncManager.instance.triggerSync());
      await NotificationRefreshHelper.notifyLocalAction(
        title: 'Status kendaraan disimpan offline',
        message: 'Akan sinkron otomatis saat online.',
        type: 'vehicle',
        priority: 'high',
        important: true,
        enqueueSync: true,
      );
      return KendaraanItem.fromJson({...fallbackData, ...payload});
    }
  }

  static Map<String, dynamic> _statusPayloadFromFallback(String statusApiOrLabel) {
    final statusApi = statusApiOrLabel.contains('_')
        ? statusApiOrLabel
        : kendaraanStatusApiFromLabel(kendaraanStatusLabelFromApi(statusApiOrLabel));
    return {'status': statusApi};
  }

  static int _toInt(dynamic value) {
    if (value is int) return value;
    if (value is num) return value.toInt();
    return int.tryParse(value?.toString() ?? '') ?? 0;
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
      for (final key in ['data', 'items', 'vehicles', 'results']) {
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
