// lib/category/manajemen_kategori_page.dart
import 'package:flutter/material.dart';
import 'package:tokoronifrontend/features/delivery/manajemen_pengiriman_page.dart';
import 'package:tokoronifrontend/features/profile/profile_page.dart';
import 'package:tokoronifrontend/features/report/laporan_penjualan_page.dart';
import 'package:tokoronifrontend/features/transaction/kasir_page.dart';
import 'package:tokoronifrontend/features/transaction/riwayat_transaksi_page.dart';
import 'package:tokoronifrontend/core/services/category_service.dart';
import 'package:tokoronifrontend/features/vehicle/manajemen_kendaraan_page.dart';
import '../user/manajemen_pengguna_page.dart';
import '../../shared/widgets/shared_widgets.dart';
import '../../shared/widgets/notifikasi_widget.dart';
import '../../shared/widgets/profile_widget.dart';
import '../../shared/widgets/semua_notifikasi_page.dart';
import 'tambah_kategori_page.dart';
import 'edit_kategori_page.dart';
import '../home/dashboard_router.dart';
import '../product/daftar_produk_page.dart'; // DaftarProdukPage
import '../member/daftar_member_page.dart';

// ════════════════════════════════════════════════════════════════════════════
// MODEL
// ════════════════════════════════════════════════════════════════════════════
class KategoriData {
  final int id;
  String nama;
  String slug;
  String deskripsi;
  bool aktif;
  int totalProduk;
  String terakhirDiperbarui;

  KategoriData({
    required this.id,
    required this.nama,
    required this.slug,
    required this.deskripsi,
    required this.aktif,
    required this.totalProduk,
    required this.terakhirDiperbarui,
  });
}

// ════════════════════════════════════════════════════════════════════════════
// PAGE
// ════════════════════════════════════════════════════════════════════════════
class ManajemenKategoriPage extends StatefulWidget {
  const ManajemenKategoriPage({super.key});

  @override
  State<ManajemenKategoriPage> createState() => _ManajemenKategoriPageState();
}

class _ManajemenKategoriPageState extends State<ManajemenKategoriPage>
    with SingleTickerProviderStateMixin, SidebarMixin {
  late List<KategoriData> _kategoriList;
  bool _isLoading = true;
  bool _hasError = false;
  String _errorMessage = '';
  final _searchCtrl = TextEditingController();

  List<KategoriData> get _filtered {
    final q = _searchCtrl.text.trim().toLowerCase();
    if (q.isEmpty) return _kategoriList;
    return _kategoriList
        .where(
          (k) =>
              k.nama.toLowerCase().contains(q) ||
              k.slug.toLowerCase().contains(q),
        )
        .toList();
  }

  @override
  void initState() {
    super.initState();
    initSidebar(this);
    _kategoriList = [];
    _loadAllData();
  }

  @override
  void dispose() {
    disposeSidebar();
    _searchCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadAllData() async {
    if (!mounted) return;
    setState(() {
      _isLoading = true;
      _hasError = false;
      _errorMessage = '';
    });

    try {
      final result = await CategoryService.getCategories();
      if (!mounted) return;
      setState(() {
        _kategoriList = result
            .map(
              (e) => KategoriData(
                id: e.id,
                nama: e.nama,
                slug: e.slug,
                deskripsi: e.deskripsi,
                aktif: e.aktif,
                totalProduk: e.totalProduk,
                terakhirDiperbarui: e.terakhirDiperbarui,
              ),
            )
            .toList();
        _isLoading = false;
      });
    } catch (e) {
      final msg = e.toString().replaceFirst('Exception: ', '').trim();
      if (!mounted) return;
      setState(() {
        _isLoading = false;
        _hasError = true;
        _errorMessage = msg.isEmpty
            ? 'Gagal memuat kategori. Periksa koneksi internet atau server.'
            : msg;
      });
    }
  }

  Future<void> _deleteKategori(KategoriData k) async {
    try {
      await CategoryService.deleteCategory(categoryId: k.id);
      if (!mounted) return;
      setState(() => _kategoriList.removeWhere((e) => e.id == k.id));
      _showSnack('Kategori "${k.nama}" berhasil dihapus', Colors.red);
    } catch (e) {
      if (!mounted) return;
      _showSnack(
        e.toString().replaceFirst('Exception: ', ''),
        const Color(0xFFE53E3E),
      );
    }
  }

  // ── Navigasi sidebar ──────────────────────────────────────
  void _handleMenuTap(String menu) {
    if (menu == 'Kategori') {
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

  void _showHapusDialog(KategoriData k) {
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Text(
          'Hapus Kategori',
          style: TextStyle(fontWeight: FontWeight.bold),
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text('Apakah kamu yakin ingin menghapus kategori "${k.nama}"?'),
            if (k.totalProduk > 0) ...[
              const SizedBox(height: 10),
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: Colors.orange.shade50,
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: Colors.orange.shade200),
                ),
                child: Row(
                  children: [
                    Icon(
                      Icons.warning_rounded,
                      color: Colors.orange.shade600,
                      size: 16,
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        'Kategori ini memiliki ${k.totalProduk} produk. Produk tidak akan terhapus.',
                        style: TextStyle(
                          fontSize: 12,
                          color: Colors.orange.shade700,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Batal', style: TextStyle(color: Colors.grey)),
          ),
          ElevatedButton(
            onPressed: () async {
              Navigator.pop(context);
              await _deleteKategori(k);
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFFE53E3E),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(10),
              ),
            ),
            child: const Text('Hapus', style: TextStyle(color: Colors.white)),
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

  @override
  Widget build(BuildContext context) {
    final filtered = _filtered;
    return Scaffold(
      backgroundColor: const Color(0xFFF3F4F8),
      body: Stack(
        children: [
          RefreshIndicator(
            onRefresh: _loadAllData,
            child: _isLoading
                ? _buildLoadingState()
                : (_hasError && _kategoriList.isEmpty)
                ? _buildErrorState()
                : _buildPageContent(filtered),
          ),
          ...buildSidebarLayer(
            activeMenu: 'Kategori',
            onMenuTap: _handleMenuTap,
          ),
        ],
      ),
    );
  }

  Widget _buildPageContent(List<KategoriData> filtered) => SingleChildScrollView(
    physics: const AlwaysScrollableScrollPhysics(),
    child: Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        _buildHeader(),
        const SizedBox(height: 24),
        if (_hasError) _buildSyncWarning(),
        _buildTable(filtered),
        const SizedBox(height: 40),
      ],
    ),
  );

  Widget _buildLoadingState() => SingleChildScrollView(
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
            SizedBox(height: 12),
            Text(
              'Memuat data kategori...',
              style: TextStyle(color: Color(0xFF4A5568)),
            ),
          ],
        ),
      ),
    ),
  );

  Widget _buildErrorState() => SingleChildScrollView(
    physics: const AlwaysScrollableScrollPhysics(),
    child: SizedBox(
      height:
          MediaQuery.of(context).size.height -
          MediaQuery.of(context).padding.top -
          MediaQuery.of(context).padding.bottom,
      child: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.cloud_off_rounded, size: 56, color: Colors.grey[400]),
            const SizedBox(height: 12),
            const Text(
              'Gagal memuat data kategori',
              style: TextStyle(
                fontSize: 15,
                fontWeight: FontWeight.w600,
                color: Color(0xFF2D3748),
              ),
            ),
            const SizedBox(height: 6),
            Text(
              _errorMessage.isEmpty
                  ? 'Periksa koneksi internet atau server Laravel'
                  : _errorMessage,
              textAlign: TextAlign.center,
              style: TextStyle(fontSize: 12, color: Colors.grey[600]),
            ),
            const SizedBox(height: 18),
            ElevatedButton.icon(
              onPressed: _loadAllData,
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
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
            ),
          ],
        ),
      ),
    ),
  );

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

                  // Title
                  const Text(
                    'Manajemen Kategori',
                    style: TextStyle(
                      color: Colors.white,
                      fontSize: 22,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 6),
                  const Text(
                    'Kelola kategori produk untuk mengorganisir\ninventaris anda',
                    style: TextStyle(
                      color: Colors.white70,
                      fontSize: 13,
                      height: 1.4,
                    ),
                  ),
                  const SizedBox(height: 16),

                  // Tambah Kategori button — di bawah teks, sebelah kanan
                  Align(
                    alignment: Alignment.centerRight,
                    child: ElevatedButton.icon(
                      onPressed: () async {
                        final result = await Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (_) => const TambahKategoriPage(),
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
                        'Tambah Kategori',
                        style: TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.white,
                        foregroundColor: const Color(0xFF2B55D0),
                        padding: const EdgeInsets.symmetric(
                          horizontal: 14,
                          vertical: 10,
                        ),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
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

  // ── TABLE ─────────────────────────────────────────────────────────────────
  Widget _buildTable(List<KategoriData> filtered) {
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
            // Table header — title + search di bawahnya supaya tidak overflow
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 16, 16, 12),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Judul
                  const Text(
                    'Daftar Kategori',
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                      color: Color(0xFF2D3748),
                    ),
                  ),
                  const SizedBox(height: 3),
                  Text(
                    'Total ${_kategoriList.length} kategori ditemukan',
                    style: TextStyle(fontSize: 12, color: Colors.grey.shade500),
                  ),
                  const SizedBox(height: 12),
                  // Search bar full width + tombol Cari
                  Row(
                    children: [
                      Expanded(
                        child: TextField(
                          controller: _searchCtrl,
                          onChanged: (_) => setState(() {}),
                          style: const TextStyle(fontSize: 12),
                          decoration: InputDecoration(
                            hintText: 'Cari kategori',
                            hintStyle: TextStyle(
                              color: Colors.grey.shade400,
                              fontSize: 12,
                            ),
                            prefixIcon: Icon(
                              Icons.search_rounded,
                              size: 16,
                              color: Colors.grey.shade400,
                            ),
                            filled: true,
                            fillColor: const Color(0xFFF5F7FA),
                            contentPadding: const EdgeInsets.symmetric(
                              vertical: 8,
                            ),
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(20),
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
                            horizontal: 16,
                            vertical: 11,
                          ),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(20),
                          ),
                          elevation: 0,
                        ),
                        child: const Text(
                          'Cari',
                          style: TextStyle(color: Colors.white, fontSize: 12),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),

            const Divider(height: 1),

            // Table
            SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: DataTable(
                headingRowColor: WidgetStateProperty.all(
                  const Color(0xFFF7F8FA),
                ),
                headingRowHeight: 42,
                dataRowMinHeight: 52,
                dataRowMaxHeight: 64,
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
                  DataColumn(label: Text('No')),
                  DataColumn(label: Text('Nama Kategori')),
                  DataColumn(label: Text('Slug')),
                  DataColumn(label: Text('Status')),
                  DataColumn(label: Text('Aksi')),
                ],
                rows: List.generate(filtered.length, (i) {
                  final k = filtered[i];
                  return DataRow(
                    cells: [
                      DataCell(Text('${i + 1}')),
                      DataCell(
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Text(
                              k.nama,
                              style: const TextStyle(
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                            Text(
                              'ID: ${k.id}',
                              style: TextStyle(
                                fontSize: 10,
                                color: Colors.grey.shade400,
                              ),
                            ),
                          ],
                        ),
                      ),
                      DataCell(
                        Text(
                          '#${k.slug}',
                          style: const TextStyle(
                            color: Color(0xFF4169E1),
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                      ),
                      DataCell(
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 10,
                            vertical: 4,
                          ),
                          decoration: BoxDecoration(
                            color: k.aktif
                                ? const Color(0xFF48BB78).withOpacity(0.12)
                                : Colors.grey.shade100,
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: Text(
                            k.aktif ? 'Aktif' : 'Nonaktif',
                            style: TextStyle(
                              color: k.aktif
                                  ? const Color(0xFF48BB78)
                                  : Colors.grey,
                              fontSize: 11,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                        ),
                      ),
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
                                        EditKategoriPage(kategori: k),
                                  ),
                                );
                                if (!mounted) return;
                                if (result is String && result.isNotEmpty) {
                                  final isDelete = result.toLowerCase().contains(
                                    'hapus',
                                  );
                                  _showSnack(
                                    result,
                                    isDelete
                                        ? const Color(0xFFE53E3E)
                                        : const Color(0xFF48BB78),
                                  );
                                }
                                await _loadAllData();
                              },
                            ),
                            const SizedBox(width: 8),
                            _AksiBtn(
                              icon: Icons.delete_rounded,
                              color: const Color(0xFFE53E3E),
                              label: 'Hapus',
                              onTap: () => _showHapusDialog(k),
                            ),
                          ],
                        ),
                      ),
                    ],
                  );
                }),
              ),
            ),

            if (filtered.isEmpty)
              Padding(
                padding: const EdgeInsets.symmetric(vertical: 32),
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
                        'Kategori tidak ditemukan',
                        style: TextStyle(
                          fontSize: 14,
                          color: Colors.grey.shade400,
                        ),
                      ),
                    ],
                  ),
                ),
              ),

            const SizedBox(height: 8),
          ],
        ),
      ),
    );
  }
}

// ── Aksi button — sama persis dengan style di daftar_produk_page.dart ─────────
// icon di container (background transparan) + label teks di bawah
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
            fontSize: 10,
            color: color,
            fontWeight: FontWeight.w500,
          ),
        ),
      ],
    ),
  );
}
