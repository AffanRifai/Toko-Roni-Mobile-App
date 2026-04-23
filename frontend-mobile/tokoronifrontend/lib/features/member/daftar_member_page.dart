// lib/member/daftar_member_page.dart
import 'package:flutter/material.dart';
import 'package:tokoronifrontend/features/delivery/manajemen_pengiriman_page.dart';
import 'package:tokoronifrontend/features/profile/profile_page.dart';
import 'package:tokoronifrontend/features/report/laporan_penjualan_page.dart';
import 'package:tokoronifrontend/features/transaction/kasir_page.dart';
import 'package:tokoronifrontend/features/transaction/riwayat_transaksi_page.dart';
import 'package:tokoronifrontend/features/vehicle/manajemen_kendaraan_page.dart';
import '../../core/services/member_service.dart';
import '../../shared/widgets/shared_widgets.dart';
import '../../shared/widgets/notifikasi_widget.dart';
import '../../shared/widgets/profile_widget.dart';
import '../../shared/widgets/semua_notifikasi_page.dart';
import '../../models/member_model.dart';
import 'tambah_member_page.dart';
import 'edit_member_page.dart';
import '../home/dashboard_page.dart';
import '../category/manajemen_kategori_page.dart';
import '../product/daftar_produk_page.dart';
import '../user/manajemen_pengguna_page.dart';

class DaftarMemberPage extends StatefulWidget {
  const DaftarMemberPage({super.key});

  @override
  State<DaftarMemberPage> createState() => _DaftarMemberPageState();
}

class _DaftarMemberPageState extends State<DaftarMemberPage>
    with SingleTickerProviderStateMixin, SidebarMixin {
  late List<MemberData> _list;
  bool _isLoading = true;
  bool _hasError = false;
  String _errorMessage = '';
  final _searchCtrl = TextEditingController();
  String _filterStatus = 'Semua Status';
  String _filterTipe = 'Semua Tipe';

  // ── Filter ────────────────────────────────────────────────────────────────
  List<MemberData> get _filtered => _list.where((m) {
    final q = _searchCtrl.text.trim().toLowerCase();
    final matchSearch =
        q.isEmpty ||
        m.nama.toLowerCase().contains(q) ||
        m.kode.toLowerCase().contains(q) ||
        m.telepon.contains(q);
    final matchStatus =
        _filterStatus == 'Semua Status' ||
        (_filterStatus == 'Aktif' && m.aktif) ||
        (_filterStatus == 'Nonaktif' && !m.aktif);
    final matchTipe = _filterTipe == 'Semua Tipe' || m.tipe == _filterTipe;
    return matchSearch && matchStatus && matchTipe;
  }).toList();

  // ── Stats ─────────────────────────────────────────────────────────────────
  int get _totalMember => _list.length;
  int get _totalPiutang => _list.fold(0, (s, m) => s + m.piutang);
  int get _memberAktif => _list.where((m) => m.aktif).length;
  int get _rataRataPiutang =>
      _memberAktif == 0 ? 0 : (_totalPiutang / _memberAktif).round();

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

  // ── Navigasi sidebar ──────────────────────────────────────────────────────
  void _handleMenuTap(String menu) {
    if (menu == 'Member') {
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

  void _resetFilter() => setState(() {
    _searchCtrl.clear();
    _filterStatus = 'Semua Status';
    _filterTipe = 'Semua Tipe';
  });

  Future<void> _loadAllData() async {
    if (!mounted) return;
    setState(() {
      _isLoading = true;
      _hasError = false;
      _errorMessage = '';
    });

    try {
      final members = await MemberService.getMembers(perPage: 200);
      if (!mounted) return;
      setState(() {
        _list = members
            .map(
              (m) => MemberData(
                id: m.id,
                kode: m.kodeMember.isEmpty ? 'MBR-${m.id}' : m.kodeMember,
                nama: m.nama,
                email: m.email,
                telepon: m.noTelepon,
                alamat: m.alamat,
                tipe: tipeMemberLabelFromApi(m.tipeMember),
                limitKredit: m.limitKredit.round(),
                piutang: m.totalPiutang.round(),
                aktif: m.isActive,
                tanggalRegistrasi: formatTanggalMember(
                  m.tanggalRegistrasiRaw.isNotEmpty
                      ? m.tanggalRegistrasiRaw
                      : m.createdAtRaw,
                ),
                terdaftarSejak: relativeTimeFromRaw(m.createdAtRaw),
                terakhirUpdate: relativeTimeFromRaw(m.updatedAtRaw),
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
                  ? 'Sebagian data member mungkin belum sinkron. Tarik ke bawah untuk coba lagi.'
                  : _errorMessage,
              style: const TextStyle(fontSize: 12, color: Color(0xFF744210)),
            ),
          ),
        ],
      ),
    ),
  );

  // ════════════════════════════════════════════════════════════════════════
  // MODAL — Detail Member (bottom sheet)
  // ════════════════════════════════════════════════════════════════════════
  void _showDetailMember(MemberData m) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => DraggableScrollableSheet(
        initialChildSize: 0.75, // ← Ukuran awal modal (0-1, 0.75 = 75% layar)
        minChildSize: 0.5, // ← Ukuran minimal saat di-drag (0.5 = 50% layar)
        maxChildSize: 0.75, // ← Ukuran maksimal saat di-drag (0.75 = 75% layar)
        builder: (_, scrollCtrl) => Container(
          decoration: const BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
          ),
          child: Column(
            children: [
              // Handle bar
              Padding(
                padding: const EdgeInsets.only(top: 12, bottom: 8),
                child: Container(
                  width: 40,
                  height: 4,
                  decoration: BoxDecoration(
                    color: Colors.grey.shade300,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),

              // Header
              Container(
                margin: const EdgeInsets.fromLTRB(16, 0, 16, 0),
                padding: const EdgeInsets.fromLTRB(16, 14, 16, 14),
                decoration: const BoxDecoration(
                  color: Color(0xFF3B6FE8),
                  borderRadius: BorderRadius.all(Radius.circular(12)),
                ),
                child: Row(
                  children: [
                    const Expanded(
                      child: Text(
                        'Informasi Pribadi',
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 10,
                        vertical: 4,
                      ),
                      decoration: BoxDecoration(
                        color: Colors.white.withOpacity(0.2),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text(
                        'ID: ${m.kode}',
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 11,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 4),

              // Scrollable content
              Expanded(
                child: ListView(
                  controller: scrollCtrl,
                  padding: const EdgeInsets.fromLTRB(16, 8, 16, 24),
                  children: [
                    // Informasi Pribadi
                    _modalInfoRow('Nama', m.nama),
                    _modalInfoRow('Email', m.email),
                    _modalInfoRow('Telepon', m.telepon),
                    _modalInfoRow('Tipe Member', m.tipe),
                    _modalInfoRow('Alamat', m.alamat),
                    // Status badge
                    Padding(
                      padding: const EdgeInsets.symmetric(vertical: 8),
                      child: Row(
                        children: [
                          Expanded(
                            child: Text(
                              'Status',
                              style: TextStyle(
                                fontSize: 13,
                                color: Colors.grey.shade500,
                              ),
                            ),
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 12,
                              vertical: 5,
                            ),
                            decoration: BoxDecoration(
                              color: m.aktif
                                  ? const Color(0xFF48BB78).withOpacity(0.12)
                                  : const Color(0xFFE53E3E).withOpacity(0.10),
                              borderRadius: BorderRadius.circular(20),
                            ),
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Icon(
                                  m.aktif
                                      ? Icons.check_circle_rounded
                                      : Icons.cancel_rounded,
                                  size: 13,
                                  color: m.aktif
                                      ? const Color(0xFF48BB78)
                                      : const Color(0xFFE53E3E),
                                ),
                                const SizedBox(width: 5),
                                Text(
                                  m.aktif ? 'Aktif' : 'Nonaktif',
                                  style: TextStyle(
                                    fontSize: 12,
                                    fontWeight: FontWeight.w600,
                                    color: m.aktif
                                        ? const Color(0xFF48BB78)
                                        : const Color(0xFFE53E3E),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
                    _modalInfoRow('Tanggal Registrasi', m.tanggalRegistrasi),
                    _modalInfoRow('Terdaftar Sejak', m.terdaftarSejak),
                    _modalInfoRow('Terakhir Update', m.terakhirUpdate),
                    _modalInfoRow('Limit Kredit', rupiahFormat(m.limitKredit)),
                    Padding(
                      padding: const EdgeInsets.symmetric(vertical: 8),
                      child: Row(
                        children: [
                          Expanded(
                            child: Text(
                              'Piutang Saat Ini',
                              style: TextStyle(
                                fontSize: 13,
                                color: Colors.grey.shade500,
                              ),
                            ),
                          ),
                          Text(
                            rupiahFormat(m.piutang),
                            style: const TextStyle(
                              fontSize: 13,
                              fontWeight: FontWeight.w700,
                              color: Color(0xFFE53E3E),
                            ),
                          ),
                        ],
                      ),
                    ),
                    Padding(
                      padding: const EdgeInsets.symmetric(vertical: 8),
                      child: Row(
                        children: [
                          Expanded(
                            child: Text(
                              'Sisa Limit',
                              style: TextStyle(
                                fontSize: 13,
                                color: Colors.grey.shade500,
                              ),
                            ),
                          ),
                          Text(
                            rupiahFormat(m.sisaLimit),
                            style: const TextStyle(
                              fontSize: 13,
                              fontWeight: FontWeight.w700,
                              color: Color(0xFF48BB78),
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 10),
                    // Progress bar
                    ClipRRect(
                      borderRadius: BorderRadius.circular(6),
                      child: LinearProgressIndicator(
                        value: m.persenPiutang,
                        minHeight: 10,
                        backgroundColor: Colors.grey.shade200,
                        valueColor: AlwaysStoppedAnimation<Color>(
                          m.persenPiutang > 0.8
                              ? const Color(0xFFE53E3E)
                              : m.persenPiutang > 0.5
                              ? const Color(0xFFECC94B)
                              : const Color(0xFF48BB78),
                        ),
                      ),
                    ),
                    const SizedBox(height: 6),
                    Text(
                      'penggunaan sisa limit ${(m.persenPiutang * 100).toStringAsFixed(0)}%',
                      style: TextStyle(
                        fontSize: 11,
                        color: Colors.grey.shade500,
                      ),
                    ),
                    const SizedBox(height: 20),

                    // Tutup button
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton(
                        onPressed: () => Navigator.pop(context),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF3B6FE8),
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(vertical: 14),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                          elevation: 0,
                        ),
                        child: const Text(
                          'Tutup',
                          style: TextStyle(
                            fontWeight: FontWeight.w600,
                            fontSize: 14,
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  // ════════════════════════════════════════════════════════════════════════
  // MODAL — Detail Piutang (bottom sheet)
  // ════════════════════════════════════════════════════════════════════════
  Future<void> _showDetailPiutang(MemberData m) async {
    if (m.id <= 0) {
      _snack('ID member tidak valid untuk mengambil data piutang.', Colors.red);
      return;
    }

    _showLoadingDialog();

    MemberReceivableSummary summary;
    try {
      summary = await MemberService.getMemberReceivableSummary(memberId: m.id);
    } catch (e) {
      if (!mounted) return;
      Navigator.pop(context);
      _snack(e.toString().replaceFirst('Exception: ', ''), Colors.red);
      return;
    }

    if (!mounted) return;
    Navigator.pop(context);

    final totalPiutang = summary.totalPiutang > 0
        ? summary.totalPiutang.round()
        : m.piutang;
    final hasPiutang = summary.hasData || totalPiutang > 0;
    if (!hasPiutang) {
      _snack('Tidak ada data piutang untuk ${m.nama}', Colors.orange);
      return;
    }

    final hasLimitStats = summary.totalLimit > 0;

    final piutang = PiutangData(
      noPiutang: summary.noPiutang.isEmpty ? '-' : summary.noPiutang,
      invoice: summary.invoiceNumber.isEmpty ? '-' : summary.invoiceNumber,
      tanggal: summary.tanggalTransaksiRaw.isEmpty
          ? '-'
          : formatTanggalMember(summary.tanggalTransaksiRaw),
      totalPiutang: totalPiutang,
      sisaLimit: hasLimitStats ? summary.sisaLimit.round() : m.sisaLimit,
      totalLimit: hasLimitStats ? summary.totalLimit.round() : m.limitKredit,
      totalTransaksiKredit: summary.totalTransaksiKredit,
      jatuhTempo: summary.jatuhTempoRaw.isEmpty
          ? '-'
          : formatTanggalMember(summary.jatuhTempoRaw),
      status: _piutangStatusLabel(
        rawStatus: summary.status,
        jatuhTempoRaw: summary.jatuhTempoRaw,
      ),
    );

    Color statusColor;
    switch (piutang.status) {
      case 'Lunas':
        statusColor = const Color(0xFF48BB78);
        break;
      case 'Menunggak':
        statusColor = const Color(0xFFE53E3E);
        break;
      default:
        statusColor = const Color(0xFFECC94B);
    }

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (_) => Container(
        decoration: const BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
        ),
        padding: const EdgeInsets.fromLTRB(20, 16, 20, 32),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            // Handle
            Container(
              width: 40,
              height: 4,
              decoration: BoxDecoration(
                color: Colors.grey.shade300,
                borderRadius: BorderRadius.circular(2),
              ),
            ),
            const SizedBox(height: 16),

            // Header
            Row(
              children: [
                const Expanded(
                  child: Text(
                    'Detail Piutang',
                    style: TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                      color: Color(0xFF2D3748),
                    ),
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 10,
                    vertical: 5,
                  ),
                  decoration: BoxDecoration(
                    color: const Color(0xFF3B6FE8).withOpacity(0.12),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    'ID: ${m.kode}',
                    style: const TextStyle(
                      color: Color(0xFF3B6FE8),
                      fontSize: 11,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),
            const Divider(),
            const SizedBox(height: 8),

            // Rows
            _modalInfoRow('Nama', m.nama),
            _modalInfoRow('No Piutang', piutang.noPiutang),
            _modalInfoRow('Invoice', piutang.invoice),
            _modalInfoRow('Tanggal', piutang.tanggal),

            // Total Piutang merah
            Padding(
              padding: const EdgeInsets.symmetric(vertical: 8),
              child: Row(
                children: [
                  Expanded(
                    child: Text(
                      'Total Piutang',
                      style: TextStyle(
                        fontSize: 13,
                        color: Colors.grey.shade500,
                      ),
                    ),
                  ),
                  Text(
                    rupiahFormat(piutang.totalPiutang),
                    style: const TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w700,
                      color: Color(0xFFE53E3E),
                    ),
                  ),
                ],
              ),
            ),

            // Sisa Limit hijau
            Padding(
              padding: const EdgeInsets.symmetric(vertical: 8),
              child: Row(
                children: [
                  Expanded(
                    child: Text(
                      'Sisa Limit',
                      style: TextStyle(
                        fontSize: 13,
                        color: Colors.grey.shade500,
                      ),
                    ),
                  ),
                  Text(
                    rupiahFormat(piutang.sisaLimit),
                    style: const TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w700,
                      color: Color(0xFF48BB78),
                    ),
                  ),
                ],
              ),
            ),

            // Total Limit biru
            Padding(
              padding: const EdgeInsets.symmetric(vertical: 8),
              child: Row(
                children: [
                  Expanded(
                    child: Text(
                      'Total Limit',
                      style: TextStyle(
                        fontSize: 13,
                        color: Colors.grey.shade500,
                      ),
                    ),
                  ),
                  Text(
                    rupiahFormat(piutang.totalLimit),
                    style: const TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w700,
                      color: Color(0xFF3B6FE8),
                    ),
                  ),
                ],
              ),
            ),

            _modalInfoRow(
              'Total Transaksi Kredit',
              '${piutang.totalTransaksiKredit} Transaksi',
            ),
            _modalInfoRow('Jatuh Tempo', piutang.jatuhTempo),

            // Status badge
            Padding(
              padding: const EdgeInsets.symmetric(vertical: 8),
              child: Row(
                children: [
                  Expanded(
                    child: Text(
                      'Status',
                      style: TextStyle(
                        fontSize: 13,
                        color: Colors.grey.shade500,
                      ),
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 14,
                      vertical: 6,
                    ),
                    decoration: BoxDecoration(
                      color: statusColor,
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Text(
                      piutang.status,
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 12,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ),
                ],
              ),
            ),

            const SizedBox(height: 20),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: () => Navigator.pop(context),
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF3B6FE8),
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 14),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                  elevation: 0,
                ),
                child: const Text(
                  'Tutup',
                  style: TextStyle(fontWeight: FontWeight.w600, fontSize: 14),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  String _piutangStatusLabel({
    required String rawStatus,
    required String jatuhTempoRaw,
  }) {
    final status = rawStatus.trim().toUpperCase();
    if (status == 'LUNAS') return 'Lunas';

    DateTime? jatuhTempo;
    try {
      jatuhTempo = DateTime.parse(jatuhTempoRaw).toLocal();
    } catch (_) {
      jatuhTempo = null;
    }

    if (jatuhTempo != null) {
      final now = DateTime.now();
      final dueDate = DateTime(
        jatuhTempo.year,
        jatuhTempo.month,
        jatuhTempo.day,
      );
      final today = DateTime(now.year, now.month, now.day);
      if (dueDate.isBefore(today)) return 'Menunggak';
    }

    return 'Belum Jatuh Tempo';
  }

  void _showLoadingDialog() {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (_) => const AlertDialog(
        content: Row(
          children: [
            SizedBox(
              width: 20,
              height: 20,
              child: CircularProgressIndicator(strokeWidth: 2),
            ),
            SizedBox(width: 12),
            Expanded(child: Text('Memuat detail piutang...')),
          ],
        ),
      ),
    );
  }

  // ── Toggle aktif/nonaktif ─────────────────────────────────────────────────
  void _toggleStatus(MemberData m) {
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
                color: (m.aktif ? Colors.red : Colors.green).withOpacity(0.10),
                shape: BoxShape.circle,
              ),
              child: Icon(
                m.aktif
                    ? Icons.power_settings_new_rounded
                    : Icons.check_circle_rounded,
                color: m.aktif ? Colors.red : Colors.green,
                size: 32,
              ),
            ),
            const SizedBox(height: 16),
            Text(
              '${m.aktif ? 'Nonaktifkan' : 'Aktifkan'} Member',
              style: const TextStyle(
                fontSize: 17,
                fontWeight: FontWeight.bold,
                color: Color(0xFF2D3748),
              ),
            ),
            const SizedBox(height: 10),
            Text(
              'Apakah kamu yakin ingin ${m.aktif ? 'nonaktifkan' : 'aktifkan'} member "${m.nama}"?',
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
                  onPressed: () async {
                    Navigator.pop(context);
                    await _submitToggleStatus(m);
                  },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: m.aktif ? Colors.red : Colors.green,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 12),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(10),
                    ),
                    elevation: 0,
                  ),
                  child: Text(
                    m.aktif ? 'Nonaktifkan' : 'Aktifkan',
                    style: const TextStyle(fontWeight: FontWeight.w600),
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Future<void> _submitToggleStatus(MemberData m) async {
    if (m.id <= 0) {
      _snack('ID member tidak valid untuk mengubah status.', Colors.red);
      return;
    }

    try {
      final isActive = await MemberService.toggleMemberStatus(memberId: m.id);
      if (!mounted) return;
      setState(() => m.aktif = isActive);
      _snack(
        'Member "${m.nama}" berhasil di${isActive ? 'aktifkan' : 'nonaktifkan'}',
        isActive ? Colors.green : Colors.orange,
      );
    } catch (e) {
      if (!mounted) return;
      _snack(e.toString().replaceFirst('Exception: ', ''), Colors.red);
    }
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
          ...buildSidebarLayer(activeMenu: 'Member', onMenuTap: _handleMenuTap),
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
                  const SizedBox(height: 22),

                  const Text(
                    'Daftar Member',
                    style: TextStyle(
                      color: Colors.white,
                      fontSize: 22,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 4),
                  const Text(
                    'Kelola data member dan pantau piutang',
                    style: TextStyle(color: Colors.white70, fontSize: 13),
                  ),
                  const SizedBox(height: 20),

                  // ── Summary cards — pakai SummaryCard dari shared_widgets ──
                  SizedBox(
                    height: 110,
                    child: ListView(
                      scrollDirection: Axis.horizontal,
                      children: [
                        SummaryCard(
                          label: 'Total Member',
                          value: '$_totalMember',
                          icon: Icons.people_alt_rounded,
                          color: const Color(0xFF6B9FFF),
                        ),
                        SummaryCard(
                          label: 'Total Piutang',
                          value: rupiahFormat(_totalPiutang),
                          icon: Icons.monetization_on_rounded,
                          color: const Color(0xFFECC94B),
                        ),
                        SummaryCard(
                          label: 'Member Aktif',
                          value: '$_memberAktif',
                          icon: Icons.person_rounded,
                          color: const Color(0xFF48BB78),
                        ),
                        SummaryCard(
                          label: 'Rata-Rata Piutang',
                          value: rupiahFormat(_rataRataPiutang),
                          icon: Icons.assessment_rounded,
                          color: const Color(0xFFFC8181),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 16),

                  // Tambah Member button
                  Align(
                    alignment: Alignment.centerRight,
                    child: ElevatedButton.icon(
                      onPressed: () async {
                        final result = await Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (_) => const TambahMemberPage(),
                          ),
                        );
                        if (!mounted) return;
                        if (result is String && result.isNotEmpty) {
                          _snack(result, const Color(0xFF48BB78));
                        }
                        await _loadAllData();
                      },
                      icon: const Icon(Icons.person_add_rounded, size: 18),
                      label: const Text(
                        'Tambah Member',
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
              'Filter Member',
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
                hintText: 'Cari nama, kode, telepon',
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
                  statusMemberList,
                  (v) => setState(() => _filterStatus = v!),
                ),
                _dd(
                  _filterTipe,
                  tipeMemberFilterList,
                  (v) => setState(() => _filterTipe = v!),
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
  Widget _buildTable(List<MemberData> filtered) {
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
            const SizedBox(height: 8),
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
                        'Gagal memuat data member',
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
                        'Member tidak ditemukan',
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
                  dataRowMinHeight: 64,
                  dataRowMaxHeight: 76,
                  columnSpacing: 12,
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
                    DataColumn(label: Text('NAMA')),
                    DataColumn(label: Text('KONTAK')),
                    DataColumn(label: Text('TIPE')),
                    DataColumn(label: Text('LIMIT')),
                    DataColumn(label: Text('PIUTANG')),
                    DataColumn(label: Text('SISA LIMIT')),
                    DataColumn(label: Text('STATUS')),
                    DataColumn(label: Text('AKSI')),
                  ],
                  rows: filtered
                      .map(
                        (m) => DataRow(
                          cells: [
                            DataCell(
                              Text(
                                m.kode,
                                style: const TextStyle(
                                  fontSize: 11,
                                  fontWeight: FontWeight.w600,
                                ),
                              ),
                            ),
                            DataCell(
                              Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Text(
                                    m.nama,
                                    style: const TextStyle(
                                      fontSize: 12,
                                      fontWeight: FontWeight.w600,
                                    ),
                                  ),
                                  Text(
                                    m.email,
                                    style: TextStyle(
                                      fontSize: 10,
                                      color: Colors.grey.shade500,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                            DataCell(Text(m.telepon)),
                            DataCell(_tipeBadge(m.tipe)),
                            DataCell(Text(rupiahFormat(m.limitKredit))),
                            DataCell(
                              Text(
                                rupiahFormat(m.piutang),
                                style: TextStyle(
                                  color: m.piutang > 0
                                      ? const Color(0xFFE53E3E)
                                      : const Color(0xFF2D3748),
                                  fontWeight: m.piutang > 0
                                      ? FontWeight.w600
                                      : FontWeight.normal,
                                ),
                              ),
                            ),
                            DataCell(
                              Text(
                                rupiahFormat(m.sisaLimit),
                                style: TextStyle(
                                  color: m.sisaLimit < m.limitKredit * 0.2
                                      ? const Color(0xFFE53E3E)
                                      : const Color(0xFF48BB78),
                                  fontWeight: FontWeight.w600,
                                ),
                              ),
                            ),
                            // Status — style daftar produk
                            DataCell(
                              Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 10,
                                  vertical: 4,
                                ),
                                decoration: BoxDecoration(
                                  color: m.aktif
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
                                      m.aktif
                                          ? Icons.check_circle_rounded
                                          : Icons.cancel_rounded,
                                      size: 12,
                                      color: m.aktif
                                          ? const Color(0xFF48BB78)
                                          : const Color(0xFFE53E3E),
                                    ),
                                    const SizedBox(width: 4),
                                    Text(
                                      m.aktif ? 'Aktif' : 'Nonaktif',
                                      style: TextStyle(
                                        fontSize: 11,
                                        fontWeight: FontWeight.w600,
                                        color: m.aktif
                                            ? const Color(0xFF48BB78)
                                            : const Color(0xFFE53E3E),
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ),
                            // Aksi
                            DataCell(
                              Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  _AksiBtn(
                                    icon: Icons.visibility_rounded,
                                    color: const Color(0xFF4169E1),
                                    label: 'Detail',
                                    onTap: () => _showDetailMember(m),
                                  ),
                                  const SizedBox(width: 12),
                                  _AksiBtn(
                                    icon: Icons.edit_rounded,
                                    color: const Color(0xFF48BB78),
                                    label: 'Edit',
                                    onTap: () async {
                                      final result = await Navigator.push(
                                        context,
                                        MaterialPageRoute(
                                          builder: (_) =>
                                              EditMemberPage(member: m),
                                        ),
                                      );
                                      if (!mounted) return;
                                      if (result is String &&
                                          result.isNotEmpty) {
                                        _snack(result, const Color(0xFF48BB78));
                                      }
                                      await _loadAllData();
                                    },
                                  ),
                                  const SizedBox(width: 12),
                                  _AksiBtn(
                                    icon: Icons.monetization_on_rounded,
                                    color: const Color(0xFFECC94B),
                                    label: 'Piutang',
                                    onTap: () async {
                                      await _showDetailPiutang(m);
                                    },
                                  ),
                                  const SizedBox(width: 12),
                                  _AksiBtn(
                                    icon: Icons.power_settings_new_rounded,
                                    color: m.aktif
                                        ? const Color(0xFFE53E3E)
                                        : const Color(0xFF48BB78),
                                    label: m.aktif ? 'Nonaktif' : 'Aktifkan',
                                    onTap: () => _toggleStatus(m),
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

  Widget _tipeBadge(String tipe) {
    Color color;
    switch (tipe) {
      case 'Gold':
        color = const Color(0xFFD69E2E);
        break;
      case 'Platinum':
        color = const Color(0xFF718096);
        break;
      default:
        color = const Color(0xFF48BB78);
    }
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: color.withOpacity(0.12),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Text(
        tipe,
        style: TextStyle(
          fontSize: 11,
          fontWeight: FontWeight.w600,
          color: color,
        ),
      ),
    );
  }

  // Helper modal info row
  Widget _modalInfoRow(String label, String value) => Padding(
    padding: const EdgeInsets.symmetric(vertical: 8),
    child: Row(
      children: [
        Expanded(
          child: Text(
            label,
            style: TextStyle(fontSize: 13, color: Colors.grey.shade500),
          ),
        ),
        Text(
          value,
          style: const TextStyle(
            fontSize: 13,
            fontWeight: FontWeight.w600,
            color: Color(0xFF2D3748),
          ),
        ),
      ],
    ),
  );
}

// ── Aksi Button ───────────────────────────────────────────────────────────────
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
          padding: const EdgeInsets.all(9), // ← Diperbesar dari 7
          decoration: BoxDecoration(
            color: color.withOpacity(0.13),
            borderRadius: BorderRadius.circular(9), // ← Diperbesar dari 8
          ),
          child: Icon(icon, color: color, size: 17), // ← Diperbesar dari 15
        ),
        const SizedBox(height: 4), // ← Diperbesar dari 2
        Text(
          label,
          style: TextStyle(
            fontSize: 10, // ← Diperbesar dari 9
            color: color,
            fontWeight: FontWeight.w600, // ← Diperbesar dari w500
          ),
        ),
      ],
    ),
  );
}
