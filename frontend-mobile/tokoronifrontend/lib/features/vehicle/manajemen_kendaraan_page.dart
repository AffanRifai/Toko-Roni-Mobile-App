import 'package:flutter/material.dart';
import 'package:tokoronifrontend/features/category/manajemen_kategori_page.dart';
import 'package:tokoronifrontend/features/delivery/manajemen_pengiriman_page.dart';
import 'package:tokoronifrontend/features/home/dashboard_page.dart';
import 'package:tokoronifrontend/features/member/daftar_member_page.dart';
import 'package:tokoronifrontend/features/product/daftar_produk_page.dart';
import 'package:tokoronifrontend/features/profile/profile_page.dart';
import 'package:tokoronifrontend/features/report/laporan_penjualan_page.dart';
import 'package:tokoronifrontend/features/transaction/kasir_page.dart';
import 'package:tokoronifrontend/features/transaction/riwayat_transaksi_page.dart';
import 'package:tokoronifrontend/features/user/manajemen_pengguna_page.dart';

import '../../core/services/vehicle_service.dart';
import '../../shared/widgets/notifikasi_widget.dart';
import '../../shared/widgets/profile_widget.dart';
import '../../shared/widgets/semua_notifikasi_page.dart';
import '../../shared/widgets/shared_widgets.dart';
import 'detail_kendaraan_page.dart';
import 'edit_kendaraan_page.dart';
import '../../models/kendaraan_model.dart';
import 'tambah_kendaraan_page.dart';

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
  final _searchCtrl = TextEditingController();

  List<KendaraanItem> _data = [];
  bool _isLoading = true;
  bool _hasError = false;
  String _errorMessage = '';

  String _filterStatus = 'Semua';
  String _filterJenis = 'Semua Jenis';

  @override
  void initState() {
    super.initState();
    initSidebar(this);
    _loadAllData();
  }

  @override
  void dispose() {
    disposeSidebar();
    _searchCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadAllData({bool showLoading = true}) async {
    if (!mounted) return;

    if (showLoading) {
      setState(() {
        _isLoading = true;
        _hasError = false;
        _errorMessage = '';
      });
    }

    try {
      final list = await VehicleService.getVehicles(perPage: 300);
      if (!mounted) return;
      setState(() {
        _data = list;
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

  List<KendaraanItem> get _filtered {
    final q = _searchCtrl.text.trim().toLowerCase();
    return _data.where((k) {
      final match =
          q.isEmpty ||
          k.kode.toLowerCase().contains(q) ||
          k.id.toString().contains(q) ||
          k.nama.toLowerCase().contains(q) ||
          k.platNomor.toLowerCase().contains(q);

      final matchStatus = _filterStatus == 'Semua' || k.status == _filterStatus;
      final matchJenis =
          _filterJenis == 'Semua Jenis' || k.jenis == _filterJenis;
      return match && matchStatus && matchJenis;
    }).toList();
  }

  int get _total => _data.length;
  int get _tersedia => _data.where((k) => k.status == 'Tersedia').length;
  int get _digunakan =>
      _data.where((k) => k.status == 'Sedang Digunakan').length;
  int get _servis => _data.where((k) => k.status == 'Servis').length;

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

  void _resetFilter() {
    setState(() {
      _searchCtrl.clear();
      _filterStatus = 'Semua';
      _filterJenis = 'Semua Jenis';
    });
  }

  Future<void> _goTambah() async {
    final result = await Navigator.push<KendaraanItem>(
      context,
      MaterialPageRoute(builder: (_) => const TambahKendaraanPage()),
    );

    if (result == null || !mounted) return;

    setState(() {
      _data.removeWhere((e) => e.id == result.id);
      _data.insert(0, result);
    });

    _snack(
      'Kendaraan ${result.nama} berhasil ditambahkan',
      const Color(0xFF48BB78),
    );

    await _loadAllData(showLoading: false);
  }

  Future<void> _goEdit(KendaraanItem kendaraan) async {
    final result = await Navigator.push<KendaraanItem>(
      context,
      MaterialPageRoute(
        builder: (_) => EditKendaraanPage(kendaraan: kendaraan),
      ),
    );

    if (result == null || !mounted) return;

    setState(() {
      final idx = _data.indexWhere((e) => e.id == result.id);
      if (idx >= 0) {
        _data[idx] = result;
      }
    });

    _snack(
      'Kendaraan ${result.nama} berhasil diperbarui',
      const Color(0xFF48BB78),
    );

    await _loadAllData(showLoading: false);
  }

  void _goDetail(KendaraanItem kendaraan) {
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => DetailKendaraanPage(
          kendaraan: kendaraan,
          onEdit: () => _goEdit(kendaraan),
          onStatusChange: (statusBaru) {
            setState(() {
              kendaraan.status = statusBaru;
            });
          },
          onRefreshRequested: () => _loadAllData(showLoading: false),
        ),
      ),
    );
  }

  void _hapus(KendaraanItem kendaraan) {
    showDialog(
      context: context,
      builder: (_) => _ConfirmDialog(
        icon: Icons.delete_forever_rounded,
        iconColor: const Color(0xFFE53E3E),
        title: 'Hapus Kendaraan?',
        message:
            '${kendaraan.nama} (${kendaraan.platNomor}) akan dihapus.\nTindakan ini tidak dapat dibatalkan.',
        confirmLabel: 'Ya, Hapus',
        confirmColor: const Color(0xFFE53E3E),
        onConfirm: () async {
          try {
            await VehicleService.deleteVehicle(vehicleId: kendaraan.id);
            if (!mounted) return;

            setState(() {
              _data.removeWhere((e) => e.id == kendaraan.id);
            });

            _snack(
              'Kendaraan ${kendaraan.nama} dihapus',
              const Color(0xFFE53E3E),
            );
          } catch (e) {
            if (!mounted) return;
            _snack(
              e.toString().replaceFirst('Exception: ', ''),
              const Color(0xFFE53E3E),
            );
          }
        },
      ),
    );
  }

  void _snack(String msg, Color color) {
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

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF3F4F8),
      body: Stack(
        children: [
          RefreshIndicator(
            onRefresh: () => _loadAllData(showLoading: false),
            child: SingleChildScrollView(
              physics: const AlwaysScrollableScrollPhysics(),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _buildHeader(),
                  const SizedBox(height: 20),
                  _buildFilter(),
                  const SizedBox(height: 16),
                  if (_hasError && _data.isNotEmpty) _buildSyncWarning(),
                  _buildTable(_filtered),
                  const SizedBox(height: 40),
                ],
              ),
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
                    ProfileWidget.fromAuth(onTap: () {}),
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
              hintText: 'Cari kode, nama, atau plat nomor...',
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
                statusFilterKendaraanList,
                (v) => setState(() => _filterStatus = v ?? 'Semua'),
              ),
              _ddWidget(
                _filterJenis,
                jenisFilterKendaraanList,
                (v) => setState(() => _filterJenis = v ?? 'Semua Jenis'),
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
          child: _isLoading
              ? const Padding(
                  padding: EdgeInsets.symmetric(vertical: 36),
                  child: Center(
                    child: CircularProgressIndicator(color: Color(0xFF4169E1)),
                  ),
                )
              : (_hasError && _data.isEmpty)
              ? Padding(
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
                          'Gagal memuat data kendaraan',
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
              : list.isEmpty
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
                      DataColumn(label: Text('KODE')),
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
                                  k.kode,
                                  style: const TextStyle(
                                    fontSize: 11,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                              ),
                              DataCell(
                                SizedBox(
                                  width: 160,
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
                                              .join(' | '),
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

  Widget _statusBadge(String status) {
    final color = kendaraanStatusColor(status);
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
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

  Widget _jenisBadge(String jenis) {
    const colorMap = {
      'Motor': Color(0xFF3B6FE8),
      'Mobil Pick-up': Color(0xFF6B5CE7),
      'Van': Color(0xFF2B6CB0),
      'Truck': Color(0xFFED8936),
    };

    final color = colorMap[jenis] ?? const Color(0xFF4A5568);

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
      decoration: BoxDecoration(
        color: color.withOpacity(0.10),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Text(
        jenis,
        style: TextStyle(
          fontSize: 11,
          fontWeight: FontWeight.w600,
          color: color,
        ),
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
  final String title;
  final String message;
  final String confirmLabel;
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
