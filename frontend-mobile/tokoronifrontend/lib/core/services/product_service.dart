import 'dart:async';
import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;
import '../../models/produk_model.dart';
import '../config/api_config.dart';
import '../offline/category_local_repository.dart';
import '../offline/offline_utils.dart';
import '../offline/product_local_repository.dart';
import '../offline/sync_manager.dart';
import '../offline/sync_queue_repository.dart';
import '../offline/sync_types.dart';
import 'auth_service.dart';
import 'notification_refresh_helper.dart';

class ProductBundle {
  final List<ProdukItem> products;
  final List<KategoriItem> categories;

  const ProductBundle({required this.products, required this.categories});
}

class ProductService {
  static Future<ProductBundle> getProductsAndCategories() async {
    final results = await Future.wait([getProducts(), getCategories()]);
    return ProductBundle(
      products: results[0] as List<ProdukItem>,
      categories: results[1] as List<KategoriItem>,
    );
  }

  static Future<List<ProdukItem>> getProducts({int perPage = 200}) async {
    try {
      final response = await _performRequest(
        () async => http
            .get(
              Uri.parse('${ApiConfig.productIndex}?per_page=$perPage'),
              headers: await AuthService.authHeaders(),
            )
            .timeout(const Duration(seconds: 15)),
      );

      final json = _decode(response, fallbackMessage: 'Gagal memuat produk');
      final list = _extractList(json['data']);
      final rawMaps = list.map(_asMap).where((m) => m.isNotEmpty).toList();
      await ProductLocalRepository.instance.cacheRemoteProducts(rawMaps);
      return rawMaps.map(ProdukItem.fromJson).toList(growable: false);
    } catch (error) {
      final fallback = await ProductLocalRepository.instance.getProducts();
      if (fallback.isNotEmpty && isNetworkReachabilityError(error)) {
        return fallback;
      }
      rethrow;
    }
  }

  static Future<List<KategoriItem>> getCategories() async {
    try {
      final response = await _performRequest(
        () async => http
            .get(
              Uri.parse(ApiConfig.productCategories),
              headers: await AuthService.authHeaders(),
            )
            .timeout(const Duration(seconds: 15)),
      );

      final json = _decode(response, fallbackMessage: 'Gagal memuat kategori');
      final list = _extractList(json['data']);
      final rawMaps = list.map(_asMap).where((m) => m.isNotEmpty).toList();
      await CategoryLocalRepository.instance.cacheRemoteCategories(rawMaps);
      return rawMaps.map(KategoriItem.fromJson).toList(growable: false);
    } catch (error) {
      final local = await CategoryLocalRepository.instance.getCategories();
      if (local.isNotEmpty && isNetworkReachabilityError(error)) {
        return local
            .map(
              (e) =>
                  KategoriItem(id: e.id, nama: e.nama, deskripsi: e.deskripsi),
            )
            .toList(growable: false);
      }
      rethrow;
    }
  }

  static Future<ProdukItem> createProduct({
    required ProdukFormModel model,
    required List<KategoriItem> categories,
  }) async {
    final body = _buildProductPayload(model: model, categories: categories);
    final categoryName = model.kategori.trim();
    try {
      final response = await _performRequest(
        () async => http
            .post(
              Uri.parse(ApiConfig.productIndex),
              headers: await AuthService.authHeaders(),
              body: jsonEncode(body),
            )
            .timeout(const Duration(seconds: 20)),
      );

      final json = _decode(response, fallbackMessage: 'Gagal menambah produk');
      final data = _asMap(json['data']);
      if (data.isNotEmpty) {
        await ProductLocalRepository.instance.cacheRemoteProducts([data]);
      }
      final created = ProdukItem.fromJson(data);
      await NotificationRefreshHelper.refreshSafely();
      return created;
    } catch (error) {
      if (!isNetworkReachabilityError(error)) rethrow;
      final pending = await ProductLocalRepository.instance.savePendingCreate(
        payload: body,
        categoryName: categoryName,
      );
      await SyncQueueRepository.instance.enqueue(
        entityType: LocalEntityType.product,
        entityLocalId: pending.localId,
        operation: SyncOperation.create,
        payload: body,
      );
      unawaited(SyncManager.instance.triggerSync());
      return pending.item;
    }
  }

  static Future<ProdukItem> updateProduct({
    required int productId,
    required ProdukFormModel model,
    required List<KategoriItem> categories,
  }) async {
    final body = _buildProductPayload(model: model, categories: categories);
    final categoryName = model.kategori.trim();
    try {
      final response = await _performRequest(
        () async => http
            .put(
              Uri.parse('${ApiConfig.productIndex}/$productId'),
              headers: await AuthService.authHeaders(),
              body: jsonEncode(body),
            )
            .timeout(const Duration(seconds: 20)),
      );

      final json = _decode(
        response,
        fallbackMessage: 'Gagal memperbarui produk',
      );
      final data = _asMap(json['data']);
      if (data.isNotEmpty) {
        await ProductLocalRepository.instance.cacheRemoteProducts([data]);
      }
      final updated = ProdukItem.fromJson(data);
      await NotificationRefreshHelper.refreshSafely();
      return updated;
    } catch (error) {
      if (!isNetworkReachabilityError(error)) rethrow;
      await ProductLocalRepository.instance.savePendingUpdate(
        productId: productId,
        payload: body,
        categoryName: categoryName,
      );
      final localId = await ProductLocalRepository.instance.findLocalIdByAnyId(
        productId,
      );
      if (localId != null) {
        await SyncQueueRepository.instance.enqueue(
          entityType: LocalEntityType.product,
          entityLocalId: localId,
          operation: SyncOperation.update,
          payload: body,
        );
      }
      unawaited(SyncManager.instance.triggerSync());
      final local = await ProductLocalRepository.instance.getProducts();
      final match = local.where((p) => p.id == productId).toList();
      return match.isNotEmpty
          ? match.first
          : ProdukItem.fromJson({
              'id': productId,
              'name': model.nama,
              'code': model.kode,
              'category_name': model.kategori,
              'description': model.deskripsi,
              'unit': model.satuan,
              'price': body['price'],
              'cost_price': body['cost_price'],
              'stock': body['stock'],
              'min_stock': body['min_stock'],
              'barcode': body['barcode'],
              'weight': body['weight'],
              'dimensions': body['dimensions'],
              'expiry_date': body['expiry_date'],
              'is_active': body['is_active'],
            });
    }
  }

  static Future<void> deleteProduct({required int productId}) async {
    try {
      final response = await _performRequest(
        () async => http
            .delete(
              Uri.parse('${ApiConfig.productIndex}/$productId'),
              headers: await AuthService.authHeaders(),
            )
            .timeout(const Duration(seconds: 20)),
      );

      _decode(
        response,
        fallbackMessage: 'Gagal menghapus produk',
        allowEmptyBody: true,
      );
      final localId = await ProductLocalRepository.instance.findLocalIdByAnyId(
        productId,
      );
      if (localId != null) {
        await ProductLocalRepository.instance.removeByLocalId(localId);
      }
      await NotificationRefreshHelper.refreshSafely();
    } catch (error) {
      if (!isNetworkReachabilityError(error)) rethrow;
      await ProductLocalRepository.instance.savePendingDelete(productId);
      final localId = await ProductLocalRepository.instance.findLocalIdByAnyId(
        productId,
      );
      if (localId != null) {
        await SyncQueueRepository.instance.enqueue(
          entityType: LocalEntityType.product,
          entityLocalId: localId,
          operation: SyncOperation.delete,
          payload: {'id': productId},
        );
      }
      unawaited(SyncManager.instance.triggerSync());
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
      if (decoded is Map<String, dynamic>) body = decoded;
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

    final isSuccess = body['success'];
    if (isSuccess is bool && !isSuccess) {
      throw Exception(
        _buildErrorMessage(body, fallbackMessage, response.statusCode),
      );
    }

    return body;
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

  static Map<String, dynamic> _asMap(dynamic raw) {
    if (raw is Map<String, dynamic>) return raw;
    if (raw is Map) {
      return raw.map((k, v) => MapEntry(k.toString(), v));
    }
    return {};
  }

  static String _buildErrorMessage(
    Map<String, dynamic> body,
    String fallbackMessage,
    int statusCode,
  ) {
    final message = body['message']?.toString();
    final error = body['error']?.toString();
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
    if (details.isNotEmpty && message != null && message.trim().isNotEmpty) {
      return '$message: $details';
    }
    if (details.isNotEmpty) return details;
    if (error != null && error.trim().isNotEmpty) return error;
    if (message != null && message.trim().isNotEmpty) return message;
    return '$fallbackMessage (HTTP $statusCode)';
  }

  static List<dynamic> _extractList(dynamic raw) {
    if (raw is List) return raw;
    if (raw is Map<String, dynamic>) {
      final nested = raw['data'];
      if (nested is List) return nested;
    }
    return const [];
  }

  static Map<String, dynamic> _buildProductPayload({
    required ProdukFormModel model,
    required List<KategoriItem> categories,
  }) {
    final kategoriNama = model.kategori.trim().toLowerCase();
    final matchedKategori = categories.firstWhere(
      (k) => k.nama.trim().toLowerCase() == kategoriNama,
      orElse: () => KategoriItem(nama: ''),
    );
    final categoryId = matchedKategori.id;

    if (categoryId == null) {
      throw Exception('Kategori "${model.kategori}" tidak ditemukan di server');
    }

    return {
      'code': model.kode.trim(),
      'name': model.nama.trim(),
      'category_id': categoryId,
      'description': model.deskripsi.trim().isEmpty ? null : model.deskripsi,
      'price': _toInt(model.hargaJual),
      'cost_price': _toInt(model.hargaModal),
      'stock': _toInt(model.stokAwal),
      'min_stock': _toInt(model.stokMinimum, fallback: 10),
      'unit': model.satuan.trim().isEmpty ? 'Pcs' : model.satuan.trim(),
      'barcode': model.barcode.trim().isEmpty ? null : model.barcode.trim(),
      'weight': _toNullableInt(model.berat),
      'dimensions': model.dimensi.trim().isEmpty ? null : model.dimensi.trim(),
      'expiry_date': _toApiDate(model.kadaluarsa),
      'is_active': model.aktif,
    };
  }

  static int _toInt(String value, {int fallback = 0}) {
    final raw = value.trim();
    if (raw.isEmpty) return fallback;
    final parsed = int.tryParse(raw);
    if (parsed != null) return parsed;

    final clean = raw.replaceAll(RegExp(r'[^0-9]'), '');
    return int.tryParse(clean) ?? fallback;
  }

  static int? _toNullableInt(String value) {
    final raw = value.trim();
    if (raw.isEmpty) return null;
    final parsed = int.tryParse(raw);
    if (parsed != null) return parsed;
    final clean = raw.replaceAll(RegExp(r'[^0-9]'), '');
    return int.tryParse(clean);
  }

  static String? _toApiDate(DateTime? date) {
    if (date == null) return null;
    final mm = date.month.toString().padLeft(2, '0');
    final dd = date.day.toString().padLeft(2, '0');
    return '${date.year}-$mm-$dd';
  }
}
