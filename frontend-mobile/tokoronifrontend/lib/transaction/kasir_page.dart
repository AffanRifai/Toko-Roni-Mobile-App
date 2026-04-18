// lib/transaction/kasir_page.dart
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:tokoronifrontend/delivery/manajemen_pengiriman_page.dart';
import 'package:tokoronifrontend/transaction/riwayat_transaksi_page.dart';
import '../report/laporan_penjualan_page.dart';
import '../widgets/shared_widgets.dart';
import '../widgets/notifikasi_widget.dart';
import '../widgets/semua_notifikasi_page.dart';
import '../widgets/profile_widget.dart';
import '../product/produk_model.dart';
import '../member/member_model.dart';
import '../home/menu_pages.dart';
import '../home/beranda_page.dart';
import '../product/daftar_produk_page.dart';
import '../category/manajemen_kategori_page.dart';
import '../user/manajemen_pengguna_page.dart';
import '../member/daftar_member_page.dart';
import '../core/services/product_service.dart';

// ════════════════════════════════════════════════════════════════════════════
// MODEL KERANJANG
// ════════════════════════════════════════════════════════════════════════════
class KeranjangItem {
  final ProdukItem produk;
  int qty;

  KeranjangItem({required this.produk, this.qty = 1});

  int get subtotal => produk.harga * qty;
}

// ════════════════════════════════════════════════════════════════════════════
// KASIR PAGE
// ════════════════════════════════════════════════════════════════════════════
class KasirPage extends StatefulWidget {
  final String userName;
  final String userRole;

  const KasirPage({
    super.key,
    this.userName = 'Owner',
    this.userRole = 'Kasir',
  });

  @override
  State<KasirPage> createState() => _KasirPageState();
}

class _KasirPageState extends State<KasirPage>
    with SingleTickerProviderStateMixin, SidebarMixin {
  static const _blue = Color(0xFF3B6FE8);

  // ── Katalog ───────────────────────────────────────────────────────────────
  final _searchCtrl = TextEditingController();
  String _filterKategori = 'Semua';
  List<ProdukItem> _produkList = [];

  List<ProdukItem> get _katalog {
    final q = _searchCtrl.text.trim().toLowerCase();
    return _produkList.where((p) {
      if (!p.aktif) return false;
      final matchSearch =
          q.isEmpty ||
          p.nama.toLowerCase().contains(q) ||
          p.kode.toLowerCase().contains(q);
      final matchKat =
          _filterKategori == 'Semua' || p.kategori == _filterKategori;
      return matchSearch && matchKat;
    }).toList();
  }

  List<String> get _kategoriList {
    final set =
        _produkList
            .where((p) => p.aktif)
            .map((p) => p.kategori)
            .toSet()
            .toList()
          ..sort();
    return ['Semua', ...set];
  }

  // ── Keranjang ─────────────────────────────────────────────────────────────
  final List<KeranjangItem> _keranjang = [];

  // ── Member ────────────────────────────────────────────────────────────────
  MemberData? _selectedMember;
  bool _useMember = false;

  // ── Customer info ─────────────────────────────────────────────────────────
  final _namaCtrl = TextEditingController(text: 'Pelanggan umum');
  final _teleponCtrl = TextEditingController();

  // ── Diskon ────────────────────────────────────────────────────────────────
  final _diskonCtrl = TextEditingController(text: '0');
  double get _diskonPersen =>
      double.tryParse(_diskonCtrl.text.replaceAll(',', '.')) ?? 0;

  // ── Pembayaran ────────────────────────────────────────────────────────────
  String _metodePembayaran = 'Tunai';
  final _uangDiterimaCtrl = TextEditingController();

  // ── Kalkulasi ─────────────────────────────────────────────────────────────
  int get _subtotal => _keranjang.fold(0, (s, i) => s + i.subtotal);
  int get _nilaiDiskon => (_subtotal * _diskonPersen / 100).round();
  int get _total => _subtotal - _nilaiDiskon;
  int get _uangDiterima =>
      int.tryParse(_uangDiterimaCtrl.text.replaceAll('.', '')) ?? 0;
  int get _kembalian => (_uangDiterima - _total).clamp(0, 999999999);

  List<String> get _metodePembayaranList =>
      _useMember && _selectedMember != null
      ? ['Tunai', 'Debit', 'Hutang', 'E-Wallet']
      : ['Tunai', 'Debit', 'E-Wallet'];

  @override
  void initState() {
    super.initState();
    initSidebar(this);
    _loadProducts();
  }

  Future<void> _loadProducts() async {
    try {
      final products = await ProductService.getProducts();
      if (mounted) {
        setState(() => _produkList = products);
      }
    } catch (_) {
      // Jika API gagal, tampilkan error ke user, jangan fallback ke dummy
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Gagal memuat produk dari server')),
        );
      }
    }
  }

  @override
  void dispose() {
    disposeSidebar();
    _searchCtrl.dispose();
    _namaCtrl.dispose();
    _teleponCtrl.dispose();
    _diskonCtrl.dispose();
    _uangDiterimaCtrl.dispose();
    super.dispose();
  }

  void _handleMenuTap(String menu) {
    if (menu == 'Kasir') {
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

  // ── Tambah ke keranjang ───────────────────────────────────────────────────
  void _tambahKeKeranjang(ProdukItem p) {
    setState(() {
      final idx = _keranjang.indexWhere((k) => k.produk.kode == p.kode);
      if (idx >= 0) {
        if (_keranjang[idx].qty < p.stok) _keranjang[idx].qty++;
      } else {
        if (p.stok > 0) _keranjang.add(KeranjangItem(produk: p));
      }
    });
  }

  void _ubahQty(int idx, int delta) {
    setState(() {
      final item = _keranjang[idx];
      final newQty = item.qty + delta;
      if (newQty <= 0) {
        _keranjang.removeAt(idx);
      } else if (newQty <= item.produk.stok) {
        _keranjang[idx].qty = newQty;
      }
    });
  }

  void _hapusDariKeranjang(int idx) {
    setState(() => _keranjang.removeAt(idx));
  }

  void _resetKeranjang() {
    setState(() {
      _keranjang.clear();
      _selectedMember = null;
      _useMember = false;
      _namaCtrl.text = 'Pelanggan umum';
      _teleponCtrl.clear();
      _diskonCtrl.text = '0';
      _uangDiterimaCtrl.clear();
      _metodePembayaran = 'Tunai';
    });
  }

  // ── Proses transaksi ──────────────────────────────────────────────────────
  void _prosesTransaksi() {
    if (_keranjang.isEmpty) {
      _snack('Keranjang masih kosong!', Colors.red);
      return;
    }
    if (_metodePembayaran == 'Tunai' && _uangDiterima < _total) {
      _snack('Uang diterima kurang dari total!', Colors.red);
      return;
    }
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
                color: _blue.withOpacity(0.10),
                shape: BoxShape.circle,
              ),
              child: const Icon(
                Icons.shopping_cart_checkout_rounded,
                color: _blue,
                size: 32,
              ),
            ),
            const SizedBox(height: 16),
            const Text(
              'Proses Transaksi',
              style: TextStyle(
                fontSize: 17,
                fontWeight: FontWeight.bold,
                color: Color(0xFF2D3748),
              ),
            ),
            const SizedBox(height: 10),
            Text(
              'Total: ${_rp(_total)}\n'
              'Metode: $_metodePembayaran\n'
              '${_metodePembayaran == 'Tunai' ? 'Kembalian: ${_rp(_kembalian)}' : ''}',
              textAlign: TextAlign.center,
              style: TextStyle(
                fontSize: 13,
                color: Colors.grey.shade600,
                height: 1.6,
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
              const SizedBox(width: 10),
              Expanded(
                child: ElevatedButton(
                  onPressed: () {
                    // TODO: POST /api/transaksi
                    Navigator.pop(context);
                    _snack(
                      'Transaksi berhasil diproses!',
                      const Color(0xFF48BB78),
                    );
                    _resetKeranjang();
                  },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: _blue,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 12),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(10),
                    ),
                    elevation: 0,
                  ),
                  child: const Text(
                    'Proses',
                    style: TextStyle(fontWeight: FontWeight.w600),
                  ),
                ),
              ),
            ],
          ),
        ],
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
    return Scaffold(
      backgroundColor: const Color(0xFFF3F4F8),
      body: Stack(
        children: [
          Column(
            children: [
              _buildHeader(),
              Expanded(
                child: SingleChildScrollView(
                  child: Column(
                    children: [
                      const SizedBox(height: 16),
                      _buildKatalog(),
                      const SizedBox(height: 16),
                      _buildKeranjang(),
                      const SizedBox(height: 40),
                    ],
                  ),
                ),
              ),
            ],
          ),
          ...buildSidebarLayer(activeMenu: 'Kasir', onMenuTap: _handleMenuTap),
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
              padding: const EdgeInsets.fromLTRB(20, 16, 20, 24),
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
                        onTap: () => Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (_) => const ProfilePage(),
                          ),
                        ),
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
                          Icons.point_of_sale_rounded,
                          color: Colors.white,
                          size: 22,
                        ),
                      ),
                      const SizedBox(width: 12),
                      const Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Kasir',
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: 22,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          Text(
                            'Proses transaksi penjualan',
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

  // ════════════════════════════════════════════════════════════════════════
  // KATALOG PRODUK
  // ════════════════════════════════════════════════════════════════════════
  Widget _buildKatalog() {
    final katalog = _katalog;
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Container(
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
            // Header katalog
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 16, 16, 12),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Katalog Produk',
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                      color: Color(0xFF2D3748),
                    ),
                  ),
                  const SizedBox(height: 10),
                  // Search
                  TextField(
                    controller: _searchCtrl,
                    onChanged: (_) => setState(() {}),
                    style: const TextStyle(fontSize: 13),
                    decoration: InputDecoration(
                      hintText: 'Cari nama atau kode produk...',
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
                  const SizedBox(height: 10),
                  // Filter kategori chips
                  SingleChildScrollView(
                    scrollDirection: Axis.horizontal,
                    child: Row(
                      children: _kategoriList.map((k) {
                        final active = _filterKategori == k;
                        return GestureDetector(
                          onTap: () => setState(() => _filterKategori = k),
                          child: Container(
                            margin: const EdgeInsets.only(right: 8),
                            padding: const EdgeInsets.symmetric(
                              horizontal: 14,
                              vertical: 7,
                            ),
                            decoration: BoxDecoration(
                              color: active ? _blue : Colors.grey.shade100,
                              borderRadius: BorderRadius.circular(20),
                            ),
                            child: Text(
                              k,
                              style: TextStyle(
                                fontSize: 12,
                                fontWeight: active
                                    ? FontWeight.w600
                                    : FontWeight.normal,
                                color: active
                                    ? Colors.white
                                    : Colors.grey.shade600,
                              ),
                            ),
                          ),
                        );
                      }).toList(),
                    ),
                  ),
                ],
              ),
            ),

            const Divider(height: 1),

            // Grid produk
            katalog.isEmpty
                ? Padding(
                    padding: const EdgeInsets.symmetric(vertical: 32),
                    child: Center(
                      child: Column(
                        children: [
                          Icon(
                            Icons.search_off_rounded,
                            size: 36,
                            color: Colors.grey.shade300,
                          ),
                          const SizedBox(height: 8),
                          Text(
                            'Produk tidak ditemukan',
                            style: TextStyle(
                              fontSize: 13,
                              color: Colors.grey.shade400,
                            ),
                          ),
                        ],
                      ),
                    ),
                  )
                : GridView.builder(
                    shrinkWrap: true,
                    physics: const NeverScrollableScrollPhysics(),
                    padding: const EdgeInsets.all(12),
                    gridDelegate:
                        const SliverGridDelegateWithFixedCrossAxisCount(
                          crossAxisCount: 2,
                          crossAxisSpacing: 10,
                          mainAxisSpacing: 10,
                          mainAxisExtent: 130,
                        ),
                    itemCount: katalog.length,
                    itemBuilder: (_, i) => _ProdukCard(
                      produk: katalog[i],
                      qtyDiKeranjang: _keranjang
                          .where((k) => k.produk.kode == katalog[i].kode)
                          .fold(0, (s, k) => s + k.qty),
                      onTambah: () => _tambahKeKeranjang(katalog[i]),
                    ),
                  ),
          ],
        ),
      ),
    );
  }

  // ════════════════════════════════════════════════════════════════════════
  // KERANJANG BELANJAAN
  // ════════════════════════════════════════════════════════════════════════
  Widget _buildKeranjang() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Container(
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
            // Header keranjang
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 16, 16, 0),
              child: Row(
                children: [
                  const Icon(
                    Icons.shopping_cart_rounded,
                    color: _blue,
                    size: 20,
                  ),
                  const SizedBox(width: 8),
                  Text(
                    'Keranjang (${_keranjang.length} produk)',
                    style: const TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                      color: Color(0xFF2D3748),
                    ),
                  ),
                  const Spacer(),
                  if (_keranjang.isNotEmpty)
                    GestureDetector(
                      onTap: () => _showResetDialog(),
                      child: Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 12,
                          vertical: 6,
                        ),
                        decoration: BoxDecoration(
                          color: const Color(0xFFE53E3E).withOpacity(0.10),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: const Row(
                          children: [
                            Icon(
                              Icons.delete_sweep_rounded,
                              color: Color(0xFFE53E3E),
                              size: 16,
                            ),
                            SizedBox(width: 4),
                            Text(
                              'Hapus',
                              style: TextStyle(
                                fontSize: 12,
                                color: Color(0xFFE53E3E),
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                ],
              ),
            ),
            const SizedBox(height: 12),
            const Divider(height: 1),
            const SizedBox(height: 16),

            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // ── Member Area ───────────────────────────────────────────────
                  _buildMemberArea(),
                  const SizedBox(height: 16),

                  // ── Info Pelanggan ────────────────────────────────────────────
                  _buildInfoPelanggan(),
                  const SizedBox(height: 16),
                  const Divider(),
                  const SizedBox(height: 16),

                  // ── Item Keranjang ────────────────────────────────────────────
                  if (_keranjang.isEmpty)
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.symmetric(vertical: 32),
                      child: Column(
                        children: [
                          Icon(
                            Icons.shopping_cart_outlined,
                            size: 48,
                            color: Colors.grey.shade300,
                          ),
                          const SizedBox(height: 10),
                          Text(
                            'Keranjang masih kosong',
                            style: TextStyle(
                              fontSize: 14,
                              color: Colors.grey.shade400,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            'Tambahkan produk dari katalog di atas',
                            style: TextStyle(
                              fontSize: 12,
                              color: Colors.grey.shade400,
                            ),
                          ),
                        ],
                      ),
                    )
                  else
                    Column(
                      children: List.generate(
                        _keranjang.length,
                        (i) => _KeranjangTile(
                          item: _keranjang[i],
                          onIncrement: () => _ubahQty(i, 1),
                          onDecrement: () => _ubahQty(i, -1),
                          onHapus: () => _hapusDariKeranjang(i),
                        ),
                      ),
                    ),

                  if (_keranjang.isNotEmpty) ...[
                    const SizedBox(height: 16),
                    const Divider(),
                    const SizedBox(height: 16),
                    // ── Ringkasan Transaksi ──────────────────────────────────
                    _buildRingkasan(),
                  ],

                  const SizedBox(height: 20),

                  // ── Tombol Reset & Proses ─────────────────────────────────────
                  Row(
                    children: [
                      Expanded(
                        child: OutlinedButton.icon(
                          onPressed: _keranjang.isEmpty
                              ? null
                              : _showResetDialog,
                          icon: const Icon(Icons.refresh_rounded, size: 16),
                          label: const Text(
                            'Reset',
                            style: TextStyle(
                              fontSize: 14,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                          style: OutlinedButton.styleFrom(
                            foregroundColor: const Color(0xFFE53E3E),
                            side: const BorderSide(color: Color(0xFFE53E3E)),
                            padding: const EdgeInsets.symmetric(vertical: 14),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                            disabledForegroundColor: Colors.grey.shade300,
                          ),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        flex: 2,
                        child: ElevatedButton.icon(
                          onPressed: _keranjang.isEmpty
                              ? null
                              : _prosesTransaksi,
                          icon: const Icon(
                            Icons.check_circle_rounded,
                            size: 18,
                          ),
                          label: const Text(
                            'Proses Transaksi',
                            style: TextStyle(
                              fontSize: 14,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: _blue,
                            foregroundColor: Colors.white,
                            padding: const EdgeInsets.symmetric(vertical: 14),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                            elevation: 0,
                            disabledBackgroundColor: Colors.grey.shade200,
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  // ── Member Area ───────────────────────────────────────────────────────────
  Widget _buildMemberArea() {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: const Color(0xFFF0F4FF),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFFBEE3F8)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Icon(Icons.card_membership_rounded, color: _blue, size: 18),
              const SizedBox(width: 8),
              const Text(
                'Member Area',
                style: TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w600,
                  color: Color(0xFF2D3748),
                ),
              ),
              const SizedBox(width: 6),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                decoration: BoxDecoration(
                  color: Colors.grey.shade200,
                  borderRadius: BorderRadius.circular(8),
                ),
                child: const Text(
                  'Opsional',
                  style: TextStyle(fontSize: 10, color: Colors.grey),
                ),
              ),
              const Spacer(),
              Switch(
                value: _useMember,
                onChanged: (v) => setState(() {
                  _useMember = v;
                  if (!v) _selectedMember = null;
                }),
                activeColor: Colors.white,
                activeTrackColor: _blue,
                materialTapTargetSize: MaterialTapTargetSize.shrinkWrap,
              ),
            ],
          ),

          if (_useMember) ...[
            const SizedBox(height: 12),
            // Dropdown member
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 12),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: Colors.grey.shade300),
              ),
              child: DropdownButtonHideUnderline(
                child: DropdownButton<String>(
                  value: _selectedMember?.kode,
                  isExpanded: true,
                  hint: Text(
                    '-- Pilih member --',
                    style: TextStyle(color: Colors.grey.shade400, fontSize: 13),
                  ),
                  style: const TextStyle(
                    fontSize: 13,
                    color: Color(0xFF2D3748),
                  ),
                  icon: const Icon(Icons.keyboard_arrow_down_rounded, size: 22),
                  items: dummyMemberList
                      .map(
                        (m) => DropdownMenuItem(
                          value: m.kode,
                          child: Text(
                            '${m.kode} — ${m.nama}',
                            style: const TextStyle(fontSize: 13),
                          ),
                        ),
                      )
                      .toList(),
                  onChanged: (kode) => setState(() {
                    _selectedMember = kode == null
                        ? null
                        : dummyMemberList.firstWhere((m) => m.kode == kode);
                  }),
                ),
              ),
            ),

            // Info member terpilih
            if (_selectedMember != null) ...[
              const SizedBox(height: 12),
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(
                    color: const Color(0xFF3B6FE8).withOpacity(0.3),
                  ),
                ),
                child: Column(
                  children: [
                    _memberRow('Nama', _selectedMember!.nama),
                    _memberRow(
                      'Tipe Member',
                      _selectedMember!.tipe,
                      valueColor: _tipeMemberColor(_selectedMember!.tipe),
                    ),
                    _memberRow('ID Member', _selectedMember!.kode),
                    _memberRow(
                      'Limit Kredit',
                      rupiahFormat(_selectedMember!.limitKredit),
                    ),
                    _memberRow(
                      'Sisa Limit',
                      rupiahFormat(_selectedMember!.sisaLimit),
                      valueColor: _selectedMember!.sisaLimit < 100000
                          ? const Color(0xFFE53E3E)
                          : const Color(0xFF48BB78),
                    ),
                    _memberRow(
                      'Piutang',
                      rupiahFormat(_selectedMember!.piutang),
                      valueColor: _selectedMember!.piutang > 0
                          ? const Color(0xFFE53E3E)
                          : const Color(0xFF2D3748),
                    ),
                  ],
                ),
              ),
            ],
          ],
        ],
      ),
    );
  }

  Widget _memberRow(String label, String value, {Color? valueColor}) => Padding(
    padding: const EdgeInsets.symmetric(vertical: 4),
    child: Row(
      children: [
        Expanded(
          child: Text(
            label,
            style: TextStyle(fontSize: 12, color: Colors.grey.shade500),
          ),
        ),
        Text(
          value,
          style: TextStyle(
            fontSize: 12,
            fontWeight: FontWeight.w600,
            color: valueColor ?? const Color(0xFF2D3748),
          ),
        ),
      ],
    ),
  );

  Color _tipeMemberColor(String tipe) {
    switch (tipe) {
      case 'Gold':
        return const Color(0xFFD69E2E);
      case 'Platinum':
        return const Color(0xFF718096);
      case 'Diamon':
        return const Color(0xFF4169E1);
      default:
        return const Color(0xFF48BB78);
    }
  }

  // ── Info Pelanggan ────────────────────────────────────────────────────────
  Widget _buildInfoPelanggan() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Informasi Pelanggan',
          style: TextStyle(
            fontSize: 14,
            fontWeight: FontWeight.w600,
            color: Color(0xFF2D3748),
          ),
        ),
        const SizedBox(height: 10),
        // Nama
        _lbl('Nama Pelanggan', opsional: true),
        _inputField(_namaCtrl, 'Pelanggan umum'),
        const SizedBox(height: 12),
        // Telepon
        _lbl('No Telepon', opsional: true),
        _inputField(
          _teleponCtrl,
          'Masukan nomor telepon',
          type: TextInputType.phone,
        ),
      ],
    );
  }

  // ── Ringkasan Transaksi ───────────────────────────────────────────────────
  Widget _buildRingkasan() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Ringkasan Transaksi',
          style: TextStyle(
            fontSize: 14,
            fontWeight: FontWeight.w600,
            color: Color(0xFF2D3748),
          ),
        ),
        const SizedBox(height: 12),

        // Per item
        ..._keranjang.map(
          (k) => Padding(
            padding: const EdgeInsets.symmetric(vertical: 3),
            child: Row(
              children: [
                Expanded(
                  child: Text(
                    '${k.produk.nama} x${k.qty}',
                    style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
                  ),
                ),
                Text(
                  _rp(k.subtotal),
                  style: const TextStyle(
                    fontSize: 12,
                    color: Color(0xFF2D3748),
                  ),
                ),
              ],
            ),
          ),
        ),

        const SizedBox(height: 10),
        const Divider(),
        const SizedBox(height: 10),

        // Subtotal
        _ringkasanRow('Subtotal', _rp(_subtotal)),
        const SizedBox(height: 8),

        // Diskon
        Row(
          children: [
            const Text(
              'Diskon (%)',
              style: TextStyle(fontSize: 13, color: Color(0xFF4A5568)),
            ),
            const SizedBox(width: 12),
            SizedBox(
              width: 70,
              child: TextField(
                controller: _diskonCtrl,
                onChanged: (_) => setState(() {}),
                textAlign: TextAlign.center,
                keyboardType: const TextInputType.numberWithOptions(
                  decimal: true,
                ),
                inputFormatters: [
                  FilteringTextInputFormatter.allow(
                    RegExp(r'^\d{0,2}([.,]\d{0,1})?'),
                  ),
                ],
                style: const TextStyle(fontSize: 13),
                decoration: InputDecoration(
                  filled: true,
                  fillColor: const Color(0xFFF8F9FA),
                  contentPadding: const EdgeInsets.symmetric(
                    horizontal: 10,
                    vertical: 8,
                  ),
                  suffixText: '%',
                  suffixStyle: TextStyle(
                    fontSize: 12,
                    color: Colors.grey.shade500,
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
                    borderSide: const BorderSide(color: _blue, width: 1.5),
                  ),
                ),
              ),
            ),
            const Spacer(),
            if (_nilaiDiskon > 0)
              Text(
                '- ${_rp(_nilaiDiskon)}',
                style: const TextStyle(
                  fontSize: 13,
                  color: Color(0xFF48BB78),
                  fontWeight: FontWeight.w600,
                ),
              ),
          ],
        ),
        const SizedBox(height: 10),
        const Divider(),
        const SizedBox(height: 10),

        // Total
        Row(
          children: [
            const Text(
              'Total',
              style: TextStyle(
                fontSize: 15,
                fontWeight: FontWeight.bold,
                color: Color(0xFF2D3748),
              ),
            ),
            const Spacer(),
            Text(
              _rp(_total),
              style: const TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.bold,
                color: _blue,
              ),
            ),
          ],
        ),
        const SizedBox(height: 16),

        // Metode Pembayaran
        const Text(
          'Metode Pembayaran',
          style: TextStyle(
            fontSize: 13,
            fontWeight: FontWeight.w600,
            color: Color(0xFF2D3748),
          ),
        ),
        const SizedBox(height: 8),
        Wrap(
          spacing: 8,
          runSpacing: 8,
          children: _metodePembayaranList.map((m) {
            final active = _metodePembayaran == m;
            return GestureDetector(
              onTap: () => setState(() => _metodePembayaran = m),
              child: Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 16,
                  vertical: 9,
                ),
                decoration: BoxDecoration(
                  color: active ? _blue : Colors.grey.shade100,
                  borderRadius: BorderRadius.circular(24),
                  border: Border.all(
                    color: active ? _blue : Colors.grey.shade300,
                  ),
                ),
                child: Text(
                  m,
                  style: TextStyle(
                    fontSize: 13,
                    fontWeight: active ? FontWeight.w600 : FontWeight.normal,
                    color: active ? Colors.white : Colors.grey.shade600,
                  ),
                ),
              ),
            );
          }).toList(),
        ),
        const SizedBox(height: 16),

        // Uang Diterima + Kembalian (hanya untuk Tunai)
        if (_metodePembayaran == 'Tunai') ...[
          _lbl('Uang Diterima'),
          _inputField(
            _uangDiterimaCtrl,
            'Rp 0',
            type: TextInputType.number,
            formatters: [FilteringTextInputFormatter.digitsOnly],
            prefix: 'Rp ',
            onChanged: (_) => setState(() {}),
          ),
          const SizedBox(height: 12),
          Container(
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: _kembalian >= 0 && _uangDiterima >= _total
                  ? const Color(0xFF48BB78).withOpacity(0.10)
                  : const Color(0xFFE53E3E).withOpacity(0.08),
              borderRadius: BorderRadius.circular(10),
              border: Border.all(
                color: _uangDiterima >= _total
                    ? const Color(0xFF48BB78).withOpacity(0.3)
                    : const Color(0xFFE53E3E).withOpacity(0.3),
              ),
            ),
            child: Row(
              children: [
                Icon(
                  _uangDiterima >= _total
                      ? Icons.check_circle_rounded
                      : Icons.warning_rounded,
                  color: _uangDiterima >= _total
                      ? const Color(0xFF48BB78)
                      : const Color(0xFFE53E3E),
                  size: 18,
                ),
                const SizedBox(width: 10),
                const Text(
                  'Kembalian',
                  style: TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w600,
                    color: Color(0xFF2D3748),
                  ),
                ),
                const Spacer(),
                Text(
                  _uangDiterima == 0 ? '-' : _rp(_kembalian),
                  style: TextStyle(
                    fontSize: 15,
                    fontWeight: FontWeight.bold,
                    color: _uangDiterima >= _total
                        ? const Color(0xFF48BB78)
                        : const Color(0xFFE53E3E),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 4),
        ],
      ],
    );
  }

  // ── Reset Dialog ──────────────────────────────────────────────────────────
  void _showResetDialog() {
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
                color: Colors.red.withOpacity(0.10),
                shape: BoxShape.circle,
              ),
              child: const Icon(
                Icons.delete_sweep_rounded,
                color: Color(0xFFE53E3E),
                size: 32,
              ),
            ),
            const SizedBox(height: 16),
            const Text(
              'Reset Keranjang?',
              style: TextStyle(
                fontSize: 17,
                fontWeight: FontWeight.bold,
                color: Color(0xFF2D3748),
              ),
            ),
            const SizedBox(height: 10),
            Text(
              'Semua produk di keranjang akan dihapus.',
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
          Row(
            children: [
              Expanded(
                child: OutlinedButton(
                  onPressed: () => Navigator.pop(context),
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
              const SizedBox(width: 10),
              Expanded(
                child: ElevatedButton(
                  onPressed: () {
                    Navigator.pop(context);
                    _resetKeranjang();
                  },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFFE53E3E),
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 12),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(10),
                    ),
                    elevation: 0,
                  ),
                  child: const Text(
                    'Ya, Reset',
                    style: TextStyle(fontWeight: FontWeight.w600),
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  // ── Field Helpers ─────────────────────────────────────────────────────────
  Widget _lbl(String text, {bool opsional = false}) => Padding(
    padding: const EdgeInsets.only(bottom: 7),
    child: Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Text(
          text,
          style: const TextStyle(
            fontSize: 13,
            fontWeight: FontWeight.w500,
            color: Color(0xFF4A5568),
          ),
        ),
        if (opsional) ...[
          const SizedBox(width: 6),
          Text(
            '(opsional)',
            style: TextStyle(fontSize: 11, color: Colors.grey.shade500),
          ),
        ],
      ],
    ),
  );

  Widget _inputField(
    TextEditingController ctrl,
    String hint, {
    TextInputType type = TextInputType.text,
    List<TextInputFormatter>? formatters,
    String? prefix,
    void Function(String)? onChanged,
  }) => TextField(
    controller: ctrl,
    keyboardType: type,
    inputFormatters: formatters,
    onChanged: onChanged,
    style: const TextStyle(fontSize: 13),
    decoration: InputDecoration(
      hintText: hint,
      hintStyle: TextStyle(color: Colors.grey.shade400, fontSize: 13),
      prefixText: prefix,
      filled: true,
      fillColor: const Color(0xFFF8F9FA),
      contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(10),
        borderSide: BorderSide(color: Colors.grey.shade300),
      ),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(10),
        borderSide: BorderSide(color: Colors.grey.shade300),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(10),
        borderSide: const BorderSide(color: _blue, width: 1.5),
      ),
    ),
  );

  Widget _ringkasanRow(String label, String value) => Row(
    children: [
      Text(label, style: TextStyle(fontSize: 13, color: Colors.grey.shade600)),
      const Spacer(),
      Text(
        value,
        style: const TextStyle(
          fontSize: 13,
          fontWeight: FontWeight.w600,
          color: Color(0xFF2D3748),
        ),
      ),
    ],
  );
}

// ════════════════════════════════════════════════════════════════════════════
// PRODUK CARD — di katalog
// ════════════════════════════════════════════════════════════════════════════
class _ProdukCard extends StatelessWidget {
  final ProdukItem produk;
  final int qtyDiKeranjang;
  final VoidCallback onTambah;

  const _ProdukCard({
    required this.produk,
    required this.qtyDiKeranjang,
    required this.onTambah,
  });

  @override
  Widget build(BuildContext context) {
    final habis = produk.stok == 0;
    return Container(
      padding: const EdgeInsets.all(10),
      decoration: BoxDecoration(
        color: habis ? Colors.grey.shade50 : Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
          color: qtyDiKeranjang > 0
              ? const Color(0xFF3B6FE8).withOpacity(0.4)
              : Colors.grey.shade200,
          width: qtyDiKeranjang > 0 ? 1.5 : 1,
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.04),
            blurRadius: 4,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Kode + badge qty
          Row(
            children: [
              Expanded(
                child: Text(
                  produk.kode,
                  style: TextStyle(fontSize: 9, color: Colors.grey.shade400),
                  overflow: TextOverflow.ellipsis,
                ),
              ),
              if (qtyDiKeranjang > 0)
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 6,
                    vertical: 2,
                  ),
                  decoration: BoxDecoration(
                    color: const Color(0xFF3B6FE8),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    '$qtyDiKeranjang',
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 10,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
            ],
          ),
          const SizedBox(height: 4),
          // Nama produk
          Text(
            produk.nama,
            style: TextStyle(
              fontSize: 12,
              fontWeight: FontWeight.w600,
              color: habis ? Colors.grey : const Color(0xFF2D3748),
            ),
            maxLines: 2,
            overflow: TextOverflow.ellipsis,
          ),
          const SizedBox(height: 2),
          // Kategori
          Text(
            produk.kategori,
            style: TextStyle(fontSize: 10, color: Colors.grey.shade500),
          ),
          const SizedBox(height: 6),
          // Harga + tombol tambah
          Row(
            children: [
              Expanded(
                child: Text(
                  _rp(produk.harga),
                  style: const TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.bold,
                    color: Color(0xFF3B6FE8),
                  ),
                  overflow: TextOverflow.ellipsis,
                ),
              ),
              GestureDetector(
                onTap: habis ? null : onTambah,
                child: Container(
                  width: 28,
                  height: 28,
                  decoration: BoxDecoration(
                    color: habis
                        ? Colors.grey.shade200
                        : const Color(0xFF3B6FE8),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Icon(
                    Icons.add_rounded,
                    color: habis ? Colors.grey.shade400 : Colors.white,
                    size: 18,
                  ),
                ),
              ),
            ],
          ),
          if (habis)
            Padding(
              padding: const EdgeInsets.only(top: 2),
              child: Text(
                'Stok Habis',
                style: TextStyle(fontSize: 9, color: Colors.red.shade400),
              ),
            ),
        ],
      ),
    );
  }
}

// ════════════════════════════════════════════════════════════════════════════
// KERANJANG TILE — item di keranjang
// ════════════════════════════════════════════════════════════════════════════
class _KeranjangTile extends StatelessWidget {
  final KeranjangItem item;
  final VoidCallback onIncrement;
  final VoidCallback onDecrement;
  final VoidCallback onHapus;

  const _KeranjangTile({
    required this.item,
    required this.onIncrement,
    required this.onDecrement,
    required this.onHapus,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: const Color(0xFFF8F9FC),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.grey.shade200),
      ),
      child: Row(
        children: [
          // Info produk
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  item.produk.nama,
                  style: const TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w600,
                    color: Color(0xFF2D3748),
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  _rp(item.produk.harga),
                  style: TextStyle(fontSize: 11, color: Colors.grey.shade500),
                ),
              ],
            ),
          ),

          // Qty control
          Container(
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(8),
              border: Border.all(color: Colors.grey.shade200),
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                _qtyBtn(Icons.remove_rounded, onDecrement),
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 12),
                  child: Text(
                    '${item.qty}',
                    style: const TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.bold,
                      color: Color(0xFF2D3748),
                    ),
                  ),
                ),
                _qtyBtn(Icons.add_rounded, onIncrement),
              ],
            ),
          ),
          const SizedBox(width: 10),

          // Subtotal
          SizedBox(
            width: 80,
            child: Text(
              _rp(item.subtotal),
              textAlign: TextAlign.right,
              style: const TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.w600,
                color: Color(0xFF3B6FE8),
              ),
            ),
          ),
          const SizedBox(width: 8),

          // Hapus
          GestureDetector(
            onTap: onHapus,
            child: Container(
              padding: const EdgeInsets.all(6),
              decoration: BoxDecoration(
                color: const Color(0xFFE53E3E).withOpacity(0.10),
                borderRadius: BorderRadius.circular(7),
              ),
              child: const Icon(
                Icons.delete_rounded,
                color: Color(0xFFE53E3E),
                size: 16,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _qtyBtn(IconData icon, VoidCallback onTap) => GestureDetector(
    onTap: onTap,
    child: Padding(
      padding: const EdgeInsets.all(6),
      child: Icon(icon, size: 16, color: const Color(0xFF3B6FE8)),
    ),
  );
}

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
