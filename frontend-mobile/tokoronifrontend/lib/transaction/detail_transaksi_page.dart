// lib/transaction/detail_transaksi_page.dart
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

// ════════════════════════════════════════════════════════════════════════════
// MODEL
// ════════════════════════════════════════════════════════════════════════════
class DetailProdukTransaksi {
  final String kode;
  final String nama;
  final String kategori;
  final int harga;
  final int qty;

  const DetailProdukTransaksi({
    required this.kode,
    required this.nama,
    required this.kategori,
    required this.harga,
    required this.qty,
  });

  int get subtotal => harga * qty;
}

class DetailTransaksiData {
  final String invoice;
  final String tanggal;
  final String waktu;
  final String kasir;
  final String status;
  final String namaPelanggan;
  final String noTelepon;
  final String metodePembayaran;
  final List<DetailProdukTransaksi> produkList;
  final int diskonPersen;
  final int cashDiterima;
  final bool isMember;
  final String? memberNama;

  const DetailTransaksiData({
    required this.invoice,
    required this.tanggal,
    required this.waktu,
    required this.kasir,
    required this.status,
    required this.namaPelanggan,
    required this.noTelepon,
    required this.metodePembayaran,
    required this.produkList,
    this.diskonPersen = 0,
    this.cashDiterima = 0,
    this.isMember = false,
    this.memberNama,
  });

  int get subtotal => produkList.fold(0, (s, p) => s + p.subtotal);
  int get nilaiDiskon => (subtotal * diskonPersen / 100).round();
  int get totalBayar => subtotal - nilaiDiskon;
  int get kembalian => (cashDiterima - totalBayar).clamp(0, 999999999);
}

// ── Model status pengiriman ───────────────────────────────────────────────────
enum _ShipStatus { pending, done, active }

class _ShipStep {
  final IconData icon;
  final String label;
  final String? sublabel1; // tanggal/waktu atau nama kurir
  final String? sublabel2; // nomor HP kurir
  final _ShipStatus status;

  const _ShipStep({
    required this.icon,
    required this.label,
    this.sublabel1,
    this.sublabel2,
    required this.status,
  });
}

// ── Dummy kendaraan ───────────────────────────────────────────────────────────
const List<String> _kendaraanList = [
  'Motor - B 1234 XYZ (Andi)',
  'Motor - B 5678 ABC (Budi)',
  'Mobil Pick-up - B 9012 DEF (Rusdi)',
];

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

// ════════════════════════════════════════════════════════════════════════════
// PAGE
// ════════════════════════════════════════════════════════════════════════════
class DetailTransaksiPage extends StatefulWidget {
  final DetailTransaksiData transaksi;
  const DetailTransaksiPage({super.key, required this.transaksi});

  @override
  State<DetailTransaksiPage> createState() => _DetailTransaksiPageState();
}

class _DetailTransaksiPageState extends State<DetailTransaksiPage> {
  static const _blue = Color(0xFF3B6FE8);
  static const _green = Color(0xFF38A169);

  // ── Pengiriman form ───────────────────────────────────────────────────────
  bool? _pilihanKirim;
  bool _sudahDikirim = false; // ← true setelah kirim ke logistik

  late TextEditingController _namaPenerimaCtrl;
  late TextEditingController _teleponPenerimaCtrl;
  final _alamatCtrl = TextEditingController();
  final _tglKirimCtrl = TextEditingController();
  final _biayaKirimCtrl = TextEditingController();
  final _catatanCtrl = TextEditingController();
  String? _kendaraan;
  DateTime? _tglKirim;
  late List<bool> _barangDikirim;

  // Data pengiriman yang tersimpan
  String _savedNama = '';
  String _savedTelepon = '';
  String _savedAlamat = '';
  String _savedKendaraan = '';
  String _savedTanggal = '';

  @override
  void initState() {
    super.initState();
    _namaPenerimaCtrl = TextEditingController(
      text: widget.transaksi.namaPelanggan,
    );
    _teleponPenerimaCtrl = TextEditingController(
      text: widget.transaksi.noTelepon,
    );
    _barangDikirim = List.filled(widget.transaksi.produkList.length, false);
  }

  @override
  void dispose() {
    _namaPenerimaCtrl.dispose();
    _teleponPenerimaCtrl.dispose();
    _alamatCtrl.dispose();
    _tglKirimCtrl.dispose();
    _biayaKirimCtrl.dispose();
    _catatanCtrl.dispose();
    super.dispose();
  }

  // ── Date picker ───────────────────────────────────────────────────────────
  Future<void> _pickTanggal() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: _tglKirim ?? DateTime.now(),
      firstDate: DateTime.now(),
      lastDate: DateTime.now().add(const Duration(days: 60)),
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
    if (picked != null)
      setState(() {
        _tglKirim = picked;
        _tglKirimCtrl.text =
            '${picked.day.toString().padLeft(2, '0')}/'
            '${picked.month.toString().padLeft(2, '0')}/'
            '${picked.year}';
      });
  }

  // ── Kirim ke logistik ─────────────────────────────────────────────────────
  void _kirimKeLogistik() {
    if (_alamatCtrl.text.trim().isEmpty) {
      _snack('Alamat pengiriman wajib diisi', Colors.red);
      return;
    }
    if (_tglKirim == null) {
      _snack('Tanggal pengiriman wajib dipilih', Colors.red);
      return;
    }
    if (_kendaraan == null) {
      _snack('Pilih kendaraan pengiriman', Colors.red);
      return;
    }
    showDialog(
      context: context,
      builder: (_) => _ConfirmDialog(
        icon: Icons.local_shipping_rounded,
        title: 'Kirim ke Logistik?',
        message:
            'Pesanan akan diteruskan ke bagian logistik untuk ditugaskan ke kurir.',
        confirmLabel: 'Kirim',
        onConfirm: () {
          // TODO: POST /api/pengiriman
          setState(() {
            _savedNama = _namaPenerimaCtrl.text;
            _savedTelepon = _teleponPenerimaCtrl.text;
            _savedAlamat = _alamatCtrl.text;
            _savedKendaraan = _kendaraan ?? '';
            _savedTanggal = _tglKirimCtrl.text;
            _sudahDikirim = true;
          });
          _snack('Pesanan berhasil dikirim ke logistik!', _green);
        },
      ),
    );
  }

  // ── Print struk ───────────────────────────────────────────────────────────
  void _showStruk() {
    showDialog(
      context: context,
      barrierColor: Colors.black54,
      builder: (_) => Dialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        insetPadding: const EdgeInsets.symmetric(horizontal: 24, vertical: 32),
        child: _StrukDialog(transaksi: widget.transaksi),
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
    final t = widget.transaksi;
    return Scaffold(
      backgroundColor: const Color(0xFFF3F4F8),
      appBar: AppBar(
        backgroundColor: _blue,
        foregroundColor: Colors.white,
        elevation: 0,
        shape: const RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(bottom: Radius.circular(20)),
        ),
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Detail Transaksi',
              style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold),
            ),
            Text(
              t.invoice,
              style: const TextStyle(
                fontSize: 11,
                color: Colors.white60,
                fontWeight: FontWeight.w400,
              ),
            ),
          ],
        ),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new_rounded),
          onPressed: () => Navigator.pop(context),
        ),
        actions: [
          IconButton(
            onPressed: _showStruk,
            icon: const Icon(Icons.print_rounded),
            tooltip: 'Cetak Struk',
          ),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.fromLTRB(16, 16, 16, 40),
        child: Column(
          children: [
            _buildInfoTransaksi(t),
            const SizedBox(height: 12),
            _buildInfoPelanggan(t),
            const SizedBox(height: 12),
            _buildDaftarProduk(t),
            const SizedBox(height: 12),
            _buildRingkasan(t),
            const SizedBox(height: 12),
            _buildPengiriman(t),
          ],
        ),
      ),
    );
  }

  // ════════════════════════════════════════════════════════════════════════
  // SECTION: Info Transaksi
  // ════════════════════════════════════════════════════════════════════════
  Widget _buildInfoTransaksi(DetailTransaksiData t) => _Card(
    title: 'Informasi Transaksi',
    child: Column(
      children: [
        _row('Invoice', t.invoice, valueBold: true),
        _divRow(),
        _row('Tanggal', '${t.tanggal}  •  ${t.waktu}'),
        _divRow(),
        _row('Kasir', t.kasir),
        _divRow(),
        _rowW('Status', _statusBadge(t.status)),
      ],
    ),
  );

  // ════════════════════════════════════════════════════════════════════════
  // SECTION: Info Pelanggan
  // ════════════════════════════════════════════════════════════════════════
  Widget _buildInfoPelanggan(DetailTransaksiData t) => _Card(
    title: 'Informasi Pelanggan',
    child: Column(
      children: [
        _row('Nama', t.namaPelanggan),
        _divRow(),
        _row('Telepon', t.noTelepon.isEmpty ? '-' : t.noTelepon),
        if (t.isMember && t.memberNama != null) ...[
          _divRow(),
          _row('Member', t.memberNama!),
        ],
        _divRow(),
        _rowW('Metode Bayar', _metodeBadge(t.metodePembayaran)),
      ],
    ),
  );

  // ════════════════════════════════════════════════════════════════════════
  // SECTION: Daftar Produk
  // ════════════════════════════════════════════════════════════════════════
  Widget _buildDaftarProduk(DetailTransaksiData t) => _Card(
    title: 'Daftar Produk  (${t.produkList.length} item)',
    child: Column(
      children: [
        // Header kolom
        Row(
          children: [
            const Expanded(
              child: Text(
                'Produk',
                style: TextStyle(
                  fontSize: 11,
                  fontWeight: FontWeight.w600,
                  color: Color(0xFF718096),
                ),
              ),
            ),
            const SizedBox(width: 8),
            SizedBox(
              width: 30,
              child: Text(
                'Qty',
                textAlign: TextAlign.center,
                style: const TextStyle(
                  fontSize: 11,
                  fontWeight: FontWeight.w600,
                  color: Color(0xFF718096),
                ),
              ),
            ),
            SizedBox(
              width: 78,
              child: Text(
                'Subtotal',
                textAlign: TextAlign.right,
                style: const TextStyle(
                  fontSize: 11,
                  fontWeight: FontWeight.w600,
                  color: Color(0xFF718096),
                ),
              ),
            ),
          ],
        ),
        const SizedBox(height: 6),
        const Divider(height: 1),
        const SizedBox(height: 6),
        // Item
        ...t.produkList.asMap().entries.map((e) {
          final i = e.key;
          final p = e.value;
          return Column(
            children: [
              if (i > 0) const Divider(height: 16, color: Color(0xFFF0F0F0)),
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          p.nama,
                          style: const TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.w500,
                            color: Color(0xFF2D3748),
                          ),
                        ),
                        const SizedBox(height: 1),
                        Text(
                          _rp(p.harga),
                          style: TextStyle(
                            fontSize: 11,
                            color: Colors.grey.shade500,
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 8),
                  SizedBox(
                    width: 30,
                    child: Text(
                      '${p.qty}',
                      textAlign: TextAlign.center,
                      style: const TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ),
                  SizedBox(
                    width: 78,
                    child: Text(
                      _rp(p.subtotal),
                      textAlign: TextAlign.right,
                      style: const TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w600,
                        color: Color(0xFF2D3748),
                      ),
                    ),
                  ),
                ],
              ),
            ],
          );
        }),
      ],
    ),
  );

  // ════════════════════════════════════════════════════════════════════════
  // SECTION: Ringkasan Pembayaran
  // ════════════════════════════════════════════════════════════════════════
  Widget _buildRingkasan(DetailTransaksiData t) => _Card(
    title: 'Ringkasan Pembayaran',
    child: Column(
      children: [
        _row('Subtotal', _rp(t.subtotal)),
        if (t.diskonPersen > 0) ...[
          _divRow(),
          _row('Diskon (${t.diskonPersen}%)', '- ${_rp(t.nilaiDiskon)}'),
        ],
        const SizedBox(height: 10),
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
          decoration: BoxDecoration(
            color: const Color(0xFFF0F4FF),
            borderRadius: BorderRadius.circular(8),
          ),
          child: Row(
            children: [
              const Text(
                'Total Bayar',
                style: TextStyle(
                  fontSize: 13,
                  fontWeight: FontWeight.bold,
                  color: Color(0xFF2D3748),
                ),
              ),
              const Spacer(),
              Text(
                _rp(t.totalBayar),
                style: const TextStyle(
                  fontSize: 15,
                  fontWeight: FontWeight.bold,
                  color: _blue,
                ),
              ),
            ],
          ),
        ),
        if (t.metodePembayaran == 'Tunai' && t.cashDiterima > 0) ...[
          const SizedBox(height: 10),
          _divRow(),
          _row('Cash Diterima', _rp(t.cashDiterima)),
          _divRow(),
          _row('Kembalian', _rp(t.kembalian)),
        ] else if (t.metodePembayaran == 'Hutang') ...[
          const SizedBox(height: 8),
          Row(
            children: [
              Icon(
                Icons.info_outline_rounded,
                size: 13,
                color: Colors.grey.shade400,
              ),
              const SizedBox(width: 6),
              Expanded(
                child: Text(
                  'Dibebankan ke limit kredit member',
                  style: TextStyle(fontSize: 11, color: Colors.grey.shade500),
                ),
              ),
            ],
          ),
        ],
      ],
    ),
  );

  // ════════════════════════════════════════════════════════════════════════
  // SECTION: Opsi Pengiriman
  // ════════════════════════════════════════════════════════════════════════
  Widget _buildPengiriman(DetailTransaksiData t) => _Card(
    title: 'Opsi Pengiriman',
    child: Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Dua opsi pilihan
        _PilihanTile(
          label: 'Ya, perlu dikirim',
          sublabel: 'Pesanan dikirim ke alamat pelanggan',
          icon: Icons.local_shipping_outlined,
          selected: _pilihanKirim == true,
          onTap: _sudahDikirim
              ? null
              : () => setState(() => _pilihanKirim = true),
        ),
        const SizedBox(height: 8),
        _PilihanTile(
          label: 'Tidak perlu dikirim',
          sublabel: 'Pelanggan ambil sendiri di toko',
          icon: Icons.store_outlined,
          selected: _pilihanKirim == false,
          onTap: _sudahDikirim
              ? null
              : () => setState(() => _pilihanKirim = false),
        ),

        // ── Ambil sendiri ────────────────────────────────────────────────────
        if (_pilihanKirim == false) ...[
          const SizedBox(height: 14),
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: Colors.grey.shade50,
              borderRadius: BorderRadius.circular(8),
              border: Border.all(color: Colors.grey.shade200),
            ),
            child: Row(
              children: [
                Icon(
                  Icons.store_rounded,
                  size: 16,
                  color: Colors.grey.shade500,
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Text(
                    'Pelanggan akan mengambil pesanan sendiri di toko.',
                    style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
                  ),
                ),
              ],
            ),
          ),
        ],

        // ── Form pengiriman ──────────────────────────────────────────────────
        if (_pilihanKirim == true && !_sudahDikirim) ...[
          const SizedBox(height: 18),
          const Divider(),
          const SizedBox(height: 14),

          _flbl('Nama Penerima'),
          _fld(_namaPenerimaCtrl, 'Nama penerima'),
          const SizedBox(height: 10),

          _flbl('No Telepon Penerima'),
          _fld(
            _teleponPenerimaCtrl,
            'Nomor telepon',
            type: TextInputType.phone,
          ),
          const SizedBox(height: 10),

          _flbl('Alamat Pengiriman', req: true),
          _fld(_alamatCtrl, 'Alamat lengkap pengiriman', maxLines: 3),
          const SizedBox(height: 10),

          _flbl('Tanggal Pengiriman', req: true),
          _dateFieldWidget(),
          const SizedBox(height: 10),

          _flbl('Daftar Barang Dikirim'),
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: Colors.grey.shade50,
              borderRadius: BorderRadius.circular(8),
              border: Border.all(color: Colors.grey.shade200),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Kosongkan jika semua barang dikirim',
                  style: TextStyle(
                    fontSize: 11,
                    color: Colors.grey.shade500,
                    fontStyle: FontStyle.italic,
                  ),
                ),
                const SizedBox(height: 4),
                ...List.generate(
                  t.produkList.length,
                  (i) => CheckboxListTile(
                    value: _barangDikirim[i],
                    onChanged: (v) =>
                        setState(() => _barangDikirim[i] = v ?? false),
                    contentPadding: EdgeInsets.zero,
                    dense: true,
                    visualDensity: const VisualDensity(vertical: -3),
                    title: Text(
                      '${t.produkList[i].nama} (${t.produkList[i].qty} pcs)',
                      style: const TextStyle(
                        fontSize: 12,
                        color: Color(0xFF2D3748),
                      ),
                    ),
                    controlAffinity: ListTileControlAffinity.leading,
                    activeColor: _blue,
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 10),

          _flbl('Pilih Kendaraan', req: true),
          _dropdownWidget(),
          const SizedBox(height: 10),

          _flbl('Biaya Pengiriman'),
          TextField(
            controller: _biayaKirimCtrl,
            keyboardType: TextInputType.number,
            inputFormatters: [FilteringTextInputFormatter.digitsOnly],
            style: const TextStyle(fontSize: 13),
            decoration: _dec('Rp 0', prefix: 'Rp '),
          ),
          const SizedBox(height: 10),

          _flbl('Catatan Pengiriman'),
          _fld(_catatanCtrl, 'Tambahkan catatan...', maxLines: 2),
          const SizedBox(height: 14),

          // Note info
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
            decoration: BoxDecoration(
              color: Colors.grey.shade50,
              borderRadius: BorderRadius.circular(8),
              border: Border.all(color: Colors.grey.shade200),
            ),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Icon(
                  Icons.info_outline_rounded,
                  size: 13,
                  color: Colors.grey.shade400,
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: Text(
                    'Pesanan akan diteruskan ke bagian logistik untuk ditugaskan ke kurir.',
                    style: TextStyle(
                      fontSize: 11,
                      color: Colors.grey.shade500,
                      height: 1.4,
                    ),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 14),

          SizedBox(
            width: double.infinity,
            child: ElevatedButton.icon(
              onPressed: _kirimKeLogistik,
              icon: const Icon(Icons.send_rounded, size: 16),
              label: const Text(
                'Kirim ke Logistik',
                style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600),
              ),
              style: ElevatedButton.styleFrom(
                backgroundColor: _blue,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(vertical: 13),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(10),
                ),
                elevation: 0,
              ),
            ),
          ),
        ],

        // ════════════════════════════════════════════════════════════════════
        // STATUS PENGIRIMAN — muncul setelah kirim ke logistik
        // ════════════════════════════════════════════════════════════════════
        if (_pilihanKirim == true && _sudahDikirim) ...[
          const SizedBox(height: 20),
          const Divider(),
          const SizedBox(height: 16),
          _buildStatusPengiriman(),
        ],
      ],
    ),
  );

  // ── Status pengiriman tracker ─────────────────────────────────────────────
  Widget _buildStatusPengiriman() {
    // Dummy: anggap sudah sampai "Ditugaskan ke Kurir"
    // Status: done = sudah lewat, active = saat ini, pending = belum
    final steps = [
      _ShipStep(
        icon: Icons.check_rounded,
        label: 'Pesanan Diterima',
        sublabel1:
            '${_savedTanggal.isNotEmpty ? _savedTanggal : "23/03/2026"}  02:01',
        status: _ShipStatus.done,
      ),
      _ShipStep(
        icon: Icons.check_rounded,
        label: 'Diproses Logistik',
        sublabel1:
            '${_savedTanggal.isNotEmpty ? _savedTanggal : "23/03/2026"}  12:13',
        status: _ShipStatus.done,
      ),
      _ShipStep(
        icon: Icons.person_rounded,
        label: 'Ditugaskan ke Kurir',
        sublabel1: 'luhut',
        sublabel2: '0831 4287 8951',
        status: _ShipStatus.active,
      ),
      _ShipStep(
        icon: Icons.local_shipping_rounded,
        label: 'Dalam Perjalanan',
        status: _ShipStatus.pending,
      ),
      _ShipStep(
        icon: Icons.check_rounded,
        label: 'Terkirim',
        status: _ShipStatus.pending,
      ),
    ];

    // Overall status badge label
    const overallLabel = 'Dalam Proses';

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Header status pengiriman
        Row(
          children: [
            const Icon(Icons.local_shipping_rounded, color: _green, size: 20),
            const SizedBox(width: 8),
            const Text(
              'Status Pengiriman',
              style: TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.bold,
                color: Color(0xFF2D3748),
              ),
            ),
            const Spacer(),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
              decoration: BoxDecoration(
                color: _green.withOpacity(0.10),
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: _green.withOpacity(0.3)),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(Icons.check_circle_rounded, size: 12, color: _green),
                  const SizedBox(width: 4),
                  Text(
                    overallLabel,
                    style: const TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w600,
                      color: _green,
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
        const SizedBox(height: 20),

        // Timeline steps
        ...List.generate(steps.length, (i) {
          final step = steps[i];
          final isLast = i == steps.length - 1;
          return _TimelineTile(step: step, isLast: isLast);
        }),

        const SizedBox(height: 16),
        const Divider(),
        const SizedBox(height: 12),

        // Info kendaraan & alamat
        _row('Kendaraan', _savedKendaraan.split(' - ').first.trim()),
        _divRow(),
        _row('Alamat', _savedAlamat),
      ],
    );
  }

  // ── Field helpers ─────────────────────────────────────────────────────────
  Widget _flbl(String text, {bool req = false}) => Padding(
    padding: const EdgeInsets.only(bottom: 6),
    child: Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Text(
          text,
          style: const TextStyle(
            fontSize: 12,
            fontWeight: FontWeight.w500,
            color: Color(0xFF4A5568),
          ),
        ),
        if (req)
          const Text(' *', style: TextStyle(color: Colors.red, fontSize: 12)),
      ],
    ),
  );

  InputDecoration _dec(String hint, {String? prefix}) => InputDecoration(
    hintText: hint,
    hintStyle: TextStyle(color: Colors.grey.shade400, fontSize: 13),
    prefixText: prefix,
    filled: true,
    fillColor: Colors.white,
    contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 11),
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
  );

  Widget _fld(
    TextEditingController ctrl,
    String hint, {
    int maxLines = 1,
    TextInputType type = TextInputType.text,
  }) => TextField(
    controller: ctrl,
    maxLines: maxLines,
    keyboardType: type,
    style: const TextStyle(fontSize: 13),
    decoration: _dec(hint),
  );

  Widget _dateFieldWidget() => TextField(
    controller: _tglKirimCtrl,
    readOnly: true,
    style: const TextStyle(fontSize: 13),
    decoration: _dec('Pilih tanggal').copyWith(
      suffixIcon: IconButton(
        onPressed: _pickTanggal,
        icon: Icon(
          Icons.calendar_month_rounded,
          size: 18,
          color: Colors.grey.shade500,
        ),
      ),
    ),
  );

  Widget _dropdownWidget() => Container(
    padding: const EdgeInsets.symmetric(horizontal: 12),
    decoration: BoxDecoration(
      color: Colors.white,
      borderRadius: BorderRadius.circular(8),
      border: Border.all(color: Colors.grey.shade300),
    ),
    child: DropdownButtonHideUnderline(
      child: DropdownButton<String>(
        value: _kendaraan,
        isExpanded: true,
        hint: Text(
          '-- Pilih kendaraan --',
          style: TextStyle(color: Colors.grey.shade400, fontSize: 13),
        ),
        style: const TextStyle(fontSize: 13, color: Color(0xFF2D3748)),
        icon: const Icon(Icons.keyboard_arrow_down_rounded, size: 20),
        items: _kendaraanList
            .map(
              (k) => DropdownMenuItem(
                value: k,
                child: Text(k, style: const TextStyle(fontSize: 13)),
              ),
            )
            .toList(),
        onChanged: (v) => setState(() => _kendaraan = v),
      ),
    ),
  );

  // ── Row helpers ───────────────────────────────────────────────────────────
  Widget _row(String label, String value, {bool valueBold = false}) => Padding(
    padding: const EdgeInsets.symmetric(vertical: 5),
    child: Row(
      children: [
        Expanded(
          child: Text(
            label,
            style: TextStyle(fontSize: 13, color: Colors.grey.shade500),
          ),
        ),
        const SizedBox(width: 16),
        Flexible(
          child: Text(
            value,
            textAlign: TextAlign.right,
            style: TextStyle(
              fontSize: 13,
              fontWeight: valueBold ? FontWeight.bold : FontWeight.w500,
              color: const Color(0xFF2D3748),
            ),
          ),
        ),
      ],
    ),
  );

  Widget _rowW(String label, Widget w) => Padding(
    padding: const EdgeInsets.symmetric(vertical: 5),
    child: Row(
      children: [
        Expanded(
          child: Text(
            label,
            style: TextStyle(fontSize: 13, color: Colors.grey.shade500),
          ),
        ),
        w,
      ],
    ),
  );

  Widget _divRow() => const Divider(height: 1, color: Color(0xFFF0F0F0));

  // ── Badges ────────────────────────────────────────────────────────────────
  Widget _statusBadge(String s) {
    Color c;
    switch (s) {
      case 'Lunas':
        c = const Color(0xFF38A169);
        break;
      case 'Kredit':
      case 'Hutang':
        c = const Color(0xFFE53E3E);
        break;
      default:
        c = const Color(0xFFD69E2E);
    }
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: c.withOpacity(0.10),
        borderRadius: BorderRadius.circular(20),
      ),
      child: Text(
        s,
        style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: c),
      ),
    );
  }

  Widget _metodeBadge(String m) {
    const map = {
      'Tunai': Color(0xFF38A169),
      'Debit': Color(0xFF4169E1),
      'E-Wallet': Color(0xFF6B5CE7),
      'Hutang': Color(0xFFE53E3E),
    };
    final c = map[m] ?? Colors.grey;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: c.withOpacity(0.10),
        borderRadius: BorderRadius.circular(20),
      ),
      child: Text(
        m,
        style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: c),
      ),
    );
  }
}

// ════════════════════════════════════════════════════════════════════════════
// TIMELINE TILE
// ════════════════════════════════════════════════════════════════════════════

class _TimelineTile extends StatelessWidget {
  final _ShipStep step;
  final bool isLast;
  const _TimelineTile({required this.step, required this.isLast});

  static const _green = Color(0xFF38A169);

  @override
  Widget build(BuildContext context) {
    final isDone = step.status == _ShipStatus.done;
    final isActive = step.status == _ShipStatus.active;
    final isPending = step.status == _ShipStatus.pending;

    // Warna dot
    final dotColor = isDone
        ? _green
        : isActive
        ? Colors.grey.shade400
        : Colors.grey.shade300;

    // Icon color
    final iconColor = isDone
        ? Colors.white
        : isActive
        ? Colors.white
        : Colors.grey.shade400;

    return IntrinsicHeight(
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // ── Timeline garis + dot ─────────────────────────────────────────────
          SizedBox(
            width: 44,
            child: Column(
              mainAxisAlignment: MainAxisAlignment.start,
              children: [
                // Dot
                Container(
                  width: 36,
                  height: 36,
                  decoration: BoxDecoration(
                    color: dotColor,
                    shape: BoxShape.circle,
                    border: isActive
                        ? Border.all(color: Colors.grey.shade300, width: 2)
                        : null,
                  ),
                  child: Icon(step.icon, color: iconColor, size: 18),
                ),
                // Garis vertikal
                if (!isLast)
                  Expanded(
                    child: Container(
                      width: 2,
                      margin: const EdgeInsets.symmetric(vertical: 4),
                      decoration: BoxDecoration(
                        color: isDone
                            ? _green.withOpacity(0.3)
                            : Colors.grey.shade200,
                        borderRadius: BorderRadius.circular(1),
                      ),
                    ),
                  ),
              ],
            ),
          ),

          // ── Label ─────────────────────────────────────────────────────────────
          Expanded(
            child: Padding(
              padding: const EdgeInsets.only(left: 10, bottom: 20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const SizedBox(height: 7),
                  Text(
                    step.label,
                    style: TextStyle(
                      fontSize: 14,
                      fontWeight: isDone || isActive
                          ? FontWeight.bold
                          : FontWeight.normal,
                      color: isPending
                          ? Colors.grey.shade400
                          : const Color(0xFF2D3748),
                    ),
                  ),
                  if (step.sublabel1 != null) ...[
                    const SizedBox(height: 2),
                    Text(
                      step.sublabel1!,
                      style: TextStyle(
                        fontSize: 12,
                        color: Colors.grey.shade500,
                      ),
                    ),
                  ],
                  if (step.sublabel2 != null) ...[
                    const SizedBox(height: 1),
                    Text(
                      step.sublabel2!,
                      style: TextStyle(
                        fontSize: 12,
                        color: Colors.grey.shade500,
                      ),
                    ),
                  ],
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}

// ════════════════════════════════════════════════════════════════════════════
// CARD WRAPPER
// ════════════════════════════════════════════════════════════════════════════
class _Card extends StatelessWidget {
  final String title;
  final Widget child;
  const _Card({required this.title, required this.child});

  @override
  Widget build(BuildContext context) => Container(
    width: double.infinity,
    padding: const EdgeInsets.fromLTRB(16, 14, 16, 16),
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
        Text(
          title,
          style: const TextStyle(
            fontSize: 12,
            fontWeight: FontWeight.w600,
            color: Color(0xFF718096),
            letterSpacing: 0.3,
          ),
        ),
        const SizedBox(height: 10),
        const Divider(height: 1),
        const SizedBox(height: 10),
        child,
      ],
    ),
  );
}

// ════════════════════════════════════════════════════════════════════════════
// PILIHAN TILE — radio card
// ════════════════════════════════════════════════════════════════════════════
class _PilihanTile extends StatelessWidget {
  final String label, sublabel;
  final IconData icon;
  final bool selected;
  final VoidCallback? onTap;
  const _PilihanTile({
    required this.label,
    required this.sublabel,
    required this.icon,
    required this.selected,
    this.onTap,
  });

  static const _blue = Color(0xFF3B6FE8);

  @override
  Widget build(BuildContext context) => GestureDetector(
    onTap: onTap,
    child: Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
      decoration: BoxDecoration(
        color: selected ? const Color(0xFFF0F4FF) : const Color(0xFFF8F9FA),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(
          color: selected ? _blue : Colors.grey.shade200,
          width: selected ? 1.5 : 1,
        ),
      ),
      child: Row(
        children: [
          // Radio dot
          Container(
            width: 18,
            height: 18,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              border: Border.all(
                color: selected ? _blue : Colors.grey.shade400,
                width: 2,
              ),
              color: selected ? _blue : Colors.transparent,
            ),
            child: selected
                ? const Icon(Icons.check_rounded, color: Colors.white, size: 11)
                : null,
          ),
          const SizedBox(width: 10),
          Icon(icon, size: 16, color: selected ? _blue : Colors.grey.shade400),
          const SizedBox(width: 8),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  label,
                  style: TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w500,
                    color: selected ? _blue : const Color(0xFF2D3748),
                  ),
                ),
                Text(
                  sublabel,
                  style: TextStyle(fontSize: 11, color: Colors.grey.shade500),
                ),
              ],
            ),
          ),
        ],
      ),
    ),
  );
}

// ════════════════════════════════════════════════════════════════════════════
// CONFIRM DIALOG
// ════════════════════════════════════════════════════════════════════════════
class _ConfirmDialog extends StatelessWidget {
  final IconData icon;
  final String title, message, confirmLabel;
  final VoidCallback onConfirm;
  const _ConfirmDialog({
    required this.icon,
    required this.title,
    required this.message,
    required this.confirmLabel,
    required this.onConfirm,
  });

  static const _blue = Color(0xFF3B6FE8);

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
            color: _blue.withOpacity(0.08),
            shape: BoxShape.circle,
          ),
          child: Icon(icon, color: _blue, size: 26),
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
                backgroundColor: _blue,
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

// ════════════════════════════════════════════════════════════════════════════
// STRUK DIALOG
// ════════════════════════════════════════════════════════════════════════════
class _StrukDialog extends StatelessWidget {
  final DetailTransaksiData transaksi;
  const _StrukDialog({required this.transaksi});

  static const _mono = TextStyle(fontFamily: 'monospace', fontSize: 12);
  static const _monoBold = TextStyle(
    fontFamily: 'monospace',
    fontSize: 12,
    fontWeight: FontWeight.bold,
  );

  String _rpStr(int n) {
    if (n == 0) return 'Rp 0';
    final s = n.toString();
    final buf = StringBuffer('Rp ');
    for (int i = 0; i < s.length; i++) {
      if (i > 0 && (s.length - i) % 3 == 0) buf.write('.');
      buf.write(s[i]);
    }
    return buf.toString();
  }

  @override
  Widget build(BuildContext context) {
    final t = transaksi;
    return Container(
      width: 300,
      color: Colors.white,
      child: SingleChildScrollView(
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 20),
          child: Column(
            children: [
              // Header
              const Text(
                'TOKO  RONI',
                style: TextStyle(
                  fontFamily: 'monospace',
                  fontSize: 15,
                  fontWeight: FontWeight.bold,
                  letterSpacing: 2,
                ),
              ),
              const SizedBox(height: 3),
              const Text('Jl. H.Hasan', style: _mono),
              const Text('Telp: 0812-3456-7890', style: _mono),
              const SizedBox(height: 8),
              const Text(
                'STRUK  TOKO  RONI',
                style: TextStyle(
                  fontFamily: 'monospace',
                  fontSize: 13,
                  fontWeight: FontWeight.bold,
                  letterSpacing: 1,
                ),
              ),
              const SizedBox(height: 6),
              _garis(),
              const SizedBox(height: 6),
              _sRow('No. Invoice', t.invoice, bold: true),
              _sRow('Tanggal', '${t.tanggal}  ${t.waktu}', bold: true),
              _sRow('Kasir', t.kasir, bold: true),
              _sRow('Pelanggan', t.namaPelanggan, bold: true),
              const SizedBox(height: 6),
              _garisTitik(),
              const SizedBox(height: 6),
              // Header produk
              Row(
                children: const [
                  Expanded(flex: 4, child: Text('Item', style: _monoBold)),
                  SizedBox(
                    width: 26,
                    child: Text(
                      'Qty',
                      style: _monoBold,
                      textAlign: TextAlign.center,
                    ),
                  ),
                  SizedBox(
                    width: 54,
                    child: Text(
                      'Harga',
                      style: _monoBold,
                      textAlign: TextAlign.right,
                    ),
                  ),
                  SizedBox(
                    width: 58,
                    child: Text(
                      'Subtotal',
                      style: _monoBold,
                      textAlign: TextAlign.right,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 4),
              _garisTitik(),
              const SizedBox(height: 4),
              // Produk
              ...t.produkList.map(
                (p) => Padding(
                  padding: const EdgeInsets.symmetric(vertical: 2),
                  child: Row(
                    children: [
                      Expanded(
                        flex: 4,
                        child: Text(
                          p.nama,
                          style: _mono,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                      SizedBox(
                        width: 26,
                        child: Text(
                          '${p.qty}',
                          style: _mono,
                          textAlign: TextAlign.center,
                        ),
                      ),
                      SizedBox(
                        width: 54,
                        child: Text(
                          _rpStr(p.harga),
                          style: _mono,
                          textAlign: TextAlign.right,
                        ),
                      ),
                      SizedBox(
                        width: 58,
                        child: Text(
                          _rpStr(p.subtotal),
                          style: _mono,
                          textAlign: TextAlign.right,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 4),
              _garis(),
              const SizedBox(height: 6),
              _sRow('Subtotal', _rpStr(t.subtotal)),
              if (t.diskonPersen > 0)
                _sRow(
                  'Diskon ${t.diskonPersen}%',
                  '- ${_rpStr(t.nilaiDiskon)}',
                ),
              const SizedBox(height: 4),
              Row(
                children: [
                  const Expanded(
                    child: Text(
                      'Total',
                      style: TextStyle(
                        fontFamily: 'monospace',
                        fontSize: 13,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                  Text(
                    _rpStr(t.totalBayar),
                    style: const TextStyle(
                      fontFamily: 'monospace',
                      fontSize: 13,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ],
              ),
              if (t.metodePembayaran == 'Tunai' && t.cashDiterima > 0) ...[
                const SizedBox(height: 4),
                _sRow('Tunai', _rpStr(t.cashDiterima)),
                _sRow('Kembali', _rpStr(t.kembalian)),
              ],
              const SizedBox(height: 6),
              _garisTitik(),
              const SizedBox(height: 6),
              _sRow('Metode Pembayaran', t.metodePembayaran.toUpperCase()),
              const SizedBox(height: 6),
              _garis(),
              const SizedBox(height: 10),
              const Text(
                'Terima Kasih atas Kunjungan Anda',
                style: _mono,
                textAlign: TextAlign.center,
              ),
              const Text(
                'Barang yang sudah dibeli tidak dapat',
                style: _mono,
                textAlign: TextAlign.center,
              ),
              const Text(
                'dikembalikan',
                style: _mono,
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 4),
              const Text('www.tokoroni.com', style: _mono),
              const SizedBox(height: 16),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton.icon(
                  onPressed: () {
                    Navigator.pop(context);
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(
                        content: Text('Mencetak struk...'),
                        backgroundColor: Color(0xFF48BB78),
                        behavior: SnackBarBehavior.floating,
                      ),
                    );
                  },
                  icon: const Icon(Icons.print_rounded, size: 16),
                  label: const Text(
                    'Cetak Struk',
                    style: TextStyle(fontWeight: FontWeight.w600, fontSize: 13),
                  ),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF3B6FE8),
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 11),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(10),
                    ),
                    elevation: 0,
                  ),
                ),
              ),
              TextButton(
                onPressed: () => Navigator.pop(context),
                child: const Text(
                  'Tutup',
                  style: TextStyle(color: Colors.grey, fontSize: 13),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _garis() => const Text(
    '----------------------------------------',
    style: TextStyle(
      fontFamily: 'monospace',
      fontSize: 10,
      color: Colors.black87,
    ),
  );
  Widget _garisTitik() => const Text(
    '- - - - - - - - - - - - - - - - - - - -',
    style: TextStyle(
      fontFamily: 'monospace',
      fontSize: 10,
      color: Colors.black54,
    ),
  );
  Widget _sRow(String label, String value, {bool bold = false}) => Padding(
    padding: const EdgeInsets.symmetric(vertical: 1.5),
    child: Row(
      children: [
        Expanded(child: Text(label, style: bold ? _monoBold : _mono)),
        Text(value, style: bold ? _monoBold : _mono),
      ],
    ),
  );
}
