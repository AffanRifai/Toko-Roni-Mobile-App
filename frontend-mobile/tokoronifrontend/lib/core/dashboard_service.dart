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

import 'dart:convert';
import 'package:http/http.dart' as http;
import 'auth_service.dart';
import 'api_config.dart';

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

    // Status: bisa 'LUNAS'/'lunas'/'success'/'completed'
    final status = d['status']?.toString().toLowerCase() ?? '';
    final success =
        status == 'success' ||
        status == 'completed' ||
        status == 'lunas' ||
        status == '1';

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
  // ── Stats ─────────────────────────────────────────────────────────────────
  static Future<DashboardStats> getStats() async {
    try {
      final res = await http
          .get(
            Uri.parse(ApiConfig.dashboardStats),
            headers: await AuthService.authHeaders(),
          )
          .timeout(const Duration(seconds: 15));

      if (res.statusCode == 200) {
        final json = jsonDecode(res.body) as Map<String, dynamic>;
        if (json['success'] == true) {
          return DashboardStats.fromJson(json);
        }
      }
    } catch (_) {}
    return const DashboardStats();
  }

  // ── Stok menipis ─────────────────────────────────────────────────────────
  static Future<List<StokMenipisItem>> getLowStockProducts() async {
    try {
      final res = await http
          .get(
            Uri.parse(ApiConfig.productLowStock),
            headers: await AuthService.authHeaders(),
          )
          .timeout(const Duration(seconds: 15));

      if (res.statusCode == 200) {
        final json = jsonDecode(res.body) as Map<String, dynamic>;
        if (json['success'] == true) {
          final list = (json['data'] as List?) ?? [];
          return list
              .map((e) => StokMenipisItem.fromJson(e as Map<String, dynamic>))
              .toList();
        }
      }
    } catch (_) {}
    return [];
  }

  // ── Produk akan kadaluarsa ────────────────────────────────────────────────
  // Menggunakan endpoint getDashboardStats — field expiring ada di alerts
  // atau di notifications. Kalau belum ada, return list kosong.
  static Future<List<KadaluarsaItem>> getExpiringProducts() async {
    try {
      // Ambil dari endpoint stats — DashboardApiController sekarang
      // menyertakan 'expiring_products' list di dalam response data
      final res = await http
          .get(
            Uri.parse(ApiConfig.dashboardStats),
            headers: await AuthService.authHeaders(),
          )
          .timeout(const Duration(seconds: 15));

      if (res.statusCode == 200) {
        final json = jsonDecode(res.body) as Map<String, dynamic>;
        if (json['success'] == true) {
          final data = (json['data'] as Map<String, dynamic>?) ?? {};
          final list = (data['expiring_products'] as List?);
          if (list != null && list.isNotEmpty) {
            return list
                .map((e) => KadaluarsaItem.fromJson(e as Map<String, dynamic>))
                .toList();
          }
        }
      }
    } catch (_) {}
    return [];
  }

  // ── Transaksi terbaru ────────────────────────────────────────────────────
  static Future<List<TransaksiItem>> getRecentTransactions() async {
    try {
      final res = await http
          .get(
            Uri.parse(ApiConfig.transactionsRecent),
            headers: await AuthService.authHeaders(),
          )
          .timeout(const Duration(seconds: 15));

      if (res.statusCode == 200) {
        final json = jsonDecode(res.body) as Map<String, dynamic>;
        if (json['success'] == true) {
          final list = (json['data'] as List?) ?? [];
          return list
              .map((e) => TransaksiItem.fromJson(e as Map<String, dynamic>))
              .toList();
        }
      }
    } catch (_) {}
    return [];
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

      final res = await http
          .get(
            Uri.parse('${ApiConfig.dashboardChart}?period=$period'),
            headers: await AuthService.authHeaders(),
          )
          .timeout(const Duration(seconds: 15));

      if (res.statusCode == 200) {
        final json = jsonDecode(res.body) as Map<String, dynamic>;
        if (json['success'] == true) {
          final data = (json['data'] as Map<String, dynamic>?) ?? json;

          final labels =
              (data['labels'] as List?)?.map((e) => e.toString()).toList() ??
              [];

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
        }
      }
    } catch (_) {}
    return null;
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
