import 'dart:async';
import 'dart:convert';
import 'dart:io';

import 'package:http/http.dart' as http;

import '../config/api_config.dart';
import '../offline/dashboard_cache_repository.dart';
import '../offline/offline_utils.dart';
import 'auth_service.dart';

class DashboardKasirSummary {
  final int totalTransactionsToday;
  final double revenueToday;
  final double averageTransactionToday;
  final int soldProductsToday;

  const DashboardKasirSummary({
    this.totalTransactionsToday = 0,
    this.revenueToday = 0,
    this.averageTransactionToday = 0,
    this.soldProductsToday = 0,
  });
}

class DashboardKasirTransaction {
  final String invoiceNumber;
  final String productName;
  final DateTime? createdAt;
  final double totalAmount;
  final bool isSuccess;

  const DashboardKasirTransaction({
    required this.invoiceNumber,
    required this.productName,
    required this.createdAt,
    required this.totalAmount,
    required this.isSuccess,
  });

  String get waktuLabel {
    final dt = createdAt;
    if (dt == null) return '-';

    const months = [
      '',
      'Jan',
      'Feb',
      'Mar',
      'Apr',
      'Mei',
      'Jun',
      'Jul',
      'Agu',
      'Sep',
      'Okt',
      'Nov',
      'Des',
    ];

    final local = dt.toLocal();
    final hour = local.hour.toString().padLeft(2, '0');
    final minute = local.minute.toString().padLeft(2, '0');
    return '${local.day.toString().padLeft(2, '0')} ${months[local.month]} ${local.year}, $hour:$minute';
  }

  String get totalLabel => 'Rp ${_formatThousands(totalAmount.round())}';

  factory DashboardKasirTransaction.fromJson(Map<String, dynamic> json) {
    final successFlag = json['is_success'];

    final transactionStatus = (json['transaction_status'] ?? '')
        .toString()
        .toLowerCase()
        .trim();
    final status = (json['status'] ?? '').toString().toLowerCase().trim();

    bool isSuccess;
    if (successFlag is bool) {
      isSuccess = successFlag;
    } else {
      const failedTokens = {'failed', 'gagal', 'cancelled', 'canceled', 'void'};
      isSuccess =
          !failedTokens.contains(transactionStatus) &&
          !failedTokens.contains(status);
    }

    return DashboardKasirTransaction(
      invoiceNumber:
          (json['invoice_number'] ?? json['invoice'] ?? json['id'] ?? '-')
              .toString(),
      productName: (json['product_name'] ?? json['produk'] ?? 'Berbagai produk')
          .toString(),
      createdAt: _parseDate(
        json['created_at'] ?? json['date'] ?? json['tanggal'],
      ),
      totalAmount: _toDouble(json['total_amount'] ?? json['total'] ?? 0),
      isSuccess: isSuccess,
    );
  }

  static DateTime? _parseDate(dynamic raw) {
    if (raw == null) return null;
    return DateTime.tryParse(raw.toString());
  }

  static double _toDouble(dynamic value) {
    if (value is num) return value.toDouble();
    return double.tryParse(value?.toString() ?? '') ?? 0;
  }

  static String _formatThousands(int value) {
    final text = value.toString();
    final buffer = StringBuffer();

    for (int i = 0; i < text.length; i++) {
      if (i > 0 && (text.length - i) % 3 == 0) buffer.write('.');
      buffer.write(text[i]);
    }
    return buffer.toString();
  }
}

class DashboardKasirService {
  static const Duration _requestTimeout = Duration(seconds: 15);
  static const Set<String> _failedTransactionTokens = {
    'failed',
    'gagal',
    'cancelled',
    'canceled',
    'void',
  };

  static Future<DashboardKasirSummary> getSummary() async {
    try {
      final statsJson = await _getJson(
        ApiConfig.dashboardStats,
        fallbackMessage: 'Gagal memuat ringkasan dashboard kasir',
      );

      final statsData = _asMap(statsJson['data']);
      final transactions = _asMap(statsData['transactions']);
      final today = _asMap(transactions['today']);

      final fallbackCount = _toInt(today['count']);
      final fallbackAmount = _toDouble(today['amount']);

      final todayStatsJson = await _tryGetJson(
        '${ApiConfig.transactionIndex}/today-stats',
      );
      final todayStatsData = _asMap(todayStatsJson?['data']);

      final totalTransactions = _toInt(
        todayStatsData['total_transactions'],
        fallback: fallbackCount,
      );

      final revenue = _toDouble(
        todayStatsData['total_amount'],
        fallback: fallbackAmount,
      );

      double average = _toDouble(todayStatsData['average_transaction']);
      if (average <= 0 && totalTransactions > 0) {
        average = revenue / totalTransactions;
      }

      int soldProducts = _toInt(
        todayStatsData['sold_products'],
        fallback: _toInt(todayStatsData['total_items_sold']),
      );

      if (soldProducts <= 0) {
        soldProducts = await _getTodaySoldProductsFromTransactions();
      }

      if (soldProducts <= 0) {
        final chartJson = await _tryGetJson(
          '${ApiConfig.dashboardChart}?period=week',
        );
        soldProducts = _extractTodaySoldProducts(chartJson);
      }

      final summary = DashboardKasirSummary(
        totalTransactionsToday: totalTransactions,
        revenueToday: revenue,
        averageTransactionToday: average,
        soldProductsToday: soldProducts,
      );
      await DashboardCacheRepository.instance.saveKasir({
        'summary': {
          'total_transactions_today': summary.totalTransactionsToday,
          'revenue_today': summary.revenueToday,
          'average_transaction_today': summary.averageTransactionToday,
          'sold_products_today': summary.soldProductsToday,
        },
      });
      return summary;
    } catch (error) {
      if (!isNetworkReachabilityError(error)) rethrow;
      final cached = await DashboardCacheRepository.instance.getKasir();
      final summary = _asMap(cached['summary']);
      if (summary.isEmpty) rethrow;
      return DashboardKasirSummary(
        totalTransactionsToday: _toInt(summary['total_transactions_today']),
        revenueToday: _toDouble(summary['revenue_today']),
        averageTransactionToday: _toDouble(summary['average_transaction_today']),
        soldProductsToday: _toInt(summary['sold_products_today']),
      );
    }
  }

  static Future<List<DashboardKasirTransaction>> getRecentTransactions({
    int limit = 5,
  }) async {
    final safeLimit = limit <= 0 ? 5 : limit;
    try {
      final json = await _getJson(
        '${ApiConfig.transactionsRecent}?limit=$safeLimit',
        fallbackMessage: 'Gagal memuat transaksi terbaru',
      );

      final list = _extractList(json['data']);
      final rows = list.map(_asMap).toList(growable: false);
      final cached = await DashboardCacheRepository.instance.getKasir();
      await DashboardCacheRepository.instance.saveKasir({
        ...cached,
        'recent_transactions': rows,
      });
      return rows
          .map((item) => DashboardKasirTransaction.fromJson(item))
          .take(safeLimit)
          .toList(growable: false);
    } catch (error) {
      if (!isNetworkReachabilityError(error)) rethrow;
      final cached = await DashboardCacheRepository.instance.getKasir();
      final rows = _extractList(cached['recent_transactions']);
      if (rows.isEmpty) rethrow;
      return rows
          .map((item) => DashboardKasirTransaction.fromJson(_asMap(item)))
          .take(safeLimit)
          .toList(growable: false);
    }
  }

  static int _extractTodaySoldProducts(Map<String, dynamic>? chartJson) {
    if (chartJson == null) return 0;
    final data = _asMap(chartJson['data']);
    final rawStokKeluar = data['stok_keluar'];

    if (rawStokKeluar is List && rawStokKeluar.isNotEmpty) {
      return _toInt(rawStokKeluar.last);
    }
    return 0;
  }

  static Future<int> _getTodaySoldProductsFromTransactions() async {
    final now = DateTime.now();
    final startOfToday = DateTime(now.year, now.month, now.day);

    int totalQty = 0;
    int currentPage = 1;
    int lastPage = 1;

    do {
      final json = await _tryGetJson(
        '${ApiConfig.transactionIndex}?per_page=200&page=$currentPage',
      );
      if (json == null) break;

      final paginated = _asMap(json['data']);
      final rows = _extractList(paginated['data']);
      if (rows.isEmpty) break;

      bool reachedOlderData = false;

      for (final row in rows) {
        final trx = _asMap(row);
        if (trx.isEmpty) continue;

        final createdAt = _parseDateTime(trx['created_at']);
        if (createdAt == null) continue;

        final localDate = createdAt.toLocal();
        if (_isSameDate(localDate, now)) {
          if (_isFailedTransaction(trx)) continue;

          final items = _extractList(trx['items']);
          for (final item in items) {
            totalQty += _toInt(_asMap(item)['qty']);
          }
        } else if (localDate.isBefore(startOfToday)) {
          // Urutan data terbaru dulu, begitu lewat hari ini kita bisa stop.
          reachedOlderData = true;
        }
      }

      currentPage = _toInt(paginated['current_page'], fallback: currentPage);
      lastPage = _toInt(paginated['last_page'], fallback: currentPage);

      if (reachedOlderData || currentPage >= lastPage) {
        break;
      }

      currentPage += 1;
    } while (currentPage <= lastPage);

    return totalQty;
  }

  static bool _isFailedTransaction(Map<String, dynamic> trx) {
    final status = (trx['status'] ?? trx['transaction_status'] ?? '')
        .toString()
        .toLowerCase()
        .trim();
    return _failedTransactionTokens.contains(status);
  }

  static bool _isSameDate(DateTime left, DateTime right) {
    return left.year == right.year &&
        left.month == right.month &&
        left.day == right.day;
  }

  static DateTime? _parseDateTime(dynamic value) {
    if (value == null) return null;
    final raw = value.toString().trim();
    if (raw.isEmpty) return null;
    return DateTime.tryParse(raw);
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

  static Map<String, dynamic> _asMap(dynamic value) {
    if (value is Map<String, dynamic>) return value;
    if (value is Map) {
      return value.map((key, val) => MapEntry(key.toString(), val));
    }
    return <String, dynamic>{};
  }

  static List<dynamic> _extractList(dynamic value) {
    if (value is List) return value;
    if (value is Map<String, dynamic>) {
      final nested = value['data'];
      if (nested is List) return nested;
    }
    return const [];
  }

  static int _toInt(dynamic value, {int fallback = 0}) {
    if (value is int) return value;
    if (value is num) return value.toInt();
    return int.tryParse(value?.toString() ?? '') ?? fallback;
  }

  static double _toDouble(dynamic value, {double fallback = 0}) {
    if (value is num) return value.toDouble();
    return double.tryParse(value?.toString() ?? '') ?? fallback;
  }
}
