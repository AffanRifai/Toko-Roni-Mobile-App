// lib/category/edit_kategori_page.dart
import 'package:flutter/material.dart';
import 'package:tokoronifrontend/core/services/category_service.dart';
import 'manajemen_kategori_page.dart' show KategoriData;

class EditKategoriPage extends StatefulWidget {
  final KategoriData kategori;
  const EditKategoriPage({super.key, required this.kategori});

  @override
  State<EditKategoriPage> createState() => _EditKategoriPageState();
}

class _EditKategoriPageState extends State<EditKategoriPage> {
  late TextEditingController _namaCtrl;
  late TextEditingController _deskCtrl;
  late String _slug;
  late bool _aktif;
  String? _namaError;
  bool _isSubmitting = false;

  static const _primaryBlue = Color(0xFF3B6FE8);

  // ── Generate slug ─────────────────────────────────────────────────────────
  String _generateSlug(String nama) {
    return nama
        .toLowerCase()
        .trim()
        .replaceAll(RegExp(r'\s+'), '-')
        .replaceAll(RegExp(r'[^a-z0-9\-]'), '');
  }

  void _onNamaChanged(String value) {
    setState(() {
      _slug = value.trim().isEmpty
          ? widget.kategori.slug
          : _generateSlug(value);
      if (_namaError != null && value.trim().isNotEmpty) _namaError = null;
    });
  }

  void _regenSlug() {
    setState(() {
      _slug = _generateSlug(
        _namaCtrl.text.isNotEmpty ? _namaCtrl.text : widget.kategori.nama,
      );
    });
  }

  @override
  void initState() {
    super.initState();
    final k = widget.kategori;
    _namaCtrl = TextEditingController(text: k.nama);
    _deskCtrl = TextEditingController(text: k.deskripsi);
    _slug = k.slug;
    _aktif = k.aktif;
  }

  @override
  void dispose() {
    _namaCtrl.dispose();
    _deskCtrl.dispose();
    super.dispose();
  }

  // ── Simpan ────────────────────────────────────────────────────────────────
  void _simpan() {
    if (_namaCtrl.text.trim().isEmpty) {
      setState(() => _namaError = 'Nama kategori wajib diisi');
      return;
    }
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
                color: _primaryBlue.withOpacity(0.1),
                shape: BoxShape.circle,
              ),
              child: const Icon(
                Icons.edit_rounded,
                color: _primaryBlue,
                size: 32,
              ),
            ),
            const SizedBox(height: 16),
            const Text(
              'Simpan Perubahan',
              style: TextStyle(
                fontSize: 17,
                fontWeight: FontWeight.bold,
                color: Color(0xFF2D3748),
              ),
            ),
            const SizedBox(height: 10),
            Text(
              'Apakah perubahan kategori "${_namaCtrl.text.trim()}" sudah benar?',
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
                    style: TextStyle(color: Colors.grey),
                  ),
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: ElevatedButton(
                  onPressed: _isSubmitting
                      ? null
                      : () async {
                          Navigator.pop(context); // tutup dialog konfirmasi
                          await _submitUpdate();
                        },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: _primaryBlue,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 12),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(10),
                    ),
                    elevation: 0,
                  ),
                  child: Text(
                    _isSubmitting ? 'Menyimpan...' : 'Ya, Simpan',
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

  // ── Hapus permanen ────────────────────────────────────────────────────────
  Future<void> _submitUpdate() async {
    if (_isSubmitting) return;
    if (widget.kategori.id <= 0) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: const Text('ID kategori tidak valid. Muat ulang data kategori.'),
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
      final nama = _namaCtrl.text.trim();
      final slugForSubmit = _generateSlug(nama);
      final updated = await CategoryService.updateCategory(
        categoryId: widget.kategori.id,
        nama: nama,
        slug: slugForSubmit,
        deskripsi: _deskCtrl.text.trim(),
        aktif: _aktif,
      );

      String persistedSlug = updated.slug.trim();
      try {
        final categories = await CategoryService.getCategories();
        for (final category in categories) {
          if (category.id == widget.kategori.id) {
            persistedSlug = category.slug.trim();
            break;
          }
        }
      } catch (_) {
        // Jika refresh gagal, pakai data dari response update.
      }

      if (!mounted) return;
      final successMessage = persistedSlug == slugForSubmit
          ? 'Kategori "${updated.nama}" berhasil diperbarui!'
          : 'Nama kategori berhasil diperbarui, tapi slug belum berubah di server.';
      Navigator.pop(
        context,
        successMessage,
      );
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

  Future<void> _submitDelete() async {
    if (_isSubmitting) return;
    if (widget.kategori.id <= 0) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: const Text('ID kategori tidak valid. Muat ulang data kategori.'),
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
      await CategoryService.deleteCategory(categoryId: widget.kategori.id);
      if (!mounted) return;
      Navigator.pop(context, 'Kategori "${widget.kategori.nama}" dihapus');
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

  void _showHapusDialog() {
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
                color: const Color(0xFFE53E3E).withOpacity(0.1),
                shape: BoxShape.circle,
              ),
              child: const Icon(
                Icons.delete_forever_rounded,
                color: Color(0xFFE53E3E),
                size: 32,
              ),
            ),
            const SizedBox(height: 16),
            const Text(
              'Hapus Kategori',
              style: TextStyle(
                fontSize: 17,
                fontWeight: FontWeight.bold,
                color: Color(0xFF2D3748),
              ),
            ),
            const SizedBox(height: 10),
            Text(
              'Apakah kamu yakin ingin menghapus kategori "${widget.kategori.nama}"?\nTindakan ini tidak dapat dibatalkan.',
              textAlign: TextAlign.center,
              style: TextStyle(
                fontSize: 13,
                color: Colors.grey.shade600,
                height: 1.5,
              ),
            ),
            if (widget.kategori.totalProduk > 0) ...[
              const SizedBox(height: 12),
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: Colors.orange.shade50,
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: Colors.orange.shade200),
                ),
                child: Row(
                  children: [
                    Icon(
                      Icons.warning_rounded,
                      color: Colors.orange.shade600,
                      size: 16,
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        '${widget.kategori.totalProduk} produk masih menggunakan kategori ini.',
                        style: TextStyle(
                          fontSize: 12,
                          color: Colors.orange.shade700,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ],
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
                    style: TextStyle(color: Colors.grey),
                  ),
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: ElevatedButton(
                  onPressed: _isSubmitting
                      ? null
                      : () async {
                          Navigator.pop(context); // tutup dialog konfirmasi
                          await _submitDelete();
                        },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFFE53E3E),
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 12),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(10),
                    ),
                    elevation: 0,
                  ),
                  child: Text(
                    _isSubmitting ? 'Menghapus...' : 'Ya, Hapus',
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

  // ════════════════════════════════════════════════════════════════════════
  // BUILD
  // ════════════════════════════════════════════════════════════════════════
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF3F4F8),
      appBar: AppBar(
        backgroundColor: _primaryBlue,
        foregroundColor: Colors.white,
        elevation: 0,
        shape: const RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(bottom: Radius.circular(20)),
        ),
        title: const Text(
          'Edit Kategori',
          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18),
        ),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new_rounded),
          onPressed: () => Navigator.pop(context),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            // ── Form card ──────────────────────────────────────────────────────
            Container(
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(16),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.08),
                    blurRadius: 12,
                    offset: const Offset(0, 4),
                  ),
                ],
              ),
              child: Column(
                children: [
                  // Header biru
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.fromLTRB(20, 16, 20, 16),
                    decoration: const BoxDecoration(
                      color: _primaryBlue,
                      borderRadius: BorderRadius.vertical(
                        top: Radius.circular(16),
                      ),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Form Edit Kategori',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 17,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 4),
                        const Text(
                          'Perbarui data kategori sesuai kebutuhan',
                          style: TextStyle(color: Colors.white70, fontSize: 12),
                        ),
                      ],
                    ),
                  ),

                  // Form body
                  Container(
                    padding: const EdgeInsets.fromLTRB(20, 24, 20, 24),
                    decoration: const BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.vertical(
                        bottom: Radius.circular(16),
                      ),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // ── Nama Kategori ──
                        _label('Nama Kategori', required: true),
                        TextField(
                          controller: _namaCtrl,
                          onChanged: _onNamaChanged,
                          style: const TextStyle(fontSize: 14),
                          decoration: InputDecoration(
                            hintText: 'Masukan nama kategori',
                            hintStyle: TextStyle(
                              color: Colors.grey.shade400,
                              fontSize: 14,
                            ),
                            filled: true,
                            fillColor: const Color(0xFFF8F9FA),
                            contentPadding: const EdgeInsets.symmetric(
                              horizontal: 14,
                              vertical: 14,
                            ),
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(10),
                              borderSide: BorderSide(
                                color: _namaError != null
                                    ? Colors.red
                                    : Colors.grey.shade300,
                              ),
                            ),
                            enabledBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(10),
                              borderSide: BorderSide(
                                color: _namaError != null
                                    ? Colors.red.shade300
                                    : Colors.grey.shade300,
                              ),
                            ),
                            focusedBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(10),
                              borderSide: BorderSide(
                                color: _namaError != null
                                    ? Colors.red
                                    : _primaryBlue,
                                width: 1.5,
                              ),
                            ),
                          ),
                        ),
                        if (_namaError != null) ...[
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
                                _namaError!,
                                style: const TextStyle(
                                  color: Colors.red,
                                  fontSize: 11,
                                ),
                              ),
                            ],
                          ),
                        ],
                        const SizedBox(height: 6),
                        _infoText(
                          'Nama kategori akan ditampilkan dihalaman produk dan katalog',
                        ),
                        const SizedBox(height: 20),

                        // ── URL Slug ──
                        Container(
                          padding: const EdgeInsets.all(14),
                          decoration: BoxDecoration(
                            color: const Color(0xFFEBF4FF),
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: const Color(0xFFBEE3F8)),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text(
                                'URL Slug (otomatis)',
                                style: TextStyle(
                                  fontSize: 13,
                                  fontWeight: FontWeight.w600,
                                  color: Color(0xFF2D3748),
                                ),
                              ),
                              const SizedBox(height: 10),
                              Row(
                                children: [
                                  // Slug display
                                  Expanded(
                                    child: Container(
                                      padding: const EdgeInsets.symmetric(
                                        horizontal: 12,
                                        vertical: 11,
                                      ),
                                      decoration: BoxDecoration(
                                        color: Colors.white,
                                        borderRadius: BorderRadius.circular(8),
                                        border: Border.all(
                                          color: const Color(0xFFBEE3F8),
                                        ),
                                      ),
                                      child: Row(
                                        children: [
                                          Text(
                                            '/categories/ ',
                                            style: TextStyle(
                                              fontSize: 13,
                                              color: Colors.grey.shade500,
                                            ),
                                          ),
                                          Flexible(
                                            child: Text(
                                              _slug,
                                              style: const TextStyle(
                                                fontSize: 13,
                                                color: _primaryBlue,
                                                fontWeight: FontWeight.w600,
                                              ),
                                            ),
                                          ),
                                        ],
                                      ),
                                    ),
                                  ),
                                  const SizedBox(width: 8),
                                  // Regenerate button
                                  ElevatedButton.icon(
                                    onPressed: _regenSlug,
                                    icon: const Icon(
                                      Icons.refresh_rounded,
                                      size: 15,
                                    ),
                                    label: const Text(
                                      'Regenerate',
                                      style: TextStyle(fontSize: 12),
                                    ),
                                    style: ElevatedButton.styleFrom(
                                      backgroundColor: Colors.white,
                                      foregroundColor: const Color(0xFF4A5568),
                                      side: BorderSide(
                                        color: Colors.grey.shade300,
                                      ),
                                      padding: const EdgeInsets.symmetric(
                                        horizontal: 12,
                                        vertical: 10,
                                      ),
                                      shape: RoundedRectangleBorder(
                                        borderRadius: BorderRadius.circular(8),
                                      ),
                                      elevation: 0,
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 8),
                              _infoText(
                                'URL Slug akan dibuat otomatis dari nama kategori',
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 20),

                        // ── Deskripsi ──
                        _label('Deskripsi Kategori', required: false),
                        TextField(
                          controller: _deskCtrl,
                          maxLines: 4,
                          style: const TextStyle(fontSize: 14),
                          decoration: InputDecoration(
                            hintText: 'Tambahkan deskripsi kategori (opsional)',
                            hintStyle: TextStyle(
                              color: Colors.grey.shade400,
                              fontSize: 13,
                            ),
                            filled: true,
                            fillColor: const Color(0xFFF8F9FA),
                            contentPadding: const EdgeInsets.all(14),
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(10),
                              borderSide: BorderSide(
                                color: Colors.grey.shade300,
                              ),
                            ),
                            enabledBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(10),
                              borderSide: BorderSide(
                                color: Colors.grey.shade300,
                              ),
                            ),
                            focusedBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(10),
                              borderSide: const BorderSide(
                                color: _primaryBlue,
                                width: 1.5,
                              ),
                            ),
                          ),
                        ),
                        const SizedBox(height: 6),
                        _infoText(
                          'Deskripsi membantu pengguna memahami kategori produk ini',
                        ),
                        const SizedBox(height: 20),

                        // ── Status Kategori ──
                        _label('Status Kategori', required: false),
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 14,
                            vertical: 12,
                          ),
                          decoration: BoxDecoration(
                            color: const Color(0xFFF8F9FA),
                            borderRadius: BorderRadius.circular(10),
                            border: Border.all(color: Colors.grey.shade300),
                          ),
                          child: Column(
                            children: [
                              Row(
                                children: [
                                  Switch(
                                    value: _aktif,
                                    onChanged: (v) =>
                                        setState(() => _aktif = v),
                                    activeColor: Colors.white,
                                    activeTrackColor: const Color(0xFF48BB78),
                                    inactiveTrackColor: Colors.grey.shade400,
                                  ),
                                  const SizedBox(width: 8),
                                  Text(
                                    _aktif ? 'Aktif' : 'Nonaktif',
                                    style: TextStyle(
                                      fontSize: 14,
                                      fontWeight: FontWeight.w600,
                                      color: _aktif
                                          ? const Color(0xFF48BB78)
                                          : Colors.grey,
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 6),
                              _infoText(
                                'Kategori yang nonaktif tidak akan ditampilkan dihalaman publik',
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 28),

                        // ── Tombol Batal & Simpan ──
                        Row(
                          children: [
                            Expanded(
                              child: OutlinedButton(
                                onPressed: () => Navigator.pop(context),
                                style: OutlinedButton.styleFrom(
                                  foregroundColor: const Color(0xFF2D3748),
                                  side: BorderSide(color: Colors.grey.shade400),
                                  padding: const EdgeInsets.symmetric(
                                    vertical: 14,
                                  ),
                                  shape: RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(12),
                                  ),
                                ),
                                child: const Text(
                                  'Batal',
                                  style: TextStyle(
                                    fontSize: 14,
                                    fontWeight: FontWeight.w600,
                                  ),
                                ),
                              ),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: ElevatedButton(
                                onPressed: _isSubmitting ? null : _simpan,
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: _primaryBlue,
                                  foregroundColor: Colors.white,
                                  padding: const EdgeInsets.symmetric(
                                    vertical: 14,
                                  ),
                                  shape: RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(12),
                                  ),
                                  elevation: 0,
                                ),
                                child: Text(
                                  _isSubmitting ? 'Menyimpan...' : 'Simpan',
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
                ],
              ),
            ),
            const SizedBox(height: 16),

            // ── Opsi lanjutan — Hapus Kategori ────────────────────────────────
            Container(
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(16),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.06),
                    blurRadius: 8,
                    offset: const Offset(0, 2),
                  ),
                ],
              ),
              child: Column(
                children: [
                  // Header
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.fromLTRB(20, 14, 20, 14),
                    decoration: const BoxDecoration(
                      color: _primaryBlue,
                      borderRadius: BorderRadius.vertical(
                        top: Radius.circular(16),
                      ),
                    ),
                    child: const Text(
                      'Opsi lanjutan',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 15,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),

                  // Body
                  Container(
                    padding: const EdgeInsets.fromLTRB(20, 20, 20, 20),
                    decoration: const BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.vertical(
                        bottom: Radius.circular(16),
                      ),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Title hapus
                        const Text(
                          'Hapus Kategori',
                          style: TextStyle(
                            fontSize: 15,
                            fontWeight: FontWeight.bold,
                            color: Color(0xFF2D3748),
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          'Hapus peremanen kategori ini. Tindakan tidak dapat dibatalkan',
                          style: TextStyle(
                            fontSize: 12,
                            color: Colors.grey.shade500,
                          ),
                        ),
                        const SizedBox(height: 16),

                        // Info produk & tanggal
                        _infoRow(
                          'Total produk',
                          ': ${widget.kategori.totalProduk}',
                        ),
                        const SizedBox(height: 6),
                        _infoRow(
                          'Terakhir diperbarui',
                          ': ${widget.kategori.terakhirDiperbarui}',
                        ),
                        const SizedBox(height: 16),

                        // Tombol Hapus — full width di bawah info supaya tidak overflow
                        SizedBox(
                          width: double.infinity,
                          child: ElevatedButton.icon(
                            onPressed: _isSubmitting ? null : _showHapusDialog,
                            icon: const Icon(
                              Icons.delete_forever_rounded,
                              size: 18,
                            ),
                            label: Text(
                              _isSubmitting ? 'Memproses...' : 'Hapus Kategori',
                              style: const TextStyle(
                                fontSize: 13,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                            style: ElevatedButton.styleFrom(
                              backgroundColor: const Color(0xFFE53E3E),
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
                    ),
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

  Widget _label(String text, {required bool required}) => Padding(
    padding: const EdgeInsets.only(bottom: 8),
    child: Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Text(
          text,
          style: const TextStyle(
            fontSize: 14,
            fontWeight: FontWeight.w600,
            color: Color(0xFF2D3748),
          ),
        ),
        if (required) ...[
          const SizedBox(width: 4),
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

  Widget _infoText(String text) => Row(
    children: [
      Icon(Icons.info_outline_rounded, size: 13, color: Colors.grey.shade500),
      const SizedBox(width: 5),
      Expanded(
        child: Text(
          text,
          style: TextStyle(fontSize: 11, color: Colors.grey.shade500),
        ),
      ),
    ],
  );

  Widget _infoRow(String label, String value) => Row(
    children: [
      SizedBox(
        width: 130,
        child: Text(
          label,
          style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
        ),
      ),
      Text(
        value,
        style: const TextStyle(
          fontSize: 12,
          color: Color(0xFF2D3748),
          fontWeight: FontWeight.w500,
        ),
      ),
    ],
  );
}
