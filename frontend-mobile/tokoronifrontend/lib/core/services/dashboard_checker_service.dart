import 'dart:async';
import 'dart:convert';
import 'dart:io';
import 'dart:math' as math;

import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

import '../config/api_config.dart';
import 'auth_service.dart';
import 'notification_refresh_helper.dart';
import 'product_service.dart';

class DashboardCheckerSummary {
  final int stokRendah;
  final int akanKadaluarsa;
  final int sudahKadaluarsa;
  final int produkAktif;
  final int totalKategori;
  final int totalProduk;
  final double rasioStokRendah;

  const DashboardCheckerSummary({
    this.stokRendah = 0,
    this.akanKadaluarsa = 0,
    this.sudahKadaluarsa = 0,
    this.produkAktif = 0,
    this.totalKategori = 0,
    this.totalProduk = 0,
    this.rasioStokRendah = 0,
  });
}

class CheckerIssueItem {
  final int productId;
  final String namaProduk;
  final String kategori;
  final int stok;
  final int stokMinimum;
  final DateTime? expiryDate;
  final int? daysLeft;
  final int? daysExpired;
  final bool isExpired;

  const CheckerIssueItem({
    required this.productId,
    required this.namaProduk,
    required this.kategori,
    required this.stok,
    required this.stokMinimum,
    this.expiryDate,
    this.daysLeft,
    this.daysExpired,
    this.isExpired = false,
  });
}

class CheckerReportItem {
  final String id;
  final int productId;
  final String productName;
  final String category;
  final String reportType;
  final String notes;
  final int? quantity;
  final String status;
  final bool isSynced;
  final DateTime createdAt;

  const CheckerReportItem({
    required this.id,
    required this.productId,
    required this.productName,
    required this.category,
    required this.reportType,
    required this.notes,
    required this.quantity,
    required this.status,
    required this.isSynced,
    required this.createdAt,
  });

  String get reportTypeLabel {
    switch (reportType) {
      case 'low_stock':
        return 'Stok Rendah';
      case 'expiring':
        return 'Akan Kadaluarsa';
      case 'expired':
        return 'Sudah Kadaluarsa';
      case 'damaged':
        return 'Produk Rusak';
      default:
        return 'Laporan Produk';
    }
  }

  String get statusLabel {
    switch (status) {
      case 'resolved':
        return 'Selesai';
      case 'in_progress':
        return 'Diproses';
      default:
        return 'Pending';
    }
  }

  CheckerReportItem copyWith({
    String? id,
    String? status,
    bool? isSynced,
    DateTime? createdAt,
  }) {
    return CheckerReportItem(
      id: id ?? this.id,
      productId: productId,
      productName: productName,
      category: category,
      reportType: reportType,
      notes: notes,
      quantity: quantity,
      status: status ?? this.status,
      isSynced: isSynced ?? this.isSynced,
      createdAt: createdAt ?? this.createdAt,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'product_id': productId,
      'product_name': productName,
      'category': category,
      'report_type': reportType,
      'notes': notes,
      'quantity': quantity,
      'status': status,
      'is_synced': isSynced,
      'created_at': createdAt.toIso8601String(),
    };
  }

  factory CheckerReportItem.fromJson(Map<String, dynamic> json) {
    final createdAtRaw = (json['created_at'] ?? '').toString().trim();
    final parsed = DateTime.tryParse(createdAtRaw)?.toLocal() ?? DateTime.now();

    return CheckerReportItem(
      id: (json['id'] ?? '').toString(),
      productId: _parseInt(json['product_id']),
      productName: (json['product_name'] ?? '').toString(),
      category: (json['category'] ?? '-').toString(),
      reportType: (json['report_type'] ?? 'other').toString(),
      notes: (json['notes'] ?? '').toString(),
      quantity: _parseNullableInt(json['quantity']),
      status: (json['status'] ?? 'pending').toString(),
      isSynced: json['is_synced'] == true,
      createdAt: parsed,
    );
  }

  static int _parseInt(dynamic value) {
    if (value == null) return 0;
    if (value is int) return value;
    if (value is num) return value.toInt();
    return int.tryParse(value.toString()) ?? 0;
  }

  static int? _parseNullableInt(dynamic value) {
    final parsed = _parseInt(value);
    return parsed <= 0 ? null : parsed;
  }
}

class DashboardCheckerData {
  final DashboardCheckerSummary summary;
  final List<CheckerIssueItem> lowStockItems;
  final List<CheckerIssueItem> expiringItems;
  final List<CheckerIssueItem> expiredItems;
  final List<CheckerReportItem> recentReports;

  const DashboardCheckerData({
    required this.summary,
    required this.lowStockItems,
    required this.expiringItems,
    required this.expiredItems,
    required this.recentReports,
  });
}

class CheckerReportSubmitResult {
  final CheckerReportItem report;
  final bool sentToServer;
  final String message;

  const CheckerReportSubmitResult({
    required this.report,
    required this.sentToServer,
    required this.message,
  });
}

class DashboardCheckerService {
  static const String _storageKey = 'checker_reports_local_v1';

  static Future<DashboardCheckerData> getDashboardData() async {
    final bundle = await ProductService.getProductsAndCategories();
    final products = bundle.products;
    final categories = bundle.categories;

    final now = DateTime.now();
    final today = DateTime(now.year, now.month, now.day);

    final lowStock = <CheckerIssueItem>[];
    final expiring = <CheckerIssueItem>[];
    final expired = <CheckerIssueItem>[];

    for (final p in products) {
      final threshold = _resolveMinStock(p.stokMinimum);
      if (p.stok < threshold) {
        lowStock.add(
          CheckerIssueItem(
            productId: p.id ?? 0,
            namaProduk: p.nama.trim().isEmpty ? '-' : p.nama.trim(),
            kategori: p.kategori.trim().isEmpty ? '-' : p.kategori.trim(),
            stok: p.stok,
            stokMinimum: threshold,
          ),
        );
      }

      final expiry = _parseDateFlexible(p.kadaluarsa);
      if (expiry == null) continue;
      final expiryDay = DateTime(expiry.year, expiry.month, expiry.day);
      final diff = expiryDay.difference(today).inDays;

      if (diff < 0) {
        expired.add(
          CheckerIssueItem(
            productId: p.id ?? 0,
            namaProduk: p.nama.trim().isEmpty ? '-' : p.nama.trim(),
            kategori: p.kategori.trim().isEmpty ? '-' : p.kategori.trim(),
            stok: p.stok,
            stokMinimum: threshold,
            expiryDate: expiryDay,
            daysExpired: diff.abs(),
            isExpired: true,
          ),
        );
      } else if (diff <= 30) {
        expiring.add(
          CheckerIssueItem(
            productId: p.id ?? 0,
            namaProduk: p.nama.trim().isEmpty ? '-' : p.nama.trim(),
            kategori: p.kategori.trim().isEmpty ? '-' : p.kategori.trim(),
            stok: p.stok,
            stokMinimum: threshold,
            expiryDate: expiryDay,
            daysLeft: diff,
          ),
        );
      }
    }

    lowStock.sort((a, b) => a.stok.compareTo(b.stok));
    expiring.sort((a, b) => (a.daysLeft ?? 999).compareTo(b.daysLeft ?? 999));
    expired.sort((a, b) => (b.daysExpired ?? 0).compareTo(a.daysExpired ?? 0));

    final localReports = await _loadReports();
    final serverReports = await _fetchServerReports(limit: 40);
    final reports = _mergeReports(localReports, serverReports);
    reports.sort((a, b) => b.createdAt.compareTo(a.createdAt));

    final activeProducts = products.where((p) => p.aktif).length;
    final totalProduk = products.length;
    final ratio = totalProduk <= 0
        ? 0.0
        : (lowStock.length / totalProduk) * 100;

    return DashboardCheckerData(
      summary: DashboardCheckerSummary(
        stokRendah: lowStock.length,
        akanKadaluarsa: expiring.length,
        sudahKadaluarsa: expired.length,
        produkAktif: activeProducts,
        totalKategori: categories.length,
        totalProduk: totalProduk,
        rasioStokRendah: ratio,
      ),
      lowStockItems: lowStock,
      expiringItems: expiring,
      expiredItems: expired,
      recentReports: reports.take(20).toList(),
    );
  }

  static Future<CheckerReportSubmitResult> submitReport({
    required CheckerIssueItem issueItem,
    required String reportType,
    required String notes,
    int? quantity,
  }) async {
    final type = _normalizeType(reportType);
    final localId = 'local-${DateTime.now().millisecondsSinceEpoch}';
    final now = DateTime.now();

    bool sentToServer = false;
    String message = 'Laporan disimpan lokal.';
    String finalId = localId;

    final remoteResult = await _sendToServer(
      productId: issueItem.productId,
      reportType: type,
      notes: notes,
      quantity: quantity,
    );

    if (remoteResult.success) {
      sentToServer = true;
      message = remoteResult.message.isEmpty
          ? 'Laporan berhasil dikirim.'
          : remoteResult.message;
      if (remoteResult.reportId.trim().isNotEmpty) {
        finalId = remoteResult.reportId.trim();
      }
    } else if (remoteResult.message.isNotEmpty) {
      message = '${remoteResult.message} Laporan disimpan lokal.';
    }

    final report = CheckerReportItem(
      id: finalId,
      productId: issueItem.productId,
      productName: issueItem.namaProduk,
      category: issueItem.kategori,
      reportType: type,
      notes: notes,
      quantity: quantity,
      status: 'pending',
      isSynced: sentToServer,
      createdAt: now,
    );

    final reports = await _loadReports();
    reports.insert(0, report);
    await _saveReports(reports);

    await NotificationRefreshHelper.refreshSafely();

    return CheckerReportSubmitResult(
      report: report,
      sentToServer: sentToServer,
      message: message,
    );
  }

  static Future<List<CheckerReportItem>> _loadReports() async {
    final prefs = await SharedPreferences.getInstance();
    final raw = prefs.getString(_storageKey);
    if (raw == null || raw.trim().isEmpty) return [];

    try {
      final decoded = jsonDecode(raw);
      if (decoded is! List) return [];
      return decoded
          .map(_asMap)
          .where((item) => item.isNotEmpty)
          .map(CheckerReportItem.fromJson)
          .toList();
    } catch (_) {
      return [];
    }
  }

  static Future<void> _saveReports(List<CheckerReportItem> reports) async {
    final prefs = await SharedPreferences.getInstance();
    final capped = reports.take(120).toList();
    await prefs.setString(
      _storageKey,
      jsonEncode(capped.map((e) => e.toJson()).toList()),
    );
  }

  static Future<List<CheckerReportItem>> _fetchServerReports({
    int limit = 20,
  }) async {
    final safeLimit = limit <= 0 ? 20 : limit;

    try {
      final uri = Uri.parse(
        ApiConfig.checkerReportIndex,
      ).replace(queryParameters: {'limit': '$safeLimit'});
      final response = await http
          .get(uri, headers: await AuthService.authHeaders())
          .timeout(const Duration(seconds: 15));

      if (response.statusCode < 200 || response.statusCode >= 300) return [];

      dynamic decoded;
      try {
        decoded = jsonDecode(response.body);
      } catch (_) {
        return [];
      }

      final body = _asMap(decoded);
      if (body.isEmpty) return [];

      final success = body['success'];
      if (success is bool && !success) return [];

      final rows = _extractList(body['data']);
      return rows.map(_asMap).where((map) => map.isNotEmpty).map((map) {
        final item = Map<String, dynamic>.from(map);
        item['is_synced'] = true;
        return CheckerReportItem.fromJson(item);
      }).toList();
    } catch (_) {
      return [];
    }
  }

  static List<CheckerReportItem> _mergeReports(
    List<CheckerReportItem> localReports,
    List<CheckerReportItem> serverReports,
  ) {
    final merged = <String, CheckerReportItem>{};

    for (final report in [...serverReports, ...localReports]) {
      final key = _buildReportKey(report);
      final existing = merged[key];
      if (existing == null) {
        merged[key] = report;
        continue;
      }

      if (!existing.isSynced && report.isSynced) {
        merged[key] = report;
        continue;
      }

      if (existing.isSynced == report.isSynced &&
          report.createdAt.isAfter(existing.createdAt)) {
        merged[key] = report;
      }
    }

    return merged.values.toList()
      ..sort((a, b) => b.createdAt.compareTo(a.createdAt));
  }

  static String _buildReportKey(CheckerReportItem report) {
    final id = report.id.trim();
    if (id.isNotEmpty && !id.startsWith('local-')) return 'id:$id';
    return [
      report.productId,
      report.reportType,
      report.notes,
      report.quantity ?? 0,
      report.createdAt.toIso8601String(),
    ].join('|');
  }

  static Future<_RemoteSendResult> _sendToServer({
    required int productId,
    required String reportType,
    required String notes,
    required int? quantity,
  }) async {
    if (productId <= 0) {
      return const _RemoteSendResult(
        success: false,
        message: 'Produk tidak valid.',
      );
    }

    final payload = <String, dynamic>{
      'product_id': productId,
      'report_type': reportType,
      'notes': notes,
      if (quantity != null && quantity > 0) 'quantity': quantity,
      'priority': 'urgent',
      'urgent': true,
    };

    final urls = <String>[
      ApiConfig.checkerReportIndex,
      ApiConfig.productReport(productId),
    ];

    String lastMessage = '';
    for (final url in urls) {
      final result = await _postJson(url: url, payload: payload);
      if (result.success) return result;
      if (result.message.trim().isNotEmpty) {
        lastMessage = result.message.trim();
      }
    }

    return _RemoteSendResult(
      success: false,
      message: lastMessage.isNotEmpty
          ? lastMessage
          : 'Endpoint laporan checker belum tersedia.',
    );
  }

  static Future<_RemoteSendResult> _postJson({
    required String url,
    required Map<String, dynamic> payload,
  }) async {
    try {
      final response = await http
          .post(
            Uri.parse(url),
            headers: await AuthService.authHeaders(),
            body: jsonEncode(payload),
          )
          .timeout(const Duration(seconds: 15));

      Map<String, dynamic> body = {};
      try {
        final decoded = jsonDecode(response.body);
        if (decoded is Map<String, dynamic>) {
          body = decoded;
        }
      } catch (_) {}

      if (response.statusCode < 200 || response.statusCode >= 300) {
        return _RemoteSendResult(
          success: false,
          message: _firstNonEmpty([
            body['message'],
            body['error'],
            'HTTP ${response.statusCode}',
          ]),
        );
      }

      final success = body['success'] == true || body['status'] == true;
      if (!success) {
        return _RemoteSendResult(
          success: false,
          message: _firstNonEmpty([body['message'], body['error']]),
        );
      }

      final data = _asMap(body['data']);
      final reportMap = _asMap(body['report']);
      final reportId = _firstNonEmpty([data['id'], reportMap['id']]);

      return _RemoteSendResult(
        success: true,
        message: _firstNonEmpty([body['message'], 'Laporan berhasil dikirim.']),
        reportId: reportId,
      );
    } on TimeoutException {
      return const _RemoteSendResult(
        success: false,
        message: 'Koneksi timeout.',
      );
    } on SocketException {
      return const _RemoteSendResult(
        success: false,
        message: 'Tidak ada koneksi internet.',
      );
    } catch (_) {
      return const _RemoteSendResult(success: false);
    }
  }

  static int _resolveMinStock(int minStock) {
    if (minStock <= 0) return 10;
    return math.max(10, minStock);
  }

  static String _normalizeType(String reportType) {
    final raw = reportType.trim().toLowerCase();
    if (raw == 'low_stock' || raw == 'expiring' || raw == 'expired') {
      return raw;
    }
    if (raw == 'stok_rendah') return 'low_stock';
    if (raw == 'akan_kadaluarsa') return 'expiring';
    if (raw == 'kadaluarsa') return 'expired';
    return 'other';
  }

  static DateTime? _parseDateFlexible(String raw) {
    final text = raw.trim();
    if (text.isEmpty || text == '-') return null;

    final parsedIso = DateTime.tryParse(text);
    if (parsedIso != null) return parsedIso;

    final separators = ['-', '/'];
    for (final sep in separators) {
      final parts = text.split(sep);
      if (parts.length != 3) continue;
      try {
        if (parts[0].length == 4) {
          // yyyy-mm-dd
          return DateTime(
            int.parse(parts[0]),
            int.parse(parts[1]),
            int.parse(parts[2]),
          );
        }
        // dd-mm-yyyy
        return DateTime(
          int.parse(parts[2]),
          int.parse(parts[1]),
          int.parse(parts[0]),
        );
      } catch (_) {}
    }

    return null;
  }

  static Map<String, dynamic> _asMap(dynamic raw) {
    if (raw is Map<String, dynamic>) return raw;
    if (raw is Map) {
      return raw.map((key, value) => MapEntry(key.toString(), value));
    }
    return {};
  }

  static List<dynamic> _extractList(dynamic raw) {
    if (raw is List) return raw;
    if (raw is Map<String, dynamic>) {
      final nestedData = raw['data'];
      if (nestedData is List) return nestedData;
      final nestedItems = raw['items'];
      if (nestedItems is List) return nestedItems;
    }
    return const [];
  }

  static String _firstNonEmpty(List<dynamic> values) {
    for (final value in values) {
      final text = (value ?? '').toString().trim();
      if (text.isNotEmpty && text.toLowerCase() != 'null') return text;
    }
    return '';
  }
}

class _RemoteSendResult {
  final bool success;
  final String message;
  final String reportId;

  const _RemoteSendResult({
    required this.success,
    this.message = '',
    this.reportId = '',
  });
}
