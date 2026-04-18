// lib/transaction/laporan_penjualan_page.dart
import 'package:flutter/material.dart';
import 'package:tokoronifrontend/delivery/manajemen_pengiriman_page.dart';
import 'package:tokoronifrontend/home/beranda_page.dart';
import 'package:tokoronifrontend/transaction/riwayat_transaksi_page.dart';
import '../widgets/shared_widgets.dart';
import '../widgets/notifikasi_widget.dart';
import '../widgets/semua_notifikasi_page.dart';
import '../widgets/profile_widget.dart';
import '/member/daftar_member_page.dart';
import '/user/manajemen_pengguna_page.dart';
import '/product/daftar_produk_page.dart';
import '/category/manajemen_kategori_page.dart';
import '/home/menu_pages.dart';
import '/home/beranda_page.dart';

// ════════════════════════════════════════════════════════════════════════════
// MODEL
// ════════════════════════════════════════════════════════════════════════════
class TransaksiLaporanItem {
  final int no;
  final String tanggal;
  final String jam;
  final String invoice;
  final int invoiceId;
  final String kasir;
  final String customer;
  final String pembayaran;
  final String status;
  final int total;
  final int jumlahItem;

  const TransaksiLaporanItem({
    required this.no,
    required this.tanggal,
    required this.jam,
    required this.invoice,
    required this.invoiceId,
    required this.kasir,
    required this.customer,
    required this.pembayaran,
    required this.status,
    required this.total,
    required this.jumlahItem,
  });
}

// ── Dummy data ────────────────────────────────────────────────────────────────
final List<TransaksiLaporanItem> _dummyTransaksi = [
  const TransaksiLaporanItem(
    no: 1,
    tanggal: '28 Feb 2026',
    jam: '18:03',
    invoice: 'INV202602280305',
    invoiceId: 28,
    kasir: 'Rusdi',
    customer: 'Pelanggan umum',
    pembayaran: 'Cash',
    status: 'Lunas',
    total: 180000,
    jumlahItem: 7,
  ),
  const TransaksiLaporanItem(
    no: 1,
    tanggal: '28 Feb 2026',
    jam: '18:03',
    invoice: 'INV202602280305',
    invoiceId: 28,
    kasir: 'Rusdi',
    customer: 'Pelanggan umum',
    pembayaran: 'Cash',
    status: 'Kredit',
    total: 180000,
    jumlahItem: 7,
  ),
  const TransaksiLaporanItem(
    no: 1,
    tanggal: '28 Feb 2026',
    jam: '18:03',
    invoice: 'INV202602280305',
    invoiceId: 28,
    kasir: 'Rusdi',
    customer: 'Pelanggan umum',
    pembayaran: 'Cash',
    status: 'Lunas',
    total: 180000,
    jumlahItem: 7,
  ),
  const TransaksiLaporanItem(
    no: 2,
    tanggal: '27 Feb 2026',
    jam: '09:13',
    invoice: 'INV202602280449',
    invoiceId: 30,
    kasir: 'Rusdi',
    customer: 'pelanggan umum',
    pembayaran: 'E-Wallet',
    status: 'Kredit',
    total: 245000,
    jumlahItem: 12,
  ),
  const TransaksiLaporanItem(
    no: 2,
    tanggal: '27 Feb 2026',
    jam: '09:13',
    invoice: 'INV202602280449',
    invoiceId: 30,
    kasir: 'Rusdi',
    customer: 'pelanggan umum',
    pembayaran: 'E-Wallet',
    status: 'Lunas',
    total: 245000,
    jumlahItem: 12,
  ),
  const TransaksiLaporanItem(
    no: 2,
    tanggal: '27 Feb 2026',
    jam: '09:13',
    invoice: 'INV202602280449',
    invoiceId: 30,
    kasir: 'Rusdi',
    customer: 'pelanggan umum',
    pembayaran: 'E-Wallet',
    status: 'Lunas',
    total: 245000,
    jumlahItem: 12,
  ),
  const TransaksiLaporanItem(
    no: 3,
    tanggal: '26 Feb 2026',
    jam: '14:22',
    invoice: 'INV202602260188',
    invoiceId: 25,
    kasir: 'Budi',
    customer: 'Asep Saepudin',
    pembayaran: 'Transfer',
    status: 'Lunas',
    total: 320000,
    jumlahItem: 5,
  ),
  const TransaksiLaporanItem(
    no: 4,
    tanggal: '25 Feb 2026',
    jam: '10:05',
    invoice: 'INV202602250092',
    invoiceId: 22,
    kasir: 'Budi',
    customer: 'Jomod',
    pembayaran: 'Cash',
    status: 'Lunas',
    total: 115000,
    jumlahItem: 3,
  ),
  const TransaksiLaporanItem(
    no: 5,
    tanggal: '24 Feb 2026',
    jam: '16:45',
    invoice: 'INV202602240077',
    invoiceId: 20,
    kasir: 'Rusdi',
    customer: 'Pelanggan umum',
    pembayaran: 'E-Wallet',
    status: 'Kredit',
    total: 1280000,
    jumlahItem: 18,
  ),
];

// ── Helper format Rupiah ──────────────────────────────────────────────────────
String _rp(int n) {
  final s = n.toString();
  final buf = StringBuffer('Rp ');
  for (int i = 0; i < s.length; i++) {
    if (i > 0 && (s.length - i) % 3 == 0) buf.write('.');
    buf.write(s[i]);
  }
  return buf.toString();
}

// ════════════════════════════════════════════════════════════════════════════
// PAGE
// ════════════════════════════════════════════════════════════════════════════
class LaporanPenjualanPage extends StatefulWidget {
  final String userName;
  final String userRole;

  const LaporanPenjualanPage({
    super.key,
    this.userName = '',
    this.userRole = '',
  });

  @override
  State<LaporanPenjualanPage> createState() => _LaporanPenjualanPageState();
}

class _LaporanPenjualanPageState extends State<LaporanPenjualanPage>
    with SingleTickerProviderStateMixin, SidebarMixin {
  // ── Filter state ──────────────────────────────────────────────────────────
  final _tanggalCtrl = TextEditingController();
  final _bulanCtrl = TextEditingController();
  String _urutkan = 'Terbaru';
  static const _urutkanList = [
    'Terbaru',
    'Terlama',
    'Total Tertinggi',
    'Total Terendah',
  ];

  DateTime? _filterTanggal;
  DateTime? _filterBulan;

  // ── Data ──────────────────────────────────────────────────────────────────
  late List<TransaksiLaporanItem> _data;
  List<TransaksiLaporanItem> get _filtered {
    var list = List<TransaksiLaporanItem>.from(_data);

    // Filter berdasarkan tanggal spesifik
    if (_filterTanggal != null) {
      list = list
          .where(
            (t) =>
                t.tanggal.contains(
                  _filterTanggal!.day.toString().padLeft(2, '0'),
                ) &&
                t.tanggal.contains(_getMonthName(_filterTanggal!.month)) &&
                t.tanggal.contains(_filterTanggal!.year.toString()),
          )
          .toList();
    }

    // Filter berdasarkan bulan & tahun
    if (_filterBulan != null) {
      list = list
          .where(
            (t) =>
                t.tanggal.contains(_getMonthName(_filterBulan!.month)) &&
                t.tanggal.contains(_filterBulan!.year.toString()),
          )
          .toList();
    }

    // Urutkan
    switch (_urutkan) {
      case 'Terlama':
        list = list.reversed.toList();
        break;
      case 'Total Tertinggi':
        list.sort((a, b) => b.total.compareTo(a.total));
        break;
      case 'Total Terendah':
        list.sort((a, b) => a.total.compareTo(b.total));
        break;
    }
    return list;
  }

  String _getMonthName(int month) {
    const months = [
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
    return months[month - 1];
  }

  // ── Stats ─────────────────────────────────────────────────────────────────
  int get _totalOmzet => _data.fold(0, (s, t) => s + t.total);
  int get _rataRata => _data.isEmpty ? 0 : (_totalOmzet / _data.length).round();
  int get _transaksiTertinggi => _data.isEmpty
      ? 0
      : _data.map((t) => t.total).reduce((a, b) => a > b ? a : b);
  int get _jumlahTransaksi => _data.length;

  @override
  void initState() {
    super.initState();
    initSidebar(this);
    _data = List.from(_dummyTransaksi);
  }

  @override
  void dispose() {
    disposeSidebar();
    _tanggalCtrl.dispose();
    _bulanCtrl.dispose();
    super.dispose();
  }

  void _handleMenuTap(String menu) {
    if (menu == 'Laporan') {
      closeSidebar();
      return;
    }
    Widget? page;
    switch (menu) {
      case 'Dashboard':
        page = const BerandaPage();
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
      case 'Manajemen Pengiriman':
        page = const ManajemenPengirimanPage();
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
      closeSidebarThenNavigate(
        () => Navigator.push(context, MaterialPageRoute(builder: (_) => page!)),
      );
    }
  }

  // ── Date pickers ──────────────────────────────────────────────────────────
  Future<void> _pickTanggal() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: _filterTanggal ?? DateTime.now(),
      firstDate: DateTime(2024),
      lastDate: DateTime.now(),
      builder: (ctx, child) => Theme(
        data: Theme.of(ctx).copyWith(
          colorScheme: const ColorScheme.light(
            primary: Color(0xFF3B6FE8),
            onPrimary: Colors.white,
          ),
        ),
        child: child!,
      ),
    );
    if (picked != null) {
      setState(() {
        _filterTanggal = picked;
        _tanggalCtrl.text =
            '${picked.day.toString().padLeft(2, '0')}/${picked.month.toString().padLeft(2, '0')}/${picked.year}';
      });
    }
  }

  Future<void> _pickBulan() async {
    final now = DateTime.now();
    // Pakai date picker biasa, ambil bulan + tahun saja
    final picked = await showDatePicker(
      context: context,
      initialDate: _filterBulan ?? now,
      firstDate: DateTime(2024),
      lastDate: now,
      builder: (ctx, child) => Theme(
        data: Theme.of(ctx).copyWith(
          colorScheme: const ColorScheme.light(
            primary: Color(0xFF3B6FE8),
            onPrimary: Colors.white,
          ),
        ),
        child: child!,
      ),
    );
    if (picked != null) {
      const months = [
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
      setState(() {
        _filterBulan = picked;
        _bulanCtrl.text = '${months[picked.month - 1]} ${picked.year}';
      });
    }
  }

  void _resetFilter() {
    setState(() {
      _tanggalCtrl.clear();
      _bulanCtrl.clear();
      _filterTanggal = null;
      _filterBulan = null;
      _urutkan = 'Terbaru';
    });
    _showSnack('Filter direset', const Color(0xFF3B6FE8));
  }

  void _showSnack(String msg, Color color) =>
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(msg),
          backgroundColor: color,
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(10),
          ),
        ),
      );

  // ── Export PDF ────────────────────────────────────────────────────────────
  void _exportPDF() {
    // TODO: generate PDF dari data transaksi
    _showSnack('Mengekspor laporan ke PDF...', const Color(0xFF48BB78));
  }

  // ── Detail transaksi ──────────────────────────────────────────────────────
  void _showDetail(TransaksiLaporanItem t) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => Container(
        decoration: const BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
        ),
        padding: const EdgeInsets.fromLTRB(20, 16, 20, 32),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            // Handle
            Center(
              child: Container(
                width: 40,
                height: 4,
                decoration: BoxDecoration(
                  color: Colors.grey.shade300,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
            ),
            const SizedBox(height: 16),
            // Header
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: const Color(0xFF3B6FE8).withOpacity(0.12),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: const Icon(
                    Icons.receipt_long_rounded,
                    color: Color(0xFF3B6FE8),
                    size: 24,
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        t.invoice,
                        style: const TextStyle(
                          fontSize: 15,
                          fontWeight: FontWeight.bold,
                          color: Color(0xFF2D3748),
                        ),
                      ),
                      Text(
                        'ID: ${t.invoiceId}',
                        style: TextStyle(
                          fontSize: 12,
                          color: Colors.grey.shade500,
                        ),
                      ),
                    ],
                  ),
                ),
                _statusBadge(t.status),
              ],
            ),
            const SizedBox(height: 16),
            const Divider(),
            const SizedBox(height: 8),
            ...[
              ['Tanggal', '${t.tanggal}  ${t.jam}'],
              ['Kasir', t.kasir],
              ['Customer', t.customer],
              ['Pembayaran', t.pembayaran],
              ['Jumlah Item', '${t.jumlahItem} item'],
              ['Total', _rp(t.total)],
            ].map(
              (r) => Padding(
                padding: const EdgeInsets.symmetric(vertical: 6),
                child: Row(
                  children: [
                    Expanded(
                      child: Text(
                        r[0],
                        style: TextStyle(
                          fontSize: 13,
                          color: Colors.grey.shade500,
                        ),
                      ),
                    ),
                    Text(
                      r[1],
                      style: const TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w600,
                        color: Color(0xFF2D3748),
                      ),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 20),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: () => Navigator.pop(context),
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF3B6FE8),
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                  elevation: 0,
                ),
                child: const Text(
                  'Tutup',
                  style: TextStyle(fontWeight: FontWeight.w600, fontSize: 14),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  // ════════════════════════════════════════════════════════════════════════
  // BUILD
  // ════════════════════════════════════════════════════════════════════════
  @override
  Widget build(BuildContext context) {
    final filtered = _filtered;
    return Scaffold(
      backgroundColor: const Color(0xFFF3F4F8),
      body: Stack(
        children: [
          SingleChildScrollView(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                _buildHeader(),
                const SizedBox(height: 20),
                _buildSummaryCards(),
                const SizedBox(height: 16),
                _buildFilter(),
                const SizedBox(height: 16),
                _buildTable(filtered),
                const SizedBox(height: 40),
              ],
            ),
          ),
          ...buildSidebarLayer(
            activeMenu: 'Laporan',
            onMenuTap: _handleMenuTap,
          ),
        ],
      ),
    );
  }

  // ── HEADER ────────────────────────────────────────────────────────────────
  Widget _buildHeader() {
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
              padding: const EdgeInsets.fromLTRB(20, 16, 20, 32),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Top bar
                  Row(
                    children: [
                      BurgerMenuButton(onTap: openSidebar),
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
                      ProfileWidget.fromAuth(
                        onTap: () {
                          // TODO: navigasi ke ProfilePage
                        },
                      ),
                    ],
                  ),
                  const SizedBox(height: 24),

                  // Title + Export PDF
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: [
                      const Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Laporan Penjualan',
                              style: TextStyle(
                                color: Colors.white,
                                fontSize: 22,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                            SizedBox(height: 4),
                            Text(
                              'Periode bulan sekarang',
                              style: TextStyle(
                                color: Colors.white70,
                                fontSize: 13,
                              ),
                            ),
                          ],
                        ),
                      ),
                      // Export PDF button
                      ElevatedButton.icon(
                        onPressed: _exportPDF,
                        icon: const Icon(
                          Icons.picture_as_pdf_rounded,
                          color: Colors.white,
                          size: 18,
                        ),
                        label: const Text(
                          'Export PDF',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 13,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFFE53E3E),
                          padding: const EdgeInsets.symmetric(
                            horizontal: 16,
                            vertical: 10,
                          ),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                          elevation: 0,
                        ),
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

  // ── SUMMARY CARDS ─────────────────────────────────────────────────────────
  Widget _buildSummaryCards() {
    return SizedBox(
      height: 110,
      child: ListView(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 16),
        children: [
          // Total Omzet — card biru gelap
          _OmzetCard(label: 'Total Omzet', value: _rp(_totalOmzet)),
          SummaryCard(
            label: 'Rata-rata Transaksi',
            value: _rp(_rataRata),
            icon: Icons.receipt_rounded,
            color: const Color(0xFF48BB78),
          ),
          SummaryCard(
            label: 'Transaksi Tertinggi',
            value: _rp(_transaksiTertinggi),
            icon: Icons.trending_up_rounded,
            color: const Color(0xFFECC94B),
          ),
          SummaryCard(
            label: 'Jumlah Transaksi',
            value: '$_jumlahTransaksi transaksi',
            icon: Icons.shopping_cart_rounded,
            color: const Color(0xFF6B9FFF),
          ),
        ],
      ),
    );
  }

  // ── FILTER ────────────────────────────────────────────────────────────────
  Widget _buildFilter() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(14),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.05),
              blurRadius: 7,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Title
            const Text(
              'Filter Laporan',
              style: TextStyle(
                fontSize: 13,
                fontWeight: FontWeight.w600,
                color: Color(0xFF2D3748),
              ),
            ),
            const SizedBox(height: 12),

            // Filter Fields Row
            Row(
              children: [
                // Tanggal Spesifik
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Tanggal',
                        style: TextStyle(
                          fontSize: 11,
                          color: Colors.grey.shade600,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                      const SizedBox(height: 6),
                      _FilterField(
                        ctrl: _tanggalCtrl,
                        hint: 'Pilih tanggal',
                        suffixIcon: Icons.calendar_today_rounded,
                        onTapSuffix: _pickTanggal,
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: 10),

                // Bulan
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Bulan',
                        style: TextStyle(
                          fontSize: 11,
                          color: Colors.grey.shade600,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                      const SizedBox(height: 6),
                      _FilterField(
                        ctrl: _bulanCtrl,
                        hint: 'Pilih bulan',
                        suffixIcon: Icons.calendar_month_rounded,
                        onTapSuffix: _pickBulan,
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: 10),

                // Urutkan Berdasarkan
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Urutkan',
                        style: TextStyle(
                          fontSize: 11,
                          color: Colors.grey.shade600,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                      const SizedBox(height: 6),
                      Container(
                        height: 38,
                        padding: const EdgeInsets.symmetric(horizontal: 8),
                        decoration: BoxDecoration(
                          color: const Color(0xFFF8F9FA),
                          borderRadius: BorderRadius.circular(7),
                          border: Border.all(color: Colors.grey.shade300),
                        ),
                        child: DropdownButtonHideUnderline(
                          child: DropdownButton<String>(
                            value: _urutkan,
                            isExpanded: true,
                            isDense: true,
                            style: const TextStyle(
                              fontSize: 11,
                              color: Color(0xFF2D3748),
                            ),
                            icon: const Icon(
                              Icons.keyboard_arrow_down_rounded,
                              size: 16,
                            ),
                            items: _urutkanList
                                .map(
                                  (e) => DropdownMenuItem(
                                    value: e,
                                    child: Text(
                                      e,
                                      style: const TextStyle(fontSize: 11),
                                    ),
                                  ),
                                )
                                .toList(),
                            onChanged: (v) =>
                                setState(() => _urutkan = v ?? 'Terbaru'),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: 10),

                // Button: Reset
                Padding(
                  padding: const EdgeInsets.only(top: 18),
                  child: SizedBox(
                    height: 38,
                    child: OutlinedButton.icon(
                      onPressed: _resetFilter,
                      icon: const Icon(Icons.refresh_rounded, size: 14),
                      label: const Text(
                        'Reset',
                        style: TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      style: OutlinedButton.styleFrom(
                        foregroundColor: const Color(0xFF4A5568),
                        side: BorderSide(
                          color: Colors.grey.shade300,
                          width: 0.8,
                        ),
                        padding: const EdgeInsets.symmetric(horizontal: 14),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(7),
                        ),
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  // ── TABEL DATA TRANSAKSI ──────────────────────────────────────────────────
  Widget _buildTable(List<TransaksiLaporanItem> list) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Data Transaksi',
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.bold,
              color: Color(0xFF2D3748),
            ),
          ),
          const SizedBox(height: 2),
          Text(
            'detail transaksi penjualan',
            style: TextStyle(fontSize: 12, color: Colors.grey.shade500),
          ),
          const SizedBox(height: 12),
          Container(
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(16),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withOpacity(0.06),
                  blurRadius: 10,
                  offset: const Offset(0, 3),
                ),
              ],
            ),
            child: list.isEmpty
                ? Padding(
                    padding: const EdgeInsets.symmetric(vertical: 40),
                    child: Center(
                      child: Column(
                        children: [
                          Icon(
                            Icons.receipt_long_rounded,
                            size: 40,
                            color: Colors.grey.shade300,
                          ),
                          const SizedBox(height: 10),
                          Text(
                            'Tidak ada data transaksi',
                            style: TextStyle(
                              fontSize: 14,
                              color: Colors.grey.shade400,
                            ),
                          ),
                        ],
                      ),
                    ),
                  )
                : SingleChildScrollView(
                    scrollDirection: Axis.horizontal,
                    child: DataTable(
                      headingRowColor: WidgetStateProperty.all(
                        const Color(0xFFF7F8FA),
                      ),
                      headingRowHeight: 48,
                      dataRowMinHeight: 64,
                      dataRowMaxHeight: 80,
                      columnSpacing: 16,
                      headingTextStyle: const TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w700,
                        color: Color(0xFF4A5568),
                      ),
                      dataTextStyle: const TextStyle(
                        fontSize: 12,
                        color: Color(0xFF2D3748),
                      ),
                      columns: const [
                        DataColumn(label: Text('NO')),
                        DataColumn(label: Text('TANGGAL')),
                        DataColumn(label: Text('INVOICE')),
                        DataColumn(label: Text('KASIR')),
                        DataColumn(label: Text('CUSTOMER')),
                        DataColumn(label: Text('PEMBAYARAN')),
                        DataColumn(label: Text('STATUS')),
                        DataColumn(label: Text('Total')),
                        DataColumn(label: Text('Aksi')),
                      ],
                      rows: list
                          .map(
                            (t) => DataRow(
                              cells: [
                                DataCell(
                                  Text(
                                    '${t.no}',
                                    style: const TextStyle(
                                      fontWeight: FontWeight.w700,
                                      fontSize: 12,
                                    ),
                                  ),
                                ),
                                // Tanggal + jam
                                DataCell(
                                  Column(
                                    crossAxisAlignment:
                                        CrossAxisAlignment.start,
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      Text(
                                        t.tanggal,
                                        style: const TextStyle(
                                          fontSize: 12,
                                          fontWeight: FontWeight.w600,
                                        ),
                                      ),
                                      Text(
                                        t.jam,
                                        style: TextStyle(
                                          fontSize: 11,
                                          color: Colors.grey.shade500,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                                // Invoice + ID
                                DataCell(
                                  Column(
                                    crossAxisAlignment:
                                        CrossAxisAlignment.start,
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      Text(
                                        t.invoice,
                                        style: const TextStyle(
                                          fontSize: 12,
                                          fontWeight: FontWeight.bold,
                                          color: Color(0xFF2D3748),
                                        ),
                                      ),
                                      Text(
                                        'ID: ${t.invoiceId}',
                                        style: TextStyle(
                                          fontSize: 11,
                                          color: Colors.grey.shade400,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                                DataCell(
                                  Text(
                                    t.kasir,
                                    style: const TextStyle(fontSize: 12),
                                  ),
                                ),
                                DataCell(
                                  Text(
                                    t.customer,
                                    style: const TextStyle(fontSize: 12),
                                  ),
                                ),
                                DataCell(
                                  Text(
                                    t.pembayaran,
                                    style: const TextStyle(fontSize: 12),
                                  ),
                                ),
                                DataCell(_statusBadge(t.status)),
                                // Total + jumlah item
                                DataCell(
                                  Column(
                                    crossAxisAlignment:
                                        CrossAxisAlignment.start,
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      Text(
                                        _rp(t.total),
                                        style: const TextStyle(
                                          fontWeight: FontWeight.w700,
                                          fontSize: 12,
                                        ),
                                      ),
                                      Text(
                                        '${t.jumlahItem} item',
                                        style: TextStyle(
                                          fontSize: 11,
                                          color: Colors.grey.shade400,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                                // Aksi detail
                                DataCell(
                                  GestureDetector(
                                    onTap: () => _showDetail(t),
                                    child: Column(
                                      mainAxisAlignment:
                                          MainAxisAlignment.center,
                                      children: [
                                        Container(
                                          padding: const EdgeInsets.all(9),
                                          decoration: BoxDecoration(
                                            color: const Color(
                                              0xFF3B6FE8,
                                            ).withOpacity(0.12),
                                            borderRadius: BorderRadius.circular(
                                              10,
                                            ),
                                          ),
                                          child: const Icon(
                                            Icons.visibility_rounded,
                                            color: Color(0xFF3B6FE8),
                                            size: 19,
                                          ),
                                        ),
                                        const SizedBox(height: 4),
                                        const Text(
                                          'Detail',
                                          style: TextStyle(
                                            fontSize: 11,
                                            color: Color(0xFF3B6FE8),
                                            fontWeight: FontWeight.w600,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          )
                          .toList(),
                    ),
                  ),
          ),
        ],
      ),
    );
  }

  Widget _statusBadge(String status) {
    final isLunas = status == 'Lunas';
    final color = isLunas ? const Color(0xFF48BB78) : const Color(0xFFECC94B);
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: color.withOpacity(isLunas ? 0.12 : 0.15),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Text(
        status,
        style: TextStyle(
          fontSize: 11,
          fontWeight: FontWeight.w600,
          color: color,
        ),
      ),
    );
  }
}

// ════════════════════════════════════════════════════════════════════════════
// TOTAL OMZET CARD — dark blue style
// ════════════════════════════════════════════════════════════════════════════
class _OmzetCard extends StatelessWidget {
  final String label, value;
  const _OmzetCard({required this.label, required this.value});

  @override
  Widget build(BuildContext context) => Container(
    width: 190,
    margin: const EdgeInsets.only(right: 12),
    padding: const EdgeInsets.all(16),
    decoration: BoxDecoration(
      color: const Color(0xFF2B55D0),
      borderRadius: BorderRadius.circular(16),
      boxShadow: [
        BoxShadow(
          color: const Color(0xFF2B55D0).withOpacity(0.35),
          blurRadius: 10,
          offset: const Offset(0, 4),
        ),
      ],
    ),
    child: Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Row(
          children: [
            Text(
              label,
              style: const TextStyle(color: Colors.white70, fontSize: 12),
            ),
            const Spacer(),
            Container(
              padding: const EdgeInsets.all(6),
              decoration: BoxDecoration(
                color: Colors.white.withOpacity(0.2),
                borderRadius: BorderRadius.circular(8),
              ),
              child: const Icon(
                Icons.bar_chart_rounded,
                color: Colors.white,
                size: 18,
              ),
            ),
          ],
        ),
        const SizedBox(height: 8),
        FittedBox(
          fit: BoxFit.scaleDown,
          alignment: Alignment.centerLeft,
          child: Text(
            value,
            style: const TextStyle(
              color: Colors.white,
              fontSize: 20,
              fontWeight: FontWeight.bold,
            ),
          ),
        ),
      ],
    ),
  );
}

// ════════════════════════════════════════════════════════════════════════════
// FILTER FIELD HELPER
// ════════════════════════════════════════════════════════════════════════════
class _FilterField extends StatelessWidget {
  final TextEditingController ctrl;
  final String hint;
  final IconData suffixIcon;
  final VoidCallback onTapSuffix;

  const _FilterField({
    required this.ctrl,
    required this.hint,
    required this.suffixIcon,
    required this.onTapSuffix,
  });

  @override
  Widget build(BuildContext context) => TextField(
    controller: ctrl,
    readOnly: true,
    style: const TextStyle(fontSize: 12),
    decoration: InputDecoration(
      hintText: hint,
      hintStyle: TextStyle(color: Colors.grey.shade400, fontSize: 12),
      filled: true,
      fillColor: const Color(0xFFF8F9FA),
      contentPadding: const EdgeInsets.only(
        left: 10,
        top: 10,
        bottom: 10,
        right: 4,
      ),
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(8),
        borderSide: BorderSide(color: Colors.grey.shade300),
      ),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(8),
        borderSide: BorderSide(color: Colors.grey.shade300),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(8),
        borderSide: const BorderSide(color: Color(0xFF3B6FE8), width: 1.5),
      ),
      suffixIcon: IconButton(
        onPressed: onTapSuffix,
        icon: Icon(suffixIcon, size: 18, color: Colors.grey.shade500),
        splashRadius: 18,
      ),
    ),
  );
}
