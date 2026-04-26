import 'package:flutter/material.dart';

import '../../core/services/auth_service.dart';
import '../../core/services/dashboard_kasir_service.dart';
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

class DashboardKasirPage extends StatefulWidget {
  const DashboardKasirPage({super.key});

  @override
  State<DashboardKasirPage> createState() => _DashboardKasirPageState();
}

class _DashboardKasirPageState extends State<DashboardKasirPage>
    with SingleTickerProviderStateMixin, SidebarMixin {
  bool _isLoading = true;
  bool _hasError = false;
  String _errorMessage = '';

  DashboardKasirSummary _summary = const DashboardKasirSummary();
  List<DashboardKasirTransaction> _recentTransactions = [];

  @override
  void initState() {
    super.initState();
    initSidebar(this);

    AppState.instance.userName.addListener(_onUserNameChanged);
    AppState.instance.dashboardRefreshTick.addListener(_onDashboardRefresh);

    _guardAndLoad();
  }

  @override
  void dispose() {
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
      if (showLoading) {
        _isLoading = true;
      }
      _hasError = false;
      _errorMessage = '';
    });

    try {
      final results = await Future.wait([
        DashboardKasirService.getSummary(),
        DashboardKasirService.getRecentTransactions(limit: 8),
      ]);

      if (!mounted) return;
      setState(() {
        _summary = results[0] as DashboardKasirSummary;
        _recentTransactions = results[1] as List<DashboardKasirTransaction>;
        _isLoading = false;
      });
    } catch (error) {
      final friendlyMessage = error
          .toString()
          .replaceFirst('Exception: ', '')
          .trim();

      if (!mounted) return;
      setState(() {
        _isLoading = false;
        _hasError = true;
        _errorMessage = friendlyMessage.isEmpty
            ? 'Periksa koneksi internet atau server, lalu coba lagi.'
            : friendlyMessage;
      });

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(_errorMessage),
          backgroundColor: Colors.red.shade600,
          duration: const Duration(seconds: 3),
        ),
      );
    }
  }

  void _redirectToLogin() {
    if (!mounted) return;
    Navigator.of(context).pushAndRemoveUntil(
      MaterialPageRoute(builder: (_) => const LoginPage()),
      (_) => false,
    );
  }

  void _openKasirPage() {
    Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => const KasirPage()),
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
                : _KasirDashboardContent(
                    userName: greetingName,
                    summary: _summary,
                    recentTransactions: _recentTransactions,
                    onMenuTap: openSidebar,
                    onTransaksiBaruTap: _openKasirPage,
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
              'Memuat dashboard kasir...',
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
                'Gagal memuat dashboard kasir',
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

class _KasirDashboardContent extends StatelessWidget {
  final String userName;
  final DashboardKasirSummary summary;
  final List<DashboardKasirTransaction> recentTransactions;
  final VoidCallback onMenuTap;
  final VoidCallback onTransaksiBaruTap;

  const _KasirDashboardContent({
    required this.userName,
    required this.summary,
    required this.recentTransactions,
    required this.onMenuTap,
    required this.onTransaksiBaruTap,
  });

  @override
  Widget build(BuildContext context) => SingleChildScrollView(
    physics: const AlwaysScrollableScrollPhysics(),
    child: Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        _KasirHeader(
          userName: userName,
          onMenuTap: onMenuTap,
          onTransaksiBaruTap: onTransaksiBaruTap,
        ),
        const SizedBox(height: 16),
        _KasirSummarySlider(summary: summary),
        const SizedBox(height: 14),
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16),
          child: _SectionCard(
            title: 'Transaksi Terbaru',
            subtitle: 'Data transaksi terbaru dari kasir',
            child: _KasirTransactionTable(items: recentTransactions),
          ),
        ),
        const SizedBox(height: 28),
      ],
    ),
  );
}

class _KasirHeader extends StatelessWidget {
  final String userName;
  final VoidCallback onMenuTap;
  final VoidCallback onTransaksiBaruTap;

  const _KasirHeader({
    required this.userName,
    required this.onMenuTap,
    required this.onTransaksiBaruTap,
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
              padding: const EdgeInsets.fromLTRB(20, 16, 20, 26),
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
                    'siap melayani transaksi hari ini',
                    style: TextStyle(
                      color: Colors.white70,
                      fontSize: 14,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                  const SizedBox(height: 20),
                  SizedBox(
                    height: 44,
                    child: ElevatedButton.icon(
                      onPressed: onTransaksiBaruTap,
                      icon: const Icon(Icons.point_of_sale_rounded, size: 18),
                      label: const Text(
                        'Transaksi Baru',
                        style: TextStyle(fontWeight: FontWeight.w700),
                      ),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.white,
                        foregroundColor: const Color(0xFF2B55D0),
                        elevation: 0,
                        padding: const EdgeInsets.symmetric(horizontal: 16),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                      ),
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

class _KasirSummarySlider extends StatelessWidget {
  final DashboardKasirSummary summary;

  const _KasirSummarySlider({required this.summary});

  static String _formatThousands(int value) {
    final text = value.toString();
    final buffer = StringBuffer();
    for (int i = 0; i < text.length; i++) {
      if (i > 0 && (text.length - i) % 3 == 0) buffer.write('.');
      buffer.write(text[i]);
    }
    return buffer.toString();
  }

  @override
  Widget build(BuildContext context) {
    final cards = [
      _KasirMetricCardData(
        label: 'Total Transaksi Hari Ini',
        value: _formatThousands(summary.totalTransactionsToday),
        icon: Icons.receipt_long_rounded,
        color: const Color(0xFF4169E1),
      ),
      _KasirMetricCardData(
        label: 'Pendapatan Hari Ini',
        value: 'Rp ${_formatThousands(summary.revenueToday.round())}',
        icon: Icons.payments_rounded,
        color: const Color(0xFF10B981),
      ),
      _KasirMetricCardData(
        label: 'Rata-rata Transaksi',
        value:
            'Rp ${_formatThousands(summary.averageTransactionToday.round())}',
        icon: Icons.show_chart_rounded,
        color: const Color(0xFFF59E0B),
      ),
      _KasirMetricCardData(
        label: 'Produk Terjual Hari Ini',
        value: _formatThousands(summary.soldProductsToday),
        icon: Icons.inventory_2_rounded,
        color: const Color(0xFF8B5CF6),
      ),
    ];

    return SizedBox(
      height: 110,
      child: ListView.builder(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 16),
        itemCount: cards.length,
        itemBuilder: (context, index) {
          final card = cards[index];
          return Padding(
            padding: EdgeInsets.only(right: index < cards.length - 1 ? 10 : 0),
            child: _KasirMetricCard(data: card),
          );
        },
      ),
    );
  }
}

class _KasirMetricCardData {
  final String label;
  final String value;
  final IconData icon;
  final Color color;

  const _KasirMetricCardData({
    required this.label,
    required this.value,
    required this.icon,
    required this.color,
  });
}

class _KasirMetricCard extends StatelessWidget {
  final _KasirMetricCardData data;

  const _KasirMetricCard({required this.data});

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 150,
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.06),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Container(
            padding: const EdgeInsets.all(7),
            decoration: BoxDecoration(
              color: data.color.withValues(alpha: 0.12),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(data.icon, color: data.color, size: 18),
          ),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                data.value,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: const TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                  color: Color(0xFF1F2937),
                ),
              ),
              const SizedBox(height: 2),
              Text(
                data.label,
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
                style: TextStyle(
                  fontSize: 11,
                  color: Colors.grey.shade600,
                  fontWeight: FontWeight.w500,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class _SectionCard extends StatelessWidget {
  final String title;
  final String? subtitle;
  final Widget child;

  const _SectionCard({required this.title, this.subtitle, required this.child});

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
          const SizedBox(height: 12),
          child,
        ],
      ),
    );
  }
}

class _KasirTransactionTable extends StatelessWidget {
  final List<DashboardKasirTransaction> items;

  const _KasirTransactionTable({required this.items});

  @override
  Widget build(BuildContext context) {
    if (items.isEmpty) {
      return const _EmptyState(
        icon: Icons.receipt_long_rounded,
        message: 'Belum ada transaksi terbaru',
      );
    }

    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      child: DataTable(
        headingRowHeight: 36,
        dataRowMinHeight: 36,
        dataRowMaxHeight: 44,
        columnSpacing: 12,
        headingTextStyle: const TextStyle(
          fontSize: 11,
          fontWeight: FontWeight.w600,
          color: Color(0xFF4A5568),
        ),
        dataTextStyle: const TextStyle(fontSize: 11, color: Color(0xFF2D3748)),
        columns: const [
          DataColumn(label: Text('Transaksi')),
          DataColumn(label: Text('Produk')),
          DataColumn(label: Text('Waktu')),
          DataColumn(label: Text('Total')),
          DataColumn(label: Text('Status')),
        ],
        rows: items.map((item) {
          final statusBg = item.isSuccess
              ? const Color(0xFF48BB78)
              : const Color(0xFFE53E3E);

          return DataRow(
            cells: [
              DataCell(
                Text(item.invoiceNumber, style: const TextStyle(fontSize: 10)),
              ),
              DataCell(Text(item.productName)),
              DataCell(Text(item.waktuLabel)),
              DataCell(Text(item.totalLabel)),
              DataCell(
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 10,
                    vertical: 3,
                  ),
                  decoration: BoxDecoration(
                    color: statusBg,
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Text(
                    item.isSuccess ? 'success' : 'gagal',
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 10,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                ),
              ),
            ],
          );
        }).toList(),
      ),
    );
  }
}

class _EmptyState extends StatelessWidget {
  final IconData icon;
  final String message;

  const _EmptyState({required this.icon, required this.message});

  @override
  Widget build(BuildContext context) => Center(
    child: Padding(
      padding: const EdgeInsets.symmetric(vertical: 28),
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
            child: Icon(icon, color: Colors.white, size: 32),
          ),
          const SizedBox(height: 12),
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
