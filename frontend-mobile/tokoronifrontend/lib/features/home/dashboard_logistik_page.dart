import 'dart:math' as math;

import 'package:flutter/material.dart';

import '../../core/access/role_access.dart';
import '../../core/services/auth_service.dart';
import '../../core/services/dashboard_logistik_service.dart';
import '../../core/state/app_state.dart';
import '../../core/ui/live_clock_controller.dart';
import '../../models/kendaraan_model.dart';
import '../../models/pengiriman_model.dart';
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

class DashboardLogistikPage extends StatefulWidget {
  const DashboardLogistikPage({super.key});

  @override
  State<DashboardLogistikPage> createState() => _DashboardLogistikPageState();
}

class _DashboardLogistikPageState extends State<DashboardLogistikPage>
    with SingleTickerProviderStateMixin, SidebarMixin {
  late final LiveClockController _clock;

  bool _isLoading = true;
  bool _hasError = false;
  String _errorMessage = '';
  String _chartFilter = '7 Hari';

  DashboardLogistikSummary _summary = const DashboardLogistikSummary();
  List<PengirimanItem> _pengirimanSaya = [];
  List<KendaraanItem> _armada = [];
  List<PengirimanItem> _chartSource = [];
  DashboardLogistikChartData _chartData = const DashboardLogistikChartData(
    labels: [],
    totalPengiriman: [],
    pengirimanTerkirim: [],
  );

  bool get _isStaffLogistik =>
      RoleAccess.isStaffLogistik(AppState.instance.userRole.value);

  @override
  void initState() {
    super.initState();
    initSidebar(this);
    _clock = LiveClockController();

    AppState.instance.userName.addListener(_onUserNameChanged);
    AppState.instance.dashboardRefreshTick.addListener(_onDashboardRefresh);
    _guardAndLoad();
  }

  @override
  void dispose() {
    _clock.dispose();
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
      final data = await DashboardLogistikService.getDashboardData(
        onlyMyDeliveries: _isStaffLogistik,
        chartFilter: _chartFilter,
      );
      if (!mounted) return;

      setState(() {
        _summary = data.summary;
        _pengirimanSaya = data.pengirimanSaya;
        _armada = data.armada;
        _chartSource = data.chartSourceDeliveries;
        _chartData = data.chartData;
        _isLoading = false;
      });
    } catch (e) {
      final message = e.toString().replaceFirst('Exception: ', '').trim();
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

  void _changeChartFilter(String value) {
    if (_chartFilter == value) return;
    setState(() {
      _chartFilter = value;
      _chartData = DashboardLogistikService.buildChartData(
        deliveries: _chartSource,
        filter: value,
      );
    });
  }

  void _redirectToLogin() {
    if (!mounted) return;
    Navigator.of(context).pushAndRemoveUntil(
      MaterialPageRoute(builder: (_) => const LoginPage()),
      (_) => false,
    );
  }

  void _openPengirimanPage() {
    Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => const ManajemenPengirimanPage()),
    );
  }

  void _openKendaraanPage() {
    Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => const ManajemenKendaraanPage()),
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
                : _DashboardContent(
                    userName: greetingName,
                    clock: _clock,
                    formatDate: _formatDate,
                    formatTime: _formatTime,
                    summary: _summary,
                    pengirimanSaya: _pengirimanSaya,
                    armada: _armada,
                    chartData: _chartData,
                    chartFilter: _chartFilter,
                    onChangeChartFilter: _changeChartFilter,
                    onMenuTap: openSidebar,
                    onLihatSemuaPengirimanTap: _openPengirimanPage,
                    onLihatSemuaArmadaTap: _openKendaraanPage,
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
              'Memuat dashboard logistik...',
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
                'Gagal memuat dashboard logistik',
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

class _DashboardContent extends StatelessWidget {
  final String userName;
  final LiveClockController clock;
  final String Function(DateTime) formatDate;
  final String Function(DateTime) formatTime;
  final DashboardLogistikSummary summary;
  final List<PengirimanItem> pengirimanSaya;
  final List<KendaraanItem> armada;
  final DashboardLogistikChartData chartData;
  final String chartFilter;
  final ValueChanged<String> onChangeChartFilter;
  final VoidCallback onMenuTap;
  final VoidCallback onLihatSemuaPengirimanTap;
  final VoidCallback onLihatSemuaArmadaTap;

  const _DashboardContent({
    required this.userName,
    required this.clock,
    required this.formatDate,
    required this.formatTime,
    required this.summary,
    required this.pengirimanSaya,
    required this.armada,
    required this.chartData,
    required this.chartFilter,
    required this.onChangeChartFilter,
    required this.onMenuTap,
    required this.onLihatSemuaPengirimanTap,
    required this.onLihatSemuaArmadaTap,
  });

  @override
  Widget build(BuildContext context) {
    final topPengirimanSaya = pengirimanSaya.take(10).toList();
    final topArmada = armada.take(10).toList();

    return SingleChildScrollView(
      physics: const AlwaysScrollableScrollPhysics(),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _Header(
            userName: userName,
            clock: clock,
            formatDate: formatDate,
            formatTime: formatTime,
            onMenuTap: onMenuTap,
          ),
          const SizedBox(height: 16),
          _SummaryCards(summary: summary),
          const SizedBox(height: 14),
          _SectionCard(
            title: 'Pengiriman Saya',
            subtitle: 'Pengiriman yang ditugaskan kepada Anda',
            onLihatSemua: onLihatSemuaPengirimanTap,
            child: _PengirimanSayaList(items: topPengirimanSaya),
          ),
          const SizedBox(height: 12),
          _SectionCard(
            title: 'Daftar Armada / Kendaraan',
            subtitle: 'Status armada saat ini',
            onLihatSemua: onLihatSemuaArmadaTap,
            child: _ArmadaList(items: topArmada),
          ),
          const SizedBox(height: 12),
          _SectionCard(
            title: 'Kinerja Pengiriman',
            subtitle: 'Statistik pengiriman Anda $chartFilter terakhir',
            child: _KinerjaChart(
              data: chartData,
              filter: chartFilter,
              onFilterChanged: onChangeChartFilter,
            ),
          ),
          const SizedBox(height: 28),
        ],
      ),
    );
  }
}

class _Header extends StatelessWidget {
  final String userName;
  final LiveClockController clock;
  final String Function(DateTime) formatDate;
  final String Function(DateTime) formatTime;
  final VoidCallback onMenuTap;

  const _Header({
    required this.userName,
    required this.clock,
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
                    'siap mengelola pengiriman hari ini',
                    style: TextStyle(
                      color: Colors.white70,
                      fontSize: 14,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                  const SizedBox(height: 20),
                  ValueListenableBuilder<DateTime>(
                    valueListenable: clock,
                    builder: (context, now, child) => Row(
                      children: [
                        _HeaderInfoCard(
                          icon: Icons.calendar_today_rounded,
                          iconBg: const Color(0xFF4A90D9),
                          label: 'Tanggal',
                          value: formatDate(now),
                        ),
                        const SizedBox(width: 12),
                        _HeaderInfoCard(
                          icon: Icons.access_time_rounded,
                          iconBg: const Color(0xFF38A169),
                          label: 'Waktu',
                          value: formatTime(now),
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

class _HeaderInfoCard extends StatelessWidget {
  final IconData icon;
  final Color iconBg;
  final String label;
  final String value;

  const _HeaderInfoCard({
    required this.icon,
    required this.iconBg,
    required this.label,
    required this.value,
  });

  @override
  Widget build(BuildContext context) => Container(
    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
    decoration: BoxDecoration(
      color: Colors.white,
      borderRadius: BorderRadius.circular(14),
      boxShadow: [
        BoxShadow(
          color: Colors.black.withValues(alpha: 0.08),
          blurRadius: 8,
          offset: const Offset(0, 3),
        ),
      ],
    ),
    child: Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Container(
          padding: const EdgeInsets.all(8),
          decoration: BoxDecoration(
            color: iconBg,
            borderRadius: BorderRadius.circular(8),
          ),
          child: Icon(icon, color: Colors.white, size: 18),
        ),
        const SizedBox(width: 10),
        Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              label,
              style: const TextStyle(fontSize: 11, color: Colors.grey),
            ),
            const SizedBox(height: 2),
            Text(
              value,
              style: const TextStyle(
                fontSize: 13,
                fontWeight: FontWeight.bold,
                color: Color(0xFF2D3748),
              ),
            ),
          ],
        ),
      ],
    ),
  );
}

class _SummaryCards extends StatelessWidget {
  final DashboardLogistikSummary summary;

  const _SummaryCards({required this.summary});

  @override
  Widget build(BuildContext context) {
    final cards = [
      _SummaryCardData(
        label: 'Pengiriman Hari Ini',
        value: _fmtNum(summary.pengirimanHariIni),
        icon: Icons.today_rounded,
        color: const Color(0xFF4169E1),
      ),
      _SummaryCardData(
        label: 'Dalam Proses',
        value: _fmtNum(summary.pengirimanDalamProses),
        icon: Icons.local_shipping_rounded,
        color: const Color(0xFFF59E0B),
      ),
      _SummaryCardData(
        label: 'Barang Dikirim',
        value: '${_fmtNum(summary.barangDikirim)} unit',
        icon: Icons.inventory_2_rounded,
        color: const Color(0xFF10B981),
      ),
      _SummaryCardData(
        label: 'On Time Rate',
        value: '${summary.onTimeRate.toStringAsFixed(1)}%',
        icon: Icons.timelapse_rounded,
        color: const Color(0xFF8B5CF6),
      ),
      _SummaryCardData(
        label: 'Armada Tersedia',
        value: _fmtNum(summary.armadaTersedia),
        icon: Icons.directions_car_rounded,
        color: const Color(0xFF0EA5E9),
      ),
    ];

    return SizedBox(
      height: 120,
      child: ListView.builder(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 16),
        itemCount: cards.length,
        itemBuilder: (context, index) {
          return _SummaryCard(data: cards[index]);
        },
      ),
    );
  }

  String _fmtNum(int value) {
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
      width: 160,
      margin: const EdgeInsets.only(right: 12),
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
  final String? subtitle;
  final VoidCallback? onLihatSemua;
  final Widget child;

  const _SectionCard({
    required this.title,
    this.subtitle,
    this.onLihatSemua,
    required this.child,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Container(
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
                      if (subtitle != null) ...[
                        const SizedBox(height: 2),
                        Text(
                          subtitle!,
                          style: TextStyle(
                            fontSize: 12,
                            color: Colors.grey.shade500,
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                      ],
                    ],
                  ),
                ),
                if (onLihatSemua != null)
                  GestureDetector(
                    onTap: onLihatSemua,
                    child: const Row(
                      children: [
                        Text(
                          'Lihat semua',
                          style: TextStyle(
                            fontSize: 12,
                            color: Color(0xFF4169E1),
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                        SizedBox(width: 4),
                        Icon(
                          Icons.arrow_forward_rounded,
                          size: 14,
                          color: Color(0xFF4169E1),
                        ),
                      ],
                    ),
                  ),
              ],
            ),
            const SizedBox(height: 12),
            child,
          ],
        ),
      ),
    );
  }
}

class _PengirimanSayaList extends StatelessWidget {
  final List<PengirimanItem> items;

  const _PengirimanSayaList({required this.items});

  String _formatEta(String raw) {
    final text = raw.trim();
    if (text.isEmpty) return '-';
    final parsed = DateTime.tryParse(text)?.toLocal();
    if (parsed == null) return '-';
    final dd = parsed.day.toString().padLeft(2, '0');
    final mm = parsed.month.toString().padLeft(2, '0');
    final hh = parsed.hour.toString().padLeft(2, '0');
    final mi = parsed.minute.toString().padLeft(2, '0');
    return '$dd/$mm/${parsed.year} $hh:$mi';
  }

  @override
  Widget build(BuildContext context) {
    if (items.isEmpty) {
      return const _EmptyState(
        icon: Icons.local_shipping_rounded,
        message: 'Belum ada pengiriman untuk akun Anda',
      );
    }

    final sorted = [...items]
      ..sort((a, b) => b.createdAt.compareTo(a.createdAt));

    return Column(
      children: sorted.take(10).map((item) {
        return Container(
          margin: const EdgeInsets.only(bottom: 8),
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
          decoration: BoxDecoration(
            color: const Color(0xFFF8FAFF),
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: const Color(0xFFE3E9FF)),
          ),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                width: 34,
                height: 34,
                decoration: BoxDecoration(
                  color: const Color(0xFF4169E1).withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Icon(
                  Icons.local_shipping_rounded,
                  size: 18,
                  color: Color(0xFF4169E1),
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      item.kodePengiriman,
                      style: const TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w700,
                        color: Color(0xFF1F2937),
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      'Invoice: ${item.invoice.isEmpty ? '-' : item.invoice}',
                      style: TextStyle(
                        fontSize: 12,
                        color: Colors.grey.shade700,
                        fontWeight: FontWeight.w500,
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 6),
                    Text(
                      'Tujuan: ${item.tujuan}',
                      style: TextStyle(
                        fontSize: 11,
                        color: Colors.grey.shade600,
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 2),
                    Text(
                      'Armada: ${(item.kendaraan ?? '-')}',
                      style: TextStyle(
                        fontSize: 11,
                        color: Colors.grey.shade600,
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 2),
                    Text(
                      'Estimasi tiba: ${_formatEta(item.estimatedDeliveryRaw)}',
                      style: TextStyle(
                        fontSize: 11,
                        color: Colors.grey.shade600,
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 8),
              _StatusBadge(status: item.status),
            ],
          ),
        );
      }).toList(),
    );
  }
}

class _ArmadaList extends StatelessWidget {
  final List<KendaraanItem> items;

  const _ArmadaList({required this.items});

  @override
  Widget build(BuildContext context) {
    if (items.isEmpty) {
      return const _EmptyState(
        icon: Icons.directions_car_filled_rounded,
        message: 'Data armada belum tersedia',
      );
    }

    return Column(
      children: items.take(10).map((item) {
        final statusColor = kendaraanStatusColor(item.status);

        return Container(
          margin: const EdgeInsets.only(bottom: 8),
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
          decoration: BoxDecoration(
            color: const Color(0xFFF8FAFF),
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: const Color(0xFFE3E9FF)),
          ),
          child: Row(
            children: [
              Container(
                width: 34,
                height: 34,
                decoration: BoxDecoration(
                  color: statusColor.withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Icon(
                  Icons.directions_car_rounded,
                  size: 18,
                  color: statusColor,
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      item.nama,
                      style: const TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w700,
                        color: Color(0xFF1F2937),
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      '${item.jenis} - ${item.platNomor}',
                      style: TextStyle(
                        fontSize: 12,
                        color: Colors.grey.shade700,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ],
                ),
              ),
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
                  item.status,
                  style: TextStyle(
                    color: statusColor,
                    fontSize: 11,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ),
            ],
          ),
        );
      }).toList(),
    );
  }
}

class _StatusBadge extends StatelessWidget {
  final String status;

  const _StatusBadge({required this.status});

  @override
  Widget build(BuildContext context) {
    final color = _statusColor(status);
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.15),
        borderRadius: BorderRadius.circular(20),
      ),
      child: Text(
        status,
        style: TextStyle(
          color: color,
          fontSize: 10,
          fontWeight: FontWeight.w700,
        ),
      ),
    );
  }

  static Color _statusColor(String status) {
    switch (status) {
      case 'Terkirim':
        return const Color(0xFF38A169);
      case 'Dalam Perjalanan':
        return const Color(0xFF3B6FE8);
      case 'Diambil':
        return const Color(0xFF6B5CE7);
      case 'Assigned':
        return const Color(0xFF805AD5);
      case 'Diproses':
        return const Color(0xFFD69E2E);
      case 'Pending':
        return const Color(0xFFECC94B);
      case 'Gagal':
        return const Color(0xFFE53E3E);
      case 'Dibatalkan':
        return const Color(0xFF718096);
      default:
        return const Color(0xFF4A5568);
    }
  }
}

class _KinerjaChart extends StatelessWidget {
  final DashboardLogistikChartData data;
  final String filter;
  final ValueChanged<String> onFilterChanged;

  const _KinerjaChart({
    required this.data,
    required this.filter,
    required this.onFilterChanged,
  });

  @override
  Widget build(BuildContext context) {
    final chartWidth = math.max(
      MediaQuery.of(context).size.width - 56,
      data.labels.length * 70.0,
    );

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            const _LegendDot(
              color: Color(0xFF4169E1),
              label: 'Total Pengiriman',
            ),
            const SizedBox(width: 12),
            const _LegendDot(color: Color(0xFF48BB78), label: 'Terkirim'),
            const Spacer(),
            Container(
              height: 30,
              padding: const EdgeInsets.symmetric(horizontal: 8),
              decoration: BoxDecoration(
                border: Border.all(color: Colors.grey.shade300),
                borderRadius: BorderRadius.circular(8),
              ),
              child: DropdownButton<String>(
                value: filter,
                underline: const SizedBox(),
                icon: const Icon(Icons.keyboard_arrow_down_rounded, size: 18),
                isDense: true,
                items: const ['7 Hari', '30 Hari', '90 Hari']
                    .map((e) => DropdownMenuItem(value: e, child: Text(e)))
                    .toList(),
                onChanged: (value) {
                  if (value != null) onFilterChanged(value);
                },
              ),
            ),
          ],
        ),
        const SizedBox(height: 10),
        if (data.isEmpty)
          const SizedBox(
            height: 160,
            child: Center(
              child: Text(
                'Data grafik belum tersedia',
                style: TextStyle(color: Color(0xFF4A5568)),
              ),
            ),
          )
        else
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: SizedBox(
              width: chartWidth,
              height: 280,
              child: CustomPaint(
                size: Size(chartWidth, 280),
                painter: _DeliveryLineChartPainter(
                  totalSeries: data.totalPengiriman,
                  deliveredSeries: data.pengirimanTerkirim,
                  labels: data.labels,
                ),
              ),
            ),
          ),
      ],
    );
  }
}

class _LegendDot extends StatelessWidget {
  final Color color;
  final String label;

  const _LegendDot({required this.color, required this.label});

  @override
  Widget build(BuildContext context) => Row(
    children: [
      Container(
        width: 10,
        height: 10,
        decoration: BoxDecoration(color: color, shape: BoxShape.circle),
      ),
      const SizedBox(width: 5),
      Text(
        label,
        style: const TextStyle(fontSize: 11, color: Color(0xFF4A5568)),
      ),
    ],
  );
}

class _DeliveryLineChartPainter extends CustomPainter {
  final List<double> totalSeries;
  final List<double> deliveredSeries;
  final List<String> labels;

  const _DeliveryLineChartPainter({
    required this.totalSeries,
    required this.deliveredSeries,
    required this.labels,
  });

  @override
  void paint(Canvas canvas, Size size) {
    if (labels.isEmpty) return;

    const left = 40.0;
    const right = 18.0;
    const top = 18.0;
    const bottom = 52.0;

    final width = size.width - left - right;
    final height = size.height - top - bottom;

    final maxTotal = totalSeries.isEmpty
        ? 0
        : totalSeries.reduce((a, b) => a > b ? a : b);
    final maxDelivered = deliveredSeries.isEmpty
        ? 0
        : deliveredSeries.reduce((a, b) => a > b ? a : b);
    final maxValue = math.max(1.0, math.max(maxTotal, maxDelivered)).toDouble();

    canvas.drawRect(
      Rect.fromLTWH(left, top, width, height),
      Paint()..color = const Color(0xFFFAFAFC),
    );

    final grid = Paint()
      ..color = const Color(0xFFE5E7EB)
      ..strokeWidth = 0.8;
    for (int i = 0; i <= 4; i++) {
      final y = top + height - (i / 4) * height;
      canvas.drawLine(Offset(left, y), Offset(size.width - right, y), grid);
    }

    final axis = Paint()
      ..color = const Color(0xFF9CA3AF)
      ..strokeWidth = 1.2;
    canvas.drawLine(Offset(left, top), Offset(left, top + height), axis);
    canvas.drawLine(
      Offset(left, top + height),
      Offset(size.width - right, top + height),
      axis,
    );

    _drawYLabels(canvas, left, top, height, maxValue);
    if (totalSeries.length > 1) {
      _drawSeries(
        canvas,
        data: totalSeries,
        maxVal: maxValue,
        left: left,
        top: top,
        width: width,
        height: height,
        color: const Color(0xFF4169E1),
      );
    }
    if (deliveredSeries.length > 1) {
      _drawSeries(
        canvas,
        data: deliveredSeries,
        maxVal: maxValue,
        left: left,
        top: top,
        width: width,
        height: height,
        color: const Color(0xFF48BB78),
      );
    }
    _drawXLabels(canvas, size, left, top, width, height, bottom);
  }

  void _drawYLabels(
    Canvas canvas,
    double left,
    double top,
    double height,
    double maxValue,
  ) {
    final tp = TextPainter(textDirection: TextDirection.ltr);
    for (int i = 0; i <= 4; i++) {
      tp.text = TextSpan(
        text: ((i / 4) * maxValue).round().toString(),
        style: const TextStyle(
          fontSize: 10,
          fontWeight: FontWeight.w500,
          color: Color(0xFF6B7280),
        ),
      );
      tp.layout();
      final y = top + height - (i / 4) * height;
      tp.paint(canvas, Offset(left - tp.width - 8, y - tp.height / 2));
    }
  }

  void _drawXLabels(
    Canvas canvas,
    Size size,
    double left,
    double top,
    double width,
    double height,
    double bottom,
  ) {
    final tp = TextPainter(textDirection: TextDirection.ltr);
    for (int i = 0; i < labels.length; i++) {
      final x =
          left +
          (labels.length == 1 ? width / 2 : (i / (labels.length - 1)) * width);
      tp.text = TextSpan(
        text: labels[i],
        style: const TextStyle(
          fontSize: 11,
          fontWeight: FontWeight.w500,
          color: Color(0xFF6B7280),
        ),
      );
      tp.layout();
      tp.paint(
        canvas,
        Offset(
          (x - tp.width / 2).clamp(left, size.width - tp.width),
          size.height - bottom + 12,
        ),
      );
      canvas.drawLine(
        Offset(x, top + height),
        Offset(x, top + height + 5),
        Paint()
          ..color = const Color(0xFFD1D5DB)
          ..strokeWidth = 0.8,
      );
    }
  }

  void _drawSeries(
    Canvas canvas, {
    required List<double> data,
    required double maxVal,
    required double left,
    required double top,
    required double width,
    required double height,
    required Color color,
  }) {
    final path = Path();
    for (int i = 0; i < data.length; i++) {
      final x = left + (i / (data.length - 1)) * width;
      final y = top + height - (data[i] / maxVal) * height;
      if (i == 0) {
        path.moveTo(x, y);
      } else {
        final px = left + ((i - 1) / (data.length - 1)) * width;
        final py = top + height - (data[i - 1] / maxVal) * height;
        path.cubicTo(px + (x - px) / 2, py, px + (x - px) / 2, y, x, y);
      }
    }

    canvas.drawPath(
      path,
      Paint()
        ..color = color.withValues(alpha: 0.15)
        ..strokeWidth = 5
        ..style = PaintingStyle.stroke
        ..strokeCap = StrokeCap.round,
    );

    canvas.drawPath(
      path,
      Paint()
        ..color = color
        ..strokeWidth = 3
        ..style = PaintingStyle.stroke
        ..strokeCap = StrokeCap.round,
    );

    for (int i = 0; i < data.length; i++) {
      final x = left + (i / (data.length - 1)) * width;
      final y = top + height - (data[i] / maxVal) * height;
      canvas.drawCircle(
        Offset(x, y),
        6,
        Paint()..color = color.withValues(alpha: 0.2),
      );
      canvas.drawCircle(Offset(x, y), 4, Paint()..color = color);
      canvas.drawCircle(Offset(x, y), 1.7, Paint()..color = Colors.white);
    }
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
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
