import 'dart:math';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

// ════════════════════════════════════════════════════════════════════════════
// TAMBAH PRODUK PAGE
// ════════════════════════════════════════════════════════════════════════════
class TambahProdukPage extends StatefulWidget {
  const TambahProdukPage({super.key});

  @override
  State<TambahProdukPage> createState() => _TambahProdukPageState();
}

class _TambahProdukPageState extends State<TambahProdukPage> {
  // Controllers
  final _namaCtrl = TextEditingController();
  final _kodeCtrl = TextEditingController();
  final _deskripsiCtrl = TextEditingController();
  final _hargaJualCtrl = TextEditingController(text: '0');
  final _hargaModalCtrl = TextEditingController(text: '0');
  final _stokAwalCtrl = TextEditingController(text: '0');
  final _stokMinCtrl = TextEditingController(text: '0');
  final _barcodeCtrl = TextEditingController(text: '0');
  final _beratCtrl = TextEditingController(text: '0');
  final _dimensiCtrl = TextEditingController();
  final _kadaluarsaCtrl = TextEditingController();

  String? _kategori;
  String _satuan = 'Dus';
  bool _aktif = true;
  DateTime? _tglKadaluarsa;

  // Error messages
  final Map<String, String?> _errors = {};

  static const _kategoriList = [
    'Makanan',
    'Minuman',
    'Sembako',
    'Obat',
    'Kebutuhan Rumah',
  ];
  static const _satuanList = ['Dus', 'Pcs', 'Pack', 'Kg', 'Liter', 'Meter'];

  @override
  void initState() {
    super.initState();
    _generateKodeProduk();
  }

  @override
  void dispose() {
    _namaCtrl.dispose();
    _kodeCtrl.dispose();
    _deskripsiCtrl.dispose();
    _hargaJualCtrl.dispose();
    _hargaModalCtrl.dispose();
    _stokAwalCtrl.dispose();
    _stokMinCtrl.dispose();
    _barcodeCtrl.dispose();
    _beratCtrl.dispose();
    _dimensiCtrl.dispose();
    _kadaluarsaCtrl.dispose();
    super.dispose();
  }

  // ── Generate kode produk ──────────────────────────────────────────────────
  void _generateKodeProduk() {
    final rng = Random();
    final angka = List.generate(8, (_) => rng.nextInt(10)).join();
    _kodeCtrl.text = 'PRD-$angka';
    setState(() => _errors.remove('kode'));
  }

  // ── Generate barcode ──────────────────────────────────────────────────────
  void _generateBarcode() {
    final rng = Random();
    final angka = List.generate(12, (_) => rng.nextInt(10)).join();
    _barcodeCtrl.text = angka;
    setState(() => _errors.remove('barcode'));
  }

  // ── Date picker ───────────────────────────────────────────────────────────
  Future<void> _pickDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate:
          _tglKadaluarsa ?? DateTime.now().add(const Duration(days: 365)),
      firstDate: DateTime.now(),
      lastDate: DateTime(2100),
      builder: (ctx, child) => Theme(
        data: Theme.of(ctx).copyWith(
          colorScheme: const ColorScheme.light(
            primary: Color(0xFF4169E1),
            onPrimary: Colors.white,
            surface: Colors.white,
          ),
        ),
        child: child!,
      ),
    );
    if (picked != null) {
      setState(() {
        _tglKadaluarsa = picked;
        _kadaluarsaCtrl.text =
            '${picked.day.toString().padLeft(2, '0')}/${picked.month.toString().padLeft(2, '0')}/${picked.year}';
        _errors.remove('kadaluarsa');
      });
    }
  }

  // ── Validasi ──────────────────────────────────────────────────────────────
  bool _validate() {
    final e = <String, String?>{};
    if (_namaCtrl.text.trim().isEmpty) e['nama'] = 'Nama produk wajib diisi';
    if (_kodeCtrl.text.trim().isEmpty) e['kode'] = 'Kode produk wajib diisi';
    if (_kategori == null) e['kategori'] = 'Kategori wajib dipilih';
    if (_hargaJualCtrl.text.trim().isEmpty ||
        (int.tryParse(_hargaJualCtrl.text.replaceAll('.', '')) ?? 0) <= 0)
      e['hargaJual'] = 'Harga jual wajib diisi';
    if (_stokAwalCtrl.text.trim().isEmpty)
      e['stokAwal'] = 'Stok awal wajib diisi';
    if (_barcodeCtrl.text.trim().isEmpty) e['barcode'] = 'Barcode wajib diisi';
    if (_kadaluarsaCtrl.text.trim().isEmpty)
      e['kadaluarsa'] = 'Tanggal kadaluarsa wajib diisi';

    setState(() => _errors.addAll(e));
    return e.isEmpty;
  }

  // ── Reset form ────────────────────────────────────────────────────────────
  void _showResetDialog() {
    showDialog(
      context: context,
      builder: (_) => _ConfirmDialog(
        title: 'Reset Form',
        icon: Icons.refresh_rounded,
        iconColor: const Color(0xFFECC94B),
        message:
            'Apakah kamu yakin ingin mereset semua isian form? Data yang sudah diisi akan hilang.',
        confirmLabel: 'Ya, Reset',
        confirmColor: const Color(0xFFECC94B),
        onConfirm: () {
          Navigator.pop(context);
          _resetForm();
        },
      ),
    );
  }

  void _resetForm() {
    setState(() {
      _namaCtrl.clear();
      _deskripsiCtrl.clear();
      _hargaJualCtrl.text = '0';
      _hargaModalCtrl.text = '0';
      _stokAwalCtrl.text = '0';
      _stokMinCtrl.text = '0';
      _barcodeCtrl.text = '0';
      _beratCtrl.text = '0';
      _dimensiCtrl.clear();
      _kadaluarsaCtrl.clear();
      _kategori = null;
      _satuan = 'Dus';
      _aktif = true;
      _tglKadaluarsa = null;
      _errors.clear();
      _generateKodeProduk();
    });
  }

  // ── Simpan ────────────────────────────────────────────────────────────────
  void _showSimpanDialog() {
    if (!_validate()) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: const Text(
            'Harap lengkapi semua field yang wajib diisi (*)',
          ),
          backgroundColor: const Color(0xFFE53E3E),
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(10),
          ),
        ),
      );
      return;
    }
    showDialog(
      context: context,
      builder: (_) => _ConfirmDialog(
        title: 'Simpan Produk',
        icon: Icons.save_rounded,
        iconColor: const Color(0xFF4169E1),
        message:
            'Apakah semua data produk sudah benar?\n\nProduk akan ditambahkan ke dalam sistem.',
        confirmLabel: 'Ya, Simpan',
        confirmColor: const Color(0xFF4169E1),
        onConfirm: () {
          Navigator.pop(context);
          // TODO: kirim data ke API Laravel
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: const Text('Produk berhasil ditambahkan!'),
              backgroundColor: const Color(0xFF48BB78),
              behavior: SnackBarBehavior.floating,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(10),
              ),
            ),
          );
          Navigator.pop(context);
        },
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF3F4F8),
      appBar: AppBar(
        backgroundColor: const Color(0xFF4169E1),
        foregroundColor: Colors.white,
        elevation: 0,
        title: const Text(
          'Tambah Produk',
          style: TextStyle(fontWeight: FontWeight.w600, fontSize: 18),
        ),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new_rounded),
          onPressed: () => Navigator.pop(context),
        ),
        shape: const RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(bottom: Radius.circular(20)),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            // ── Form card header ──
            _FormCard(
              headerTitle: 'Form Tambah Produk',
              headerSubtitle: 'isi form dibawah untuk menambahkan produk baru',
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // ══ INFORMASI DASAR ══
                  _SectionTitle(title: 'Informasi dasar'),
                  const SizedBox(height: 16),

                  // Nama Produk
                  _FieldLabel(label: 'Nama Produk', required: true),
                  _TextField(
                    ctrl: _namaCtrl,
                    hint: 'Masukan nama produk',
                    error: _errors['nama'],
                    onChanged: (_) => setState(() => _errors.remove('nama')),
                  ),
                  const SizedBox(height: 14),

                  // Kode Produk + Kategori (row)
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Kode Produk
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            _FieldLabel(label: 'Kode Produk', required: true),
                            _TextField(
                              ctrl: _kodeCtrl,
                              hint: 'PRD-XXXXXXXX',
                              error: _errors['kode'],
                              onChanged: (_) =>
                                  setState(() => _errors.remove('kode')),
                              suffix: IconButton(
                                icon: const Icon(
                                  Icons.refresh_rounded,
                                  size: 20,
                                  color: Color(0xFF4169E1),
                                ),
                                onPressed: _generateKodeProduk,
                                tooltip: 'Generate ulang kode',
                              ),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(width: 12),
                      // Kategori
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            _FieldLabel(label: 'Kategori', required: true),
                            _DropdownField(
                              value: _kategori,
                              hint: '---pilih kategori---',
                              items: _kategoriList,
                              error: _errors['kategori'],
                              onChanged: (v) => setState(() {
                                _kategori = v;
                                _errors.remove('kategori');
                              }),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 14),

                  // Deskripsi
                  _FieldLabel(label: 'Deskripsi produk'),
                  _TextField(
                    ctrl: _deskripsiCtrl,
                    hint: 'Isi deskripsi jika perlu',
                    maxLines: 4,
                  ),
                  const SizedBox(height: 24),

                  // ══ HARGA & STOK ══
                  _SectionTitle(title: 'Harga & Stok'),
                  const SizedBox(height: 16),

                  // Harga Jual + Harga Modal
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            _FieldLabel(label: 'Harga Jual', required: true),
                            _TextField(
                              ctrl: _hargaJualCtrl,
                              hint: 'Rp 0',
                              type: TextInputType.number,
                              prefix: const Text(
                                'Rp ',
                                style: TextStyle(
                                  fontSize: 13,
                                  color: Color(0xFF4A5568),
                                ),
                              ),
                              error: _errors['hargaJual'],
                              onChanged: (_) =>
                                  setState(() => _errors.remove('hargaJual')),
                              inputFormatters: [
                                FilteringTextInputFormatter.digitsOnly,
                              ],
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            _FieldLabel(label: 'Harga Modal'),
                            _TextField(
                              ctrl: _hargaModalCtrl,
                              hint: 'Rp 0',
                              type: TextInputType.number,
                              prefix: const Text(
                                'Rp ',
                                style: TextStyle(
                                  fontSize: 13,
                                  color: Color(0xFF4A5568),
                                ),
                              ),
                              inputFormatters: [
                                FilteringTextInputFormatter.digitsOnly,
                              ],
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 14),

                  // Stok Awal + Stok Minimum
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            _FieldLabel(label: 'Stok awal', required: true),
                            _TextField(
                              ctrl: _stokAwalCtrl,
                              hint: '0',
                              type: TextInputType.number,
                              error: _errors['stokAwal'],
                              onChanged: (_) =>
                                  setState(() => _errors.remove('stokAwal')),
                              inputFormatters: [
                                FilteringTextInputFormatter.digitsOnly,
                              ],
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            _FieldLabel(label: 'Stok minimum'),
                            _TextField(
                              ctrl: _stokMinCtrl,
                              hint: '0',
                              type: TextInputType.number,
                              inputFormatters: [
                                FilteringTextInputFormatter.digitsOnly,
                              ],
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 14),

                  // Satuan + Barcode
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            _FieldLabel(label: 'Satuan', required: true),
                            _DropdownField(
                              value: _satuan,
                              items: _satuanList,
                              onChanged: (v) => setState(() => _satuan = v!),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            _FieldLabel(label: 'Barcode', required: true),
                            _TextField(
                              ctrl: _barcodeCtrl,
                              hint: '0',
                              type: TextInputType.number,
                              error: _errors['barcode'],
                              onChanged: (_) =>
                                  setState(() => _errors.remove('barcode')),
                              inputFormatters: [
                                FilteringTextInputFormatter.digitsOnly,
                              ],
                              suffix: IconButton(
                                icon: const Icon(
                                  Icons.qr_code_rounded,
                                  size: 20,
                                  color: Color(0xFF4169E1),
                                ),
                                onPressed: _generateBarcode,
                                tooltip: 'Generate barcode',
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 24),

                  // ══ INFORMASI TAMBAHAN ══
                  _SectionTitle(title: 'Informasi Tambahan'),
                  const SizedBox(height: 16),

                  // Berat + Dimensi
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            _FieldLabel(label: 'Berat (gram)'),
                            _TextField(
                              ctrl: _beratCtrl,
                              hint: '0',
                              type: TextInputType.number,
                              inputFormatters: [
                                FilteringTextInputFormatter.digitsOnly,
                              ],
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            _FieldLabel(label: 'Dimensi'),
                            _TextField(
                              ctrl: _dimensiCtrl,
                              hint: 'panjang X lebar X tinggi',
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 14),

                  // Tanggal Kadaluarsa
                  _FieldLabel(label: 'Tanggal Kadaluarsa', required: true),
                  _TextField(
                    ctrl: _kadaluarsaCtrl,
                    hint: 'hari/bulan/tahun',
                    error: _errors['kadaluarsa'],
                    onChanged: (_) =>
                        setState(() => _errors.remove('kadaluarsa')),
                    suffix: IconButton(
                      icon: const Icon(
                        Icons.calendar_month_rounded,
                        size: 20,
                        color: Color(0xFF4169E1),
                      ),
                      onPressed: _pickDate,
                    ),
                    inputFormatters: [
                      FilteringTextInputFormatter.allow(RegExp(r'[0-9/]')),
                    ],
                  ),
                  const SizedBox(height: 20),

                  // Checkbox produk aktif
                  GestureDetector(
                    onTap: () => setState(() => _aktif = !_aktif),
                    child: Row(
                      children: [
                        AnimatedContainer(
                          duration: const Duration(milliseconds: 150),
                          width: 22,
                          height: 22,
                          decoration: BoxDecoration(
                            color: _aktif
                                ? const Color(0xFF4169E1)
                                : Colors.white,
                            borderRadius: BorderRadius.circular(4),
                            border: Border.all(
                              color: _aktif
                                  ? const Color(0xFF4169E1)
                                  : Colors.grey.shade400,
                              width: 1.5,
                            ),
                          ),
                          child: _aktif
                              ? const Icon(
                                  Icons.check_rounded,
                                  color: Colors.white,
                                  size: 15,
                                )
                              : null,
                        ),
                        const SizedBox(width: 10),
                        const Text(
                          'Produk aktif',
                          style: TextStyle(
                            fontSize: 14,
                            color: Color(0xFF2D3748),
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 28),

                  // Tombol Reset + Simpan
                  Row(
                    children: [
                      Expanded(
                        child: OutlinedButton.icon(
                          onPressed: _showResetDialog,
                          icon: const Icon(Icons.refresh_rounded, size: 18),
                          label: const Text(
                            'Reset',
                            style: TextStyle(
                              fontSize: 15,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                          style: OutlinedButton.styleFrom(
                            foregroundColor: const Color(0xFF4A5568),
                            side: BorderSide(
                              color: Colors.grey.shade400,
                              width: 1.5,
                            ),
                            padding: const EdgeInsets.symmetric(vertical: 14),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        flex: 2,
                        child: ElevatedButton(
                          onPressed: _showSimpanDialog,
                          style: ElevatedButton.styleFrom(
                            backgroundColor: const Color(0xFF4169E1),
                            padding: const EdgeInsets.symmetric(vertical: 14),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                            elevation: 0,
                          ),
                          child: const Text(
                            'Simpan',
                            style: TextStyle(
                              fontSize: 15,
                              fontWeight: FontWeight.w600,
                              color: Colors.white,
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),
          ],
        ),
      ),
    );
  }
}

// ════════════════════════════════════════════════════════════════════════════
// EDIT PRODUK PAGE
// ════════════════════════════════════════════════════════════════════════════

/// Model ringkas untuk data produk yang diedit.
/// Sesuaikan dengan model utama di project kamu.
class ProdukEditData {
  final String kode;
  final String nama;
  final String kategori;
  final String deskripsi;
  final int hargaJual;
  final int hargaModal;
  final int stokAwal;
  final int stokMin;
  final String satuan;
  final String barcode;
  final int berat;
  final String dimensi;
  final String kadaluarsa;
  final bool aktif;

  const ProdukEditData({
    required this.kode,
    required this.nama,
    required this.kategori,
    this.deskripsi = '',
    required this.hargaJual,
    this.hargaModal = 0,
    required this.stokAwal,
    this.stokMin = 0,
    required this.satuan,
    required this.barcode,
    this.berat = 0,
    this.dimensi = '',
    required this.kadaluarsa,
    this.aktif = true,
  });
}

class EditProdukPage extends StatefulWidget {
  final ProdukEditData produk;

  const EditProdukPage({super.key, required this.produk});

  @override
  State<EditProdukPage> createState() => _EditProdukPageState();
}

class _EditProdukPageState extends State<EditProdukPage> {
  late TextEditingController _namaCtrl;
  late TextEditingController _kodeCtrl;
  late TextEditingController _deskripsiCtrl;
  late TextEditingController _hargaJualCtrl;
  late TextEditingController _hargaModalCtrl;
  late TextEditingController _stokAwalCtrl;
  late TextEditingController _stokMinCtrl;
  late TextEditingController _barcodeCtrl;
  late TextEditingController _beratCtrl;
  late TextEditingController _dimensiCtrl;
  late TextEditingController _kadaluarsaCtrl;

  late String? _kategori;
  late String _satuan;
  late bool _aktif;
  DateTime? _tglKadaluarsa;

  final Map<String, String?> _errors = {};

  static const _kategoriList = [
    'Makanan',
    'Minuman',
    'Sembako',
    'Obat',
    'Kebutuhan Rumah',
  ];
  static const _satuanList = ['Dus', 'Pcs', 'Pack', 'Kg', 'Liter', 'Meter'];

  @override
  void initState() {
    super.initState();
    final p = widget.produk;
    _namaCtrl = TextEditingController(text: p.nama);
    _kodeCtrl = TextEditingController(text: p.kode);
    _deskripsiCtrl = TextEditingController(text: p.deskripsi);
    _hargaJualCtrl = TextEditingController(text: '${p.hargaJual}');
    _hargaModalCtrl = TextEditingController(text: '${p.hargaModal}');
    _stokAwalCtrl = TextEditingController(text: '${p.stokAwal}');
    _stokMinCtrl = TextEditingController(text: '${p.stokMin}');
    _barcodeCtrl = TextEditingController(text: p.barcode);
    _beratCtrl = TextEditingController(text: '${p.berat}');
    _dimensiCtrl = TextEditingController(text: p.dimensi);
    _kadaluarsaCtrl = TextEditingController(text: p.kadaluarsa);
    _kategori = _kategoriList.contains(p.kategori) ? p.kategori : null;
    _satuan = _satuanList.contains(p.satuan) ? p.satuan : 'Dus';
    _aktif = p.aktif;
  }

  @override
  void dispose() {
    _namaCtrl.dispose();
    _kodeCtrl.dispose();
    _deskripsiCtrl.dispose();
    _hargaJualCtrl.dispose();
    _hargaModalCtrl.dispose();
    _stokAwalCtrl.dispose();
    _stokMinCtrl.dispose();
    _barcodeCtrl.dispose();
    _beratCtrl.dispose();
    _dimensiCtrl.dispose();
    _kadaluarsaCtrl.dispose();
    super.dispose();
  }

  void _generateKodeProduk() {
    final rng = Random();
    final angka = List.generate(8, (_) => rng.nextInt(10)).join();
    _kodeCtrl.text = 'PRD-$angka';
    setState(() => _errors.remove('kode'));
  }

  void _generateBarcode() {
    final rng = Random();
    final angka = List.generate(12, (_) => rng.nextInt(10)).join();
    _barcodeCtrl.text = angka;
    setState(() => _errors.remove('barcode'));
  }

  Future<void> _pickDate() async {
    DateTime initial = DateTime.now().add(const Duration(days: 365));
    if (_kadaluarsaCtrl.text.isNotEmpty) {
      final parts = _kadaluarsaCtrl.text.split('/');
      if (parts.length == 3) {
        try {
          initial = DateTime(
            int.parse(parts[2]),
            int.parse(parts[1]),
            int.parse(parts[0]),
          );
        } catch (_) {}
      }
    }
    final picked = await showDatePicker(
      context: context,
      initialDate: _tglKadaluarsa ?? initial,
      firstDate: DateTime(2000),
      lastDate: DateTime(2100),
      builder: (ctx, child) => Theme(
        data: Theme.of(ctx).copyWith(
          colorScheme: const ColorScheme.light(
            primary: Color(0xFFD69E2E),
            onPrimary: Colors.white,
            surface: Colors.white,
          ),
        ),
        child: child!,
      ),
    );
    if (picked != null) {
      setState(() {
        _tglKadaluarsa = picked;
        _kadaluarsaCtrl.text =
            '${picked.day.toString().padLeft(2, '0')}/${picked.month.toString().padLeft(2, '0')}/${picked.year}';
        _errors.remove('kadaluarsa');
      });
    }
  }

  bool _validate() {
    final e = <String, String?>{};
    if (_namaCtrl.text.trim().isEmpty) e['nama'] = 'Nama produk wajib diisi';
    if (_kodeCtrl.text.trim().isEmpty) e['kode'] = 'Kode produk wajib diisi';
    if (_kategori == null) e['kategori'] = 'Kategori wajib dipilih';
    if (_hargaJualCtrl.text.trim().isEmpty ||
        (int.tryParse(_hargaJualCtrl.text) ?? 0) <= 0)
      e['hargaJual'] = 'Harga jual wajib diisi';
    if (_stokAwalCtrl.text.trim().isEmpty)
      e['stokAwal'] = 'Stok awal wajib diisi';
    if (_barcodeCtrl.text.trim().isEmpty) e['barcode'] = 'Barcode wajib diisi';
    if (_kadaluarsaCtrl.text.trim().isEmpty)
      e['kadaluarsa'] = 'Tanggal kadaluarsa wajib diisi';

    setState(() => _errors.addAll(e));
    return e.isEmpty;
  }

  void _showResetDialog() {
    showDialog(
      context: context,
      builder: (_) => _ConfirmDialog(
        title: 'Reset Form',
        icon: Icons.refresh_rounded,
        iconColor: const Color(0xFFECC94B),
        message: 'Apakah kamu yakin ingin mereset form ke data awal produk?',
        confirmLabel: 'Ya, Reset',
        confirmColor: const Color(0xFFECC94B),
        onConfirm: () {
          Navigator.pop(context);
          _resetToOriginal();
        },
      ),
    );
  }

  void _resetToOriginal() {
    final p = widget.produk;
    setState(() {
      _namaCtrl.text = p.nama;
      _kodeCtrl.text = p.kode;
      _deskripsiCtrl.text = p.deskripsi;
      _hargaJualCtrl.text = '${p.hargaJual}';
      _hargaModalCtrl.text = '${p.hargaModal}';
      _stokAwalCtrl.text = '${p.stokAwal}';
      _stokMinCtrl.text = '${p.stokMin}';
      _barcodeCtrl.text = p.barcode;
      _beratCtrl.text = '${p.berat}';
      _dimensiCtrl.text = p.dimensi;
      _kadaluarsaCtrl.text = p.kadaluarsa;
      _kategori = _kategoriList.contains(p.kategori) ? p.kategori : null;
      _satuan = _satuanList.contains(p.satuan) ? p.satuan : 'Dus';
      _aktif = p.aktif;
      _tglKadaluarsa = null;
      _errors.clear();
    });
  }

  void _showSimpanDialog() {
    if (!_validate()) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: const Text(
            'Harap lengkapi semua field yang wajib diisi (*)',
          ),
          backgroundColor: const Color(0xFFE53E3E),
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(10),
          ),
        ),
      );
      return;
    }
    showDialog(
      context: context,
      builder: (_) => _ConfirmDialog(
        title: 'Update Produk',
        icon: Icons.save_rounded,
        iconColor: const Color(0xFFD69E2E),
        message:
            'Apakah semua perubahan data produk sudah benar?\n\nData produk akan diperbarui.',
        confirmLabel: 'Ya, Update',
        confirmColor: const Color(0xFFD69E2E),
        onConfirm: () {
          Navigator.pop(context);
          // TODO: kirim update ke API Laravel
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: const Text('Produk berhasil diperbarui!'),
              backgroundColor: const Color(0xFF48BB78),
              behavior: SnackBarBehavior.floating,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(10),
              ),
            ),
          );
          Navigator.pop(context);
        },
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF3F4F8),
      appBar: AppBar(
        backgroundColor: const Color(0xFFD69E2E),
        foregroundColor: Colors.white,
        elevation: 0,
        title: const Text(
          'Edit Produk',
          style: TextStyle(fontWeight: FontWeight.w600, fontSize: 18),
        ),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new_rounded),
          onPressed: () => Navigator.pop(context),
        ),
        shape: const RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(bottom: Radius.circular(20)),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            _FormCard(
              headerTitle: 'Form Edit Produk',
              headerSubtitle: 'ubah data produk yang ingin diperbarui',
              headerColor: const Color(0xFFD69E2E),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _SectionTitle(title: 'Informasi dasar'),
                  const SizedBox(height: 16),

                  _FieldLabel(label: 'Nama Produk', required: true),
                  _TextField(
                    ctrl: _namaCtrl,
                    hint: 'Masukan nama produk',
                    error: _errors['nama'],
                    onChanged: (_) => setState(() => _errors.remove('nama')),
                  ),
                  const SizedBox(height: 14),

                  Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            _FieldLabel(label: 'Kode Produk', required: true),
                            _TextField(
                              ctrl: _kodeCtrl,
                              hint: 'PRD-XXXXXXXX',
                              error: _errors['kode'],
                              onChanged: (_) =>
                                  setState(() => _errors.remove('kode')),
                              suffix: IconButton(
                                icon: const Icon(
                                  Icons.refresh_rounded,
                                  size: 20,
                                  color: Color(0xFFD69E2E),
                                ),
                                onPressed: _generateKodeProduk,
                              ),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            _FieldLabel(label: 'Kategori', required: true),
                            _DropdownField(
                              value: _kategori,
                              hint: '---pilih kategori---',
                              items: _kategoriList,
                              error: _errors['kategori'],
                              onChanged: (v) => setState(() {
                                _kategori = v;
                                _errors.remove('kategori');
                              }),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 14),

                  _FieldLabel(label: 'Deskripsi produk'),
                  _TextField(
                    ctrl: _deskripsiCtrl,
                    hint: 'Isi deskripsi jika perlu',
                    maxLines: 4,
                  ),
                  const SizedBox(height: 24),

                  _SectionTitle(title: 'Harga & Stok'),
                  const SizedBox(height: 16),

                  Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            _FieldLabel(label: 'Harga Jual', required: true),
                            _TextField(
                              ctrl: _hargaJualCtrl,
                              hint: 'Rp 0',
                              type: TextInputType.number,
                              prefix: const Text(
                                'Rp ',
                                style: TextStyle(
                                  fontSize: 13,
                                  color: Color(0xFF4A5568),
                                ),
                              ),
                              error: _errors['hargaJual'],
                              onChanged: (_) =>
                                  setState(() => _errors.remove('hargaJual')),
                              inputFormatters: [
                                FilteringTextInputFormatter.digitsOnly,
                              ],
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            _FieldLabel(label: 'Harga Modal'),
                            _TextField(
                              ctrl: _hargaModalCtrl,
                              hint: 'Rp 0',
                              type: TextInputType.number,
                              prefix: const Text(
                                'Rp ',
                                style: TextStyle(
                                  fontSize: 13,
                                  color: Color(0xFF4A5568),
                                ),
                              ),
                              inputFormatters: [
                                FilteringTextInputFormatter.digitsOnly,
                              ],
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 14),

                  Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            _FieldLabel(label: 'Stok awal', required: true),
                            _TextField(
                              ctrl: _stokAwalCtrl,
                              hint: '0',
                              type: TextInputType.number,
                              error: _errors['stokAwal'],
                              onChanged: (_) =>
                                  setState(() => _errors.remove('stokAwal')),
                              inputFormatters: [
                                FilteringTextInputFormatter.digitsOnly,
                              ],
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            _FieldLabel(label: 'Stok minimum'),
                            _TextField(
                              ctrl: _stokMinCtrl,
                              hint: '0',
                              type: TextInputType.number,
                              inputFormatters: [
                                FilteringTextInputFormatter.digitsOnly,
                              ],
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 14),

                  Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            _FieldLabel(label: 'Satuan', required: true),
                            _DropdownField(
                              value: _satuan,
                              items: _satuanList,
                              onChanged: (v) => setState(() => _satuan = v!),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            _FieldLabel(label: 'Barcode', required: true),
                            _TextField(
                              ctrl: _barcodeCtrl,
                              hint: '0',
                              type: TextInputType.number,
                              error: _errors['barcode'],
                              onChanged: (_) =>
                                  setState(() => _errors.remove('barcode')),
                              inputFormatters: [
                                FilteringTextInputFormatter.digitsOnly,
                              ],
                              suffix: IconButton(
                                icon: const Icon(
                                  Icons.qr_code_rounded,
                                  size: 20,
                                  color: Color(0xFFD69E2E),
                                ),
                                onPressed: _generateBarcode,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 24),

                  _SectionTitle(title: 'Informasi Tambahan'),
                  const SizedBox(height: 16),

                  Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            _FieldLabel(label: 'Berat (gram)'),
                            _TextField(
                              ctrl: _beratCtrl,
                              hint: '0',
                              type: TextInputType.number,
                              inputFormatters: [
                                FilteringTextInputFormatter.digitsOnly,
                              ],
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            _FieldLabel(label: 'Dimensi'),
                            _TextField(
                              ctrl: _dimensiCtrl,
                              hint: 'panjang X lebar X tinggi',
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 14),

                  _FieldLabel(label: 'Tanggal Kadaluarsa', required: true),
                  _TextField(
                    ctrl: _kadaluarsaCtrl,
                    hint: 'hari/bulan/tahun',
                    error: _errors['kadaluarsa'],
                    onChanged: (_) =>
                        setState(() => _errors.remove('kadaluarsa')),
                    suffix: IconButton(
                      icon: const Icon(
                        Icons.calendar_month_rounded,
                        size: 20,
                        color: Color(0xFFD69E2E),
                      ),
                      onPressed: _pickDate,
                    ),
                    inputFormatters: [
                      FilteringTextInputFormatter.allow(RegExp(r'[0-9/]')),
                    ],
                  ),
                  const SizedBox(height: 20),

                  GestureDetector(
                    onTap: () => setState(() => _aktif = !_aktif),
                    child: Row(
                      children: [
                        AnimatedContainer(
                          duration: const Duration(milliseconds: 150),
                          width: 22,
                          height: 22,
                          decoration: BoxDecoration(
                            color: _aktif
                                ? const Color(0xFFD69E2E)
                                : Colors.white,
                            borderRadius: BorderRadius.circular(4),
                            border: Border.all(
                              color: _aktif
                                  ? const Color(0xFFD69E2E)
                                  : Colors.grey.shade400,
                              width: 1.5,
                            ),
                          ),
                          child: _aktif
                              ? const Icon(
                                  Icons.check_rounded,
                                  color: Colors.white,
                                  size: 15,
                                )
                              : null,
                        ),
                        const SizedBox(width: 10),
                        const Text(
                          'Produk aktif',
                          style: TextStyle(
                            fontSize: 14,
                            color: Color(0xFF2D3748),
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 28),

                  Row(
                    children: [
                      Expanded(
                        child: OutlinedButton.icon(
                          onPressed: _showResetDialog,
                          icon: const Icon(Icons.refresh_rounded, size: 18),
                          label: const Text(
                            'Reset',
                            style: TextStyle(
                              fontSize: 15,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                          style: OutlinedButton.styleFrom(
                            foregroundColor: const Color(0xFF4A5568),
                            side: BorderSide(
                              color: Colors.grey.shade400,
                              width: 1.5,
                            ),
                            padding: const EdgeInsets.symmetric(vertical: 14),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        flex: 2,
                        child: ElevatedButton(
                          onPressed: _showSimpanDialog,
                          style: ElevatedButton.styleFrom(
                            backgroundColor: const Color(0xFFD69E2E),
                            padding: const EdgeInsets.symmetric(vertical: 14),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                            elevation: 0,
                          ),
                          child: const Text(
                            'Simpan Perubahan',
                            style: TextStyle(
                              fontSize: 15,
                              fontWeight: FontWeight.w600,
                              color: Colors.white,
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),
          ],
        ),
      ),
    );
  }
}

// ════════════════════════════════════════════════════════════════════════════
// SHARED SMALL WIDGETS
// ════════════════════════════════════════════════════════════════════════════

/// Card wrapper dengan header biru
class _FormCard extends StatelessWidget {
  final String headerTitle;
  final String headerSubtitle;
  final Color headerColor;
  final Widget child;

  const _FormCard({
    required this.headerTitle,
    required this.headerSubtitle,
    this.headerColor = const Color(0xFF4169E1),
    required this.child,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.06),
            blurRadius: 12,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      clipBehavior: Clip.antiAlias,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header berwarna
          Container(
            width: double.infinity,
            padding: const EdgeInsets.fromLTRB(20, 18, 20, 18),
            color: headerColor,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  headerTitle,
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 17,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: 3),
                Text(
                  headerSubtitle,
                  style: TextStyle(
                    color: Colors.white.withOpacity(0.85),
                    fontSize: 12,
                  ),
                ),
              ],
            ),
          ),
          // Body form
          Padding(padding: const EdgeInsets.all(20), child: child),
        ],
      ),
    );
  }
}

/// Judul seksi dengan garis bawah
class _SectionTitle extends StatelessWidget {
  final String title;
  const _SectionTitle({required this.title});

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          title,
          style: const TextStyle(
            fontSize: 15,
            fontWeight: FontWeight.bold,
            color: Color(0xFF2D3748),
          ),
        ),
        const SizedBox(height: 4),
        Container(
          height: 2,
          width: 120,
          decoration: BoxDecoration(
            color: const Color(0xFF4169E1).withOpacity(0.3),
            borderRadius: BorderRadius.circular(1),
          ),
        ),
      ],
    );
  }
}

/// Label field — opsional tampilkan bintang merah
class _FieldLabel extends StatelessWidget {
  final String label;
  final bool required;
  const _FieldLabel({required this.label, this.required = false});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 6),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Text(
            label,
            style: const TextStyle(
              fontSize: 13,
              fontWeight: FontWeight.w500,
              color: Color(0xFF4A5568),
            ),
          ),
          if (required) ...[
            const SizedBox(width: 3),
            const Text(
              ' *',
              style: TextStyle(
                color: Colors.red,
                fontSize: 13,
                fontWeight: FontWeight.bold,
              ),
            ),
          ],
        ],
      ),
    );
  }
}

/// TextField reusable dengan error state
class _TextField extends StatelessWidget {
  final TextEditingController ctrl;
  final String hint;
  final String? error;
  final TextInputType type;
  final int maxLines;
  final Widget? suffix;
  final Widget? prefix;
  final void Function(String)? onChanged;
  final List<TextInputFormatter>? inputFormatters;

  const _TextField({
    required this.ctrl,
    required this.hint,
    this.error,
    this.type = TextInputType.text,
    this.maxLines = 1,
    this.suffix,
    this.prefix,
    this.onChanged,
    this.inputFormatters,
  });

  @override
  Widget build(BuildContext context) {
    final hasError = error != null && error!.isNotEmpty;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Container(
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(10),
            border: Border.all(
              color: hasError ? Colors.red.shade400 : Colors.grey.shade300,
              width: hasError ? 1.5 : 1,
            ),
          ),
          child: TextField(
            controller: ctrl,
            keyboardType: type,
            maxLines: maxLines,
            onChanged: onChanged,
            inputFormatters: inputFormatters,
            style: const TextStyle(fontSize: 13, color: Color(0xFF2D3748)),
            decoration: InputDecoration(
              hintText: hint,
              hintStyle: TextStyle(color: Colors.grey.shade400, fontSize: 13),
              border: InputBorder.none,
              contentPadding: const EdgeInsets.symmetric(
                horizontal: 12,
                vertical: 12,
              ),
              prefixIcon: prefix != null
                  ? Padding(
                      padding: const EdgeInsets.only(left: 12, top: 12),
                      child: prefix,
                    )
                  : null,
              prefixIconConstraints: const BoxConstraints(
                minWidth: 0,
                minHeight: 0,
              ),
              suffixIcon: suffix,
            ),
          ),
        ),
        if (hasError) ...[
          const SizedBox(height: 4),
          Row(
            children: [
              const Icon(
                Icons.error_outline_rounded,
                size: 13,
                color: Colors.red,
              ),
              const SizedBox(width: 4),
              Text(
                error!,
                style: const TextStyle(fontSize: 11, color: Colors.red),
              ),
            ],
          ),
        ],
      ],
    );
  }
}

/// Dropdown reusable dengan error state
class _DropdownField extends StatelessWidget {
  final String? value;
  final String? hint;
  final List<String> items;
  final String? error;
  final void Function(String?) onChanged;

  const _DropdownField({
    required this.items,
    required this.onChanged,
    this.value,
    this.hint,
    this.error,
  });

  @override
  Widget build(BuildContext context) {
    final hasError = error != null && error!.isNotEmpty;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 12),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(10),
            border: Border.all(
              color: hasError ? Colors.red.shade400 : Colors.grey.shade300,
              width: hasError ? 1.5 : 1,
            ),
          ),
          child: DropdownButtonHideUnderline(
            child: DropdownButton<String>(
              value: value,
              isExpanded: true,
              hint: hint != null
                  ? Text(
                      hint!,
                      style: TextStyle(
                        fontSize: 13,
                        color: Colors.grey.shade400,
                      ),
                    )
                  : null,
              items: items
                  .map(
                    (e) => DropdownMenuItem(
                      value: e,
                      child: Text(
                        e,
                        style: const TextStyle(
                          fontSize: 13,
                          color: Color(0xFF2D3748),
                        ),
                      ),
                    ),
                  )
                  .toList(),
              onChanged: onChanged,
              icon: const Icon(Icons.keyboard_arrow_down_rounded, size: 20),
              style: const TextStyle(fontSize: 13, color: Color(0xFF2D3748)),
            ),
          ),
        ),
        if (hasError) ...[
          const SizedBox(height: 4),
          Row(
            children: [
              const Icon(
                Icons.error_outline_rounded,
                size: 13,
                color: Colors.red,
              ),
              const SizedBox(width: 4),
              Text(
                error!,
                style: const TextStyle(fontSize: 11, color: Colors.red),
              ),
            ],
          ),
        ],
      ],
    );
  }
}

/// Dialog konfirmasi reusable
class _ConfirmDialog extends StatelessWidget {
  final String title;
  final IconData icon;
  final Color iconColor;
  final String message;
  final String confirmLabel;
  final Color confirmColor;
  final VoidCallback onConfirm;

  const _ConfirmDialog({
    required this.title,
    required this.icon,
    required this.iconColor,
    required this.message,
    required this.confirmLabel,
    required this.confirmColor,
    required this.onConfirm,
  });

  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
      contentPadding: EdgeInsets.zero,
      content: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          // Icon area
          Container(
            width: double.infinity,
            padding: const EdgeInsets.symmetric(vertical: 24),
            decoration: BoxDecoration(
              color: iconColor.withOpacity(0.1),
              borderRadius: const BorderRadius.vertical(
                top: Radius.circular(20),
              ),
            ),
            child: Column(
              children: [
                Container(
                  width: 60,
                  height: 60,
                  decoration: BoxDecoration(
                    color: iconColor.withOpacity(0.15),
                    shape: BoxShape.circle,
                  ),
                  child: Icon(icon, color: iconColor, size: 30),
                ),
                const SizedBox(height: 12),
                Text(
                  title,
                  style: const TextStyle(
                    fontSize: 17,
                    fontWeight: FontWeight.bold,
                    color: Color(0xFF2D3748),
                  ),
                ),
              ],
            ),
          ),
          // Message
          Padding(
            padding: const EdgeInsets.fromLTRB(24, 20, 24, 8),
            child: Text(
              message,
              textAlign: TextAlign.center,
              style: TextStyle(
                fontSize: 13,
                color: Colors.grey.shade600,
                height: 1.5,
              ),
            ),
          ),
          // Buttons
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 12, 16, 20),
            child: Row(
              children: [
                Expanded(
                  child: OutlinedButton(
                    onPressed: () => Navigator.pop(context),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: Colors.grey.shade600,
                      side: BorderSide(color: Colors.grey.shade300),
                      padding: const EdgeInsets.symmetric(vertical: 12),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(10),
                      ),
                    ),
                    child: const Text(
                      'Batal',
                      style: TextStyle(fontWeight: FontWeight.w600),
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: ElevatedButton(
                    onPressed: onConfirm,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: confirmColor,
                      padding: const EdgeInsets.symmetric(vertical: 12),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(10),
                      ),
                      elevation: 0,
                    ),
                    child: Text(
                      confirmLabel,
                      style: const TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
