// lib/transaction/riwayat_transaksi_page.dart
import 'package:flutter/material.dart';
import 'package:tokoronifrontend/delivery/manajemen_pengiriman_page.dart';
import '../widgets/shared_widgets.dart';
import '../widgets/notifikasi_widget.dart';
import '../widgets/semua_notifikasi_page.dart';
import '../widgets/profile_widget.dart';
import 'detail_transaksi_page.dart';
import 'kasir_page.dart';
import '../product/daftar_produk_page.dart';
import '../category/manajemen_kategori_page.dart';
import '../member/daftar_member_page.dart';
import '../user/manajemen_pengguna_page.dart';
import '../home/beranda_page.dart';
import '../report/laporan_penjualan_page.dart';
import '../home/menu_pages.dart' hide KasirPage;

// ════════════════════════════════════════════════════════════════════════════
// MODEL
// ════════════════════════════════════════════════════════════════════════════
class RiwayatTransaksiItem {
  final String invoice;
  final String tanggal; // '23/03/2026'
  final String waktu; // '02:00'
  final String customer;
  final String kasir;
  final String status; // 'Lunas' | 'Kredit' | 'Hutang'
  final String metode; // 'Tunai' | 'Debit' | 'E-Wallet' | 'Hutang'
  final int total;
  final int jumlahItem;
  final List<DetailProdukTransaksi> produkList;
  final int cashDiterima;
  final int diskonPersen;
  final bool isMember;
  final String? memberNama;
  final String noTelepon;

  const RiwayatTransaksiItem({
    required this.invoice,
    required this.tanggal,
    required this.waktu,
    required this.customer,
    required this.kasir,
    required this.status,
    required this.metode,
    required this.total,
    required this.jumlahItem,
    required this.produkList,
    this.cashDiterima = 0,
    this.diskonPersen = 0,
    this.isMember = false,
    this.memberNama,
    this.noTelepon = '',
  });
}

// ── Dummy data ────────────────────────────────────────────────────────────────
final List<RiwayatTransaksiItem> _dummyRiwayat = [
  RiwayatTransaksiItem(
    invoice: 'INV202603230001',
    tanggal: '23/03/2026',
    waktu: '02:00',
    customer: 'Pelanggan Umum',
    kasir: 'jomokg',
    status: 'Lunas',
    metode: 'Tunai',
    total: 2001,
    jumlahItem: 1,
    cashDiterima: 9000,
    diskonPersen: 0,
    produkList: const [
      DetailProdukTransaksi(
        kode: 'PRD-00000001',
        nama: 'Fullo',
        kategori: 'Makanan',
        harga: 2001,
        qty: 1,
      ),
    ],
  ),
  RiwayatTransaksiItem(
    invoice: 'INV202602280305',
    tanggal: '28/02/2026',
    waktu: '18:03',
    customer: 'Pelanggan Umum',
    kasir: 'Rusdi',
    noTelepon: '081234567890',
    status: 'Lunas',
    metode: 'Tunai',
    total: 180000,
    jumlahItem: 7,
    cashDiterima: 200000,
    produkList: const [
      DetailProdukTransaksi(
        kode: 'PRD-48293100',
        nama: 'Beras Premium 5kg',
        kategori: 'Sembako',
        harga: 68000,
        qty: 1,
      ),
      DetailProdukTransaksi(
        kode: 'PRD-30751800',
        nama: 'Teh Celup',
        kategori: 'Minuman',
        harga: 22000,
        qty: 2,
      ),
      DetailProdukTransaksi(
        kode: 'PRD-24680300',
        nama: 'Sabun Cuci Piring',
        kategori: 'Kebutuhan Rumah',
        harga: 12000,
        qty: 1,
      ),
      DetailProdukTransaksi(
        kode: 'PRD-97513400',
        nama: 'Tisu Gulung',
        kategori: 'Kebutuhan Rumah',
        harga: 45000,
        qty: 1,
      ),
      DetailProdukTransaksi(
        kode: 'PRD-15864000',
        nama: 'Gula Pasir 1kg',
        kategori: 'Sembako',
        harga: 14000,
        qty: 1,
      ),
      DetailProdukTransaksi(
        kode: 'PRD-73920500',
        nama: 'Minyak Goreng 2L',
        kategori: 'Sembako',
        harga: 17000,
        qty: 1,
      ),
    ],
  ),
  RiwayatTransaksiItem(
    invoice: 'INV202602280449',
    tanggal: '27/02/2026',
    waktu: '09:13',
    customer: 'Asep Saepudin',
    kasir: 'Rusdi',
    noTelepon: '081234567890',
    status: 'Kredit',
    metode: 'Hutang',
    total: 245000,
    jumlahItem: 12,
    isMember: true,
    memberNama: 'Asep Saepudin',
    produkList: const [
      DetailProdukTransaksi(
        kode: 'PRD-86421900',
        nama: 'Mie Instan Ayam',
        kategori: 'Makanan',
        harga: 115000,
        qty: 1,
      ),
      DetailProdukTransaksi(
        kode: 'PRD-59172600',
        nama: 'Susu UHT 1L',
        kategori: 'Minuman',
        harga: 18500,
        qty: 3,
      ),
      DetailProdukTransaksi(
        kode: 'PRD-62489000',
        nama: 'Kopi Sachet (Box)',
        kategori: 'Minuman',
        harga: 52000,
        qty: 1,
      ),
    ],
  ),
  RiwayatTransaksiItem(
    invoice: 'INV202602260188',
    tanggal: '26/02/2026',
    waktu: '14:22',
    customer: 'Jomod',
    kasir: 'Budi',
    noTelepon: '082345678901',
    status: 'Lunas',
    metode: 'E-Wallet',
    total: 320000,
    jumlahItem: 5,
    cashDiterima: 320000,
    produkList: const [
      DetailProdukTransaksi(
        kode: 'PRD-81345700',
        nama: 'Tepung Terigu 1kg',
        kategori: 'Sembako',
        harga: 13500,
        qty: 5,
      ),
      DetailProdukTransaksi(
        kode: 'PRD-97513400',
        nama: 'Tisu Gulung',
        kategori: 'Kebutuhan Rumah',
        harga: 45000,
        qty: 2,
      ),
      DetailProdukTransaksi(
        kode: 'PRD-30751800',
        nama: 'Teh Celup',
        kategori: 'Minuman',
        harga: 22000,
        qty: 4,
      ),
    ],
  ),
  RiwayatTransaksiItem(
    invoice: 'INV202602250092',
    tanggal: '25/02/2026',
    waktu: '10:05',
    customer: 'Udin Petot',
    kasir: 'Budi',
    noTelepon: '083456789012',
    status: 'Lunas',
    metode: 'Debit',
    total: 115000,
    jumlahItem: 3,
    cashDiterima: 115000,
    produkList: const [
      DetailProdukTransaksi(
        kode: 'PRD-86421900',
        nama: 'Mie Instan Ayam',
        kategori: 'Makanan',
        harga: 115000,
        qty: 1,
      ),
    ],
  ),
];

// ── Helper format Rupiah ──────────────────────────────────────────────────────
String _rp(int n) {
  if (n == 0) return 'Rp 0';
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
class RiwayatTransaksiPage extends StatefulWidget {
  final String userName;
  final String userRole;

  const RiwayatTransaksiPage({
    super.key,
    this.userName = 'Owner',
    this.userRole = 'Owner',
  });

  @override
  State<RiwayatTransaksiPage> createState() => _RiwayatTransaksiPageState();
}

class _RiwayatTransaksiPageState extends State<RiwayatTransaksiPage>
    with SingleTickerProviderStateMixin, SidebarMixin {
  static const _blue = Color(0xFF3B6FE8);

  // ── State ─────────────────────────────────────────────────────────────────
  late List<RiwayatTransaksiItem> _data;
  final _searchCtrl = TextEditingController();
  String _filterWaktu = 'Semua';
  String _filterMetode = 'Semua';

  static const _waktuList = ['Semua', 'Hari ini', 'Minggu ini', 'Bulan ini'];
  static const _metodeList = ['Semua', 'Tunai', 'Debit', 'E-Wallet', 'Hutang'];

  // ── Filter ────────────────────────────────────────────────────────────────
  List<RiwayatTransaksiItem> get _filtered {
    final q = _searchCtrl.text.trim().toLowerCase();
    final today = DateTime.now();
    return _data.where((t) {
      final matchSearch =
          q.isEmpty ||
          t.invoice.toLowerCase().contains(q) ||
          t.customer.toLowerCase().contains(q) ||
          t.kasir.toLowerCase().contains(q);
      final matchMetode = _filterMetode == 'Semua' || t.metode == _filterMetode;
      bool matchWaktu = true;
      if (_filterWaktu != 'Semua') {
        final parts = t.tanggal.split('/');
        if (parts.length == 3) {
          final tgl = DateTime(
            int.parse(parts[2]),
            int.parse(parts[1]),
            int.parse(parts[0]),
          );
          if (_filterWaktu == 'Hari ini') {
            matchWaktu =
                tgl.year == today.year &&
                tgl.month == today.month &&
                tgl.day == today.day;
          } else if (_filterWaktu == 'Minggu ini') {
            final weekStart = today.subtract(Duration(days: today.weekday - 1));
            matchWaktu = tgl.isAfter(
              weekStart.subtract(const Duration(days: 1)),
            );
          } else if (_filterWaktu == 'Bulan ini') {
            matchWaktu = tgl.year == today.year && tgl.month == today.month;
          }
        }
      }
      return matchSearch && matchMetode && matchWaktu;
    }).toList();
  }

  // ── Stats ─────────────────────────────────────────────────────────────────
  int get _totalTransaksi => _data.length;
  int get _totalPendapatan => _data.fold(0, (s, t) => s + t.total);
  int get _rataRata =>
      _totalTransaksi == 0 ? 0 : (_totalPendapatan / _totalTransaksi).round();
  int get _transaksiHariIni {
    final today = DateTime.now();
    return _data.where((t) {
      final parts = t.tanggal.split('/');
      if (parts.length != 3) return false;
      final tgl = DateTime(
        int.parse(parts[2]),
        int.parse(parts[1]),
        int.parse(parts[0]),
      );
      return tgl.year == today.year &&
          tgl.month == today.month &&
          tgl.day == today.day;
    }).length;
  }

  @override
  void initState() {
    super.initState();
    initSidebar(this);
    _data = List.from(_dummyRiwayat);
  }

  @override
  void dispose() {
    disposeSidebar();
    _searchCtrl.dispose();
    super.dispose();
  }

  void _handleMenuTap(String menu) {
    if (menu == 'Riwayat Transaksi') {
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

  void _resetFilter() => setState(() {
    _searchCtrl.clear();
    _filterWaktu = 'Semua';
    _filterMetode = 'Semua';
  });

  // ── Hapus transaksi ───────────────────────────────────────────────────────
  void _showHapusDialog(RiwayatTransaksiItem t) {
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        contentPadding: const EdgeInsets.fromLTRB(24, 20, 24, 0),
        actionsPadding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 64,
              height: 64,
              decoration: BoxDecoration(
                color: const Color(0xFFE53E3E).withOpacity(0.10),
                shape: BoxShape.circle,
              ),
              child: const Icon(
                Icons.delete_forever_rounded,
                color: Color(0xFFE53E3E),
                size: 32,
              ),
            ),
            const SizedBox(height: 16),
            const Text(
              'Hapus Riwayat?',
              style: TextStyle(
                fontSize: 17,
                fontWeight: FontWeight.bold,
                color: Color(0xFF2D3748),
              ),
            ),
            const SizedBox(height: 10),
            Text(
              'Hapus transaksi ${t.invoice}?\nTindakan ini tidak dapat dibatalkan.',
              textAlign: TextAlign.center,
              style: TextStyle(
                fontSize: 13,
                color: Colors.grey.shade600,
                height: 1.5,
              ),
            ),
            const SizedBox(height: 20),
          ],
        ),
        actions: [
          _dialogBtns(
            onBatal: () => Navigator.pop(context),
            onConfirm: () {
              setState(() => _data.remove(t));
              Navigator.pop(context);
              _snack('Transaksi ${t.invoice} dihapus', Colors.red);
            },
            confirmLabel: 'Ya, Hapus',
            confirmColor: const Color(0xFFE53E3E),
          ),
        ],
      ),
    );
  }

  // ── Print Struk ───────────────────────────────────────────────────────────
  void _showStruk(RiwayatTransaksiItem t) {
    showDialog(
      context: context,
      barrierColor: Colors.black54,
      builder: (_) => Dialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        insetPadding: const EdgeInsets.symmetric(horizontal: 24, vertical: 32),
        child: _StrukWidget(transaksi: t),
      ),
    );
  }

  void _snack(String msg, Color color) =>
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
                _buildFilter(),
                const SizedBox(height: 16),
                _buildTable(filtered),
                const SizedBox(height: 40),
              ],
            ),
          ),
          ...buildSidebarLayer(
            activeMenu: 'Riwayat Transaksi',
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
                          // Navigator.push(context, MaterialPageRoute(
                          //     builder: (_) => const ProfilePage()));
                        },
                      ),
                    ],
                  ),
                  const SizedBox(height: 22),

                  // Title + Tambah Transaksi
                  const Text(
                    'Riwayat Transaksi',
                    style: TextStyle(
                      color: Colors.white,
                      fontSize: 22,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 4),
                  const Text(
                    'Kelola dan pantau semua transaksi penjualan',
                    style: TextStyle(color: Colors.white70, fontSize: 13),
                  ),
                  const SizedBox(height: 16),

                  // Summary cards
                  SizedBox(
                    height: 110,
                    child: ListView(
                      scrollDirection: Axis.horizontal,
                      children: [
                        SummaryCard(
                          label: 'Total Transaksi',
                          value: '$_totalTransaksi',
                          icon: Icons.receipt_long_rounded,
                          color: const Color(0xFF6B9FFF),
                        ),
                        SummaryCard(
                          label: 'Total Pendapatan',
                          value: _rp(_totalPendapatan),
                          icon: Icons.monetization_on_rounded,
                          color: const Color(0xFF48BB78),
                        ),
                        SummaryCard(
                          label: 'Rata-rata/Transaksi',
                          value: _rp(_rataRata),
                          icon: Icons.bar_chart_rounded,
                          color: const Color(0xFFECC94B),
                        ),
                        SummaryCard(
                          label: 'Transaksi Hari Ini',
                          value: '$_transaksiHariIni',
                          icon: Icons.today_rounded,
                          color: const Color(0xFFFC8181),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 16),

                  // Tambah Transaksi button
                  Align(
                    alignment: Alignment.centerRight,
                    child: ElevatedButton.icon(
                      onPressed: () => Navigator.push(
                        context,
                        MaterialPageRoute(builder: (_) => const KasirPage()),
                      ),
                      icon: const Icon(Icons.add_rounded, size: 18),
                      label: const Text(
                        'Tambah Transaksi',
                        style: TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.white,
                        foregroundColor: const Color(0xFF2B55D0),
                        padding: const EdgeInsets.symmetric(
                          horizontal: 16,
                          vertical: 10,
                        ),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(24),
                        ),
                        elevation: 0,
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

  // ── FILTER ────────────────────────────────────────────────────────────────
  Widget _buildFilter() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Container(
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
            const Text(
              'Cari & Filter Transaksi',
              style: TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.w600,
                color: Color(0xFF2D3748),
              ),
            ),
            const SizedBox(height: 12),

            // Search
            TextField(
              controller: _searchCtrl,
              onChanged: (_) => setState(() {}),
              style: const TextStyle(fontSize: 13),
              decoration: InputDecoration(
                hintText: 'Cari invoice, customer, atau kasir...',
                hintStyle: TextStyle(color: Colors.grey.shade400, fontSize: 13),
                prefixIcon: Icon(
                  Icons.search_rounded,
                  color: Colors.grey.shade400,
                  size: 20,
                ),
                filled: true,
                fillColor: const Color(0xFFF5F7FA),
                contentPadding: const EdgeInsets.symmetric(vertical: 10),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(24),
                  borderSide: BorderSide.none,
                ),
              ),
            ),
            const SizedBox(height: 12),

            // Dropdowns + Reset
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: [
                // Jangka waktu
                _dd(
                  _filterWaktu,
                  _waktuList,
                  (v) => setState(() => _filterWaktu = v!),
                  icon: Icons.calendar_today_rounded,
                ),
                // Metode pembayaran
                _dd(
                  _filterMetode,
                  _metodeList,
                  (v) => setState(() => _filterMetode = v!),
                  icon: Icons.payment_rounded,
                ),
                // Reset
                SizedBox(
                  height: 44,
                  child: OutlinedButton.icon(
                    onPressed: _resetFilter,
                    icon: const Icon(Icons.refresh_rounded, size: 16),
                    label: const Text('Reset', style: TextStyle(fontSize: 13)),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: const Color(0xFF4A5568),
                      side: BorderSide(color: Colors.grey.shade300),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(24),
                      ),
                      padding: const EdgeInsets.symmetric(horizontal: 14),
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

  Widget _dd(
    String value,
    List<String> items,
    void Function(String?) fn, {
    IconData? icon,
  }) => Container(
    height: 44,
    padding: const EdgeInsets.symmetric(horizontal: 12),
    decoration: BoxDecoration(
      border: Border.all(color: Colors.grey.shade300),
      borderRadius: BorderRadius.circular(24),
      color: Colors.white,
    ),
    child: DropdownButtonHideUnderline(
      child: DropdownButton<String>(
        value: value,
        isDense: true,
        style: const TextStyle(fontSize: 13, color: Color(0xFF2D3748)),
        icon: const Icon(Icons.keyboard_arrow_down_rounded, size: 20),
        items: items
            .map(
              (e) => DropdownMenuItem(
                value: e,
                child: Text(e, style: const TextStyle(fontSize: 13)),
              ),
            )
            .toList(),
        onChanged: fn,
      ),
    ),
  );

  // ── TABEL ─────────────────────────────────────────────────────────────────
  Widget _buildTable(List<RiwayatTransaksiItem> list) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Text(
                'Daftar Transaksi',
                style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                  color: Color(0xFF2D3748),
                ),
              ),
              const Spacer(),
              Text(
                '${list.length} transaksi',
                style: TextStyle(fontSize: 12, color: Colors.grey.shade500),
              ),
            ],
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
                            'Tidak ada transaksi ditemukan',
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
                      headingRowHeight: 44,
                      dataRowMinHeight: 64,
                      dataRowMaxHeight: 76,
                      columnSpacing: 12,
                      headingTextStyle: const TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.w700,
                        color: Color(0xFF4A5568),
                      ),
                      dataTextStyle: const TextStyle(
                        fontSize: 11,
                        color: Color(0xFF2D3748),
                      ),
                      columns: const [
                        DataColumn(label: Text('INVOICE')),
                        DataColumn(label: Text('TANGGAL')),
                        DataColumn(label: Text('CUSTOMER')),
                        DataColumn(label: Text('KASIR')),
                        DataColumn(label: Text('METODE')),
                        DataColumn(label: Text('STATUS')),
                        DataColumn(label: Text('TOTAL')),
                        DataColumn(label: Text('AKSI')),
                      ],
                      rows: list
                          .map(
                            (t) => DataRow(
                              cells: [
                                // Invoice
                                DataCell(
                                  SizedBox(
                                    width: 130,
                                    child: Text(
                                      t.invoice,
                                      style: const TextStyle(
                                        fontSize: 10,
                                        fontWeight: FontWeight.bold,
                                      ),
                                      overflow: TextOverflow.ellipsis,
                                    ),
                                  ),
                                ),
                                // Tanggal + waktu
                                DataCell(
                                  Column(
                                    crossAxisAlignment:
                                        CrossAxisAlignment.start,
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      Text(
                                        t.tanggal,
                                        style: const TextStyle(
                                          fontSize: 11,
                                          fontWeight: FontWeight.w600,
                                        ),
                                      ),
                                      Text(
                                        t.waktu,
                                        style: TextStyle(
                                          fontSize: 10,
                                          color: Colors.grey.shade500,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                                DataCell(
                                  Text(
                                    t.customer,
                                    style: const TextStyle(fontSize: 11),
                                  ),
                                ),
                                DataCell(Text(t.kasir)),
                                // Metode badge
                                DataCell(_metodeBadge(t.metode)),
                                // Status badge
                                DataCell(_statusBadge(t.status)),
                                // Total + item
                                DataCell(
                                  Column(
                                    crossAxisAlignment:
                                        CrossAxisAlignment.start,
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      Text(
                                        _rp(t.total),
                                        style: const TextStyle(
                                          fontWeight: FontWeight.w600,
                                          fontSize: 11,
                                        ),
                                      ),
                                      Text(
                                        '${t.jumlahItem} item',
                                        style: TextStyle(
                                          fontSize: 10,
                                          color: Colors.grey.shade400,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                                // Aksi
                                DataCell(
                                  Row(
                                    mainAxisSize: MainAxisSize.min,
                                    children: [
                                      // Detail
                                      _AksiBtn(
                                        icon: Icons.visibility_rounded,
                                        color: const Color(0xFF4169E1),
                                        label: 'Detail',
                                        onTap: () => Navigator.push(
                                          context,
                                          MaterialPageRoute(
                                            builder: (_) => DetailTransaksiPage(
                                              transaksi: DetailTransaksiData(
                                                invoice: t.invoice,
                                                tanggal: t.tanggal.replaceAll(
                                                  '/',
                                                  ' ',
                                                ),
                                                waktu: t.waktu,
                                                kasir: t.kasir,
                                                status: t.status,
                                                namaPelanggan: t.customer,
                                                noTelepon: t.noTelepon,
                                                metodePembayaran: t.metode,
                                                produkList: t.produkList,
                                                diskonPersen: t.diskonPersen,
                                                cashDiterima: t.cashDiterima,
                                                isMember: t.isMember,
                                                memberNama: t.memberNama,
                                              ),
                                            ),
                                          ),
                                        ),
                                      ),
                                      const SizedBox(width: 6),
                                      // Cetak Struk
                                      _AksiBtn(
                                        icon: Icons.print_rounded,
                                        color: const Color(0xFF48BB78),
                                        label: 'Cetak',
                                        onTap: () => _showStruk(t),
                                      ),
                                      const SizedBox(width: 6),
                                      // Hapus
                                      _AksiBtn(
                                        icon: Icons.delete_rounded,
                                        color: const Color(0xFFE53E3E),
                                        label: 'Hapus',
                                        onTap: () => _showHapusDialog(t),
                                      ),
                                    ],
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
    Color color;
    switch (status) {
      case 'Lunas':
        color = const Color(0xFF48BB78);
        break;
      case 'Kredit':
      case 'Hutang':
        color = const Color(0xFFE53E3E);
        break;
      default:
        color = const Color(0xFFECC94B);
    }
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: color.withOpacity(0.12),
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

  Widget _metodeBadge(String metode) {
    const colors = {
      'Tunai': Color(0xFF48BB78),
      'Debit': Color(0xFF4169E1),
      'E-Wallet': Color(0xFF6B5CE7),
      'Hutang': Color(0xFFE53E3E),
    };
    final color = colors[metode] ?? const Color(0xFF4A5568);
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: color.withOpacity(0.12),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Text(
        metode,
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
// STRUK WIDGET — tampilan struk thermal/kasir
// ════════════════════════════════════════════════════════════════════════════
class _StrukWidget extends StatelessWidget {
  final RiwayatTransaksiItem transaksi;
  const _StrukWidget({required this.transaksi});

  static const _mono = TextStyle(fontFamily: 'monospace', fontSize: 12);
  static const _monoBold = TextStyle(
    fontFamily: 'monospace',
    fontSize: 12,
    fontWeight: FontWeight.bold,
  );

  @override
  Widget build(BuildContext context) {
    final t = transaksi;
    final subtotal = t.produkList.fold(0, (s, p) => s + p.subtotal);
    final kembalian = t.cashDiterima > 0
        ? (t.cashDiterima - subtotal).clamp(0, 999999999)
        : 0;

    return Container(
      width: 320,
      color: Colors.white,
      child: SingleChildScrollView(
        child: Padding(
          padding: const EdgeInsets.all(20),
          child: Column(
            children: [
              // ── Header toko ────────────────────────────────────────────────
              const Text(
                'TOKO  RONI',
                style: TextStyle(
                  fontFamily: 'monospace',
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                  letterSpacing: 2,
                ),
              ),
              const SizedBox(height: 4),
              const Text('Jl. H.Hasan', style: _mono),
              const Text('Telp: 0812-3456-7890', style: _mono),
              const SizedBox(height: 10),
              const Text(
                'STRUK  TOKO  RONI',
                style: TextStyle(
                  fontFamily: 'monospace',
                  fontSize: 13,
                  fontWeight: FontWeight.bold,
                  letterSpacing: 1,
                ),
              ),
              const SizedBox(height: 8),

              _garis(),

              // ── Info transaksi ─────────────────────────────────────────────
              const SizedBox(height: 6),
              _struRow('No. Invoice', t.invoice, bold: true),
              _struRow('Tanggal', '${t.tanggal} ${t.waktu}', bold: true),
              _struRow('Kasir', t.kasir, bold: true),
              _struRow('Pelanggan', t.customer, bold: true),
              const SizedBox(height: 6),

              _garisTitik(),
              const SizedBox(height: 6),

              // ── Header kolom produk ────────────────────────────────────────
              Row(
                children: [
                  const Expanded(
                    flex: 4,
                    child: Text('Item', style: _monoBold),
                  ),
                  const SizedBox(
                    width: 36,
                    child: Text(
                      'Qty',
                      style: _monoBold,
                      textAlign: TextAlign.center,
                    ),
                  ),
                  const SizedBox(
                    width: 60,
                    child: Text(
                      'Harga',
                      style: _monoBold,
                      textAlign: TextAlign.right,
                    ),
                  ),
                  const SizedBox(
                    width: 65,
                    child: Text(
                      'Subtotal',
                      style: _monoBold,
                      textAlign: TextAlign.right,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 4),
              _garisTitik(),
              const SizedBox(height: 4),

              // ── List produk ────────────────────────────────────────────────
              ...t.produkList.map(
                (p) => Padding(
                  padding: const EdgeInsets.symmetric(vertical: 2),
                  child: Row(
                    children: [
                      Expanded(
                        flex: 4,
                        child: Text(
                          p.nama,
                          style: _mono,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                      SizedBox(
                        width: 36,
                        child: Text(
                          '${p.qty}',
                          style: _mono,
                          textAlign: TextAlign.center,
                        ),
                      ),
                      SizedBox(
                        width: 60,
                        child: Text(
                          _rpStr(p.harga),
                          style: _mono,
                          textAlign: TextAlign.right,
                        ),
                      ),
                      SizedBox(
                        width: 65,
                        child: Text(
                          _rpStr(p.subtotal),
                          style: _mono,
                          textAlign: TextAlign.right,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 4),
              _garis(),
              const SizedBox(height: 6),

              // ── Subtotal ───────────────────────────────────────────────────
              _struRow('Subtotal', _rpStr(subtotal)),
              const SizedBox(height: 4),

              // ── Total ──────────────────────────────────────────────────────
              Row(
                children: [
                  const Expanded(
                    child: Text(
                      'Total',
                      style: TextStyle(
                        fontFamily: 'monospace',
                        fontSize: 14,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                  Text(
                    _rpStr(subtotal),
                    style: const TextStyle(
                      fontFamily: 'monospace',
                      fontSize: 14,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ],
              ),

              if (t.metode == 'Tunai' && t.cashDiterima > 0) ...[
                const SizedBox(height: 4),
                _struRow('Tunai', _rpStr(t.cashDiterima)),
                _struRow('Kembali', _rpStr(kembalian as int)),
              ],

              const SizedBox(height: 6),
              _garisTitik(),
              const SizedBox(height: 6),

              // ── Metode pembayaran ──────────────────────────────────────────
              _struRow('Metode Pembayaran', t.metode.toUpperCase()),
              const SizedBox(height: 6),
              _garis(),
              const SizedBox(height: 10),

              // ── Footer ────────────────────────────────────────────────────
              const Text(
                'Terima Kasih atas Kunjungan Anda',
                style: _mono,
                textAlign: TextAlign.center,
              ),
              const Text(
                'Barang yang sudah dibeli tidak dapat',
                style: _mono,
                textAlign: TextAlign.center,
              ),
              const Text(
                'dikembalikan',
                style: _mono,
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 6),
              const Text('www.tokoroni.com', style: _mono),
              const SizedBox(height: 16),

              // Tombol cetak
              SizedBox(
                width: double.infinity,
                child: ElevatedButton.icon(
                  onPressed: () {
                    // TODO: implementasi print ke printer thermal
                    Navigator.pop(context);
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(
                        content: Text('Mencetak struk...'),
                        backgroundColor: Color(0xFF48BB78),
                        behavior: SnackBarBehavior.floating,
                      ),
                    );
                  },
                  icon: const Icon(Icons.print_rounded, size: 18),
                  label: const Text(
                    'Cetak Struk',
                    style: TextStyle(fontWeight: FontWeight.w600, fontSize: 14),
                  ),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF3B6FE8),
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 12),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(10),
                    ),
                    elevation: 0,
                  ),
                ),
              ),
              TextButton(
                onPressed: () => Navigator.pop(context),
                child: const Text(
                  'Tutup',
                  style: TextStyle(color: Colors.grey),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _garis() => Column(
    children: [
      const Text(
        '----------------------------------------',
        style: TextStyle(
          fontFamily: 'monospace',
          fontSize: 11,
          color: Colors.black87,
        ),
      ),
    ],
  );

  Widget _garisTitik() => const Text(
    '- - - - - - - - - - - - - - - - - - - -',
    style: TextStyle(
      fontFamily: 'monospace',
      fontSize: 11,
      color: Colors.black54,
    ),
  );

  Widget _struRow(String label, String value, {bool bold = false}) => Padding(
    padding: const EdgeInsets.symmetric(vertical: 1.5),
    child: Row(
      children: [
        Expanded(child: Text(label, style: bold ? _monoBold : _mono)),
        Text(value, style: bold ? _monoBold : _mono),
      ],
    ),
  );

  // Format rupiah tanpa prefix panjang untuk struk
  String _rpStr(int n) {
    if (n == 0) return 'Rp 0';
    final s = n.toString();
    final buf = StringBuffer('Rp ');
    for (int i = 0; i < s.length; i++) {
      if (i > 0 && (s.length - i) % 3 == 0) buf.write('.');
      buf.write(s[i]);
    }
    return buf.toString();
  }
}

// ════════════════════════════════════════════════════════════════════════════
// AKSI BUTTON
// ════════════════════════════════════════════════════════════════════════════
class _AksiBtn extends StatelessWidget {
  final IconData icon;
  final Color color;
  final String label;
  final VoidCallback onTap;
  const _AksiBtn({
    required this.icon,
    required this.color,
    required this.label,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) => GestureDetector(
    onTap: onTap,
    child: Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        Container(
          padding: const EdgeInsets.all(8),
          decoration: BoxDecoration(
            color: color.withOpacity(0.13),
            borderRadius: BorderRadius.circular(9),
          ),
          child: Icon(icon, color: color, size: 16),
        ),
        const SizedBox(height: 2),
        Text(
          label,
          style: TextStyle(
            fontSize: 9,
            color: color,
            fontWeight: FontWeight.w500,
          ),
        ),
      ],
    ),
  );
}

// ════════════════════════════════════════════════════════════════════════════
// DIALOG BUTTONS
// ════════════════════════════════════════════════════════════════════════════
Widget _dialogBtns({
  required VoidCallback onBatal,
  required VoidCallback onConfirm,
  required String confirmLabel,
  required Color confirmColor,
}) => Row(
  children: [
    Expanded(
      child: OutlinedButton(
        onPressed: onBatal,
        style: OutlinedButton.styleFrom(
          side: BorderSide(color: Colors.grey.shade300),
          padding: const EdgeInsets.symmetric(vertical: 12),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(10),
          ),
        ),
        child: const Text(
          'Batal',
          style: TextStyle(color: Colors.grey, fontWeight: FontWeight.w600),
        ),
      ),
    ),
    const SizedBox(width: 10),
    Expanded(
      child: ElevatedButton(
        onPressed: onConfirm,
        style: ElevatedButton.styleFrom(
          backgroundColor: confirmColor,
          foregroundColor: Colors.white,
          padding: const EdgeInsets.symmetric(vertical: 12),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(10),
          ),
          elevation: 0,
        ),
        child: Text(
          confirmLabel,
          style: const TextStyle(fontWeight: FontWeight.w600),
        ),
      ),
    ),
  ],
);
