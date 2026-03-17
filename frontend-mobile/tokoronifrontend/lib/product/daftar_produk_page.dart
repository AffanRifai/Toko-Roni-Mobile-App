// lib/product/daftar_produk_page.dart
import 'package:flutter/material.dart';
import '../shared_widgets.dart';
import 'produk_model.dart';
import 'produk_form_page.dart';
import '/home/menu_pages.dart';
import '/home/beranda_page.dart';

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
  // Pakai ProdukItem dari produk_model.dart — tidak konflik dengan class lain
  late List<ProdukItem> _produkList;
  late List<KategoriItem> _kategoriList;

  final _searchCtrl = TextEditingController();
  String _filterKategori = 'Semua kategori';
  String _filterStatus = 'Semua status';
  String _filterStok = 'Semua stok';

  @override
  void initState() {
    super.initState();
    initSidebar(this);
    // Pakai dummy data dari produk_model.dart
    _produkList = List.from(dummyProdukList);
    _kategoriList = List.from(dummyKategoriList);
  }

  @override
  void dispose() {
    disposeSidebar();
    _searchCtrl.dispose();
    super.dispose();
  }

  // ── Filter ────────────────────────────────────────────────────────────────
  List<ProdukItem> get _filtered => _produkList.where((p) {
    final q = _searchCtrl.text.trim().toLowerCase();
    final matchSearch =
        q.isEmpty ||
        p.nama.toLowerCase().contains(q) ||
        p.kode.toLowerCase().contains(q);
    final matchKat =
        _filterKategori == 'Semua kategori' || p.kategori == _filterKategori;
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
  }).toList();

  List<String> get _kategoriOptions {
    final set = _produkList.map((p) => p.kategori).toSet().toList()..sort();
    return ['Semua kategori', ...set];
  }

  bool _kategoriHasProduk(String nama) =>
      _produkList.any((p) => p.kategori == nama);

  void _resetFilter() => setState(() {
    _searchCtrl.clear();
    _filterKategori = 'Semua kategori';
    _filterStatus = 'Semua status';
    _filterStok = 'Semua stok';
  });

  String _rupiah(int n) {
    final s = n.toString();
    final buf = StringBuffer('Rp ');
    for (int i = 0; i < s.length; i++) {
      if (i > 0 && (s.length - i) % 3 == 0) buf.write('.');
      buf.write(s[i]);
    }
    return buf.toString();
  }

  // ── Navigasi sidebar ──────────────────────────────────────
  void _handleMenuTap(String menu) {
    closeSidebar();
    if (menu == 'Produk') return;
    Widget? page;
    switch (menu) {
      case 'Dashboard':
        page = const BerandaPage();
        break;
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

  // ── Dialogs ───────────────────────────────────────────────────────────────
  void _showDetailModal(ProdukItem p) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => _DetailModal(produk: p, rupiah: _rupiah),
    );
  }

  void _showHapusProdukDialog(ProdukItem p) {
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
            onPressed: () {
              setState(() => _produkList.remove(p));
              Navigator.pop(context);
              _showSnack('Produk "${p.nama}" dihapus', Colors.red);
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
              setState(() => _kategoriList.remove(k));
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
      backgroundColor: const Color(0xFFF3F4F8),
      body: Stack(
        children: [
          SingleChildScrollView(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                _buildHeader(),
                const SizedBox(height: 16),
                _buildSummaryCards(),
                const SizedBox(height: 20),
                _buildFilterSection(),
                const SizedBox(height: 16),
                _buildActionButtons(),
                const SizedBox(height: 20),
                _buildProdukSection(filtered),
                const SizedBox(height: 24),
                _buildKategoriSection(),
                const SizedBox(height: 40),
              ],
            ),
          ),
          ...buildSidebarLayer(activeMenu: 'Produk', onMenuTap: _handleMenuTap),
        ],
      ),
    );
  }

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
                      const Text(
                        'Hallo, Alex',
                        style: TextStyle(
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

  Widget _buildSummaryCards() {
    return SizedBox(
      height: 110,
      child: ListView(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 16),
        children: [
          SummaryCard(
            label: 'Total Karyawan',
            value: '15',
            icon: Icons.people_alt_rounded,
            color: const Color(0xFF6B9FFF),
          ),
          SummaryCard(
            label: 'Total Produk',
            value: '${_produkList.length}',
            icon: Icons.inventory_2_rounded,
            color: const Color(0xFF48BB78),
          ),
          SummaryCard(
            label: 'Stok Hampir Habis',
            value:
                '${_produkList.where((p) => p.stok > 0 && p.stok < 20).length}',
            icon: Icons.warning_amber_rounded,
            color: const Color(0xFFECC94B),
          ),
          const SummaryCard(
            label: 'Akan Kadaluarsa',
            value: '23',
            icon: Icons.timer_rounded,
            color: Color(0xFFFC8181),
          ),
        ],
      ),
    );
  }

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
                    onChanged: (_) => setState(() {}),
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
                  onPressed: () => setState(() {}),
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
                  (v) => setState(() => _filterKategori = v!),
                ),
                _dd(_filterStatus, const [
                  'Semua status',
                  'Aktif',
                  'Nonaktif',
                ], (v) => setState(() => _filterStatus = v!)),
                _dd(_filterStok, const [
                  'Semua stok',
                  'Stok Normal',
                  'Stok Rendah',
                  'Stok Habis',
                ], (v) => setState(() => _filterStok = v!)),
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

  Widget _buildActionButtons() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Row(
        children: [
          Expanded(
            child: ElevatedButton.icon(
              onPressed: () => Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const TambahKategoriPage()),
              ),
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
              // ← Navigasi ke TambahProdukPage dari produk_form_page.dart
              onPressed: () => Navigator.push(
                context,
                MaterialPageRoute(builder: (_) => const TambahProdukPage()),
              ),
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

  Widget _buildProdukSection(List<ProdukItem> filtered) {
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
          if (filtered.isEmpty)
            _buildEmptyState()
          else
            Container(
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
              child: SingleChildScrollView(
                scrollDirection: Axis.horizontal,
                child: DataTable(
                  headingRowColor: WidgetStateProperty.all(
                    const Color(0xFFF7F8FA),
                  ),
                  headingRowHeight: 44,
                  dataRowMinHeight: 58,
                  dataRowMaxHeight: 66,
                  columnSpacing: 16,
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
                    DataColumn(label: Text('No')),
                    DataColumn(label: Text('Kode Produk')),
                    DataColumn(label: Text('Produk')),
                    DataColumn(label: Text('Kategori')),
                    DataColumn(label: Text('Jenis')),
                    DataColumn(label: Text('Harga Jual')),
                    DataColumn(label: Text('Stok')),
                    DataColumn(label: Text('Kadaluarsa')),
                    DataColumn(label: Text('Status')),
                    DataColumn(label: Text('Ketersediaan')),
                    DataColumn(label: Text('Aksi')),
                  ],
                  rows: List.generate(filtered.length, (i) {
                    final p = filtered[i];
                    final stokColor = p.stok == 0
                        ? const Color(0xFFE53E3E)
                        : p.stok < 20
                        ? const Color(0xFFECC94B)
                        : const Color(0xFF48BB78);
                    return DataRow(
                      cells: [
                        DataCell(Text('${i + 1}')),
                        DataCell(
                          Text(p.kode, style: const TextStyle(fontSize: 10)),
                        ),
                        DataCell(
                          SizedBox(
                            width: 130,
                            child: Text(
                              p.nama,
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                        ),
                        DataCell(Text(p.kategori)),
                        DataCell(Text(p.jenis)),
                        DataCell(Text(_rupiah(p.harga))),
                        DataCell(
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 10,
                              vertical: 4,
                            ),
                            decoration: BoxDecoration(
                              color: stokColor.withOpacity(0.12),
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: Text(
                              '${p.stok}',
                              style: TextStyle(
                                color: stokColor,
                                fontWeight: FontWeight.w600,
                                fontSize: 12,
                              ),
                            ),
                          ),
                        ),
                        DataCell(Text(p.kadaluarsa)),
                        DataCell(
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 10,
                              vertical: 4,
                            ),
                            decoration: BoxDecoration(
                              color: p.aktif
                                  ? const Color(0xFF48BB78).withOpacity(0.12)
                                  : Colors.grey.shade100,
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: Text(
                              p.aktif ? 'Aktif' : 'Nonaktif',
                              style: TextStyle(
                                color: p.aktif
                                    ? const Color(0xFF48BB78)
                                    : Colors.grey,
                                fontWeight: FontWeight.w600,
                                fontSize: 11,
                              ),
                            ),
                          ),
                        ),
                        DataCell(
                          p.stok > 0
                              ? Container(
                                  padding: const EdgeInsets.symmetric(
                                    horizontal: 10,
                                    vertical: 4,
                                  ),
                                  decoration: BoxDecoration(
                                    color: const Color(
                                      0xFF4169E1,
                                    ).withOpacity(0.1),
                                    borderRadius: BorderRadius.circular(12),
                                  ),
                                  child: const Text(
                                    'Tersedia',
                                    style: TextStyle(
                                      color: Color(0xFF4169E1),
                                      fontSize: 11,
                                      fontWeight: FontWeight.w600,
                                    ),
                                  ),
                                )
                              : Container(
                                  padding: const EdgeInsets.symmetric(
                                    horizontal: 10,
                                    vertical: 4,
                                  ),
                                  decoration: BoxDecoration(
                                    color: const Color(
                                      0xFFE53E3E,
                                    ).withOpacity(0.1),
                                    borderRadius: BorderRadius.circular(12),
                                  ),
                                  child: const Text(
                                    'Habis',
                                    style: TextStyle(
                                      color: Color(0xFFE53E3E),
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
                                icon: Icons.visibility_rounded,
                                color: const Color(0xFF4169E1),
                                label: 'Detail',
                                onTap: () => _showDetailModal(p),
                              ),
                              const SizedBox(width: 8),
                              // ← EditProdukPage terima ProdukItem
                              _AksiBtn(
                                icon: Icons.edit_rounded,
                                color: const Color(0xFFD69E2E),
                                label: 'Edit',
                                onTap: () => Navigator.push(
                                  context,
                                  MaterialPageRoute(
                                    builder: (_) => EditProdukPage(produk: p),
                                  ),
                                ),
                              ),
                              const SizedBox(width: 8),
                              _AksiBtn(
                                icon: Icons.delete_rounded,
                                color: const Color(0xFFE53E3E),
                                label: 'Hapus',
                                onTap: () => _showHapusProdukDialog(p),
                              ),
                            ],
                          ),
                        ),
                      ],
                    );
                  }),
                ),
              ),
            ),
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
          const SizedBox(height: 12),
          GridView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 2,
              crossAxisSpacing: 12,
              mainAxisSpacing: 12,
              childAspectRatio: 2.0,
            ),
            itemCount: _kategoriList.length,
            itemBuilder: (_, i) {
              final k = _kategoriList[i];
              final hasProduk = _kategoriHasProduk(k.nama);
              final jumlah = _produkList
                  .where((p) => p.kategori == k.nama)
                  .length;
              return Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 14,
                  vertical: 12,
                ),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withOpacity(0.05),
                      blurRadius: 6,
                      offset: const Offset(0, 2),
                    ),
                  ],
                ),
                child: Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(9),
                      decoration: BoxDecoration(
                        color: const Color(0xFF4169E1).withOpacity(0.12),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: const Icon(
                        Icons.label_rounded,
                        color: Color(0xFF4169E1),
                        size: 20,
                      ),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Text(
                            k.nama,
                            style: const TextStyle(
                              fontSize: 14,
                              fontWeight: FontWeight.w600,
                              color: Color(0xFF2D3748),
                            ),
                          ),
                          const SizedBox(height: 3),
                          Text(
                            '$jumlah produk',
                            style: TextStyle(
                              fontSize: 11,
                              color: Colors.grey.shade500,
                            ),
                          ),
                        ],
                      ),
                    ),
                    Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        _KatBtn(
                          icon: Icons.edit_rounded,
                          color: const Color(0xFFD69E2E),
                          label: 'edit',
                          onTap: () => Navigator.push(
                            context,
                            MaterialPageRoute(
                              builder: (_) => EditKategoriPage(kategori: k),
                            ),
                          ),
                        ),
                        const SizedBox(height: 6),
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
                    ),
                  ],
                ),
              );
            },
          ),
        ],
      ),
    );
  }
}

// ════════════════════════════════════════════════════════════════════════════
// DETAIL MODAL
// ════════════════════════════════════════════════════════════════════════════
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
                  color: const Color(0xFF4169E1).withOpacity(0.12),
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
                      ? const Color(0xFF48BB78).withOpacity(0.12)
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
// TAMBAH / EDIT KATEGORI PAGES
// ════════════════════════════════════════════════════════════════════════════
class TambahKategoriPage extends StatefulWidget {
  const TambahKategoriPage({super.key});
  @override
  State<TambahKategoriPage> createState() => _TambahKategoriPageState();
}

class _TambahKategoriPageState extends State<TambahKategoriPage> {
  final _n = TextEditingController();
  final _d = TextEditingController();
  @override
  Widget build(BuildContext context) => _KategoriScaffold(
    title: 'Tambah Kategori',
    color: const Color(0xFF6B5CE7),
    btnLabel: 'Simpan Kategori',
    namaCtrl: _n,
    deskCtrl: _d,
    onSave: () => Navigator.pop(context),
  );
}

class EditKategoriPage extends StatefulWidget {
  final KategoriItem kategori;
  const EditKategoriPage({super.key, required this.kategori});
  @override
  State<EditKategoriPage> createState() => _EditKategoriPageState();
}

class _EditKategoriPageState extends State<EditKategoriPage> {
  late TextEditingController _n;
  final _d = TextEditingController();
  @override
  void initState() {
    super.initState();
    _n = TextEditingController(text: widget.kategori.nama);
  }

  @override
  Widget build(BuildContext context) => _KategoriScaffold(
    title: 'Edit Kategori',
    color: const Color(0xFFD69E2E),
    btnLabel: 'Update Kategori',
    namaCtrl: _n,
    deskCtrl: _d,
    onSave: () => Navigator.pop(context),
  );
}

class _KategoriScaffold extends StatelessWidget {
  final String title, btnLabel;
  final Color color;
  final TextEditingController namaCtrl, deskCtrl;
  final VoidCallback onSave;

  const _KategoriScaffold({
    required this.title,
    required this.color,
    required this.btnLabel,
    required this.namaCtrl,
    required this.deskCtrl,
    required this.onSave,
  });

  @override
  Widget build(BuildContext context) => Scaffold(
    backgroundColor: const Color(0xFFF3F4F8),
    appBar: AppBar(
      backgroundColor: color,
      foregroundColor: Colors.white,
      elevation: 0,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(bottom: Radius.circular(20)),
      ),
      title: Text(title, style: const TextStyle(fontWeight: FontWeight.w600)),
      leading: IconButton(
        icon: const Icon(Icons.arrow_back_ios_new_rounded),
        onPressed: () => Navigator.pop(context),
      ),
    ),
    body: Padding(
      padding: const EdgeInsets.all(20),
      child: Column(
        children: [
          Container(
            padding: const EdgeInsets.all(20),
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
              children: [
                _katField('Nama Kategori', namaCtrl, 'Contoh: Makanan'),
                const SizedBox(height: 14),
                _katField(
                  'Deskripsi',
                  deskCtrl,
                  'Deskripsi (opsional)',
                  maxLines: 3,
                ),
              ],
            ),
          ),
          const SizedBox(height: 20),
          SizedBox(
            width: double.infinity,
            child: ElevatedButton(
              onPressed: onSave,
              style: ElevatedButton.styleFrom(
                backgroundColor: color,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(14),
                ),
                padding: const EdgeInsets.symmetric(vertical: 15),
              ),
              child: Text(
                btnLabel,
                style: const TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.w600,
                  fontSize: 15,
                ),
              ),
            ),
          ),
        ],
      ),
    ),
  );

  Widget _katField(
    String label,
    TextEditingController ctrl,
    String hint, {
    int maxLines = 1,
  }) => Column(
    crossAxisAlignment: CrossAxisAlignment.start,
    children: [
      Text(
        label,
        style: const TextStyle(
          fontSize: 13,
          fontWeight: FontWeight.w500,
          color: Color(0xFF4A5568),
        ),
      ),
      const SizedBox(height: 6),
      TextField(
        controller: ctrl,
        maxLines: maxLines,
        style: const TextStyle(fontSize: 13),
        decoration: InputDecoration(
          hintText: hint,
          hintStyle: TextStyle(color: Colors.grey.shade400, fontSize: 13),
          filled: true,
          fillColor: const Color(0xFFF8F9FA),
          contentPadding: const EdgeInsets.symmetric(
            horizontal: 14,
            vertical: 12,
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
            borderSide: const BorderSide(color: Color(0xFF4169E1), width: 1.5),
          ),
        ),
      ),
    ],
  );
}

// ════════════════════════════════════════════════════════════════════════════
// AKSI BUTTONS
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
            color: disabled ? Colors.grey.shade100 : color.withOpacity(0.15),
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
