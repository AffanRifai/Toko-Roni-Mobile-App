import 'dart:async';
import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;
import '../config/api_config.dart';
import '../offline/category_local_repository.dart';
import '../offline/offline_utils.dart';
import '../offline/sync_manager.dart';
import '../offline/sync_queue_repository.dart';
import '../offline/sync_types.dart';
import 'auth_service.dart';
import 'notification_refresh_helper.dart';

class CategoryRecord {
  final int id;
  final String nama;
  final String slug;
  final String deskripsi;
  final bool aktif;
  final int totalProduk;
  final String terakhirDiperbarui;

  const CategoryRecord({
    required this.id,
    required this.nama,
    required this.slug,
    required this.deskripsi,
    required this.aktif,
    required this.totalProduk,
    required this.terakhirDiperbarui,
  });

  factory CategoryRecord.fromJson(Map<String, dynamic> json) {
    final name =
        (json['name'] ??
                json['nama'] ??
                json['nama_kategori'] ??
                json['category_name'] ??
                '')
            .toString()
            .trim();
    final slugRaw = (json['slug'] ?? json['slug_url'] ?? '').toString().trim();
    final slug = slugRaw.isNotEmpty ? slugRaw : _slugify(name);

    return CategoryRecord(
      id: _toInt(json['id']),
      nama: name,
      slug: slug,
      deskripsi:
          (json['description'] ?? json['deskripsi'] ?? json['desc'] ?? '')
              .toString(),
      aktif: _toBool(
        json['is_active'] ?? json['aktif'] ?? json['status'],
        defaultValue: true,
      ),
      totalProduk: _toInt(
        json['products_count'] ??
            json['product_count'] ??
            json['total_products'] ??
            json['totalProduk'] ??
            (json['products'] is List ? (json['products'] as List).length : 0),
      ),
      terakhirDiperbarui: _formatUpdatedAt(
        json['updated_at'] ??
            json['updatedAt'] ??
            json['terakhir_diperbarui'] ??
            json['last_updated'],
      ),
    );
  }
}

class CategoryService {
  static Future<List<CategoryRecord>> getCategories({int perPage = 200}) async {
    try {
      final categories = await _getCategoriesFromUrl(
        '${ApiConfig.categoryIndex}?per_page=$perPage',
      );
      return categories;
    } catch (e) {
      final msg = e.toString().toLowerCase();
      final looksLikeMissingEndpoint =
          msg.contains('http 404') || msg.contains('not found');
      if (!looksLikeMissingEndpoint) {
        final local = await CategoryLocalRepository.instance.getCategories();
        if (local.isNotEmpty && isNetworkReachabilityError(e)) return local;
        rethrow;
      }
    }

    // Fallback untuk backend yang hanya expose endpoint kategori via products.
    try {
      return await _getCategoriesFromUrl(ApiConfig.productCategories);
    } catch (e) {
      final local = await CategoryLocalRepository.instance.getCategories();
      if (local.isNotEmpty && isNetworkReachabilityError(e)) return local;
      rethrow;
    }
  }

  static Future<List<CategoryRecord>> _getCategoriesFromUrl(String url) async {
    final response = await _performRequest(
      () async => http
          .get(Uri.parse(url), headers: await AuthService.authHeaders())
          .timeout(const Duration(seconds: 15)),
    );

    final json = _decode(response, fallbackMessage: 'Gagal memuat kategori');
    final list = _extractList(json['data']);
    final rawMaps = list.map(_asMap).where((m) => m.isNotEmpty).toList();
    await CategoryLocalRepository.instance.cacheRemoteCategories(rawMaps);

    return rawMaps.map(CategoryRecord.fromJson).toList();
  }

  static Future<CategoryRecord> createCategory({
    required String nama,
    required String slug,
    required String deskripsi,
    required bool aktif,
  }) async {
    final body = _buildCategoryPayload(
      nama: nama,
      slug: slug,
      deskripsi: deskripsi,
      aktif: aktif,
    );
    try {
      final created = await _createCategoryRemote(body);
      await NotificationRefreshHelper.refreshSafely();
      return created;
    } catch (e) {
      if (!isNetworkReachabilityError(e)) rethrow;
      final pending = await CategoryLocalRepository.instance.savePendingCreate(
        nama: nama,
        slug: body['slug']?.toString() ?? slug,
        deskripsi: deskripsi,
        aktif: aktif,
      );
      await SyncQueueRepository.instance.enqueue(
        entityType: LocalEntityType.category,
        entityLocalId: pending.localId,
        operation: SyncOperation.create,
        payload: body,
      );
      unawaited(SyncManager.instance.triggerSync());
      return pending.item;
    }
  }

  static Future<CategoryRecord> updateCategory({
    required int categoryId,
    required String nama,
    required String slug,
    required String deskripsi,
    required bool aktif,
  }) async {
    final body = _buildCategoryPayload(
      nama: nama,
      slug: slug,
      deskripsi: deskripsi,
      aktif: aktif,
    );
    try {
      final updated = await _updateCategoryRemote(categoryId, body);
      await NotificationRefreshHelper.refreshSafely();
      return updated;
    } catch (e) {
      if (!isNetworkReachabilityError(e)) rethrow;
      await CategoryLocalRepository.instance.savePendingUpdate(
        categoryId: categoryId,
        nama: nama,
        slug: body['slug']?.toString() ?? slug,
        deskripsi: deskripsi,
        aktif: aktif,
      );
      final localId = await CategoryLocalRepository.instance.findLocalIdByAnyId(
        categoryId,
      );
      if (localId != null) {
        await SyncQueueRepository.instance.enqueue(
          entityType: LocalEntityType.category,
          entityLocalId: localId,
          operation: SyncOperation.update,
          payload: body,
        );
      }
      unawaited(SyncManager.instance.triggerSync());
      return CategoryRecord(
        id: categoryId,
        nama: nama,
        slug: body['slug']?.toString() ?? slug,
        deskripsi: deskripsi,
        aktif: aktif,
        totalProduk: 0,
        terakhirDiperbarui: _formatUpdatedAt(DateTime.now().toIso8601String()),
      );
    }
  }

  static Future<void> deleteCategory({required int categoryId}) async {
    try {
      final response = await _performRequest(
        () async => http
            .delete(
              Uri.parse('${ApiConfig.categoryIndex}/$categoryId'),
              headers: await AuthService.authHeaders(),
            )
            .timeout(const Duration(seconds: 20)),
      );

      _decode(
        response,
        fallbackMessage: 'Gagal menghapus kategori',
        allowEmptyBody: true,
      );
      final localId = await CategoryLocalRepository.instance.findLocalIdByAnyId(
        categoryId,
      );
      if (localId != null) {
        await CategoryLocalRepository.instance.removeByLocalId(localId);
      }
      await NotificationRefreshHelper.refreshSafely();
    } catch (e) {
      if (!isNetworkReachabilityError(e)) rethrow;
      await CategoryLocalRepository.instance.savePendingDelete(categoryId);
      final localId = await CategoryLocalRepository.instance.findLocalIdByAnyId(
        categoryId,
      );
      if (localId != null) {
        await SyncQueueRepository.instance.enqueue(
          entityType: LocalEntityType.category,
          entityLocalId: localId,
          operation: SyncOperation.delete,
          payload: {'id': categoryId},
        );
      }
      unawaited(SyncManager.instance.triggerSync());
    }
  }

  static Future<CategoryRecord> _createCategoryRemote(
    Map<String, dynamic> body,
  ) async {
    final url = Uri.parse(ApiConfig.categoryIndex);
    final attempts = <Future<http.Response> Function()>[
      () => _sendJson(method: 'POST', url: url, body: body),
      () => _sendForm(method: 'POST', url: url, body: body),
      () => _sendMultipart(method: 'POST', url: url, body: body),
    ];

    http.Response? lastResponse;
    for (final attempt in attempts) {
      final response = await _performRequest(
        () => attempt().timeout(const Duration(seconds: 20)),
      );

      if (_isSuccessStatus(response.statusCode)) {
        final created = _recordFromMutationResponse(
          response: response,
          fallbackMessage: 'Gagal menambah kategori',
          fallbackRecord: body,
          fallbackId: 0,
        );
        final decoded = _decode(
          response,
          fallbackMessage: 'Gagal menambah kategori',
          allowEmptyBody: true,
        );
        final data = _asMap(decoded['data']);
        await CategoryLocalRepository.instance.cacheRemoteCategories([
          data.isNotEmpty ? data : body,
        ]);
        return created;
      }

      lastResponse = response;
      if (!_isRetryableMutationStatus(response.statusCode)) break;
    }

    if (lastResponse != null) {
      _decode(lastResponse, fallbackMessage: 'Gagal menambah kategori');
    }
    throw Exception('Gagal menambah kategori');
  }

  static Future<CategoryRecord> _updateCategoryRemote(
    int categoryId,
    Map<String, dynamic> body,
  ) async {
    final url = Uri.parse('${ApiConfig.categoryIndex}/$categoryId');

    final attempts = <Future<http.Response> Function()>[
      () => _sendJson(method: 'PUT', url: url, body: body),
      () => _sendJson(method: 'PATCH', url: url, body: body),
      () => _sendForm(method: 'PUT', url: url, body: body),
      () => _sendForm(method: 'PATCH', url: url, body: body),
      () => _sendForm(
        method: 'POST',
        url: url,
        body: {...body, '_method': 'PUT'},
      ),
      () => _sendMultipart(
        method: 'POST',
        url: url,
        body: {...body, '_method': 'PUT'},
      ),
    ];

    http.Response? lastResponse;
    for (final attempt in attempts) {
      final response = await _performRequest(
        () => attempt().timeout(const Duration(seconds: 20)),
      );

      if (_isSuccessStatus(response.statusCode)) {
        final updated = _recordFromMutationResponse(
          response: response,
          fallbackMessage: 'Gagal memperbarui kategori',
          fallbackRecord: body,
          fallbackId: categoryId,
        );
        final decoded = _decode(
          response,
          fallbackMessage: 'Gagal memperbarui kategori',
          allowEmptyBody: true,
        );
        final data = _asMap(decoded['data']);
        await CategoryLocalRepository.instance.cacheRemoteCategories([
          data.isNotEmpty ? data : {...body, 'id': categoryId},
        ]);
        return updated;
      }

      lastResponse = response;
      if (!_isRetryableMutationStatus(response.statusCode)) break;
    }

    if (lastResponse != null) {
      _decode(lastResponse, fallbackMessage: 'Gagal memperbarui kategori');
    }
    throw Exception('Gagal memperbarui kategori');
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
    bool allowEmptyBody = false,
  }) {
    Map<String, dynamic> body = {};
    try {
      final decoded = jsonDecode(response.body);
      if (decoded is Map<String, dynamic>) {
        body = decoded;
      } else if (decoded is List) {
        body = {'data': decoded};
      }
    } catch (_) {}

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
    if (body.isEmpty && allowEmptyBody) return {};

    final success = body['success'];
    if (success is bool && !success) {
      throw Exception(
        _buildErrorMessage(body, fallbackMessage, response.statusCode),
      );
    }

    return body;
  }

  static Map<String, dynamic> _asMap(dynamic raw) {
    if (raw is Map<String, dynamic>) return raw;
    if (raw is Map) {
      return raw.map((k, v) => MapEntry(k.toString(), v));
    }
    return {};
  }

  static List<dynamic> _extractList(dynamic raw) {
    if (raw is List) return raw;
    if (raw is Map<String, dynamic>) {
      for (final key in ['data', 'items', 'categories']) {
        final nested = raw[key];
        if (nested is List) return nested;
      }
    }
    return const [];
  }

  static String _buildErrorMessage(
    Map<String, dynamic> body,
    String fallbackMessage,
    int statusCode,
  ) {
    final message = body['message']?.toString();
    final error = body['error']?.toString();
    final errors = body['errors'];

    final sqlMessage = _friendlySqlMessage(message: message, error: error);
    if (sqlMessage != null) return sqlMessage;

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
    if (details.isNotEmpty && message != null && message.trim().isNotEmpty) {
      return '$message: $details';
    }
    if (details.isNotEmpty) return details;
    if (error != null && error.trim().isNotEmpty) return error;
    if (message != null && message.trim().isNotEmpty) return message;
    return '$fallbackMessage (HTTP $statusCode)';
  }

  static Map<String, dynamic> _buildCategoryPayload({
    required String nama,
    required String slug,
    required String deskripsi,
    required bool aktif,
  }) {
    final name = nama.trim();
    final normalizedSlug = _normalizeSlug(slug, name);
    final description = deskripsi.trim();
    final descriptionOrNull = description.isEmpty ? null : description;
    final activeValue = aktif ? 1 : 0;

    // Kirim beberapa alias field agar kompatibel dengan variasi backend.
    return {
      'name': name,
      'nama': name,
      'nama_kategori': name,
      'category_name': name,
      'slug': normalizedSlug,
      'slug_url': normalizedSlug,
      'slug_kategori': normalizedSlug,
      'category_slug': normalizedSlug,
      'description': descriptionOrNull,
      'deskripsi': descriptionOrNull,
      'desc': descriptionOrNull,
      'is_active': aktif ? 1 : 0,
      'aktif': activeValue,
      'status': aktif ? 'aktif' : 'nonaktif',
    };
  }

  static String _normalizeSlug(String slug, String nama) {
    final clean = slug.trim();
    if (clean.isNotEmpty) return clean;
    return _slugify(nama);
  }

  static String? _friendlySqlMessage({String? message, String? error}) {
    final raw = '${message ?? ''} ${error ?? ''}'.toLowerCase();
    final missingSlugDefault =
        raw.contains('sqlstate[hy000]') &&
        raw.contains("field 'slug'") &&
        raw.contains("doesn't have a default value");

    if (missingSlugDefault) {
      return 'Server kategori gagal menyimpan karena field slug belum diproses di backend (create/update).';
    }
    return null;
  }

  static Future<http.Response> _sendJson({
    required String method,
    required Uri url,
    required Map<String, dynamic> body,
  }) async {
    final headers = await AuthService.authHeaders();
    final req = http.Request(method, url)
      ..headers.addAll(headers)
      ..body = jsonEncode(body);
    final streamed = await req.send();
    return http.Response.fromStream(streamed);
  }

  static Future<http.Response> _sendForm({
    required String method,
    required Uri url,
    required Map<String, dynamic> body,
  }) async {
    final headers = await AuthService.authHeaders();
    headers['Content-Type'] = 'application/x-www-form-urlencoded';
    final req = http.Request(method, url)
      ..headers.addAll(headers)
      ..bodyFields = _toFormFields(body);
    final streamed = await req.send();
    return http.Response.fromStream(streamed);
  }

  static Future<http.Response> _sendMultipart({
    required String method,
    required Uri url,
    required Map<String, dynamic> body,
  }) async {
    final headers = await AuthService.authHeaders();
    headers.remove('Content-Type');
    final req = http.MultipartRequest(method, url)
      ..headers.addAll(headers)
      ..fields.addAll(_toFormFields(body));
    final streamed = await req.send();
    return http.Response.fromStream(streamed);
  }

  static Map<String, String> _toFormFields(Map<String, dynamic> body) {
    final fields = <String, String>{};
    body.forEach((key, value) {
      if (value == null) return;
      fields[key] = value.toString();
    });
    return fields;
  }

  static bool _isSuccessStatus(int statusCode) =>
      statusCode >= 200 && statusCode < 300;

  static bool _isRetryableMutationStatus(int statusCode) =>
      statusCode == 400 ||
      statusCode == 404 ||
      statusCode == 405 ||
      statusCode == 415 ||
      statusCode == 422 ||
      statusCode >= 500;

  static CategoryRecord _recordFromMutationResponse({
    required http.Response response,
    required String fallbackMessage,
    required Map<String, dynamic> fallbackRecord,
    required int fallbackId,
  }) {
    final json = _decode(
      response,
      fallbackMessage: fallbackMessage,
      allowEmptyBody: true,
    );
    final data = _asMap(json['data']);
    final record = data.isNotEmpty ? data : fallbackRecord;
    return CategoryRecord.fromJson({
      ...record,
      if (_toInt(record['id']) == 0) 'id': fallbackId,
      if (!record.containsKey('products_count')) 'products_count': 0,
      if (!record.containsKey('updated_at'))
        'updated_at': DateTime.now().toIso8601String(),
    });
  }
}

int _toInt(dynamic value) {
  if (value == null) return 0;
  if (value is int) return value;
  if (value is num) return value.toInt();
  return int.tryParse(value.toString()) ?? 0;
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

String _slugify(String input) {
  return input
      .toLowerCase()
      .trim()
      .replaceAll(RegExp(r'\s+'), '-')
      .replaceAll(RegExp(r'[^a-z0-9\-]'), '');
}

String _formatUpdatedAt(dynamic value) {
  if (value == null) return '-';
  final raw = value.toString().trim();
  if (raw.isEmpty) return '-';
  try {
    final dt = DateTime.parse(raw);
    final dd = dt.day.toString().padLeft(2, '0');
    final mm = dt.month.toString().padLeft(2, '0');
    return '$dd-$mm-${dt.year}';
  } catch (_) {
    return raw;
  }
}
