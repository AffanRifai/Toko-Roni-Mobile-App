// lib/user/manajemen_pengguna_page.dart
import 'package:flutter/material.dart';
import 'package:tokoronifrontend/features/delivery/manajemen_pengiriman_page.dart';
import 'package:tokoronifrontend/features/profile/profile_page.dart';
import 'package:tokoronifrontend/features/report/laporan_penjualan_page.dart';
import 'package:tokoronifrontend/features/transaction/kasir_page.dart';
import 'package:tokoronifrontend/features/transaction/riwayat_transaksi_page.dart';
import 'package:tokoronifrontend/features/user/registrasi_wajah_page.dart';
import 'package:tokoronifrontend/features/vehicle/manajemen_kendaraan_page.dart';
import '../../core/services/user_service.dart';
import '../../shared/widgets/shared_widgets.dart';
import '../../shared/widgets/notifikasi_widget.dart';
import '../../shared/widgets/profile_widget.dart';
import '../../shared/widgets/semua_notifikasi_page.dart';
import '../../models/pengguna_model.dart';
import 'tambah_pengguna_page.dart';
import 'edit_pengguna_page.dart'; // SidebarMixin
import '../home/dashboard_router.dart';
import '../product/daftar_produk_page.dart'; // DaftarProdukPage
import '../category/manajemen_kategori_page.dart'; // ManajemenKategoriPage
import '../member/daftar_member_page.dart';

class ManajemenPenggunaPage extends StatefulWidget {
  const ManajemenPenggunaPage({super.key});

  @override
  State<ManajemenPenggunaPage> createState() => _ManajemenPenggunaPageState();
}

class _ManajemenPenggunaPageState extends State<ManajemenPenggunaPage>
    with SingleTickerProviderStateMixin, SidebarMixin {
  late List<PenggunaData> _list;
  bool _isLoading = true;
  bool _hasError = false;
  String _errorMessage = '';
  final _searchCtrl = TextEditingController();
  String _filterStatus = 'Semua status';
  String _filterRole = 'Semua role';

  List<PenggunaData> get _filtered => _list.where((p) {
    final q = _searchCtrl.text.trim().toLowerCase();
    final matchSearch =
        q.isEmpty ||
        p.nama.toLowerCase().contains(q) ||
        p.email.toLowerCase().contains(q) ||
        p.role.toLowerCase().contains(q);
    final matchStatus =
        _filterStatus == 'Semua status' ||
        (_filterStatus == 'Aktif' && p.aktif) ||
        (_filterStatus == 'Nonaktif' && !p.aktif);
    final matchRole = _filterRole == 'Semua role' || p.role == _filterRole;
    return matchSearch && matchStatus && matchRole;
  }).toList();

  @override
  void initState() {
    super.initState();
    initSidebar(this);
    _list = [];
    _loadAllData();
  }

  @override
  void dispose() {
    disposeSidebar();
    _searchCtrl.dispose();
    super.dispose();
  }

  // ── Navigasi sidebar ──────────────────────────────────────
  void _handleMenuTap(String menu) {
    if (menu == 'Pengguna') {
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

  void _resetFilter() => setState(() {
    _searchCtrl.clear();
    _filterStatus = 'Semua status';
    _filterRole = 'Semua role';
  });

  Future<void> _loadAllData() async {
    if (!mounted) return;
    setState(() {
      _isLoading = true;
      _hasError = false;
      _errorMessage = '';
    });

    try {
      final result = await UserService.getUsers();
      if (!mounted) return;
      setState(() {
        _list = result
            .map(
              (e) => PenggunaData(
                id: e.id,
                kode: e.kode,
                nama: e.nama,
                email: e.email,
                role: roleLabelFromApi(e.role),
                jenisToko: jenisTokoLabelFromApi(e.jenisToko),
                aktif: e.aktif,
                bergabung: formatTanggalGabung(e.bergabungRaw),
                telepon: e.telepon,
                alamat: e.alamat,
              ),
            )
            .toList();
        _isLoading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _isLoading = false;
        _hasError = true;
        _errorMessage = e.toString().replaceFirst('Exception: ', '');
      });
    }
  }

  Future<void> _deletePengguna(PenggunaData p) async {
    try {
      await UserService.deleteUser(userId: p.id);
      if (!mounted) return;
      setState(() => _list.removeWhere((e) => e.id == p.id));
      _showSnack('Pengguna "${p.nama}" berhasil dihapus', Colors.red);
    } catch (e) {
      if (!mounted) return;
      _showSnack(
        e.toString().replaceFirst('Exception: ', ''),
        const Color(0xFFE53E3E),
      );
    }
  }

  // ── Dialog hapus ──────────────────────────────────────────────────────────
  void _showHapusDialog(PenggunaData p) {
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
              'Hapus Pengguna',
              style: TextStyle(
                fontSize: 17,
                fontWeight: FontWeight.bold,
                color: Color(0xFF2D3748),
              ),
            ),
            const SizedBox(height: 10),
            Text(
              'Apakah kamu yakin ingin menghapus akun "${p.nama}"?\nTindakan ini tidak dapat dibatalkan.',
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
          _dialogRow(
            onBatal: () => Navigator.pop(context),
            onConfirm: () async {
              Navigator.pop(context);
              await _deletePengguna(p);
            },
            confirmLabel: 'Ya, Hapus',
            confirmColor: const Color(0xFFE53E3E),
          ),
        ],
      ),
    );
  }

  void _showSnack(String msg, Color color) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(msg),
        backgroundColor: color,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
      ),
    );
  }

  Widget _buildSyncWarning() => Padding(
    padding: const EdgeInsets.symmetric(horizontal: 16),
    child: Container(
      width: double.infinity,
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
      decoration: BoxDecoration(
        color: const Color(0xFFFFF7E6),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: const Color(0xFFFBD38D)),
      ),
      child: Row(
        children: [
          const Icon(
            Icons.wifi_off_rounded,
            color: Color(0xFFB7791F),
            size: 18,
          ),
          const SizedBox(width: 8),
          Expanded(
            child: Text(
              _errorMessage.isEmpty
                  ? 'Sebagian data mungkin belum sinkron. Tarik ke bawah untuk coba lagi.'
                  : _errorMessage,
              style: const TextStyle(fontSize: 12, color: Color(0xFF744210)),
            ),
          ),
        ],
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
          RefreshIndicator(
            onRefresh: _loadAllData,
            child: SingleChildScrollView(
              physics: const AlwaysScrollableScrollPhysics(),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _buildHeader(),
                  const SizedBox(height: 20),
                  _buildFilter(),
                  const SizedBox(height: 16),
                  if (_hasError && _list.isNotEmpty) _buildSyncWarning(),
                  _buildTable(filtered),
                  const SizedBox(height: 40),
                ],
              ),
            ),
          ),
          ...buildSidebarLayer(
            activeMenu: 'Pengguna',
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
                  Row(
                    children: [
                      BurgerMenuButton(onTap: openSidebar),
                      const Spacer(),

                      // Notifikasi — load otomatis dari database via NotifikasiService
                      NotifikasiBell(
                        onLihatSemua: () => Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (_) => const SemuaNotifikasiPage(),
                          ),
                        ),
                      ),
                      const SizedBox(width: 12),

                      // ProfileWidget.fromAuth() load nama, role, foto
                      // langsung dari SharedPreferences hasil login (sesuai DB)
                      ProfileWidget.fromAuth(
                        onTap: () {
                          // Navigator.push(context, MaterialPageRoute(
                          //     builder: (_) => const ProfilePage()));
                        },
                      ),
                    ],
                  ),
                  const SizedBox(height: 24),
                  const Text(
                    'Manajemen Pengguna',
                    style: TextStyle(
                      color: Colors.white,
                      fontSize: 22,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 6),
                  const Text(
                    'Kelola pengguna dan akses sistem',
                    style: TextStyle(color: Colors.white70, fontSize: 13),
                  ),
                  const SizedBox(height: 16),
                  Align(
                    alignment: Alignment.centerRight,
                    child: ElevatedButton.icon(
                      onPressed: () async {
                        final result = await Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (_) => const TambahPenggunaPage(),
                          ),
                        );
                        if (!mounted) return;
                        if (result is String && result.isNotEmpty) {
                          _showSnack(result, const Color(0xFF48BB78));
                        }
                        await _loadAllData();
                      },
                      icon: const Icon(Icons.add_rounded, size: 18),
                      label: const Text(
                        'Tambah pengguna',
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
              'Filter Data',
              style: TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.w600,
                color: Color(0xFF2D3748),
              ),
            ),
            const SizedBox(height: 12),
            // Search + Cari
            Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _searchCtrl,
                    onChanged: (_) => setState(() {}),
                    style: const TextStyle(fontSize: 13),
                    decoration: InputDecoration(
                      hintText: 'Cari nama, email, role',
                      hintStyle: TextStyle(
                        color: Colors.grey.shade400,
                        fontSize: 13,
                      ),
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
                ),
                const SizedBox(width: 8),
                ElevatedButton(
                  onPressed: () => setState(() {}),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF4169E1),
                    padding: const EdgeInsets.symmetric(
                      horizontal: 18,
                      vertical: 13,
                    ),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(24),
                    ),
                    elevation: 0,
                  ),
                  child: const Text(
                    'Cari',
                    style: TextStyle(color: Colors.white, fontSize: 13),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 10),
            // Dropdowns + reset
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: [
                _dd(_filterStatus, const [
                  'Semua status',
                  'Aktif',
                  'Nonaktif',
                ], (v) => setState(() => _filterStatus = v!)),
                _dd(_filterRole, [
                  'Semua role',
                  ...roleList,
                ], (v) => setState(() => _filterRole = v!)),
                SizedBox(
                  height: 44,
                  child: OutlinedButton.icon(
                    onPressed: _resetFilter,
                    icon: const Icon(Icons.refresh_rounded, size: 16),
                    label: const Text(
                      'Reset filter',
                      style: TextStyle(fontSize: 13),
                    ),
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

  // ── TABEL ─────────────────────────────────────────────────────────────────
  Widget _buildTable(List<PenggunaData> filtered) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Container(
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
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 16, 16, 4),
              child: Row(
                children: [
                  const Text(
                    'Daftar Pengguna',
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                      color: Color(0xFF2D3748),
                    ),
                  ),
                  const Spacer(),
                  Text(
                    'Menampilkan ${filtered.length} dari ${_list.length}',
                    style: TextStyle(fontSize: 11, color: Colors.grey.shade500),
                  ),
                ],
              ),
            ),
            const Divider(height: 16),

            if (_isLoading)
              const Padding(
                padding: EdgeInsets.symmetric(vertical: 36),
                child: Center(
                  child: CircularProgressIndicator(color: Color(0xFF4169E1)),
                ),
              )
            else if (_hasError && _list.isEmpty)
              Padding(
                padding: const EdgeInsets.symmetric(
                  vertical: 32,
                  horizontal: 16,
                ),
                child: Center(
                  child: Column(
                    children: [
                      Icon(
                        Icons.cloud_off_rounded,
                        size: 40,
                        color: Colors.grey.shade400,
                      ),
                      const SizedBox(height: 10),
                      const Text(
                        'Gagal memuat data pengguna',
                        style: TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.w600,
                          color: Color(0xFF2D3748),
                        ),
                      ),
                      const SizedBox(height: 6),
                      Text(
                        _errorMessage.isEmpty
                            ? 'Periksa koneksi ke server Laravel'
                            : _errorMessage,
                        textAlign: TextAlign.center,
                        style: TextStyle(
                          fontSize: 12,
                          color: Colors.grey.shade600,
                        ),
                      ),
                      const SizedBox(height: 12),
                      ElevatedButton.icon(
                        onPressed: _loadAllData,
                        icon: const Icon(Icons.refresh_rounded, size: 16),
                        label: const Text('Coba lagi'),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF4169E1),
                          foregroundColor: Colors.white,
                          elevation: 0,
                        ),
                      ),
                    ],
                  ),
                ),
              )
            else if (filtered.isEmpty)
              Padding(
                padding: const EdgeInsets.symmetric(vertical: 36),
                child: Center(
                  child: Column(
                    children: [
                      Icon(
                        Icons.search_off_rounded,
                        size: 40,
                        color: Colors.grey.shade300,
                      ),
                      const SizedBox(height: 10),
                      Text(
                        'Pengguna tidak ditemukan',
                        style: TextStyle(
                          fontSize: 14,
                          color: Colors.grey.shade400,
                        ),
                      ),
                    ],
                  ),
                ),
              )
            else
              SingleChildScrollView(
                scrollDirection: Axis.horizontal,
                child: DataTable(
                  headingRowColor: WidgetStateProperty.all(
                    const Color(0xFFF7F8FA),
                  ),
                  headingRowHeight: 42,
                  dataRowMinHeight: 60,
                  dataRowMaxHeight: 72,
                  columnSpacing: 14,
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
                    DataColumn(label: Text('KODE')),
                    DataColumn(label: Text('ROLE')),
                    DataColumn(label: Text('JENIS TOKO')),
                    DataColumn(label: Text('STATUS')),
                    DataColumn(label: Text('BERGABUNG')),
                    DataColumn(label: Text('AKSI')),
                  ],
                  rows: filtered
                      .map(
                        (p) => DataRow(
                          cells: [
                            // Nama + email sebagai kode kolom
                            DataCell(
                              Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Text(
                                    p.kode,
                                    style: const TextStyle(
                                      fontSize: 12,
                                      fontWeight: FontWeight.w600,
                                    ),
                                  ),
                                  Text(
                                    p.nama,
                                    style: TextStyle(
                                      fontSize: 10,
                                      color: Colors.grey.shade700,
                                    ),
                                  ),
                                  Text(
                                    p.email,
                                    style: TextStyle(
                                      fontSize: 10,
                                      color: Colors.grey.shade500,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                            DataCell(Text(p.role)),
                            DataCell(Text(p.jenisToko)),
                            // Status — style sama dengan daftar_produk
                            DataCell(
                              Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 10,
                                  vertical: 4,
                                ),
                                decoration: BoxDecoration(
                                  color: p.aktif
                                      ? const Color(
                                          0xFF48BB78,
                                        ).withOpacity(0.12)
                                      : const Color(
                                          0xFFE53E3E,
                                        ).withOpacity(0.10),
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                child: Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    Icon(
                                      p.aktif
                                          ? Icons.check_circle_rounded
                                          : Icons.cancel_rounded,
                                      size: 12,
                                      color: p.aktif
                                          ? const Color(0xFF48BB78)
                                          : const Color(0xFFE53E3E),
                                    ),
                                    const SizedBox(width: 4),
                                    Text(
                                      p.aktif ? 'Aktif' : 'Nonaktif',
                                      style: TextStyle(
                                        fontSize: 11,
                                        fontWeight: FontWeight.w600,
                                        color: p.aktif
                                            ? const Color(0xFF48BB78)
                                            : const Color(0xFFE53E3E),
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ),
                            DataCell(
                              Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Text(
                                    p.bergabung,
                                    style: const TextStyle(fontSize: 11),
                                  ),
                                  Text(
                                    '1 week ago',
                                    style: TextStyle(
                                      fontSize: 10,
                                      color: Colors.grey.shade400,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                            // Aksi — style sama dengan daftar_produk (icon + label bawah)
                            DataCell(
                              Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  _AksiBtn(
                                    icon: Icons.edit_rounded,
                                    color: const Color(0xFFD69E2E),
                                    label: 'Edit',
                                    onTap: () async {
                                      final result = await Navigator.push(
                                        context,
                                        MaterialPageRoute(
                                          builder: (_) =>
                                              EditPenggunaPage(pengguna: p),
                                        ),
                                      );
                                      if (!mounted) return;
                                      if (result is String &&
                                          result.isNotEmpty) {
                                        _showSnack(
                                          result,
                                          const Color(0xFF48BB78),
                                        );
                                      }
                                      await _loadAllData();
                                    },
                                  ),
                                  const SizedBox(width: 8),
                                  _AksiBtn(
                                    icon: Icons.face_rounded,
                                    color: const Color(0xFF4169E1),
                                    label: 'Daftar wajah',
                                    onTap: () async {
                                      final result = await Navigator.push(
                                        context,
                                        MaterialPageRoute(
                                          builder: (_) => RegistrasiWajahPage(
                                            namaUser: p.nama,
                                            userId: p.id,
                                          ),
                                        ),
                                      );
                                      if (!mounted) return;
                                      if (result == true) {
                                        _showSnack(
                                          'Wajah untuk ${p.nama} berhasil didaftarkan',
                                          const Color(0xFF48BB78),
                                        );
                                      }
                                    },
                                  ),
                                  const SizedBox(width: 8),
                                  _AksiBtn(
                                    icon: Icons.delete_rounded,
                                    color: const Color(0xFFE53E3E),
                                    label: 'Hapus',
                                    onTap: () => _showHapusDialog(p),
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
            const SizedBox(height: 8),
          ],
        ),
      ),
    );
  }
}

// ════════════════════════════════════════════════════════════════════════════
// SHARED WIDGETS
// ════════════════════════════════════════════════════════════════════════════

// Aksi button — icon di atas, label di bawah (persis daftar_produk_page)
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
          child: Icon(icon, color: color, size: 17),
        ),
        const SizedBox(height: 3),
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

// Tombol row di dalam dialog
Widget _dialogRow({
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
