// lib/transaction/laporan_penjualan_page.dart
import 'dart:math' as math;
import 'package:flutter/material.dart';
import 'package:tokoronifrontend/features/delivery/manajemen_pengiriman_page.dart';
import 'package:tokoronifrontend/features/home/dashboard_page.dart';
import 'package:tokoronifrontend/features/profile/profile_page.dart';
import 'package:tokoronifrontend/features/transaction/kasir_page.dart';
import 'package:tokoronifrontend/features/transaction/riwayat_transaksi_page.dart';
import 'package:tokoronifrontend/features/vehicle/manajemen_kendaraan_page.dart';
import '../../shared/widgets/shared_widgets.dart';
import '../../shared/widgets/notifikasi_widget.dart';
import '../../shared/widgets/semua_notifikasi_page.dart';
import '../../shared/widgets/profile_widget.dart';
import '../../core/services/report_service.dart';
import '../../core/services/transaction_service.dart';
import '../../models/transaction_api_model.dart';
import '../member/daftar_member_page.dart';
import '../user/manajemen_pengguna_page.dart';
import '../product/daftar_produk_page.dart';
import '../category/manajemen_kategori_page.dart';
import '../transaction/detail_transaksi_page.dart';

// ════════════════════════════════════════════════════════════════════════════
// MODEL
// ════════════════════════════════════════════════════════════════════════════
class TransaksiLaporanItem {
  final int no;
  final int transactionId;
  final DateTime createdAt;
  final String tanggal;
  final String jam;
  final String invoice;
  final int invoiceId;
  final String kasir;
  final String customer;
  final String pembayaran;
  final String status;
  final int total;
  final int jumlahItem;
  final String noTelepon;
  final int diskonPersen;
  final int cashDiterima;
  final bool isMember;
  final String? memberNama;
  final String? alamatPelanggan;
  final List<DetailProdukTransaksi> produkList;

  const TransaksiLaporanItem({
    required this.no,
    required this.transactionId,
    required this.createdAt,
    required this.tanggal,
    required this.jam,
    required this.invoice,
    required this.invoiceId,
    required this.kasir,
    required this.customer,
    required this.pembayaran,
    required this.status,
    required this.total,
    required this.jumlahItem,
    required this.noTelepon,
    required this.diskonPersen,
    required this.cashDiterima,
    required this.isMember,
    required this.memberNama,
    required this.alamatPelanggan,
    required this.produkList,
  });

  TransaksiLaporanItem copyWith({int? no}) {
    return TransaksiLaporanItem(
      no: no ?? this.no,
      transactionId: transactionId,
      createdAt: createdAt,
      tanggal: tanggal,
      jam: jam,
      invoice: invoice,
      invoiceId: invoiceId,
      kasir: kasir,
      customer: customer,
      pembayaran: pembayaran,
      status: status,
      total: total,
      jumlahItem: jumlahItem,
      noTelepon: noTelepon,
      diskonPersen: diskonPersen,
      cashDiterima: cashDiterima,
      isMember: isMember,
      memberNama: memberNama,
      alamatPelanggan: alamatPelanggan,
      produkList: produkList,
    );
  }
}

// ── Dummy data ────────────────────────────────────────────────────────────────

// ── Helper format Rupiah ──────────────────────────────────────────────────────
String _rp(int n) {
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
class LaporanPenjualanPage extends StatefulWidget {
  final String userName;
  final String userRole;

  const LaporanPenjualanPage({
    super.key,
    this.userName = '',
    this.userRole = '',
  });

  @override
  State<LaporanPenjualanPage> createState() => _LaporanPenjualanPageState();
}

class _LaporanPenjualanPageState extends State<LaporanPenjualanPage>
    with SingleTickerProviderStateMixin, SidebarMixin {
  // ── Filter state ──────────────────────────────────────────────────────────
  final _tanggalCtrl = TextEditingController();
  final _bulanCtrl = TextEditingController();
  String _urutkan = 'Terbaru';
  static const _urutkanList = [
    'Terbaru',
    'Terlama',
    'Total Tertinggi',
    'Total Terendah',
  ];

  DateTime? _filterTanggal;
  DateTime? _filterBulan;
  static const int _rowsPerPage = 10;
  int _currentPage = 1;

  // ── Data ──────────────────────────────────────────────────────────────────
  List<TransaksiLaporanItem> _data = [];
  bool _isLoading = false;
  bool _isExportingPdf = false;
  int? _openingDetailId;
  List<TransaksiLaporanItem> get _filtered {
    var list = List<TransaksiLaporanItem>.from(_data);

    // Filter berdasarkan tanggal spesifik
    if (_filterTanggal != null) {
      final target = DateTime(
        _filterTanggal!.year,
        _filterTanggal!.month,
        _filterTanggal!.day,
      );
      list = list.where((t) {
        final current = DateTime(
          t.createdAt.year,
          t.createdAt.month,
          t.createdAt.day,
        );
        return current == target;
      }).toList();
    }

    // Filter berdasarkan bulan & tahun
    if (_filterBulan != null) {
      list = list
          .where(
            (t) =>
                t.createdAt.month == _filterBulan!.month &&
                t.createdAt.year == _filterBulan!.year,
          )
          .toList();
    }

    // Urutkan
    switch (_urutkan) {
      case 'Terbaru':
        list.sort((a, b) => b.createdAt.compareTo(a.createdAt));
        break;
      case 'Terlama':
        list.sort((a, b) => a.createdAt.compareTo(b.createdAt));
        break;
      case 'Total Tertinggi':
        list.sort((a, b) => b.total.compareTo(a.total));
        break;
      case 'Total Terendah':
        list.sort((a, b) => a.total.compareTo(b.total));
        break;
    }
    return list;
  }

  String _getMonthName(int month) {
    const months = [
      'Jan',
      'Feb',
      'Mar',
      'Apr',
      'Mei',
      'Jun',
      'Jul',
      'Agu',
      'Sep',
      'Okt',
      'Nov',
      'Des',
    ];
    return months[month - 1];
  }

  // ── Stats ─────────────────────────────────────────────────────────────────
  int get _totalOmzet => _filtered.fold(0, (s, t) => s + t.total);
  int get _rataRata =>
      _filtered.isEmpty ? 0 : (_totalOmzet / _filtered.length).round();
  int get _transaksiTertinggi => _filtered.isEmpty
      ? 0
      : _filtered.map((t) => t.total).reduce((a, b) => a > b ? a : b);
  int get _jumlahTransaksi => _filtered.length;

  @override
  void initState() {
    super.initState();
    initSidebar(this);
    _applyDefaultMonthFilter();
    _loadTransactions();
  }

  void _applyDefaultMonthFilter() {
    final now = DateTime.now();
    _filterBulan = DateTime(now.year, now.month, 1);
    _bulanCtrl.text = '${_getMonthName(now.month)} ${now.year}';
  }

  Future<void> _loadTransactions() async {
    setState(() => _isLoading = true);
    try {
      final transactions = await TransactionService.getTransactions(
        perPage: 1000,
      );
      if (!mounted) return;
      final mapped = transactions.map((trx) => _mapApiToLaporan(trx)).toList()
        ..sort((a, b) => b.createdAt.compareTo(a.createdAt));

      for (var i = 0; i < mapped.length; i++) {
        mapped[i] = mapped[i].copyWith(no: i + 1);
      }

      setState(() {
        _data = mapped;
        _currentPage = 1;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() => _data = []);
      final msg = e.toString().replaceFirst('Exception: ', '').trim();
      _showSnack(
        msg.isEmpty ? 'Gagal memuat data laporan penjualan' : msg,
        Colors.red,
      );
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  TransaksiLaporanItem _mapApiToLaporan(TransactionApiItem trx) {
    final date = trx.createdAt;
    final day = date.day.toString().padLeft(2, '0');
    final hour = date.hour.toString().padLeft(2, '0');
    final minute = date.minute.toString().padLeft(2, '0');

    return TransaksiLaporanItem(
      no: 0,
      transactionId: trx.id,
      createdAt: date,
      tanggal: '$day ${_getMonthName(date.month)} ${date.year}',
      jam: '$hour:$minute',
      invoice: trx.invoiceNumber,
      invoiceId: trx.id,
      kasir: trx.cashierName.isEmpty ? '-' : trx.cashierName,
      customer: trx.customerName.isEmpty ? 'Pelanggan Umum' : trx.customerName,
      pembayaran: trx.paymentMethodLabel,
      status: trx.statusLabel,
      total: trx.totalAmount,
      jumlahItem: trx.itemCount,
      noTelepon: trx.customerPhone,
      diskonPersen: trx.discountPercent.round(),
      cashDiterima: trx.cashReceived,
      isMember: trx.memberId != null,
      memberNama: trx.memberName.isEmpty ? null : trx.memberName,
      alamatPelanggan: trx.memberAddress.isEmpty ? null : trx.memberAddress,
      produkList: trx.items
          .map(
            (item) => DetailProdukTransaksi(
              kode: item.productCode,
              nama: item.productName,
              kategori: item.categoryName.isEmpty ? '-' : item.categoryName,
              harga: item.price,
              qty: item.qty,
            ),
          )
          .toList(),
    );
  }

  @override
  void dispose() {
    disposeSidebar();
    _tanggalCtrl.dispose();
    _bulanCtrl.dispose();
    super.dispose();
  }

  void _handleMenuTap(String menu) {
    if (menu == 'Laporan') {
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

  // ── Date pickers ──────────────────────────────────────────────────────────
  Future<void> _pickTanggal() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: _filterTanggal ?? DateTime.now(),
      firstDate: DateTime(2024),
      lastDate: DateTime.now(),
      builder: (ctx, child) => Theme(
        data: Theme.of(ctx).copyWith(
          colorScheme: const ColorScheme.light(
            primary: Color(0xFF3B6FE8),
            onPrimary: Colors.white,
          ),
        ),
        child: child!,
      ),
    );
    if (picked != null) {
      setState(() {
        _filterTanggal = picked;
        _currentPage = 1;
        _tanggalCtrl.text =
            '${picked.day.toString().padLeft(2, '0')}/${picked.month.toString().padLeft(2, '0')}/${picked.year}';
      });
    }
  }

  Future<void> _pickBulan() async {
    final now = DateTime.now();
    // Pakai date picker biasa, ambil bulan + tahun saja
    final picked = await showDatePicker(
      context: context,
      initialDate: _filterBulan ?? now,
      firstDate: DateTime(2024),
      lastDate: now,
      builder: (ctx, child) => Theme(
        data: Theme.of(ctx).copyWith(
          colorScheme: const ColorScheme.light(
            primary: Color(0xFF3B6FE8),
            onPrimary: Colors.white,
          ),
        ),
        child: child!,
      ),
    );
    if (picked != null) {
      const months = [
        'Jan',
        'Feb',
        'Mar',
        'Apr',
        'Mei',
        'Jun',
        'Jul',
        'Agu',
        'Sep',
        'Okt',
        'Nov',
        'Des',
      ];
      setState(() {
        _filterBulan = picked;
        _currentPage = 1;
        _bulanCtrl.text = '${months[picked.month - 1]} ${picked.year}';
      });
    }
  }

  void _resetFilter() {
    final now = DateTime.now();
    setState(() {
      _tanggalCtrl.clear();
      _filterTanggal = null;
      _filterBulan = DateTime(now.year, now.month, 1);
      _bulanCtrl.text = '${_getMonthName(now.month)} ${now.year}';
      _urutkan = 'Terbaru';
      _currentPage = 1;
    });
    _showSnack('Filter direset', const Color(0xFF3B6FE8));
  }

  void _showSnack(String msg, Color color) =>
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

  // ── Export PDF ────────────────────────────────────────────────────────────
  Future<void> _exportPDF() async {
    if (_isExportingPdf) return;

    setState(() => _isExportingPdf = true);
    try {
      final result = await ReportService.exportSalesPdf(
        date: _toIsoDate(_filterTanggal),
        month: _toIsoMonth(_filterBulan),
        sort: _mapSortToApi(),
      );
      if (!mounted) return;

      final opened = await ReportService.openPdf(result.filePath);
      if (!mounted) return;

      if (opened) {
        _showSnack('Laporan PDF berhasil dibuka', const Color(0xFF48BB78));
      } else {
        _showSnack(
          'PDF tersimpan di: ${result.filePath}',
          const Color(0xFF48BB78),
        );
      }
    } catch (e) {
      if (!mounted) return;
      final msg = e.toString().replaceFirst('Exception: ', '').trim();
      _showSnack(msg.isEmpty ? 'Gagal export laporan PDF' : msg, Colors.red);
    } finally {
      if (mounted) {
        setState(() => _isExportingPdf = false);
      }
    }
  }

  String _mapSortToApi() {
    switch (_urutkan) {
      case 'Terlama':
        return 'oldest';
      case 'Total Tertinggi':
        return 'highest';
      case 'Total Terendah':
        return 'lowest';
      default:
        return 'latest';
    }
  }

  String? _toIsoDate(DateTime? date) {
    if (date == null) return null;
    final month = date.month.toString().padLeft(2, '0');
    final day = date.day.toString().padLeft(2, '0');
    return '${date.year}-$month-$day';
  }

  String? _toIsoMonth(DateTime? date) {
    if (date == null) return null;
    final month = date.month.toString().padLeft(2, '0');
    return '${date.year}-$month';
  }

  Future<void> _openDetail(TransaksiLaporanItem t) async {
    if (t.transactionId <= 0) {
      await Navigator.push(
        context,
        MaterialPageRoute(
          builder: (_) => DetailTransaksiPage(transaksi: _toDetailFromItem(t)),
        ),
      );
      return;
    }

    if (_openingDetailId == t.transactionId) return;
    setState(() => _openingDetailId = t.transactionId);
    try {
      final detail = await TransactionService.getTransactionDetail(
        transactionId: t.transactionId,
      );
      if (!mounted) return;
      final pageData = _toDetailTransaksiData(detail);
      await Navigator.push(
        context,
        MaterialPageRoute(
          builder: (_) => DetailTransaksiPage(transaksi: pageData),
        ),
      );
    } catch (e) {
      if (!mounted) return;
      final msg = e.toString().replaceFirst('Exception: ', '').trim();
      _showSnack(
        msg.isEmpty ? 'Gagal membuka detail transaksi' : msg,
        Colors.red,
      );
    } finally {
      if (mounted) {
        setState(() => _openingDetailId = null);
      }
    }
  }

  DetailTransaksiData _toDetailTransaksiData(TransactionApiItem trx) {
    final day = trx.createdAt.day.toString().padLeft(2, '0');
    final month = trx.createdAt.month.toString().padLeft(2, '0');
    final hour = trx.createdAt.hour.toString().padLeft(2, '0');
    final minute = trx.createdAt.minute.toString().padLeft(2, '0');
    return DetailTransaksiData(
      transactionId: trx.id,
      invoice: trx.invoiceNumber,
      tanggal: '$day/$month/${trx.createdAt.year}',
      waktu: '$hour:$minute',
      kasir: trx.cashierName.isEmpty ? '-' : trx.cashierName,
      status: trx.statusLabel,
      namaPelanggan: trx.customerName.isEmpty
          ? 'Pelanggan Umum'
          : trx.customerName,
      noTelepon: trx.customerPhone,
      metodePembayaran: trx.paymentMethodLabel,
      produkList: trx.items
          .map(
            (item) => DetailProdukTransaksi(
              kode: item.productCode,
              nama: item.productName,
              kategori: item.categoryName.isEmpty ? '-' : item.categoryName,
              harga: item.price,
              qty: item.qty,
            ),
          )
          .toList(),
      diskonPersen: trx.discountPercent.round(),
      cashDiterima: trx.cashReceived,
      isMember: trx.memberId != null,
      memberNama: trx.memberName.isEmpty ? null : trx.memberName,
      alamatPelanggan: trx.memberAddress.isEmpty ? null : trx.memberAddress,
    );
  }

  DetailTransaksiData _toDetailFromItem(TransaksiLaporanItem t) {
    return DetailTransaksiData(
      transactionId: t.transactionId,
      invoice: t.invoice,
      tanggal: t.tanggal,
      waktu: t.jam,
      kasir: t.kasir,
      status: t.status,
      namaPelanggan: t.customer,
      noTelepon: t.noTelepon,
      metodePembayaran: t.pembayaran,
      produkList: t.produkList,
      diskonPersen: t.diskonPersen,
      cashDiterima: t.cashDiterima,
      isMember: t.isMember,
      memberNama: t.memberNama,
      alamatPelanggan: t.alamatPelanggan,
    );
  }

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
                _buildSummaryCards(),
                const SizedBox(height: 16),
                _buildFilter(),
                const SizedBox(height: 16),
                _buildTable(filtered),
                const SizedBox(height: 40),
              ],
            ),
          ),
          ...buildSidebarLayer(
            activeMenu: 'Laporan',
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
                  // Top bar
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
                          // TODO: navigasi ke ProfilePage
                        },
                      ),
                    ],
                  ),
                  const SizedBox(height: 24),

                  // Title + Export PDF
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: [
                      const Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Laporan Penjualan',
                              style: TextStyle(
                                color: Colors.white,
                                fontSize: 22,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                            SizedBox(height: 4),
                            Text(
                              'Periode bulan sekarang',
                              style: TextStyle(
                                color: Colors.white70,
                                fontSize: 13,
                              ),
                            ),
                          ],
                        ),
                      ),
                      // Export PDF button
                      ElevatedButton.icon(
                        onPressed: _isExportingPdf ? null : () => _exportPDF(),
                        icon: _isExportingPdf
                            ? const SizedBox(
                                width: 16,
                                height: 16,
                                child: CircularProgressIndicator(
                                  strokeWidth: 2,
                                  color: Colors.white,
                                ),
                              )
                            : const Icon(
                                Icons.picture_as_pdf_rounded,
                                color: Colors.white,
                                size: 18,
                              ),
                        label: Text(
                          _isExportingPdf ? 'Export...' : 'Export PDF',
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 13,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFFE53E3E),
                          padding: const EdgeInsets.symmetric(
                            horizontal: 16,
                            vertical: 10,
                          ),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12),
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
  }

  // ── SUMMARY CARDS ─────────────────────────────────────────────────────────
  Widget _buildSummaryCards() {
    return SizedBox(
      height: 110,
      child: ListView(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 16),
        children: [
          // Total Omzet — card biru gelap
          _OmzetCard(label: 'Total Omzet', value: _rp(_totalOmzet)),
          SummaryCard(
            label: 'Rata-rata Transaksi',
            value: _rp(_rataRata),
            icon: Icons.receipt_rounded,
            color: const Color(0xFF48BB78),
          ),
          SummaryCard(
            label: 'Transaksi Tertinggi',
            value: _rp(_transaksiTertinggi),
            icon: Icons.trending_up_rounded,
            color: const Color(0xFFECC94B),
          ),
          SummaryCard(
            label: 'Jumlah Transaksi',
            value: '$_jumlahTransaksi transaksi',
            icon: Icons.shopping_cart_rounded,
            color: const Color(0xFF6B9FFF),
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
          borderRadius: BorderRadius.circular(14),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.05),
              blurRadius: 7,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Title
            const Text(
              'Filter Laporan',
              style: TextStyle(
                fontSize: 13,
                fontWeight: FontWeight.w600,
                color: Color(0xFF2D3748),
              ),
            ),
            const SizedBox(height: 12),

            // Filter Fields Row
            Row(
              children: [
                // Tanggal Spesifik
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Tanggal',
                        style: TextStyle(
                          fontSize: 11,
                          color: Colors.grey.shade600,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                      const SizedBox(height: 6),
                      _FilterField(
                        ctrl: _tanggalCtrl,
                        hint: 'Pilih tanggal',
                        suffixIcon: Icons.calendar_today_rounded,
                        onTapSuffix: _pickTanggal,
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: 10),

                // Bulan
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Bulan',
                        style: TextStyle(
                          fontSize: 11,
                          color: Colors.grey.shade600,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                      const SizedBox(height: 6),
                      _FilterField(
                        ctrl: _bulanCtrl,
                        hint: 'Pilih bulan',
                        suffixIcon: Icons.calendar_month_rounded,
                        onTapSuffix: _pickBulan,
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: 10),

                // Urutkan Berdasarkan
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Urutkan',
                        style: TextStyle(
                          fontSize: 11,
                          color: Colors.grey.shade600,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                      const SizedBox(height: 6),
                      Container(
                        height: 38,
                        padding: const EdgeInsets.symmetric(horizontal: 8),
                        decoration: BoxDecoration(
                          color: const Color(0xFFF8F9FA),
                          borderRadius: BorderRadius.circular(7),
                          border: Border.all(color: Colors.grey.shade300),
                        ),
                        child: DropdownButtonHideUnderline(
                          child: DropdownButton<String>(
                            value: _urutkan,
                            isExpanded: true,
                            isDense: true,
                            style: const TextStyle(
                              fontSize: 11,
                              color: Color(0xFF2D3748),
                            ),
                            icon: const Icon(
                              Icons.keyboard_arrow_down_rounded,
                              size: 16,
                            ),
                            items: _urutkanList
                                .map(
                                  (e) => DropdownMenuItem(
                                    value: e,
                                    child: Text(
                                      e,
                                      style: const TextStyle(fontSize: 11),
                                    ),
                                  ),
                                )
                                .toList(),
                            onChanged: (v) => setState(() {
                              _urutkan = v ?? 'Terbaru';
                              _currentPage = 1;
                            }),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: 10),

                // Button: Reset
                Padding(
                  padding: const EdgeInsets.only(top: 18),
                  child: SizedBox(
                    height: 38,
                    child: OutlinedButton.icon(
                      onPressed: _resetFilter,
                      icon: const Icon(Icons.refresh_rounded, size: 14),
                      label: const Text(
                        'Reset',
                        style: TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      style: OutlinedButton.styleFrom(
                        foregroundColor: const Color(0xFF4A5568),
                        side: BorderSide(
                          color: Colors.grey.shade300,
                          width: 0.8,
                        ),
                        padding: const EdgeInsets.symmetric(horizontal: 14),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(7),
                        ),
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

  // ── TABEL DATA TRANSAKSI ──────────────────────────────────────────────────
  Widget _buildTable(List<TransaksiLaporanItem> list) {
    final totalResults = list.length;
    final totalPages = totalResults == 0
        ? 1
        : ((totalResults - 1) ~/ _rowsPerPage) + 1;
    var safePage = _currentPage;
    if (safePage < 1) safePage = 1;
    if (safePage > totalPages) safePage = totalPages;

    final startIndex = totalResults == 0 ? 0 : (safePage - 1) * _rowsPerPage;
    final endExclusive = math.min(startIndex + _rowsPerPage, totalResults);
    final pageItems = totalResults == 0
        ? const <TransaksiLaporanItem>[]
        : list.sublist(startIndex, endExclusive);
    final startResult = totalResults == 0 ? 0 : startIndex + 1;
    final endResult = totalResults == 0 ? 0 : endExclusive;

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Data Transaksi',
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.bold,
              color: Color(0xFF2D3748),
            ),
          ),
          const SizedBox(height: 2),
          Text(
            'detail transaksi penjualan',
            style: TextStyle(fontSize: 12, color: Colors.grey.shade500),
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
                    padding: EdgeInsets.symmetric(vertical: 40),
                    child: Center(child: CircularProgressIndicator()),
                  )
                : list.isEmpty
                ? Padding(
                    padding: const EdgeInsets.symmetric(vertical: 40),
                    child: Center(
                      child: Column(
                        children: [
                          Icon(
                            Icons.receipt_long_rounded,
                            size: 40,
                            color: Colors.grey.shade300,
                          ),
                          const SizedBox(height: 10),
                          Text(
                            'Tidak ada data transaksi',
                            style: TextStyle(
                              fontSize: 14,
                              color: Colors.grey.shade400,
                            ),
                          ),
                        ],
                      ),
                    ),
                  )
                : Column(
                    children: [
                      SingleChildScrollView(
                        scrollDirection: Axis.horizontal,
                        child: DataTable(
                          headingRowColor: WidgetStateProperty.all(
                            const Color(0xFFF7F8FA),
                          ),
                          headingRowHeight: 48,
                          dataRowMinHeight: 64,
                          dataRowMaxHeight: 80,
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
                            DataColumn(label: Text('NO')),
                            DataColumn(label: Text('TANGGAL')),
                            DataColumn(label: Text('INVOICE')),
                            DataColumn(label: Text('KASIR')),
                            DataColumn(label: Text('CUSTOMER')),
                            DataColumn(label: Text('PEMBAYARAN')),
                            DataColumn(label: Text('STATUS')),
                            DataColumn(label: Text('Total')),
                            DataColumn(label: Text('Aksi')),
                          ],
                          rows: pageItems.asMap().entries.map((entry) {
                            final index = entry.key;
                            final t = entry.value;
                            final rowNo = startResult + index;
                            return DataRow(
                              cells: [
                                DataCell(
                                  Text(
                                    '$rowNo',
                                    style: const TextStyle(
                                      fontWeight: FontWeight.w700,
                                      fontSize: 12,
                                    ),
                                  ),
                                ),
                                // Tanggal + jam
                                DataCell(
                                  Column(
                                    crossAxisAlignment:
                                        CrossAxisAlignment.start,
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      Text(
                                        t.tanggal,
                                        style: const TextStyle(
                                          fontSize: 12,
                                          fontWeight: FontWeight.w600,
                                        ),
                                      ),
                                      Text(
                                        t.jam,
                                        style: TextStyle(
                                          fontSize: 11,
                                          color: Colors.grey.shade500,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                                // Invoice + ID
                                DataCell(
                                  Column(
                                    crossAxisAlignment:
                                        CrossAxisAlignment.start,
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      Text(
                                        t.invoice,
                                        style: const TextStyle(
                                          fontSize: 12,
                                          fontWeight: FontWeight.bold,
                                          color: Color(0xFF2D3748),
                                        ),
                                      ),
                                      Text(
                                        'ID: ${t.invoiceId}',
                                        style: TextStyle(
                                          fontSize: 11,
                                          color: Colors.grey.shade400,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                                DataCell(
                                  Text(
                                    t.kasir,
                                    style: const TextStyle(fontSize: 12),
                                  ),
                                ),
                                DataCell(
                                  Text(
                                    t.customer,
                                    style: const TextStyle(fontSize: 12),
                                  ),
                                ),
                                DataCell(
                                  Text(
                                    t.pembayaran,
                                    style: const TextStyle(fontSize: 12),
                                  ),
                                ),
                                DataCell(_statusBadge(t.status)),
                                // Total + jumlah item
                                DataCell(
                                  Column(
                                    crossAxisAlignment:
                                        CrossAxisAlignment.start,
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      Text(
                                        _rp(t.total),
                                        style: const TextStyle(
                                          fontWeight: FontWeight.w700,
                                          fontSize: 12,
                                        ),
                                      ),
                                      Text(
                                        '${t.jumlahItem} item',
                                        style: TextStyle(
                                          fontSize: 11,
                                          color: Colors.grey.shade400,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                                // Aksi detail
                                DataCell(
                                  GestureDetector(
                                    onTap: () => _openDetail(t),
                                    child: Column(
                                      mainAxisAlignment:
                                          MainAxisAlignment.center,
                                      children: [
                                        Container(
                                          padding: const EdgeInsets.all(9),
                                          decoration: BoxDecoration(
                                            color: const Color(
                                              0xFF3B6FE8,
                                            ).withOpacity(0.12),
                                            borderRadius: BorderRadius.circular(
                                              10,
                                            ),
                                          ),
                                          child:
                                              _openingDetailId ==
                                                  t.transactionId
                                              ? const SizedBox(
                                                  width: 19,
                                                  height: 19,
                                                  child:
                                                      CircularProgressIndicator(
                                                        strokeWidth: 2,
                                                        color: Color(
                                                          0xFF3B6FE8,
                                                        ),
                                                      ),
                                                )
                                              : const Icon(
                                                  Icons.visibility_rounded,
                                                  color: Color(0xFF3B6FE8),
                                                  size: 19,
                                                ),
                                        ),
                                        const SizedBox(height: 4),
                                        const Text(
                                          'Detail',
                                          style: TextStyle(
                                            fontSize: 11,
                                            color: Color(0xFF3B6FE8),
                                            fontWeight: FontWeight.w600,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                ),
                              ],
                            );
                          }).toList(),
                        ),
                      ),
                      Container(
                        width: double.infinity,
                        padding: const EdgeInsets.fromLTRB(14, 10, 14, 12),
                        decoration: BoxDecoration(
                          border: Border(
                            top: BorderSide(color: Colors.grey.shade200),
                          ),
                        ),
                        child: Wrap(
                          alignment: WrapAlignment.spaceBetween,
                          crossAxisAlignment: WrapCrossAlignment.center,
                          runSpacing: 8,
                          children: [
                            Text(
                              'Menampilkan $startResult-$endResult dari $totalResults transaksi',
                              style: TextStyle(
                                fontSize: 12,
                                color: Colors.grey.shade600,
                                fontWeight: FontWeight.w500,
                              ),
                            ),
                            Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                OutlinedButton.icon(
                                  onPressed: safePage > 1
                                      ? () => setState(
                                          () => _currentPage = safePage - 1,
                                        )
                                      : null,
                                  icon: const Icon(
                                    Icons.chevron_left_rounded,
                                    size: 16,
                                  ),
                                  label: const Text('Prev'),
                                  style: OutlinedButton.styleFrom(
                                    padding: const EdgeInsets.symmetric(
                                      horizontal: 10,
                                      vertical: 6,
                                    ),
                                  ),
                                ),
                                const SizedBox(width: 8),
                                Text(
                                  'Hal. $safePage / $totalPages',
                                  style: const TextStyle(
                                    fontSize: 12,
                                    fontWeight: FontWeight.w600,
                                    color: Color(0xFF2D3748),
                                  ),
                                ),
                                const SizedBox(width: 8),
                                OutlinedButton.icon(
                                  onPressed: safePage < totalPages
                                      ? () => setState(
                                          () => _currentPage = safePage + 1,
                                        )
                                      : null,
                                  icon: const Icon(
                                    Icons.chevron_right_rounded,
                                    size: 16,
                                  ),
                                  label: const Text('Next'),
                                  style: OutlinedButton.styleFrom(
                                    padding: const EdgeInsets.symmetric(
                                      horizontal: 10,
                                      vertical: 6,
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
          ),
        ],
      ),
    );
  }

  Widget _statusBadge(String status) {
    final isLunas = status == 'Lunas';
    final color = isLunas ? const Color(0xFF48BB78) : const Color(0xFFECC94B);
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: color.withOpacity(isLunas ? 0.12 : 0.15),
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
}

// ════════════════════════════════════════════════════════════════════════════
// TOTAL OMZET CARD — dark blue style
// ════════════════════════════════════════════════════════════════════════════
class _OmzetCard extends StatelessWidget {
  final String label, value;
  const _OmzetCard({required this.label, required this.value});

  @override
  Widget build(BuildContext context) => Container(
    width: 190,
    margin: const EdgeInsets.only(right: 12),
    padding: const EdgeInsets.all(16),
    decoration: BoxDecoration(
      color: const Color(0xFF2B55D0),
      borderRadius: BorderRadius.circular(16),
      boxShadow: [
        BoxShadow(
          color: const Color(0xFF2B55D0).withOpacity(0.35),
          blurRadius: 10,
          offset: const Offset(0, 4),
        ),
      ],
    ),
    child: Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Row(
          children: [
            Text(
              label,
              style: const TextStyle(color: Colors.white70, fontSize: 12),
            ),
            const Spacer(),
            Container(
              padding: const EdgeInsets.all(6),
              decoration: BoxDecoration(
                color: Colors.white.withOpacity(0.2),
                borderRadius: BorderRadius.circular(8),
              ),
              child: const Icon(
                Icons.bar_chart_rounded,
                color: Colors.white,
                size: 18,
              ),
            ),
          ],
        ),
        const SizedBox(height: 8),
        FittedBox(
          fit: BoxFit.scaleDown,
          alignment: Alignment.centerLeft,
          child: Text(
            value,
            style: const TextStyle(
              color: Colors.white,
              fontSize: 20,
              fontWeight: FontWeight.bold,
            ),
          ),
        ),
      ],
    ),
  );
}

// ════════════════════════════════════════════════════════════════════════════
// FILTER FIELD HELPER
// ════════════════════════════════════════════════════════════════════════════
class _FilterField extends StatelessWidget {
  final TextEditingController ctrl;
  final String hint;
  final IconData suffixIcon;
  final VoidCallback onTapSuffix;

  const _FilterField({
    required this.ctrl,
    required this.hint,
    required this.suffixIcon,
    required this.onTapSuffix,
  });

  @override
  Widget build(BuildContext context) => TextField(
    controller: ctrl,
    readOnly: true,
    style: const TextStyle(fontSize: 12),
    decoration: InputDecoration(
      hintText: hint,
      hintStyle: TextStyle(color: Colors.grey.shade400, fontSize: 12),
      filled: true,
      fillColor: const Color(0xFFF8F9FA),
      contentPadding: const EdgeInsets.only(
        left: 10,
        top: 10,
        bottom: 10,
        right: 4,
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
        borderSide: const BorderSide(color: Color(0xFF3B6FE8), width: 1.5),
      ),
      suffixIcon: IconButton(
        onPressed: onTapSuffix,
        icon: Icon(suffixIcon, size: 18, color: Colors.grey.shade500),
        splashRadius: 18,
      ),
    ),
  );
}
