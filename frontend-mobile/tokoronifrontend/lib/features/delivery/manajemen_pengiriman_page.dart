import 'package:flutter/material.dart';
import 'package:tokoronifrontend/features/category/manajemen_kategori_page.dart';
import 'package:tokoronifrontend/features/home/dashboard_page.dart';
import 'package:tokoronifrontend/features/member/daftar_member_page.dart';
import 'package:tokoronifrontend/features/product/daftar_produk_page.dart';
import 'package:tokoronifrontend/features/profile/profile_page.dart';
import 'package:tokoronifrontend/features/report/laporan_penjualan_page.dart';
import 'package:tokoronifrontend/features/transaction/kasir_page.dart';
import 'package:tokoronifrontend/features/transaction/riwayat_transaksi_page.dart';
import 'package:tokoronifrontend/features/user/manajemen_pengguna_page.dart';
import 'package:tokoronifrontend/features/vehicle/manajemen_kendaraan_page.dart';
import '../../core/services/delivery_service.dart';
import '../../shared/widgets/notifikasi_widget.dart';
import '../../shared/widgets/profile_widget.dart';
import '../../shared/widgets/semua_notifikasi_page.dart';
import '../../shared/widgets/shared_widgets.dart';
import 'detail_pengiriman_page.dart';
import '../../models/pengiriman_model.dart';
import 'tambah_pengiriman_page.dart';

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

  final _searchCtrl = TextEditingController();
  final _dariCtrl = TextEditingController();
  final _sampaiCtrl = TextEditingController();

  List<PengirimanItem> _data = [];
  List<DeliveryDriverOption> _drivers = [];
  List<DeliveryVehicleOption> _vehicles = [];

  bool _isLoading = true;
  bool _hasError = false;
  String _errorMessage = '';

  String _filterStatus = 'Semua';
  String _filterKurir = 'Semua Kurir';
  DateTime? _dariTgl;
  DateTime? _sampaiTgl;

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
    _dariCtrl.dispose();
    _sampaiCtrl.dispose();
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
      final deliveries = await DeliveryService.getDeliveries(perPage: 300);
      List<DeliveryDriverOption> drivers = [];
      List<DeliveryVehicleOption> vehicles = [];

      try {
        drivers = await DeliveryService.getAvailableDrivers();
      } catch (_) {}
      try {
        vehicles = await DeliveryService.getAvailableVehicles();
      } catch (_) {}

      if (!mounted) return;
      setState(() {
        _data = deliveries;
        _drivers = drivers;
        _vehicles = vehicles;
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

  List<String> get _kurirFilterList {
    final names = <String>{};
    for (final d in _drivers) {
      final name = d.name.trim();
      if (name.isNotEmpty) names.add(name);
    }
    for (final p in _data) {
      final name = (p.namaKurir ?? '').trim();
      if (name.isNotEmpty) names.add(name);
    }
    final list = names.toList()..sort();
    return ['Semua Kurir', ...list];
  }

  List<PengirimanItem> get _filtered {
    final q = _searchCtrl.text.trim().toLowerCase();
    final dari = _dariTgl == null
        ? null
        : DateTime(_dariTgl!.year, _dariTgl!.month, _dariTgl!.day);
    final sampai = _sampaiTgl == null
        ? null
        : DateTime(
            _sampaiTgl!.year,
            _sampaiTgl!.month,
            _sampaiTgl!.day,
            23,
            59,
            59,
          );

    return _data.where((p) {
      final matchSearch =
          q.isEmpty ||
          p.kodePengiriman.toLowerCase().contains(q) ||
          p.invoice.toLowerCase().contains(q) ||
          p.tujuan.toLowerCase().contains(q) ||
          p.namaCustomer.toLowerCase().contains(q);
      final matchStatus = _filterStatus == 'Semua' || p.status == _filterStatus;
      final matchKurir =
          _filterKurir == 'Semua Kurir' ||
          (p.namaKurir?.toLowerCase() == _filterKurir.toLowerCase());
      final created = p.createdAt;
      final matchDate =
          (dari == null || !created.isBefore(dari)) &&
          (sampai == null || !created.isAfter(sampai));
      return matchSearch && matchStatus && matchKurir && matchDate;
    }).toList();
  }

  int get _total => _data.length;
  int get _menunggu => _data
      .where((p) => p.status == 'Pending' || p.status == 'Diproses')
      .length;
  int get _dalamPerjalanan => _data
      .where(
        (p) =>
            p.status == 'Assigned' ||
            p.status == 'Diambil' ||
            p.status == 'Dalam Perjalanan',
      )
      .length;
  int get _terkirim => _data.where((p) => p.status == 'Terkirim').length;
  int get _gagal => _data
      .where((p) => p.status == 'Gagal' || p.status == 'Dibatalkan')
      .length;

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
      _dariCtrl.clear();
      _sampaiCtrl.clear();
      _filterStatus = 'Semua';
      _filterKurir = 'Semua Kurir';
      _dariTgl = null;
      _sampaiTgl = null;
    });
  }

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

    if (picked == null || !mounted) return;
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

  Future<void> _refreshSingleDelivery(int deliveryId) async {
    try {
      final latest = await DeliveryService.getDeliveryDetail(
        deliveryId: deliveryId,
      );
      if (!mounted) return;
      setState(() {
        final idx = _data.indexWhere((e) => e.id == deliveryId);
        if (idx >= 0) _data[idx] = latest;
      });
    } catch (_) {
      await _loadAllData();
    }
  }

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
            onConfirm: () async {
              Navigator.pop(context);
              try {
                await DeliveryService.updateStatus(
                  deliveryId: p.id,
                  statusApi: 'cancelled',
                );
                if (!mounted) return;
                setState(() {
                  p.status = 'Dibatalkan';
                  p.statusApi = 'cancelled';
                });
                _snack(
                  'Pengiriman ${p.kodePengiriman} dibatalkan',
                  Colors.orange,
                );
                await _refreshSingleDelivery(p.id);
              } catch (e) {
                if (!mounted) return;
                _snack(
                  e.toString().replaceFirst('Exception: ', ''),
                  const Color(0xFFE53E3E),
                );
              }
            },
            confirmLabel: 'Ya, Batalkan',
            confirmColor: const Color(0xFFE53E3E),
          ),
        ],
      ),
    );
  }

  void _showAssignKurir(PengirimanItem p) {
    if (_drivers.isEmpty) {
      _snack(
        'Daftar kurir tidak tersedia. Coba refresh lalu ulangi.',
        const Color(0xFFE53E3E),
      );
      return;
    }

    int? selectedDriverId = p.kurirId;
    int? selectedVehicleId = p.kendaraanId;

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
                      const Row(
                        children: [
                          Icon(
                            Icons.info_outline_rounded,
                            color: _blue,
                            size: 14,
                          ),
                          SizedBox(width: 6),
                          Text(
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
                const Row(
                  children: [
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
                    child: DropdownButton<int>(
                      value: selectedDriverId,
                      isExpanded: true,
                      hint: Text(
                        '-- Pilih Kurir --',
                        style: TextStyle(
                          color: Colors.grey.shade400,
                          fontSize: 13,
                        ),
                      ),
                      items: _drivers
                          .map(
                            (d) => DropdownMenuItem<int>(
                              value: d.id,
                              child: Text(
                                d.phone.isEmpty
                                    ? d.name
                                    : '${d.name} (${d.phone})',
                                style: const TextStyle(fontSize: 13),
                              ),
                            ),
                          )
                          .toList(),
                      onChanged: (v) =>
                          setModalState(() => selectedDriverId = v),
                    ),
                  ),
                ),
                const SizedBox(height: 14),
                const Row(
                  children: [
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
                    child: DropdownButton<int>(
                      value: selectedVehicleId,
                      isExpanded: true,
                      hint: Text(
                        _vehicles.isEmpty
                            ? 'Tidak ada kendaraan tersedia'
                            : '-- Pilih Kendaraan --',
                        style: TextStyle(
                          color: Colors.grey.shade400,
                          fontSize: 13,
                        ),
                      ),
                      items: _vehicles
                          .map(
                            (v) => DropdownMenuItem<int>(
                              value: v.id,
                              child: Text(
                                v.displayLabel,
                                style: const TextStyle(fontSize: 12),
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                          )
                          .toList(),
                      onChanged: _vehicles.isEmpty
                          ? null
                          : (v) => setModalState(() => selectedVehicleId = v),
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
                    onPressed: () async {
                      if (selectedDriverId == null) {
                        _snack('Pilih kurir terlebih dahulu', Colors.red);
                        return;
                      }
                      try {
                        await DeliveryService.assignDelivery(
                          deliveryId: p.id,
                          userId: selectedDriverId!,
                          vehicleId: selectedVehicleId,
                        );
                        if (!mounted) return;
                        final driver = _drivers.firstWhere(
                          (d) => d.id == selectedDriverId,
                          orElse: () => const DeliveryDriverOption(
                            id: 0,
                            name: '',
                            phone: '',
                          ),
                        );
                        final vehicle = _vehicles.firstWhere(
                          (v) => v.id == selectedVehicleId,
                          orElse: () => const DeliveryVehicleOption(
                            id: 0,
                            name: '',
                            plate: '',
                            type: '',
                            status: '',
                          ),
                        );
                        setState(() {
                          p.kurirId = selectedDriverId;
                          p.kendaraanId = selectedVehicleId;
                          p.namaKurir = driver.name.isEmpty
                              ? null
                              : driver.name;
                          p.nomorKurir = driver.phone.isEmpty
                              ? null
                              : driver.phone;
                          if (vehicle.id != 0) {
                            p.kendaraan = vehicle.displayLabel;
                          }
                          p.status = 'Assigned';
                          p.statusApi = 'assigned';
                        });
                        Navigator.of(context).pop();
                        _snack(
                          'Kurir berhasil di-assign ke ${p.kodePengiriman}',
                          const Color(0xFF48BB78),
                        );
                        await _refreshSingleDelivery(p.id);
                      } catch (e) {
                        if (!mounted) return;
                        _snack(
                          e.toString().replaceFirst('Exception: ', ''),
                          const Color(0xFFE53E3E),
                        );
                      }
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

  void _cetakSuratJalan(PengirimanItem p) {
    _snack(
      'Mencetak surat jalan ${p.kodePengiriman}...',
      const Color(0xFF48BB78),
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
    final filtered = _filtered;
    final activeKurir = _kurirFilterList.contains(_filterKurir)
        ? _filterKurir
        : 'Semua Kurir';

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
                  _buildFilter(activeKurir),
                  const SizedBox(height: 16),
                  if (_hasError && _data.isNotEmpty) _buildSyncWarning(),
                  _buildTable(filtered),
                  const SizedBox(height: 40),
                ],
              ),
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
                Wrap(
                  spacing: 10,
                  runSpacing: 8,
                  children: [
                    ElevatedButton.icon(
                      onPressed: () async {
                        final result = await Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (_) => const TambahPengirimanPage(),
                          ),
                        );
                        if (!mounted) return;
                        if (result is String && result.isNotEmpty) {
                          _snack(result, const Color(0xFF48BB78));
                        }
                        if (result != null) await _loadAllData();
                      },
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
                    ElevatedButton.icon(
                      onPressed: () {
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

  Widget _buildFilter(String activeKurir) => Padding(
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
          TextField(
            controller: _searchCtrl,
            onChanged: (_) => setState(() {}),
            style: const TextStyle(fontSize: 13),
            decoration: InputDecoration(
              hintText: 'Cari kode, invoice, tujuan, customer...',
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
              _dd(
                _filterStatus,
                _statusFilterList,
                (v) => setState(() => _filterStatus = v!),
              ),
              _dd(
                activeKurir,
                _kurirFilterList,
                (v) => setState(() => _filterKurir = v!),
              ),
              _dateInput(_dariCtrl, 'Dari', () => _pickDate(true)),
              _dateInput(_sampaiCtrl, 'Sampai', () => _pickDate(false)),
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
                          'Gagal memuat data pengiriman',
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
                    rows: list.asMap().entries.map((entry) {
                      final index = entry.key;
                      final p = entry.value;
                      return DataRow(
                        cells: [
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
                          DataCell(
                            SizedBox(
                              width: 145,
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
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
                          DataCell(_statusBadge(p.status)),
                          DataCell(
                            Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                _AksiBtn(
                                  icon: Icons.visibility_rounded,
                                  color: const Color(0xFF4169E1),
                                  label: 'Detail',
                                  onTap: () async {
                                    final changed = await Navigator.push(
                                      context,
                                      MaterialPageRoute(
                                        builder: (_) =>
                                            DetailPengirimanPage(pengiriman: p),
                                      ),
                                    );
                                    if (!mounted) return;
                                    if (changed == true) {
                                      await _refreshSingleDelivery(p.id);
                                    }
                                  },
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
                    }).toList(),
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
