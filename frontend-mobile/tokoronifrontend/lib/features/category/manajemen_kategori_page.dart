// lib/category/manajemen_kategori_page.dart
import 'dart:async';

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
  Timer? _searchDebounce;
  String _searchQuery = '';
  int _dataRevision = 0;
  int _filteredCacheRevision = -1;
  String _filteredCacheQuery = '';
  List<KategoriData> _filteredCache = const [];
  static const int _initialRenderedRows = 24;
  static const int _renderedRowsStep = 24;
  int _maxRenderedRows = _initialRenderedRows;

  List<KategoriData> get _filtered {
    final canUseCache =
        _filteredCacheRevision == _dataRevision &&
        _filteredCacheQuery == _searchQuery;
    if (canUseCache) return _filteredCache;

    final q = _searchQuery;
    if (q.isEmpty) {
      _filteredCache = List<KategoriData>.unmodifiable(_kategoriList);
    } else {
      _filteredCache = _kategoriList
          .where(
            (k) =>
                k.nama.toLowerCase().contains(q) ||
                k.slug.toLowerCase().contains(q),
          )
          .toList(growable: false);
    }
    _filteredCacheRevision = _dataRevision;
    _filteredCacheQuery = _searchQuery;
    return _filteredCache;
  }

  @override
  void initState() {
    super.initState();
    _searchQuery = _searchCtrl.text.trim().toLowerCase();
    initSidebar(this);
    _kategoriList = [];
    _loadAllData();
  }

  @override
  void dispose() {
    _searchDebounce?.cancel();
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
        _bumpDataRevision();
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
      setState(() {
        _kategoriList.removeWhere((e) => e.id == k.id);
        _bumpDataRevision();
      });
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
      FocusManager.instance.primaryFocus?.unfocus();
      closeSidebarThenNavigate(() {
        _releaseHeavyContentForBackground();
        Navigator.push(context, MaterialPageRoute(builder: (_) => page!)).then((
          _,
        ) {
          if (!mounted || _kategoriList.isNotEmpty) return;
          _loadAllData();
        });
      });
    }
  }

  void _releaseHeavyContentForBackground() {
    if (_kategoriList.isEmpty && _filteredCache.isEmpty) return;
    _searchDebounce?.cancel();
    setState(() {
      _kategoriList = [];
      _filteredCache = const [];
      _filteredCacheRevision = -1;
      _filteredCacheQuery = '';
      _isLoading = true;
      _hasError = false;
      _errorMessage = '';
      _maxRenderedRows = _initialRenderedRows;
    });
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

  void _onSearchChanged(String value) {
    _searchDebounce?.cancel();
    final normalized = value.trim().toLowerCase();
    _searchDebounce = Timer(const Duration(milliseconds: 120), () {
      if (!mounted || normalized == _searchQuery) return;
      setState(() {
        _searchQuery = normalized;
        _invalidateFilteredCache();
        _maxRenderedRows = _initialRenderedRows;
      });
    });
  }

  void _bumpDataRevision() {
    _dataRevision++;
    _invalidateFilteredCache();
    _maxRenderedRows = _initialRenderedRows;
  }

  void _invalidateFilteredCache() {
    _filteredCacheRevision = -1;
  }

  Future<void> _openEditKategori(KategoriData k) async {
    FocusManager.instance.primaryFocus?.unfocus();
    final result = await Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => EditKategoriPage(kategori: k)),
    );
    if (!mounted) return;
    if (result is String && result.isNotEmpty) {
      final isDelete = result.toLowerCase().contains('hapus');
      _showSnack(
        result,
        isDelete ? const Color(0xFFE53E3E) : const Color(0xFF48BB78),
      );
    }
    await _loadAllData();
  }

  @override
  Widget build(BuildContext context) {
    final filtered = _filtered;
    return Scaffold(
      resizeToAvoidBottomInset: false,
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

  Widget _buildPageContent(List<KategoriData> filtered) =>
      SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        keyboardDismissBehavior: ScrollViewKeyboardDismissBehavior.manual,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            RepaintBoundary(child: _buildHeader()),
            const SizedBox(height: 24),
            if (_hasError) _buildSyncWarning(),
            _buildTable(filtered),
            const SizedBox(height: 40),
          ],
        ),
      );

  Widget _buildLoadingState() => SingleChildScrollView(
    physics: const AlwaysScrollableScrollPhysics(),
    keyboardDismissBehavior: ScrollViewKeyboardDismissBehavior.manual,
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
    keyboardDismissBehavior: ScrollViewKeyboardDismissBehavior.manual,
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
    final visibleCount = filtered.length < _maxRenderedRows
        ? filtered.length
        : _maxRenderedRows;
    final visibleItems = filtered.take(visibleCount).toList(growable: false);
    final canLoadMore = visibleCount < filtered.length;

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.06),
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
                          onChanged: _onSearchChanged,
                          onSubmitted: (_) =>
                              FocusManager.instance.primaryFocus?.unfocus(),
                          textInputAction: TextInputAction.search,
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
                        onPressed: () => FocusScope.of(context).unfocus(),
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
            RepaintBoundary(
              child: _KategoriRowsViewport(
                items: visibleItems,
                onEdit: _openEditKategori,
                onDelete: _showHapusDialog,
              ),
            ),
            if (canLoadMore)
              Padding(
                padding: const EdgeInsets.fromLTRB(0, 10, 0, 12),
                child: Center(
                  child: OutlinedButton.icon(
                    onPressed: () => setState(() {
                      _maxRenderedRows += _renderedRowsStep;
                    }),
                    icon: const Icon(Icons.expand_more_rounded, size: 18),
                    label: Text(
                      'Muat lebih banyak ($visibleCount/${filtered.length})',
                      style: const TextStyle(fontSize: 13),
                    ),
                  ),
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
class _KategoriRowsViewport extends StatelessWidget {
  final List<KategoriData> items;
  final ValueChanged<KategoriData> onEdit;
  final ValueChanged<KategoriData> onDelete;

  const _KategoriRowsViewport({
    required this.items,
    required this.onEdit,
    required this.onDelete,
  });

  static const double _tableWidth = 620;
  static const double _noWidth = 56;
  static const double _nameWidth = 190;
  static const double _slugWidth = 170;
  static const double _statusWidth = 96;
  static const double _actionWidth = 108;

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      keyboardDismissBehavior: ScrollViewKeyboardDismissBehavior.manual,
      child: SizedBox(
        width: _tableWidth,
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const _KategoriHeaderRow(),
            for (var i = 0; i < items.length; i++)
              _KategoriDataRow(
                index: i + 1,
                kategori: items[i],
                onEdit: () => onEdit(items[i]),
                onDelete: () => onDelete(items[i]),
              ),
          ],
        ),
      ),
    );
  }
}

class _KategoriHeaderRow extends StatelessWidget {
  const _KategoriHeaderRow();

  @override
  Widget build(BuildContext context) {
    return Container(
      height: 42,
      color: const Color.fromARGB(255, 74, 134, 255),
      child: const Row(
        children: [
          _KategoriHeaderCell('No', width: _KategoriRowsViewport._noWidth),
          _KategoriHeaderCell(
            'Nama Kategori',
            width: _KategoriRowsViewport._nameWidth,
          ),
          _KategoriHeaderCell('Slug', width: _KategoriRowsViewport._slugWidth),
          _KategoriHeaderCell(
            'Status',
            width: _KategoriRowsViewport._statusWidth,
          ),
          _KategoriHeaderCell(
            'Aksi',
            width: _KategoriRowsViewport._actionWidth,
          ),
        ],
      ),
    );
  }
}

class _KategoriHeaderCell extends StatelessWidget {
  final String text;
  final double width;

  const _KategoriHeaderCell(this.text, {required this.width});

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: width,
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 12),
        child: Text(
          text,
          style: const TextStyle(
            fontSize: 12,
            fontWeight: FontWeight.w700,
            color: Colors.white,
          ),
        ),
      ),
    );
  }
}

class _KategoriDataRow extends StatelessWidget {
  final int index;
  final KategoriData kategori;
  final VoidCallback onEdit;
  final VoidCallback onDelete;

  const _KategoriDataRow({
    required this.index,
    required this.kategori,
    required this.onEdit,
    required this.onDelete,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      height: 64,
      decoration: BoxDecoration(
        border: Border(bottom: BorderSide(color: Colors.grey.shade200)),
      ),
      child: Row(
        children: [
          _KategoriCell(
            width: _KategoriRowsViewport._noWidth,
            child: Text('$index', style: _rowTextStyle),
          ),
          _KategoriCell(
            width: _KategoriRowsViewport._nameWidth,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Text(
                  kategori.nama,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: _rowTextStyle.copyWith(fontWeight: FontWeight.w600),
                ),
                Text(
                  'ID: ${kategori.id}',
                  style: TextStyle(fontSize: 10, color: Colors.grey.shade400),
                ),
              ],
            ),
          ),
          _KategoriCell(
            width: _KategoriRowsViewport._slugWidth,
            child: Text(
              '#${kategori.slug}',
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: _rowTextStyle.copyWith(
                color: const Color(0xFF4169E1),
                fontWeight: FontWeight.w500,
              ),
            ),
          ),
          _KategoriCell(
            width: _KategoriRowsViewport._statusWidth,
            child: Align(
              alignment: Alignment.centerLeft,
              child: Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 10,
                  vertical: 4,
                ),
                decoration: BoxDecoration(
                  color: kategori.aktif
                      ? const Color(0xFF48BB78).withValues(alpha: 0.12)
                      : Colors.grey.shade100,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Text(
                  kategori.aktif ? 'Aktif' : 'Nonaktif',
                  style: TextStyle(
                    color: kategori.aktif
                        ? const Color(0xFF48BB78)
                        : Colors.grey,
                    fontSize: 11,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ),
            ),
          ),
          SizedBox(
            width: _KategoriRowsViewport._actionWidth,
            child: Row(
              mainAxisAlignment: MainAxisAlignment.center,
              mainAxisSize: MainAxisSize.min,
              children: [
                _AksiBtn(
                  icon: Icons.edit_rounded,
                  color: const Color(0xFFD69E2E),
                  label: 'Edit',
                  onTap: onEdit,
                ),
                const SizedBox(width: 8),
                _AksiBtn(
                  icon: Icons.delete_rounded,
                  color: const Color(0xFFE53E3E),
                  label: 'Hapus',
                  onTap: onDelete,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  static const TextStyle _rowTextStyle = TextStyle(
    fontSize: 12,
    color: Color(0xFF2D3748),
  );
}

class _KategoriCell extends StatelessWidget {
  final double width;
  final Widget child;

  const _KategoriCell({required this.width, required this.child});

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: width,
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 12),
        child: child,
      ),
    );
  }
}

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
            color: color.withValues(alpha: 0.13),
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
