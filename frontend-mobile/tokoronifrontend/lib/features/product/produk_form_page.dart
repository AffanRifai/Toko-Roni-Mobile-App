// lib/product/produk_form_page.dart
import 'dart:math';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../../core/services/product_service.dart';
import '../../shared/widgets/shared_widgets.dart';
import '../../models/produk_model.dart';

// ════════════════════════════════════════════════════════════════════════════
// HELPERS
// ════════════════════════════════════════════════════════════════════════════
String generateKodeProduk() {
  final rng = Random();
  return 'PRD-${List.generate(8, (_) => rng.nextInt(10)).join()}';
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
  late _FormCtrls _ctrls;
  final Map<String, String?> _errors = {};
  List<KategoriItem> _kategoriItems = [];
  bool _isSubmitting = false;

  static const _satuanList = [
    'Dus',
    'Pcs',
    'Pack',
    'Kg',
    'Per Kg',
    'Liter',
    'Per Liter',
    'Meter',
  ];
  List<String> get _kategoriList => _kategoriItems.map((k) => k.nama).toList();

  @override
  void initState() {
    super.initState();
    _model = ProdukFormModel(
      kode: generateKodeProduk(),
      barcode: generateBarcode(),
    );
    _ctrls = _FormCtrls.fromModel(_model);
    _loadKategori();
  }

  @override
  void dispose() {
    _ctrls.dispose();
    super.dispose();
  }

  void _regenKode() => setState(() {
    _ctrls.kode.text = _model.kode = generateKodeProduk();
  });
  void _regenBarcode() => setState(() {
    _ctrls.barcode.text = _model.barcode = generateBarcode();
  });

  Future<void> _loadKategori() async {
    try {
      final fromApi = await ProductService.getCategories();
      if (!mounted || fromApi.isEmpty) return;
      setState(() => _kategoriItems = fromApi);
    } catch (_) {}
  }

  void _syncModelFromCtrls() {
    _model
      ..nama = _ctrls.nama.text.trim()
      ..kode = _ctrls.kode.text.trim()
      ..deskripsi = _ctrls.deskripsi.text.trim()
      ..hargaJual = _ctrls.hargaJual.text.trim()
      ..hargaModal = _ctrls.hargaModal.text.trim()
      ..stokAwal = _ctrls.stokAwal.text.trim()
      ..stokMinimum = _ctrls.stokMin.text.trim()
      ..barcode = _ctrls.barcode.text.trim()
      ..berat = _ctrls.berat.text.trim()
      ..dimensi = _ctrls.dimensi.text.trim();
  }

  Future<void> _pickDate() async {
    final today = DateTime.now();
    final initial =
        _model.kadaluarsa ?? DateTime.now().add(const Duration(days: 365));
    final picked = await showDatePicker(
      context: context,
      initialDate: initial.isBefore(today) ? today : initial,
      firstDate: today,
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
        _ctrls.kadaluarsa.text = _fmtDate(picked);
        _errors.remove('kadaluarsa');
      });
  }

  bool _validate() {
    final e = <String, String?>{};
    if (_ctrls.nama.text.trim().isEmpty) e['nama'] = 'Nama produk wajib diisi';
    if (_ctrls.kode.text.trim().isEmpty) e['kode'] = 'Kode produk wajib diisi';
    if (_model.kategori.isEmpty) e['kategori'] = 'Kategori wajib dipilih';
    if (_ctrls.hargaJual.text.trim().isEmpty)
      e['hargaJual'] = 'Harga jual wajib diisi';
    if (_ctrls.stokAwal.text.trim().isEmpty)
      e['stokAwal'] = 'Stok awal wajib diisi';
    if (_ctrls.barcode.text.trim().isEmpty)
      e['barcode'] = 'Barcode wajib diisi';
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
        _ctrls.resetToModel(_model);
      },
    ),
  );

  void _showSimpanDialog() {
    if (!_validate()) {
      _showErrorSnack();
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
        onConfirm: _submitCreateProduk,
      ),
    );
  }

  Future<void> _submitCreateProduk() async {
    if (_isSubmitting) return;
    _syncModelFromCtrls();
    setState(() => _isSubmitting = true);
    try {
      final latestKategori = await ProductService.getCategories();
      final kategoriForSubmit = latestKategori.isNotEmpty
          ? latestKategori
          : _kategoriItems;
      if (latestKategori.isNotEmpty && mounted) {
        setState(() => _kategoriItems = latestKategori);
      }
      await ProductService.createProduct(
        model: _model,
        categories: kategoriForSubmit,
      );
      if (!mounted) return;
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
      Navigator.pop(context, true);
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(e.toString().replaceFirst('Exception: ', '')),
          backgroundColor: const Color(0xFFE53E3E),
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(10),
          ),
        ),
      );
    } finally {
      if (mounted) setState(() => _isSubmitting = false);
    }
  }

  void _showErrorSnack() => ScaffoldMessenger.of(context).showSnackBar(
    SnackBar(
      content: const Text('Harap isi semua field yang wajib diisi (*)'),
      backgroundColor: const Color(0xFFE53E3E),
      behavior: SnackBarBehavior.floating,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
    ),
  );

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
        ctrls: _ctrls,
        kategoriList: _kategoriList,
        satuanList: _satuanList,
        onKategoriChanged: (v) => setState(() {
          _model.kategori = v ?? '';
          _errors.remove('kategori');
        }),
        onSatuanChanged: (v) => setState(() => _model.satuan = v ?? 'Dus'),
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
// EDIT PRODUK PAGE — pre-fill dari ProdukItem yang dipilih di tabel
// ════════════════════════════════════════════════════════════════════════════
class EditProdukPage extends StatefulWidget {
  final ProdukItem produk; // data dari baris tabel yang diklik
  const EditProdukPage({super.key, required this.produk});
  @override
  State<EditProdukPage> createState() => _EditProdukPageState();
}

class _EditProdukPageState extends State<EditProdukPage> {
  late ProdukFormModel _model;
  late _FormCtrls _ctrls;
  final Map<String, String?> _errors = {};
  List<KategoriItem> _kategoriItems = [];
  bool _isSubmitting = false;

  static const _satuanList = [
    'Dus',
    'Pcs',
    'Pack',
    'Kg',
    'Per Kg',
    'Liter',
    'Per Liter',
    'Meter',
  ];
  List<String> get _kategoriList => _kategoriItems.map((k) => k.nama).toList();

  @override
  void initState() {
    super.initState();
    // Pre-fill semua field dari ProdukItem yang dikirim dari tabel
    _model = ProdukFormModel.fromItem(widget.produk);
    if (_model.barcode.isEmpty) _model.barcode = generateBarcode();
    _ctrls = _FormCtrls.fromModel(_model);
    _loadKategori();
  }

  @override
  void dispose() {
    _ctrls.dispose();
    super.dispose();
  }

  void _regenKode() => setState(() {
    _ctrls.kode.text = _model.kode = generateKodeProduk();
  });
  void _regenBarcode() => setState(() {
    _ctrls.barcode.text = _model.barcode = generateBarcode();
  });

  Future<void> _loadKategori() async {
    try {
      final fromApi = await ProductService.getCategories();
      if (!mounted || fromApi.isEmpty) return;
      setState(() {
        _kategoriItems = fromApi;
        final activeKategori = _model.kategori.trim().toLowerCase();
        final matched = _kategoriItems.where(
          (k) => k.nama.trim().toLowerCase() == activeKategori,
        );
        _model.kategori = matched.isNotEmpty ? matched.first.nama : '';
      });
    } catch (_) {}
  }

  void _syncModelFromCtrls() {
    _model
      ..nama = _ctrls.nama.text.trim()
      ..kode = _ctrls.kode.text.trim()
      ..deskripsi = _ctrls.deskripsi.text.trim()
      ..hargaJual = _ctrls.hargaJual.text.trim()
      ..hargaModal = _ctrls.hargaModal.text.trim()
      ..stokAwal = _ctrls.stokAwal.text.trim()
      ..stokMinimum = _ctrls.stokMin.text.trim()
      ..barcode = _ctrls.barcode.text.trim()
      ..berat = _ctrls.berat.text.trim()
      ..dimensi = _ctrls.dimensi.text.trim();
  }

  Future<void> _pickDate() async {
    final today = DateTime.now();
    final initial =
        _model.kadaluarsa ?? DateTime.now().add(const Duration(days: 365));
    final picked = await showDatePicker(
      context: context,
      initialDate: initial.isBefore(today) ? today : initial,
      firstDate: today,
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
        _ctrls.kadaluarsa.text = _fmtDate(picked);
        _errors.remove('kadaluarsa');
      });
  }

  bool _validate() {
    final e = <String, String?>{};
    if (_ctrls.nama.text.trim().isEmpty) e['nama'] = 'Nama produk wajib diisi';
    if (_ctrls.kode.text.trim().isEmpty) e['kode'] = 'Kode produk wajib diisi';
    if (_model.kategori.isEmpty) e['kategori'] = 'Kategori wajib dipilih';
    if (_ctrls.hargaJual.text.trim().isEmpty)
      e['hargaJual'] = 'Harga jual wajib diisi';
    if (_ctrls.stokAwal.text.trim().isEmpty)
      e['stokAwal'] = 'Stok awal wajib diisi';
    if (_ctrls.barcode.text.trim().isEmpty)
      e['barcode'] = 'Barcode wajib diisi';
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
        _ctrls.resetToModel(_model);
      },
    ),
  );

  void _showSimpanDialog() {
    if (!_validate()) {
      _showErrorSnack();
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
        onConfirm: _submitUpdateProduk,
      ),
    );
  }

  Future<void> _submitUpdateProduk() async {
    if (_isSubmitting) return;
    if (widget.produk.id == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: const Text('ID produk tidak ditemukan, gagal update.'),
          backgroundColor: const Color(0xFFE53E3E),
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(10),
          ),
        ),
      );
      return;
    }

    _syncModelFromCtrls();
    setState(() => _isSubmitting = true);
    try {
      final latestKategori = await ProductService.getCategories();
      final kategoriForSubmit = latestKategori.isNotEmpty
          ? latestKategori
          : _kategoriItems;
      if (latestKategori.isNotEmpty && mounted) {
        setState(() => _kategoriItems = latestKategori);
      }
      await ProductService.updateProduct(
        productId: widget.produk.id!,
        model: _model,
        categories: kategoriForSubmit,
      );
      if (!mounted) return;
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
      Navigator.pop(context, true);
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(e.toString().replaceFirst('Exception: ', '')),
          backgroundColor: const Color(0xFFE53E3E),
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(10),
          ),
        ),
      );
    } finally {
      if (mounted) setState(() => _isSubmitting = false);
    }
  }

  void _showErrorSnack() => ScaffoldMessenger.of(context).showSnackBar(
    SnackBar(
      content: const Text('Harap isi semua field yang wajib diisi (*)'),
      backgroundColor: const Color(0xFFE53E3E),
      behavior: SnackBarBehavior.floating,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
    ),
  );

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
        ctrls: _ctrls,
        kategoriList: _kategoriList,
        satuanList: _satuanList,
        onKategoriChanged: (v) => setState(() {
          _model.kategori = v ?? '';
          _errors.remove('kategori');
        }),
        onSatuanChanged: (v) => setState(() => _model.satuan = v ?? 'Dus'),
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
// FORM CONTROLLERS — bundled supaya mudah di-pass & dispose
// ════════════════════════════════════════════════════════════════════════════
class _FormCtrls {
  final TextEditingController nama, kode, deskripsi;
  final TextEditingController hargaJual, hargaModal;
  final TextEditingController stokAwal, stokMin;
  final TextEditingController barcode, berat, dimensi, kadaluarsa;

  _FormCtrls({
    required this.nama,
    required this.kode,
    required this.deskripsi,
    required this.hargaJual,
    required this.hargaModal,
    required this.stokAwal,
    required this.stokMin,
    required this.barcode,
    required this.berat,
    required this.dimensi,
    required this.kadaluarsa,
  });

  factory _FormCtrls.fromModel(ProdukFormModel m) => _FormCtrls(
    nama: TextEditingController(text: m.nama),
    kode: TextEditingController(text: m.kode),
    deskripsi: TextEditingController(text: m.deskripsi),
    hargaJual: TextEditingController(text: m.hargaJual),
    hargaModal: TextEditingController(text: m.hargaModal),
    stokAwal: TextEditingController(text: m.stokAwal),
    stokMin: TextEditingController(text: m.stokMinimum),
    barcode: TextEditingController(text: m.barcode),
    berat: TextEditingController(text: m.berat),
    dimensi: TextEditingController(text: m.dimensi),
    kadaluarsa: TextEditingController(
      text: m.kadaluarsa != null ? _fmtDate(m.kadaluarsa!) : '',
    ),
  );

  /// Reset semua controller ke nilai model
  void resetToModel(ProdukFormModel m) {
    nama.text = m.nama;
    kode.text = m.kode;
    deskripsi.text = m.deskripsi;
    hargaJual.text = m.hargaJual;
    hargaModal.text = m.hargaModal;
    stokAwal.text = m.stokAwal;
    stokMin.text = m.stokMinimum;
    barcode.text = m.barcode;
    berat.text = m.berat;
    dimensi.text = m.dimensi;
    kadaluarsa.text = m.kadaluarsa != null ? _fmtDate(m.kadaluarsa!) : '';
  }

  void dispose() {
    for (final c in [
      nama,
      kode,
      deskripsi,
      hargaJual,
      hargaModal,
      stokAwal,
      stokMin,
      barcode,
      berat,
      dimensi,
      kadaluarsa,
    ]) {
      c.dispose();
    }
  }
}

String _fmtDate(DateTime dt) =>
    '${dt.day.toString().padLeft(2, '0')}/${dt.month.toString().padLeft(2, '0')}/${dt.year}';

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
// PRODUK FORM BODY — shared antara Tambah & Edit, semua field 1 kolom penuh
// ════════════════════════════════════════════════════════════════════════════
class _ProdukFormBody extends StatelessWidget {
  final bool isEdit;
  final ProdukFormModel model;
  final Map<String, String?> errors;
  final _FormCtrls ctrls;
  final List<String> kategoriList, satuanList;
  final void Function(String?) onKategoriChanged, onSatuanChanged;
  final void Function(bool) onAktifChanged;
  final VoidCallback onRegenKode, onRegenBarcode, onPickDate, onReset, onSimpan;
  final void Function(String) onClearError;

  const _ProdukFormBody({
    required this.isEdit,
    required this.model,
    required this.errors,
    required this.ctrls,
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
        // ── Form header card ──────────────────────────────────────────────────
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

        // ── Form body ─────────────────────────────────────────────────────────
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
          padding: const EdgeInsets.fromLTRB(16, 20, 16, 20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // ══════════════════════════════
              // INFORMASI DASAR
              // ══════════════════════════════
              _SectionTitle(title: 'Informasi dasar', color: _accent),
              const SizedBox(height: 16),

              // Nama Produk
              _FieldLabel(label: 'Nama Produk', required: true),
              _FField(
                ctrl: ctrls.nama,
                hint: 'Masukan nama produk',
                error: errors['nama'],
                onChanged: (_) => onClearError('nama'),
              ),
              const SizedBox(height: 16),

              // Kode Produk
              _FieldLabel(label: 'Kode Produk', required: true),
              _FFieldAction(
                ctrl: ctrls.kode,
                hint: 'PRD-XXXXXXXX',
                error: errors['kode'],
                onChanged: (_) => onClearError('kode'),
                actionIcon: Icons.refresh_rounded,
                actionTooltip: 'Generate ulang kode',
                onAction: onRegenKode,
              ),
              const SizedBox(height: 16),

              // Kategori
              _FieldLabel(label: 'Kategori', required: true),
              _FDropdown(
                value: kategoriList.contains(model.kategori)
                    ? model.kategori
                    : null,
                hint: '---pilih kategori---',
                items: kategoriList,
                error: errors['kategori'],
                onChanged: onKategoriChanged,
              ),
              const SizedBox(height: 16),

              // Deskripsi
              _FieldLabel(label: 'Deskripsi produk', required: false),
              _FField(
                ctrl: ctrls.deskripsi,
                hint: 'Isi deskripsi jika perlu',
                maxLines: 4,
              ),
              const SizedBox(height: 28),

              // ══════════════════════════════
              // HARGA & STOK
              // ══════════════════════════════
              _SectionTitle(title: 'Harga & Stok', color: _accent),
              const SizedBox(height: 16),

              // Harga Jual
              _FieldLabel(label: 'Harga Jual', required: true),
              _FField(
                ctrl: ctrls.hargaJual,
                hint: 'Rp 0',
                prefix: 'Rp ',
                keyboardType: TextInputType.number,
                inputFormatters: [FilteringTextInputFormatter.digitsOnly],
                error: errors['hargaJual'],
                onChanged: (_) => onClearError('hargaJual'),
              ),
              const SizedBox(height: 16),

              // Harga Modal
              _FieldLabel(label: 'Harga Modal', required: false),
              _FField(
                ctrl: ctrls.hargaModal,
                hint: 'Rp 0',
                prefix: 'Rp ',
                keyboardType: TextInputType.number,
                inputFormatters: [FilteringTextInputFormatter.digitsOnly],
              ),
              const SizedBox(height: 16),

              // Stok Awal
              _FieldLabel(label: 'Stok awal', required: true),
              _FField(
                ctrl: ctrls.stokAwal,
                hint: '0',
                keyboardType: TextInputType.number,
                inputFormatters: [FilteringTextInputFormatter.digitsOnly],
                error: errors['stokAwal'],
                onChanged: (_) => onClearError('stokAwal'),
              ),
              const SizedBox(height: 16),

              // Stok Minimum
              _FieldLabel(label: 'Stok minimum', required: false),
              _FField(
                ctrl: ctrls.stokMin,
                hint: '0',
                keyboardType: TextInputType.number,
                inputFormatters: [FilteringTextInputFormatter.digitsOnly],
              ),
              const SizedBox(height: 16),

              // Satuan
              _FieldLabel(label: 'Satuan', required: true),
              _FDropdown(
                value: satuanList.contains(model.satuan) ? model.satuan : null,
                hint: 'Pilih satuan',
                items: satuanList,
                onChanged: onSatuanChanged,
              ),
              const SizedBox(height: 16),

              // Barcode
              _FieldLabel(label: 'Barcode', required: true),
              _FFieldAction(
                ctrl: ctrls.barcode,
                hint: '0',
                keyboardType: TextInputType.number,
                inputFormatters: [FilteringTextInputFormatter.digitsOnly],
                error: errors['barcode'],
                onChanged: (_) => onClearError('barcode'),
                actionIcon: Icons.barcode_reader,
                actionTooltip: 'Generate barcode',
                onAction: onRegenBarcode,
              ),
              const SizedBox(height: 28),

              // ══════════════════════════════
              // INFORMASI TAMBAHAN
              // ══════════════════════════════
              _SectionTitle(title: 'Informasi Tambahan', color: _accent),
              const SizedBox(height: 16),

              // Berat
              _FieldLabel(label: 'Berat (gram)', required: false),
              _FField(
                ctrl: ctrls.berat,
                hint: '0',
                keyboardType: TextInputType.number,
                inputFormatters: [FilteringTextInputFormatter.digitsOnly],
              ),
              const SizedBox(height: 16),

              // Dimensi
              _FieldLabel(label: 'Dimensi', required: false),
              _FField(ctrl: ctrls.dimensi, hint: 'panjang X lebar X tinggi'),
              const SizedBox(height: 16),

              // Tanggal Kadaluarsa
              _FieldLabel(label: 'Tanggal Kadaluarsa', required: true),
              _FDateField(
                ctrl: ctrls.kadaluarsa,
                error: errors['kadaluarsa'],
                onTapCalendar: onPickDate,
                onChanged: (_) => onClearError('kadaluarsa'),
              ),
              const SizedBox(height: 20),

              // Produk Aktif checkbox
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
                  const SizedBox(width: 4),
                  const Text(
                    'Produk aktif',
                    style: TextStyle(fontSize: 14, color: Color(0xFF2D3748)),
                  ),
                ],
              ),
              const SizedBox(height: 28),

              // ── Tombol Reset & Simpan ─────────────────────────────────────────
              Row(
                children: [
                  Expanded(
                    child: OutlinedButton.icon(
                      onPressed: onReset,
                      icon: const Icon(Icons.refresh_rounded, size: 17),
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
                        padding: const EdgeInsets.symmetric(vertical: 15),
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
                        padding: const EdgeInsets.symmetric(vertical: 15),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                        elevation: 0,
                      ),
                      child: Text(
                        isEdit ? 'Update' : 'Simpan',
                        style: const TextStyle(
                          fontSize: 15,
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
// SECTION TITLE
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

// ════════════════════════════════════════════════════════════════════════════
// FIELD LABEL
// ════════════════════════════════════════════════════════════════════════════
class _FieldLabel extends StatelessWidget {
  final String label;
  final bool required;
  const _FieldLabel({required this.label, required this.required});

  @override
  Widget build(BuildContext context) => Padding(
    padding: const EdgeInsets.only(bottom: 7),
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

// ════════════════════════════════════════════════════════════════════════════
// TEXT FIELD
// ════════════════════════════════════════════════════════════════════════════
class _FField extends StatelessWidget {
  final TextEditingController ctrl;
  final String hint;
  final String? prefix, error;
  final int maxLines;
  final TextInputType keyboardType;
  final List<TextInputFormatter>? inputFormatters;
  final void Function(String)? onChanged;

  const _FField({
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
            vertical: 14,
          ),
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(10),
            borderSide: BorderSide(
              color: error != null ? Colors.red : Colors.grey.shade300,
            ),
          ),
          enabledBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(10),
            borderSide: BorderSide(
              color: error != null ? Colors.red.shade300 : Colors.grey.shade300,
            ),
          ),
          focusedBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(10),
            borderSide: BorderSide(
              color: error != null ? Colors.red : const Color(0xFF4169E1),
              width: 1.5,
            ),
          ),
        ),
      ),
      if (error != null) ...[
        const SizedBox(height: 5),
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
// TEXT FIELD + ACTION BUTTON (refresh/barcode)
// ════════════════════════════════════════════════════════════════════════════
class _FFieldAction extends StatelessWidget {
  final TextEditingController ctrl;
  final String hint;
  final String? error;
  final TextInputType keyboardType;
  final List<TextInputFormatter>? inputFormatters;
  final void Function(String)? onChanged;
  final IconData actionIcon;
  final String actionTooltip;
  final VoidCallback onAction;

  const _FFieldAction({
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
            top: 14,
            bottom: 14,
            right: 4,
          ),
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(10),
            borderSide: BorderSide(
              color: error != null ? Colors.red : Colors.grey.shade300,
            ),
          ),
          enabledBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(10),
            borderSide: BorderSide(
              color: error != null ? Colors.red.shade300 : Colors.grey.shade300,
            ),
          ),
          focusedBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(10),
            borderSide: BorderSide(
              color: error != null ? Colors.red : const Color(0xFF4169E1),
              width: 1.5,
            ),
          ),
          suffixIcon: Tooltip(
            message: actionTooltip,
            child: IconButton(
              onPressed: onAction,
              icon: Icon(actionIcon, size: 22, color: Colors.grey.shade500),
              splashRadius: 20,
            ),
          ),
        ),
      ),
      if (error != null) ...[
        const SizedBox(height: 5),
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
// DROPDOWN FIELD
// ════════════════════════════════════════════════════════════════════════════
class _FDropdown extends StatelessWidget {
  final String? value;
  final String hint;
  final List<String> items;
  final String? error;
  final void Function(String?) onChanged;

  const _FDropdown({
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
          borderRadius: BorderRadius.circular(10),
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
            icon: const Icon(Icons.keyboard_arrow_down_rounded, size: 22),
            items: items
                .map((e) => DropdownMenuItem(value: e, child: Text(e)))
                .toList(),
            onChanged: onChanged,
          ),
        ),
      ),
      if (error != null) ...[
        const SizedBox(height: 5),
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
// DATE FIELD
// ════════════════════════════════════════════════════════════════════════════
class _FDateField extends StatelessWidget {
  final TextEditingController ctrl;
  final String? error;
  final VoidCallback onTapCalendar;
  final void Function(String)? onChanged;

  const _FDateField({
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
            top: 14,
            bottom: 14,
            right: 4,
          ),
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(10),
            borderSide: BorderSide(
              color: error != null ? Colors.red : Colors.grey.shade300,
            ),
          ),
          enabledBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(10),
            borderSide: BorderSide(
              color: error != null ? Colors.red.shade300 : Colors.grey.shade300,
            ),
          ),
          focusedBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(10),
            borderSide: BorderSide(
              color: error != null ? Colors.red : const Color(0xFF4169E1),
              width: 1.5,
            ),
          ),
          suffixIcon: IconButton(
            onPressed: onTapCalendar,
            icon: Icon(
              Icons.calendar_month_rounded,
              size: 22,
              color: Colors.grey.shade500,
            ),
            splashRadius: 20,
          ),
        ),
      ),
      if (error != null) ...[
        const SizedBox(height: 5),
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
