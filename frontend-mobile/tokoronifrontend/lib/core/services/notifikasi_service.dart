import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;

import '../config/api_config.dart';
import 'auth_service.dart';

class NotifItem {
  final String id;
  final IconData icon;
  final Color iconColor;
  final String judul;
  final String pesan;
  final String waktu;
  final String tipe;
  bool sudahDibaca;

  NotifItem({
    required this.id,
    required this.icon,
    required this.iconColor,
    required this.judul,
    required this.pesan,
    required this.waktu,
    required this.tipe,
    this.sudahDibaca = false,
  });

  factory NotifItem.fromJson(Map<String, dynamic> json) {
    final data = _asMap(json['data']);
    final backendTypeGroup = (json['type_group'] ?? '').toString();
    final tipe = _parseTipe(
      className: (json['type'] ?? '').toString(),
      data: data,
      backendTypeGroup: backendTypeGroup,
    );

    final isRead = json['is_read'] == true || json['read_at'] != null;
    final rootTitle = json['title']?.toString().trim() ?? '';
    final rootMessage = json['message']?.toString().trim() ?? '';
    final dataTitle = data['title']?.toString().trim() ?? '';
    final dataMessage = data['message']?.toString().trim() ?? '';
    final dataBody = data['body']?.toString().trim() ?? '';

    final judul = rootTitle.isNotEmpty
        ? rootTitle
        : (dataTitle.isNotEmpty ? dataTitle : _judulFromTipe(tipe));
    final pesan = rootMessage.isNotEmpty
        ? rootMessage
        : (dataMessage.isNotEmpty
              ? dataMessage
              : (dataBody.isNotEmpty ? dataBody : '-'));

    return NotifItem(
      id: (json['id'] ?? '').toString(),
      icon: _iconFromTipe(tipe),
      iconColor: _colorFromTipe(tipe),
      judul: judul,
      pesan: pesan,
      waktu: _formatWaktu(
        json['created_at']?.toString() ?? data['created_at']?.toString(),
      ),
      tipe: tipe,
      sudahDibaca: isRead,
    );
  }

  static String _parseTipe({
    required String className,
    required Map<String, dynamic> data,
    required String backendTypeGroup,
  }) {
    final direct = backendTypeGroup.toLowerCase().trim();
    if (direct.isNotEmpty) return direct;

    final dataType = (data['type'] ?? '').toString().toLowerCase().trim();
    final cls = className.toLowerCase().trim();
    final source = '$dataType $cls';

    if (source.contains('stock') ||
        source.contains('low_stock') ||
        source.contains('out_of_stock')) {
      return 'stock';
    }
    if (source.contains('expiry') ||
        source.contains('expir') ||
        source.contains('kadaluarsa')) {
      return 'expiry';
    }
    if (source.contains('transaction')) return 'transaction';
    if (source.contains('product')) return 'product';
    if (source.contains('member')) return 'member';
    if (source.contains('user') ||
        dataType == 'create' ||
        dataType == 'update') {
      return 'user';
    }
    if (source.contains('category') || source.contains('kategori')) {
      return 'category';
    }
    if (source.contains('delivery') || source.contains('pengiriman')) {
      return 'delivery';
    }
    if (source.contains('vehicle') || source.contains('kendaraan')) {
      return 'vehicle';
    }
    if (source.contains('receivable') || source.contains('piutang')) {
      return 'receivable';
    }
    if (source.contains('payment')) return 'payment';
    if (source.contains('report')) return 'report';

    return 'default';
  }

  static IconData _iconFromTipe(String tipe) {
    switch (tipe) {
      case 'transaction':
        return Icons.receipt_long_rounded;
      case 'product':
        return Icons.inventory_2_rounded;
      case 'stock':
        return Icons.warning_amber_rounded;
      case 'expiry':
        return Icons.timer_rounded;
      case 'member':
        return Icons.people_alt_rounded;
      case 'user':
        return Icons.person_rounded;
      case 'category':
        return Icons.label_rounded;
      case 'delivery':
        return Icons.local_shipping_rounded;
      case 'vehicle':
        return Icons.directions_car_rounded;
      case 'receivable':
        return Icons.account_balance_wallet_rounded;
      case 'payment':
        return Icons.payments_rounded;
      case 'report':
        return Icons.assessment_rounded;
      default:
        return Icons.notifications_rounded;
    }
  }

  static Color _colorFromTipe(String tipe) {
    switch (tipe) {
      case 'transaction':
        return const Color(0xFF4169E1);
      case 'product':
        return const Color(0xFF48BB78);
      case 'stock':
        return const Color(0xFFECC94B);
      case 'expiry':
        return const Color(0xFFFC8181);
      case 'member':
        return const Color(0xFF38B2AC);
      case 'user':
        return const Color(0xFF6B5CE7);
      case 'category':
        return const Color(0xFFD69E2E);
      case 'delivery':
        return const Color(0xFF3182CE);
      case 'vehicle':
        return const Color(0xFF2B6CB0);
      case 'receivable':
        return const Color(0xFFE53E3E);
      case 'payment':
        return const Color(0xFF48BB78);
      case 'report':
        return const Color(0xFFDD6B20);
      default:
        return const Color(0xFF718096);
    }
  }

  static String _judulFromTipe(String tipe) {
    switch (tipe) {
      case 'transaction':
        return 'Transaksi';
      case 'product':
        return 'Produk';
      case 'stock':
        return 'Stok Produk';
      case 'expiry':
        return 'Masa Kadaluarsa';
      case 'member':
        return 'Member';
      case 'user':
        return 'Pengguna';
      case 'category':
        return 'Kategori';
      case 'delivery':
        return 'Pengiriman';
      case 'vehicle':
        return 'Kendaraan';
      case 'receivable':
        return 'Piutang';
      case 'payment':
        return 'Pembayaran';
      case 'report':
        return 'Laporan';
      default:
        return 'Notifikasi';
    }
  }

  static String _formatWaktu(String? iso) {
    if (iso == null || iso.trim().isEmpty) return '-';
    try {
      final dt = DateTime.parse(iso).toLocal();
      final diff = DateTime.now().difference(dt);
      if (diff.inSeconds < 60) return 'Baru saja';
      if (diff.inMinutes < 60) return '${diff.inMinutes} menit lalu';
      if (diff.inHours < 24) return '${diff.inHours} jam lalu';
      if (diff.inDays < 7) return '${diff.inDays} hari lalu';
      if (diff.inDays < 30) return '${(diff.inDays / 7).floor()} minggu lalu';

      const months = [
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
      return '${dt.day} ${months[dt.month - 1]} ${dt.year}';
    } catch (_) {
      return iso;
    }
  }

  static Map<String, dynamic> _asMap(dynamic raw) {
    if (raw is Map<String, dynamic>) return raw;
    if (raw is Map) {
      return raw.map((k, v) => MapEntry(k.toString(), v));
    }
    return {};
  }
}

class NotifikasiService {
  static String get _notificationsIndex => '${ApiConfig.baseUrl}/notifications';

  static Future<List<NotifItem>> getAll({
    int perPage = 100,
    int page = 1,
  }) async {
    try {
      final safePerPage = perPage <= 0 ? 20 : perPage;
      final safePage = page <= 0 ? 1 : page;
      final uri = Uri.parse(_notificationsIndex).replace(
        queryParameters: {'per_page': '$safePerPage', 'page': '$safePage'},
      );

      final res = await http
          .get(uri, headers: await AuthService.authHeaders())
          .timeout(const Duration(seconds: 15));

      if (res.statusCode != 200) return [];
      final json = jsonDecode(res.body) as Map<String, dynamic>;
      if (json['success'] != true) return [];

      final list = _extractList(json['data']);
      return list
          .map(_asMap)
          .where((e) => e.isNotEmpty)
          .map(NotifItem.fromJson)
          .toList();
    } catch (_) {
      return [];
    }
  }

  static Future<List<NotifItem>> getUnread() async {
    try {
      final res = await http
          .get(
            Uri.parse(ApiConfig.notifications),
            headers: await AuthService.authHeaders(),
          )
          .timeout(const Duration(seconds: 15));

      if (res.statusCode != 200) return [];
      final json = jsonDecode(res.body) as Map<String, dynamic>;
      if (json['success'] != true) return [];

      final list = _extractList(json['data']);
      return list
          .map(_asMap)
          .where((e) => e.isNotEmpty)
          .map(NotifItem.fromJson)
          .toList();
    } catch (_) {
      return [];
    }
  }

  static Future<bool> markAsRead(String id) async {
    try {
      final res = await http
          .post(
            Uri.parse('${ApiConfig.baseUrl}/notifications/$id/read'),
            headers: await AuthService.authHeaders(),
          )
          .timeout(const Duration(seconds: 10));
      return res.statusCode >= 200 && res.statusCode < 300;
    } catch (_) {
      return false;
    }
  }

  static Future<bool> markAllAsRead() async {
    try {
      final res = await http
          .post(
            Uri.parse('${ApiConfig.baseUrl}/notifications/read-all'),
            headers: await AuthService.authHeaders(),
          )
          .timeout(const Duration(seconds: 10));
      return res.statusCode >= 200 && res.statusCode < 300;
    } catch (_) {
      return false;
    }
  }

  static Future<bool> delete(String id) async {
    try {
      final res = await http
          .delete(
            Uri.parse('${ApiConfig.baseUrl}/notifications/$id'),
            headers: await AuthService.authHeaders(),
          )
          .timeout(const Duration(seconds: 10));
      return res.statusCode >= 200 && res.statusCode < 300;
    } catch (_) {
      return false;
    }
  }

  static Future<bool> clearAll() async {
    try {
      final res = await http
          .delete(
            Uri.parse('${ApiConfig.baseUrl}/notifications/clear/all'),
            headers: await AuthService.authHeaders(),
          )
          .timeout(const Duration(seconds: 10));
      return res.statusCode >= 200 && res.statusCode < 300;
    } catch (_) {
      return false;
    }
  }

  static List<dynamic> _extractList(dynamic raw) {
    if (raw is List) return raw;
    if (raw is Map<String, dynamic>) {
      final nested = raw['data'];
      if (nested is List) return nested;
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
