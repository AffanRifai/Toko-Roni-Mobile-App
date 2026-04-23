import 'dart:async';
import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;
import '../../models/transaction_api_model.dart';
import '../config/api_config.dart';
import 'auth_service.dart';
import 'notification_refresh_helper.dart';

class TransactionService {
  static Future<List<TransactionApiItem>> getTransactions({
    int perPage = 200,
    String search = '',
  }) async {
    final query = <String, String>{'per_page': '$perPage'};
    if (search.trim().isNotEmpty) query['search'] = search.trim();

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
      fallbackMessage: 'Gagal memuat riwayat transaksi',
      allowEmptyBody: false,
    );

    final data = parsed['data'];
    final list = _extractList(data).isNotEmpty
        ? _extractList(data)
        : _extractList(parsed);

    return list
        .map(_asMap)
        .where((e) => e.isNotEmpty)
        .map(TransactionApiItem.fromJson)
        .toList();
  }

  static Future<TransactionApiItem> getTransactionDetail({
    required int transactionId,
  }) async {
    final response = await _performRequest(
      () async => http
          .get(
            Uri.parse(ApiConfig.transactionDetail(transactionId)),
            headers: await AuthService.authHeaders(),
          )
          .timeout(const Duration(seconds: 20)),
    );

    final parsed = _decode(
      response,
      fallbackMessage: 'Gagal memuat detail transaksi',
      allowEmptyBody: false,
    );

    final data = _asMap(parsed['data']);
    if (data.isEmpty) {
      throw Exception('Data detail transaksi tidak ditemukan.');
    }

    return TransactionApiItem.fromJson(data);
  }

  static Future<TransactionApiItem> createTransaction({
    required CreateTransactionPayload payload,
  }) async {
    final response = await _performRequest(
      () => _sendJson(
        method: 'POST',
        uri: Uri.parse(ApiConfig.transactionIndex),
        body: payload.toJson(),
      ).timeout(const Duration(seconds: 25)),
    );

    final parsed = _decode(
      response,
      fallbackMessage: 'Gagal memproses transaksi',
      allowEmptyBody: false,
    );

    final data = _asMap(parsed['data']);
    if (data.isEmpty) {
      throw Exception('Respons transaksi dari server kosong.');
    }

    final created = TransactionApiItem.fromJson(data);
    await NotificationRefreshHelper.refreshSafely();
    return created;
  }

  static Future<void> deleteTransaction({required int transactionId}) async {
    final response = await _performRequest(
      () async => http
          .delete(
            Uri.parse(ApiConfig.transactionDetail(transactionId)),
            headers: await AuthService.authHeaders(),
          )
          .timeout(const Duration(seconds: 20)),
    );

    _decode(
      response,
      fallbackMessage: 'Gagal menghapus transaksi',
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
      for (final key in ['data', 'items', 'transactions', 'results']) {
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
