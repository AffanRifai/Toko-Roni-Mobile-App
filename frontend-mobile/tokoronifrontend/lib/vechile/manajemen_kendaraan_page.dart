// lib/delivery/manajemen_kendaraan_page.dart
import 'package:flutter/material.dart';
import 'package:tokoronifrontend/category/manajemen_kategori_page.dart';
import 'package:tokoronifrontend/delivery/manajemen_pengiriman_page.dart';
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
import 'detail_kendaraan_page.dart';
import 'tambah_kendaraan_page.dart';
import 'edit_kendaraan_page.dart';

// ════════════════════════════════════════════════════════════════════════════
// MODEL
// ════════════════════════════════════════════════════════════════════════════
class KendaraanItem {
  final String id;
  String nama;
  String platNomor;
  String jenis; // 'Motor' | 'Mobil Pick-up' | 'Truck'
  String status; // 'Tersedia' | 'Sedang Digunakan' | 'Servis'
  String tanggalMaintenance;
  double kapasitasBerat; // kg
  double kapasitasVolume; // m³
  String catatan;
  String warna;
  String tahun;

  KendaraanItem({
    required this.id,
    required this.nama,
    required this.platNomor,
    required this.jenis,
    required this.status,
    required this.tanggalMaintenance,
    this.kapasitasBerat = 0,
    this.kapasitasVolume = 0,
    this.catatan = '',
    this.warna = '',
    this.tahun = '',
  });
}

// ── Constants — di-export supaya bisa dipakai di tambah & edit ────────────────
const jenisKendaraanList = ['Motor', 'Mobil Pick-up', 'Truck'];
const statusKendaraanList = ['Tersedia', 'Sedang Digunakan', 'Servis'];
const _statusFilterList = ['Semua', 'Tersedia', 'Sedang Digunakan', 'Servis'];
const _jenisFilterList = ['Semua Jenis', 'Motor', 'Mobil Pick-up', 'Truck'];

// ── Dummy data global ─────────────────────────────────────────────────────────
final List<KendaraanItem> dummyKendaraanList = [
  KendaraanItem(
    id: 'KND-001',
    nama: 'Honda Beat',
    platNomor: 'B 1234 XYZ',
    jenis: 'Motor',
    status: 'Tersedia',
    tanggalMaintenance: '10/03/2026',
    kapasitasBerat: 50,
    kapasitasVolume: 0.3,
    warna: 'Hitam',
    tahun: '2022',
    catatan: 'Untuk area Indramayu',
  ),
  KendaraanItem(
    id: 'KND-002',
    nama: 'Honda Vario',
    platNomor: 'B 5678 ABC',
    jenis: 'Motor',
    status: 'Sedang Digunakan',
    tanggalMaintenance: '15/03/2026',
    kapasitasBerat: 50,
    kapasitasVolume: 0.3,
    warna: 'Merah',
    tahun: '2021',
  ),
  KendaraanItem(
    id: 'KND-003',
    nama: 'Mitsubishi L300',
    platNomor: 'B 9012 DEF',
    jenis: 'Mobil Pick-up',
    status: 'Tersedia',
    tanggalMaintenance: '01/03/2026',
    kapasitasBerat: 1000,
    kapasitasVolume: 4.5,
    warna: 'Putih',
    tahun: '2019',
    catatan: 'Kapasitas 1 ton',
  ),
  KendaraanItem(
    id: 'KND-004',
    nama: 'Isuzu Elf',
    platNomor: 'B 3456 GHI',
    jenis: 'Truck',
    status: 'Servis',
    tanggalMaintenance: '25/03/2026',
    kapasitasBerat: 3000,
    kapasitasVolume: 12.0,
    warna: 'Biru',
    tahun: '2018',
    catatan: 'Sedang perbaikan rem',
  ),
  KendaraanItem(
    id: 'KND-005',
    nama: 'Toyota HiAce',
    platNomor: 'B 7890 JKL',
    jenis: 'Mobil Pick-up',
    status: 'Tersedia',
    tanggalMaintenance: '20/03/2026',
    kapasitasBerat: 800,
    kapasitasVolume: 6.0,
    warna: 'Silver',
    tahun: '2023',
  ),
];

// ── Status color helper ───────────────────────────────────────────────────────
Color kendaraanStatusColor(String s) {
  switch (s) {
    case 'Tersedia':
      return const Color(0xFF38A169);
    case 'Sedang Digunakan':
      return const Color(0xFFD69E2E);
    case 'Servis':
      return const Color(0xFFE53E3E);
    default:
      return const Color(0xFF718096);
  }
}

// ════════════════════════════════════════════════════════════════════════════
// PAGE
// ════════════════════════════════════════════════════════════════════════════
class ManajemenKendaraanPage extends StatefulWidget {
  final String userName;
  final String userRole;
  const ManajemenKendaraanPage({
    super.key,
    this.userName = 'Owner',
    this.userRole = 'Owner',
  });

  @override
  State<ManajemenKendaraanPage> createState() => _ManajemenKendaraanPageState();
}

class _ManajemenKendaraanPageState extends State<ManajemenKendaraanPage>
    with SingleTickerProviderStateMixin, SidebarMixin {
  static const blue = Color(0xFF3B6FE8);

  late List<KendaraanItem> _data;
  final _searchCtrl = TextEditingController();
  String _filterStatus = 'Semua';
  String _filterJenis = 'Semua Jenis';

  List<KendaraanItem> get _filtered {
    final q = _searchCtrl.text.trim().toLowerCase();
    return _data.where((k) {
      final match =
          q.isEmpty ||
          k.id.toLowerCase().contains(q) ||
          k.nama.toLowerCase().contains(q) ||
          k.platNomor.toLowerCase().contains(q);
      final matchSt = _filterStatus == 'Semua' || k.status == _filterStatus;
      final matchJn = _filterJenis == 'Semua Jenis' || k.jenis == _filterJenis;
      return match && matchSt && matchJn;
    }).toList();
  }

  int get _total => _data.length;
  int get _tersedia => _data.where((k) => k.status == 'Tersedia').length;
  int get _digunakan =>
      _data.where((k) => k.status == 'Sedang Digunakan').length;
  int get _servis => _data.where((k) => k.status == 'Servis').length;

  @override
  void initState() {
    super.initState();
    initSidebar(this);
    _data = dummyKendaraanList;
  }

  @override
  void dispose() {
    disposeSidebar();
    _searchCtrl.dispose();
    super.dispose();
  }

  void _handleMenuTap(String menu) {
    if (menu == 'Kendaraan') {
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

  void _resetFilter() => setState(() {
    _searchCtrl.clear();
    _filterStatus = 'Semua';
    _filterJenis = 'Semua Jenis';
  });

  // ── Navigasi ──────────────────────────────────────────────────────────────
  Future<void> _goTambah() async {
    final result = await Navigator.push<KendaraanItem>(
      context,
      MaterialPageRoute(builder: (_) => const TambahKendaraanPage()),
    );
    if (result != null) {
      setState(() => _data.insert(0, result));
      _snack(
        'Kendaraan ${result.nama} berhasil ditambahkan',
        const Color(0xFF48BB78),
      );
    }
  }

  Future<void> _goEdit(KendaraanItem k) async {
    final result = await Navigator.push<KendaraanItem>(
      context,
      MaterialPageRoute(builder: (_) => EditKendaraanPage(kendaraan: k)),
    );
    if (result != null) {
      setState(() {
        final i = _data.indexWhere((x) => x.id == result.id);
        if (i >= 0) _data[i] = result;
      });
      _snack(
        'Kendaraan ${result.nama} berhasil diperbarui',
        const Color(0xFF48BB78),
      );
    }
  }

  void _goDetail(KendaraanItem k) {
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => DetailKendaraanPage(
          kendaraan: k,
          onEdit: () => _goEdit(k),
          onStatusChange: (s) => setState(() => k.status = s),
        ),
      ),
    );
  }

  void _hapus(KendaraanItem k) {
    showDialog(
      context: context,
      builder: (_) => _ConfirmDialog(
        icon: Icons.delete_forever_rounded,
        iconColor: const Color(0xFFE53E3E),
        title: 'Hapus Kendaraan?',
        message:
            '${k.nama} (${k.platNomor}) akan dihapus.\nTindakan ini tidak dapat dibatalkan.',
        confirmLabel: 'Ya, Hapus',
        confirmColor: const Color(0xFFE53E3E),
        onConfirm: () {
          setState(() => _data.remove(k));
          _snack('Kendaraan ${k.nama} dihapus', const Color(0xFFE53E3E));
        },
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
  @override
  Widget build(BuildContext context) {
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
                _buildTable(_filtered),
                const SizedBox(height: 40),
              ],
            ),
          ),
          ...buildSidebarLayer(
            activeMenu: 'Kendaraan',
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
                  'Manajemen Kendaraan',
                  style: TextStyle(
                    color: Colors.white,
                    fontSize: 22,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: 4),
                const Text(
                  'Kelola armada kendaraan pengiriman',
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
                        label: 'Total Kendaraan',
                        value: '$_total',
                        icon: Icons.directions_car_rounded,
                        color: const Color(0xFF6B9FFF),
                      ),
                      SummaryCard(
                        label: 'Tersedia',
                        value: '$_tersedia',
                        icon: Icons.check_circle_rounded,
                        color: const Color(0xFF48BB78),
                      ),
                      SummaryCard(
                        label: 'Sedang Digunakan',
                        value: '$_digunakan',
                        icon: Icons.local_shipping_rounded,
                        color: const Color(0xFFECC94B),
                      ),
                      SummaryCard(
                        label: 'Servis/Maintenance',
                        value: '$_servis',
                        icon: Icons.build_rounded,
                        color: const Color(0xFFFC8181),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 16),
                // Tombol tambah
                Align(
                  alignment: Alignment.centerRight,
                  child: ElevatedButton.icon(
                    onPressed: _goTambah,
                    icon: const Icon(Icons.add_rounded, size: 18),
                    label: const Text(
                      'Tambah Kendaraan',
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
            'Filter Kendaraan',
            style: TextStyle(
              fontSize: 14,
              fontWeight: FontWeight.w600,
              color: Color(0xFF2D3748),
            ),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _searchCtrl,
            onChanged: (_) => setState(() {}),
            style: const TextStyle(fontSize: 13),
            decoration: InputDecoration(
              hintText: 'Cari ID, nama, atau plat nomor...',
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
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: [
              _ddWidget(
                _filterStatus,
                _statusFilterList,
                (v) => setState(() => _filterStatus = v!),
              ),
              _ddWidget(
                _filterJenis,
                _jenisFilterList,
                (v) => setState(() => _filterJenis = v!),
              ),
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

  Widget _ddWidget(
    String value,
    List<String> items,
    void Function(String?) fn,
  ) => Container(
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
  Widget _buildTable(List<KendaraanItem> list) => Padding(
    padding: const EdgeInsets.symmetric(horizontal: 16),
    child: Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            const Text(
              'Daftar Kendaraan',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
                color: Color(0xFF2D3748),
              ),
            ),
            const Spacer(),
            Text(
              '${list.length} kendaraan',
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
                          Icons.directions_car_rounded,
                          size: 40,
                          color: Colors.grey.shade300,
                        ),
                        const SizedBox(height: 10),
                        Text(
                          'Tidak ada kendaraan ditemukan',
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
                      DataColumn(label: Text('ID')),
                      DataColumn(label: Text('KENDARAAN')),
                      DataColumn(label: Text('PLAT')),
                      DataColumn(label: Text('JENIS')),
                      DataColumn(label: Text('MAINTENANCE')),
                      DataColumn(label: Text('STATUS')),
                      DataColumn(label: Text('AKSI')),
                    ],
                    rows: list
                        .map(
                          (k) => DataRow(
                            cells: [
                              DataCell(
                                Text(
                                  k.id,
                                  style: const TextStyle(
                                    fontSize: 11,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                              ),
                              DataCell(
                                SizedBox(
                                  width: 150,
                                  child: Column(
                                    crossAxisAlignment:
                                        CrossAxisAlignment.start,
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      Text(
                                        k.nama,
                                        style: const TextStyle(
                                          fontSize: 13,
                                          fontWeight: FontWeight.w600,
                                        ),
                                      ),
                                      if (k.warna.isNotEmpty ||
                                          k.tahun.isNotEmpty) ...[
                                        const SizedBox(height: 2),
                                        Text(
                                          [k.warna, k.tahun]
                                              .where((s) => s.isNotEmpty)
                                              .join(' • '),
                                          style: TextStyle(
                                            fontSize: 10,
                                            color: Colors.grey.shade500,
                                          ),
                                        ),
                                      ],
                                    ],
                                  ),
                                ),
                              ),
                              DataCell(
                                Container(
                                  padding: const EdgeInsets.symmetric(
                                    horizontal: 8,
                                    vertical: 5,
                                  ),
                                  decoration: BoxDecoration(
                                    color: Colors.grey.shade100,
                                    borderRadius: BorderRadius.circular(6),
                                    border: Border.all(
                                      color: Colors.grey.shade300,
                                    ),
                                  ),
                                  child: Text(
                                    k.platNomor,
                                    style: const TextStyle(
                                      fontSize: 11,
                                      fontWeight: FontWeight.w700,
                                      letterSpacing: 0.5,
                                    ),
                                  ),
                                ),
                              ),
                              DataCell(_jenisBadge(k.jenis)),
                              DataCell(
                                Text(
                                  k.tanggalMaintenance,
                                  style: const TextStyle(fontSize: 12),
                                ),
                              ),
                              DataCell(_statusBadge(k.status)),
                              // ── Aksi — 3 button horizontal sejajar ──────────────────
                              DataCell(
                                Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    _AksiBtn(
                                      icon: Icons.visibility_rounded,
                                      color: const Color(0xFF4169E1),
                                      label: 'Detail',
                                      onTap: () => _goDetail(k),
                                    ),
                                    const SizedBox(width: 8),
                                    _AksiBtn(
                                      icon: Icons.edit_rounded,
                                      color: const Color(0xFF48BB78),
                                      label: 'Edit',
                                      onTap: () => _goEdit(k),
                                    ),
                                    const SizedBox(width: 8),
                                    _AksiBtn(
                                      icon: Icons.delete_rounded,
                                      color: const Color(0xFFE53E3E),
                                      label: 'Hapus',
                                      onTap: () => _hapus(k),
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

  Widget _statusBadge(String s) {
    final c = kendaraanStatusColor(s);
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
      decoration: BoxDecoration(
        color: c.withOpacity(0.12),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Text(
        s,
        style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: c),
      ),
    );
  }

  Widget _jenisBadge(String jenis) {
    const cm = {
      'Motor': Color(0xFF3B6FE8),
      'Mobil Pick-up': Color(0xFF6B5CE7),
      'Truck': Color(0xFFED8936),
    };
    final c = cm[jenis] ?? const Color(0xFF4A5568);
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
      decoration: BoxDecoration(
        color: c.withOpacity(0.10),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Text(
        jenis,
        style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: c),
      ),
    );
  }
}

// ════════════════════════════════════════════════════════════════════════════
// SHARED WIDGETS (dipakai di halaman ini)
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
          padding: const EdgeInsets.all(9),
          decoration: BoxDecoration(
            color: color.withOpacity(0.13),
            borderRadius: BorderRadius.circular(9),
          ),
          child: Icon(icon, color: color, size: 17),
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

class _ConfirmDialog extends StatelessWidget {
  final IconData icon;
  final Color iconColor;
  final String title, message, confirmLabel;
  final Color confirmColor;
  final VoidCallback onConfirm;
  const _ConfirmDialog({
    required this.icon,
    required this.iconColor,
    required this.title,
    required this.message,
    required this.confirmLabel,
    required this.confirmColor,
    required this.onConfirm,
  });
  @override
  Widget build(BuildContext context) => AlertDialog(
    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
    contentPadding: const EdgeInsets.fromLTRB(24, 20, 24, 0),
    actionsPadding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
    content: Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        Container(
          width: 56,
          height: 56,
          decoration: BoxDecoration(
            color: iconColor.withOpacity(0.08),
            shape: BoxShape.circle,
          ),
          child: Icon(icon, color: iconColor, size: 26),
        ),
        const SizedBox(height: 12),
        Text(
          title,
          style: const TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.bold,
            color: Color(0xFF2D3748),
          ),
        ),
        const SizedBox(height: 8),
        Text(
          message,
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
      Row(
        children: [
          Expanded(
            child: OutlinedButton(
              onPressed: () => Navigator.pop(context),
              style: OutlinedButton.styleFrom(
                side: BorderSide(color: Colors.grey.shade300),
                padding: const EdgeInsets.symmetric(vertical: 11),
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
          const SizedBox(width: 10),
          Expanded(
            child: ElevatedButton(
              onPressed: () {
                Navigator.pop(context);
                onConfirm();
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: confirmColor,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(vertical: 11),
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
      ),
    ],
  );
}
