// ============================================================
// lib/core/dashboard_service.dart
//
// CATATAN STRUKTUR RESPONSE YANG DIHARAPKAN DARI BACKEND:
//
// GET /api/v1/dashboard/stats
//   { "data": { "total_karyawan": 15, "total_produk": 285,
//               "stok_hampir_habis": 7, "akan_kadaluarsa": 23,
//               "stok_normal": 270, "stok_kritis": 2 } }
//
// GET /api/v1/products/low-stock
//   { "data": [ { "name": "Aqua 1L", "category": {"name":"Minuman"},
//                 "min_stock": 20, "stock": 5 }, ... ] }
//
// GET /api/v1/dashboard/notifications
//   { "data": { "expiring": [ { "name": "Indomie",
//               "category": {"name":"Makanan"}, "stock": 192,
//               "expiry_date": "2026-06-01", "days_left": 77 }, ... ] } }
//
// GET /api/v1/transactions/recent
//   { "data": [ { "invoice_number": "INV001", "product_name": "Sukro",
//                 "created_at": "2026-02-10T00:00:00Z",
//                 "total_amount": 13000, "status": "success" }, ... ] }
//
// GET /api/v1/dashboard/chart-data?days=7
//   { "data": { "labels": ["Senin",...],
//               "penjualan": [1200000,...],
//               "stok_keluar": [20,...] } }
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
    this.totalKaryawan    = 0,
    this.totalProduk      = 0,
    this.stokHampirHabis  = 0,
    this.akanKadaluarsa   = 0,
    this.stokNormal       = 0,
    this.stokKritis       = 0,
  });

  factory DashboardStats.fromJson(Map<String, dynamic> raw) {
    final d           = (raw['data'] as Map<String, dynamic>?) ?? raw;
    final totalProduk  = _i(d, ['total_produk', 'total_products']);
    final hampirHabis  = _i(d, ['stok_hampir_habis', 'low_stock_count']);
    final kritis       = _i(d, ['stok_kritis', 'critical_stock_count']);
    final normalCalc   = totalProduk - hampirHabis - kritis;
    return DashboardStats(
      totalKaryawan   : _i(d, ['total_karyawan', 'total_employees']),
      totalProduk     : totalProduk,
      stokHampirHabis : hampirHabis,
      akanKadaluarsa  : _i(d, ['akan_kadaluarsa', 'expiring_count']),
      stokNormal      : _i(d, ['stok_normal']) != 0 ? _i(d, ['stok_normal']) : (normalCalc < 0 ? 0 : normalCalc),
      stokKritis      : kritis,
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
    final cat = d['category'] as Map<String, dynamic>?;
    return StokMenipisItem(
      produk   : d['name']?.toString() ?? d['product_name']?.toString() ?? '-',
      kategori : cat?['name']?.toString() ?? d['category_name']?.toString() ?? '-',
      stokMin  : (d['min_stock'] ?? d['minimum_stock'] ?? 0 as num).toInt(),
      sisaStok : (d['stock'] ?? d['current_stock'] ?? d['stok'] ?? 0 as num).toInt(),
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
    final cat      = d['category'] as Map<String, dynamic>?;
    final daysLeft = d['days_left'] ?? d['sisa_hari'];
    final expired  = d['is_expired'] == true ||
        (daysLeft is num && daysLeft <= 0);

    String tglFormatted = '-';
    final rawTgl = d['expiry_date'] ?? d['tanggal_kadaluarsa'] ?? d['expiry'];
    if (rawTgl != null) {
      try {
        final dt = DateTime.parse(rawTgl.toString());
        const m = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        tglFormatted = '${dt.day.toString().padLeft(2,'0')}-${m[dt.month]}-${dt.year}';
      } catch (_) {
        tglFormatted = rawTgl.toString();
      }
    }

    return KadaluarsaItem(
      produk            : d['name']?.toString() ?? d['product_name']?.toString() ?? '-',
      kategori          : cat?['name']?.toString() ?? d['category_name']?.toString() ?? '-',
      stok              : (d['stock'] ?? d['stok'] ?? 0 as num).toInt(),
      tanggalKadaluarsa : tglFormatted,
      sisaHari          : expired ? 'expired' : '${daysLeft ?? '-'} hari',
      isExpired         : expired,
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
    String waktuFormatted = '-';
    final rawTgl = d['created_at'] ?? d['date'] ?? d['tanggal'];
    if (rawTgl != null) {
      try {
        final dt = DateTime.parse(rawTgl.toString());
        const m = ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        waktuFormatted = '${dt.day} ${m[dt.month]} ${dt.year}';
      } catch (_) {
        waktuFormatted = rawTgl.toString();
      }
    }

    final rawTotal = d['total_amount'] ?? d['total'] ?? d['grand_total'] ?? 0;
    String totalFormatted;
    if (rawTotal is num) {
      totalFormatted = 'Rp ${_fmt(rawTotal.toInt())}';
    } else {
      totalFormatted = rawTotal.toString();
    }

    final status = d['status']?.toString().toLowerCase() ?? '';
    return TransaksiItem(
      id       : d['invoice_number']?.toString() ?? d['invoice']?.toString() ?? d['id']?.toString() ?? '-',
      produk   : d['product_name']?.toString() ?? d['produk']?.toString() ?? '-',
      waktu    : waktuFormatted,
      total    : totalFormatted,
      isSuccess: status == 'success' || status == 'completed' || status == '1',
    );
  }

  static String _fmt(int n) {
    final s   = n.toString();
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
  final List<double> penjualan;   // dalam jutaan rupiah
  final List<double> stokKeluar;  // dalam unit

  const ChartData({
    required this.labels,
    required this.penjualan,
    required this.stokKeluar,
  });

  bool get isEmpty => labels.isEmpty;
}

// ── DashboardService ──────────────────────────────────────────────────────────
class DashboardService {

  static Future<DashboardStats> getStats() async {
    try {
      final res = await http.get(
        Uri.parse(ApiConfig.dashboardStats),
        headers: await AuthService.authHeaders(),
      ).timeout(const Duration(seconds: 15));
      if (res.statusCode == 200) {
        return DashboardStats.fromJson(jsonDecode(res.body) as Map<String, dynamic>);
      }
    } catch (_) {}
    return const DashboardStats();
  }

  static Future<List<StokMenipisItem>> getLowStockProducts() async {
    try {
      final res = await http.get(
        Uri.parse(ApiConfig.productLowStock),
        headers: await AuthService.authHeaders(),
      ).timeout(const Duration(seconds: 15));
      if (res.statusCode == 200) {
        final list = (jsonDecode(res.body)['data'] as List?) ?? [];
        return list.map((e) => StokMenipisItem.fromJson(e as Map<String, dynamic>)).toList();
      }
    } catch (_) {}
    return [];
  }

  static Future<List<KadaluarsaItem>> getExpiringProducts() async {
    try {
      final res = await http.get(
        Uri.parse(ApiConfig.dashboardNotifications),
        headers: await AuthService.authHeaders(),
      ).timeout(const Duration(seconds: 15));
      if (res.statusCode == 200) {
        final json  = jsonDecode(res.body) as Map<String, dynamic>;
        final data  = (json['data'] as Map<String, dynamic>?) ?? json;
        final list  = (data['expiring'] ?? data['kadaluarsa'] ?? []) as List;
        return list.map((e) => KadaluarsaItem.fromJson(e as Map<String, dynamic>)).toList();
      }
    } catch (_) {}
    return [];
  }

  static Future<List<TransaksiItem>> getRecentTransactions() async {
    try {
      final res = await http.get(
        Uri.parse(ApiConfig.transactionsRecent),
        headers: await AuthService.authHeaders(),
      ).timeout(const Duration(seconds: 15));
      if (res.statusCode == 200) {
        final list = (jsonDecode(res.body)['data'] as List?) ?? [];
        return list.map((e) => TransaksiItem.fromJson(e as Map<String, dynamic>)).toList();
      }
    } catch (_) {}
    return [];
  }

  /// [filter] = '7 Hari' | '30 Hari' | '90 Hari'
  static Future<ChartData?> getChartData(String filter) async {
    try {
      final days = filter.split(' ').first;
      final res  = await http.get(
        Uri.parse('${ApiConfig.dashboardChart}?days=$days'),
        headers: await AuthService.authHeaders(),
      ).timeout(const Duration(seconds: 15));
      if (res.statusCode == 200) {
        final json    = jsonDecode(res.body) as Map<String, dynamic>;
        final data    = (json['data'] as Map<String, dynamic>?) ?? json;
        final labels  = (data['labels']     as List?)?.map((e) => e.toString()).toList() ?? [];
        final penjualan = (data['penjualan'] as List?)?.map((e) {
          final n = (e as num).toDouble();
          // Jika backend kirim dalam rupiah (> 1000), konversi ke jutaan
          return n > 1000 ? n / 1_000_000 : n;
        }).toList() ?? [];
        final stokKeluar = ((data['stok_keluar'] ?? data['stock_out']) as List?)
            ?.map((e) => (e as num).toDouble()).toList() ?? [];
        return ChartData(labels: labels, penjualan: penjualan, stokKeluar: stokKeluar);
      }
    } catch (_) {}
    return null;
  }
}