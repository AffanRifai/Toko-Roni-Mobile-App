// lib/delivery/manajemen_pengiriman_page.dart
import 'package:flutter/material.dart';
import 'package:tokoronifrontend/category/manajemen_kategori_page.dart';
import 'package:tokoronifrontend/home/beranda_page.dart';
import 'package:tokoronifrontend/home/menu_pages.dart';
import 'package:tokoronifrontend/member/daftar_member_page.dart';
import 'package:tokoronifrontend/product/daftar_produk_page.dart';
import 'package:tokoronifrontend/report/laporan_penjualan_page.dart';
import 'package:tokoronifrontend/transaction/riwayat_transaksi_page.dart';
import 'package:tokoronifrontend/user/manajemen_pengguna_page.dart';
import '../widgets/shared_widgets.dart';
import '../widgets/notifikasi_widget.dart';
import '../widgets/semua_notifikasi_page.dart';
import '../widgets/profile_widget.dart';
import 'detail_pengiriman_page.dart';
import 'tambah_pengiriman_page.dart';

// ════════════════════════════════════════════════════════════════════════════
// MODEL
// ════════════════════════════════════════════════════════════════════════════
class PengirimanItem {
  final String kodePengiriman;
  final String invoice;
  final String tujuan;
  final String tanggalDibuat; // '25/03/2026'
  final String jamDibuat; // '01:43'
  final String namaCustomer;
  final int totalBelanja;
  final int totalItem;
  String
  status; // 'Pending' | 'Diproses' | 'Assigned' | 'Diambil' | 'Dalam Perjalanan' | 'Terkirim' | 'Gagal' | 'Dibatalkan'
  String? namaKurir;
  String? nomorKurir;
  String? kendaraan;

  PengirimanItem({
    required this.kodePengiriman,
    required this.invoice,
    required this.tujuan,
    required this.tanggalDibuat,
    required this.jamDibuat,
    required this.namaCustomer,
    required this.totalBelanja,
    required this.totalItem,
    required this.status,
    this.namaKurir,
    this.nomorKurir,
    this.kendaraan,
  });
}

// ── Dummy data ────────────────────────────────────────────────────────────────
final List<PengirimanItem> _dummyPengiriman = [
  PengirimanItem(
    kodePengiriman: 'DEL202603250001',
    invoice: 'INV202603250001',
    tujuan: 'Jl. Merdeka No.5, Indramayu',
    tanggalDibuat: '25/03/2026',
    jamDibuat: '01:43',
    namaCustomer: 'Asep Saepudin',
    totalBelanja: 180000,
    totalItem: 3,
    status: 'Terkirim',
    namaKurir: 'luhut',
    nomorKurir: '0831 4287 8951',
    kendaraan: 'Motor - B 1234 XYZ',
  ),
  PengirimanItem(
    kodePengiriman: 'DEL202603250002',
    invoice: 'INV202603250002',
    tujuan: 'jhgfd',
    tanggalDibuat: '25/03/2026',
    jamDibuat: '02:00',
    namaCustomer: 'Pelanggan Umum',
    totalBelanja: 50000,
    totalItem: 1,
    status: 'Assigned',
    namaKurir: 'luhut',
    nomorKurir: '0831 4287 8951',
    kendaraan: 'Motor - B 1234 XYZ',
  ),
  PengirimanItem(
    kodePengiriman: 'DEL202603240001',
    invoice: 'INV202603240001',
    tujuan: 'Jl. Veteran No.17, Jakarta',
    tanggalDibuat: '24/03/2026',
    jamDibuat: '14:30',
    namaCustomer: 'Jomod',
    totalBelanja: 320000,
    totalItem: 5,
    status: 'Dalam Perjalanan',
    namaKurir: 'Budi',
    nomorKurir: '0812 3456 7890',
    kendaraan: 'Motor - B 5678 ABC',
  ),
  PengirimanItem(
    kodePengiriman: 'DEL202603230001',
    invoice: 'INV202603230001',
    tujuan: 'jawa',
    tanggalDibuat: '23/03/2026',
    jamDibuat: '15:22',
    namaCustomer: 'Udin Petot',
    totalBelanja: 115000,
    totalItem: 2,
    status: 'Diproses',
  ),
  PengirimanItem(
    kodePengiriman: 'DEL202603220001',
    invoice: 'INV202603220001',
    tujuan: 'Bandung Barat',
    tanggalDibuat: '22/03/2026',
    jamDibuat: '10:00',
    namaCustomer: 'Munip',
    totalBelanja: 240000,
    totalItem: 4,
    status: 'Pending',
  ),
  PengirimanItem(
    kodePengiriman: 'DEL202603210001',
    invoice: 'INV202603210001',
    tujuan: 'Cirebon Timur',
    tanggalDibuat: '21/03/2026',
    jamDibuat: '09:00',
    namaCustomer: 'Mastem',
    totalBelanja: 75000,
    totalItem: 1,
    status: 'Dibatalkan',
  ),
  PengirimanItem(
    kodePengiriman: 'DEL202603200001',
    invoice: 'INV202603200001',
    tujuan: 'Subang Selatan',
    tanggalDibuat: '20/03/2026',
    jamDibuat: '16:45',
    namaCustomer: 'Pelanggan Umum',
    totalBelanja: 95000,
    totalItem: 2,
    status: 'Gagal',
  ),
  PengirimanItem(
    kodePengiriman: 'DEL202603190001',
    invoice: 'INV202603190001',
    tujuan: 'Majalengka Utara',
    tanggalDibuat: '19/03/2026',
    jamDibuat: '11:20',
    namaCustomer: 'Pelanggan Umum',
    totalBelanja: 155000,
    totalItem: 3,
    status: 'Diambil',
    namaKurir: 'Rusdi',
    nomorKurir: '0823 4567 8901',
    kendaraan: 'Mobil Pick-up - B 9012 DEF',
  ),
];

// ── Dummy kurir ───────────────────────────────────────────────────────────────
const _dummyKurirList = ['luhut', 'Budi', 'Rusdi', 'Andi'];
const _dummyKendaraanList = [
  'Motor - B 1234 XYZ (Andi)',
  'Motor - B 5678 ABC (Budi)',
  'Mobil Pick-up - B 9012 DEF (Rusdi)',
];
const _statusFilterList = [
  'Semua',
  'Pending',
  'Diproses',
  'Assigned',
  'Diambil',
  'Dalam Perjalanan',
  'Terkirim',
  'Gagal',
  'Dibatalkan',
];

// ── Rupiah helper ─────────────────────────────────────────────────────────────
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
class ManajemenPengirimanPage extends StatefulWidget {
  final String userName;
  final String userRole;
  const ManajemenPengirimanPage({
    super.key,
    this.userName = 'Owner',
    this.userRole = 'Owner',
  });

  @override
  State<ManajemenPengirimanPage> createState() =>
      _ManajemenPengirimanPageState();
}

class _ManajemenPengirimanPageState extends State<ManajemenPengirimanPage>
    with SingleTickerProviderStateMixin, SidebarMixin {
  static const _blue = Color(0xFF3B6FE8);

  late List<PengirimanItem> _data;
  final _searchCtrl = TextEditingController();
  String _filterStatus = 'Semua';
  String _filterKurir = 'Semua Kurir';
  final _dariCtrl = TextEditingController();
  final _sampaiCtrl = TextEditingController();
  DateTime? _dariTgl;
  DateTime? _sampaiTgl;

  // ── Filter ────────────────────────────────────────────────────────────────
  List<PengirimanItem> get _filtered {
    final q = _searchCtrl.text.trim().toLowerCase();
    return _data.where((p) {
      final matchSearch =
          q.isEmpty ||
          p.kodePengiriman.toLowerCase().contains(q) ||
          p.invoice.toLowerCase().contains(q) ||
          p.tujuan.toLowerCase().contains(q);
      final matchStatus = _filterStatus == 'Semua' || p.status == _filterStatus;
      final matchKurir =
          _filterKurir == 'Semua Kurir' ||
          (p.namaKurir?.toLowerCase() == _filterKurir.toLowerCase());
      bool matchTgl = true;
      if (_dariTgl != null || _sampaiTgl != null) {
        final parts = p.tanggalDibuat.split('/');
        if (parts.length == 3) {
          final tgl = DateTime(
            int.parse(parts[2]),
            int.parse(parts[1]),
            int.parse(parts[0]),
          );
          if (_dariTgl != null && tgl.isBefore(_dariTgl!)) matchTgl = false;
          if (_sampaiTgl != null && tgl.isAfter(_sampaiTgl!)) matchTgl = false;
        }
      }
      return matchSearch && matchStatus && matchKurir && matchTgl;
    }).toList();
  }

  // ── Stats ─────────────────────────────────────────────────────────────────
  int get _total => _data.length;
  int get _menunggu => _data
      .where((p) => p.status == 'Pending' || p.status == 'Diproses')
      .length;
  int get _dalamPerjalanan => _data
      .where(
        (p) =>
            p.status == 'Dalam Perjalanan' ||
            p.status == 'Diambil' ||
            p.status == 'Assigned',
      )
      .length;
  int get _terkirim => _data.where((p) => p.status == 'Terkirim').length;
  int get _gagal => _data
      .where((p) => p.status == 'Gagal' || p.status == 'Dibatalkan')
      .length;

  @override
  void initState() {
    super.initState();
    initSidebar(this);
    _data = List.from(_dummyPengiriman);
  }

  @override
  void dispose() {
    disposeSidebar();
    _searchCtrl.dispose();
    _dariCtrl.dispose();
    _sampaiCtrl.dispose();
    super.dispose();
  }

  void _handleMenuTap(String menu) {
    if (menu == 'Manajemen Pengiriman') {
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
        page = const ManajemenKategoriPage();
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
    _dariCtrl.clear();
    _sampaiCtrl.clear();
    _filterStatus = 'Semua';
    _filterKurir = 'Semua Kurir';
    _dariTgl = null;
    _sampaiTgl = null;
  });

  // ── Date pickers ──────────────────────────────────────────────────────────
  Future<void> _pickDate(bool isDari) async {
    final picked = await showDatePicker(
      context: context,
      initialDate: isDari
          ? (_dariTgl ?? DateTime.now())
          : (_sampaiTgl ?? DateTime.now()),
      firstDate: DateTime(2024),
      lastDate: DateTime.now().add(const Duration(days: 365)),
      builder: (ctx, child) => Theme(
        data: Theme.of(ctx).copyWith(
          colorScheme: const ColorScheme.light(
            primary: _blue,
            onPrimary: Colors.white,
          ),
        ),
        child: child!,
      ),
    );
    if (picked != null)
      setState(() {
        if (isDari) {
          _dariTgl = picked;
          _dariCtrl.text =
              '${picked.day.toString().padLeft(2, '0')}/${picked.month.toString().padLeft(2, '0')}/${picked.year}';
        } else {
          _sampaiTgl = picked;
          _sampaiCtrl.text =
              '${picked.day.toString().padLeft(2, '0')}/${picked.month.toString().padLeft(2, '0')}/${picked.year}';
        }
      });
  }

  // ── Batalkan pengiriman ───────────────────────────────────────────────────
  void _batalkan(PengirimanItem p) {
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
              width: 60,
              height: 60,
              decoration: BoxDecoration(
                color: Colors.red.withOpacity(0.08),
                shape: BoxShape.circle,
              ),
              child: const Icon(
                Icons.cancel_rounded,
                color: Color(0xFFE53E3E),
                size: 28,
              ),
            ),
            const SizedBox(height: 14),
            const Text(
              'Batalkan Pengiriman?',
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.bold,
                color: Color(0xFF2D3748),
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'Pengiriman ${p.kodePengiriman} akan dibatalkan.',
              textAlign: TextAlign.center,
              style: TextStyle(
                fontSize: 13,
                color: Colors.grey.shade600,
                height: 1.4,
              ),
            ),
            const SizedBox(height: 20),
          ],
        ),
        actions: [
          _dialogBtns(
            onBatal: () => Navigator.pop(context),
            onConfirm: () {
              setState(() => p.status = 'Dibatalkan');
              Navigator.pop(context);
              _snack(
                'Pengiriman ${p.kodePengiriman} dibatalkan',
                Colors.orange,
              );
            },
            confirmLabel: 'Ya, Batalkan',
            confirmColor: const Color(0xFFE53E3E),
          ),
        ],
      ),
    );
  }

  // ── Assign kurir modal ────────────────────────────────────────────────────
  void _showAssignKurir(PengirimanItem p) {
    String? selectedKurir = p.namaKurir;
    String? selectedKendaraan = p.kendaraan != null
        ? _dummyKendaraanList.firstWhere(
            (k) => k.startsWith(p.kendaraan!.split(' - ').first),
            orElse: () => _dummyKendaraanList.first,
          )
        : null;

    showDialog(
      context: context,
      builder: (_) => StatefulBuilder(
        builder: (ctx, setModalState) => AlertDialog(
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(20),
          ),
          titlePadding: const EdgeInsets.fromLTRB(20, 20, 20, 0),
          contentPadding: const EdgeInsets.fromLTRB(20, 12, 20, 0),
          actionsPadding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
          title: Row(
            children: [
              const Icon(Icons.person_add_rounded, color: _blue, size: 20),
              const SizedBox(width: 8),
              const Text(
                'Assign Kurir & Kendaraan',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
              ),
              const Spacer(),
              GestureDetector(
                onTap: () => Navigator.pop(ctx),
                child: Icon(Icons.close, size: 20, color: Colors.grey.shade400),
              ),
            ],
          ),
          content: SingleChildScrollView(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                const SizedBox(height: 8),
                // Info pengiriman
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: const Color(0xFFF0F4FF),
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(color: const Color(0xFFBEE3F8)),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          const Icon(
                            Icons.info_outline_rounded,
                            color: _blue,
                            size: 14,
                          ),
                          const SizedBox(width: 6),
                          const Text(
                            'Informasi Pengiriman',
                            style: TextStyle(
                              fontSize: 12,
                              color: _blue,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 8),
                      _infoRow('Kode:', p.kodePengiriman),
                      _infoRow('Tujuan:', p.tujuan),
                      _infoRow('Total Item:', '${p.totalItem} barang'),
                    ],
                  ),
                ),
                const SizedBox(height: 16),
                // Pilih Kurir
                Row(
                  children: const [
                    Icon(
                      Icons.person_rounded,
                      size: 14,
                      color: Color(0xFF4A5568),
                    ),
                    SizedBox(width: 5),
                    Text(
                      'Pilih Kurir',
                      style: TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                    Text(' *', style: TextStyle(color: Colors.red)),
                  ],
                ),
                const SizedBox(height: 6),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12),
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(color: Colors.grey.shade300),
                  ),
                  child: DropdownButtonHideUnderline(
                    child: DropdownButton<String>(
                      value: selectedKurir,
                      isExpanded: true,
                      hint: Text(
                        '-- Pilih Kurir --',
                        style: TextStyle(
                          color: Colors.grey.shade400,
                          fontSize: 13,
                        ),
                      ),
                      style: const TextStyle(
                        fontSize: 13,
                        color: Color(0xFF2D3748),
                      ),
                      icon: const Icon(
                        Icons.keyboard_arrow_down_rounded,
                        size: 20,
                      ),
                      items: _dummyKurirList
                          .map(
                            (k) => DropdownMenuItem(value: k, child: Text(k)),
                          )
                          .toList(),
                      onChanged: (v) => setModalState(() => selectedKurir = v),
                    ),
                  ),
                ),
                const SizedBox(height: 14),
                // Pilih Kendaraan
                Row(
                  children: const [
                    Icon(
                      Icons.local_shipping_rounded,
                      size: 14,
                      color: Color(0xFF4A5568),
                    ),
                    SizedBox(width: 5),
                    Text(
                      'Pilih Kendaraan',
                      style: TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                    Text(' *', style: TextStyle(color: Colors.red)),
                  ],
                ),
                const SizedBox(height: 6),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12),
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(color: Colors.grey.shade300),
                  ),
                  child: DropdownButtonHideUnderline(
                    child: DropdownButton<String>(
                      value: selectedKendaraan,
                      isExpanded: true,
                      hint: Text(
                        '-- Pilih Kendaraan --',
                        style: TextStyle(
                          color: Colors.grey.shade400,
                          fontSize: 13,
                        ),
                      ),
                      style: const TextStyle(
                        fontSize: 13,
                        color: Color(0xFF2D3748),
                      ),
                      icon: const Icon(
                        Icons.keyboard_arrow_down_rounded,
                        size: 20,
                      ),
                      items: _dummyKendaraanList
                          .map(
                            (k) => DropdownMenuItem(
                              value: k,
                              child: Text(
                                k,
                                style: const TextStyle(fontSize: 12),
                              ),
                            ),
                          )
                          .toList(),
                      onChanged: (v) =>
                          setModalState(() => selectedKendaraan = v),
                    ),
                  ),
                ),
                const SizedBox(height: 8),
              ],
            ),
          ),
          actions: [
            Row(
              children: [
                Expanded(
                  child: ElevatedButton.icon(
                    onPressed: () {
                      if (selectedKurir == null || selectedKendaraan == null) {
                        _snack(
                          'Pilih kurir dan kendaraan terlebih dahulu',
                          Colors.red,
                        );
                        return;
                      }
                      setState(() {
                        p.namaKurir = selectedKurir;
                        p.nomorKurir = '0831 4287 8951'; // dummy
                        p.kendaraan = selectedKendaraan!.split(' (').first;
                        if (p.status == 'Pending' || p.status == 'Diproses')
                          p.status = 'Assigned';
                      });
                      Navigator.pop(ctx);
                      _snack(
                        'Kurir berhasil di-assign ke ${p.kodePengiriman}',
                        const Color(0xFF48BB78),
                      );
                    },
                    icon: const Icon(Icons.check_rounded, size: 16),
                    label: const Text(
                      'Assign Sekarang',
                      style: TextStyle(fontWeight: FontWeight.w600),
                    ),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: _blue,
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 12),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(10),
                      ),
                      elevation: 0,
                    ),
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: OutlinedButton(
                    onPressed: () => Navigator.pop(ctx),
                    style: OutlinedButton.styleFrom(
                      side: BorderSide(color: Colors.grey.shade300),
                      padding: const EdgeInsets.symmetric(vertical: 12),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(10),
                      ),
                    ),
                    child: const Text(
                      'Batal',
                      style: TextStyle(
                        color: Colors.grey,
                        fontWeight: FontWeight.w600,
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

  Widget _infoRow(String label, String value) => Padding(
    padding: const EdgeInsets.symmetric(vertical: 2),
    child: Row(
      children: [
        Text(
          label,
          style: TextStyle(fontSize: 11, color: Colors.grey.shade600),
        ),
        const SizedBox(width: 8),
        Expanded(
          child: Text(
            value,
            textAlign: TextAlign.right,
            style: const TextStyle(
              fontSize: 11,
              fontWeight: FontWeight.w600,
              color: Color(0xFF2D3748),
            ),
          ),
        ),
      ],
    ),
  );

  // ── Cetak surat jalan ─────────────────────────────────────────────────────
  void _cetakSuratJalan(PengirimanItem p) => _snack(
    'Mencetak surat jalan ${p.kodePengiriman}...',
    const Color(0xFF48BB78),
  );

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
            activeMenu: 'Pengiriman',
            onMenuTap: _handleMenuTap,
          ),
        ],
      ),
    );
  }

  // ── HEADER ────────────────────────────────────────────────────────────────
  Widget _buildHeader() => Container(
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
                const Text(
                  'Manajemen Pengiriman',
                  style: TextStyle(
                    color: Colors.white,
                    fontSize: 22,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: 4),
                const Text(
                  'Kelola dan pantau semua pengiriman',
                  style: TextStyle(color: Colors.white70, fontSize: 13),
                ),
                const SizedBox(height: 20),
                // Summary cards
                SizedBox(
                  height: 110,
                  child: ListView(
                    scrollDirection: Axis.horizontal,
                    children: [
                      SummaryCard(
                        label: 'Total Pengiriman',
                        value: '$_total',
                        icon: Icons.local_shipping_rounded,
                        color: const Color(0xFF6B9FFF),
                      ),
                      SummaryCard(
                        label: 'Menunggu Diproses',
                        value: '$_menunggu',
                        icon: Icons.hourglass_empty_rounded,
                        color: const Color(0xFFECC94B),
                      ),
                      SummaryCard(
                        label: 'Dalam Perjalanan',
                        value: '$_dalamPerjalanan',
                        icon: Icons.directions_car_rounded,
                        color: const Color(0xFF48BB78),
                      ),
                      SummaryCard(
                        label: 'Terkirim',
                        value: '$_terkirim',
                        icon: Icons.check_circle_rounded,
                        color: const Color(0xFF6B5CE7),
                      ),
                      SummaryCard(
                        label: 'Pengiriman Gagal',
                        value: '$_gagal',
                        icon: Icons.cancel_rounded,
                        color: const Color(0xFFFC8181),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 16),

                // ── 3 tombol aksi cepat ──────────────────────────────────────────
                Wrap(
                  spacing: 10,
                  runSpacing: 8,
                  children: [
                    // Tambah Pengiriman
                    ElevatedButton.icon(
                      onPressed: () => Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (_) => const TambahPengirimanPage(),
                        ),
                      ),
                      icon: const Icon(Icons.add_rounded, size: 16),
                      label: const Text(
                        'Tambah Pengiriman',
                        style: TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.white,
                        foregroundColor: const Color(0xFF2B55D0),
                        padding: const EdgeInsets.symmetric(
                          horizontal: 14,
                          vertical: 9,
                        ),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(20),
                        ),
                        elevation: 0,
                      ),
                    ),
                    // Tambah Kurir
                    ElevatedButton.icon(
                      onPressed: () {
                        // TODO: Navigator.push ke TambahPenggunaPage
                        // Navigator.push(context, MaterialPageRoute(
                        //     builder: (_) => const TambahPenggunaPage()));
                        _snack(
                          'Mengarahkan ke Tambah Pengguna...',
                          const Color(0xFF48BB78),
                        );
                      },
                      icon: const Icon(Icons.person_add_rounded, size: 16),
                      label: const Text(
                        'Tambah Kurir',
                        style: TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.white.withOpacity(0.85),
                        foregroundColor: const Color(0xFF2B55D0),
                        padding: const EdgeInsets.symmetric(
                          horizontal: 14,
                          vertical: 9,
                        ),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(20),
                        ),
                        elevation: 0,
                      ),
                    ),
                    // Tambah Kendaraan (on-going)
                    ElevatedButton.icon(
                      onPressed: () => _snack(
                        'Fitur tambah kendaraan sedang dikembangkan',
                        const Color(0xFFECC94B),
                      ),
                      icon: const Icon(Icons.directions_car_rounded, size: 16),
                      label: const Text(
                        'Tambah Kendaraan',
                        style: TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.white.withOpacity(0.70),
                        foregroundColor: const Color(0xFF4A5568),
                        padding: const EdgeInsets.symmetric(
                          horizontal: 14,
                          vertical: 9,
                        ),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(20),
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

  // ── FILTER ────────────────────────────────────────────────────────────────
  Widget _buildFilter() => Padding(
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
            'Filter Pengiriman',
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
              hintText: 'Cari kode pengiriman, invoice, tujuan...',
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
          const SizedBox(height: 10),
          // Dropdowns + tanggal
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: [
              _dd(
                _filterStatus,
                _statusFilterList,
                (v) => setState(() => _filterStatus = v!),
              ),
              _dd(_filterKurir, [
                'Semua Kurir',
                ..._dummyKurirList,
              ], (v) => setState(() => _filterKurir = v!)),
              // Dari tanggal
              _dateInput(_dariCtrl, 'Dari', () => _pickDate(true)),
              // Sampai tanggal
              _dateInput(_sampaiCtrl, 'Sampai', () => _pickDate(false)),
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

  Widget _dd(String value, List<String> items, void Function(String?) fn) =>
      Container(
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

  Widget _dateInput(
    TextEditingController ctrl,
    String hint,
    VoidCallback onTap,
  ) => SizedBox(
    height: 44,
    width: 140,
    child: TextField(
      controller: ctrl,
      readOnly: true,
      onTap: onTap,
      style: const TextStyle(fontSize: 12),
      decoration: InputDecoration(
        hintText: '$hint Tanggal',
        hintStyle: TextStyle(color: Colors.grey.shade400, fontSize: 12),
        filled: true,
        fillColor: Colors.white,
        contentPadding: const EdgeInsets.only(
          left: 12,
          top: 11,
          bottom: 11,
          right: 8,
        ),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(24),
          borderSide: BorderSide(color: Colors.grey.shade300),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(24),
          borderSide: BorderSide(color: Colors.grey.shade300),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(24),
          borderSide: const BorderSide(color: _blue),
        ),
        suffixIcon: Padding(
          padding: const EdgeInsets.only(right: 12),
          child: Icon(
            Icons.calendar_today_rounded,
            size: 16,
            color: Colors.grey.shade400,
          ),
        ),
        suffixIconConstraints: const BoxConstraints(
          minWidth: 38,
          minHeight: 44,
        ),
      ),
    ),
  );

  // ── TABEL ─────────────────────────────────────────────────────────────────
  Widget _buildTable(List<PengirimanItem> list) => Padding(
    padding: const EdgeInsets.symmetric(horizontal: 16),
    child: Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            const Text(
              'Daftar Pengiriman',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
                color: Color(0xFF2D3748),
              ),
            ),
            const Spacer(),
            Text(
              '${list.length} pengiriman',
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
                          Icons.local_shipping_rounded,
                          size: 40,
                          color: Colors.grey.shade300,
                        ),
                        const SizedBox(height: 10),
                        Text(
                          'Tidak ada pengiriman ditemukan',
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
                    dataRowMinHeight: 72,
                    dataRowMaxHeight: 88,
                    columnSpacing: 8,
                    horizontalMargin: 10,
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
                      DataColumn(label: Text('KODE')),
                      DataColumn(label: Text('INVOICE')),
                      DataColumn(label: Text('TUJUAN')),
                      DataColumn(label: Text('KURIR')),
                      DataColumn(label: Text('STATUS')),
                      DataColumn(label: Text('AKSI')),
                    ],
                    rows: list
                        .asMap()
                        .entries
                        .map(
                          (entry) {
                            final index = entry.key;
                            final p = entry.value;
                            return DataRow(
                            cells: [
                              // No urut
                              DataCell(
                                SizedBox(
                                  width: 32,
                                  child: Text(
                                    '${index + 1}',
                                    textAlign: TextAlign.center,
                                    style: const TextStyle(
                                      fontSize: 11,
                                      fontWeight: FontWeight.w600,
                                    ),
                                  ),
                                ),
                              ),
                              // Kode
                              DataCell(
                                SizedBox(
                                  width: 130,
                                  child: Text(
                                    p.kodePengiriman,
                                    style: const TextStyle(
                                      fontSize: 11,
                                      fontWeight: FontWeight.bold,
                                    ),
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                ),
                              ),
                              // Invoice + tanggal
                              DataCell(
                                SizedBox(
                                  width: 145,
                                  child: Column(
                                    crossAxisAlignment:
                                        CrossAxisAlignment.start,
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      Text(
                                        p.invoice,
                                        style: const TextStyle(
                                          fontSize: 11,
                                          fontWeight: FontWeight.w600,
                                        ),
                                        overflow: TextOverflow.ellipsis,
                                      ),
                                      const SizedBox(height: 2),
                                      Text(
                                        '${p.tanggalDibuat}  ${p.jamDibuat}',
                                        style: TextStyle(
                                          fontSize: 10,
                                          color: Colors.grey.shade500,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ),
                              // Tujuan
                              DataCell(
                                SizedBox(
                                  width: 145,
                                  child: Text(
                                    p.tujuan,
                                    style: const TextStyle(fontSize: 12),
                                    overflow: TextOverflow.ellipsis,
                                    maxLines: 2,
                                  ),
                                ),
                              ),
                              // Kurir
                              DataCell(
                                SizedBox(
                                  width: 145,
                                  child: p.namaKurir != null
                                      ? Column(
                                          crossAxisAlignment:
                                              CrossAxisAlignment.start,
                                          mainAxisAlignment:
                                              MainAxisAlignment.center,
                                          children: [
                                            Text(
                                              p.namaKurir!,
                                              style: const TextStyle(
                                                fontSize: 12,
                                                fontWeight: FontWeight.w600,
                                              ),
                                            ),
                                            const SizedBox(height: 2),
                                            if (p.kendaraan != null)
                                              Text(
                                                p.kendaraan!,
                                                style: TextStyle(
                                                  fontSize: 10,
                                                  color: Colors.grey.shade500,
                                                ),
                                                overflow: TextOverflow.ellipsis,
                                              ),
                                          ],
                                        )
                                      : Text(
                                          'Belum ada kurir',
                                          style: TextStyle(
                                            fontSize: 11,
                                            color: Colors.grey.shade400,
                                            fontStyle: FontStyle.italic,
                                          ),
                                        ),
                                ),
                              ),
                              // Status badge
                              DataCell(_statusBadge(p.status)),
                              // Aksi — Row horizontal rapat
                              DataCell(
                                Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    _AksiBtn(
                                      icon: Icons.visibility_rounded,
                                      color: const Color(0xFF4169E1),
                                      label: 'Detail',
                                      onTap: () => Navigator.push(
                                        context,
                                        MaterialPageRoute(
                                          builder: (_) => DetailPengirimanPage(
                                            pengiriman: p,
                                          ),
                                        ),
                                      ),
                                    ),
                                    if (p.status == 'Pending' ||
                                        p.status == 'Diproses') ...[
                                      const SizedBox(width: 8),
                                      _AksiBtn(
                                        icon: Icons.person_add_rounded,
                                        color: const Color(0xFF48BB78),
                                        label: 'Assign',
                                        onTap: () => _showAssignKurir(p),
                                      ),
                                    ],
                                    const SizedBox(width: 8),
                                    _AksiBtn(
                                      icon: Icons.print_rounded,
                                      color: const Color(0xFF6B5CE7),
                                      label: 'Cetak',
                                      onTap: () => _cetakSuratJalan(p),
                                    ),
                                    if (p.status != 'Terkirim' &&
                                        p.status != 'Dibatalkan' &&
                                        p.status != 'Gagal') ...[
                                      const SizedBox(width: 8),
                                      _AksiBtn(
                                        icon: Icons.cancel_rounded,
                                        color: const Color(0xFFE53E3E),
                                        label: 'Batal',
                                        onTap: () => _batalkan(p),
                                      ),
                                    ],
                                  ],
                                ),
                              ),
                            ],
                          );
                          },
                        )
                        .toList(),
                  ),
                ),
        ),
      ],
    ),
  );

  Widget _statusBadge(String status) {
    final c = _statusColor(status);
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
      decoration: BoxDecoration(
        color: c.withOpacity(0.12),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Text(
        status,
        style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: c),
      ),
    );
  }

  static Color _statusColor(String s) {
    switch (s) {
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
        const SizedBox(height: 3),
        Text(
          label,
          style: TextStyle(
            fontSize: 10,
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
