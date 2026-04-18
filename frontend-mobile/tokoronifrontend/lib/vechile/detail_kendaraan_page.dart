// lib/delivery/detail_kendaraan_page.dart
import 'package:flutter/material.dart';
import 'manajemen_kendaraan_page.dart';

// ── Dummy riwayat pengiriman per kendaraan ────────────────────────────────────
class _RiwayatDelivery {
  final String kode;
  final String tanggal;
  final String waktu;
  final String status;
  const _RiwayatDelivery({
    required this.kode,
    required this.tanggal,
    required this.waktu,
    required this.status,
  });
}

final _dummyRiwayatMap = <String, List<_RiwayatDelivery>>{
  'KND-001': [
    const _RiwayatDelivery(
      kode: 'DEL202603250001',
      tanggal: '25/03/2026',
      waktu: '01:43',
      status: 'Terkirim',
    ),
    const _RiwayatDelivery(
      kode: 'DEL202603200001',
      tanggal: '20/03/2026',
      waktu: '10:00',
      status: 'Terkirim',
    ),
    const _RiwayatDelivery(
      kode: 'DEL202603150001',
      tanggal: '15/03/2026',
      waktu: '14:30',
      status: 'Terkirim',
    ),
  ],
  'KND-002': [
    const _RiwayatDelivery(
      kode: 'DEL202603250002',
      tanggal: '25/03/2026',
      waktu: '02:00',
      status: 'Dalam Perjalanan',
    ),
    const _RiwayatDelivery(
      kode: 'DEL202603180001',
      tanggal: '18/03/2026',
      waktu: '09:15',
      status: 'Terkirim',
    ),
  ],
  'KND-003': [
    const _RiwayatDelivery(
      kode: 'DEL202603240001',
      tanggal: '24/03/2026',
      waktu: '14:30',
      status: 'Terkirim',
    ),
    const _RiwayatDelivery(
      kode: 'DEL202603100001',
      tanggal: '10/03/2026',
      waktu: '08:00',
      status: 'Terkirim',
    ),
    const _RiwayatDelivery(
      kode: 'DEL202603050001',
      tanggal: '05/03/2026',
      waktu: '11:20',
      status: 'Gagal',
    ),
  ],
  'KND-004': [],
  'KND-005': [
    const _RiwayatDelivery(
      kode: 'DEL202603190001',
      tanggal: '19/03/2026',
      waktu: '11:20',
      status: 'Terkirim',
    ),
  ],
};

// ════════════════════════════════════════════════════════════════════════════
// PAGE
// ════════════════════════════════════════════════════════════════════════════
class DetailKendaraanPage extends StatefulWidget {
  final KendaraanItem kendaraan;
  final VoidCallback? onEdit;
  final void Function(String newStatus)? onStatusChange;

  const DetailKendaraanPage({
    super.key,
    required this.kendaraan,
    this.onEdit,
    this.onStatusChange,
  });

  @override
  State<DetailKendaraanPage> createState() => _DetailKendaraanPageState();
}

class _DetailKendaraanPageState extends State<DetailKendaraanPage> {
  static const _blue = Color(0xFF3B6FE8);
  static const _green = Color(0xFF38A169);

  late KendaraanItem _k;

  @override
  void initState() {
    super.initState();
    _k = widget.kendaraan;
  }

  // ── Data riwayat ──────────────────────────────────────────────────────────
  List<_RiwayatDelivery> get _riwayat => _dummyRiwayatMap[_k.id] ?? [];

  int get _totalDelivery => _riwayat.length;
  int get _selesai => _riwayat.where((r) => r.status == 'Terkirim').length;
  int get _berjalan => _riwayat
      .where((r) => r.status == 'Dalam Perjalanan' || r.status == 'Diambil')
      .length;
  String get _terakhirPakai => _riwayat.isNotEmpty
      ? '${_riwayat.first.tanggal}  ${_riwayat.first.waktu}'
      : '-';

  // ── Status actions ────────────────────────────────────────────────────────
  void _tandaiServis() {
    _showConfirm(
      icon: Icons.build_rounded,
      iconColor: const Color(0xFFE53E3E),
      title: 'Tandai Servis?',
      message: 'Status ${_k.nama} akan diubah menjadi Servis/Maintenance.',
      confirmLabel: 'Tandai Servis',
      confirmColor: const Color(0xFFE53E3E),
      onConfirm: () {
        setState(() => _k.status = 'Servis');
        widget.onStatusChange?.call('Servis');
        _snack('${_k.nama} ditandai sebagai Servis', const Color(0xFFE53E3E));
      },
    );
  }

  void _tandaiTersedia() {
    _showConfirm(
      icon: Icons.check_circle_rounded,
      iconColor: _green,
      title: 'Tandai Tersedia?',
      message: 'Status ${_k.nama} akan diubah menjadi Tersedia.',
      confirmLabel: 'Tandai Tersedia',
      confirmColor: _green,
      onConfirm: () {
        setState(() => _k.status = 'Tersedia');
        widget.onStatusChange?.call('Tersedia');
        _snack('${_k.nama} ditandai sebagai Tersedia', _green);
      },
    );
  }

  void _showConfirm({
    required IconData icon,
    required Color iconColor,
    required String title,
    required String message,
    required String confirmLabel,
    required Color confirmColor,
    required VoidCallback onConfirm,
  }) {
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
      appBar: AppBar(
        backgroundColor: Colors.white,
        foregroundColor: const Color(0xFF2D3748),
        elevation: 0,
        titleSpacing: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new_rounded, size: 18),
          onPressed: () => Navigator.pop(context),
        ),
        title: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(9),
              decoration: BoxDecoration(
                color: _blue,
                borderRadius: BorderRadius.circular(10),
              ),
              child: const Icon(
                Icons.directions_car_rounded,
                color: Colors.white,
                size: 18,
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    _k.nama,
                    style: const TextStyle(
                      fontSize: 15,
                      fontWeight: FontWeight.bold,
                      color: Color(0xFF2D3748),
                    ),
                  ),
                  Text(
                    _k.platNomor,
                    style: TextStyle(
                      fontSize: 11,
                      color: Colors.grey.shade500,
                      fontWeight: FontWeight.w400,
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
        actions: [
          Padding(
            padding: const EdgeInsets.only(right: 12),
            child: OutlinedButton.icon(
              onPressed: widget.onEdit,
              icon: const Icon(Icons.edit_rounded, size: 14),
              label: const Text(
                'Edit',
                style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600),
              ),
              style: OutlinedButton.styleFrom(
                foregroundColor: const Color(0xFF2D3748),
                side: BorderSide(color: Colors.grey.shade300),
                padding: const EdgeInsets.symmetric(
                  horizontal: 12,
                  vertical: 8,
                ),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(8),
                ),
              ),
            ),
          ),
        ],
        bottom: PreferredSize(
          preferredSize: const Size.fromHeight(1),
          child: Container(height: 1, color: Colors.grey.shade200),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // ── Status bar + action buttons ─────────────────────────────────────
            _buildStatusBar(),
            const SizedBox(height: 14),

            // ── Informasi Kendaraan ─────────────────────────────────────────────
            _buildInfoKendaraan(),
            const SizedBox(height: 14),

            // ── Statistik Pengiriman ────────────────────────────────────────────
            _buildStatistikPengiriman(),
            const SizedBox(height: 14),

            // ── Riwayat Pengiriman ──────────────────────────────────────────────
            _buildRiwayatPengiriman(),
            const SizedBox(height: 32),
          ],
        ),
      ),
    );
  }

  // ── STATUS BAR ────────────────────────────────────────────────────────────
  Widget _buildStatusBar() => Container(
    padding: const EdgeInsets.fromLTRB(16, 12, 16, 12),
    decoration: BoxDecoration(
      color: Colors.white,
      borderRadius: BorderRadius.circular(12),
      boxShadow: [
        BoxShadow(
          color: Colors.black.withOpacity(0.05),
          blurRadius: 6,
          offset: const Offset(0, 2),
        ),
      ],
    ),
    child: Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Baris 1: label + chip status
        Row(
          children: [
            Text(
              'Status: ',
              style: TextStyle(fontSize: 13, color: Colors.grey.shade500),
            ),
            _statusChip(_k.status),
          ],
        ),
        const SizedBox(height: 10),
        // Baris 2: tombol action
        Row(
          children: [
            if (_k.status != 'Servis') ...[
              Expanded(
                child: ElevatedButton.icon(
                  onPressed: _tandaiServis,
                  icon: const Icon(Icons.build_rounded, size: 14),
                  label: const Text(
                    'Tandai Servis',
                    style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600),
                  ),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFFE53E3E),
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 10),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(8),
                    ),
                    elevation: 0,
                  ),
                ),
              ),
              if (_k.status != 'Tersedia') const SizedBox(width: 8),
            ],
            if (_k.status != 'Tersedia')
              Expanded(
                child: ElevatedButton.icon(
                  onPressed: _tandaiTersedia,
                  icon: const Icon(Icons.check_circle_rounded, size: 14),
                  label: const Text(
                    'Tandai Tersedia',
                    style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600),
                  ),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: _green,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 10),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(8),
                    ),
                    elevation: 0,
                  ),
                ),
              ),
          ],
        ),
      ],
    ),
  );

  Widget _statusChip(String s) {
    final c = kendaraanStatusColor(s);
    final iconMap = {
      'Tersedia': Icons.check_circle_rounded,
      'Sedang Digunakan': Icons.local_shipping_rounded,
      'Servis': Icons.build_rounded,
    };
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
      decoration: BoxDecoration(
        color: c.withOpacity(0.12),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: c.withOpacity(0.3)),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(iconMap[s] ?? Icons.circle, size: 13, color: c),
          const SizedBox(width: 5),
          Text(
            s,
            style: TextStyle(
              fontSize: 12,
              fontWeight: FontWeight.w600,
              color: c,
            ),
          ),
        ],
      ),
    );
  }

  // ── INFORMASI KENDARAAN ───────────────────────────────────────────────────
  Widget _buildInfoKendaraan() => _SectionCard(
    title: 'Informasi Kendaraan',
    icon: Icons.info_rounded,
    child: Column(
      children: [
        _row2Col(
          _infoItem('Jenis Kendaraan', _k.jenis),
          _infoItem(
            'Kapasitas Berat',
            _k.kapasitasBerat > 0
                ? '${_k.kapasitasBerat.toStringAsFixed(0)} kg'
                : '-',
          ),
        ),
        const SizedBox(height: 14),
        _row2Col(
          _infoItem(
            'Kapasitas Volume',
            _k.kapasitasVolume > 0 ? '${_k.kapasitasVolume} m³' : '-',
          ),
          _infoItem('Terakhir Maintenance', _k.tanggalMaintenance),
        ),
        if (_k.catatan.isNotEmpty) ...[
          const SizedBox(height: 14),
          const Divider(height: 1, color: Color(0xFFF0F0F0)),
          const SizedBox(height: 12),
          _infoItem('Catatan', _k.catatan, fullWidth: true),
        ],
      ],
    ),
  );

  // ── STATISTIK PENGIRIMAN ──────────────────────────────────────────────────
  Widget _buildStatistikPengiriman() => _SectionCard(
    title: 'Statistik Pengiriman',
    icon: Icons.bar_chart_rounded,
    child: Column(
      children: [
        Row(
          children: [
            Expanded(
              child: _statItem(
                'Total Pengiriman',
                '$_totalDelivery',
                const Color(0xFF3B6FE8),
                Icons.local_shipping_rounded,
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: _statItem(
                'Selesai',
                '$_selesai',
                _green,
                Icons.check_circle_rounded,
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: _statItem(
                'Sedang Berjalan',
                '$_berjalan',
                const Color(0xFFD69E2E),
                Icons.directions_car_rounded,
              ),
            ),
          ],
        ),
        if (_riwayat.isNotEmpty) ...[
          const SizedBox(height: 14),
          const Divider(height: 1, color: Color(0xFFF0F0F0)),
          const SizedBox(height: 10),
          Row(
            children: [
              Text(
                'Terakhir Digunakan:',
                style: TextStyle(fontSize: 12, color: Colors.grey.shade500),
              ),
              const Spacer(),
              Text(
                _terakhirPakai,
                style: const TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w600,
                  color: Color(0xFF2D3748),
                ),
              ),
            ],
          ),
        ],
      ],
    ),
  );

  Widget _statItem(String label, String value, Color color, IconData icon) =>
      Container(
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: color.withOpacity(0.07),
          borderRadius: BorderRadius.circular(10),
          border: Border.all(color: color.withOpacity(0.15)),
        ),
        child: Column(
          children: [
            Icon(icon, color: color, size: 22),
            const SizedBox(height: 6),
            Text(
              value,
              style: TextStyle(
                fontSize: 20,
                fontWeight: FontWeight.bold,
                color: color,
              ),
            ),
            const SizedBox(height: 2),
            Text(
              label,
              style: TextStyle(fontSize: 10, color: Colors.grey.shade500),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      );

  // ── RIWAYAT PENGIRIMAN ────────────────────────────────────────────────────
  Widget _buildRiwayatPengiriman() => _SectionCard(
    title: 'Riwayat Pengiriman',
    icon: Icons.history_rounded,
    child: _riwayat.isEmpty
        ? Padding(
            padding: const EdgeInsets.symmetric(vertical: 20),
            child: Center(
              child: Column(
                children: [
                  Icon(
                    Icons.inbox_rounded,
                    size: 36,
                    color: Colors.grey.shade300,
                  ),
                  const SizedBox(height: 8),
                  Text(
                    'Belum ada riwayat pengiriman',
                    style: TextStyle(fontSize: 13, color: Colors.grey.shade400),
                  ),
                ],
              ),
            ),
          )
        : Column(
            children: _riwayat.asMap().entries.map((e) {
              final i = e.key;
              final r = e.value;
              return Column(
                children: [
                  if (i > 0) const Divider(height: 1, color: Color(0xFFF5F5F5)),
                  Padding(
                    padding: const EdgeInsets.symmetric(vertical: 10),
                    child: Row(
                      children: [
                        // Timeline dot
                        Container(
                          width: 10,
                          height: 10,
                          decoration: BoxDecoration(
                            color: _deliveryStatusColor(r.status),
                            shape: BoxShape.circle,
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                r.kode,
                                style: const TextStyle(
                                  fontSize: 12,
                                  fontWeight: FontWeight.w600,
                                  color: Color(0xFF2D3748),
                                ),
                              ),
                              const SizedBox(height: 2),
                              Text(
                                '${r.tanggal}  •  ${r.waktu}',
                                style: TextStyle(
                                  fontSize: 11,
                                  color: Colors.grey.shade500,
                                ),
                              ),
                            ],
                          ),
                        ),
                        _deliveryStatusBadge(r.status),
                      ],
                    ),
                  ),
                ],
              );
            }).toList(),
          ),
  );

  Color _deliveryStatusColor(String s) {
    switch (s) {
      case 'Terkirim':
        return const Color(0xFF38A169);
      case 'Dalam Perjalanan':
        return const Color(0xFF3B6FE8);
      case 'Gagal':
        return const Color(0xFFE53E3E);
      default:
        return const Color(0xFFD69E2E);
    }
  }

  Widget _deliveryStatusBadge(String s) {
    final c = _deliveryStatusColor(s);
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
      decoration: BoxDecoration(
        color: c.withOpacity(0.10),
        borderRadius: BorderRadius.circular(10),
      ),
      child: Text(
        s,
        style: TextStyle(fontSize: 10, fontWeight: FontWeight.w600, color: c),
      ),
    );
  }

  // ── Helpers ───────────────────────────────────────────────────────────────
  Widget _row2Col(Widget left, Widget right) => Row(
    crossAxisAlignment: CrossAxisAlignment.start,
    children: [
      Expanded(child: left),
      const SizedBox(width: 14),
      Expanded(child: right),
    ],
  );

  Widget _infoItem(String label, String value, {bool fullWidth = false}) =>
      Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            label,
            style: TextStyle(fontSize: 11, color: Colors.grey.shade500),
          ),
          const SizedBox(height: 4),
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
// SECTION CARD
// ════════════════════════════════════════════════════════════════════════════
class _SectionCard extends StatelessWidget {
  final String title;
  final IconData icon;
  final Widget child;
  const _SectionCard({
    required this.title,
    required this.icon,
    required this.child,
  });

  @override
  Widget build(BuildContext context) => Container(
    width: double.infinity,
    decoration: BoxDecoration(
      color: Colors.white,
      borderRadius: BorderRadius.circular(14),
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
        // Header section
        Container(
          padding: const EdgeInsets.fromLTRB(16, 13, 16, 13),
          decoration: BoxDecoration(
            color: const Color(0xFFF0F4FF),
            borderRadius: const BorderRadius.vertical(top: Radius.circular(14)),
            border: Border(bottom: BorderSide(color: Colors.grey.shade200)),
          ),
          child: Row(
            children: [
              Icon(icon, size: 16, color: const Color(0xFF3B6FE8)),
              const SizedBox(width: 8),
              Text(
                title,
                style: const TextStyle(
                  fontSize: 13,
                  fontWeight: FontWeight.w600,
                  color: Color(0xFF2D3748),
                ),
              ),
            ],
          ),
        ),
        // Body
        Padding(padding: const EdgeInsets.all(16), child: child),
      ],
    ),
  );
}
