// lib/product/produk_form_page.dart
import 'dart:math';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../shared_widgets.dart';
import 'produk_model.dart';

// ════════════════════════════════════════════════════════════════════════════
// HELPER — generate kode & barcode random
// ════════════════════════════════════════════════════════════════════════════
String generateKodeProduk() {
  final rng = Random();
  final digits = List.generate(8, (_) => rng.nextInt(10)).join();
  return 'PRD-$digits';
}

String generateBarcode() {
  final rng = Random();
  return List.generate(13, (_) => rng.nextInt(10)).join();
}

// ════════════════════════════════════════════════════════════════════════════
// TAMBAH PRODUK PAGE
// ════════════════════════════════════════════════════════════════════════════
class TambahProdukPage extends StatefulWidget {
  const TambahProdukPage({super.key});

  @override
  State<TambahProdukPage> createState() => _TambahProdukPageState();
}

class _TambahProdukPageState extends State<TambahProdukPage> {
  late ProdukFormModel _model;

  late TextEditingController _namaCtrl, _kodeCtrl, _deskCtrl;
  late TextEditingController _hargaJualCtrl, _hargaModalCtrl;
  late TextEditingController _stokAwalCtrl, _stokMinCtrl;
  late TextEditingController _barcodeCtrl, _beratCtrl, _dimensiCtrl;
  late TextEditingController _kadaluarsaCtrl;

  final Map<String, String?> _errors = {};

  static const _satuanList = ['Dus', 'Pcs', 'Pack', 'Kg', 'Liter', 'Meter'];

  List<String> get _kategoriList =>
      dummyKategoriList.map((k) => k.nama).toList();

  @override
  void initState() {
    super.initState();
    _model = ProdukFormModel(
      kode: generateKodeProduk(),
      barcode: generateBarcode(),
    );
    _initControllers();
  }

  void _initControllers() {
    _namaCtrl = TextEditingController(text: _model.nama);
    _kodeCtrl = TextEditingController(text: _model.kode);
    _deskCtrl = TextEditingController(text: _model.deskripsi);
    _hargaJualCtrl = TextEditingController(text: _model.hargaJual);
    _hargaModalCtrl = TextEditingController(text: _model.hargaModal);
    _stokAwalCtrl = TextEditingController(text: _model.stokAwal);
    _stokMinCtrl = TextEditingController(text: _model.stokMinimum);
    _barcodeCtrl = TextEditingController(text: _model.barcode);
    _beratCtrl = TextEditingController(text: _model.berat);
    _dimensiCtrl = TextEditingController(text: _model.dimensi);
    _kadaluarsaCtrl = TextEditingController(
      text: _model.kadaluarsa != null ? _fmtDate(_model.kadaluarsa!) : '',
    );
  }

  @override
  void dispose() {
    for (final c in [
      _namaCtrl,
      _kodeCtrl,
      _deskCtrl,
      _hargaJualCtrl,
      _hargaModalCtrl,
      _stokAwalCtrl,
      _stokMinCtrl,
      _barcodeCtrl,
      _beratCtrl,
      _dimensiCtrl,
      _kadaluarsaCtrl,
    ]) {
      c.dispose();
    }
    super.dispose();
  }

  String _fmtDate(DateTime dt) =>
      '${dt.day.toString().padLeft(2, '0')}/${dt.month.toString().padLeft(2, '0')}/${dt.year}';

  void _regenKode() => setState(() {
    _kodeCtrl.text = _model.kode = generateKodeProduk();
  });
  void _regenBarcode() => setState(() {
    _barcodeCtrl.text = _model.barcode = generateBarcode();
  });

  Future<void> _pickDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate:
          _model.kadaluarsa ?? DateTime.now().add(const Duration(days: 365)),
      firstDate: DateTime.now(),
      lastDate: DateTime(2040),
      builder: (ctx, child) => Theme(
        data: Theme.of(ctx).copyWith(
          colorScheme: const ColorScheme.light(
            primary: Color(0xFF4169E1),
            onPrimary: Colors.white,
          ),
        ),
        child: child!,
      ),
    );
    if (picked != null)
      setState(() {
        _model.kadaluarsa = picked;
        _kadaluarsaCtrl.text = _fmtDate(picked);
        _errors.remove('kadaluarsa');
      });
  }

  bool _validate() {
    final e = <String, String?>{};
    if (_namaCtrl.text.trim().isEmpty) e['nama'] = 'Nama produk wajib diisi';
    if (_kodeCtrl.text.trim().isEmpty) e['kode'] = 'Kode produk wajib diisi';
    if (_model.kategori.isEmpty) e['kategori'] = 'Kategori wajib dipilih';
    if (_hargaJualCtrl.text.trim().isEmpty)
      e['hargaJual'] = 'Harga jual wajib diisi';
    if (_stokAwalCtrl.text.trim().isEmpty)
      e['stokAwal'] = 'Stok awal wajib diisi';
    if (_barcodeCtrl.text.trim().isEmpty) e['barcode'] = 'Barcode wajib diisi';
    if (_model.kadaluarsa == null)
      e['kadaluarsa'] = 'Tanggal kadaluarsa wajib diisi';
    setState(
      () => _errors
        ..clear()
        ..addAll(e),
    );
    return e.isEmpty;
  }

  void _showResetDialog() => showDialog(
    context: context,
    builder: (_) => _ConfirmDialog(
      title: 'Reset Form',
      icon: Icons.refresh_rounded,
      iconColor: const Color(0xFFE67E22),
      message:
          'Apakah kamu yakin ingin mereset semua isian form? Data yang sudah diisi akan hilang.',
      confirmLabel: 'Ya, Reset',
      confirmColor: const Color(0xFFE67E22),
      onConfirm: () {
        setState(() {
          _model = ProdukFormModel(
            kode: generateKodeProduk(),
            barcode: generateBarcode(),
          );
          _errors.clear();
        });
        for (final c in [
          _namaCtrl,
          _deskCtrl,
          _hargaJualCtrl,
          _hargaModalCtrl,
          _stokAwalCtrl,
          _stokMinCtrl,
          _beratCtrl,
          _dimensiCtrl,
          _kadaluarsaCtrl,
        ]) {
          c.clear();
        }
        _kodeCtrl.text = _model.kode;
        _barcodeCtrl.text = _model.barcode;
      },
    ),
  );

  void _showSimpanDialog() {
    if (!_validate()) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: const Text('Harap isi semua field yang wajib diisi (*)'),
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
            'Apakah semua informasi produk sudah benar? Produk akan disimpan ke database.',
        confirmLabel: 'Ya, Simpan',
        confirmColor: const Color(0xFF4169E1),
        onConfirm: () {
          // TODO: kirim _model.toJson() ke API Laravel POST /api/produk
          Navigator.pop(context);
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: const Text('Produk berhasil disimpan!'),
              backgroundColor: const Color(0xFF48BB78),
              behavior: SnackBarBehavior.floating,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(10),
              ),
            ),
          );
        },
      ),
    );
  }

  @override
  Widget build(BuildContext context) => Scaffold(
    backgroundColor: const Color(0xFFF3F4F8),
    appBar: _formAppBar('Tambah Produk'),
    body: SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: _ProdukFormBody(
        isEdit: false,
        model: _model,
        errors: _errors,
        namaCtrl: _namaCtrl,
        kodeCtrl: _kodeCtrl,
        deskCtrl: _deskCtrl,
        hargaJualCtrl: _hargaJualCtrl,
        hargaModalCtrl: _hargaModalCtrl,
        stokAwalCtrl: _stokAwalCtrl,
        stokMinCtrl: _stokMinCtrl,
        barcodeCtrl: _barcodeCtrl,
        beratCtrl: _beratCtrl,
        dimensiCtrl: _dimensiCtrl,
        kadaluarsaCtrl: _kadaluarsaCtrl,
        kategoriList: _kategoriList,
        satuanList: _satuanList,
        onKategoriChanged: (v) => setState(() {
          _model.kategori = v ?? '';
          _errors.remove('kategori');
        }),
        onSatuanChanged: (v) => setState(() {
          _model.satuan = v ?? '';
          _errors.remove('satuan');
        }),
        onAktifChanged: (v) => setState(() => _model.aktif = v),
        onRegenKode: _regenKode,
        onRegenBarcode: _regenBarcode,
        onPickDate: _pickDate,
        onClearError: (key) => setState(() => _errors.remove(key)),
        onReset: _showResetDialog,
        onSimpan: _showSimpanDialog,
      ),
    ),
  );
}

// ════════════════════════════════════════════════════════════════════════════
// EDIT PRODUK PAGE
// ════════════════════════════════════════════════════════════════════════════
class EditProdukPage extends StatefulWidget {
  // ← Terima ProdukItem (bukan ProdukTableItem lagi)
  final ProdukItem produk;
  const EditProdukPage({super.key, required this.produk});

  @override
  State<EditProdukPage> createState() => _EditProdukPageState();
}

class _EditProdukPageState extends State<EditProdukPage> {
  late ProdukFormModel _model;

  late TextEditingController _namaCtrl, _kodeCtrl, _deskCtrl;
  late TextEditingController _hargaJualCtrl, _hargaModalCtrl;
  late TextEditingController _stokAwalCtrl, _stokMinCtrl;
  late TextEditingController _barcodeCtrl, _beratCtrl, _dimensiCtrl;
  late TextEditingController _kadaluarsaCtrl;

  final Map<String, String?> _errors = {};

  static const _satuanList = ['Dus', 'Pcs', 'Pack', 'Kg', 'Liter', 'Meter'];
  List<String> get _kategoriList =>
      dummyKategoriList.map((k) => k.nama).toList();

  @override
  void initState() {
    super.initState();
    // Pre-fill dari ProdukItem pakai ProdukFormModel.fromItem()
    _model = ProdukFormModel.fromItem(widget.produk);
    if (_model.barcode.isEmpty) _model.barcode = generateBarcode();
    _initControllers();
  }

  void _initControllers() {
    _namaCtrl = TextEditingController(text: _model.nama);
    _kodeCtrl = TextEditingController(text: _model.kode);
    _deskCtrl = TextEditingController(text: _model.deskripsi);
    _hargaJualCtrl = TextEditingController(text: _model.hargaJual);
    _hargaModalCtrl = TextEditingController(text: _model.hargaModal);
    _stokAwalCtrl = TextEditingController(text: _model.stokAwal);
    _stokMinCtrl = TextEditingController(text: _model.stokMinimum);
    _barcodeCtrl = TextEditingController(text: _model.barcode);
    _beratCtrl = TextEditingController(text: _model.berat);
    _dimensiCtrl = TextEditingController(text: _model.dimensi);
    _kadaluarsaCtrl = TextEditingController(
      text: _model.kadaluarsa != null ? _fmtDate(_model.kadaluarsa!) : '',
    );
  }

  @override
  void dispose() {
    for (final c in [
      _namaCtrl,
      _kodeCtrl,
      _deskCtrl,
      _hargaJualCtrl,
      _hargaModalCtrl,
      _stokAwalCtrl,
      _stokMinCtrl,
      _barcodeCtrl,
      _beratCtrl,
      _dimensiCtrl,
      _kadaluarsaCtrl,
    ]) {
      c.dispose();
    }
    super.dispose();
  }

  String _fmtDate(DateTime dt) =>
      '${dt.day.toString().padLeft(2, '0')}/${dt.month.toString().padLeft(2, '0')}/${dt.year}';

  void _regenKode() => setState(() {
    _kodeCtrl.text = _model.kode = generateKodeProduk();
  });
  void _regenBarcode() => setState(() {
    _barcodeCtrl.text = _model.barcode = generateBarcode();
  });

  Future<void> _pickDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate:
          _model.kadaluarsa ?? DateTime.now().add(const Duration(days: 365)),
      firstDate: DateTime.now(),
      lastDate: DateTime(2040),
      builder: (ctx, child) => Theme(
        data: Theme.of(ctx).copyWith(
          colorScheme: const ColorScheme.light(
            primary: Color(0xFFD69E2E),
            onPrimary: Colors.white,
          ),
        ),
        child: child!,
      ),
    );
    if (picked != null)
      setState(() {
        _model.kadaluarsa = picked;
        _kadaluarsaCtrl.text = _fmtDate(picked);
        _errors.remove('kadaluarsa');
      });
  }

  bool _validate() {
    final e = <String, String?>{};
    if (_namaCtrl.text.trim().isEmpty) e['nama'] = 'Nama produk wajib diisi';
    if (_kodeCtrl.text.trim().isEmpty) e['kode'] = 'Kode produk wajib diisi';
    if (_model.kategori.isEmpty) e['kategori'] = 'Kategori wajib dipilih';
    if (_hargaJualCtrl.text.trim().isEmpty)
      e['hargaJual'] = 'Harga jual wajib diisi';
    if (_stokAwalCtrl.text.trim().isEmpty)
      e['stokAwal'] = 'Stok awal wajib diisi';
    if (_barcodeCtrl.text.trim().isEmpty) e['barcode'] = 'Barcode wajib diisi';
    if (_model.kadaluarsa == null)
      e['kadaluarsa'] = 'Tanggal kadaluarsa wajib diisi';
    setState(
      () => _errors
        ..clear()
        ..addAll(e),
    );
    return e.isEmpty;
  }

  void _showResetDialog() => showDialog(
    context: context,
    builder: (_) => _ConfirmDialog(
      title: 'Reset Form',
      icon: Icons.refresh_rounded,
      iconColor: const Color(0xFFE67E22),
      message: 'Apakah kamu yakin ingin mereset form ke data awal produk?',
      confirmLabel: 'Ya, Reset',
      confirmColor: const Color(0xFFE67E22),
      onConfirm: () {
        setState(() {
          _model = ProdukFormModel.fromItem(widget.produk);
          _errors.clear();
        });
        _namaCtrl.text = _model.nama;
        _kodeCtrl.text = _model.kode;
        _hargaJualCtrl.text = _model.hargaJual;
        _stokAwalCtrl.text = _model.stokAwal;
        _kadaluarsaCtrl.text = _model.kadaluarsa != null
            ? _fmtDate(_model.kadaluarsa!)
            : '';
        _deskCtrl.clear();
        _hargaModalCtrl.clear();
        _stokMinCtrl.clear();
        _beratCtrl.clear();
        _dimensiCtrl.clear();
      },
    ),
  );

  void _showSimpanDialog() {
    if (!_validate()) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: const Text('Harap isi semua field yang wajib diisi (*)'),
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
        title: 'Simpan Perubahan',
        icon: Icons.edit_rounded,
        iconColor: const Color(0xFFD69E2E),
        message:
            'Apakah semua perubahan sudah benar? Data produk akan diperbarui.',
        confirmLabel: 'Ya, Update',
        confirmColor: const Color(0xFFD69E2E),
        onConfirm: () {
          // TODO: kirim _model.toJson() ke API Laravel PUT /api/produk/{kode}
          Navigator.pop(context);
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
        },
      ),
    );
  }

  @override
  Widget build(BuildContext context) => Scaffold(
    backgroundColor: const Color(0xFFF3F4F8),
    appBar: _formAppBar('Edit Produk', color: const Color(0xFFD69E2E)),
    body: SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: _ProdukFormBody(
        isEdit: true,
        model: _model,
        errors: _errors,
        namaCtrl: _namaCtrl,
        kodeCtrl: _kodeCtrl,
        deskCtrl: _deskCtrl,
        hargaJualCtrl: _hargaJualCtrl,
        hargaModalCtrl: _hargaModalCtrl,
        stokAwalCtrl: _stokAwalCtrl,
        stokMinCtrl: _stokMinCtrl,
        barcodeCtrl: _barcodeCtrl,
        beratCtrl: _beratCtrl,
        dimensiCtrl: _dimensiCtrl,
        kadaluarsaCtrl: _kadaluarsaCtrl,
        kategoriList: _kategoriList,
        satuanList: _satuanList,
        onKategoriChanged: (v) => setState(() {
          _model.kategori = v ?? '';
          _errors.remove('kategori');
        }),
        onSatuanChanged: (v) => setState(() {
          _model.satuan = v ?? '';
          _errors.remove('satuan');
        }),
        onAktifChanged: (v) => setState(() => _model.aktif = v),
        onRegenKode: _regenKode,
        onRegenBarcode: _regenBarcode,
        onPickDate: _pickDate,
        onClearError: (key) => setState(() => _errors.remove(key)),
        onReset: _showResetDialog,
        onSimpan: _showSimpanDialog,
      ),
    ),
  );
}

// ════════════════════════════════════════════════════════════════════════════
// SHARED APP BAR
// ════════════════════════════════════════════════════════════════════════════
PreferredSizeWidget _formAppBar(
  String title, {
  Color color = const Color(0xFF4169E1),
}) => AppBar(
  backgroundColor: color,
  foregroundColor: Colors.white,
  elevation: 0,
  shape: const RoundedRectangleBorder(
    borderRadius: BorderRadius.vertical(bottom: Radius.circular(20)),
  ),
  title: Text(
    title,
    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 18),
  ),
);

// ════════════════════════════════════════════════════════════════════════════
// PRODUK FORM BODY — shared antara Tambah & Edit
// ════════════════════════════════════════════════════════════════════════════
class _ProdukFormBody extends StatelessWidget {
  final bool isEdit;
  final ProdukFormModel model;
  final Map<String, String?> errors;
  final TextEditingController namaCtrl, kodeCtrl, deskCtrl;
  final TextEditingController hargaJualCtrl, hargaModalCtrl;
  final TextEditingController stokAwalCtrl, stokMinCtrl;
  final TextEditingController barcodeCtrl,
      beratCtrl,
      dimensiCtrl,
      kadaluarsaCtrl;
  final List<String> kategoriList, satuanList;
  final void Function(String?) onKategoriChanged, onSatuanChanged;
  final void Function(bool) onAktifChanged;
  final VoidCallback onRegenKode, onRegenBarcode, onPickDate, onReset, onSimpan;
  final void Function(String) onClearError;

  const _ProdukFormBody({
    required this.isEdit,
    required this.model,
    required this.errors,
    required this.namaCtrl,
    required this.kodeCtrl,
    required this.deskCtrl,
    required this.hargaJualCtrl,
    required this.hargaModalCtrl,
    required this.stokAwalCtrl,
    required this.stokMinCtrl,
    required this.barcodeCtrl,
    required this.beratCtrl,
    required this.dimensiCtrl,
    required this.kadaluarsaCtrl,
    required this.kategoriList,
    required this.satuanList,
    required this.onKategoriChanged,
    required this.onSatuanChanged,
    required this.onAktifChanged,
    required this.onRegenKode,
    required this.onRegenBarcode,
    required this.onPickDate,
    required this.onClearError,
    required this.onReset,
    required this.onSimpan,
  });

  Color get _accent =>
      isEdit ? const Color(0xFFD69E2E) : const Color(0xFF4169E1);

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // ── Form header ──
        Container(
          width: double.infinity,
          padding: const EdgeInsets.fromLTRB(16, 14, 16, 14),
          decoration: BoxDecoration(
            color: _accent,
            borderRadius: const BorderRadius.vertical(top: Radius.circular(16)),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                isEdit ? 'Form Edit Produk' : 'Form Tambah Produk',
                style: const TextStyle(
                  color: Colors.white,
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(height: 4),
              Text(
                isEdit
                    ? 'ubah informasi produk yang ingin diperbarui'
                    : 'isi form dibawah untuk menambahkan produk baru',
                style: const TextStyle(color: Colors.white70, fontSize: 12),
              ),
            ],
          ),
        ),

        // ── Form body ──
        Container(
          decoration: const BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.vertical(bottom: Radius.circular(16)),
            boxShadow: [
              BoxShadow(
                color: Color(0x0D000000),
                blurRadius: 8,
                offset: Offset(0, 2),
              ),
            ],
          ),
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // ════ INFORMASI DASAR ════
              _SectionTitle(title: 'Informasi dasar', color: _accent),
              const SizedBox(height: 12),

              _FieldLabel(label: 'Nama Produk', required: true),
              _FormTextField(
                ctrl: namaCtrl,
                hint: 'Masukan nama produk',
                error: errors['nama'],
                onChanged: (_) => onClearError('nama'),
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
                        _TextFieldWithAction(
                          ctrl: kodeCtrl,
                          hint: 'PRD-XXXXXXXX',
                          error: errors['kode'],
                          onChanged: (_) => onClearError('kode'),
                          actionIcon: Icons.refresh_rounded,
                          actionTooltip: 'Generate ulang kode',
                          onAction: onRegenKode,
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        _FieldLabel(label: 'Kategori', required: true),
                        _DropdownField(
                          value: model.kategori.isEmpty ? null : model.kategori,
                          hint: '---pilih kategori---',
                          items: kategoriList,
                          error: errors['kategori'],
                          onChanged: onKategoriChanged,
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 14),

              _FieldLabel(label: 'Deskripsi produk', required: false),
              _FormTextField(
                ctrl: deskCtrl,
                hint: 'Isi deskripsi jika perlu',
                maxLines: 4,
              ),
              const SizedBox(height: 24),

              // ════ HARGA & STOK ════
              _SectionTitle(title: 'Harga & Stok', color: _accent),
              const SizedBox(height: 12),

              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        _FieldLabel(label: 'Harga Jual', required: true),
                        _FormTextField(
                          ctrl: hargaJualCtrl,
                          hint: 'Rp 0',
                          prefix: 'Rp ',
                          keyboardType: TextInputType.number,
                          inputFormatters: [
                            FilteringTextInputFormatter.digitsOnly,
                          ],
                          error: errors['hargaJual'],
                          onChanged: (_) => onClearError('hargaJual'),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        _FieldLabel(label: 'Harga Modal', required: false),
                        _FormTextField(
                          ctrl: hargaModalCtrl,
                          hint: 'Rp 0',
                          prefix: 'Rp ',
                          keyboardType: TextInputType.number,
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
                        _FormTextField(
                          ctrl: stokAwalCtrl,
                          hint: '0',
                          keyboardType: TextInputType.number,
                          inputFormatters: [
                            FilteringTextInputFormatter.digitsOnly,
                          ],
                          error: errors['stokAwal'],
                          onChanged: (_) => onClearError('stokAwal'),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        _FieldLabel(label: 'Stok minimum', required: false),
                        _FormTextField(
                          ctrl: stokMinCtrl,
                          hint: '0',
                          keyboardType: TextInputType.number,
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
                          value: model.satuan.isEmpty ? null : model.satuan,
                          hint: '---pilih satuan---',
                          items: satuanList,
                          error: errors['satuan'],
                          onChanged: onSatuanChanged,
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        _FieldLabel(label: 'Barcode', required: true),
                        _TextFieldWithAction(
                          ctrl: barcodeCtrl,
                          hint: '0',
                          keyboardType: TextInputType.number,
                          inputFormatters: [
                            FilteringTextInputFormatter.digitsOnly,
                          ],
                          error: errors['barcode'],
                          onChanged: (_) => onClearError('barcode'),
                          actionIcon: Icons.barcode_reader,
                          actionTooltip: 'Generate barcode',
                          onAction: onRegenBarcode,
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 24),

              // ════ INFORMASI TAMBAHAN ════
              _SectionTitle(title: 'Informasi Tambahan', color: _accent),
              const SizedBox(height: 12),

              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        _FieldLabel(label: 'Berat (gram)', required: false),
                        _FormTextField(
                          ctrl: beratCtrl,
                          hint: '0',
                          keyboardType: TextInputType.number,
                          inputFormatters: [
                            FilteringTextInputFormatter.digitsOnly,
                          ],
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        _FieldLabel(label: 'Dimensi', required: false),
                        _FormTextField(
                          ctrl: dimensiCtrl,
                          hint: 'panjang X lebar X tinggi',
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 14),

              _FieldLabel(label: 'Tanggal Kadaluarsa', required: true),
              _DateField(
                ctrl: kadaluarsaCtrl,
                error: errors['kadaluarsa'],
                onTapCalendar: onPickDate,
                onChanged: (_) => onClearError('kadaluarsa'),
              ),
              const SizedBox(height: 16),

              // Produk aktif checkbox
              Row(
                children: [
                  Checkbox(
                    value: model.aktif,
                    onChanged: (v) => onAktifChanged(v ?? true),
                    activeColor: _accent,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(4),
                    ),
                  ),
                  const SizedBox(width: 0),
                  const Text(
                    'Produk aktif',
                    style: TextStyle(fontSize: 14, color: Color(0xFF2D3748)),
                  ),
                  const Spacer(),
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 8,
                      vertical: 4,
                    ),
                    decoration: BoxDecoration(
                      color: model.aktif
                          ? const Color(0xFF48BB78)
                          : const Color(0xFFE53E3E),
                      borderRadius: BorderRadius.circular(4),
                    ),
                    child: Text(
                      model.aktif ? 'Aktif' : 'Tidak aktif',
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 12,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ),
                ],
              ),

              Row(
                children: [
                  const Icon(Icons.info_outline, size: 16, color: Color(0xFF4A5568)),

                  const SizedBox(width: 4),
                  const Text(
                    'produk aktif akan ditampilkan di katalog',
                    style: TextStyle(fontSize: 12, color: Color(0xFF4A5568)),
                  ),
                ],
              ),

              const SizedBox(height: 24),

              // ── Tombol Reset & Simpan ──
              Row(
                children: [
                  Expanded(
                    child: OutlinedButton.icon(
                      onPressed: onReset,
                      icon: const Icon(Icons.refresh_rounded, size: 16),
                      label: const Text(
                        'Reset',
                        style: TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      style: OutlinedButton.styleFrom(
                        foregroundColor: const Color(0xFF4A5568),
                        side: const BorderSide(
                          color: Color(0xFFCBD5E0),
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
                      onPressed: onSimpan,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: _accent,
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                        elevation: 0,
                      ),
                      child: Text(
                        isEdit ? 'Update' : 'Simpan',
                        style: const TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.bold,
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
    );
  }
}

// ════════════════════════════════════════════════════════════════════════════
// FORM WIDGETS
// ════════════════════════════════════════════════════════════════════════════
class _SectionTitle extends StatelessWidget {
  final String title;
  final Color color;
  const _SectionTitle({required this.title, required this.color});
  @override
  Widget build(BuildContext context) => Column(
    crossAxisAlignment: CrossAxisAlignment.start,
    children: [
      Text(
        title,
        style: TextStyle(
          fontSize: 15,
          fontWeight: FontWeight.bold,
          color: color,
        ),
      ),
      const SizedBox(height: 4),
      Container(
        height: 2,
        width: 120,
        decoration: BoxDecoration(
          color: color.withOpacity(0.3),
          borderRadius: BorderRadius.circular(2),
        ),
      ),
    ],
  );
}

class _FieldLabel extends StatelessWidget {
  final String label;
  final bool required;
  const _FieldLabel({required this.label, required this.required});
  @override
  Widget build(BuildContext context) => Padding(
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
            '*',
            style: TextStyle(
              color: Colors.red,
              fontSize: 14,
              fontWeight: FontWeight.bold,
            ),
          ),
        ],
      ],
    ),
  );
}

class _FormTextField extends StatelessWidget {
  final TextEditingController ctrl;
  final String hint;
  final String? prefix, error;
  final int maxLines;
  final TextInputType keyboardType;
  final List<TextInputFormatter>? inputFormatters;
  final void Function(String)? onChanged;

  const _FormTextField({
    required this.ctrl,
    required this.hint,
    this.prefix,
    this.error,
    this.maxLines = 1,
    this.keyboardType = TextInputType.text,
    this.inputFormatters,
    this.onChanged,
  });

  @override
  Widget build(BuildContext context) => Column(
    crossAxisAlignment: CrossAxisAlignment.start,
    children: [
      TextField(
        controller: ctrl,
        maxLines: maxLines,
        keyboardType: keyboardType,
        inputFormatters: inputFormatters,
        onChanged: onChanged,
        style: const TextStyle(fontSize: 13),
        decoration: InputDecoration(
          hintText: hint,
          prefixText: prefix,
          hintStyle: TextStyle(color: Colors.grey.shade400, fontSize: 13),
          filled: true,
          fillColor: const Color(0xFFF8F9FA),
          contentPadding: const EdgeInsets.symmetric(
            horizontal: 14,
            vertical: 12,
          ),
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(8),
            borderSide: BorderSide(
              color: error != null ? Colors.red : Colors.grey.shade300,
            ),
          ),
          enabledBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(8),
            borderSide: BorderSide(
              color: error != null ? Colors.red.shade300 : Colors.grey.shade300,
            ),
          ),
          focusedBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(8),
            borderSide: BorderSide(
              color: error != null ? Colors.red : const Color(0xFF4169E1),
              width: 1.5,
            ),
          ),
        ),
      ),
      if (error != null) ...[
        const SizedBox(height: 4),
        Row(
          children: [
            const Icon(
              Icons.error_outline_rounded,
              color: Colors.red,
              size: 13,
            ),
            const SizedBox(width: 4),
            Text(
              error!,
              style: const TextStyle(color: Colors.red, fontSize: 11),
            ),
          ],
        ),
      ],
    ],
  );
}

class _TextFieldWithAction extends StatelessWidget {
  final TextEditingController ctrl;
  final String hint;
  final String? error;
  final TextInputType keyboardType;
  final List<TextInputFormatter>? inputFormatters;
  final void Function(String)? onChanged;
  final IconData actionIcon;
  final String actionTooltip;
  final VoidCallback onAction;

  const _TextFieldWithAction({
    required this.ctrl,
    required this.hint,
    required this.actionIcon,
    required this.actionTooltip,
    required this.onAction,
    this.error,
    this.keyboardType = TextInputType.text,
    this.inputFormatters,
    this.onChanged,
  });

  @override
  Widget build(BuildContext context) => Column(
    crossAxisAlignment: CrossAxisAlignment.start,
    children: [
      TextField(
        controller: ctrl,
        keyboardType: keyboardType,
        inputFormatters: inputFormatters,
        onChanged: onChanged,
        style: const TextStyle(fontSize: 13),
        decoration: InputDecoration(
          hintText: hint,
          hintStyle: TextStyle(color: Colors.grey.shade400, fontSize: 13),
          filled: true,
          fillColor: const Color(0xFFF8F9FA),
          contentPadding: const EdgeInsets.only(
            left: 14,
            top: 12,
            bottom: 12,
            right: 4,
          ),
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(8),
            borderSide: BorderSide(
              color: error != null ? Colors.red : Colors.grey.shade300,
            ),
          ),
          enabledBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(8),
            borderSide: BorderSide(
              color: error != null ? Colors.red.shade300 : Colors.grey.shade300,
            ),
          ),
          focusedBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(8),
            borderSide: BorderSide(
              color: error != null ? Colors.red : const Color(0xFF4169E1),
              width: 1.5,
            ),
          ),
          suffixIcon: Tooltip(
            message: actionTooltip,
            child: IconButton(
              onPressed: onAction,
              icon: Icon(actionIcon, size: 20, color: Colors.grey.shade500),
              splashRadius: 18,
            ),
          ),
        ),
      ),
      if (error != null) ...[
        const SizedBox(height: 4),
        Row(
          children: [
            const Icon(
              Icons.error_outline_rounded,
              color: Colors.red,
              size: 13,
            ),
            const SizedBox(width: 4),
            Text(
              error!,
              style: const TextStyle(color: Colors.red, fontSize: 11),
            ),
          ],
        ),
      ],
    ],
  );
}

class _DropdownField extends StatelessWidget {
  final String? value;
  final String hint;
  final List<String> items;
  final String? error;
  final void Function(String?) onChanged;

  const _DropdownField({
    required this.value,
    required this.hint,
    required this.items,
    required this.onChanged,
    this.error,
  });

  @override
  Widget build(BuildContext context) => Column(
    crossAxisAlignment: CrossAxisAlignment.start,
    children: [
      Container(
        padding: const EdgeInsets.symmetric(horizontal: 12),
        decoration: BoxDecoration(
          color: const Color(0xFFF8F9FA),
          borderRadius: BorderRadius.circular(8),
          border: Border.all(
            color: error != null ? Colors.red.shade300 : Colors.grey.shade300,
          ),
        ),
        child: DropdownButtonHideUnderline(
          child: DropdownButton<String>(
            value: value,
            isExpanded: true,
            hint: Text(
              hint,
              style: TextStyle(color: Colors.grey.shade400, fontSize: 13),
            ),
            style: const TextStyle(fontSize: 13, color: Color(0xFF2D3748)),
            icon: const Icon(Icons.keyboard_arrow_down_rounded, size: 20),
            items: items
                .map((e) => DropdownMenuItem(value: e, child: Text(e)))
                .toList(),
            onChanged: onChanged,
          ),
        ),
      ),
      if (error != null) ...[
        const SizedBox(height: 4),
        Row(
          children: [
            const Icon(
              Icons.error_outline_rounded,
              color: Colors.red,
              size: 13,
            ),
            const SizedBox(width: 4),
            Text(
              error!,
              style: const TextStyle(color: Colors.red, fontSize: 11),
            ),
          ],
        ),
      ],
    ],
  );
}

class _DateField extends StatelessWidget {
  final TextEditingController ctrl;
  final String? error;
  final VoidCallback onTapCalendar;
  final void Function(String)? onChanged;

  const _DateField({
    required this.ctrl,
    required this.onTapCalendar,
    this.error,
    this.onChanged,
  });

  @override
  Widget build(BuildContext context) => Column(
    crossAxisAlignment: CrossAxisAlignment.start,
    children: [
      TextField(
        controller: ctrl,
        onChanged: onChanged,
        keyboardType: TextInputType.datetime,
        style: const TextStyle(fontSize: 13),
        decoration: InputDecoration(
          hintText: 'hari/bulan/tahun',
          hintStyle: TextStyle(color: Colors.grey.shade400, fontSize: 13),
          filled: true,
          fillColor: const Color(0xFFF8F9FA),
          contentPadding: const EdgeInsets.only(
            left: 14,
            top: 12,
            bottom: 12,
            right: 4,
          ),
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(8),
            borderSide: BorderSide(
              color: error != null ? Colors.red : Colors.grey.shade300,
            ),
          ),
          enabledBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(8),
            borderSide: BorderSide(
              color: error != null ? Colors.red.shade300 : Colors.grey.shade300,
            ),
          ),
          focusedBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(8),
            borderSide: BorderSide(
              color: error != null ? Colors.red : const Color(0xFF4169E1),
              width: 1.5,
            ),
          ),
          suffixIcon: IconButton(
            onPressed: onTapCalendar,
            icon: Icon(
              Icons.calendar_month_rounded,
              size: 30,
              color: const Color.fromARGB(255, 31, 77, 215),
            ),
            splashRadius: 18,
          ),
        ),
      ),
      if (error != null) ...[
        const SizedBox(height: 4),
        Row(
          children: [
            const Icon(
              Icons.error_outline_rounded,
              color: Colors.red,
              size: 13,
            ),
            const SizedBox(width: 4),
            Text(
              error!,
              style: const TextStyle(color: Colors.red, fontSize: 11),
            ),
          ],
        ),
      ],
    ],
  );
}

// ════════════════════════════════════════════════════════════════════════════
// CONFIRM DIALOG
// ════════════════════════════════════════════════════════════════════════════
class _ConfirmDialog extends StatelessWidget {
  final String title, message, confirmLabel;
  final IconData icon;
  final Color iconColor, confirmColor;
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
  Widget build(BuildContext context) => AlertDialog(
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
            color: iconColor.withOpacity(0.12),
            shape: BoxShape.circle,
          ),
          child: Icon(icon, color: iconColor, size: 32),
        ),
        const SizedBox(height: 16),
        Text(
          title,
          style: const TextStyle(
            fontSize: 17,
            fontWeight: FontWeight.bold,
            color: Color(0xFF2D3748),
          ),
        ),
        const SizedBox(height: 10),
        Text(
          message,
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
      ),
    ],
  );
}
