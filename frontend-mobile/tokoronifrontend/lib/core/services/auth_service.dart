// ============================================================
// lib/core/auth_service.dart
// ============================================================

import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../config/api_config.dart';

class AuthService {
  // ── Kunci SharedPreferences ──────────────────────────────
  static const _kToken = 'auth_token';
  static const _kName = 'user_name';
  static const _kEmail = 'user_email';
  static const _kRole = 'user_role';

  // ── Cek apakah token tersimpan ───────────────────────────
  static Future<bool> isLoggedIn() async {
    final prefs = await SharedPreferences.getInstance();
    return (prefs.getString(_kToken) ?? '').isNotEmpty;
  }

  // ── Ambil token ──────────────────────────────────────────
  static Future<String?> getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(_kToken);
  }

  // ── Ambil info user ──────────────────────────────────────
  static Future<String> getUserName() async {
    final prefs = await SharedPreferences.getInstance();
    final raw = prefs.getString(_kName) ?? '';
    final cleaned = raw.trim();
    return cleaned.isEmpty ? 'User' : cleaned;
  }

  static Future<String> getUserEmail() async {
    final prefs = await SharedPreferences.getInstance();
    final raw = prefs.getString(_kEmail) ?? '';
    final cleaned = raw.trim();
    return cleaned.isEmpty ? '-' : cleaned;
  }

  static Future<String> getUserRole() async {
    final prefs = await SharedPreferences.getInstance();
    return (prefs.getString(_kRole) ?? '').trim();
  }

  // ── Ambil foto profil user (URL dari backend) ─────────────────────────────
  static Future<String?> getUserPhoto() async {
    final prefs = await SharedPreferences.getInstance();
    final raw = prefs.getString('user_photo');
    if (raw == null) return null;
    final cleaned = raw.trim();
    if (cleaned.isEmpty || cleaned.toLowerCase() == 'null') return null;
    return cleaned;
  }

  static Future<String> getUserId() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('user_id') ?? '';
  }

  // ── Header HTTP yang sudah termasuk token ────────────────
  static Future<Map<String, String>> authHeaders() async {
    final token = await getToken();
    return {
      'Authorization': 'Bearer ${token ?? ''}',
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    };
  }

  // ── Logout: panggil API + bersihkan semua data lokal ─────
  static Future<void> logout() async {
    try {
      final headers = await authHeaders();
      await http
          .post(Uri.parse(ApiConfig.logout), headers: headers)
          .timeout(const Duration(seconds: 8));
    } catch (_) {
      // Gagal koneksi pun tetap bersihkan data lokal
    }
    final prefs = await SharedPreferences.getInstance();
    await prefs.clear();
  }
}
