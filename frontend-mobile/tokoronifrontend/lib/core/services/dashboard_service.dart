// ============================================================
// lib/core/dashboard_service.dart
//
// Disesuaikan dengan DashboardApiController.php yang baru.
//
// FORMAT RESPONSE getDashboardStats (role owner/admin):
// {
//   "success": true,
//   "data": {
//     "user": { "name": "...", "role": "owner" },
//     "transactions": {
//       "today": { "count": 5, "amount": 500000 },
//       "this_month": { "count": 120, "amount": 12000000 }
//     },
//     "products": {
//       "total": 285, "low_stock": 7, "out_of_stock": 2,
//       "active": 280, "total_value": 50000000
//     },
//     "members": { "total": 45, "active": 40 },
//     "users":   { "total": 15, "active": 14 }
//   }
// }
//
// FORMAT RESPONSE getChartData:
// {
//   "success": true,
//   "data": {
//     "labels": ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"],
//     "datasets": [
//       { "label": "Transactions", "data": [5, 3, 8, 4, 6, 2, 7] },
//       { "label": "Revenue",      "data": [500000, 300000, ...] },
//       { "label": "Deliveries",   "data": [2, 1, 3, 2, 1, 0, 2] }
//     ]
//   }
// }
//
// FORMAT RESPONSE transactions/recent & products/low-stock:
// { "success": true, "data": [ {...}, {...} ] }
// ============================================================

import 'dart:async';
import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;
import 'auth_service.dart';
import '../config/api_config.dart';

// ── Model: Summary Stats ──────────────────────────────────────────────────────
class DashboardStats {
  final int totalKaryawan;
  final int totalProduk;
  final int stokHampirHabis;
  final int akanKadaluarsa;
  final int stokNormal;
  final int stokKritis;

  const DashboardStats({
    this.totalKaryawan = 0,
    this.totalProduk = 0,
    this.stokHampirHabis = 0,
    this.akanKadaluarsa = 0,
    this.stokNormal = 0,
    this.stokKritis = 0,
  });

  // Parsing dari response DashboardApiController.getDashboardStats()
  // data bisa berisi role-based nested object
  factory DashboardStats.fromJson(Map<String, dynamic> raw) {
    // Unwrap root 'data' jika ada
    final root = (raw['data'] as Map<String, dynamic>?) ?? raw;

    // ── Products block ────────────────────────────────────────
    final prod = (root['products'] as Map<String, dynamic>?) ?? {};
    final totalProduk = _i(prod, ['total', 'total_products']);
    final lowStock = _i(prod, [
      'low_stock',
      'low_stock_count',
      'stok_hampir_habis',
    ]);
    final outOfStock = _i(prod, [
      'out_of_stock',
      'out_of_stock_count',
      'stok_kritis',
    ]);
    final normalCalc = totalProduk - lowStock - outOfStock;

    // ── Users block ───────────────────────────────────────────
    final users = (root['users'] as Map<String, dynamic>?) ?? {};
    final totalKaryawan = _i(users, ['total', 'total_users', 'total_karyawan']);

    // ── Expiring (jika ada di response) ──────────────────────
    final expiring = _i(root, [
      'akan_kadaluarsa',
      'expiring_count',
      'expiring_soon',
    ]);

    return DashboardStats(
      totalKaryawan: totalKaryawan,
      totalProduk: totalProduk,
      stokHampirHabis: lowStock,
      akanKadaluarsa: expiring,
      stokNormal: normalCalc < 0 ? 0 : normalCalc,
      stokKritis: outOfStock,
    );
  }

  static int _i(Map<String, dynamic> d, List<String> keys) {
    for (final k in keys) {
      if (d[k] != null) return (d[k] as num).toInt();
    }
    return 0;
  }
}

// ── Model: Stok Menipis ───────────────────────────────────────────────────────
class StokMenipisItem {
  final String produk;
  final String kategori;
  final int stokMin;
  final int sisaStok;

  const StokMenipisItem({
    required this.produk,
    required this.kategori,
    required this.stokMin,
    required this.sisaStok,
  });

  factory StokMenipisItem.fromJson(Map<String, dynamic> d) {
    // ProductApiController bisa return 'category' object atau 'category_name' string
    final cat = d['category'] as Map<String, dynamic>?;
    return StokMenipisItem(
      produk: d['name']?.toString() ?? d['product_name']?.toString() ?? '-',
      kategori:
          cat?['name']?.toString() ??
          d['category_name']?.toString() ??
          d['kategori']?.toString() ??
          '-',
      stokMin: (d['min_stock'] ?? d['minimum_stock'] ?? 10 as num).toInt(),
      sisaStok: (d['stock'] ?? d['current_stock'] ?? d['stok'] ?? 0 as num)
          .toInt(),
    );
  }
}

// ── Model: Produk Kadaluarsa ──────────────────────────────────────────────────
class KadaluarsaItem {
  final String produk;
  final String kategori;
  final int stok;
  final String tanggalKadaluarsa;
  final String sisaHari;
  final bool isExpired;

  const KadaluarsaItem({
    required this.produk,
    required this.kategori,
    required this.stok,
    required this.tanggalKadaluarsa,
    required this.sisaHari,
    required this.isExpired,
  });

  factory KadaluarsaItem.fromJson(Map<String, dynamic> d) {
    final cat = d['category'] as Map<String, dynamic>?;
    final daysLeft = d['days_left'] ?? d['sisa_hari'];
    final expired =
        d['is_expired'] == true || (daysLeft is num && daysLeft <= 0);

    String tglFormatted = '-';
    final rawTgl = d['expiry_date'] ?? d['tanggal_kadaluarsa'] ?? d['expiry'];
    if (rawTgl != null) {
      try {
        final dt = DateTime.parse(rawTgl.toString());
        const m = [
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
        tglFormatted =
            '${dt.day.toString().padLeft(2, '0')}-${m[dt.month]}-${dt.year}';
      } catch (_) {
        tglFormatted = rawTgl.toString();
      }
    }

    return KadaluarsaItem(
      produk: d['name']?.toString() ?? d['product_name']?.toString() ?? '-',
      kategori:
          cat?['name']?.toString() ?? d['category_name']?.toString() ?? '-',
      stok: (d['stock'] ?? d['stok'] ?? 0 as num).toInt(),
      tanggalKadaluarsa: tglFormatted,
      sisaHari: expired ? 'expired' : '${daysLeft ?? '-'} hari',
      isExpired: expired,
    );
  }
}

// ── Model: Transaksi ──────────────────────────────────────────────────────────
class TransaksiItem {
  final String id;
  final String produk;
  final String waktu;
  final String total;
  final bool isSuccess;

  const TransaksiItem({
    required this.id,
    required this.produk,
    required this.waktu,
    required this.total,
    required this.isSuccess,
  });

  factory TransaksiItem.fromJson(Map<String, dynamic> d) {
    // Format tanggal
    String waktuFormatted = '-';
    final rawTgl = d['created_at'] ?? d['date'] ?? d['tanggal'];
    if (rawTgl != null) {
      try {
        final dt = DateTime.parse(rawTgl.toString());
        const m = [
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
        waktuFormatted = '${dt.day} ${m[dt.month]} ${dt.year}';
      } catch (_) {
        waktuFormatted = rawTgl.toString();
      }
    }

    // Format rupiah
    final rawTotal = d['total_amount'] ?? d['amount'] ?? d['total'] ?? 0;
    final totalFormatted = rawTotal is num
        ? 'Rp ${_fmtRupiah(rawTotal.toInt())}'
        : rawTotal.toString();

    // Status transaksi (bukan status pembayaran):
    // - Dukung payload baru: is_success / transaction_status
    // - Kompatibel payload lama: status = pending untuk transaksi kredit
    final status = d['status']?.toString().toLowerCase().trim() ?? '';
    final paymentStatus =
        d['payment_status']?.toString().toLowerCase().trim() ?? '';
    final transactionStatus =
        d['transaction_status']?.toString().toLowerCase().trim() ?? '';
    final successFlag = d['is_success'];

    bool success;
    if (successFlag is bool) {
      success = successFlag;
    } else {
      final failedTokens = {'failed', 'gagal', 'cancelled', 'canceled', 'void'};
      final successTokens = {
        'success',
        'completed',
        'lunas',
        'paid',
        'pending',
        'processing',
        'belum lunas',
        '1',
      };

      if (failedTokens.contains(transactionStatus) ||
          failedTokens.contains(status)) {
        success = false;
      } else if (successTokens.contains(transactionStatus) ||
          successTokens.contains(status) ||
          successTokens.contains(paymentStatus)) {
        success = true;
      } else {
        // Hindari false-negative untuk transaksi kredit pada payload lama.
        success = true;
      }
    }

    return TransaksiItem(
      id:
          d['invoice_number']?.toString() ??
          d['invoice']?.toString() ??
          d['id']?.toString() ??
          '-',
      produk: d['product_name']?.toString() ?? d['produk']?.toString() ?? '-',
      waktu: waktuFormatted,
      total: totalFormatted,
      isSuccess: success,
    );
  }

  static String _fmtRupiah(int n) {
    final s = n.toString();
    final buf = StringBuffer();
    for (int i = 0; i < s.length; i++) {
      if (i > 0 && (s.length - i) % 3 == 0) buf.write('.');
      buf.write(s[i]);
    }
    return buf.toString();
  }
}

// ── Model: Chart ──────────────────────────────────────────────────────────────
class ChartData {
  final List<String> labels;
  final List<double> penjualan; // revenue dalam jutaan rupiah
  final List<double> stokKeluar; // jumlah transaksi (proxy stok keluar)

  const ChartData({
    required this.labels,
    required this.penjualan,
    required this.stokKeluar,
  });

  bool get isEmpty => labels.isEmpty;
}

// ════════════════════════════════════════════════════════════════════════════
// DASHBOARD SERVICE
// ════════════════════════════════════════════════════════════════════════════
class DashboardService {
  static const Duration _requestTimeout = Duration(seconds: 15);

  // ── Stats ─────────────────────────────────────────────────────────────────
  static Future<DashboardStats> getStats() async {
    final json = await _getJson(
      ApiConfig.dashboardStats,
      fallbackMessage: 'Gagal memuat statistik dashboard',
    );
    return DashboardStats.fromJson(json);
  }

  // ── Stok menipis ─────────────────────────────────────────────────────────
  static Future<List<StokMenipisItem>> getLowStockProducts({
    int limit = 5,
  }) async {
    final safeLimit = limit <= 0 ? 5 : limit;
    final json = await _getJson(
      '${ApiConfig.productLowStock}?limit=$safeLimit',
      fallbackMessage: 'Gagal memuat data stok menipis',
    );
    final list = _extractList(json['data']);
    return list
        .map((e) => StokMenipisItem.fromJson(_asMap(e)))
        .take(safeLimit)
        .toList();
  }

  // ── Produk akan kadaluarsa ────────────────────────────────────────────────
  // Menggunakan endpoint getDashboardStats — field expiring ada di alerts
  // atau di notifications. Kalau belum ada, return list kosong.
  static Future<List<KadaluarsaItem>> getExpiringProducts({
    int limit = 5,
  }) async {
    final safeLimit = limit <= 0 ? 5 : limit;
    final json = await _getJson(
      ApiConfig.dashboardStats,
      fallbackMessage: 'Gagal memuat data produk kadaluarsa',
    );
    final data = _asMap(json['data']);
    final list = _extractList(data['expiring_products']);
    return list
        .map((e) => KadaluarsaItem.fromJson(_asMap(e)))
        .take(safeLimit)
        .toList();
  }

  // ── Transaksi terbaru ────────────────────────────────────────────────────
  static Future<List<TransaksiItem>> getRecentTransactions({
    int limit = 5,
  }) async {
    final safeLimit = limit <= 0 ? 5 : limit;
    final json = await _getJson(
      '${ApiConfig.transactionsRecent}?limit=$safeLimit',
      fallbackMessage: 'Gagal memuat transaksi terbaru',
    );
    final list = _extractList(json['data']);
    return list
        .map((e) => TransaksiItem.fromJson(_asMap(e)))
        .take(safeLimit)
        .toList();
  }

  // ── Chart penjualan & stok keluar ────────────────────────────────────────
  // DashboardApiController.getChartData() pakai param:
  //   ?period=week | month | year
  //   ?type=all | transactions | revenue | deliveries
  //
  // Response: { labels:[], datasets:[ {label:'Revenue', data:[]}, ... ] }
  //
  // Flutter mapping:
  //   penjualan  ← dataset label 'Revenue'  (konversi ke jutaan)
  //   stokKeluar ← dataset label 'Transactions' (jumlah transaksi)
  static Future<ChartData?> getChartData(String filter) async {
    try {
      final period = _filterToPeriod(filter);
      final json = await _getJson(
        '${ApiConfig.dashboardChart}?period=$period',
        fallbackMessage: 'Gagal memuat data grafik dashboard',
      );
      final data = (json['data'] as Map<String, dynamic>?) ?? json;

      final labels =
          (data['labels'] as List?)?.map((e) => e.toString()).toList() ?? [];

      // Backend kirim rupiah mentah — TIDAK dikonversi ke jutaan
      // Flutter handle format label di Y-axis secara dinamis
      final penjualan =
          (data['penjualan'] as List?)
              ?.map((e) => (e as num).toDouble())
              .toList() ??
          [];

      final stokKeluar =
          (data['stok_keluar'] as List?)
              ?.map((e) => (e as num).toDouble())
              .toList() ??
          [];

      if (labels.isNotEmpty) {
        return ChartData(
          labels: labels,
          penjualan: penjualan.isEmpty
              ? List.filled(labels.length, 0.0)
              : penjualan,
          stokKeluar: stokKeluar.isEmpty
              ? List.filled(labels.length, 0.0)
              : stokKeluar,
        );
      }
    } catch (_) {}
    return null;
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
        if (decoded is Map<String, dynamic>) {
          body = decoded;
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
      final nested = raw['data'];
      if (nested is List) return nested;
    }
    return const [];
  }

  // ── Helper: filter Flutter → period Laravel ──────────────────────────────
  static String _filterToPeriod(String filter) {
    switch (filter) {
      case '30 Hari':
        return 'month';
      case '90 Hari':
        return 'year';
      default:
        return 'week'; // '7 Hari'
    }
  }
}
