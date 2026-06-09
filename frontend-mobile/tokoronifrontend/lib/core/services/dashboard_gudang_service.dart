import 'dart:async';
import 'dart:convert';
import 'dart:io';

import 'package:http/http.dart' as http;

import '../config/api_config.dart';
import '../offline/dashboard_cache_repository.dart';
import '../offline/offline_utils.dart';
import 'auth_service.dart';

class DashboardGudangSummary {
  final int totalProduk;
  final int stokRendah;
  final double nilaiInventori;
  final int totalTerjualUnit;
  final double totalPendapatan;
  final double rataRataHargaPerItem;

  const DashboardGudangSummary({
    this.totalProduk = 0,
    this.stokRendah = 0,
    this.nilaiInventori = 0,
    this.totalTerjualUnit = 0,
    this.totalPendapatan = 0,
    this.rataRataHargaPerItem = 0,
  });
}

class DashboardGudangLowStockItem {
  final String namaProduk;
  final String kategori;
  final int stok;
  final int stokMinimum;

  const DashboardGudangLowStockItem({
    required this.namaProduk,
    required this.kategori,
    required this.stok,
    required this.stokMinimum,
  });

  factory DashboardGudangLowStockItem.fromJson(Map<String, dynamic> json) {
    final category = _asMap(json['category']);

    return DashboardGudangLowStockItem(
      namaProduk: (json['name'] ?? json['product_name'] ?? '-').toString(),
      kategori: (category['name'] ?? json['category_name'] ?? '-').toString(),
      stok: _toInt(json['stock']),
      stokMinimum: _toInt(json['min_stock'], fallback: 10),
    );
  }
}

class DashboardGudangCategoryItem {
  final String namaKategori;
  final int totalProduk;
  final bool aktif;

  const DashboardGudangCategoryItem({
    required this.namaKategori,
    required this.totalProduk,
    required this.aktif,
  });

  factory DashboardGudangCategoryItem.fromJson(Map<String, dynamic> json) {
    return DashboardGudangCategoryItem(
      namaKategori: (json['name'] ?? json['nama'] ?? '-').toString(),
      totalProduk: _toInt(
        json['products_count'] ??
            json['product_count'] ??
            json['total_products'],
      ),
      aktif: _toBool(json['is_active'], defaultValue: true),
    );
  }
}

class DashboardGudangStockUpdateItem {
  final String namaProduk;
  final String kategori;
  final int stok;
  final DateTime? updatedAt;

  const DashboardGudangStockUpdateItem({
    required this.namaProduk,
    required this.kategori,
    required this.stok,
    required this.updatedAt,
  });

  factory DashboardGudangStockUpdateItem.fromJson(Map<String, dynamic> json) {
    final category = _asMap(json['category']);

    return DashboardGudangStockUpdateItem(
      namaProduk: (json['name'] ?? '-').toString(),
      kategori: (category['name'] ?? json['category_name'] ?? '-').toString(),
      stok: _toInt(json['stock']),
      updatedAt: _parseDate(
        json['updated_at'] ?? json['updatedAt'] ?? json['created_at'],
      ),
    );
  }
}

class DashboardGudangData {
  final DashboardGudangSummary summary;
  final List<DashboardGudangLowStockItem> lowStockItems;
  final List<DashboardGudangCategoryItem> categoryItems;
  final List<DashboardGudangStockUpdateItem> stockUpdates;

  const DashboardGudangData({
    required this.summary,
    required this.lowStockItems,
    required this.categoryItems,
    required this.stockUpdates,
  });
}

class DashboardGudangService {
  static const Duration _requestTimeout = Duration(seconds: 20);
  static const Set<String> _failedTransactionStatuses = {
    'failed',
    'gagal',
    'cancelled',
    'canceled',
    'void',
  };

  static Future<DashboardGudangData> getDashboardData() async {
    try {
      final results = await Future.wait([
        _getStatsJson(),
        _getAllProducts(),
        getLowStockProducts(limit: 10),
        getCategories(limit: 20),
        _getTotalRevenueAllTime(),
        _getTotalSoldUnitsAllTime(),
      ]);

      final statsJson = results[0] as Map<String, dynamic>;
      final products = results[1] as List<Map<String, dynamic>>;
      final lowStockItems = results[2] as List<DashboardGudangLowStockItem>;
      final categories = results[3] as List<DashboardGudangCategoryItem>;
      final totalRevenue = results[4] as double;
      final soldUnits = results[5] as int;

      final statsData = _asMap(statsJson['data']);
      final productStats = _asMap(statsData['products']);

      final totalProduk = _toInt(
        productStats['total'],
        fallback: products.length,
      );
      final stokRendah = _toInt(productStats['low_stock']);
      final nilaiInventori = _toDouble(productStats['total_value']);

      final avgPrice = products.isEmpty
          ? 0.0
          : products
                    .map((p) => _toDouble(p['price']))
                    .fold<double>(0, (sum, v) => sum + v) /
                products.length;

      final stockUpdates =
          products.map(DashboardGudangStockUpdateItem.fromJson).toList()
            ..sort((a, b) {
              final aTime = a.updatedAt?.millisecondsSinceEpoch ?? 0;
              final bTime = b.updatedAt?.millisecondsSinceEpoch ?? 0;
              return bTime.compareTo(aTime);
            });

      final result = DashboardGudangData(
        summary: DashboardGudangSummary(
          totalProduk: totalProduk,
          stokRendah: stokRendah,
          nilaiInventori: nilaiInventori,
          totalTerjualUnit: soldUnits,
          totalPendapatan: totalRevenue,
          rataRataHargaPerItem: avgPrice,
        ),
        lowStockItems: lowStockItems,
        categoryItems: categories,
        stockUpdates: stockUpdates.take(10).toList(),
      );

      await DashboardCacheRepository.instance.saveGudang(
        _serializeDashboardData(result),
      );
      return result;
    } catch (error) {
      if (!isNetworkReachabilityError(error)) rethrow;
      final cached = await DashboardCacheRepository.instance.getGudang();
      if (cached.isEmpty) rethrow;
      return _deserializeDashboardData(cached);
    }
  }

  static Future<List<DashboardGudangLowStockItem>> getLowStockProducts({
    int limit = 10,
  }) async {
    final safeLimit = limit <= 0 ? 10 : limit;
    final json = await _getJson(
      '${ApiConfig.productLowStock}?limit=$safeLimit',
      fallbackMessage: 'Gagal memuat data stok rendah',
    );

    return _extractList(json['data'])
        .map((e) => DashboardGudangLowStockItem.fromJson(_asMap(e)))
        .take(safeLimit)
        .toList();
  }

  static Future<List<DashboardGudangCategoryItem>> getCategories({
    int limit = 20,
  }) async {
    final safeLimit = limit <= 0 ? 20 : limit;

    Map<String, dynamic>? json;

    try {
      json = await _getJson(
        '${ApiConfig.categoryIndex}?per_page=$safeLimit',
        fallbackMessage: 'Gagal memuat data kategori',
      );
    } catch (_) {
      json = await _getJson(
        ApiConfig.productCategories,
        fallbackMessage: 'Gagal memuat data kategori',
      );
    }

    return _extractList(json['data'])
        .map((e) => DashboardGudangCategoryItem.fromJson(_asMap(e)))
        .take(safeLimit)
        .toList();
  }

  static Future<Map<String, dynamic>> _getStatsJson() {
    return _getJson(
      ApiConfig.dashboardStats,
      fallbackMessage: 'Gagal memuat statistik gudang',
    );
  }

  static Future<List<Map<String, dynamic>>> _getAllProducts() async {
    final items = <Map<String, dynamic>>[];
    int page = 1;
    int lastPage = 1;

    do {
      final json = await _getJson(
        '${ApiConfig.productIndex}?per_page=200&page=$page',
        fallbackMessage: 'Gagal memuat data produk',
      );
      final paginated = _asMap(json['data']);
      final rows = _extractList(paginated['data']);
      if (rows.isEmpty) break;

      items.addAll(rows.map(_asMap).where((row) => row.isNotEmpty));

      page = _toInt(paginated['current_page'], fallback: page);
      lastPage = _toInt(paginated['last_page'], fallback: page);

      if (page >= lastPage) break;
      page += 1;
    } while (page <= lastPage);

    return items;
  }

  static Future<double> _getTotalRevenueAllTime() async {
    final today = DateTime.now();
    final todayText =
        '${today.year}-${today.month.toString().padLeft(2, '0')}-${today.day.toString().padLeft(2, '0')}';

    final summaryJson = await _tryGetJson(
      '${ApiConfig.baseUrl}/reports/sales/summary?start_date=2000-01-01&end_date=$todayText',
    );

    final summaryData = _asMap(summaryJson?['data']);
    final fromSummary = _toDouble(summaryData['total_revenue']);
    if (fromSummary > 0) return fromSummary;

    double total = 0;
    int page = 1;
    int lastPage = 1;

    do {
      final json = await _tryGetJson(
        '${ApiConfig.transactionIndex}?per_page=200&page=$page',
      );
      if (json == null) break;

      final paginated = _asMap(json['data']);
      final rows = _extractList(paginated['data']);
      if (rows.isEmpty) break;

      for (final row in rows) {
        final trx = _asMap(row);
        if (_isFailedTransaction(trx)) continue;
        total += _toDouble(trx['total_amount']);
      }

      page = _toInt(paginated['current_page'], fallback: page);
      lastPage = _toInt(paginated['last_page'], fallback: page);
      if (page >= lastPage) break;
      page += 1;
    } while (page <= lastPage);

    return total;
  }

  static Future<int> _getTotalSoldUnitsAllTime() async {
    int totalUnits = 0;
    int page = 1;
    int lastPage = 1;

    do {
      final json = await _getJson(
        '${ApiConfig.transactionIndex}?per_page=200&page=$page',
        fallbackMessage: 'Gagal memuat total unit terjual',
      );

      final paginated = _asMap(json['data']);
      final rows = _extractList(paginated['data']);
      if (rows.isEmpty) break;

      for (final row in rows) {
        final trx = _asMap(row);
        if (_isFailedTransaction(trx)) continue;

        final items = _extractList(trx['items']);
        for (final item in items) {
          totalUnits += _toInt(_asMap(item)['qty']);
        }
      }

      page = _toInt(paginated['current_page'], fallback: page);
      lastPage = _toInt(paginated['last_page'], fallback: page);
      if (page >= lastPage) break;
      page += 1;
    } while (page <= lastPage);

    return totalUnits;
  }

  static bool _isFailedTransaction(Map<String, dynamic> trx) {
    final status = (trx['status'] ?? '').toString().toLowerCase().trim();
    return _failedTransactionStatuses.contains(status);
  }

  static Future<Map<String, dynamic>> _getJson(
    String url, {
    required String fallbackMessage,
  }) async {
    try {
      final response = await http
          .get(Uri.parse(url), headers: await AuthService.authHeaders())
          .timeout(_requestTimeout);

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

      if (body.isEmpty) {
        throw Exception(fallbackMessage);
      }

      final success = body['success'];
      if (success is bool && !success) {
        throw Exception(
          _buildErrorMessage(body, fallbackMessage, response.statusCode),
        );
      }

      return body;
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

  static Future<Map<String, dynamic>?> _tryGetJson(String url) async {
    try {
      return await _getJson(url, fallbackMessage: 'Gagal mengambil data');
    } catch (_) {
      return null;
    }
  }

  static String _buildErrorMessage(
    Map<String, dynamic> body,
    String fallbackMessage,
    int statusCode,
  ) {
    final message = body['message']?.toString().trim();
    final error = body['error']?.toString().trim();

    if (error != null && error.isNotEmpty) return error;
    if (message != null && message.isNotEmpty) return message;
    return '$fallbackMessage (HTTP $statusCode)';
  }

  static Map<String, dynamic> _serializeDashboardData(
    DashboardGudangData data,
  ) {
    return {
      'summary': {
        'total_produk': data.summary.totalProduk,
        'stok_rendah': data.summary.stokRendah,
        'nilai_inventori': data.summary.nilaiInventori,
        'total_terjual_unit': data.summary.totalTerjualUnit,
        'total_pendapatan': data.summary.totalPendapatan,
        'rata_rata_harga_per_item': data.summary.rataRataHargaPerItem,
      },
      'low_stock_items': data.lowStockItems
          .map(
            (e) => {
              'name': e.namaProduk,
              'category_name': e.kategori,
              'stock': e.stok,
              'min_stock': e.stokMinimum,
            },
          )
          .toList(growable: false),
      'category_items': data.categoryItems
          .map(
            (e) => {
              'name': e.namaKategori,
              'products_count': e.totalProduk,
              'is_active': e.aktif,
            },
          )
          .toList(growable: false),
      'stock_updates': data.stockUpdates
          .map(
            (e) => {
              'name': e.namaProduk,
              'category_name': e.kategori,
              'stock': e.stok,
              'updated_at': e.updatedAt?.toUtc().toIso8601String(),
            },
          )
          .toList(growable: false),
    };
  }

  static DashboardGudangData _deserializeDashboardData(
    Map<String, dynamic> payload,
  ) {
    final summary = _asMap(payload['summary']);
    return DashboardGudangData(
      summary: DashboardGudangSummary(
        totalProduk: _toInt(summary['total_produk']),
        stokRendah: _toInt(summary['stok_rendah']),
        nilaiInventori: _toDouble(summary['nilai_inventori']),
        totalTerjualUnit: _toInt(summary['total_terjual_unit']),
        totalPendapatan: _toDouble(summary['total_pendapatan']),
        rataRataHargaPerItem: _toDouble(summary['rata_rata_harga_per_item']),
      ),
      lowStockItems: _extractList(payload['low_stock_items'])
          .map((e) => DashboardGudangLowStockItem.fromJson(_asMap(e)))
          .toList(growable: false),
      categoryItems: _extractList(payload['category_items'])
          .map((e) => DashboardGudangCategoryItem.fromJson(_asMap(e)))
          .toList(growable: false),
      stockUpdates: _extractList(payload['stock_updates'])
          .map((e) => DashboardGudangStockUpdateItem.fromJson(_asMap(e)))
          .toList(growable: false),
    );
  }
}

Map<String, dynamic> _asMap(dynamic raw) {
  if (raw is Map<String, dynamic>) return raw;
  if (raw is Map) {
    return raw.map((key, value) => MapEntry(key.toString(), value));
  }
  return <String, dynamic>{};
}

List<dynamic> _extractList(dynamic raw) {
  if (raw is List) return raw;
  if (raw is Map<String, dynamic>) {
    for (final key in ['data', 'items', 'products', 'categories']) {
      final nested = raw[key];
      if (nested is List) return nested;
    }
  }
  return const [];
}

int _toInt(dynamic value, {int fallback = 0}) {
  if (value is int) return value;
  if (value is num) return value.toInt();
  return int.tryParse(value?.toString() ?? '') ?? fallback;
}

double _toDouble(dynamic value, {double fallback = 0}) {
  if (value is num) return value.toDouble();
  return double.tryParse(value?.toString() ?? '') ?? fallback;
}

bool _toBool(dynamic value, {required bool defaultValue}) {
  if (value == null) return defaultValue;
  if (value is bool) return value;
  if (value is num) return value != 0;

  final raw = value.toString().trim().toLowerCase();
  if (raw == 'true' || raw == '1' || raw == 'yes' || raw == 'aktif') {
    return true;
  }
  if (raw == 'false' || raw == '0' || raw == 'no' || raw == 'nonaktif') {
    return false;
  }
  return defaultValue;
}

DateTime? _parseDate(dynamic value) {
  if (value == null) return null;
  final raw = value.toString().trim();
  if (raw.isEmpty) return null;
  return DateTime.tryParse(raw);
}
