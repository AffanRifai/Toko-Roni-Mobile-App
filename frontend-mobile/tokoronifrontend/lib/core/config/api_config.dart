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

  // Face biometrics mode:
  // - true  : backend adalah sumber kebenaran descriptor (direkomendasikan)
  // - false : client masih mengirim descriptor seperti mode lama
  static const bool faceServerComputesDescriptor = true;

  // Saat mode backend aktif, fallback descriptor client tetap dikirim
  // hanya untuk kompatibilitas sementara backend lama.
  static const bool faceLegacyDescriptorFallback = false;

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
  static const String realtimeConfig = '$baseUrl/utils/realtime';

  // ─── Transaction endpoints ───────────────────────────────
  static const String transactionIndex = '$baseUrl/transactions';
  static const String transactionsRecent = '$baseUrl/transactions/recent';
  static String transactionDetail(int transactionId) =>
      '$transactionIndex/$transactionId';

  // Report endpoints
  static const String reportExportPdf = '$baseUrl/reports/export/pdf';

  // ─── Product endpoints ───────────────────────────────────
  static const String productIndex = '$baseUrl/products';
  static const String productSearch = '$baseUrl/products/search';
  static const String productLowStock = '$baseUrl/products/low-stock';
  static const String productCategories = '$baseUrl/products/categories';
  static const String productStatistics =
      '$baseUrl/products/statistics/overview';
  static const String checkerReportIndex = '$baseUrl/checker/reports';

  static const String categoryIndex = '$baseUrl/categories';

  static String productReport(int productId) =>
      '$baseUrl/products/$productId/report';

  // Member management endpoints
  static const String memberIndex = '$baseUrl/members';
  static const String memberSearch = '$baseUrl/members/search';
  static const String memberStatistics = '$baseUrl/members/statistics/overview';

  static String memberDetail(int memberId) => '$baseUrl/members/$memberId';
  static String memberData(int memberId) => '$baseUrl/members/$memberId/data';
  static String memberReceivables(int memberId) =>
      '$baseUrl/members/$memberId/receivables';
  static String memberTransactions(int memberId) =>
      '$baseUrl/members/$memberId/transactions';
  static String memberToggleStatus(int memberId) =>
      '$baseUrl/members/$memberId/toggle-status';

  // User management endpoints
  static const String userIndex = '$baseUrl/users';
  static const String userLegacyIndex = '$baseUrl/pengguna';
  static const String userFaceRegisterEndpoint =
      '$baseUrl/users/faces/register';
  static const String userFaceRegisterLegacyEndpoint =
      '$baseUrl/pengguna/faces/register';

  static String userFaceRegister(int userId) =>
      '$baseUrl/users/$userId/face-register';
  static String userFaceRegisterLegacy(int userId) =>
      '$baseUrl/pengguna/$userId/face-register';

  // Delivery management endpoints
  static const String deliveryIndex = '$baseUrl/deliveries';
  static const String deliveryTodayStats = '$baseUrl/deliveries/today-stats';
  static const String deliveryMyDeliveries =
      '$baseUrl/deliveries/my-deliveries';
  static const String deliveryAvailableDrivers =
      '$baseUrl/deliveries/available-drivers';
  static const String deliveryAvailableVehicles =
      '$baseUrl/deliveries/available-vehicles';

  static String deliveryDetail(int deliveryId) =>
      '$baseUrl/deliveries/$deliveryId';
  static String deliveryAssign(int deliveryId) =>
      '$baseUrl/deliveries/$deliveryId/assign';
  static String deliveryUpdateStatus(int deliveryId) =>
      '$baseUrl/deliveries/$deliveryId/status';

  // Vehicle management endpoints
  static const String vehicleIndex = '$baseUrl/vehicles';
  static const String vehicleAvailable = '$baseUrl/vehicles/available';
  static const String vehicleInMaintenance = '$baseUrl/vehicles/in-maintenance';
  static const String vehicleStatistics =
      '$baseUrl/vehicles/statistics/overview';

  static String vehicleDetail(int vehicleId) => '$baseUrl/vehicles/$vehicleId';
  static String vehicleSetMaintenance(int vehicleId) =>
      '$baseUrl/vehicles/$vehicleId/maintenance';
  static String vehicleSetAvailable(int vehicleId) =>
      '$baseUrl/vehicles/$vehicleId/available';
}
