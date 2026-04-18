// ============================================================
// lib/core/notifikasi_service.dart
//
// Fetch notifikasi dari Laravel Notification API
// Endpoint: GET /api/v1/notifications          → semua notifikasi
//           GET /api/v1/notifications/unread   → belum dibaca
//           POST /api/v1/notifications/{id}/read
//           POST /api/v1/notifications/read-all
//           DELETE /api/v1/notifications/{id}
//           DELETE /api/v1/notifications/clear/all
//
// FORMAT RESPONSE (dari NotificationApiController):
// {
//   "success": true,
//   "data": [
//     {
//       "id": "uuid-xxx",
//       "type": "App\\Notifications\\TransactionCreatedNotification",
//       "data": {
//         "title": "Transaksi Baru",
//         "message": "INV001 senilai Rp 450.000 berhasil",
//         "type": "transaction"   ← kategori: transaction/product/member/user/stock/expiry
//       },
//       "read_at": null,
//       "created_at": "2026-03-18T10:30:00Z"
//     }
//   ]
// }
// ============================================================

import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'auth_service.dart';
import '../config/api_config.dart';

// ── Model ─────────────────────────────────────────────────────────────────────
class NotifItem {
  final String id;
  final IconData icon;
  final Color iconColor;
  final String judul;
  final String pesan;
  final String waktu;
  bool sudahDibaca;
  final String
  tipe; // transaction / product / member / user / stock / expiry / default

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
    final data = (json['data'] as Map<String, dynamic>?) ?? {};
    final tipe = _parseTipe(json['type']?.toString() ?? '', data);
    final isRead = json['read_at'] != null;
    final waktu = _formatWaktu(json['created_at']?.toString());

    return NotifItem(
      id: json['id']?.toString() ?? '',
      icon: _iconFromTipe(tipe),
      iconColor: _colorFromTipe(tipe),
      judul: data['title']?.toString() ?? _judulFromTipe(tipe),
      pesan: data['message']?.toString() ?? data['body']?.toString() ?? '-',
      waktu: waktu,
      tipe: tipe,
      sudahDibaca: isRead,
    );
  }

  // ── Tipe dari class name atau field 'type' di data ──────────────────────
  static String _parseTipe(String className, Map<String, dynamic> data) {
    // Cek dari field 'type' di dalam data terlebih dahulu
    final t = data['type']?.toString().toLowerCase() ?? '';
    if (t.isNotEmpty) return t;

    // Fallback dari class name
    final cls = className.toLowerCase();
    if (cls.contains('transaction')) return 'transaction';
    if (cls.contains('product') || cls.contains('produk')) return 'product';
    if (cls.contains('stock') || cls.contains('stok')) return 'stock';
    if (cls.contains('expir') || cls.contains('kadaluarsa')) return 'expiry';
    if (cls.contains('member')) return 'member';
    if (cls.contains('user') || cls.contains('pengguna')) return 'user';
    if (cls.contains('category') || cls.contains('kategori')) return 'category';
    if (cls.contains('delivery') || cls.contains('pengiriman'))
      return 'delivery';
    if (cls.contains('receivable') || cls.contains('piutang'))
      return 'receivable';
    if (cls.contains('payment')) return 'payment';
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
      case 'receivable':
        return Icons.account_balance_wallet_rounded;
      case 'payment':
        return Icons.payments_rounded;
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
      case 'receivable':
        return const Color(0xFFE53E3E);
      case 'payment':
        return const Color(0xFF48BB78);
      default:
        return const Color(0xFF718096);
    }
  }

  static String _judulFromTipe(String tipe) {
    switch (tipe) {
      case 'transaction':
        return 'Transaksi Baru';
      case 'product':
        return 'Update Produk';
      case 'stock':
        return 'Stok Menipis';
      case 'expiry':
        return 'Akan Kadaluarsa';
      case 'member':
        return 'Update Member';
      case 'user':
        return 'Update Pengguna';
      case 'category':
        return 'Update Kategori';
      case 'delivery':
        return 'Update Pengiriman';
      case 'receivable':
        return 'Update Piutang';
      case 'payment':
        return 'Pembayaran';
      default:
        return 'Notifikasi';
    }
  }

  // ── Format waktu relatif dari ISO string ─────────────────────────────────
  static String _formatWaktu(String? iso) {
    if (iso == null) return '-';
    try {
      final dt = DateTime.parse(iso).toLocal();
      final diff = DateTime.now().difference(dt);
      if (diff.inSeconds < 60) return 'Baru saja';
      if (diff.inMinutes < 60) return '${diff.inMinutes} menit lalu';
      if (diff.inHours < 24) return '${diff.inHours} jam lalu';
      if (diff.inDays < 7) return '${diff.inDays} hari lalu';
      if (diff.inDays < 30) return '${(diff.inDays / 7).floor()} minggu lalu';
      const m = [
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
      return '${dt.day} ${m[dt.month - 1]} ${dt.year}';
    } catch (_) {
      return iso;
    }
  }
}

// ── Service ───────────────────────────────────────────────────────────────────
class NotifikasiService {
  /// Ambil semua notifikasi (max 50)
  static Future<List<NotifItem>> getAll() async {
    try {
      final res = await http
          .get(
            Uri.parse(ApiConfig.notifications.replaceAll('/unread', '')),
            headers: await AuthService.authHeaders(),
          )
          .timeout(const Duration(seconds: 15));

      if (res.statusCode == 200) {
        final json = jsonDecode(res.body) as Map<String, dynamic>;
        if (json['success'] == true) {
          final list = (json['data'] as List?) ?? [];
          return list
              .map((e) => NotifItem.fromJson(e as Map<String, dynamic>))
              .toList();
        }
      }
    } catch (_) {}
    return [];
  }

  /// Ambil notifikasi belum dibaca
  static Future<List<NotifItem>> getUnread() async {
    try {
      final res = await http
          .get(
            Uri.parse(ApiConfig.notifications), // /notifications/unread
            headers: await AuthService.authHeaders(),
          )
          .timeout(const Duration(seconds: 15));

      if (res.statusCode == 200) {
        final json = jsonDecode(res.body) as Map<String, dynamic>;
        final list = (json['data'] as List?) ?? [];
        return list
            .map((e) => NotifItem.fromJson(e as Map<String, dynamic>))
            .toList();
      }
    } catch (_) {}
    return [];
  }

  /// Tandai satu notifikasi sebagai sudah dibaca
  static Future<bool> markAsRead(String id) async {
    try {
      final res = await http
          .post(
            Uri.parse('${ApiConfig.baseUrl}/notifications/$id/read'),
            headers: await AuthService.authHeaders(),
          )
          .timeout(const Duration(seconds: 10));
      return res.statusCode == 200;
    } catch (_) {
      return false;
    }
  }

  /// Tandai semua notifikasi sebagai sudah dibaca
  static Future<bool> markAllAsRead() async {
    try {
      final res = await http
          .post(
            Uri.parse('${ApiConfig.baseUrl}/notifications/read-all'),
            headers: await AuthService.authHeaders(),
          )
          .timeout(const Duration(seconds: 10));
      return res.statusCode == 200;
    } catch (_) {
      return false;
    }
  }

  /// Hapus satu notifikasi
  static Future<bool> delete(String id) async {
    try {
      final res = await http
          .delete(
            Uri.parse('${ApiConfig.baseUrl}/notifications/$id'),
            headers: await AuthService.authHeaders(),
          )
          .timeout(const Duration(seconds: 10));
      return res.statusCode == 200;
    } catch (_) {
      return false;
    }
  }

  /// Hapus semua notifikasi
  static Future<bool> clearAll() async {
    try {
      final res = await http
          .delete(
            Uri.parse('${ApiConfig.baseUrl}/notifications/clear/all'),
            headers: await AuthService.authHeaders(),
          )
          .timeout(const Duration(seconds: 10));
      return res.statusCode == 200;
    } catch (_) {
      return false;
    }
  }
}
