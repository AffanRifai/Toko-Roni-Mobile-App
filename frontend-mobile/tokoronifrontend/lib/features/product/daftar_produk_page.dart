// lib/product/daftar_produk_page.dart
import 'dart:async';

import 'package:flutter/material.dart';
import 'package:tokoronifrontend/features/delivery/manajemen_pengiriman_page.dart';
import 'package:tokoronifrontend/features/profile/profile_page.dart';
import 'package:tokoronifrontend/features/report/laporan_penjualan_page.dart';
import 'package:tokoronifrontend/features/transaction/kasir_page.dart';
import 'package:tokoronifrontend/features/transaction/riwayat_transaksi_page.dart';
import 'package:tokoronifrontend/features/vehicle/manajemen_kendaraan_page.dart';
import '../../core/access/role_access.dart';
import '../../core/state/app_state.dart';
import '../../core/services/product_service.dart';
import '../../shared/widgets/shared_widgets.dart';
import '../../shared/widgets/notifikasi_widget.dart';
import '../../shared/widgets/profile_widget.dart';
import '../../shared/widgets/semua_notifikasi_page.dart';
import '../../models/produk_model.dart';
import 'produk_form_page.dart' hide EditProdukPage;
import 'edit_produk_page.dart';
import '../home/dashboard_router.dart';
import '../category/manajemen_kategori_page.dart';
import '../category/tambah_kategori_page.dart';
import '../category/edit_kategori_page.dart';
import '../user/manajemen_pengguna_page.dart';
import '../member/daftar_member_page.dart';

// ════════════════════════════════════════════════════════════════════════════
// DAFTAR PRODUK PAGE
// ════════════════════════════════════════════════════════════════════════════
class DaftarProdukPage extends StatefulWidget {
  const DaftarProdukPage({super.key});

  @override
  State<DaftarProdukPage> createState() => _DaftarProdukPageState();
}

class _DaftarProdukPageState extends State<DaftarProdukPage>
    with SingleTickerProviderStateMixin, SidebarMixin {
  late List<ProdukItem> _produkList;
  late List<KategoriItem> _kategoriList;
  bool _isLoading = true;
  bool _hasError = false;
  String _errorMessage = '';

  final _searchCtrl = TextEditingController();
  Timer? _searchDebounce;
  String _searchQuery = '';
  String _filterKategori = 'Semua kategori';
  String _filterStatus = 'Semua status';
  String _filterStok = 'Semua stok';

  int _dataRevision = 0;
  int _filteredCacheRevision = -1;
  String _filteredCacheQuery = '';
  String _filteredCacheKategori = '';
  String _filteredCacheStatus = '';
  String _filteredCacheStok = '';
  List<ProdukItem> _filteredCache = const [];
  static const int _initialRenderedRows = 24;
  static const int _renderedRowsStep = 24;
  int _maxRenderedRows = _initialRenderedRows;

  int _kategoriOptionsCacheRevision = -1;
  List<String> _kategoriOptionsCache = const ['Semua kategori'];
  int _kategoriStatsCacheRevision = -1;
  Map<String, int> _kategoriProdukCountCache = const {};
  Set<String> _kategoriHasProdukCache = const {};

  String get _currentRole => AppState.instance.userRole.value;
  bool get _isProdukReadOnly => RoleAccess.isProdukReadOnlyRole(_currentRole);

  @override
  void initState() {
    super.initState();
    _searchQuery = _searchCtrl.text.trim().toLowerCase();
    initSidebar(this);
    _produkList = [];
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
      final bundle = await ProductService.getProductsAndCategories();
      if (!mounted) return;

      final categories = bundle.categories.isNotEmpty
          ? bundle.categories
          : _buildCategoriesFromProducts(bundle.products);
      final kategoriOptions = categories.map((e) => e.nama).toSet();

      setState(() {
        _produkList = bundle.products;
        _kategoriList = categories;
        _bumpDataRevision();
        if (!kategoriOptions.contains(_filterKategori)) {
          _filterKategori = 'Semua kategori';
        }
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

  List<KategoriItem> _buildCategoriesFromProducts(List<ProdukItem> products) {
    final unique =
        products
            .map((e) => e.kategori.trim())
            .where((e) => e.isNotEmpty && e != '-')
            .toSet()
            .toList()
          ..sort();
    return unique.map((e) => KategoriItem(nama: e)).toList();
  }

  // ── Filter ────────────────────────────────────────────────────────────────
  List<ProdukItem> get _filtered {
    final canUseCache =
        _filteredCacheRevision == _dataRevision &&
        _filteredCacheQuery == _searchQuery &&
        _filteredCacheKategori == _filterKategori &&
        _filteredCacheStatus == _filterStatus &&
        _filteredCacheStok == _filterStok;
    if (canUseCache) return _filteredCache;

    final q = _searchQuery;
    _filteredCache = _produkList
        .where((p) {
          final matchSearch =
              q.isEmpty ||
              p.nama.toLowerCase().contains(q) ||
              p.kode.toLowerCase().contains(q);
          final matchKat =
              _filterKategori == 'Semua kategori' ||
              p.kategori == _filterKategori;
          final matchStatus =
              _filterStatus == 'Semua status' ||
              (_filterStatus == 'Aktif' && p.aktif) ||
              (_filterStatus == 'Nonaktif' && !p.aktif);
          final matchStok =
              _filterStok == 'Semua stok' ||
              (_filterStok == 'Stok Habis' && p.stok == 0) ||
              (_filterStok == 'Stok Rendah' && p.stok > 0 && p.stok < 20) ||
              (_filterStok == 'Stok Normal' && p.stok >= 20);
          return matchSearch && matchKat && matchStatus && matchStok;
        })
        .toList(growable: false);

    _filteredCacheRevision = _dataRevision;
    _filteredCacheQuery = _searchQuery;
    _filteredCacheKategori = _filterKategori;
    _filteredCacheStatus = _filterStatus;
    _filteredCacheStok = _filterStok;
    return _filteredCache;
  }

  List<String> get _kategoriOptions {
    if (_kategoriOptionsCacheRevision == _dataRevision) {
      return _kategoriOptionsCache;
    }
    final set = <String>{
      ..._produkList.map((p) => p.kategori.trim()),
      ..._kategoriList.map((k) => k.nama.trim()),
    }..removeWhere((e) => e.isEmpty || e == '-');
    final list = set.toList()..sort();
    _kategoriOptionsCache = ['Semua kategori', ...list];
    _kategoriOptionsCacheRevision = _dataRevision;
    return _kategoriOptionsCache;
  }

  bool _kategoriHasProduk(String nama) {
    _ensureKategoriStatsCache();
    return _kategoriHasProdukCache.contains(nama);
  }

  int _kategoriProdukCount(String nama) {
    _ensureKategoriStatsCache();
    return _kategoriProdukCountCache[nama] ?? 0;
  }

  void _resetFilter() => setState(() {
    _searchCtrl.clear();
    _searchQuery = '';
    _filterKategori = 'Semua kategori';
    _filterStatus = 'Semua status';
    _filterStok = 'Semua stok';
    _invalidateFilteredCache();
    _maxRenderedRows = _initialRenderedRows;
  });

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
    _invalidateDerivedCaches();
    _maxRenderedRows = _initialRenderedRows;
  }

  void _invalidateFilteredCache() {
    _filteredCacheRevision = -1;
  }

  void _invalidateDerivedCaches() {
    _filteredCacheRevision = -1;
    _kategoriOptionsCacheRevision = -1;
    _kategoriStatsCacheRevision = -1;
  }

  void _ensureKategoriStatsCache() {
    if (_kategoriStatsCacheRevision == _dataRevision) return;
    final countMap = <String, int>{};
    for (final p in _produkList) {
      final key = p.kategori;
      if (key.isEmpty || key == '-') continue;
      countMap.update(key, (value) => value + 1, ifAbsent: () => 1);
    }
    _kategoriProdukCountCache = Map.unmodifiable(countMap);
    _kategoriHasProdukCache = Set.unmodifiable(countMap.keys);
    _kategoriStatsCacheRevision = _dataRevision;
  }

  String _rupiah(int n) {
    final s = n.toString();
    final buf = StringBuffer('Rp ');
    for (int i = 0; i < s.length; i++) {
      if (i > 0 && (s.length - i) % 3 == 0) buf.write('.');
      buf.write(s[i]);
    }
    return buf.toString();
  }

  DateTime? _parseExpiryDate(String source) {
    final raw = source.trim();
    if (raw.isEmpty || raw == '-') return null;

    try {
      return DateTime.parse(raw);
    } catch (_) {}

    final normalized = raw.replaceAll('/', '-');
    final parts = normalized.split('-');
    if (parts.length != 3) return null;

    try {
      if (parts[0].length == 4) {
        return DateTime(
          int.parse(parts[0]),
          int.parse(parts[1]),
          int.parse(parts[2]),
        );
      }
      return DateTime(
        int.parse(parts[2]),
        int.parse(parts[1]),
        int.parse(parts[0]),
      );
    } catch (_) {
      return null;
    }
  }

  _ExpiryAlert? _expiryAlertOf(ProdukItem p) {
    final expiry = _parseExpiryDate(p.kadaluarsa);
    if (expiry == null) return null;

    final now = DateTime.now();
    final today = DateTime(now.year, now.month, now.day);
    final expiryDate = DateTime(expiry.year, expiry.month, expiry.day);
    final daysLeft = expiryDate.difference(today).inDays;

    if (daysLeft < 0) {
      return const _ExpiryAlert(
        label: 'Expired',
        color: Color(0xFFE53E3E),
        icon: Icons.error_rounded,
      );
    }
    if (daysLeft <= 30) {
      return _ExpiryAlert(
        label: daysLeft == 0 ? 'Kadaluarsa hari ini' : '$daysLeft hari lagi',
        color: const Color(0xFFD69E2E),
        icon: Icons.warning_amber_rounded,
      );
    }
    return null;
  }

  Widget _buildHargaCell(ProdukItem p) {
    final hargaText = _rupiah(p.harga);
    return Text(
      hargaText,
      maxLines: 1,
      overflow: TextOverflow.ellipsis,
      style: TextStyle(
        fontSize: 11,
        color: p.harga > 0 ? const Color(0xFF2D3748) : Colors.grey.shade600,
        fontWeight: FontWeight.w500,
      ),
    );
  }

  Widget _buildKadaluarsaCell(ProdukItem p) {
    final alert = _expiryAlertOf(p);
    final tgl = p.kadaluarsa.trim().isEmpty ? '-' : p.kadaluarsa;
    return Column(
      mainAxisAlignment: MainAxisAlignment.center,
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          tgl,
          maxLines: 1,
          overflow: TextOverflow.ellipsis,
          style: const TextStyle(fontSize: 11, color: Color(0xFF2D3748)),
        ),
        if (alert != null) ...[
          const SizedBox(height: 3),
          Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(alert.icon, size: 12, color: alert.color),
              const SizedBox(width: 3),
              Text(
                alert.label,
                style: TextStyle(
                  fontSize: 10,
                  color: alert.color,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ],
          ),
        ],
      ],
    );
  }

  // ── Navigasi sidebar ──────────────────────────────────────────────────────
  void _handleMenuTap(String menu) {
    if (menu == 'Produk') {
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
          if (!mounted || _produkList.isNotEmpty || _kategoriList.isNotEmpty) {
            return;
          }
          _loadAllData();
        });
      });
    }
  }

  void _releaseHeavyContentForBackground() {
    if (_produkList.isEmpty &&
        _kategoriList.isEmpty &&
        _filteredCache.isEmpty) {
      return;
    }
    _searchDebounce?.cancel();
    setState(() {
      _produkList = [];
      _kategoriList = [];
      _filteredCache = const [];
      _filteredCacheRevision = -1;
      _kategoriOptionsCacheRevision = -1;
      _kategoriStatsCacheRevision = -1;
      _kategoriOptionsCache = const ['Semua kategori'];
      _kategoriProdukCountCache = const {};
      _kategoriHasProdukCache = const {};
      _isLoading = true;
      _hasError = false;
      _errorMessage = '';
      _maxRenderedRows = _initialRenderedRows;
    });
  }

  // ── Dialogs ───────────────────────────────────────────────────────────────
  void _showDetailModal(ProdukItem p) {
    FocusManager.instance.primaryFocus?.unfocus();
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => _DetailModal(produk: p, rupiah: _rupiah),
    );
  }

  void _showHapusProdukDialog(ProdukItem p) {
    FocusManager.instance.primaryFocus?.unfocus();
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Text(
          'Hapus Produk',
          style: TextStyle(fontWeight: FontWeight.bold),
        ),
        content: Text('Apakah kamu yakin ingin menghapus produk "${p.nama}"?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Batal', style: TextStyle(color: Colors.grey)),
          ),
          ElevatedButton(
            onPressed: () async {
              Navigator.pop(context);
              await _deleteProduk(p);
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

  void _showHapusKategoriDialog(KategoriItem k) {
    FocusManager.instance.primaryFocus?.unfocus();
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Text(
          'Hapus Kategori',
          style: TextStyle(fontWeight: FontWeight.bold),
        ),
        content: Text(
          'Apakah kamu yakin ingin menghapus kategori "${k.nama}"?',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Batal', style: TextStyle(color: Colors.grey)),
          ),
          ElevatedButton(
            onPressed: () {
              setState(() {
                _kategoriList.remove(k);
                _bumpDataRevision();
              });
              Navigator.pop(context);
              _showSnack('Kategori "${k.nama}" dihapus', Colors.red);
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

  // ════════════════════════════════════════════════════════════════════════
  // BUILD
  // ════════════════════════════════════════════════════════════════════════
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
                : (_hasError && _produkList.isEmpty)
                ? _buildErrorState()
                : _buildPageContent(filtered),
          ),
          ...buildSidebarLayer(activeMenu: 'Produk', onMenuTap: _handleMenuTap),
        ],
      ),
    );
  }

  Future<void> _deleteProduk(ProdukItem p) async {
    if (p.id == null) {
      setState(() {
        _produkList.remove(p);
        _bumpDataRevision();
      });
      _showSnack(
        'Produk "${p.nama}" dihapus lokal (mode offline).',
        const Color(0xFFE53E3E),
      );
      return;
    }
    try {
      await ProductService.deleteProduct(productId: p.id!);
      if (!mounted) return;
      setState(() {
        _produkList.removeWhere((e) => e.id == p.id);
        _bumpDataRevision();
      });
      _showSnack(
        'Produk "${p.nama}" berhasil dihapus',
        const Color(0xFFE53E3E),
      );
    } catch (e) {
      if (!mounted) return;
      _showSnack(
        e.toString().replaceFirst('Exception: ', ''),
        const Color(0xFFE53E3E),
      );
    }
  }

  Widget _buildPageContent(List<ProdukItem> filtered) {
    return SingleChildScrollView(
      physics: const AlwaysScrollableScrollPhysics(),
      keyboardDismissBehavior: ScrollViewKeyboardDismissBehavior.manual,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          RepaintBoundary(child: _buildHeader()),
          const SizedBox(height: 16),
          if (_hasError) _buildSyncWarning(),
          _buildSummaryCards(),
          const SizedBox(height: 20),
          _buildFilterSection(),
          const SizedBox(height: 16),
          if (!_isProdukReadOnly) ...[
            _buildActionButtons(),
            const SizedBox(height: 20),
          ],
          _buildProdukSection(filtered),
          const SizedBox(height: 24),
          _buildKategoriSection(),
          const SizedBox(height: 40),
        ],
      ),
    );
  }

  Widget _buildLoadingState() => RefreshIndicator(
    onRefresh: _loadAllData,
    child: SingleChildScrollView(
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
                'Memuat data produk...',
                style: TextStyle(color: Color(0xFF4A5568)),
              ),
            ],
          ),
        ),
      ),
    ),
  );

  Widget _buildErrorState() => RefreshIndicator(
    onRefresh: _loadAllData,
    child: SingleChildScrollView(
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
                'Gagal memuat data produk',
                style: TextStyle(
                  fontSize: 15,
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

  Widget _buildSyncWarning() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Container(
        margin: const EdgeInsets.only(bottom: 12),
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: const Color(0xFFFFF7E6),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: const Color(0xFFF6AD55).withValues(alpha: 0.5),
          ),
        ),
        child: Row(
          children: [
            const Icon(Icons.info_outline_rounded, color: Color(0xFFB7791F)),
            const SizedBox(width: 10),
            Expanded(
              child: Text(
                _errorMessage.isEmpty
                    ? 'Data terakhir ditampilkan. Tarik ke bawah untuk sinkron ulang.'
                    : 'Sinkronisasi gagal: $_errorMessage',
                style: const TextStyle(fontSize: 12, color: Color(0xFF744210)),
              ),
            ),
            TextButton(onPressed: _loadAllData, child: const Text('Refresh')),
          ],
        ),
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
              padding: const EdgeInsets.fromLTRB(20, 16, 20, 28),
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
                  const SizedBox(height: 20),
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(8),
                        decoration: BoxDecoration(
                          color: Colors.white24,
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: const Icon(
                          Icons.inventory_2_rounded,
                          color: Colors.white,
                          size: 22,
                        ),
                      ),
                      const SizedBox(width: 12),
                      const Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Daftar Produk',
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: 22,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          Text(
                            'Kelola semua produk dalam satu dashboard',
                            style: TextStyle(
                              color: Colors.white70,
                              fontSize: 12,
                            ),
                          ),
                        ],
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
    final totalProduk = _produkList.length;
    final produkAktif = _produkList.where((p) => p.aktif).length;
    final stokRendah = _produkList
        .where((p) => p.stok > 0 && p.stok < 20)
        .length;
    final stokHabis = _produkList.where((p) => p.stok == 0).length;

    return SizedBox(
      height: 110,
      child: ListView(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 16),
        children: [
          SummaryCard(
            label: 'Total Produk',
            value: '$totalProduk',
            icon: Icons.inventory_2_rounded,
            color: const Color(0xFF4169E1),
          ),
          SummaryCard(
            label: 'Produk Aktif',
            value: '$produkAktif',
            icon: Icons.check_circle_rounded,
            color: const Color(0xFF48BB78),
          ),
          SummaryCard(
            label: 'Stok Rendah',
            value: '$stokRendah',
            icon: Icons.warning_amber_rounded,
            color: const Color(0xFFECC94B),
          ),
          SummaryCard(
            label: 'Stok Habis',
            value: '$stokHabis',
            icon: Icons.close_rounded,
            color: const Color(0xFFE53E3E),
          ),
        ],
      ),
    );
  }

  // ── FILTER ────────────────────────────────────────────────────────────────
  Widget _buildFilterSection() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.05),
              blurRadius: 8,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Filter produk',
              style: TextStyle(
                fontWeight: FontWeight.w600,
                fontSize: 14,
                color: Color(0xFF2D3748),
              ),
            ),
            const SizedBox(height: 12),
            Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _searchCtrl,
                    onChanged: _onSearchChanged,
                    onSubmitted: (_) =>
                        FocusManager.instance.primaryFocus?.unfocus(),
                    textInputAction: TextInputAction.search,
                    decoration: InputDecoration(
                      hintText: 'Cari nama, kode',
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
                  onPressed: () => FocusScope.of(context).unfocus(),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF4169E1),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(24),
                    ),
                    padding: const EdgeInsets.symmetric(
                      horizontal: 18,
                      vertical: 13,
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
            const SizedBox(height: 12),
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: [
                _dd(
                  _filterKategori,
                  _kategoriOptions,
                  (v) => setState(() {
                    _filterKategori = v!;
                    _invalidateFilteredCache();
                    _maxRenderedRows = _initialRenderedRows;
                  }),
                ),
                _dd(
                  _filterStatus,
                  const ['Semua status', 'Aktif', 'Nonaktif'],
                  (v) => setState(() {
                    _filterStatus = v!;
                    _invalidateFilteredCache();
                    _maxRenderedRows = _initialRenderedRows;
                  }),
                ),
                _dd(
                  _filterStok,
                  const [
                    'Semua stok',
                    'Stok Normal',
                    'Stok Rendah',
                    'Stok Habis',
                  ],
                  (v) => setState(() {
                    _filterStok = v!;
                    _invalidateFilteredCache();
                    _maxRenderedRows = _initialRenderedRows;
                  }),
                ),
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
                      padding: const EdgeInsets.symmetric(horizontal: 16),
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

  // ── ACTION BUTTONS ────────────────────────────────────────────────────────
  Widget _buildActionButtons() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Row(
        children: [
          Expanded(
            child: ElevatedButton.icon(
              onPressed: () async {
                FocusManager.instance.primaryFocus?.unfocus();
                _releaseHeavyContentForBackground();
                await Navigator.push(
                  context,
                  MaterialPageRoute(builder: (_) => const TambahKategoriPage()),
                );
                if (mounted) _loadAllData();
              },
              icon: const Icon(
                Icons.label_rounded,
                color: Colors.white,
                size: 16,
              ),
              label: const Text(
                'Tambah Kategori',
                style: TextStyle(color: Colors.white, fontSize: 13),
              ),
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF6B5CE7),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(24),
                ),
                padding: const EdgeInsets.symmetric(vertical: 13),
                elevation: 0,
              ),
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: ElevatedButton.icon(
              onPressed: () async {
                FocusManager.instance.primaryFocus?.unfocus();
                _releaseHeavyContentForBackground();
                await Navigator.push(
                  context,
                  MaterialPageRoute(builder: (_) => const TambahProdukPage()),
                );
                if (mounted) _loadAllData();
              },
              icon: const Icon(
                Icons.add_rounded,
                color: Colors.white,
                size: 16,
              ),
              label: const Text(
                'Tambah Produk',
                style: TextStyle(color: Colors.white, fontSize: 13),
              ),
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF4169E1),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(24),
                ),
                padding: const EdgeInsets.symmetric(vertical: 13),
                elevation: 0,
              ),
            ),
          ),
        ],
      ),
    );
  }

  // ── DAFTAR PRODUK ─────────────────────────────────────────────────────────
  Widget _buildProdukSection(List<ProdukItem> filtered) {
    final visibleCount = filtered.length < _maxRenderedRows
        ? filtered.length
        : _maxRenderedRows;
    final visibleItems = filtered.take(visibleCount).toList(growable: false);
    final canLoadMore = visibleCount < filtered.length;

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Daftar Produk',
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.bold,
              color: Color(0xFF2D3748),
            ),
          ),
          const SizedBox(height: 12),
          if (visibleItems.isEmpty)
            _buildEmptyState()
          else
            RepaintBoundary(
              child: Container(
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: 0.05),
                      blurRadius: 8,
                      offset: const Offset(0, 2),
                    ),
                  ],
                ),
                child: _ProdukRowsViewport(
                  items: visibleItems,
                  isReadOnly: _isProdukReadOnly,
                  hargaBuilder: _buildHargaCell,
                  kadaluarsaBuilder: _buildKadaluarsaCell,
                  onDetail: _showDetailModal,
                  onEdit: (p) async {
                    FocusManager.instance.primaryFocus?.unfocus();
                    _releaseHeavyContentForBackground();
                    await Navigator.push(
                      context,
                      MaterialPageRoute(
                        builder: (_) => EditProdukPage(produk: p),
                      ),
                    );
                    if (mounted) _loadAllData();
                  },
                  onDelete: _showHapusProdukDialog,
                ),
              ),
            ),
          if (canLoadMore) ...[
            const SizedBox(height: 12),
            Align(
              alignment: Alignment.center,
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
          ],
        ],
      ),
    );
  }

  Widget _buildEmptyState() => Center(
    child: Padding(
      padding: const EdgeInsets.symmetric(vertical: 40),
      child: Column(
        children: [
          Container(
            width: 80,
            height: 80,
            decoration: BoxDecoration(
              color: Colors.grey.shade100,
              shape: BoxShape.circle,
            ),
            child: Icon(
              Icons.search_off_rounded,
              size: 40,
              color: Colors.grey.shade400,
            ),
          ),
          const SizedBox(height: 16),
          const Text(
            'Produk tidak ditemukan',
            style: TextStyle(
              fontSize: 15,
              fontWeight: FontWeight.w600,
              color: Color(0xFF2D3748),
            ),
          ),
          const SizedBox(height: 6),
          Text(
            'Coba kata kunci atau filter lain',
            style: TextStyle(fontSize: 12, color: Colors.grey.shade500),
          ),
          const SizedBox(height: 18),
          ElevatedButton.icon(
            onPressed: _resetFilter,
            icon: const Icon(
              Icons.refresh_rounded,
              size: 16,
              color: Colors.white,
            ),
            label: const Text(
              'Reset Filter',
              style: TextStyle(color: Colors.white, fontSize: 13),
            ),
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF4169E1),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(24),
              ),
              padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
              elevation: 0,
            ),
          ),
        ],
      ),
    ),
  );

  // ── DAFTAR KATEGORI — 1 ROW (ListView) ───────────────────────────────────
  Widget _buildKategoriSection() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Icon(
                Icons.label_rounded,
                color: Color(0xFF2D3748),
                size: 22,
              ),
              const SizedBox(width: 8),
              const Text(
                'Daftar Kategori',
                style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                  color: Color(0xFF2D3748),
                ),
              ),
            ],
          ),
          const SizedBox(height: 4),
          Text(
            '${_kategoriList.length} kategori tersedia',
            style: TextStyle(fontSize: 12, color: Colors.grey.shade500),
          ),

          // ← ListView 1 kolom penuh (bukan GridView 2 kolom)
          const SizedBox(height: 10),
          RepaintBoundary(
            child: Column(
              children: [
                for (var i = 0; i < _kategoriList.length; i++) ...[
                  Builder(
                    builder: (_) {
                      final k = _kategoriList[i];
                      final hasProduk = _kategoriHasProduk(k.nama);
                      final jumlah = _kategoriProdukCount(k.nama);
                      return Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 16,
                          vertical: 14,
                        ),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(16),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black.withValues(alpha: 0.05),
                              blurRadius: 6,
                              offset: const Offset(0, 2),
                            ),
                          ],
                        ),
                        child: Row(
                          children: [
                            // Icon kategori
                            Container(
                              padding: const EdgeInsets.all(10),
                              decoration: BoxDecoration(
                                color: const Color(
                                  0xFF4169E1,
                                ).withValues(alpha: 0.12),
                                borderRadius: BorderRadius.circular(10),
                              ),
                              child: const Icon(
                                Icons.label_rounded,
                                color: Color(0xFF4169E1),
                                size: 22,
                              ),
                            ),
                            const SizedBox(width: 12),
                            // Nama + jumlah produk
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    k.nama,
                                    style: const TextStyle(
                                      fontSize: 15,
                                      fontWeight: FontWeight.w600,
                                      color: Color(0xFF2D3748),
                                    ),
                                  ),
                                  const SizedBox(height: 3),
                                  Text(
                                    '$jumlah produk',
                                    style: TextStyle(
                                      fontSize: 12,
                                      color: Colors.grey.shade500,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                            // Action buttons sejajar horizontal
                            Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                if (!_isProdukReadOnly) ...[
                                  _KatBtn(
                                    icon: Icons.edit_rounded,
                                    color: const Color(0xFFD69E2E),
                                    label: 'edit',
                                    onTap: () => Navigator.push(
                                      context,
                                      MaterialPageRoute(
                                        builder: (_) => EditKategoriPage(
                                          // Konversi KategoriItem → KategoriData saat navigasi
                                          kategori: KategoriData(
                                            id: k.id ?? 0,
                                            nama: k.nama,
                                            slug: k.nama
                                                .toLowerCase()
                                                .replaceAll(' ', '-'),
                                            deskripsi: k.deskripsi,
                                            aktif: true,
                                            totalProduk: jumlah,
                                            terakhirDiperbarui: '-',
                                          ),
                                        ),
                                      ),
                                    ),
                                  ),
                                  const SizedBox(width: 8),
                                  _KatBtn(
                                    icon: Icons.delete_rounded,
                                    color: hasProduk
                                        ? Colors.grey.shade300
                                        : const Color(0xFFE53E3E),
                                    label: 'hapus',
                                    disabled: hasProduk,
                                    onTap: hasProduk
                                        ? () => _showSnack(
                                            'Kategori tidak bisa dihapus karena masih punya produk',
                                            const Color(0xFFE53E3E),
                                          )
                                        : () => _showHapusKategoriDialog(k),
                                  ),
                                ],
                              ],
                            ),
                          ],
                        ),
                      );
                    },
                  ),
                  if (i != _kategoriList.length - 1) const SizedBox(height: 10),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }
}

// ════════════════════════════════════════════════════════════════════════════
// DETAIL MODAL
// ════════════════════════════════════════════════════════════════════════════
class _ProdukRowsViewport extends StatelessWidget {
  final List<ProdukItem> items;
  final bool isReadOnly;
  final Widget Function(ProdukItem) hargaBuilder;
  final Widget Function(ProdukItem) kadaluarsaBuilder;
  final ValueChanged<ProdukItem> onDetail;
  final ValueChanged<ProdukItem> onEdit;
  final ValueChanged<ProdukItem> onDelete;

  const _ProdukRowsViewport({
    required this.items,
    required this.isReadOnly,
    required this.hargaBuilder,
    required this.kadaluarsaBuilder,
    required this.onDetail,
    required this.onEdit,
    required this.onDelete,
  });

  static const double _tableWidth = 1212;
  static const double _noWidth = 52;
  static const double _kodeWidth = 110;
  static const double _produkWidth = 150;
  static const double _kategoriWidth = 128;
  static const double _jenisWidth = 84;
  static const double _hargaWidth = 112;
  static const double _stokWidth = 76;
  static const double _kadaluarsaWidth = 132;
  static const double _statusWidth = 96;
  static const double _ketersediaanWidth = 114;
  static const double _aksiWidth = 158;

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
            const _ProdukHeaderRow(),
            for (var i = 0; i < items.length; i++)
              _ProdukDataRow(
                index: i + 1,
                produk: items[i],
                isReadOnly: isReadOnly,
                hargaBuilder: hargaBuilder,
                kadaluarsaBuilder: kadaluarsaBuilder,
                onDetail: () => onDetail(items[i]),
                onEdit: () => onEdit(items[i]),
                onDelete: () => onDelete(items[i]),
              ),
          ],
        ),
      ),
    );
  }
}

class _ProdukHeaderRow extends StatelessWidget {
  const _ProdukHeaderRow();

  @override
  Widget build(BuildContext context) {
    return Container(
      height: 44,
      color: const Color.fromARGB(255, 74, 134, 255),
      child: const Row(
        children: [
          _ProdukHeaderCell('No', width: _ProdukRowsViewport._noWidth),
          _ProdukHeaderCell(
            'Kode Produk',
            width: _ProdukRowsViewport._kodeWidth,
          ),
          _ProdukHeaderCell('Produk', width: _ProdukRowsViewport._produkWidth),
          _ProdukHeaderCell(
            'Kategori',
            width: _ProdukRowsViewport._kategoriWidth,
          ),
          _ProdukHeaderCell('Jenis', width: _ProdukRowsViewport._jenisWidth),
          _ProdukHeaderCell(
            'Harga Jual',
            width: _ProdukRowsViewport._hargaWidth,
          ),
          _ProdukHeaderCell('Stok', width: _ProdukRowsViewport._stokWidth),
          _ProdukHeaderCell(
            'Kadaluarsa',
            width: _ProdukRowsViewport._kadaluarsaWidth,
          ),
          _ProdukHeaderCell('Status', width: _ProdukRowsViewport._statusWidth),
          _ProdukHeaderCell(
            'Ketersediaan',
            width: _ProdukRowsViewport._ketersediaanWidth,
          ),
          _ProdukHeaderCell('Aksi', width: _ProdukRowsViewport._aksiWidth),
        ],
      ),
    );
  }
}

class _ProdukHeaderCell extends StatelessWidget {
  final String text;
  final double width;

  const _ProdukHeaderCell(this.text, {required this.width});

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: width,
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 10),
        child: Text(
          text,
          style: const TextStyle(
            fontSize: 11,
            fontWeight: FontWeight.w700,
            color: Colors.white,
          ),
        ),
      ),
    );
  }
}

class _ProdukDataRow extends StatelessWidget {
  final int index;
  final ProdukItem produk;
  final bool isReadOnly;
  final Widget Function(ProdukItem) hargaBuilder;
  final Widget Function(ProdukItem) kadaluarsaBuilder;
  final VoidCallback onDetail;
  final VoidCallback onEdit;
  final VoidCallback onDelete;

  const _ProdukDataRow({
    required this.index,
    required this.produk,
    required this.isReadOnly,
    required this.hargaBuilder,
    required this.kadaluarsaBuilder,
    required this.onDetail,
    required this.onEdit,
    required this.onDelete,
  });

  @override
  Widget build(BuildContext context) {
    final stokColor = produk.stok == 0
        ? const Color(0xFFE53E3E)
        : produk.stok < 20
        ? const Color(0xFFECC94B)
        : const Color(0xFF48BB78);

    return Container(
      height: 76,
      decoration: BoxDecoration(
        border: Border(bottom: BorderSide(color: Colors.grey.shade200)),
      ),
      child: Row(
        children: [
          _ProdukCell(
            width: _ProdukRowsViewport._noWidth,
            child: Text('$index', style: _rowTextStyle),
          ),
          _ProdukCell(
            width: _ProdukRowsViewport._kodeWidth,
            child: Text(
              produk.kode,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: _rowTextStyle.copyWith(fontSize: 10),
            ),
          ),
          _ProdukCell(
            width: _ProdukRowsViewport._produkWidth,
            child: Text(
              produk.nama,
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
              style: _rowTextStyle,
            ),
          ),
          _ProdukCell(
            width: _ProdukRowsViewport._kategoriWidth,
            child: Text(
              produk.kategori,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: _rowTextStyle,
            ),
          ),
          _ProdukCell(
            width: _ProdukRowsViewport._jenisWidth,
            child: Text(
              produk.jenis,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: _rowTextStyle,
            ),
          ),
          _ProdukCell(
            width: _ProdukRowsViewport._hargaWidth,
            child: hargaBuilder(produk),
          ),
          _ProdukCell(
            width: _ProdukRowsViewport._stokWidth,
            child: Align(
              alignment: Alignment.centerLeft,
              child: _ProdukPill(
                label: '${produk.stok}',
                color: stokColor,
                background: stokColor.withValues(alpha: 0.12),
                fontSize: 12,
              ),
            ),
          ),
          _ProdukCell(
            width: _ProdukRowsViewport._kadaluarsaWidth,
            child: kadaluarsaBuilder(produk),
          ),
          _ProdukCell(
            width: _ProdukRowsViewport._statusWidth,
            child: Align(
              alignment: Alignment.centerLeft,
              child: _ProdukPill(
                label: produk.aktif ? 'Aktif' : 'Nonaktif',
                color: produk.aktif ? const Color(0xFF48BB78) : Colors.grey,
                background: produk.aktif
                    ? const Color(0xFF48BB78).withValues(alpha: 0.12)
                    : Colors.grey.shade100,
              ),
            ),
          ),
          _ProdukCell(
            width: _ProdukRowsViewport._ketersediaanWidth,
            child: Align(
              alignment: Alignment.centerLeft,
              child: produk.stok > 0
                  ? const _ProdukPill(
                      label: 'Tersedia',
                      color: Color(0xFF4169E1),
                      background: Color(0x1A4169E1),
                    )
                  : const _ProdukPill(
                      label: 'Habis',
                      color: Color(0xFFE53E3E),
                      background: Color(0x1AE53E3E),
                    ),
            ),
          ),
          SizedBox(
            width: _ProdukRowsViewport._aksiWidth,
            child: Row(
              mainAxisAlignment: MainAxisAlignment.center,
              mainAxisSize: MainAxisSize.min,
              children: [
                _AksiBtn(
                  icon: Icons.visibility_rounded,
                  color: const Color(0xFF4169E1),
                  label: 'Detail',
                  onTap: onDetail,
                ),
                if (!isReadOnly) ...[
                  const SizedBox(width: 8),
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
              ],
            ),
          ),
        ],
      ),
    );
  }

  static const TextStyle _rowTextStyle = TextStyle(
    fontSize: 11,
    color: Color(0xFF2D3748),
  );
}

class _ProdukCell extends StatelessWidget {
  final double width;
  final Widget child;

  const _ProdukCell({required this.width, required this.child});

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: width,
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 10),
        child: child,
      ),
    );
  }
}

class _ProdukPill extends StatelessWidget {
  final String label;
  final Color color;
  final Color background;
  final double fontSize;

  const _ProdukPill({
    required this.label,
    required this.color,
    required this.background,
    this.fontSize = 11,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: background,
        borderRadius: BorderRadius.circular(12),
      ),
      child: Text(
        label,
        maxLines: 1,
        overflow: TextOverflow.ellipsis,
        style: TextStyle(
          color: color,
          fontSize: fontSize,
          fontWeight: FontWeight.w600,
        ),
      ),
    );
  }
}

class _DetailModal extends StatelessWidget {
  final ProdukItem produk;
  final String Function(int) rupiah;
  const _DetailModal({required this.produk, required this.rupiah});

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      padding: const EdgeInsets.fromLTRB(24, 16, 24, 32),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            width: 40,
            height: 4,
            decoration: BoxDecoration(
              color: Colors.grey.shade300,
              borderRadius: BorderRadius.circular(2),
            ),
          ),
          const SizedBox(height: 20),
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: const Color(0xFF4169E1).withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Icon(
                  Icons.inventory_2_rounded,
                  color: Color(0xFF4169E1),
                  size: 24,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      produk.nama,
                      style: const TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                        color: Color(0xFF2D3748),
                      ),
                    ),
                    Text(
                      produk.kode,
                      style: TextStyle(
                        fontSize: 12,
                        color: Colors.grey.shade500,
                      ),
                    ),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 10,
                  vertical: 4,
                ),
                decoration: BoxDecoration(
                  color: produk.aktif
                      ? const Color(0xFF48BB78).withValues(alpha: 0.12)
                      : Colors.grey.shade100,
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(
                  produk.aktif ? 'Aktif' : 'Nonaktif',
                  style: TextStyle(
                    color: produk.aktif ? const Color(0xFF48BB78) : Colors.grey,
                    fontSize: 12,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 20),
          const Divider(),
          const SizedBox(height: 12),
          ...[
            ['Kategori', produk.kategori],
            ['Jenis', produk.jenis],
            ['Harga Jual', rupiah(produk.harga)],
            ['Stok', '${produk.stok} unit'],
            ['Kadaluarsa', produk.kadaluarsa],
            ['Ketersediaan', produk.stok > 0 ? 'Tersedia' : 'Habis'],
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
                backgroundColor: const Color(0xFF4169E1),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(14),
                ),
                padding: const EdgeInsets.symmetric(vertical: 14),
              ),
              child: const Text(
                'Tutup',
                style: TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

// ════════════════════════════════════════════════════════════════════════════
// AKSI BUTTONS
// ════════════════════════════════════════════════════════════════════════════
class _ExpiryAlert {
  final String label;
  final Color color;
  final IconData icon;

  const _ExpiryAlert({
    required this.label,
    required this.color,
    required this.icon,
  });
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

class _KatBtn extends StatelessWidget {
  final IconData icon;
  final Color color;
  final String label;
  final VoidCallback onTap;
  final bool disabled;
  const _KatBtn({
    required this.icon,
    required this.color,
    required this.label,
    required this.onTap,
    this.disabled = false,
  });
  @override
  Widget build(BuildContext context) => GestureDetector(
    onTap: disabled ? null : onTap,
    child: Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        Container(
          padding: const EdgeInsets.all(8),
          decoration: BoxDecoration(
            color: disabled
                ? Colors.grey.shade100
                : color.withValues(alpha: 0.15),
            borderRadius: BorderRadius.circular(9),
          ),
          child: Icon(
            icon,
            color: disabled ? Colors.grey.shade300 : color,
            size: 17,
          ),
        ),
        const SizedBox(height: 3),
        Text(
          label,
          style: TextStyle(
            fontSize: 10,
            color: disabled ? Colors.grey.shade300 : color,
            fontWeight: FontWeight.w500,
          ),
        ),
      ],
    ),
  );
}
