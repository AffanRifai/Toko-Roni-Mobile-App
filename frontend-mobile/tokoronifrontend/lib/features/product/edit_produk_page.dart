// lib/product/edit_produk_page.dart
//
// Halaman Edit Produk — dipisah dari produk_form_page.dart
// Dipanggil dari daftar_produk_page.dart ketika tombol Edit di tabel diklik:
//
//   Navigator.push(context, MaterialPageRoute(
//     builder: (_) => EditProdukPage(produk: p),
//   ));

import 'dart:math';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../../core/services/product_service.dart';
import '../../core/ui/keyboard_inset_padding.dart';
import '../../models/produk_model.dart';

// ════════════════════════════════════════════════════════════════════════════
// HELPERS
// ════════════════════════════════════════════════════════════════════════════
String _genKode() {
  final r = Random();
  return 'PRD-${List.generate(8, (_) => r.nextInt(10)).join()}';
}

String _genBarcode() {
  final r = Random();
  return List.generate(13, (_) => r.nextInt(10)).join();
}

String _fmtDate(DateTime dt) =>
    '${dt.day.toString().padLeft(2, '0')}/${dt.month.toString().padLeft(2, '0')}/${dt.year}';

// ════════════════════════════════════════════════════════════════════════════
// EDIT PRODUK PAGE
// ════════════════════════════════════════════════════════════════════════════
class EditProdukPage extends StatefulWidget {
  /// Data produk dari baris tabel yang diklik tombol Edit-nya
  final ProdukItem produk;

  const EditProdukPage({super.key, required this.produk});

  @override
  State<EditProdukPage> createState() => _EditProdukPageState();
}

class _EditProdukPageState extends State<EditProdukPage> {
  List<KategoriItem> _kategoriItems = [];
  late final String _initialKategoriRaw;
  bool _isSubmitting = false;
  // ── Daftar kategori & satuan ──────────────────────────────────────────────
  // Diambil dari API — pastikan semua kategori produk ada di backend
  List<String> get _kategoriList => _kategoriItems.map((k) => k.nama).toList();

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

  // ── State form ────────────────────────────────────────────────────────────
  late String _kategori;
  late String _satuan;
  late bool _aktif;
  DateTime? _kadaluarsa;

  // Error per field
  final Map<String, String?> _errors = {};

  // ── Controllers ───────────────────────────────────────────────────────────
  late final TextEditingController _namaCtrl;
  late final TextEditingController _kodeCtrl;
  late final TextEditingController _deskCtrl;
  late final TextEditingController _hargaJualCtrl;
  late final TextEditingController _hargaModalCtrl;
  late final TextEditingController _stokAwalCtrl;
  late final TextEditingController _stokMinCtrl;
  late final TextEditingController _barcodeCtrl;
  late final TextEditingController _beratCtrl;
  late final TextEditingController _dimensiCtrl;
  late final TextEditingController _kadaluarsaCtrl;

  @override
  void initState() {
    super.initState();
    final p = widget.produk;
    _initialKategoriRaw = p.kategori;

    // ── Validasi satuan: kalau tidak ada di list, fallback ke 'Pcs' ──
    final validSatuan = _findSatuanMatch(p.jenis) ?? 'Pcs';

    _kategori = p.kategori.trim();
    _satuan = validSatuan;
    _aktif = p.aktif;

    // ── Parse tanggal kadaluarsa dari string ──
    _kadaluarsa = _parseDate(p.kadaluarsa);

    // ── Init semua controllers dengan data produk ──
    _namaCtrl = TextEditingController(text: p.nama);
    _kodeCtrl = TextEditingController(text: p.kode);
    _deskCtrl = TextEditingController(text: p.deskripsi);
    _hargaJualCtrl = TextEditingController(text: p.harga.toString());
    _hargaModalCtrl = TextEditingController(
      text: p.hargaModal > 0 ? p.hargaModal.toString() : '',
    );
    _stokAwalCtrl = TextEditingController(text: p.stok.toString());
    _stokMinCtrl = TextEditingController(
      text: p.stokMinimum > 0 ? p.stokMinimum.toString() : '',
    );
    _barcodeCtrl = TextEditingController(text: p.barcode.trim());
    _beratCtrl = TextEditingController(text: p.berat);
    _dimensiCtrl = TextEditingController(text: p.dimensi);
    _kadaluarsaCtrl = TextEditingController(
      text: _kadaluarsa != null ? _fmtDate(_kadaluarsa!) : '',
    );
    _loadKategori();
  }

  Future<void> _loadKategori() async {
    try {
      final fromApi = await ProductService.getCategories();
      if (!mounted || fromApi.isEmpty) return;
      setState(() {
        _kategoriItems = fromApi;
        _kategori =
            _findKategoriMatch(_initialKategoriRaw) ??
            _findKategoriMatch(_kategori) ??
            '';
      });
    } catch (_) {}
  }

  String _normalizeKategori(String raw) =>
      raw.trim().toLowerCase().replaceAll(RegExp(r'\s+'), ' ');

  String? _findKategoriMatch(String raw) {
    final needle = _normalizeKategori(raw);
    if (needle.isEmpty || needle == '-') return null;
    for (final k in _kategoriItems) {
      if (_normalizeKategori(k.nama) == needle) return k.nama;
    }
    return null;
  }

  String? _findSatuanMatch(String raw) {
    final needle = raw.trim().toLowerCase();
    if (needle.isEmpty) return null;
    for (final s in _satuanList) {
      if (s.trim().toLowerCase() == needle) return s;
    }
    return null;
  }

  ProdukFormModel _buildModelFromForm() {
    return ProdukFormModel(
      kode: _kodeCtrl.text.trim(),
      nama: _namaCtrl.text.trim(),
      kategori: _kategori,
      deskripsi: _deskCtrl.text.trim(),
      hargaJual: _hargaJualCtrl.text.trim(),
      hargaModal: _hargaModalCtrl.text.trim(),
      stokAwal: _stokAwalCtrl.text.trim(),
      stokMinimum: _stokMinCtrl.text.trim(),
      satuan: _satuan,
      barcode: _barcodeCtrl.text.trim(),
      berat: _beratCtrl.text.trim(),
      dimensi: _dimensiCtrl.text.trim(),
      kadaluarsa: _kadaluarsa,
      aktif: _aktif,
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

  // ── Parse tanggal ─────────────────────────────────────────────────────────
  DateTime? _parseDate(String s) {
    try {
      if (s.isEmpty) return null;
      final parts = s.split('-');
      if (parts.length != 3) return null;
      if (parts[0].length == 4) {
        // YYYY-MM-DD
        return DateTime(
          int.parse(parts[0]),
          int.parse(parts[1]),
          int.parse(parts[2]),
        );
      } else {
        // DD-MM-YYYY
        return DateTime(
          int.parse(parts[2]),
          int.parse(parts[1]),
          int.parse(parts[0]),
        );
      }
    } catch (_) {
      return null;
    }
  }

  // ── Generate ulang kode & barcode ─────────────────────────────────────────
  void _regenKode() => setState(() => _kodeCtrl.text = _genKode());
  void _regenBarcode() => setState(() => _barcodeCtrl.text = _genBarcode());

  // ── Date picker ───────────────────────────────────────────────────────────
  Future<void> _pickDate() async {
    FocusManager.instance.primaryFocus?.unfocus();
    final today = DateTime.now();
    final initial =
        _kadaluarsa ?? DateTime.now().add(const Duration(days: 365));
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
    if (picked != null) {
      setState(() {
        _kadaluarsa = picked;
        _kadaluarsaCtrl.text = _fmtDate(picked);
        _errors.remove('kadaluarsa');
      });
    }
  }

  // ── Validasi ─────────────────────────────────────────────────────────────
  bool _validate() {
    final e = <String, String?>{};
    if (_namaCtrl.text.trim().isEmpty) e['nama'] = 'Nama produk wajib diisi';
    if (_kodeCtrl.text.trim().isEmpty) e['kode'] = 'Kode produk wajib diisi';
    if (_kategori.isEmpty) e['kategori'] = 'Kategori wajib dipilih';
    if (_hargaJualCtrl.text.trim().isEmpty) {
      e['hargaJual'] = 'Harga jual wajib diisi';
    }
    if (_stokAwalCtrl.text.trim().isEmpty) {
      e['stokAwal'] = 'Stok awal wajib diisi';
    }
    if (_kadaluarsa == null) {
      e['kadaluarsa'] = 'Tanggal kadaluarsa wajib diisi';
    }
    setState(
      () => _errors
        ..clear()
        ..addAll(e),
    );
    return e.isEmpty;
  }

  // ── Reset ke data awal produk ─────────────────────────────────────────────
  void _showResetDialog() {
    FocusManager.instance.primaryFocus?.unfocus();
    showDialog(
      context: context,
      builder: (_) => _ConfirmDialog(
        title: 'Reset Form',
        icon: Icons.refresh_rounded,
        iconColor: const Color(0xFFE67E22),
        message: 'Apakah kamu yakin ingin mereset form ke data awal produk?',
        confirmLabel: 'Ya, Reset',
        confirmColor: const Color(0xFFE67E22),
        onConfirm: () {
          final p = widget.produk;
          setState(() {
            _kategori = _findKategoriMatch(p.kategori) ?? '';
            _satuan = _findSatuanMatch(p.jenis) ?? 'Pcs';
            _aktif = p.aktif;
            _kadaluarsa = _parseDate(p.kadaluarsa);
            _errors.clear();
          });
          _namaCtrl.text = p.nama;
          _kodeCtrl.text = p.kode;
          _deskCtrl.text = p.deskripsi;
          _hargaJualCtrl.text = p.harga.toString();
          _hargaModalCtrl.text = p.hargaModal > 0
              ? p.hargaModal.toString()
              : '';
          _stokAwalCtrl.text = p.stok.toString();
          _stokMinCtrl.text = p.stokMinimum > 0 ? p.stokMinimum.toString() : '';
          _barcodeCtrl.text = p.barcode.trim();
          _beratCtrl.text = p.berat;
          _dimensiCtrl.text = p.dimensi;
          _kadaluarsaCtrl.text = _kadaluarsa != null
              ? _fmtDate(_kadaluarsa!)
              : '';
        },
      ),
    );
  }

  // ── Simpan perubahan ──────────────────────────────────────────────────────
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
    FocusManager.instance.primaryFocus?.unfocus();
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
    FocusManager.instance.primaryFocus?.unfocus();
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
        model: _buildModelFromForm(),
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

  // ════════════════════════════════════════════════════════════════════════
  // BUILD
  // ════════════════════════════════════════════════════════════════════════
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      resizeToAvoidBottomInset: false,
      backgroundColor: const Color(0xFFF3F4F8),
      appBar: AppBar(
        backgroundColor: const Color(0xFFD69E2E),
        foregroundColor: Colors.white,
        elevation: 0,
        shape: const RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(bottom: Radius.circular(20)),
        ),
        title: const Text(
          'Edit Produk',
          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18),
        ),
      ),
      body: KeyboardInsetPadding(
        settleDuration: Duration.zero,
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(16),
          keyboardDismissBehavior: ScrollViewKeyboardDismissBehavior.manual,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // ── Form header ──
              Container(
                width: double.infinity,
                padding: const EdgeInsets.fromLTRB(16, 14, 16, 14),
                decoration: const BoxDecoration(
                  color: Color(0xFFD69E2E),
                  borderRadius: BorderRadius.vertical(top: Radius.circular(16)),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Form Edit Produk',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 4),
                    const Text(
                      'ubah informasi produk yang ingin diperbarui',
                      style: TextStyle(color: Colors.white70, fontSize: 12),
                    ),
                  ],
                ),
              ),

              // ── Form body ──
              Container(
                decoration: const BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.vertical(
                    bottom: Radius.circular(16),
                  ),
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
                    // ══ INFORMASI DASAR ══
                    _sectionTitle('Informasi dasar'),
                    const SizedBox(height: 16),

                    _label('Nama Produk', required: true),
                    _textField(
                      _namaCtrl,
                      'Masukan nama produk',
                      error: _errors['nama'],
                      onChanged: (_) => _clearErr('nama'),
                    ),
                    const SizedBox(height: 16),

                    _label('Kode Produk', required: true),
                    _fieldWithAction(
                      ctrl: _kodeCtrl,
                      hint: 'PRD-XXXXXXXX',
                      error: _errors['kode'],
                      onChanged: (_) => _clearErr('kode'),
                      icon: Icons.refresh_rounded,
                      tooltip: 'Generate ulang kode',
                      onAction: _regenKode,
                    ),
                    const SizedBox(height: 16),

                    _label('Kategori', required: true),
                    _dropdown(
                      value: _kategoriList.contains(_kategori)
                          ? _kategori
                          : null,
                      hint: '---pilih kategori---',
                      items: _kategoriList,
                      error: _errors['kategori'],
                      onChanged: (v) => setState(() {
                        _kategori = v ?? '';
                        _errors.remove('kategori');
                      }),
                    ),
                    const SizedBox(height: 16),

                    _label('Deskripsi produk', required: false),
                    _textField(
                      _deskCtrl,
                      'Isi deskripsi jika perlu',
                      maxLines: 4,
                    ),
                    const SizedBox(height: 28),

                    // ══ HARGA & STOK ══
                    _sectionTitle('Harga & Stok'),
                    const SizedBox(height: 16),

                    _label('Harga Jual', required: true),
                    _textField(
                      _hargaJualCtrl,
                      'Rp 0',
                      prefix: 'Rp ',
                      type: TextInputType.number,
                      formatters: [FilteringTextInputFormatter.digitsOnly],
                      error: _errors['hargaJual'],
                      onChanged: (_) => _clearErr('hargaJual'),
                    ),
                    const SizedBox(height: 16),

                    _label('Harga Modal', required: false),
                    _textField(
                      _hargaModalCtrl,
                      'Rp 0',
                      prefix: 'Rp ',
                      type: TextInputType.number,
                      formatters: [FilteringTextInputFormatter.digitsOnly],
                    ),
                    const SizedBox(height: 16),

                    _label('Stok awal', required: true),
                    _textField(
                      _stokAwalCtrl,
                      '0',
                      type: TextInputType.number,
                      formatters: [FilteringTextInputFormatter.digitsOnly],
                      error: _errors['stokAwal'],
                      onChanged: (_) => _clearErr('stokAwal'),
                    ),
                    const SizedBox(height: 16),

                    _label('Stok minimum', required: false),
                    _textField(
                      _stokMinCtrl,
                      '0',
                      type: TextInputType.number,
                      formatters: [FilteringTextInputFormatter.digitsOnly],
                    ),
                    const SizedBox(height: 16),

                    _label('Satuan', required: true),
                    _dropdown(
                      value: _satuan,
                      hint: 'Pilih satuan',
                      items: _satuanList,
                      onChanged: (v) => setState(() => _satuan = v ?? 'Pcs'),
                    ),
                    const SizedBox(height: 16),

                    _label('Barcode', required: false),
                    _fieldWithAction(
                      ctrl: _barcodeCtrl,
                      hint: 'Kosongkan jika tidak ada',
                      error: _errors['barcode'],
                      onChanged: (_) => _clearErr('barcode'),
                      icon: Icons.barcode_reader,
                      tooltip: 'Generate barcode',
                      onAction: _regenBarcode,
                      type: TextInputType.number,
                      formatters: [FilteringTextInputFormatter.digitsOnly],
                    ),
                    const SizedBox(height: 28),

                    // ══ INFORMASI TAMBAHAN ══
                    _sectionTitle('Informasi Tambahan'),
                    const SizedBox(height: 16),

                    _label('Berat (gram)', required: false),
                    _textField(
                      _beratCtrl,
                      '0',
                      type: TextInputType.number,
                      formatters: [FilteringTextInputFormatter.digitsOnly],
                    ),
                    const SizedBox(height: 16),

                    _label('Dimensi', required: false),
                    _textField(_dimensiCtrl, 'panjang X lebar X tinggi'),
                    const SizedBox(height: 16),

                    _label('Tanggal Kadaluarsa', required: true),
                    _dateField(),
                    const SizedBox(height: 20),

                    // Produk aktif
                    Row(
                      children: [
                        Checkbox(
                          value: _aktif,
                          onChanged: (v) => setState(() => _aktif = v ?? true),
                          activeColor: const Color(0xFFD69E2E),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(4),
                          ),
                        ),
                        const SizedBox(width: 4),
                        const Text(
                          'Produk aktif',
                          style: TextStyle(
                            fontSize: 14,
                            color: Color(0xFF2D3748),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 28),

                    // ── Tombol Reset & Update ──
                    Row(
                      children: [
                        Expanded(
                          child: OutlinedButton.icon(
                            onPressed: _showResetDialog,
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
                            onPressed: _showSimpanDialog,
                            style: ElevatedButton.styleFrom(
                              backgroundColor: const Color(0xFFD69E2E),
                              foregroundColor: Colors.white,
                              padding: const EdgeInsets.symmetric(vertical: 15),
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(12),
                              ),
                              elevation: 0,
                            ),
                            child: const Text(
                              'Update',
                              style: TextStyle(
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
          ),
        ),
      ),
    );
  }

  // ════════════════════════════════════════════════════════════════════════
  // HELPER BUILDERS — private ke file ini
  // ════════════════════════════════════════════════════════════════════════

  void _clearErr(String key) {
    if (!_errors.containsKey(key)) return;
    setState(() => _errors.remove(key));
  }

  Widget _sectionTitle(String title) => Column(
    crossAxisAlignment: CrossAxisAlignment.start,
    children: [
      Text(
        title,
        style: const TextStyle(
          fontSize: 15,
          fontWeight: FontWeight.bold,
          color: Color(0xFFD69E2E),
        ),
      ),
      const SizedBox(height: 4),
      Container(
        height: 2,
        width: 120,
        decoration: BoxDecoration(
          color: const Color(0xFFD69E2E).withValues(alpha: 0.3),
          borderRadius: BorderRadius.circular(2),
        ),
      ),
    ],
  );

  Widget _label(String text, {required bool required}) => Padding(
    padding: const EdgeInsets.only(bottom: 7),
    child: Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Text(
          text,
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

  Widget _textField(
    TextEditingController ctrl,
    String hint, {
    String? prefix,
    String? error,
    int maxLines = 1,
    TextInputType type = TextInputType.text,
    TextInputAction? textInputAction,
    List<TextInputFormatter>? formatters,
    void Function(String)? onChanged,
  }) {
    final effectiveKeyboardType = maxLines > 1 ? TextInputType.multiline : type;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        TextField(
          controller: ctrl,
          maxLines: maxLines,
          keyboardType: effectiveKeyboardType,
          textInputAction:
              textInputAction ??
              (maxLines > 1 ? TextInputAction.newline : TextInputAction.next),
          inputFormatters: formatters,
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
                color: error != null
                    ? Colors.red.shade300
                    : Colors.grey.shade300,
              ),
            ),
            focusedBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(10),
              borderSide: BorderSide(
                color: error != null ? Colors.red : const Color(0xFFD69E2E),
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
                error,
                style: const TextStyle(color: Colors.red, fontSize: 11),
              ),
            ],
          ),
        ],
      ],
    );
  }

  Widget _fieldWithAction({
    required TextEditingController ctrl,
    required String hint,
    required IconData icon,
    required String tooltip,
    required VoidCallback onAction,
    String? error,
    TextInputType type = TextInputType.text,
    TextInputAction? textInputAction,
    List<TextInputFormatter>? formatters,
    void Function(String)? onChanged,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        TextField(
          controller: ctrl,
          keyboardType: type,
          textInputAction: textInputAction ?? TextInputAction.next,
          inputFormatters: formatters,
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
                color: error != null
                    ? Colors.red.shade300
                    : Colors.grey.shade300,
              ),
            ),
            focusedBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(10),
              borderSide: BorderSide(
                color: error != null ? Colors.red : const Color(0xFFD69E2E),
                width: 1.5,
              ),
            ),
            suffixIcon: Tooltip(
              message: tooltip,
              child: IconButton(
                onPressed: onAction,
                icon: Icon(icon, size: 22, color: Colors.grey.shade500),
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
                error,
                style: const TextStyle(color: Colors.red, fontSize: 11),
              ),
            ],
          ),
        ],
      ],
    );
  }

  Widget _dropdown({
    required String? value,
    required String hint,
    required List<String> items,
    required void Function(String?) onChanged,
    String? error,
  }) {
    return Column(
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
                error,
                style: const TextStyle(color: Colors.red, fontSize: 11),
              ),
            ],
          ),
        ],
      ],
    );
  }

  Widget _dateField() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        TextField(
          controller: _kadaluarsaCtrl,
          onChanged: (_) => _clearErr('kadaluarsa'),
          keyboardType: TextInputType.datetime,
          textInputAction: TextInputAction.next,
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
                color: _errors['kadaluarsa'] != null
                    ? Colors.red
                    : Colors.grey.shade300,
              ),
            ),
            enabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(10),
              borderSide: BorderSide(
                color: _errors['kadaluarsa'] != null
                    ? Colors.red.shade300
                    : Colors.grey.shade300,
              ),
            ),
            focusedBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(10),
              borderSide: BorderSide(
                color: _errors['kadaluarsa'] != null
                    ? Colors.red
                    : const Color(0xFFD69E2E),
                width: 1.5,
              ),
            ),
            suffixIcon: IconButton(
              onPressed: _pickDate,
              icon: Icon(
                Icons.calendar_month_rounded,
                size: 22,
                color: Colors.grey.shade500,
              ),
              splashRadius: 20,
            ),
          ),
        ),
        if (_errors['kadaluarsa'] != null) ...[
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
                _errors['kadaluarsa']!,
                style: const TextStyle(color: Colors.red, fontSize: 11),
              ),
            ],
          ),
        ],
      ],
    );
  }
}

// ════════════════════════════════════════════════════════════════════════════
// CONFIRM DIALOG — reusable untuk reset & simpan
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
            color: iconColor.withValues(alpha: 0.12),
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
