// ============================================================
// lib/core/api_config.dart
// Ganti baseUrl dengan IP/URL server Laravel kamu
// ============================================================

class ApiConfig {
  // ─── Ganti sesuai environment kamu ───────────────────────
  //
  // Kalau pakai emulator Android  → http://10.0.2.2:8000/api
  // Kalau pakai device fisik      → http://IP_KOMPUTER_KAMU:8000/api
  // Kalau sudah deploy ke server  → https://domain-kamu.com/api
  //
  static const String baseUrl = 'http://192.168.1.5:8000/api/v1';

  // ─── Auth endpoints ──────────────────────────────────────
  static const String login     = '$baseUrl/login';       // POST  /api/v1/auth/login
  static const String faceLogin = '$baseUrl/face-login';  // POST  /api/v1/auth/face-login
  static const String logout    = '$baseUrl/logout';      // POST  /api/v1/auth/logout  (butuh token)
  static const String profile   = '$baseUrl/profile';     // GET   /api/v1/auth/profile (butuh token)

  // ─── Dashboard endpoints ─────────────────────────────────
  static const String dashboardStats         = '$baseUrl/dashboard/stats';
  static const String dashboardChart         = '$baseUrl/dashboard/chart-data';
  static const String dashboardNotifications = '$baseUrl/dashboard/notifications';

  // ─── Transaction endpoints ───────────────────────────────
  static const String transactionsRecent     = '$baseUrl/transactions/recent';

  // ─── Product endpoints ───────────────────────────────────
  static const String productSearch          = '$baseUrl/products/search';
  static const String productLowStock        = '$baseUrl/products/low-stock';
}