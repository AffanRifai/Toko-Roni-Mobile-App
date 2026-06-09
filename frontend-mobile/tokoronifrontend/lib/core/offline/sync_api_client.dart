import 'dart:async';
import 'dart:convert';
import 'dart:io';

import 'package:http/http.dart' as http;

import '../config/api_config.dart';
import '../services/auth_service.dart';

class SyncApiClient {
  SyncApiClient._();
  static final SyncApiClient instance = SyncApiClient._();

  Future<bool> isBackendReachable() async {
    try {
      final response = await http
          .get(Uri.parse(ApiConfig.baseUrl))
          .timeout(const Duration(seconds: 5));
      return response.statusCode > 0;
    } catch (_) {
      return false;
    }
  }

  Future<Map<String, dynamic>> createProduct(
    Map<String, dynamic> payload,
  ) async {
    return _mutate(
      method: 'POST',
      uri: Uri.parse(ApiConfig.productIndex),
      body: payload,
      fallbackMessage: 'Gagal sync create produk',
    );
  }

  Future<Map<String, dynamic>> updateProduct(
    int productId,
    Map<String, dynamic> payload,
  ) async {
    return _mutate(
      method: 'PUT',
      uri: Uri.parse('${ApiConfig.productIndex}/$productId'),
      body: payload,
      fallbackMessage: 'Gagal sync update produk',
    );
  }

  Future<void> deleteProduct(int productId) async {
    await _mutate(
      method: 'DELETE',
      uri: Uri.parse('${ApiConfig.productIndex}/$productId'),
      fallbackMessage: 'Gagal sync hapus produk',
      allowEmptyBody: true,
    );
  }

  Future<Map<String, dynamic>> createCategory(
    Map<String, dynamic> payload,
  ) async {
    return _mutate(
      method: 'POST',
      uri: Uri.parse(ApiConfig.categoryIndex),
      body: payload,
      fallbackMessage: 'Gagal sync create kategori',
    );
  }

  Future<Map<String, dynamic>> updateCategory(
    int categoryId,
    Map<String, dynamic> payload,
  ) async {
    return _mutate(
      method: 'PUT',
      uri: Uri.parse('${ApiConfig.categoryIndex}/$categoryId'),
      body: payload,
      fallbackMessage: 'Gagal sync update kategori',
    );
  }

  Future<void> deleteCategory(int categoryId) async {
    await _mutate(
      method: 'DELETE',
      uri: Uri.parse('${ApiConfig.categoryIndex}/$categoryId'),
      fallbackMessage: 'Gagal sync hapus kategori',
      allowEmptyBody: true,
    );
  }

  Future<Map<String, dynamic>> createTransaction(
    Map<String, dynamic> payload,
  ) async {
    return _mutate(
      method: 'POST',
      uri: Uri.parse(ApiConfig.transactionIndex),
      body: payload,
      fallbackMessage: 'Gagal sync transaksi',
    );
  }

  Future<void> deleteTransaction(int transactionId) async {
    await _mutate(
      method: 'DELETE',
      uri: Uri.parse(ApiConfig.transactionDetail(transactionId)),
      fallbackMessage: 'Gagal sync hapus transaksi',
      allowEmptyBody: true,
    );
  }

  Future<Map<String, dynamic>> createDelivery(
    Map<String, dynamic> payload,
  ) async {
    return _mutate(
      method: 'POST',
      uri: Uri.parse(ApiConfig.deliveryIndex),
      body: payload,
      fallbackMessage: 'Gagal sync create pengiriman',
    );
  }

  Future<Map<String, dynamic>> updateDeliveryStatus(
    int deliveryId,
    String statusApi,
  ) async {
    return _mutate(
      method: 'PUT',
      uri: Uri.parse(ApiConfig.deliveryUpdateStatus(deliveryId)),
      body: {'status': statusApi},
      fallbackMessage: 'Gagal sync status pengiriman',
      allowEmptyBody: true,
    );
  }

  Future<Map<String, dynamic>> assignDelivery(
    int deliveryId,
    int userId, {
    int? vehicleId,
  }) async {
    return _mutate(
      method: 'POST',
      uri: Uri.parse(ApiConfig.deliveryAssign(deliveryId)),
      body: {
        'user_id': userId,
        if (vehicleId != null) 'vehicle_id': vehicleId,
      },
      fallbackMessage: 'Gagal sync assign pengiriman',
      allowEmptyBody: true,
    );
  }

  Future<Map<String, dynamic>> createMember(Map<String, dynamic> payload) async {
    return _mutate(
      method: 'POST',
      uri: Uri.parse(ApiConfig.memberIndex),
      body: payload,
      fallbackMessage: 'Gagal sync create member',
      allowEmptyBody: true,
    );
  }

  Future<Map<String, dynamic>> updateMember(
    int memberId,
    Map<String, dynamic> payload,
  ) async {
    return _mutate(
      method: 'PUT',
      uri: Uri.parse(ApiConfig.memberDetail(memberId)),
      body: payload,
      fallbackMessage: 'Gagal sync update member',
      allowEmptyBody: true,
    );
  }

  Future<void> toggleMemberStatus(int memberId) async {
    await _mutate(
      method: 'POST',
      uri: Uri.parse(ApiConfig.memberToggleStatus(memberId)),
      body: const {},
      fallbackMessage: 'Gagal sync status member',
      allowEmptyBody: true,
    );
  }

  Future<Map<String, dynamic>> createVehicle(
    Map<String, dynamic> payload,
  ) async {
    return _mutate(
      method: 'POST',
      uri: Uri.parse(ApiConfig.vehicleIndex),
      body: payload,
      fallbackMessage: 'Gagal sync create kendaraan',
      allowEmptyBody: true,
    );
  }

  Future<Map<String, dynamic>> updateVehicle(
    int vehicleId,
    Map<String, dynamic> payload,
  ) async {
    return _mutate(
      method: 'PUT',
      uri: Uri.parse(ApiConfig.vehicleDetail(vehicleId)),
      body: payload,
      fallbackMessage: 'Gagal sync update kendaraan',
      allowEmptyBody: true,
    );
  }

  Future<void> deleteVehicle(int vehicleId) async {
    await _mutate(
      method: 'DELETE',
      uri: Uri.parse(ApiConfig.vehicleDetail(vehicleId)),
      fallbackMessage: 'Gagal sync hapus kendaraan',
      allowEmptyBody: true,
    );
  }

  Future<Map<String, dynamic>> createUser(Map<String, dynamic> payload) async {
    return _mutate(
      method: 'POST',
      uri: Uri.parse(ApiConfig.userIndex),
      body: payload,
      fallbackMessage: 'Gagal sync create pengguna',
      allowEmptyBody: true,
    );
  }

  Future<Map<String, dynamic>> updateUser(
    int userId,
    Map<String, dynamic> payload,
  ) async {
    return _mutate(
      method: 'PUT',
      uri: Uri.parse('${ApiConfig.userIndex}/$userId'),
      body: payload,
      fallbackMessage: 'Gagal sync update pengguna',
      allowEmptyBody: true,
    );
  }

  Future<void> deleteUser(int userId) async {
    await _mutate(
      method: 'DELETE',
      uri: Uri.parse('${ApiConfig.userIndex}/$userId'),
      fallbackMessage: 'Gagal sync hapus pengguna',
      allowEmptyBody: true,
    );
  }

  Future<void> markNotificationRead(String id) async {
    await _mutate(
      method: 'POST',
      uri: Uri.parse('${ApiConfig.baseUrl}/notifications/$id/read'),
      fallbackMessage: 'Gagal sync notifikasi read',
      allowEmptyBody: true,
    );
  }

  Future<void> markAllNotificationsRead() async {
    await _mutate(
      method: 'POST',
      uri: Uri.parse('${ApiConfig.baseUrl}/notifications/read-all'),
      fallbackMessage: 'Gagal sync notifikasi read all',
      allowEmptyBody: true,
    );
  }

  Future<void> deleteNotification(String id) async {
    await _mutate(
      method: 'DELETE',
      uri: Uri.parse('${ApiConfig.baseUrl}/notifications/$id'),
      fallbackMessage: 'Gagal sync hapus notifikasi',
      allowEmptyBody: true,
    );
  }

  Future<void> clearNotifications() async {
    await _mutate(
      method: 'DELETE',
      uri: Uri.parse('${ApiConfig.baseUrl}/notifications/clear/all'),
      fallbackMessage: 'Gagal sync clear notifikasi',
      allowEmptyBody: true,
    );
  }

  Future<Map<String, dynamic>> _mutate({
    required String method,
    required Uri uri,
    Map<String, dynamic>? body,
    required String fallbackMessage,
    bool allowEmptyBody = false,
  }) async {
    try {
      final headers = await AuthService.authHeaders();
      final req = http.Request(method, uri)..headers.addAll(headers);
      if (body != null) req.body = jsonEncode(body);
      final streamed = await req.send().timeout(const Duration(seconds: 30));
      final response = await http.Response.fromStream(streamed);

      Map<String, dynamic> decoded = {};
      try {
        final json = jsonDecode(response.body);
        if (json is Map<String, dynamic>) {
          decoded = json;
        } else if (json is List) {
          decoded = {'data': json};
        }
      } catch (_) {}

      if (response.statusCode == 401) {
        throw Exception('Sesi login habis, silakan login ulang');
      }
      if (response.statusCode < 200 || response.statusCode >= 300) {
        final msg = _extractErrorMessage(
          decoded,
          fallbackMessage,
          response.statusCode,
        );
        throw Exception(msg);
      }

      if (decoded.isEmpty && allowEmptyBody) return {};
      if (decoded.isEmpty) throw Exception(fallbackMessage);

      final success = decoded['success'];
      if (success is bool && !success) {
        final msg = _extractErrorMessage(
          decoded,
          fallbackMessage,
          response.statusCode,
        );
        throw Exception(msg);
      }

      return decoded;
    } on TimeoutException {
      throw Exception(
        'Koneksi ke server timeout. Sync akan dicoba ulang otomatis.',
      );
    } on SocketException {
      throw Exception(
        'Tidak ada koneksi internet atau server tidak dapat dijangkau.',
      );
    } on http.ClientException {
      throw Exception(
        'Tidak dapat terhubung ke server. Sync akan dicoba ulang.',
      );
    }
  }

  String _extractErrorMessage(
    Map<String, dynamic> body,
    String fallbackMessage,
    int statusCode,
  ) {
    final message = body['message']?.toString();
    final error = body['error']?.toString();
    final errors = body['errors'];
    final validation = <String>[];
    if (errors is Map) {
      for (final value in errors.values) {
        if (value is List && value.isNotEmpty) {
          validation.add(value.first.toString());
        } else if (value != null) {
          validation.add(value.toString());
        }
      }
    }
    final detail = validation.join(' | ');
    if (detail.isNotEmpty && message != null && message.trim().isNotEmpty) {
      return '$message: $detail';
    }
    if (detail.isNotEmpty) return detail;
    if (error != null && error.trim().isNotEmpty) return error;
    if (message != null && message.trim().isNotEmpty) return message;
    return '$fallbackMessage (HTTP $statusCode)';
  }
}
