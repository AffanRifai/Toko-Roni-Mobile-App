import 'package:flutter/material.dart';

import '../../core/services/auth_service.dart';
import '../../core/services/dashboard_gudang_service.dart';
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
import '../product/produk_form_page.dart';
import '../profile/profile_page.dart';
import '../report/laporan_penjualan_page.dart';
import '../transaction/kasir_page.dart';
import '../transaction/riwayat_transaksi_page.dart';
import '../user/manajemen_pengguna_page.dart';
import '../vehicle/manajemen_kendaraan_page.dart';
import 'dashboard_router.dart';

class DashboardGudangPage extends StatefulWidget {
  const DashboardGudangPage({super.key});

  @override
  State<DashboardGudangPage> createState() => _DashboardGudangPageState();
}

class _DashboardGudangPageState extends State<DashboardGudangPage>
    with SingleTickerProviderStateMixin, SidebarMixin {
  bool _isLoading = true;
  bool _hasError = false;
  String _errorMessage = '';

  DashboardGudangSummary _summary = const DashboardGudangSummary();
  List<DashboardGudangLowStockItem> _lowStockItems = [];
  List<DashboardGudangCategoryItem> _categoryItems = [];
  List<DashboardGudangStockUpdateItem> _stockUpdates = [];

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
      if (showLoading) _isLoading = true;
      _hasError = false;
      _errorMessage = '';
    });

    try {
      final data = await DashboardGudangService.getDashboardData();
      if (!mounted) return;

      setState(() {
        _summary = data.summary;
        _lowStockItems = data.lowStockItems;
        _categoryItems = data.categoryItems;
        _stockUpdates = data.stockUpdates;
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

  void _openTambahProduk() {
    Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => const TambahProdukPage()),
    ).then((result) {
      if (result == true) {
        _loadDashboard(showLoading: false);
      }
    });
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
                : _GudangDashboardContent(
                    userName: greetingName,
                    summary: _summary,
                    lowStockItems: _lowStockItems,
                    categoryItems: _categoryItems,
                    stockUpdates: _stockUpdates,
                    onMenuTap: openSidebar,
                    onTambahProdukTap: _openTambahProduk,
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
              'Memuat dashboard gudang...',
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
                'Gagal memuat dashboard gudang',
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

class _GudangDashboardContent extends StatelessWidget {
  final String userName;
  final DashboardGudangSummary summary;
  final List<DashboardGudangLowStockItem> lowStockItems;
  final List<DashboardGudangCategoryItem> categoryItems;
  final List<DashboardGudangStockUpdateItem> stockUpdates;
  final VoidCallback onMenuTap;
  final VoidCallback onTambahProdukTap;

  const _GudangDashboardContent({
    required this.userName,
    required this.summary,
    required this.lowStockItems,
    required this.categoryItems,
    required this.stockUpdates,
    required this.onMenuTap,
    required this.onTambahProdukTap,
  });

  @override
  Widget build(BuildContext context) => SingleChildScrollView(
    physics: const AlwaysScrollableScrollPhysics(),
    child: Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        _GudangHeader(
          userName: userName,
          onMenuTap: onMenuTap,
          onTambahProdukTap: onTambahProdukTap,
        ),
        const SizedBox(height: 16),
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16),
          child: _GudangSummarySlider(summary: summary),
        ),
        const SizedBox(height: 14),
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16),
          child: _SectionCard(
            title: 'Produk Stok Rendah',
            subtitle: 'Pantau produk yang perlu segera restok',
            child: _LowStockTable(items: lowStockItems),
          ),
        ),
        const SizedBox(height: 12),
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16),
          child: _SectionCard(
            title: 'Daftar Kategori Produk',
            subtitle: 'Ringkasan jumlah produk per kategori',
            child: _CategoryTable(items: categoryItems),
          ),
        ),
        const SizedBox(height: 12),
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16),
          child: _SectionCard(
            title: 'Update Data Stok Produk Terbaru',
            subtitle: 'Perubahan terakhir data stok produk',
            child: _StockUpdateList(items: stockUpdates),
          ),
        ),
        const SizedBox(height: 28),
      ],
    ),
  );
}

class _GudangHeader extends StatelessWidget {
  final String userName;
  final VoidCallback onMenuTap;
  final VoidCallback onTambahProdukTap;

  const _GudangHeader({
    required this.userName,
    required this.onMenuTap,
    required this.onTambahProdukTap,
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
                    'monitor persediaan gudang hari ini',
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
                      onPressed: onTambahProdukTap,
                      icon: const Icon(Icons.add_box_rounded, size: 18),
                      label: const Text(
                        'Tambah Produk',
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

class _GudangSummarySlider extends StatefulWidget {
  final DashboardGudangSummary summary;

  const _GudangSummarySlider({required this.summary});

  @override
  State<_GudangSummarySlider> createState() => _GudangSummarySliderState();
}

class _GudangSummarySliderState extends State<_GudangSummarySlider> {
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
        label: 'Total Produk',
        value: _formatThousands(widget.summary.totalProduk),
        icon: Icons.inventory_2_rounded,
        color: const Color(0xFF4169E1),
      ),
      _SummaryCardData(
        label: 'Stok Rendah',
        value: _formatThousands(widget.summary.stokRendah),
        icon: Icons.warning_amber_rounded,
        color: const Color(0xFFE53E3E),
      ),
      _SummaryCardData(
        label: 'Nilai Inventori',
        value: 'Rp ${_formatThousands(widget.summary.nilaiInventori.round())}',
        icon: Icons.account_balance_wallet_rounded,
        color: const Color(0xFF10B981),
      ),
      _SummaryCardData(
        label: 'Total Terjual (Unit)',
        value: _formatThousands(widget.summary.totalTerjualUnit),
        icon: Icons.local_shipping_rounded,
        color: const Color(0xFF0EA5E9),
      ),
      _SummaryCardData(
        label: 'Total Pendapatan',
        value: 'Rp ${_formatThousands(widget.summary.totalPendapatan.round())}',
        icon: Icons.payments_rounded,
        color: const Color(0xFFF59E0B),
      ),
      _SummaryCardData(
        label: 'Rata-rata per Item',
        value:
            'Rp ${_formatThousands(widget.summary.rataRataHargaPerItem.round())}',
        icon: Icons.bar_chart_rounded,
        color: const Color(0xFF8B5CF6),
      ),
    ];

    return Column(
      children: [
        SizedBox(
          height: 152,
          child: PageView.builder(
            controller: _controller,
            itemCount: cards.length,
            onPageChanged: (index) {
              if (mounted) setState(() => _currentPage = index);
            },
            itemBuilder: (context, index) {
              final data = cards[index];
              return Padding(
                padding: const EdgeInsets.only(right: 10),
                child: _SummaryCard(data: data),
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

  static String _formatThousands(int value) {
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

class _LowStockTable extends StatelessWidget {
  final List<DashboardGudangLowStockItem> items;

  const _LowStockTable({required this.items});

  @override
  Widget build(BuildContext context) {
    if (items.isEmpty) {
      return const _EmptyState(
        icon: Icons.inventory_2_outlined,
        message: 'Tidak ada produk dengan stok rendah',
      );
    }

    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      child: DataTable(
        headingRowHeight: 36,
        dataRowMinHeight: 36,
        dataRowMaxHeight: 42,
        columnSpacing: 12,
        headingTextStyle: const TextStyle(
          fontSize: 11,
          fontWeight: FontWeight.w600,
          color: Color(0xFF4A5568),
        ),
        dataTextStyle: const TextStyle(fontSize: 11, color: Color(0xFF2D3748)),
        columns: const [
          DataColumn(label: Text('Produk')),
          DataColumn(label: Text('Kategori')),
          DataColumn(label: Text('Stok')),
          DataColumn(label: Text('Stok Min')),
        ],
        rows: items.map((item) {
          return DataRow(
            cells: [
              DataCell(Text(item.namaProduk)),
              DataCell(Text(item.kategori)),
              DataCell(
                Text(
                  item.stok.toString(),
                  style: const TextStyle(
                    fontWeight: FontWeight.w700,
                    color: Color(0xFFE53E3E),
                  ),
                ),
              ),
              DataCell(Text(item.stokMinimum.toString())),
            ],
          );
        }).toList(),
      ),
    );
  }
}

class _CategoryTable extends StatelessWidget {
  final List<DashboardGudangCategoryItem> items;

  const _CategoryTable({required this.items});

  @override
  Widget build(BuildContext context) {
    if (items.isEmpty) {
      return const _EmptyState(
        icon: Icons.category_outlined,
        message: 'Data kategori belum tersedia',
      );
    }

    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      child: DataTable(
        headingRowHeight: 36,
        dataRowMinHeight: 36,
        dataRowMaxHeight: 42,
        columnSpacing: 12,
        headingTextStyle: const TextStyle(
          fontSize: 11,
          fontWeight: FontWeight.w600,
          color: Color(0xFF4A5568),
        ),
        dataTextStyle: const TextStyle(fontSize: 11, color: Color(0xFF2D3748)),
        columns: const [
          DataColumn(label: Text('Kategori')),
          DataColumn(label: Text('Total Produk')),
          DataColumn(label: Text('Status')),
        ],
        rows: items.map((item) {
          return DataRow(
            cells: [
              DataCell(Text(item.namaKategori)),
              DataCell(Text(item.totalProduk.toString())),
              DataCell(
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 10,
                    vertical: 3,
                  ),
                  decoration: BoxDecoration(
                    color: item.aktif
                        ? const Color(0xFF48BB78)
                        : const Color(0xFFE53E3E),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Text(
                    item.aktif ? 'aktif' : 'nonaktif',
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 10,
                      fontWeight: FontWeight.w600,
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

class _StockUpdateList extends StatelessWidget {
  final List<DashboardGudangStockUpdateItem> items;

  const _StockUpdateList({required this.items});

  @override
  Widget build(BuildContext context) {
    if (items.isEmpty) {
      return const _EmptyState(
        icon: Icons.update_rounded,
        message: 'Belum ada update stok terbaru',
      );
    }

    return Column(
      children: items.map((item) {
        final timeLabel = _formatDateTime(item.updatedAt);
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
                  Icons.inventory_2_rounded,
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
                      item.namaProduk,
                      style: const TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w700,
                        color: Color(0xFF1F2937),
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      '${item.kategori} • Stok: ${item.stok}',
                      style: TextStyle(
                        fontSize: 12,
                        color: Colors.grey.shade700,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      'Update: $timeLabel',
                      style: TextStyle(
                        fontSize: 11,
                        color: Colors.grey.shade500,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        );
      }).toList(),
    );
  }

  static String _formatDateTime(DateTime? value) {
    if (value == null) return '-';

    const months = [
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

    final dt = value.toLocal();
    final hh = dt.hour.toString().padLeft(2, '0');
    final mm = dt.minute.toString().padLeft(2, '0');
    return '${dt.day.toString().padLeft(2, '0')} ${months[dt.month]} ${dt.year}, $hh:$mm';
  }
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
