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
  // Sesuai api.php baru: prefix 'auth'
  static const String login = '$baseUrl/auth/login'; // POST  /api/v1/auth/login
  static const String faceLogin =
      '$baseUrl/auth/face-login'; // POST  /api/v1/auth/face-login
  static const String logout =
      '$baseUrl/auth/logout'; // POST  /api/v1/auth/logout
  static const String profile =
      '$baseUrl/auth/profile'; // GET   /api/v1/auth/profile

  // ─── Dashboard endpoints ─────────────────────────────────
  static const String dashboardStats = '$baseUrl/dashboard/stats';
  static const String dashboardChart = '$baseUrl/dashboard/chart-data';

  // ─── Dashboard notifications ─────────────────────────────
  // dashboardNotifications: dipakai di dashboard_service.dart untuk expiring products
  // Sementara pakai stats endpoint, expiring diambil dari sana
  static const String dashboardNotifications = '$baseUrl/dashboard/stats';
  static const String notifications = '$baseUrl/notifications/unread';

  // ─── Transaction endpoints ───────────────────────────────
  static const String transactionsRecent = '$baseUrl/transactions/recent';

  // ─── Product endpoints ───────────────────────────────────
  static const String productSearch = '$baseUrl/products/search';
  static const String productLowStock = '$baseUrl/products/low-stock';
}
