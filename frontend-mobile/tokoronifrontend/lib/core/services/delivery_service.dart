import 'dart:async';
import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;
import '../../models/pengiriman_model.dart';
import '../config/api_config.dart';
import 'auth_service.dart';
import 'notification_refresh_helper.dart';

class DeliveryService {
  static Future<List<PengirimanItem>> getDeliveries({
    int perPage = 200,
    int? transactionId,
  }) async {
    final query = <String, String>{'per_page': '$perPage'};
    if ((transactionId ?? 0) > 0) {
      query['transaction_id'] = '$transactionId';
    }

    final uri = Uri.parse(
      ApiConfig.deliveryIndex,
    ).replace(queryParameters: query);

    final response = await _performRequest(
      () async => http
          .get(uri, headers: await AuthService.authHeaders())
          .timeout(const Duration(seconds: 20)),
    );

    final parsed = _decode(
      response,
      fallbackMessage: 'Gagal memuat data pengiriman',
      allowEmptyBody: false,
    );

    final list = _extractList(parsed['data']).isNotEmpty
        ? _extractList(parsed['data'])
        : _extractList(parsed);

    return list
        .map(_asMap)
        .where((e) => e.isNotEmpty)
        .map(PengirimanItem.fromJson)
        .toList();
  }

  static Future<List<PengirimanItem>> getMyDeliveries({
    int perPage = 300,
  }) async {
    final uri = Uri.parse(
      ApiConfig.deliveryMyDeliveries,
    ).replace(queryParameters: {'per_page': '$perPage'});

    final response = await _performRequest(
      () async => http
          .get(uri, headers: await AuthService.authHeaders())
          .timeout(const Duration(seconds: 20)),
    );

    final parsed = _decode(
      response,
      fallbackMessage: 'Gagal memuat pengiriman saya',
      allowEmptyBody: false,
    );

    final list = _extractList(parsed['data']).isNotEmpty
        ? _extractList(parsed['data'])
        : _extractList(parsed);

    final basicItems = list
        .map(_asMap)
        .where((e) => e.isNotEmpty)
        .map(PengirimanItem.fromJson)
        .toList();

    if (basicItems.isEmpty) return basicItems;

    final shouldEnrich = basicItems.any(
      (item) =>
          _isMissingText(item.invoice) ||
          _isMissingText(item.namaKurir) ||
          _isMissingText(item.tujuan) ||
          _isMissingText(item.namaCustomer),
    );
    if (!shouldEnrich) return basicItems;

    try {
      final richItems = await getDeliveries(perPage: perPage);
      if (richItems.isEmpty) return basicItems;

      final richById = {for (final item in richItems) item.id: item};
      return basicItems.map((item) {
        final rich = richById[item.id];
        if (rich == null) return item;
        return item.copyWith(
          invoice: _pickString(item.invoice, rich.invoice),
          tujuan: _pickString(item.tujuan, rich.tujuan),
          asal: _pickString(item.asal, rich.asal),
          namaCustomer: _pickString(item.namaCustomer, rich.namaCustomer),
          totalBelanja: item.totalBelanja > 0
              ? item.totalBelanja
              : rich.totalBelanja,
          totalItem: item.totalItem > 0 ? item.totalItem : rich.totalItem,
          kurirId: item.kurirId ?? rich.kurirId,
          kendaraanId: item.kendaraanId ?? rich.kendaraanId,
          namaKurir: _pickNullableString(item.namaKurir, rich.namaKurir),
          nomorKurir: _pickNullableString(item.nomorKurir, rich.nomorKurir),
          kendaraan: _pickNullableString(item.kendaraan, rich.kendaraan),
          estimatedDeliveryRaw: _pickString(
            item.estimatedDeliveryRaw,
            rich.estimatedDeliveryRaw,
          ),
          deliveredAtRaw: _pickString(item.deliveredAtRaw, rich.deliveredAtRaw),
        );
      }).toList();
    } catch (_) {
      return basicItems;
    }
  }

  static Future<PengirimanItem?> getDeliveryByTransactionId({
    required int transactionId,
  }) async {
    if (transactionId <= 0) return null;
    final list = await getDeliveries(perPage: 1, transactionId: transactionId);
    if (list.isEmpty) return null;
    return list.first;
  }

  static Future<PengirimanItem> getDeliveryDetail({
    required int deliveryId,
  }) async {
    final response = await _performRequest(
      () async => http
          .get(
            Uri.parse(ApiConfig.deliveryDetail(deliveryId)),
            headers: await AuthService.authHeaders(),
          )
          .timeout(const Duration(seconds: 20)),
    );

    final parsed = _decode(
      response,
      fallbackMessage: 'Gagal memuat detail pengiriman',
      allowEmptyBody: false,
    );
    final data = _asMap(parsed['data']);
    if (data.isEmpty) {
      throw Exception('Data detail pengiriman tidak ditemukan.');
    }
    return PengirimanItem.fromJson(data);
  }

  static Future<List<DeliveryDriverOption>> getAvailableDrivers() async {
    final response = await _performRequest(
      () async => http
          .get(
            Uri.parse(ApiConfig.deliveryAvailableDrivers),
            headers: await AuthService.authHeaders(),
          )
          .timeout(const Duration(seconds: 20)),
    );

    final parsed = _decode(
      response,
      fallbackMessage: 'Gagal memuat daftar kurir',
      allowEmptyBody: false,
    );
    final list = _extractList(parsed['data']).isNotEmpty
        ? _extractList(parsed['data'])
        : _extractList(parsed);
    return list
        .map(_asMap)
        .where((e) => e.isNotEmpty)
        .map(DeliveryDriverOption.fromJson)
        .toList();
  }

  static Future<List<DeliveryVehicleOption>> getAvailableVehicles() async {
    final response = await _performRequest(
      () async => http
          .get(
            Uri.parse(ApiConfig.deliveryAvailableVehicles),
            headers: await AuthService.authHeaders(),
          )
          .timeout(const Duration(seconds: 20)),
    );

    final parsed = _decode(
      response,
      fallbackMessage: 'Gagal memuat daftar kendaraan',
      allowEmptyBody: false,
    );
    final list = _extractList(parsed['data']).isNotEmpty
        ? _extractList(parsed['data'])
        : _extractList(parsed);
    return list
        .map(_asMap)
        .where((e) => e.isNotEmpty)
        .map(DeliveryVehicleOption.fromJson)
        .toList();
  }

  static Future<List<DeliveryInvoiceOption>> searchTransactions({
    required String search,
    int perPage = 20,
  }) async {
    final query = <String, String>{
      'per_page': '$perPage',
      if (search.trim().isNotEmpty) 'search': search.trim(),
    };
    final uri = Uri.parse(
      ApiConfig.transactionIndex,
    ).replace(queryParameters: query);

    final response = await _performRequest(
      () async => http
          .get(uri, headers: await AuthService.authHeaders())
          .timeout(const Duration(seconds: 20)),
    );

    final parsed = _decode(
      response,
      fallbackMessage: 'Gagal memuat daftar transaksi',
      allowEmptyBody: false,
    );

    final list = _extractList(parsed['data']).isNotEmpty
        ? _extractList(parsed['data'])
        : _extractList(parsed);

    return list
        .map(_asMap)
        .where((e) => e.isNotEmpty)
        .map(DeliveryInvoiceOption.fromJson)
        .toList();
  }

  static Future<PengirimanItem> createDelivery({
    required int transactionId,
    required String origin,
    required String destination,
    required int totalItems,
    required String statusApi,
    double? totalWeight,
    double? totalVolume,
    DateTime? estimatedDeliveryTime,
    String? notes,
  }) async {
    final payload = <String, dynamic>{
      'transaction_id': transactionId,
      'origin': origin.trim(),
      'destination': destination.trim(),
      'total_items': totalItems <= 0 ? 1 : totalItems,
      'status': statusApi.trim().isEmpty ? 'pending' : statusApi.trim(),
      if (totalWeight != null) 'total_weight': totalWeight,
      if (totalVolume != null) 'total_volume': totalVolume,
      if (estimatedDeliveryTime != null)
        'estimated_delivery_time': estimatedDeliveryTime
            .toUtc()
            .toIso8601String(),
      if ((notes ?? '').trim().isNotEmpty) 'notes': notes!.trim(),
    };

    final response = await _performRequest(
      () => _sendJson(
        method: 'POST',
        uri: Uri.parse(ApiConfig.deliveryIndex),
        body: payload,
      ).timeout(const Duration(seconds: 25)),
    );

    final parsed = _decode(
      response,
      fallbackMessage: 'Gagal membuat pengiriman',
      allowEmptyBody: false,
    );
    final data = _asMap(parsed['data']);
    if (data.isEmpty) {
      final created = PengirimanItem(
        id: 0,
        transactionId: transactionId,
        kodePengiriman: '',
        invoice: '',
        tujuan: payload['destination'].toString(),
        asal: payload['origin'].toString(),
        createdAt: DateTime.now(),
        tanggalDibuat:
            '${DateTime.now().day.toString().padLeft(2, '0')}/${DateTime.now().month.toString().padLeft(2, '0')}/${DateTime.now().year}',
        jamDibuat:
            '${DateTime.now().hour.toString().padLeft(2, '0')}:${DateTime.now().minute.toString().padLeft(2, '0')}',
        namaCustomer: 'Pelanggan Umum',
        totalBelanja: 0,
        totalItem: payload['total_items'] as int,
        status: deliveryStatusLabelFromApi(payload['status'].toString()),
        statusApi: payload['status'].toString(),
        catatan: (payload['notes'] ?? '').toString(),
        estimatedDeliveryRaw: (payload['estimated_delivery_time'] ?? '')
            .toString(),
        deliveredAtRaw: '',
      );
      await NotificationRefreshHelper.refreshSafely();
      return created;
    }

    final created = PengirimanItem.fromJson(data);
    await NotificationRefreshHelper.refreshSafely();
    return created;
  }

  static Future<void> assignDelivery({
    required int deliveryId,
    required int userId,
    int? vehicleId,
  }) async {
    final payloads = <Map<String, dynamic>>[
      {'user_id': userId, if (vehicleId != null) 'vehicle_id': vehicleId},
      {'driver_id': userId, if (vehicleId != null) 'vehicle_id': vehicleId},
    ];

    Exception? lastError;
    for (final payload in payloads) {
      final response = await _performRequest(
        () => _sendJson(
          method: 'POST',
          uri: Uri.parse(ApiConfig.deliveryAssign(deliveryId)),
          body: payload,
        ).timeout(const Duration(seconds: 20)),
      );

      if (response.statusCode >= 200 && response.statusCode < 300) {
        _decode(
          response,
          fallbackMessage: 'Gagal assign kurir',
          allowEmptyBody: true,
        );
        await NotificationRefreshHelper.refreshSafely();
        return;
      }

      try {
        _decode(
          response,
          fallbackMessage: 'Gagal assign kurir',
          allowEmptyBody: true,
        );
      } catch (e) {
        lastError = e is Exception ? e : Exception(e.toString());
      }
    }

    throw lastError ?? Exception('Gagal assign kurir');
  }

  static Future<void> updateStatus({
    required int deliveryId,
    required String statusApi,
  }) async {
    final response = await _performRequest(
      () => _sendJson(
        method: 'PUT',
        uri: Uri.parse(ApiConfig.deliveryUpdateStatus(deliveryId)),
        body: {'status': statusApi.trim()},
      ).timeout(const Duration(seconds: 20)),
    );

    _decode(
      response,
      fallbackMessage: 'Gagal memperbarui status pengiriman',
      allowEmptyBody: true,
    );
    await NotificationRefreshHelper.refreshSafely();
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
        'deliveries',
        'transactions',
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

  static bool _isMissingText(String? value) {
    final text = (value ?? '').trim();
    if (text.isEmpty) return true;
    return text == '-' || text.toLowerCase() == 'null';
  }

  static String _pickString(String current, String fallback) {
    return _isMissingText(current) ? fallback : current;
  }

  static String? _pickNullableString(String? current, String? fallback) {
    return _isMissingText(current) ? fallback : current;
  }
}
