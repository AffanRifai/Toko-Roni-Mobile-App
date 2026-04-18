// lib/delivery/detail_pengiriman_page.dart
import 'package:flutter/material.dart';
import 'manajemen_pengiriman_page.dart';

// ════════════════════════════════════════════════════════════════════════════
// STATUS CONFIG
// ════════════════════════════════════════════════════════════════════════════
const _statusFlowMap = {
  'Pending':           0,
  'Diproses':          1,
  'Assigned':          2,
  'Diambil':           3,
  'Dalam Perjalanan':  4,
  'Terkirim':          5,
  'Gagal':             -1,
  'Dibatalkan':        -1,
};

// ── Dummy kurir ───────────────────────────────────────────────────────────────
const _dummyKurirList = ['luhut', 'Budi', 'Rusdi', 'Andi'];
const _dummyKendaraanList = [
  'Motor - B 1234 XYZ (Andi)',
  'Motor - B 5678 ABC (Budi)',
  'Mobil Pick-up - B 9012 DEF (Rusdi)',
];

class _TimelineStep {
  final String label;
  final IconData icon;
  final String? sublabel1;
  final String? sublabel2;
  final bool isDone;
  final bool isActive;
  final bool isPending;

  const _TimelineStep({required this.label, required this.icon,
    this.sublabel1, this.sublabel2,
    this.isDone = false, this.isActive = false, this.isPending = false});
}

// ════════════════════════════════════════════════════════════════════════════
// PAGE
// ════════════════════════════════════════════════════════════════════════════
class DetailPengirimanPage extends StatefulWidget {
  final PengirimanItem pengiriman;
  const DetailPengirimanPage({super.key, required this.pengiriman});

  @override
  State<DetailPengirimanPage> createState() => _DetailPengirimanPageState();
}

class _DetailPengirimanPageState extends State<DetailPengirimanPage> {
  static const _blue  = Color(0xFF3B6FE8);
  static const _green = Color(0xFF38A169);

  late PengirimanItem _p;

  @override
  void initState() {
    super.initState();
    _p = widget.pengiriman;
  }

  // ── Action handlers ───────────────────────────────────────────────────────
  void _updateStatus(String newStatus) {
    setState(() => _p.status = newStatus);
    _snack('Status diperbarui: $newStatus', _green);
  }

  void _batalkan() {
    showDialog(context: context, builder: (_) => _ConfirmDialog(
      icon: Icons.cancel_rounded,
      title: 'Batalkan Pengiriman?',
      message: 'Pengiriman ${_p.kodePengiriman} akan dibatalkan.',
      confirmLabel: 'Ya, Batalkan',
      confirmColor: const Color(0xFFE53E3E),
      onConfirm: () { setState(() => _p.status = 'Dibatalkan'); _snack('Pengiriman dibatalkan', Colors.orange); },
    ));
  }

  void _selesaikan() {
    showDialog(context: context, builder: (_) => _ConfirmDialog(
      icon: Icons.check_circle_rounded,
      title: 'Selesaikan Pengiriman?',
      message: 'Tandai pengiriman ${_p.kodePengiriman} sebagai terkirim?',
      confirmLabel: 'Selesaikan',
      confirmColor: _green,
      onConfirm: () => _updateStatus('Terkirim'),
    ));
  }

  void _mulaiPengiriman() => _updateStatus('Dalam Perjalanan');
  void _paketDiambil()   => _updateStatus('Diambil');

  // ── Assign kurir ──────────────────────────────────────────────────────────
  void _showAssignKurir() {
    String? selKurir     = _p.namaKurir;
    String? selKendaraan;
    showDialog(context: context, builder: (_) => StatefulBuilder(
      builder: (ctx, setS) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        titlePadding: const EdgeInsets.fromLTRB(20, 20, 20, 0),
        contentPadding: const EdgeInsets.fromLTRB(20, 12, 20, 0),
        actionsPadding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
        title: Row(children: [
          const Icon(Icons.person_add_rounded, color: _blue, size: 20),
          const SizedBox(width: 8),
          const Text('Assign Kurir & Kendaraan',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
          const Spacer(),
          GestureDetector(onTap: () => Navigator.pop(ctx),
              child: Icon(Icons.close, size: 20, color: Colors.grey.shade400)),
        ]),
        content: Column(mainAxisSize: MainAxisSize.min, children: [
          const SizedBox(height: 8),
          // Info
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(color: const Color(0xFFF0F4FF),
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: const Color(0xFFBEE3F8))),
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Row(children: const [
                Icon(Icons.info_outline_rounded, color: _blue, size: 14),
                SizedBox(width: 6),
                Text('Informasi Pengiriman',
                    style: TextStyle(fontSize: 12, color: _blue, fontWeight: FontWeight.w600)),
              ]),
              const SizedBox(height: 8),
              _iRow('Kode:', _p.kodePengiriman),
              _iRow('Tujuan:', _p.tujuan),
              _iRow('Total Item:', '${_p.totalItem} barang'),
            ]),
          ),
          const SizedBox(height: 14),
          // Kurir
          _dropLabel('Pilih Kurir', Icons.person_rounded),
          const SizedBox(height: 6),
          _dropContainer(child: DropdownButtonHideUnderline(
            child: DropdownButton<String>(
              value: selKurir, isExpanded: true,
              hint: Text('-- Pilih Kurir --',
                  style: TextStyle(color: Colors.grey.shade400, fontSize: 13)),
              style: const TextStyle(fontSize: 13, color: Color(0xFF2D3748)),
              icon: const Icon(Icons.keyboard_arrow_down_rounded, size: 20),
              items: _dummyKurirList.map((k) =>
                  DropdownMenuItem(value: k, child: Text(k))).toList(),
              onChanged: (v) => setS(() => selKurir = v),
            ),
          )),
          const SizedBox(height: 12),
          // Kendaraan
          _dropLabel('Pilih Kendaraan', Icons.local_shipping_rounded),
          const SizedBox(height: 6),
          _dropContainer(child: DropdownButtonHideUnderline(
            child: DropdownButton<String>(
              value: selKendaraan, isExpanded: true,
              hint: Text('-- Pilih Kendaraan --',
                  style: TextStyle(color: Colors.grey.shade400, fontSize: 13)),
              style: const TextStyle(fontSize: 13, color: Color(0xFF2D3748)),
              icon: const Icon(Icons.keyboard_arrow_down_rounded, size: 20),
              items: _dummyKendaraanList.map((k) => DropdownMenuItem(
                  value: k, child: Text(k, style: const TextStyle(fontSize: 12)))).toList(),
              onChanged: (v) => setS(() => selKendaraan = v),
            ),
          )),
          const SizedBox(height: 8),
        ]),
        actions: [Row(children: [
          Expanded(child: ElevatedButton.icon(
            onPressed: () {
              if (selKurir == null || selKendaraan == null) {
                _snack('Pilih kurir dan kendaraan', Colors.red); return;
              }
              setState(() {
                _p.namaKurir  = selKurir;
                _p.nomorKurir = '0831 4287 8951';
                _p.kendaraan  = selKendaraan!.split(' (').first;
                if (_p.status == 'Pending' || _p.status == 'Diproses') _p.status = 'Assigned';
              });
              Navigator.pop(ctx);
              _snack('Kurir berhasil di-assign!', _green);
            },
            icon: const Icon(Icons.check_rounded, size: 16),
            label: const Text('Assign Sekarang', style: TextStyle(fontWeight: FontWeight.w600)),
            style: ElevatedButton.styleFrom(backgroundColor: _blue,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(vertical: 12),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)), elevation: 0),
          )),
          const SizedBox(width: 10),
          Expanded(child: OutlinedButton(
            onPressed: () => Navigator.pop(ctx),
            style: OutlinedButton.styleFrom(side: BorderSide(color: Colors.grey.shade300),
                padding: const EdgeInsets.symmetric(vertical: 12),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10))),
            child: const Text('Batal', style: TextStyle(color: Colors.grey, fontWeight: FontWeight.w600)),
          )),
        ])],
      ),
    ));
  }

  Widget _dropLabel(String label, IconData icon) => Row(
    mainAxisSize: MainAxisSize.min,
    children: [
      Icon(icon, size: 14, color: const Color(0xFF4A5568)),
      const SizedBox(width: 5),
      Text(label, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w500)),
      const Text(' *', style: TextStyle(color: Colors.red, fontSize: 13)),
    ],
  );

  Widget _dropContainer({required Widget child}) => Container(
    padding: const EdgeInsets.symmetric(horizontal: 12),
    decoration: BoxDecoration(borderRadius: BorderRadius.circular(10),
        border: Border.all(color: Colors.grey.shade300)),
    child: child,
  );

  Widget _iRow(String label, String val) => Padding(
    padding: const EdgeInsets.symmetric(vertical: 2),
    child: Row(children: [
      Text(label, style: TextStyle(fontSize: 11, color: Colors.grey.shade600)),
      const SizedBox(width: 8),
      Expanded(child: Text(val, textAlign: TextAlign.right,
          style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600,
              color: Color(0xFF2D3748)))),
    ]),
  );

  void _snack(String msg, Color color) =>
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(
        content: Text(msg), backgroundColor: color,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
      ));

  // ── Timeline steps ────────────────────────────────────────────────────────
  List<_TimelineStep> get _steps {
    final idx = _statusFlowMap[_p.status] ?? 0;
    final now = DateTime.now();
    final tgl = '${_p.tanggalDibuat}';
    final jam = '${_p.jamDibuat}';
    // Timestamps dummy
    final t1 = '$tgl $jam';
    final t2 = '$tgl ${(int.parse(jam.split(':')[0]) + 1).toString().padLeft(2,'0')}:${jam.split(':')[1]}';

    return [
      _TimelineStep(label: 'Pengiriman Dibuat', icon: Icons.check_rounded,
          sublabel1: t1, isDone: idx >= 0),
      _TimelineStep(label: 'Diproses Logistik', icon: Icons.check_rounded,
          sublabel1: t2, isDone: idx >= 1, isActive: idx == 0, isPending: idx < 0 || idx < 1 && idx != 0),
      _TimelineStep(label: 'Ditugaskan ke Kurir', icon: Icons.person_rounded,
          sublabel1: _p.namaKurir, sublabel2: _p.nomorKurir,
          isDone: idx >= 3, isActive: idx == 2, isPending: idx < 2),
      _TimelineStep(label: 'Paket Diambil Kurir', icon: Icons.inventory_2_rounded,
          isDone: idx >= 3, isActive: idx == 3, isPending: idx < 3),
      _TimelineStep(label: 'Dalam Perjalanan', icon: Icons.local_shipping_rounded,
          isDone: idx >= 4, isActive: idx == 4, isPending: idx < 4),
      _TimelineStep(label: 'Terkirim', icon: Icons.check_circle_rounded,
          isDone: idx >= 5, isActive: false, isPending: idx < 5),
    ];
  }

  // ════════════════════════════════════════════════════════════════════════
  // BUILD
  // ════════════════════════════════════════════════════════════════════════
  @override
  Widget build(BuildContext context) {
    final isBatalable = _p.status != 'Terkirim' &&
        _p.status != 'Dibatalkan' && _p.status != 'Gagal';
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
        title: Row(children: [
          Container(padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                  color: _blue, borderRadius: BorderRadius.circular(10)),
              child: const Icon(Icons.local_shipping_rounded,
                  color: Colors.white, size: 16)),
          const SizedBox(width: 10),
          Flexible(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            const Text('Detail Pengiriman', overflow: TextOverflow.ellipsis,
                style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold,
                    color: Color(0xFF2D3748))),
            Text(_p.kodePengiriman, overflow: TextOverflow.ellipsis,
                style: TextStyle(fontSize: 10, color: Colors.grey.shade500,
                    fontWeight: FontWeight.w400)),
          ])),
        ]),
        actions: [
          Padding(
            padding: const EdgeInsets.only(right: 10),
            child: ElevatedButton.icon(
              onPressed: () => _snack('Mencetak surat jalan...', _green),
              icon: const Icon(Icons.print_rounded, size: 13),
              label: const Text('Cetak',
                  style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600)),
              style: ElevatedButton.styleFrom(
                backgroundColor: _green, foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 7),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                elevation: 0,
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
        child: Column(children: [
          _buildStatusBar(isBatalable),
          const SizedBox(height: 14),
          _buildInfoTransaksi(),
          const SizedBox(height: 14),
          _buildInfoRute(),
          const SizedBox(height: 14),
          _buildTimeline(),
          const SizedBox(height: 32),
        ]),
      ),
    );
  }

  // ── STATUS BAR ────────────────────────────────────────────────────────────
  Widget _buildStatusBar(bool isBatalable) => Container(
    padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
    decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(12),
        boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.05),
            blurRadius: 6, offset: const Offset(0, 2))]),
    child: Row(children: [
      const Text('Status: ', style: TextStyle(fontSize: 13, color: Color(0xFF4A5568))),
      _statusChip(_p.status),
      const Spacer(),
      // Action buttons berdasarkan status
      Wrap(spacing: 8, children: [
        if (_p.status == 'Pending' || _p.status == 'Diproses')
          _actionBtn('Assign Kurir', Icons.person_add_rounded, const Color(0xFF3B6FE8),
              _showAssignKurir),
        if (_p.status == 'Assigned')
          _actionBtn('Paket Diambil', Icons.inventory_2_rounded, const Color(0xFF6B5CE7),
              _paketDiambil),
        if (_p.status == 'Diambil')
          _actionBtn('Mulai Pengiriman', Icons.local_shipping_rounded, const Color(0xFFED8936),
              _mulaiPengiriman),
        if (_p.status != 'Terkirim' && _p.status != 'Dibatalkan' && _p.status != 'Gagal')
          _actionBtn('Selesaikan', Icons.check_circle_rounded, _green, _selesaikan),
        if (isBatalable)
          _actionBtn('Batalkan', Icons.cancel_rounded, const Color(0xFFE53E3E), _batalkan),
      ]),
    ]),
  );

  Widget _actionBtn(String label, IconData icon, Color color, VoidCallback onTap) =>
      ElevatedButton.icon(
        onPressed: onTap,
        icon: Icon(icon, size: 13),
        label: Text(label, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600)),
        style: ElevatedButton.styleFrom(
          backgroundColor: color, foregroundColor: Colors.white,
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
          elevation: 0,
        ),
      );

  // ── INFO TRANSAKSI (sudah include total item) ─────────────────────────────
  Widget _buildInfoTransaksi() => _InfoCard(
    title: 'Informasi Transaksi',
    icon: Icons.receipt_long_rounded,
    child: Column(children: [
      Row(children: [
        Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text('No. Invoice', style: TextStyle(fontSize: 11, color: Colors.grey.shade500)),
          const SizedBox(height: 3),
          Text(_p.invoice, style: const TextStyle(
              fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF2D3748))),
        ])),
        Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text('Tanggal Transaksi', style: TextStyle(fontSize: 11, color: Colors.grey.shade500)),
          const SizedBox(height: 3),
          Text('${_p.tanggalDibuat} ${_p.jamDibuat}',
              style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold,
                  color: Color(0xFF2D3748))),
        ])),
      ]),
      const SizedBox(height: 12),
      Row(children: [
        Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text('Customer', style: TextStyle(fontSize: 11, color: Colors.grey.shade500)),
          const SizedBox(height: 3),
          Text(_p.namaCustomer, style: const TextStyle(
              fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF2D3748))),
        ])),
        Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text('Total Belanja', style: TextStyle(fontSize: 11, color: Colors.grey.shade500)),
          const SizedBox(height: 3),
          Text(_rp(_p.totalBelanja), style: const TextStyle(
              fontSize: 13, fontWeight: FontWeight.bold, color: _green)),
        ])),
      ]),
      const SizedBox(height: 12),
      const Divider(height: 1, color: Color(0xFFF0F0F0)),
      const SizedBox(height: 10),
      Row(children: [
        Text('Total Item', style: TextStyle(fontSize: 13, color: Colors.grey.shade500)),
        const Spacer(),
        Text('${_p.totalItem} item', style: const TextStyle(
            fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF2D3748))),
      ]),
    ]),
  );

  // ── TIMELINE ─────────────────────────────────────────────────────────────
  Widget _buildTimeline() => _InfoCard(
    title: 'Timeline Pengiriman',
    icon: Icons.schedule_rounded,
    child: Column(children: [
      ...List.generate(_steps.length, (i) {
        final step = _steps[i];
        final isLast = i == _steps.length - 1;
        return _TimelineTile(step: step, isLast: isLast);
      }),
    ]),
  );

  // ── INFO RUTE ─────────────────────────────────────────────────────────────
  Widget _buildInfoRute() => _InfoCard(
    title: 'Informasi Rute',
    icon: Icons.route_rounded,
    child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
      Text('Asal', style: TextStyle(fontSize: 11, color: Colors.grey.shade500)),
      const SizedBox(height: 4),
      Container(width: double.infinity, padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
          decoration: BoxDecoration(color: const Color(0xFFF8F9FA),
              borderRadius: BorderRadius.circular(8),
              border: Border.all(color: Colors.grey.shade200)),
          child: const Text('Toko Roni Juntinyuat',
              style: TextStyle(fontSize: 13, fontWeight: FontWeight.w500))),
      const SizedBox(height: 12),
      Text('Tujuan', style: TextStyle(fontSize: 11, color: Colors.grey.shade500)),
      const SizedBox(height: 4),
      Container(width: double.infinity, padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
          decoration: BoxDecoration(color: const Color(0xFFF8F9FA),
              borderRadius: BorderRadius.circular(8),
              border: Border.all(color: Colors.grey.shade200)),
          child: Text(_p.tujuan,
              style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w500))),
    ]),
  );

  // ── Status chip ───────────────────────────────────────────────────────────
  Widget _statusChip(String status) {
    const iconMap = {
      'Pending': Icons.hourglass_empty_rounded,
      'Diproses': Icons.settings_rounded,
      'Assigned': Icons.person_rounded,
      'Diambil': Icons.inventory_2_rounded,
      'Dalam Perjalanan': Icons.local_shipping_rounded,
      'Terkirim': Icons.check_circle_rounded,
      'Gagal': Icons.cancel_rounded,
      'Dibatalkan': Icons.block_rounded,
    };
    final color = _statusColorOf(status);
    final icon  = iconMap[status] ?? Icons.circle;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
      decoration: BoxDecoration(color: color.withOpacity(0.12),
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: color.withOpacity(0.3))),
      child: Row(mainAxisSize: MainAxisSize.min, children: [
        Icon(icon, size: 13, color: color),
        const SizedBox(width: 5),
        Text(status, style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: color)),
      ]),
    );
  }

  static Color _statusColorOf(String s) {
    switch (s) {
      case 'Terkirim':        return const Color(0xFF38A169);
      case 'Dalam Perjalanan':return const Color(0xFF3B6FE8);
      case 'Diambil':         return const Color(0xFF6B5CE7);
      case 'Assigned':        return const Color(0xFF805AD5);
      case 'Diproses':        return const Color(0xFFD69E2E);
      case 'Pending':         return const Color(0xFFD69E2E);
      case 'Gagal':           return const Color(0xFFE53E3E);
      case 'Dibatalkan':      return const Color(0xFF718096);
      default:                return const Color(0xFF4A5568);
    }
  }
}

// ════════════════════════════════════════════════════════════════════════════
// TIMELINE TILE
// ════════════════════════════════════════════════════════════════════════════
class _TimelineTile extends StatelessWidget {
  final _TimelineStep step;
  final bool isLast;
  const _TimelineTile({required this.step, required this.isLast});

  static const _green = Color(0xFF38A169);

  @override
  Widget build(BuildContext context) {
    final dotColor = step.isDone ? _green
        : step.isActive ? Colors.grey.shade400
        : Colors.grey.shade300;
    final iconColor = step.isDone || step.isActive ? Colors.white : Colors.grey.shade400;
    final textColor = step.isPending && !step.isDone && !step.isActive
        ? Colors.grey.shade400 : const Color(0xFF2D3748);

    return IntrinsicHeight(
      child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
        // Dot + garis
        SizedBox(width: 44, child: Column(
          mainAxisAlignment: MainAxisAlignment.start, children: [
          Container(width: 36, height: 36,
              decoration: BoxDecoration(color: dotColor, shape: BoxShape.circle),
              child: Icon(step.icon, color: iconColor, size: 18)),
          if (!isLast)
            Expanded(child: Container(width: 2, margin: const EdgeInsets.symmetric(vertical: 3),
                decoration: BoxDecoration(
                    color: step.isDone ? _green.withOpacity(0.3) : Colors.grey.shade200,
                    borderRadius: BorderRadius.circular(1)))),
        ])),
        // Label
        Expanded(child: Padding(
          padding: const EdgeInsets.only(left: 10, bottom: 20),
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            const SizedBox(height: 8),
            Text(step.label, style: TextStyle(fontSize: 14,
                fontWeight: (step.isDone || step.isActive) ? FontWeight.bold : FontWeight.normal,
                color: textColor)),
            if (step.sublabel1 != null && step.sublabel1!.isNotEmpty) ...[
              const SizedBox(height: 2),
              Text(step.sublabel1!, style: TextStyle(
                  fontSize: 12, color: Colors.grey.shade500)),
            ],
            if (step.sublabel2 != null && step.sublabel2!.isNotEmpty) ...[
              const SizedBox(height: 1),
              Text(step.sublabel2!, style: TextStyle(
                  fontSize: 12, color: Colors.grey.shade500)),
            ],
          ]),
        )),
      ]),
    );
  }
}

// ════════════════════════════════════════════════════════════════════════════
// INFO CARD
// ════════════════════════════════════════════════════════════════════════════
class _InfoCard extends StatelessWidget {
  final String title;
  final IconData icon;
  final Widget child;
  const _InfoCard({required this.title, required this.icon, required this.child});

  @override
  Widget build(BuildContext context) => Container(
    width: double.infinity,
    decoration: BoxDecoration(
      color: Colors.white, borderRadius: BorderRadius.circular(12),
      boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.05),
          blurRadius: 8, offset: const Offset(0, 2))],
    ),
    child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
      // Header baris biru muda
      Container(
        width: double.infinity,
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
        decoration: BoxDecoration(
          color: const Color(0xFFF0F4FF),
          borderRadius: const BorderRadius.vertical(top: Radius.circular(12)),
          border: Border(bottom: BorderSide(color: Colors.grey.shade200)),
        ),
        child: Row(children: [
          Icon(icon, size: 15, color: const Color(0xFF3B6FE8)),
          const SizedBox(width: 8),
          Text(title, style: const TextStyle(fontSize: 13,
              fontWeight: FontWeight.w600, color: Color(0xFF2D3748))),
        ]),
      ),
      // Body
      Padding(padding: const EdgeInsets.all(16), child: child),
    ]),
  );
}

// ════════════════════════════════════════════════════════════════════════════
// CONFIRM DIALOG
// ════════════════════════════════════════════════════════════════════════════
class _ConfirmDialog extends StatelessWidget {
  final IconData icon;
  final String title, message, confirmLabel;
  final Color confirmColor;
  final VoidCallback onConfirm;
  const _ConfirmDialog({required this.icon, required this.title,
    required this.message, required this.confirmLabel,
    required this.confirmColor, required this.onConfirm});

  static const _blue = Color(0xFF3B6FE8);

  @override
  Widget build(BuildContext context) => AlertDialog(
    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
    contentPadding: const EdgeInsets.fromLTRB(24, 20, 24, 0),
    actionsPadding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
    content: Column(mainAxisSize: MainAxisSize.min, children: [
      Container(width: 56, height: 56,
          decoration: BoxDecoration(color: confirmColor.withOpacity(0.08),
              shape: BoxShape.circle),
          child: Icon(icon, color: confirmColor, size: 26)),
      const SizedBox(height: 12),
      Text(title, style: const TextStyle(fontSize: 16,
          fontWeight: FontWeight.bold, color: Color(0xFF2D3748))),
      const SizedBox(height: 8),
      Text(message, textAlign: TextAlign.center,
          style: TextStyle(fontSize: 13, color: Colors.grey.shade600, height: 1.4)),
      const SizedBox(height: 20),
    ]),
    actions: [Row(children: [
      Expanded(child: OutlinedButton(
        onPressed: () => Navigator.pop(context),
        style: OutlinedButton.styleFrom(side: BorderSide(color: Colors.grey.shade300),
            padding: const EdgeInsets.symmetric(vertical: 11),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10))),
        child: const Text('Batal', style: TextStyle(color: Colors.grey, fontWeight: FontWeight.w600)),
      )),
      const SizedBox(width: 10),
      Expanded(child: ElevatedButton(
        onPressed: () { Navigator.pop(context); onConfirm(); },
        style: ElevatedButton.styleFrom(backgroundColor: confirmColor,
            foregroundColor: Colors.white,
            padding: const EdgeInsets.symmetric(vertical: 11),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            elevation: 0),
        child: Text(confirmLabel, style: const TextStyle(fontWeight: FontWeight.w600)),
      )),
    ])],
  );
}

// ── Rupiah helper ─────────────────────────────────────────────────────────────
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