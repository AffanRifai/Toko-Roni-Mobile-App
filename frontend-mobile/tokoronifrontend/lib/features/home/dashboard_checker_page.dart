import 'dart:async';
import 'package:flutter/material.dart';

import '../../core/services/auth_service.dart';
import '../../core/services/dashboard_checker_service.dart';
import '../../core/state/app_state.dart';
import '../../shared/widgets/notifikasi_widget.dart';
import '../../shared/widgets/profile_widget.dart';
import '../../shared/widgets/semua_notifikasi_page.dart';
import '../../shared/widgets/shared_widgets.dart';
import '../auth/login_page.dart';
import '../category/manajemen_kategori_page.dart';
import '../delivery/manajemen_pengiriman_page.dart';
import '../member/daftar_member_page.dart';
import '../product/daftar_produk_page.dart';
import '../profile/profile_page.dart';
import '../report/laporan_penjualan_page.dart';
import '../transaction/kasir_page.dart';
import '../transaction/riwayat_transaksi_page.dart';
import '../user/manajemen_pengguna_page.dart';
import '../vehicle/manajemen_kendaraan_page.dart';
import 'dashboard_router.dart';

class DashboardCheckerPage extends StatefulWidget {
  const DashboardCheckerPage({super.key});

  @override
  State<DashboardCheckerPage> createState() => _DashboardCheckerPageState();
}

class _DashboardCheckerPageState extends State<DashboardCheckerPage>
    with SingleTickerProviderStateMixin, SidebarMixin {
  late Timer _timer;
  DateTime _now = DateTime.now();

  bool _isLoading = true;
  bool _hasError = false;
  String _errorMessage = '';

  DashboardCheckerSummary _summary = const DashboardCheckerSummary();
  List<CheckerIssueItem> _lowStockItems = [];
  List<CheckerIssueItem> _expiringItems = [];
  List<CheckerIssueItem> _expiredItems = [];
  List<CheckerReportItem> _reports = [];

  bool _isSubmitting = false;

  @override
  void initState() {
    super.initState();
    initSidebar(this);

    _timer = Timer.periodic(const Duration(seconds: 1), (_) {
      if (mounted) setState(() => _now = DateTime.now());
    });

    AppState.instance.userName.addListener(_onUserNameChanged);
    AppState.instance.dashboardRefreshTick.addListener(_onDashboardRefresh);
    _guardAndLoad();
  }

  @override
  void dispose() {
    _timer.cancel();
    AppState.instance.userName.removeListener(_onUserNameChanged);
    AppState.instance.dashboardRefreshTick.removeListener(_onDashboardRefresh);
    disposeSidebar();
    super.dispose();
  }

  void _onUserNameChanged() {
    if (mounted) setState(() {});
  }

  void _onDashboardRefresh() {
    _loadDashboard(showLoading: false);
  }

  Future<void> _guardAndLoad() async {
    final loggedIn = await AuthService.isLoggedIn();
    if (!loggedIn) {
      _redirectToLogin();
      return;
    }
    await _loadDashboard();
  }

  Future<void> _loadDashboard({bool showLoading = true}) async {
    if (!mounted) return;

    setState(() {
      if (showLoading) _isLoading = true;
      _hasError = false;
      _errorMessage = '';
    });

    try {
      final data = await DashboardCheckerService.getDashboardData();
      if (!mounted) return;

      setState(() {
        _summary = data.summary;
        _lowStockItems = data.lowStockItems;
        _expiringItems = data.expiringItems;
        _expiredItems = data.expiredItems;
        _reports = data.recentReports;
        _isLoading = false;
      });
    } catch (error) {
      final message = error.toString().replaceFirst('Exception: ', '').trim();
      if (!mounted) return;
      setState(() {
        _isLoading = false;
        _hasError = true;
        _errorMessage = message.isEmpty
            ? 'Periksa koneksi internet atau server, lalu coba lagi.'
            : message;
      });
    }
  }

  Future<void> _openReportModal(
    CheckerIssueItem issueItem,
    String reportType,
  ) async {
    if (_isSubmitting) return;
    final result = await showDialog<_ReportFormResult>(
      context: context,
      barrierDismissible: !_isSubmitting,
      builder: (_) => _ReportProductDialog(
        productName: issueItem.namaProduk,
        initialQuantity: issueItem.stok > 0 ? issueItem.stok : null,
      ),
    );

    if (result == null) return;

    setState(() => _isSubmitting = true);
    try {
      final submit = await DashboardCheckerService.submitReport(
        issueItem: issueItem,
        reportType: reportType,
        notes: result.notes,
        quantity: result.quantity,
      );

      if (!mounted) return;
      await _loadDashboard(showLoading: false);

      if (submit.sentToServer) {
        await AppState.instance.refreshNotifications(force: true);
      } else {
        _injectLocalNotification(submit.report);
      }

      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(submit.message),
          backgroundColor: submit.sentToServer
              ? const Color(0xFF2F855A)
              : const Color(0xFFD69E2E),
        ),
      );
    } catch (error) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(error.toString().replaceFirst('Exception: ', '')),
          backgroundColor: const Color(0xFFE53E3E),
        ),
      );
    } finally {
      if (mounted) {
        setState(() => _isSubmitting = false);
      }
    }
  }

  void _injectLocalNotification(CheckerReportItem report) {
    final current = List<NotifItem>.from(AppState.instance.notifications.value);
    final nowId = 'local-report-${DateTime.now().millisecondsSinceEpoch}';

    current.insert(
      0,
      NotifItem(
        id: nowId,
        icon: Icons.report_problem_rounded,
        iconColor: const Color(0xFFD69E2E),
        judul: 'Laporan Produk Checker',
        pesan:
            '${report.productName} (${report.reportTypeLabel}) tersimpan dan menunggu sinkronisasi server.',
        waktu: 'Baru saja',
        tipe: 'report',
        sudahDibaca: false,
      ),
    );

    AppState.instance.notifications.value = current;
    AppState.instance.unreadCount.value = current
        .where((item) => !item.sudahDibaca)
        .length;
  }

  void _redirectToLogin() {
    if (!mounted) return;
    Navigator.of(context).pushAndRemoveUntil(
      MaterialPageRoute(builder: (_) => const LoginPage()),
      (_) => false,
    );
  }

  void _openProductPage() {
    Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => const DaftarProdukPage()),
    );
  }

  void _showInfo(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: const Color(0xFF3B6FE8),
      ),
    );
  }

  void _handleMenuTap(String menu) {
    if (menu == 'Dashboard') {
      closeSidebar();
      return;
    }

    Widget? page;
    switch (menu) {
      case 'Dashboard':
        page = DashboardRouter.pageForCurrentUser();
        break;
      case 'Pengguna':
        page = const ManajemenPenggunaPage();
        break;
      case 'Member':
        page = const DaftarMemberPage();
        break;
      case 'Laporan':
        page = const LaporanPenjualanPage();
        break;
      case 'Riwayat Transaksi':
        page = const RiwayatTransaksiPage();
        break;
      case 'Kasir':
        page = const KasirPage();
        break;
      case 'Produk':
        page = const DaftarProdukPage();
        break;
      case 'Kategori':
        page = const ManajemenKategoriPage();
        break;
      case 'Pengiriman':
        page = const ManajemenPengirimanPage();
        break;
      case 'Kendaraan':
        page = const ManajemenKendaraanPage();
        break;
      case 'Profile':
        page = const ProfilePage();
        break;
    }

    if (page != null) {
      closeSidebarThenNavigate(
        () => Navigator.push(context, MaterialPageRoute(builder: (_) => page!)),
      );
    }
  }

  String _formatDate(DateTime dt) {
    const days = [
      'Minggu',
      'Senin',
      'Selasa',
      'Rabu',
      'Kamis',
      'Jumat',
      'Sabtu',
    ];
    const months = [
      '',
      'Januari',
      'Februari',
      'Maret',
      'April',
      'Mei',
      'Juni',
      'Juli',
      'Agustus',
      'September',
      'Oktober',
      'November',
      'Desember',
    ];
    return '${days[dt.weekday % 7]}, ${dt.day} ${months[dt.month]} ${dt.year}';
  }

  String _formatTime(DateTime dt) =>
      '${dt.hour.toString().padLeft(2, '0')}.${dt.minute.toString().padLeft(2, '0')}';

  @override
  Widget build(BuildContext context) {
    final greetingName = AppState.instance.userName.value.trim().isEmpty
        ? 'User'
        : AppState.instance.userName.value.trim();

    return Scaffold(
      backgroundColor: const Color(0xFFF3F4F8),
      body: Stack(
        children: [
          RefreshIndicator(
            onRefresh: _loadDashboard,
            child: _isLoading
                ? const _LoadingState()
                : _hasError
                ? _ErrorState(onRetry: _loadDashboard, message: _errorMessage)
                : _CheckerDashboardContent(
                    userName: greetingName,
                    now: _now,
                    formatDate: _formatDate,
                    formatTime: _formatTime,
                    summary: _summary,
                    lowStockItems: _lowStockItems,
                    expiringItems: _expiringItems,
                    expiredItems: _expiredItems,
                    reports: _reports,
                    onMenuTap: openSidebar,
                    onOpenProducts: _openProductPage,
                    onOpenReportsInfo: () => _showInfo(
                      'Daftar laporan terbaru ditampilkan otomatis di halaman ini.',
                    ),
                    onReportTap: _openReportModal,
                    isSubmitting: _isSubmitting,
                  ),
          ),
          ...buildSidebarLayer(
            activeMenu: 'Dashboard',
            onMenuTap: _handleMenuTap,
          ),
        ],
      ),
    );
  }
}

class _LoadingState extends StatelessWidget {
  const _LoadingState();

  @override
  Widget build(BuildContext context) => SingleChildScrollView(
    physics: const AlwaysScrollableScrollPhysics(),
    child: SizedBox(
      height:
          MediaQuery.of(context).size.height -
          MediaQuery.of(context).padding.top -
          MediaQuery.of(context).padding.bottom,
      child: const Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            CircularProgressIndicator(color: Color(0xFF4169E1)),
            SizedBox(height: 16),
            Text(
              'Memuat dashboard checker...',
              style: TextStyle(color: Color(0xFF4A5568)),
            ),
          ],
        ),
      ),
    ),
  );
}

class _ErrorState extends StatelessWidget {
  final VoidCallback onRetry;
  final String message;

  const _ErrorState({required this.onRetry, required this.message});

  @override
  Widget build(BuildContext context) => SingleChildScrollView(
    physics: const AlwaysScrollableScrollPhysics(),
    child: SizedBox(
      height:
          MediaQuery.of(context).size.height -
          MediaQuery.of(context).padding.top -
          MediaQuery.of(context).padding.bottom,
      child: Center(
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 28),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(
                Icons.cloud_off_rounded,
                size: 64,
                color: Colors.grey.shade400,
              ),
              const SizedBox(height: 14),
              const Text(
                'Gagal memuat dashboard checker',
                style: TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.w600,
                  color: Color(0xFF2D3748),
                ),
              ),
              const SizedBox(height: 8),
              Text(
                message.isEmpty
                    ? 'Periksa koneksi internet atau server, lalu coba lagi.'
                    : message,
                textAlign: TextAlign.center,
                style: TextStyle(fontSize: 13, color: Colors.grey.shade500),
              ),
              const SizedBox(height: 22),
              ElevatedButton.icon(
                onPressed: onRetry,
                icon: const Icon(Icons.refresh_rounded, size: 18),
                label: const Text('Coba Lagi'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF4169E1),
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(
                    horizontal: 24,
                    vertical: 12,
                  ),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(10),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    ),
  );
}

class _CheckerDashboardContent extends StatelessWidget {
  final String userName;
  final DateTime now;
  final String Function(DateTime) formatDate;
  final String Function(DateTime) formatTime;
  final DashboardCheckerSummary summary;
  final List<CheckerIssueItem> lowStockItems;
  final List<CheckerIssueItem> expiringItems;
  final List<CheckerIssueItem> expiredItems;
  final List<CheckerReportItem> reports;
  final VoidCallback onMenuTap;
  final VoidCallback onOpenProducts;
  final VoidCallback onOpenReportsInfo;
  final Future<void> Function(CheckerIssueItem, String) onReportTap;
  final bool isSubmitting;

  const _CheckerDashboardContent({
    required this.userName,
    required this.now,
    required this.formatDate,
    required this.formatTime,
    required this.summary,
    required this.lowStockItems,
    required this.expiringItems,
    required this.expiredItems,
    required this.reports,
    required this.onMenuTap,
    required this.onOpenProducts,
    required this.onOpenReportsInfo,
    required this.onReportTap,
    required this.isSubmitting,
  });

  @override
  Widget build(BuildContext context) => SingleChildScrollView(
    physics: const AlwaysScrollableScrollPhysics(),
    child: Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        _CheckerHeader(
          userName: userName,
          now: now,
          formatDate: formatDate,
          formatTime: formatTime,
          onMenuTap: onMenuTap,
        ),
        const SizedBox(height: 16),
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16),
          child: _CheckerSummarySlider(summary: summary),
        ),
        const SizedBox(height: 14),
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16),
          child: _SectionCard(
            title: 'Stok Rendah / Menipis',
            subtitle: 'Produk yang perlu tindakan restok',
            onLihatSemua: onOpenProducts,
            child: _LowStockPanel(
              items: lowStockItems,
              onReportTap: onReportTap,
              isSubmitting: isSubmitting,
            ),
          ),
        ),
        const SizedBox(height: 12),
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16),
          child: _SectionCard(
            title: 'Produk Akan Kadaluarsa',
            subtitle: 'Produk yang akan kadaluarsa dalam 30 hari',
            onLihatSemua: onOpenProducts,
            child: _ExpiringPanel(
              items: expiringItems,
              onReportTap: onReportTap,
              isSubmitting: isSubmitting,
            ),
          ),
        ),
        const SizedBox(height: 12),
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16),
          child: _SectionCard(
            title: 'Produk Sudah Kadaluarsa',
            subtitle: 'Perlu tindakan urgent',
            onLihatSemua: onOpenProducts,
            child: _ExpiredPanel(
              items: expiredItems,
              onReportTap: onReportTap,
              isSubmitting: isSubmitting,
            ),
          ),
        ),
        const SizedBox(height: 12),
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16),
          child: _SectionCard(
            title: 'Laporan Terbaru',
            subtitle: 'Riwayat laporan checker barang',
            onLihatSemua: onOpenReportsInfo,
            child: _LatestReportsPanel(items: reports),
          ),
        ),
        const SizedBox(height: 28),
      ],
    ),
  );
}

class _CheckerHeader extends StatelessWidget {
  final String userName;
  final DateTime now;
  final String Function(DateTime) formatDate;
  final String Function(DateTime) formatTime;
  final VoidCallback onMenuTap;

  const _CheckerHeader({
    required this.userName,
    required this.now,
    required this.formatDate,
    required this.formatTime,
    required this.onMenuTap,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [Color(0xFF6B9FFF), Color(0xFF3B6FE8), Color(0xFF2B55D0)],
        ),
      ),
      child: Stack(
        children: [
          Positioned.fill(child: CustomPaint(painter: AppWavePainter())),
          SafeArea(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(20, 16, 20, 28),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      BurgerMenuButton(onTap: onMenuTap),
                      const Spacer(),
                      NotifikasiBell(
                        onLihatSemua: () => Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (_) => const SemuaNotifikasiPage(),
                          ),
                        ),
                      ),
                      const SizedBox(width: 12),
                      const ProfileWidget.fromAuth(),
                    ],
                  ),
                  const SizedBox(height: 24),
                  Text(
                    'Halo, $userName',
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 26,
                      fontWeight: FontWeight.bold,
                      height: 1.2,
                    ),
                  ),
                  const SizedBox(height: 6),
                  const Text(
                    'siap menjaga kualitas produk hari ini',
                    style: TextStyle(
                      color: Colors.white70,
                      fontSize: 14,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                  const SizedBox(height: 16),
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 14,
                      vertical: 10,
                    ),
                    decoration: BoxDecoration(
                      color: Colors.white.withValues(alpha: 0.18),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Row(
                      children: [
                        const Icon(
                          Icons.calendar_today_rounded,
                          size: 14,
                          color: Colors.white,
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: Text(
                            formatDate(now),
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 12,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                        ),
                        const Icon(
                          Icons.access_time_rounded,
                          size: 14,
                          color: Colors.white,
                        ),
                        const SizedBox(width: 5),
                        Text(
                          '${formatTime(now)} WIB',
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 12,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _CheckerSummarySlider extends StatefulWidget {
  final DashboardCheckerSummary summary;

  const _CheckerSummarySlider({required this.summary});

  @override
  State<_CheckerSummarySlider> createState() => _CheckerSummarySliderState();
}

class _CheckerSummarySliderState extends State<_CheckerSummarySlider> {
  late final PageController _controller;
  int _currentPage = 0;

  @override
  void initState() {
    super.initState();
    _controller = PageController(viewportFraction: 0.84);
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final cards = [
      _SummaryCardData(
        label: 'Stok Rendah',
        value: _formatThousands(widget.summary.stokRendah),
        icon: Icons.warning_amber_rounded,
        color: const Color(0xFFF59E0B),
      ),
      _SummaryCardData(
        label: 'Akan Kadaluarsa',
        value: _formatThousands(widget.summary.akanKadaluarsa),
        icon: Icons.schedule_rounded,
        color: const Color(0xFF3B82F6),
      ),
      _SummaryCardData(
        label: 'Sudah Kadaluarsa',
        value: _formatThousands(widget.summary.sudahKadaluarsa),
        icon: Icons.error_rounded,
        color: const Color(0xFFEF4444),
      ),
      _SummaryCardData(
        label: 'Produk Aktif',
        value: _formatThousands(widget.summary.produkAktif),
        icon: Icons.check_circle_rounded,
        color: const Color(0xFF10B981),
      ),
      _SummaryCardData(
        label: 'Total Kategori',
        value: _formatThousands(widget.summary.totalKategori),
        icon: Icons.category_rounded,
        color: const Color(0xFF8B5CF6),
      ),
      _SummaryCardData(
        label: 'Rasio Stok Rendah',
        value: '${widget.summary.rasioStokRendah.toStringAsFixed(1)}%',
        icon: Icons.pie_chart_rounded,
        color: const Color(0xFF0EA5E9),
      ),
    ];

    return Column(
      children: [
        SizedBox(
          height: 146,
          child: PageView.builder(
            controller: _controller,
            itemCount: cards.length,
            onPageChanged: (index) => setState(() => _currentPage = index),
            itemBuilder: (context, index) {
              return Padding(
                padding: const EdgeInsets.only(right: 10),
                child: _SummaryCard(data: cards[index]),
              );
            },
          ),
        ),
        const SizedBox(height: 10),
        Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: List.generate(cards.length, (index) {
            final active = index == _currentPage;
            return AnimatedContainer(
              duration: const Duration(milliseconds: 180),
              margin: const EdgeInsets.symmetric(horizontal: 3),
              width: active ? 18 : 7,
              height: 7,
              decoration: BoxDecoration(
                color: active
                    ? const Color(0xFF4169E1)
                    : const Color(0xFFD1D5DB),
                borderRadius: BorderRadius.circular(99),
              ),
            );
          }),
        ),
      ],
    );
  }

  String _formatThousands(int value) {
    final text = value.toString();
    final buffer = StringBuffer();
    for (int i = 0; i < text.length; i++) {
      if (i > 0 && (text.length - i) % 3 == 0) buffer.write('.');
      buffer.write(text[i]);
    }
    return buffer.toString();
  }
}

class _SummaryCardData {
  final String label;
  final String value;
  final IconData icon;
  final Color color;

  const _SummaryCardData({
    required this.label,
    required this.value,
    required this.icon,
    required this.color,
  });
}

class _SummaryCard extends StatelessWidget {
  final _SummaryCardData data;

  const _SummaryCard({required this.data});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.06),
            blurRadius: 12,
            offset: const Offset(0, 5),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: data.color.withValues(alpha: 0.12),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(data.icon, color: data.color, size: 20),
          ),
          const Spacer(),
          Text(
            data.value,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: const TextStyle(
              fontSize: 22,
              fontWeight: FontWeight.bold,
              color: Color(0xFF1F2937),
            ),
          ),
          const SizedBox(height: 4),
          Text(
            data.label,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: TextStyle(
              fontSize: 12,
              color: Colors.grey.shade600,
              fontWeight: FontWeight.w500,
            ),
          ),
        ],
      ),
    );
  }
}

class _SectionCard extends StatelessWidget {
  final String title;
  final String subtitle;
  final VoidCallback onLihatSemua;
  final Widget child;

  const _SectionCard({
    required this.title,
    required this.subtitle,
    required this.onLihatSemua,
    required this.child,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.fromLTRB(14, 14, 14, 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 12,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      title,
                      style: const TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.w700,
                        color: Color(0xFF1F2937),
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      subtitle,
                      style: TextStyle(
                        fontSize: 12,
                        color: Colors.grey.shade500,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ],
                ),
              ),
              TextButton(
                onPressed: onLihatSemua,
                child: const Text('Lihat Semua'),
              ),
            ],
          ),
          const SizedBox(height: 12),
          child,
        ],
      ),
    );
  }
}

class _LowStockPanel extends StatelessWidget {
  final List<CheckerIssueItem> items;
  final Future<void> Function(CheckerIssueItem, String) onReportTap;
  final bool isSubmitting;

  const _LowStockPanel({
    required this.items,
    required this.onReportTap,
    required this.isSubmitting,
  });

  @override
  Widget build(BuildContext context) {
    if (items.isEmpty) {
      return const _EmptyState(
        icon: Icons.inventory_2_rounded,
        message: 'Tidak ada produk stok rendah',
      );
    }

    return Column(
      children: items.take(6).map((item) {
        final minStock = item.stokMinimum <= 0 ? 10 : item.stokMinimum;
        final ratio = minStock <= 0
            ? 0.0
            : (item.stok / minStock).clamp(0.0, 1.0).toDouble();

        return Container(
          margin: const EdgeInsets.only(bottom: 10),
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(
            color: const Color(0xFFF8FAFF),
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: const Color(0xFFE3E9FF)),
          ),
          child: Column(
            children: [
              Row(
                children: [
                  Container(
                    width: 48,
                    height: 48,
                    decoration: BoxDecoration(
                      color: const Color(0xFFFDF3DE),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Stack(
                      clipBehavior: Clip.none,
                      children: [
                        const Center(
                          child: Icon(
                            Icons.inventory_2_rounded,
                            color: Color(0xFFD97706),
                            size: 22,
                          ),
                        ),
                        Positioned(
                          right: -2,
                          top: -2,
                          child: Container(
                            width: 18,
                            height: 18,
                            decoration: const BoxDecoration(
                              color: Color(0xFFFC8181),
                              shape: BoxShape.circle,
                            ),
                            child: const Icon(
                              Icons.priority_high_rounded,
                              size: 12,
                              color: Colors.white,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          item.namaProduk,
                          style: const TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.w700,
                            color: Color(0xFF1F2937),
                          ),
                        ),
                        const SizedBox(height: 3),
                        Text(
                          item.kategori,
                          style: TextStyle(
                            fontSize: 12,
                            color: Colors.grey.shade600,
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                      ],
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 10,
                      vertical: 5,
                    ),
                    decoration: BoxDecoration(
                      color: const Color(0xFFFDE9B9),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Text(
                      'Stok: ${item.stok}',
                      style: const TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w700,
                        color: Color(0xFF9A4A09),
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 10),
              Row(
                children: [
                  Expanded(
                    child: ClipRRect(
                      borderRadius: BorderRadius.circular(99),
                      child: LinearProgressIndicator(
                        value: ratio,
                        minHeight: 10,
                        backgroundColor: const Color(0xFFD8DBE3),
                        color: const Color(0xFFF59E0B),
                      ),
                    ),
                  ),
                  const SizedBox(width: 10),
                  ElevatedButton.icon(
                    onPressed: (isSubmitting || item.productId <= 0)
                        ? null
                        : () => onReportTap(item, 'low_stock'),
                    icon: const Icon(Icons.flag_rounded, size: 15),
                    label: const Text('Laporkan'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF4F46E5),
                      foregroundColor: Colors.white,
                      disabledBackgroundColor: Colors.grey.shade300,
                      padding: const EdgeInsets.symmetric(horizontal: 12),
                      elevation: 0,
                      minimumSize: const Size(0, 36),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(10),
                      ),
                    ),
                  ),
                ],
              ),
            ],
          ),
        );
      }).toList(),
    );
  }
}

class _ExpiringPanel extends StatelessWidget {
  final List<CheckerIssueItem> items;
  final Future<void> Function(CheckerIssueItem, String) onReportTap;
  final bool isSubmitting;

  const _ExpiringPanel({
    required this.items,
    required this.onReportTap,
    required this.isSubmitting,
  });

  @override
  Widget build(BuildContext context) {
    if (items.isEmpty) {
      return const _EmptyState(
        icon: Icons.event_available_rounded,
        message: 'Tidak ada produk yang akan kadaluarsa 30 hari ke depan',
      );
    }

    return Column(
      children: items.take(6).map((item) {
        final left = item.daysLeft ?? 0;
        final ratio = (left / 30).clamp(0.0, 1.0).toDouble();
        final dateText = item.expiryDate == null
            ? '-'
            : '${item.expiryDate!.day.toString().padLeft(2, '0')} '
                  '${_month(item.expiryDate!.month)} ${item.expiryDate!.year}';

        return Container(
          margin: const EdgeInsets.only(bottom: 10),
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(
            color: const Color(0xFFF8FAFF),
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: const Color(0xFFE3E9FF)),
          ),
          child: Column(
            children: [
              Row(
                children: [
                  Container(
                    width: 48,
                    height: 48,
                    decoration: BoxDecoration(
                      color: const Color(0xFFF4ECCC),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: const Center(
                      child: Icon(
                        Icons.schedule_rounded,
                        color: Color(0xFFD18B00),
                        size: 22,
                      ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          item.namaProduk,
                          style: const TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.w700,
                            color: Color(0xFF1F2937),
                          ),
                        ),
                        const SizedBox(height: 3),
                        Text(
                          item.kategori,
                          style: TextStyle(
                            fontSize: 12,
                            color: Colors.grey.shade600,
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                        const SizedBox(height: 6),
                        Row(
                          children: [
                            Icon(
                              Icons.calendar_month_rounded,
                              size: 14,
                              color: Colors.grey.shade500,
                            ),
                            const SizedBox(width: 6),
                            Text(
                              dateText,
                              style: TextStyle(
                                fontSize: 12,
                                color: Colors.grey.shade700,
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 10,
                      vertical: 5,
                    ),
                    decoration: BoxDecoration(
                      color: left <= 3
                          ? const Color(0xFFFEE2E2)
                          : const Color(0xFFFDECC8),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Text(
                      left == 0 ? 'Hari ini' : '$left hari lagi',
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w700,
                        color: left <= 3
                            ? const Color(0xFFB42318)
                            : const Color(0xFFA15C07),
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 10),
              Row(
                children: [
                  Expanded(
                    child: Column(
                      children: [
                        Row(
                          children: [
                            Text(
                              'Sisa waktu',
                              style: TextStyle(
                                fontSize: 11,
                                color: Colors.grey.shade600,
                              ),
                            ),
                            const Spacer(),
                            Text(
                              '$left dari 30 hari',
                              style: TextStyle(
                                fontSize: 11,
                                color: Colors.grey.shade600,
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 4),
                        ClipRRect(
                          borderRadius: BorderRadius.circular(99),
                          child: LinearProgressIndicator(
                            value: ratio,
                            minHeight: 8,
                            backgroundColor: const Color(0xFFD8DBE3),
                            color: left <= 3
                                ? const Color(0xFFEF4444)
                                : left <= 7
                                ? const Color(0xFFF97316)
                                : const Color(0xFFEAB308),
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 10),
                  ElevatedButton.icon(
                    onPressed: (isSubmitting || item.productId <= 0)
                        ? null
                        : () => onReportTap(item, 'expiring'),
                    icon: const Icon(Icons.flag_rounded, size: 15),
                    label: const Text('Laporkan'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF4F46E5),
                      foregroundColor: Colors.white,
                      disabledBackgroundColor: Colors.grey.shade300,
                      padding: const EdgeInsets.symmetric(horizontal: 12),
                      elevation: 0,
                      minimumSize: const Size(0, 36),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(10),
                      ),
                    ),
                  ),
                ],
              ),
            ],
          ),
        );
      }).toList(),
    );
  }

  static String _month(int month) {
    const names = [
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
    if (month < 1 || month > 12) return '-';
    return names[month];
  }
}

class _ExpiredPanel extends StatelessWidget {
  final List<CheckerIssueItem> items;
  final Future<void> Function(CheckerIssueItem, String) onReportTap;
  final bool isSubmitting;

  const _ExpiredPanel({
    required this.items,
    required this.onReportTap,
    required this.isSubmitting,
  });

  @override
  Widget build(BuildContext context) {
    if (items.isEmpty) {
      return const _EmptyState(
        icon: Icons.verified_rounded,
        message: 'Tidak ada produk kadaluarsa',
      );
    }

    return Column(
      children: items.take(6).map((item) {
        final daysExpired = item.daysExpired ?? 0;
        return Container(
          margin: const EdgeInsets.only(bottom: 10),
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(
            color: const Color(0xFFF8FAFF),
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: const Color(0xFFE3E9FF)),
          ),
          child: Row(
            children: [
              Container(
                width: 48,
                height: 48,
                decoration: BoxDecoration(
                  color: const Color(0xFFFDE2E4),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Center(
                  child: Icon(
                    Icons.warning_rounded,
                    color: Color(0xFFDC2626),
                    size: 22,
                  ),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      item.namaProduk,
                      style: const TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.w700,
                        color: Color(0xFF1F2937),
                      ),
                    ),
                    const SizedBox(height: 3),
                    Text(
                      item.kategori,
                      style: TextStyle(
                        fontSize: 12,
                        color: Colors.grey.shade600,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                    const SizedBox(height: 6),
                    Text(
                      'Stok: ${item.stok}',
                      style: TextStyle(
                        fontSize: 12,
                        color: Colors.grey.shade700,
                      ),
                    ),
                  ],
                ),
              ),
              Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 10,
                      vertical: 5,
                    ),
                    decoration: BoxDecoration(
                      color: const Color(0xFFFEE2E2),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Text(
                      '$daysExpired hari expired',
                      style: const TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w700,
                        color: Color(0xFFB42318),
                      ),
                    ),
                  ),
                  const SizedBox(height: 8),
                  ElevatedButton.icon(
                    onPressed: (isSubmitting || item.productId <= 0)
                        ? null
                        : () => onReportTap(item, 'expired'),
                    icon: const Icon(Icons.flag_rounded, size: 15),
                    label: const Text('Laporkan'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFFDC2626),
                      foregroundColor: Colors.white,
                      disabledBackgroundColor: Colors.grey.shade300,
                      padding: const EdgeInsets.symmetric(horizontal: 12),
                      elevation: 0,
                      minimumSize: const Size(0, 36),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(10),
                      ),
                    ),
                  ),
                ],
              ),
            ],
          ),
        );
      }).toList(),
    );
  }
}

class _LatestReportsPanel extends StatelessWidget {
  final List<CheckerReportItem> items;

  const _LatestReportsPanel({required this.items});

  @override
  Widget build(BuildContext context) {
    if (items.isEmpty) {
      return const _EmptyState(
        icon: Icons.flag_rounded,
        message: 'Belum ada laporan terbaru',
      );
    }

    final sorted = [...items]
      ..sort((a, b) => b.createdAt.compareTo(a.createdAt));
    return Column(
      children: sorted.take(8).map((item) {
        final statusColor = _statusColor(item.status);
        return Container(
          margin: const EdgeInsets.only(bottom: 8),
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(
            color: const Color(0xFFF8FAFF),
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: const Color(0xFFE3E9FF)),
          ),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                width: 44,
                height: 44,
                decoration: BoxDecoration(
                  color: statusColor.withValues(alpha: 0.15),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(
                  Icons.access_time_filled_rounded,
                  color: statusColor,
                  size: 20,
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      item.productName,
                      style: const TextStyle(
                        fontSize: 15,
                        fontWeight: FontWeight.w700,
                        color: Color(0xFF1F2937),
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      item.reportTypeLabel,
                      style: TextStyle(
                        fontSize: 12,
                        color: Colors.grey.shade600,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      item.notes,
                      style: TextStyle(
                        fontSize: 12,
                        color: Colors.grey.shade700,
                      ),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 6),
                    Text(
                      _formatRelative(item.createdAt),
                      style: TextStyle(
                        fontSize: 11,
                        color: Colors.grey.shade500,
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 8),
              Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 10,
                      vertical: 4,
                    ),
                    decoration: BoxDecoration(
                      color: statusColor.withValues(alpha: 0.15),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Text(
                      item.statusLabel,
                      style: TextStyle(
                        color: statusColor,
                        fontSize: 11,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                  ),
                  const SizedBox(height: 6),
                  if (!item.isSynced)
                    const Text(
                      'Lokal',
                      style: TextStyle(
                        fontSize: 10,
                        color: Color(0xFFD69E2E),
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                ],
              ),
            ],
          ),
        );
      }).toList(),
    );
  }

  static Color _statusColor(String status) {
    switch (status) {
      case 'resolved':
        return const Color(0xFF2F855A);
      case 'in_progress':
        return const Color(0xFF3182CE);
      default:
        return const Color(0xFFD69E2E);
    }
  }

  static String _formatRelative(DateTime dt) {
    final diff = DateTime.now().difference(dt);
    if (diff.inMinutes < 1) return 'Baru saja';
    if (diff.inMinutes < 60) return '${diff.inMinutes} menit lalu';
    if (diff.inHours < 24) return '${diff.inHours} jam lalu';
    if (diff.inDays < 7) return '${diff.inDays} hari lalu';
    final weeks = (diff.inDays / 7).floor();
    return '$weeks minggu lalu';
  }
}

class _ReportProductDialog extends StatefulWidget {
  final String productName;
  final int? initialQuantity;

  const _ReportProductDialog({
    required this.productName,
    required this.initialQuantity,
  });

  @override
  State<_ReportProductDialog> createState() => _ReportProductDialogState();
}

class _ReportProductDialogState extends State<_ReportProductDialog> {
  final _notesCtrl = TextEditingController();
  final _qtyCtrl = TextEditingController();
  final _formKey = GlobalKey<FormState>();

  @override
  void initState() {
    super.initState();
    if (widget.initialQuantity != null && widget.initialQuantity! > 0) {
      _qtyCtrl.text = widget.initialQuantity.toString();
    }
  }

  @override
  void dispose() {
    _notesCtrl.dispose();
    _qtyCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Dialog(
      insetPadding: const EdgeInsets.symmetric(horizontal: 18, vertical: 24),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
      child: Padding(
        padding: const EdgeInsets.fromLTRB(16, 14, 16, 16),
        child: Form(
          key: _formKey,
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  const Expanded(
                    child: Text(
                      'Laporkan Produk',
                      style: TextStyle(
                        fontSize: 19,
                        fontWeight: FontWeight.w600,
                        color: Color(0xFF0F172A),
                      ),
                    ),
                  ),
                  IconButton(
                    onPressed: () => Navigator.pop(context),
                    icon: const Icon(Icons.close_rounded),
                    color: const Color(0xFF94A3B8),
                  ),
                ],
              ),
              const SizedBox(height: 6),
              Text(
                'Produk: ${widget.productName}',
                style: const TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.w600,
                  color: Color(0xFF1E293B),
                ),
              ),
              const SizedBox(height: 14),
              const Text(
                'Catatan Laporan *',
                style: TextStyle(
                  fontSize: 14,
                  color: Color(0xFF1E293B),
                  fontWeight: FontWeight.w500,
                ),
              ),
              const SizedBox(height: 8),
              TextFormField(
                controller: _notesCtrl,
                minLines: 4,
                maxLines: 5,
                style: const TextStyle(fontSize: 14),
                decoration: InputDecoration(
                  hintText: 'Jelaskan kondisi produk...',
                  hintStyle: const TextStyle(color: Color(0xFF94A3B8)),
                  filled: true,
                  fillColor: const Color(0xFFF8FAFC),
                  contentPadding: const EdgeInsets.all(14),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(16),
                    borderSide: const BorderSide(color: Color(0xFFD1D5DB)),
                  ),
                  enabledBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(16),
                    borderSide: const BorderSide(color: Color(0xFFD1D5DB)),
                  ),
                  focusedBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(16),
                    borderSide: const BorderSide(color: Color(0xFF6366F1)),
                  ),
                ),
                validator: (value) {
                  final text = (value ?? '').trim();
                  if (text.isEmpty) return 'Catatan laporan wajib diisi';
                  if (text.length < 6) return 'Catatan minimal 6 karakter';
                  return null;
                },
              ),
              const SizedBox(height: 14),
              const Text(
                'Jumlah Produk (Opsional)',
                style: TextStyle(
                  fontSize: 14,
                  color: Color(0xFF1E293B),
                  fontWeight: FontWeight.w500,
                ),
              ),
              const SizedBox(height: 8),
              TextFormField(
                controller: _qtyCtrl,
                keyboardType: TextInputType.number,
                style: const TextStyle(fontSize: 14),
                decoration: InputDecoration(
                  hintText: 'Masukkan jumlah',
                  hintStyle: const TextStyle(color: Color(0xFF94A3B8)),
                  filled: true,
                  fillColor: const Color(0xFFF8FAFC),
                  contentPadding: const EdgeInsets.symmetric(
                    horizontal: 14,
                    vertical: 12,
                  ),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(16),
                    borderSide: const BorderSide(color: Color(0xFFD1D5DB)),
                  ),
                  enabledBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(16),
                    borderSide: const BorderSide(color: Color(0xFFD1D5DB)),
                  ),
                  focusedBorder: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(16),
                    borderSide: const BorderSide(color: Color(0xFF6366F1)),
                  ),
                ),
              ),
              const SizedBox(height: 18),
              Row(
                children: [
                  Expanded(
                    child: OutlinedButton(
                      onPressed: () => Navigator.pop(context),
                      style: OutlinedButton.styleFrom(
                        foregroundColor: const Color(0xFF334155),
                        side: const BorderSide(color: Color(0xFFD1D5DB)),
                        minimumSize: const Size(0, 48),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(16),
                        ),
                      ),
                      child: const Text(
                        'Batal',
                        style: TextStyle(
                          fontSize: 15,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: ElevatedButton.icon(
                      onPressed: () {
                        if (!_formKey.currentState!.validate()) return;
                        final qty = int.tryParse(_qtyCtrl.text.trim());
                        Navigator.pop(
                          context,
                          _ReportFormResult(
                            notes: _notesCtrl.text.trim(),
                            quantity: qty != null && qty > 0 ? qty : null,
                          ),
                        );
                      },
                      label: const Text(
                        'Kirim Laporan',
                        style: TextStyle(
                          fontSize: 15,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF4F46E5),
                        foregroundColor: Colors.white,
                        minimumSize: const Size(0, 48),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(16),
                        ),
                        elevation: 0,
                      ),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _ReportFormResult {
  final String notes;
  final int? quantity;

  const _ReportFormResult({required this.notes, required this.quantity});
}

class _EmptyState extends StatelessWidget {
  final IconData icon;
  final String message;

  const _EmptyState({required this.icon, required this.message});

  @override
  Widget build(BuildContext context) => Center(
    child: Padding(
      padding: const EdgeInsets.symmetric(vertical: 24),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            width: 60,
            height: 60,
            decoration: const BoxDecoration(
              color: Color(0xFF48BB78),
              shape: BoxShape.circle,
            ),
            child: Icon(icon, color: Colors.white, size: 30),
          ),
          const SizedBox(height: 10),
          Text(
            message,
            textAlign: TextAlign.center,
            style: const TextStyle(
              fontSize: 14,
              fontWeight: FontWeight.w500,
              color: Color(0xFF2D3748),
            ),
          ),
        ],
      ),
    ),
  );
}
