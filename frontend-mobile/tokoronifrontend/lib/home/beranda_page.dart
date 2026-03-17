// ============================================================
// lib/home/beranda_page.dart
// ============================================================

import 'dart:async';
import 'package:flutter/material.dart';
import '/shared_widgets.dart';
import 'menu_pages.dart';
import '/product/daftar_produk_page.dart';
import '/core/auth_service.dart';
import '/core/dashboard_service.dart';
import '/auth/login_page.dart'; // sesuaikan path

// ════════════════════════════════════════════════════════════════════════════
// BERANDA PAGE
// ════════════════════════════════════════════════════════════════════════════
class BerandaPage extends StatefulWidget {
  final String userName;
  const BerandaPage({super.key, this.userName = 'User'});

  @override
  State<BerandaPage> createState() => _BerandaPageState();
}

class _BerandaPageState extends State<BerandaPage>
    with SingleTickerProviderStateMixin, SidebarMixin {
  // ── Waktu ──────────────────────────────────────────────────
  late Timer _timer;
  late DateTime _now;

  // ── Data dashboard ─────────────────────────────────────────
  bool _isLoading = true;
  bool _hasError = false;
  DashboardStats _stats = const DashboardStats();
  List<StokMenipisItem> _lowStock = [];
  List<KadaluarsaItem> _expiring = [];
  List<TransaksiItem> _transaksi = [];

  @override
  void initState() {
    super.initState();
    initSidebar(this);
    _now = DateTime.now();
    _timer = Timer.periodic(const Duration(seconds: 1), (_) {
      if (mounted) setState(() => _now = DateTime.now());
    });
    _guardAndLoad();
  }

  // ── Route guard + load data ───────────────────────────────
  Future<void> _guardAndLoad() async {
    final loggedIn = await AuthService.isLoggedIn();
    if (!loggedIn) {
      _redirectToLogin();
      return;
    }
    await _loadAllData();
  }

  Future<void> _loadAllData() async {
    if (!mounted) return;
    setState(() {
      _isLoading = true;
      _hasError = false;
    });

    try {
      final results = await Future.wait([
        DashboardService.getStats(),
        DashboardService.getLowStockProducts(),
        DashboardService.getExpiringProducts(),
        DashboardService.getRecentTransactions(),
      ]);

      if (!mounted) return;
      setState(() {
        _stats = results[0] as DashboardStats;
        _lowStock = results[1] as List<StokMenipisItem>;
        _expiring = results[2] as List<KadaluarsaItem>;
        _transaksi = results[3] as List<TransaksiItem>;
        _isLoading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _isLoading = false;
        _hasError = true;
      });
    }
  }

  void _redirectToLogin() {
    if (!mounted) return;
    Navigator.of(context).pushAndRemoveUntil(
      MaterialPageRoute(builder: (_) => const LoginPage()),
      (_) => false,
    );
  }

  @override
  void dispose() {
    _timer.cancel();
    disposeSidebar();
    super.dispose();
  }

  // ── Navigasi sidebar ──────────────────────────────────────
  void _handleMenuTap(String menu) {
    closeSidebar();
    if (menu == 'Dashboard') return;
    Widget? page;
    switch (menu) {
      case 'Pengguna':
        page = const PenggunaPage();
        break;
      case 'Member':
        page = const MemberPage();
        break;
      case 'Laporan':
        page = const LaporanPage();
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
        page = const KategoriPage();
        break;
      case 'Pengiriman':
        page = const PengirimanPage();
        break;
      case 'Kendaraan':
        page = const KendaraanPage();
        break;
      case 'Profile':
        page = const ProfilePage();
        break;
    }
    if (page != null) {
      Navigator.push(context, MaterialPageRoute(builder: (_) => page!));
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
    return Scaffold(
      backgroundColor: const Color(0xFFF3F4F8),
      body: Stack(
        children: [
          RefreshIndicator(
            onRefresh: _loadAllData,
            child: _isLoading
                ? _LoadingState()
                : _hasError
                ? _ErrorState(onRetry: _loadAllData)
                : _BerandaContent(
                    now: _now,
                    userName: widget.userName,
                    formatDate: _formatDate,
                    formatTime: _formatTime,
                    onMenuTap: openSidebar,
                    stats: _stats,
                    lowStock: _lowStock,
                    expiring: _expiring,
                    transaksi: _transaksi,
                  ),
          ),
          ...buildSidebarLayer(
            activeMenu: 'Dashboard',
            onMenuTap: _handleMenuTap,
            // onLogout sudah punya default: navigasi ke LoginPage
          ),
        ],
      ),
    );
  }
}

// ── Loading ───────────────────────────────────────────────────────────────────
class _LoadingState extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return const Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          CircularProgressIndicator(color: Color(0xFF4169E1)),
          SizedBox(height: 16),
          Text(
            'Memuat dashboard...',
            style: TextStyle(color: Color(0xFF4A5568)),
          ),
        ],
      ),
    );
  }
}

// ── Error ─────────────────────────────────────────────────────────────────────
class _ErrorState extends StatelessWidget {
  final VoidCallback onRetry;
  const _ErrorState({required this.onRetry});

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.cloud_off_rounded, size: 64, color: Colors.grey.shade400),
          const SizedBox(height: 16),
          const Text(
            'Gagal memuat data',
            style: TextStyle(fontSize: 16, color: Color(0xFF2D3748)),
          ),
          const SizedBox(height: 8),
          Text(
            'Periksa koneksi ke server',
            style: TextStyle(fontSize: 13, color: Colors.grey.shade500),
          ),
          const SizedBox(height: 24),
          ElevatedButton.icon(
            onPressed: onRetry,
            icon: const Icon(Icons.refresh_rounded),
            label: const Text('Coba Lagi'),
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF4169E1),
              foregroundColor: Colors.white,
            ),
          ),
        ],
      ),
    );
  }
}

// ════════════════════════════════════════════════════════════════════════════
// BERANDA CONTENT
// ════════════════════════════════════════════════════════════════════════════
class _BerandaContent extends StatelessWidget {
  final DateTime now;
  final String userName;
  final String Function(DateTime) formatDate;
  final String Function(DateTime) formatTime;
  final VoidCallback onMenuTap;
  final DashboardStats stats;
  final List<StokMenipisItem> lowStock;
  final List<KadaluarsaItem> expiring;
  final List<TransaksiItem> transaksi;

  const _BerandaContent({
    required this.now,
    required this.userName,
    required this.formatDate,
    required this.formatTime,
    required this.onMenuTap,
    required this.stats,
    required this.lowStock,
    required this.expiring,
    required this.transaksi,
  });

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      physics: const AlwaysScrollableScrollPhysics(),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _BerandaHeader(
            now: now,
            userName: userName,
            formatDate: formatDate,
            formatTime: formatTime,
            onMenuTap: onMenuTap,
          ),
          const SizedBox(height: 16),
          _BerandaSummaryCards(stats: stats),
          const SizedBox(height: 20),
          _SectionCard(
            title: 'Stok Menipis',
            subtitle: 'Produk dengan stok di bawah minimum',
            onLihatSemua: () {},
            child: _StokMenipisTable(items: lowStock),
          ),
          const SizedBox(height: 12),
          _SectionCard(
            title: 'Produk akan kadaluarsa',
            subtitle: '30 hari ke depan',
            onLihatSemua: () {},
            child: _KadaluarsaTable(items: expiring),
          ),
          const SizedBox(height: 12),
          _SectionCard(
            title: 'Grafik Penjualan & Pengeluaran Stok',
            subtitle: 'Performa penjualan dan pergerakan stok harian',
            child: const _GrafikSection(),
          ),
          const SizedBox(height: 12),
          _SectionCard(
            title: 'Distribusi Stok',
            child: _DistribusiStok(stats: stats),
          ),
          const SizedBox(height: 12),
          _SectionCard(
            title: 'Transaksi Terbaru',
            subtitle: 'Transaksi 7 hari terakhir',
            onLihatSemua: () {},
            child: _TransaksiTable(items: transaksi),
          ),
          const SizedBox(height: 32),
        ],
      ),
    );
  }
}

// ════════════════════════════════════════════════════════════════════════════
// HEADER
// ════════════════════════════════════════════════════════════════════════════
class _BerandaHeader extends StatelessWidget {
  final DateTime now;
  final String userName;
  final String Function(DateTime) formatDate;
  final String Function(DateTime) formatTime;
  final VoidCallback onMenuTap;

  const _BerandaHeader({
    required this.now,
    required this.userName,
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
                      Text(
                        'Hallo, $userName',
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 16,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                      const SizedBox(width: 10),
                      const CircleAvatar(
                        radius: 22,
                        backgroundColor: Colors.white24,
                        child: Icon(
                          Icons.person,
                          color: Colors.white,
                          size: 24,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 24),
                  const Text(
                    'Selamat Datang\ndi Toko Roni Juntinyuat',
                    style: TextStyle(
                      color: Colors.white,
                      fontSize: 20,
                      fontWeight: FontWeight.bold,
                      height: 1.4,
                    ),
                  ),
                  const SizedBox(height: 20),
                  Row(
                    children: [
                      _InfoCard(
                        icon: Icons.calendar_today_rounded,
                        iconBg: const Color(0xFF4A90D9),
                        label: 'Tanggal',
                        value: formatDate(now),
                      ),
                      const SizedBox(width: 12),
                      _InfoCard(
                        icon: Icons.access_time_rounded,
                        iconBg: Colors.green,
                        label: 'Waktu',
                        value: formatTime(now),
                      ),
                    ],
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

class _InfoCard extends StatelessWidget {
  final IconData icon;
  final Color iconBg;
  final String label;
  final String value;

  const _InfoCard({
    required this.icon,
    required this.iconBg,
    required this.label,
    required this.value,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.08),
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
}

// ════════════════════════════════════════════════════════════════════════════
// SUMMARY CARDS — data dari API
// ════════════════════════════════════════════════════════════════════════════
class _BerandaSummaryCards extends StatelessWidget {
  final DashboardStats stats;
  const _BerandaSummaryCards({required this.stats});

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      height: 110,
      child: ListView(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 16),
        children: [
          SummaryCard(
            label: 'Total Karyawan',
            value: stats.totalKaryawan.toString(),
            icon: Icons.people_alt_rounded,
            color: const Color(0xFF6B9FFF),
          ),
          SummaryCard(
            label: 'Total Produk',
            value: stats.totalProduk.toString(),
            icon: Icons.inventory_2_rounded,
            color: const Color(0xFF48BB78),
          ),
          SummaryCard(
            label: 'Stok Hampir Habis',
            value: stats.stokHampirHabis.toString(),
            icon: Icons.warning_amber_rounded,
            color: const Color(0xFFECC94B),
          ),
          SummaryCard(
            label: 'Akan Kadaluarsa',
            value: stats.akanKadaluarsa.toString(),
            icon: Icons.timer_rounded,
            color: const Color(0xFFFC8181),
          ),
        ],
      ),
    );
  }
}

// ════════════════════════════════════════════════════════════════════════════
// SECTION CARD WRAPPER
// ════════════════════════════════════════════════════════════════════════════
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
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 16),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 8,
            offset: const Offset(0, 2),
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
                        fontSize: 15,
                        fontWeight: FontWeight.bold,
                        color: Color(0xFF2D3748),
                      ),
                    ),
                    if (subtitle != null) ...[
                      const SizedBox(height: 2),
                      Text(
                        subtitle!,
                        style: TextStyle(
                          fontSize: 11,
                          color: Colors.grey.shade500,
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
                          fontWeight: FontWeight.w500,
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
    );
  }
}

// ════════════════════════════════════════════════════════════════════════════
// STOK MENIPIS TABLE — dari API
// ════════════════════════════════════════════════════════════════════════════
class _StokMenipisTable extends StatelessWidget {
  final List<StokMenipisItem> items;
  const _StokMenipisTable({required this.items});

  @override
  Widget build(BuildContext context) {
    if (items.isEmpty) {
      return _PositiveEmptyState(
        icon: Icons.check_rounded,
        message: 'Semua produk dalam stok yang cukup',
      );
    }
    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      child: DataTable(
        headingRowHeight: 36,
        dataRowMinHeight: 36,
        dataRowMaxHeight: 40,
        columnSpacing: 16,
        headingTextStyle: const TextStyle(
          fontSize: 12,
          fontWeight: FontWeight.w600,
          color: Color(0xFF4A5568),
        ),
        dataTextStyle: const TextStyle(fontSize: 12, color: Color(0xFF2D3748)),
        columns: const [
          DataColumn(label: Text('Produk')),
          DataColumn(label: Text('Kategori')),
          DataColumn(label: Text('Stok Min')),
          DataColumn(label: Text('Sisa Stok')),
        ],
        rows: items
            .map(
              (item) => DataRow(
                cells: [
                  DataCell(Text(item.produk)),
                  DataCell(Text(item.kategori)),
                  DataCell(Text(item.stokMin.toString())),
                  DataCell(
                    Text(
                      item.sisaStok.toString(),
                      style: const TextStyle(
                        color: Color(0xFFE53E3E),
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ),
                ],
              ),
            )
            .toList(),
      ),
    );
  }
}

// ════════════════════════════════════════════════════════════════════════════
// KADALUARSA TABLE — dari API
// ════════════════════════════════════════════════════════════════════════════
class _KadaluarsaTable extends StatelessWidget {
  final List<KadaluarsaItem> items;
  const _KadaluarsaTable({required this.items});

  @override
  Widget build(BuildContext context) {
    if (items.isEmpty) {
      return _PositiveEmptyState(
        icon: Icons.check_rounded,
        message: 'Tidak ada produk yang akan kadaluarsa',
      );
    }
    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      child: DataTable(
        headingRowHeight: 36,
        dataRowMinHeight: 36,
        dataRowMaxHeight: 42,
        columnSpacing: 14,
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
          DataColumn(label: Text('Tgl Kadaluarsa')),
          DataColumn(label: Text('Sisa Hari')),
        ],
        rows: items
            .map(
              (item) => DataRow(
                cells: [
                  DataCell(Text(item.produk)),
                  DataCell(Text(item.kategori)),
                  DataCell(Text(item.stok.toString())),
                  DataCell(Text(item.tanggalKadaluarsa)),
                  DataCell(
                    item.isExpired
                        ? Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 10,
                              vertical: 3,
                            ),
                            decoration: BoxDecoration(
                              color: const Color(0xFFE53E3E),
                              borderRadius: BorderRadius.circular(20),
                            ),
                            child: const Text(
                              'expired',
                              style: TextStyle(
                                color: Colors.white,
                                fontSize: 11,
                              ),
                            ),
                          )
                        : Text(item.sisaHari),
                  ),
                ],
              ),
            )
            .toList(),
      ),
    );
  }
}

// ════════════════════════════════════════════════════════════════════════════
// GRAFIK — fetch dari API saat filter berubah
// ════════════════════════════════════════════════════════════════════════════
class _GrafikSection extends StatefulWidget {
  const _GrafikSection();

  @override
  State<_GrafikSection> createState() => _GrafikSectionState();
}

class _GrafikSectionState extends State<_GrafikSection> {
  String _filter = '7 Hari';
  bool _loading = true;
  ChartData? _data;

  // Fallback jika API belum siap
  static final _fallback = {
    '7 Hari': ChartData(
      labels: ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'],
      penjualan: [1.2, 1.8, 1.5, 2.1, 1.7, 2.3, 1.9],
      stokKeluar: [20.0, 35.0, 28.0, 38.0, 25.0, 32.0, 22.0],
    ),
    '30 Hari': ChartData(
      labels: ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4', 'Minggu 5'],
      penjualan: [2.0, 2.2, 2.4, 2.3, 2.2],
      stokKeluar: [28.0, 32.0, 35.0, 35.0, 29.0],
    ),
    '90 Hari': ChartData(
      labels: [
        'M1',
        'M2',
        'M3',
        'M4',
        'M5',
        'M6',
        'M7',
        'M8',
        'M9',
        'M10',
        'M11',
        'M12',
        'M13',
      ],
      penjualan: [
        1.8,
        2.0,
        1.9,
        2.1,
        2.2,
        1.7,
        2.3,
        2.1,
        1.9,
        2.4,
        2.0,
        1.8,
        2.2,
      ],
      stokKeluar: [
        25.0,
        28.0,
        26.0,
        32.0,
        30.0,
        24.0,
        35.0,
        29.0,
        27.0,
        36.0,
        28.0,
        26.0,
        31.0,
      ],
    ),
  };

  @override
  void initState() {
    super.initState();
    _fetchChart();
  }

  Future<void> _fetchChart() async {
    setState(() => _loading = true);
    final result = await DashboardService.getChartData(_filter);
    if (mounted) {
      setState(() {
        _data = (result != null && !result.isEmpty)
            ? result
            : _fallback[_filter];
        _loading = false;
      });
    }
  }

  String _dateRange() {
    final days = int.parse(_filter.split(' ').first);
    final now = DateTime.now();
    final start = now.subtract(Duration(days: days - 1));
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
    String fmt(DateTime d) => '${d.day} ${m[d.month - 1]} ${d.year}';
    return '${fmt(start)} - ${fmt(now)}';
  }

  @override
  Widget build(BuildContext context) {
    final data = _data;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            const Expanded(
              child: Row(
                children: [
                  _LegendDot(color: Color(0xFF4169E1), label: 'Penjualan'),
                  SizedBox(width: 16),
                  _LegendDot(color: Color(0xFFED8936), label: 'Stok Keluar'),
                ],
              ),
            ),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
              decoration: BoxDecoration(
                border: Border.all(color: Colors.grey.shade300),
                borderRadius: BorderRadius.circular(8),
              ),
              child: DropdownButton<String>(
                value: _filter,
                underline: const SizedBox(),
                isDense: true,
                items: ['7 Hari', '30 Hari', '90 Hari']
                    .map((e) => DropdownMenuItem(value: e, child: Text(e)))
                    .toList(),
                onChanged: (v) {
                  if (v != null) {
                    setState(() => _filter = v);
                    _fetchChart();
                  }
                },
              ),
            ),
          ],
        ),
        const SizedBox(height: 8),
        Text(
          'Periode: ${_dateRange()}',
          style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
        ),
        const SizedBox(height: 12),
        if (_loading)
          const SizedBox(
            height: 200,
            child: Center(
              child: CircularProgressIndicator(color: Color(0xFF4169E1)),
            ),
          )
        else if (data == null || data.isEmpty)
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
              width: (data.labels.length * 120).toDouble(),
              height: 300,
              child: CustomPaint(
                size: Size((data.labels.length * 120).toDouble(), 300),
                painter: _LineChartPainter(
                  penjualan: data.penjualan,
                  stokKeluar: data.stokKeluar,
                  days: data.labels,
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
  Widget build(BuildContext context) {
    return Row(
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
}

class _LineChartPainter extends CustomPainter {
  final List<double> penjualan;
  final List<double> stokKeluar;
  final List<String> days;
  const _LineChartPainter({
    required this.penjualan,
    required this.stokKeluar,
    required this.days,
  });

  @override
  void paint(Canvas canvas, Size size) {
    if (days.isEmpty) return;
    const pl = 55.0, pr = 55.0, pb = 55.0, pt = 20.0;
    final w = size.width - pl - pr;
    final h = size.height - pb - pt;
    final double maxP = penjualan.isNotEmpty
        ? penjualan.reduce((a, b) => a > b ? a : b)
        : 1.0;
    final double maxS = stokKeluar.isNotEmpty
        ? stokKeluar.reduce((a, b) => a > b ? a : b)
        : 1.0;

    canvas.drawRect(
      Rect.fromLTWH(pl, pt, w, h),
      Paint()..color = const Color(0xFFFAFAFC),
    );
    final grid = Paint()
      ..color = const Color(0xFFE5E7EB)
      ..strokeWidth = 0.7;
    for (int i = 0; i <= 4; i++) {
      final y = pt + h - (i / 4) * h;
      canvas.drawLine(Offset(pl, y), Offset(size.width - pr, y), grid);
    }

    _drawYAxis(canvas, pl, pt, h, true, maxP);
    _drawYAxis(canvas, size.width - pr, pt, h, false, maxS);
    if (penjualan.length > 1) {
      _drawArea(canvas, penjualan, maxP, pl, pt, w, h, const Color(0xFF4169E1));
      _drawLine(canvas, penjualan, maxP, pl, pt, w, h, const Color(0xFF4169E1));
    }
    if (stokKeluar.length > 1) {
      _drawArea(
        canvas,
        stokKeluar,
        maxS,
        pl,
        pt,
        w,
        h,
        const Color(0xFFED8936),
      );
      _drawLine(
        canvas,
        stokKeluar,
        maxS,
        pl,
        pt,
        w,
        h,
        const Color(0xFFED8936),
      );
    }
    _drawXLabels(canvas, size, pl, pt, w, h, pb);
  }

  void _drawYAxis(
    Canvas c,
    double x,
    double pt,
    double h,
    bool left,
    double maxVal,
  ) {
    c.drawLine(
      Offset(x, pt),
      Offset(x, pt + h),
      Paint()
        ..color = const Color(0xFF9CA3AF)
        ..strokeWidth = 1.2,
    );
    final tp = TextPainter(textDirection: TextDirection.ltr);
    for (int i = 0; i <= 4; i++) {
      final val = (i / 4) * (left ? 20 : 100);
      tp.text = TextSpan(
        text: left
            ? (val == 0 ? 'Rp 0' : 'Rp ${val.toStringAsFixed(0)}jt')
            : '${val.toStringAsFixed(0)} unit',
        style: const TextStyle(
          fontSize: 10,
          fontWeight: FontWeight.w500,
          color: Color(0xFF6B7280),
        ),
      );
      tp.layout();
      final y = pt + h - (i / 4) * h;
      tp.paint(
        c,
        left
            ? Offset(x - tp.width - 10, y - tp.height / 2)
            : Offset(x + 10, y - tp.height / 2),
      );
    }
  }

  void _drawXLabels(
    Canvas c,
    Size size,
    double pl,
    double pt,
    double w,
    double h,
    double pb,
  ) {
    if (days.isEmpty) return;
    final tp = TextPainter(textDirection: TextDirection.ltr);
    final tick = Paint()
      ..color = const Color(0xFFD1D5DB)
      ..strokeWidth = 0.8;
    c.drawLine(
      Offset(pl, pt + h),
      Offset(size.width - 55, pt + h),
      Paint()
        ..color = const Color(0xFFD1D5DB)
        ..strokeWidth = 1.2,
    );
    for (int i = 0; i < days.length; i++) {
      final x = pl + (days.length == 1 ? w / 2 : (i / (days.length - 1)) * w);
      tp.text = TextSpan(
        text: days[i],
        style: const TextStyle(
          fontSize: 11,
          fontWeight: FontWeight.w500,
          color: Color(0xFF6B7280),
        ),
      );
      tp.layout();
      tp.paint(c, Offset(x - tp.width / 2, size.height - pb + 14));
      c.drawLine(Offset(x, pt + h), Offset(x, pt + h + 5), tick);
    }
  }

  void _drawArea(
    Canvas c,
    List<double> data,
    double maxVal,
    double l,
    double t,
    double w,
    double h,
    Color color,
  ) {
    if (data.length < 2) return;
    final path = Path();
    for (int i = 0; i < data.length; i++) {
      final x = l + (i / (data.length - 1)) * w;
      final y = t + h - (data[i] / maxVal) * h;
      if (i == 0) {
        path.moveTo(x, y);
      } else {
        final px = l + ((i - 1) / (data.length - 1)) * w;
        final py = t + h - (data[i - 1] / maxVal) * h;
        path.cubicTo(px + (x - px) / 2, py, px + (x - px) / 2, y, x, y);
      }
    }
    path.lineTo(l + w, t + h);
    path.lineTo(l, t + h);
    c.drawPath(
      path,
      Paint()
        ..color = color.withOpacity(0.10)
        ..style = PaintingStyle.fill,
    );
  }

  void _drawLine(
    Canvas c,
    List<double> data,
    double maxVal,
    double l,
    double t,
    double w,
    double h,
    Color color,
  ) {
    if (data.length < 2) return;
    final path = Path();
    for (int i = 0; i < data.length; i++) {
      final x = l + (i / (data.length - 1)) * w;
      final y = t + h - (data[i] / maxVal) * h;
      if (i == 0) {
        path.moveTo(x, y);
      } else {
        final px = l + ((i - 1) / (data.length - 1)) * w;
        final py = t + h - (data[i - 1] / maxVal) * h;
        path.cubicTo(px + (x - px) / 2, py, px + (x - px) / 2, y, x, y);
      }
    }
    c.drawPath(
      path,
      Paint()
        ..color = color.withOpacity(0.15)
        ..strokeWidth = 5
        ..style = PaintingStyle.stroke
        ..strokeCap = StrokeCap.round,
    );
    c.drawPath(
      path,
      Paint()
        ..color = color
        ..strokeWidth = 3.5
        ..style = PaintingStyle.stroke
        ..strokeCap = StrokeCap.round,
    );
    for (int i = 0; i < data.length; i++) {
      final x = l + (i / (data.length - 1)) * w;
      final y = t + h - (data[i] / maxVal) * h;
      c.drawCircle(Offset(x, y), 7, Paint()..color = color.withOpacity(0.2));
      c.drawCircle(Offset(x, y), 4.5, Paint()..color = color);
      c.drawCircle(Offset(x, y), 2, Paint()..color = Colors.white);
    }
  }

  @override
  bool shouldRepaint(_) => false;
}

// ════════════════════════════════════════════════════════════════════════════
// DISTRIBUSI STOK — dari stats API
// ════════════════════════════════════════════════════════════════════════════
class _DistribusiStok extends StatelessWidget {
  final DashboardStats stats;
  const _DistribusiStok({required this.stats});

  @override
  Widget build(BuildContext context) {
    final total = stats.totalProduk == 0 ? 1 : stats.totalProduk;
    return Column(
      children: [
        SizedBox(
          height: 160,
          child: CustomPaint(
            size: const Size(double.infinity, 160),
            painter: _DonutPainter(
              normal: stats.stokNormal,
              hampirHabis: stats.stokHampirHabis,
              kritis: stats.stokKritis,
              total: stats.totalProduk,
            ),
          ),
        ),
        const SizedBox(height: 8),
        const Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            _LegendDot(color: Color(0xFF48BB78), label: 'Stok Normal'),
            SizedBox(width: 16),
            _LegendDot(color: Color(0xFFECC94B), label: 'Hampir Habis'),
            SizedBox(width: 16),
            _LegendDot(color: Color(0xFFFC8181), label: 'Kritis'),
          ],
        ),
        const SizedBox(height: 16),
        _DistribusiRow(
          color: const Color(0xFF48BB78),
          label: 'Stok Normal',
          value: '${stats.stokNormal} produk',
        ),
        _DistribusiRow(
          color: const Color(0xFFECC94B),
          label: 'Hampir Habis',
          value: '${stats.stokHampirHabis} produk',
        ),
        _DistribusiRow(
          color: const Color(0xFFFC8181),
          label: 'Kritis',
          value: '${stats.stokKritis} produk',
        ),
      ],
    );
  }
}

class _DistribusiRow extends StatelessWidget {
  final Color color;
  final String label;
  final String value;
  const _DistribusiRow({
    required this.color,
    required this.label,
    required this.value,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        children: [
          Container(
            width: 10,
            height: 10,
            decoration: BoxDecoration(color: color, shape: BoxShape.circle),
          ),
          const SizedBox(width: 8),
          Expanded(
            child: Text(
              label,
              style: const TextStyle(fontSize: 13, color: Color(0xFF4A5568)),
            ),
          ),
          Text(
            value,
            style: const TextStyle(
              fontSize: 13,
              fontWeight: FontWeight.w600,
              color: Color(0xFF2D3748),
            ),
          ),
        ],
      ),
    );
  }
}

class _DonutPainter extends CustomPainter {
  final int normal, hampirHabis, kritis, total;
  const _DonutPainter({
    required this.normal,
    required this.hampirHabis,
    required this.kritis,
    required this.total,
  });

  @override
  void paint(Canvas canvas, Size size) {
    final cx = size.width / 2;
    final cy = size.height / 2;
    final radius = size.height * 0.42;
    final t = total == 0 ? 1 : total;
    final data = [
      {'value': normal, 'color': const Color(0xFF48BB78)},
      {'value': hampirHabis, 'color': const Color(0xFFECC94B)},
      {'value': kritis > 0 ? kritis : 0.01, 'color': const Color(0xFFFC8181)},
    ];
    final rect = Rect.fromCircle(center: Offset(cx, cy), radius: radius);
    double startAngle = -90 * (3.14159 / 180);
    for (final d in data) {
      final v = (d['value'] as num).toDouble();
      final sweep = (v / t) * 2 * 3.14159;
      canvas.drawArc(
        rect,
        startAngle,
        sweep,
        false,
        Paint()
          ..color = d['color'] as Color
          ..style = PaintingStyle.stroke
          ..strokeWidth = 28
          ..strokeCap = StrokeCap.butt,
      );
      startAngle += sweep;
    }
  }

  @override
  bool shouldRepaint(_) => true;
}

// ════════════════════════════════════════════════════════════════════════════
// TRANSAKSI TABLE — dari API
// ════════════════════════════════════════════════════════════════════════════
class _TransaksiTable extends StatelessWidget {
  final List<TransaksiItem> items;
  const _TransaksiTable({required this.items});

  @override
  Widget build(BuildContext context) {
    if (items.isEmpty) {
      return _PositiveEmptyState(
        icon: Icons.receipt_long_rounded,
        message: 'Tidak ada transaksi terbaru',
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
          DataColumn(label: Text('Transaksi')),
          DataColumn(label: Text('Produk')),
          DataColumn(label: Text('Waktu')),
          DataColumn(label: Text('Total')),
          DataColumn(label: Text('Status')),
        ],
        rows: items
            .map(
              (item) => DataRow(
                cells: [
                  DataCell(Text(item.id, style: const TextStyle(fontSize: 10))),
                  DataCell(Text(item.produk)),
                  DataCell(Text(item.waktu)),
                  DataCell(Text(item.total)),
                  DataCell(
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 10,
                        vertical: 3,
                      ),
                      decoration: BoxDecoration(
                        color: item.isSuccess
                            ? const Color(0xFF48BB78)
                            : const Color(0xFFE53E3E),
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
              ),
            )
            .toList(),
      ),
    );
  }
}

// ════════════════════════════════════════════════════════════════════════════
// POSITIVE EMPTY STATE
// ════════════════════════════════════════════════════════════════════════════
class _PositiveEmptyState extends StatelessWidget {
  final IconData icon;
  final String message;
  const _PositiveEmptyState({required this.icon, required this.message});

  @override
  Widget build(BuildContext context) {
    return Center(
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
}
