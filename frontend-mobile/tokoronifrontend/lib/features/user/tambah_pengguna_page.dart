// lib/user/tambah_pengguna_page.dart
import 'package:flutter/material.dart';
import '../../core/services/user_service.dart';
import '../../models/pengguna_model.dart';

class TambahPenggunaPage extends StatefulWidget {
  const TambahPenggunaPage({super.key});

  @override
  State<TambahPenggunaPage> createState() => _TambahPenggunaPageState();
}

class _TambahPenggunaPageState extends State<TambahPenggunaPage> {
  static const _blue = Color(0xFF3B6FE8);

  // Controllers
  final _namaCtrl = TextEditingController();
  final _emailCtrl = TextEditingController();
  final _passCtrl = TextEditingController();
  final _konfPassCtrl = TextEditingController();
  final _teleponCtrl = TextEditingController();
  final _alamatCtrl = TextEditingController();

  String? _role;
  String? _jenisToko;
  bool _aktif = true;
  bool _obscurePass = true;
  bool _obscureKonfPass = true;

  // Errors
  final Map<String, String?> _errors = {};
  bool _isSubmitting = false;

  @override
  void dispose() {
    for (final c in [
      _namaCtrl,
      _emailCtrl,
      _passCtrl,
      _konfPassCtrl,
      _teleponCtrl,
      _alamatCtrl,
    ]) {
      c.dispose();
    }
    super.dispose();
  }

  // ── Validasi ──────────────────────────────────────────────────────────────
  bool _validate() {
    final e = <String, String?>{};
    if (_namaCtrl.text.trim().isEmpty) {
      e['nama'] = 'Nama lengkap wajib diisi';
    }
    if (_emailCtrl.text.trim().isEmpty) {
      e['email'] = 'Email wajib diisi';
    } else if (!RegExp(
      r'^[\w.]+@[\w.]+\.\w+$',
    ).hasMatch(_emailCtrl.text.trim())) {
      e['email'] = 'Format email tidak valid';
    }
    if (_passCtrl.text.isEmpty) {
      e['pass'] = 'Password wajib diisi';
    } else if (_passCtrl.text.length < 8) {
      e['pass'] = 'Password minimal 8 karakter';
    }
    if (_konfPassCtrl.text.isEmpty) {
      e['konfPass'] = 'Konfirmasi password wajib diisi';
    } else if (_konfPassCtrl.text != _passCtrl.text) {
      e['konfPass'] = 'Password tidak sama';
    }
    if (_role == null) {
      e['role'] = 'Role wajib dipilih';
    }
    if (_jenisToko == null) {
      e['jenisToko'] = 'Jenis toko wajib dipilih';
    }
    setState(
      () => _errors
        ..clear()
        ..addAll(e),
    );
    return e.isEmpty;
  }

  void _clearErr(String key) => setState(() => _errors.remove(key));

  // ── Dialog Batal ──────────────────────────────────────────────────────────
  void _showBatalDialog() {
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
                color: Colors.orange.withOpacity(0.10),
                shape: BoxShape.circle,
              ),
              child: const Icon(
                Icons.refresh_rounded,
                color: Colors.orange,
                size: 32,
              ),
            ),
            const SizedBox(height: 16),
            const Text(
              'Batalkan Form?',
              style: TextStyle(
                fontSize: 17,
                fontWeight: FontWeight.bold,
                color: Color(0xFF2D3748),
              ),
            ),
            const SizedBox(height: 10),
            Text(
              'Data yang sudah diisi akan hilang.',
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
          _actionRow(
            onBatal: () => Navigator.pop(context),
            onConfirm: () {
              Navigator.pop(context);
              Navigator.pop(context);
            },
            confirmLabel: 'Ya, Batalkan',
            confirmColor: Colors.orange,
          ),
        ],
      ),
    );
  }

  // ── Dialog Simpan ─────────────────────────────────────────────────────────
  void _showSimpanDialog() {
    if (!_validate()) {
      _showErrorSnack();
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
                color: _blue.withOpacity(0.10),
                shape: BoxShape.circle,
              ),
              child: const Icon(
                Icons.person_add_rounded,
                color: _blue,
                size: 32,
              ),
            ),
            const SizedBox(height: 16),
            const Text(
              'Simpan Pengguna',
              style: TextStyle(
                fontSize: 17,
                fontWeight: FontWeight.bold,
                color: Color(0xFF2D3748),
              ),
            ),
            const SizedBox(height: 10),
            Text(
              'Apakah data pengguna "${_namaCtrl.text.trim()}" sudah benar?',
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
          _actionRow(
            onBatal: () => Navigator.pop(context),
            onConfirm: _isSubmitting
                ? () {}
                : () async {
                    Navigator.pop(context);
                    await _submitCreate();
                  },
            confirmLabel: 'Ya, Simpan',
            confirmColor: _blue,
          ),
        ],
      ),
    );
  }

  Future<void> _submitCreate() async {
    if (_isSubmitting) return;
    setState(() => _isSubmitting = true);
    try {
      final created = await UserService.createUser(
        nama: _namaCtrl.text.trim(),
        email: _emailCtrl.text.trim(),
        password: _passCtrl.text,
        role: roleApiFromLabel(_role ?? ''),
        jenisToko: jenisTokoApiFromLabel(_jenisToko ?? ''),
        aktif: _aktif,
        telepon: _teleponCtrl.text.trim(),
        alamat: _alamatCtrl.text.trim(),
      );

      if (!mounted) return;
      Navigator.pop(
        context,
        'Pengguna "${created.nama}" berhasil ditambahkan!',
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

  void _showErrorSnack() => ScaffoldMessenger.of(context).showSnackBar(
    SnackBar(
      content: const Text('Harap isi semua field yang wajib diisi (*)'),
      backgroundColor: const Color(0xFFE53E3E),
      behavior: SnackBarBehavior.floating,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
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
        backgroundColor: _blue,
        foregroundColor: Colors.white,
        elevation: 0,
        shape: const RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(bottom: Radius.circular(20)),
        ),
        title: const Text(
          'Tambah Pengguna',
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
                  // Header
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.fromLTRB(20, 16, 20, 16),
                    decoration: const BoxDecoration(
                      color: _blue,
                      borderRadius: BorderRadius.vertical(
                        top: Radius.circular(16),
                      ),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Form Tambah Pengguna',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 17,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 4),
                        const Text(
                          'Tambah pengguna baru',
                          style: TextStyle(color: Colors.white70, fontSize: 12),
                        ),
                      ],
                    ),
                  ),

                  // Body
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
                        // Nama Lengkap *
                        _label('Nama Lengkap', required: true),
                        _field(
                          _namaCtrl,
                          'Masukan nama lengkap',
                          error: _errors['nama'],
                          onChanged: (_) => _clearErr('nama'),
                        ),
                        _hint('Contoh : kurniawan, agus, dll.'),
                        const SizedBox(height: 16),

                        // Email *
                        _label('Email', required: true),
                        _field(
                          _emailCtrl,
                          'Masukan alamat email',
                          type: TextInputType.emailAddress,
                          error: _errors['email'],
                          onChanged: (_) => _clearErr('email'),
                        ),
                        _hint('Email ini akan digunakan untuk login'),
                        const SizedBox(height: 16),

                        // Password *
                        _label('Password', required: true),
                        _passwordField(
                          _passCtrl,
                          'Masukan password anda',
                          obscure: _obscurePass,
                          onToggle: () =>
                              setState(() => _obscurePass = !_obscurePass),
                          error: _errors['pass'],
                          onChanged: (_) => _clearErr('pass'),
                        ),
                        _bulletHints(const [
                          'password Minimal 8 karakter',
                          'disarankan kombinasi huruf besar, kecil, angka, dan simbol',
                          'jangan gunakan password yang mudah ditebak',
                        ]),
                        const SizedBox(height: 16),

                        // Konfirmasi Password *
                        _label('Konfirmasi Password', required: true),
                        _passwordField(
                          _konfPassCtrl,
                          'Ulangi password',
                          obscure: _obscureKonfPass,
                          onToggle: () => setState(
                            () => _obscureKonfPass = !_obscureKonfPass,
                          ),
                          error: _errors['konfPass'],
                          onChanged: (_) => _clearErr('konfPass'),
                        ),
                        _hint('Password harus sama'),
                        const SizedBox(height: 16),

                        // No Telepon (opsional)
                        _label('No Telepon', required: false, opsional: true),
                        _field(
                          _teleponCtrl,
                          'Masukan no telepon anda',
                          type: TextInputType.phone,
                        ),
                        _hint('Opsional untuk keperluan kontak'),
                        const SizedBox(height: 16),

                        // Alamat (opsional)
                        _label('Alamat', required: false, opsional: true),
                        _field(
                          _alamatCtrl,
                          'Masukan alamat lengkap',
                          maxLines: 3,
                        ),
                        _hint('Opsional alamat tempat tinggal'),
                        const SizedBox(height: 16),

                        // Role * + Jenis Toko * (2 kolom)
                        Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  _label('Role', required: true),
                                  _dropdown(
                                    _role,
                                    'Pilih role',
                                    roleList,
                                    error: _errors['role'],
                                    onChanged: (v) => setState(() {
                                      _role = v;
                                      _clearErr('role');
                                    }),
                                  ),
                                  _hint('Tentukan hak akses pengguna'),
                                ],
                              ),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  _label('Jenis Toko', required: true),
                                  _dropdown(
                                    _jenisToko,
                                    'Pilih jenis toko',
                                    jenisTokoList,
                                    error: _errors['jenisToko'],
                                    onChanged: (v) => setState(() {
                                      _jenisToko = v;
                                      _clearErr('jenisToko');
                                    }),
                                  ),
                                  _hint(
                                    'pilih jenis toko tempat pengguna bekerja',
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 20),

                        // Status Akun
                        _label('Status Akun', required: false),
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
                            crossAxisAlignment: CrossAxisAlignment.start,
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
                                    'Aktifkan akun pengguna',
                                    style: TextStyle(
                                      fontSize: 14,
                                      fontWeight: FontWeight.w500,
                                      color: _aktif
                                          ? const Color(0xFF48BB78)
                                          : Colors.grey,
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 4),
                              _hint('Pengguna dapat login jika status aktif'),
                            ],
                          ),
                        ),
                        const SizedBox(height: 28),

                        // Tombol Batal + Simpan
                        Row(
                          children: [
                            Expanded(
                              child: OutlinedButton.icon(
                                onPressed: _showBatalDialog,
                                icon: const Icon(
                                  Icons.refresh_rounded,
                                  size: 16,
                                ),
                                label: const Text(
                                  'Batal',
                                  style: TextStyle(
                                    fontSize: 14,
                                    fontWeight: FontWeight.w600,
                                  ),
                                ),
                                style: OutlinedButton.styleFrom(
                                  foregroundColor: const Color(0xFF4A5568),
                                  side: BorderSide(color: Colors.grey.shade400),
                                  padding: const EdgeInsets.symmetric(
                                    vertical: 14,
                                  ),
                                  shape: RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(12),
                                  ),
                                ),
                              ),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: ElevatedButton(
                                onPressed: _isSubmitting
                                    ? null
                                    : _showSimpanDialog,
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: _blue,
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
            const SizedBox(height: 24),
          ],
        ),
      ),
    );
  }

  // ── Field helpers ─────────────────────────────────────────────────────────
  Widget _label(String text, {required bool required, bool opsional = false}) =>
      Padding(
        padding: const EdgeInsets.only(bottom: 7),
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
            if (opsional) ...[
              const SizedBox(width: 6),
              Text(
                '(opsional)',
                style: TextStyle(
                  fontSize: 12,
                  color: Colors.grey.shade500,
                  fontWeight: FontWeight.normal,
                ),
              ),
            ],
          ],
        ),
      );

  Widget _field(
    TextEditingController ctrl,
    String hint, {
    String? error,
    int maxLines = 1,
    TextInputType type = TextInputType.text,
    void Function(String)? onChanged,
  }) => Column(
    crossAxisAlignment: CrossAxisAlignment.start,
    children: [
      TextField(
        controller: ctrl,
        maxLines: maxLines,
        keyboardType: type,
        onChanged: onChanged,
        style: const TextStyle(fontSize: 13),
        decoration: InputDecoration(
          hintText: hint,
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
              color: error != null ? Colors.red : _blue,
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
              error,
              style: const TextStyle(color: Colors.red, fontSize: 11),
            ),
          ],
        ),
      ],
    ],
  );

  Widget _passwordField(
    TextEditingController ctrl,
    String hint, {
    required bool obscure,
    required VoidCallback onToggle,
    String? error,
    void Function(String)? onChanged,
  }) => Column(
    crossAxisAlignment: CrossAxisAlignment.start,
    children: [
      TextField(
        controller: ctrl,
        obscureText: obscure,
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
              color: error != null ? Colors.red : _blue,
              width: 1.5,
            ),
          ),
          suffixIcon: IconButton(
            onPressed: onToggle,
            icon: Icon(
              obscure
                  ? Icons.visibility_off_outlined
                  : Icons.visibility_outlined,
              size: 20,
              color: Colors.grey.shade400,
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
              error,
              style: const TextStyle(color: Colors.red, fontSize: 11),
            ),
          ],
        ),
      ],
    ],
  );

  Widget _dropdown(
    String? value,
    String hint,
    List<String> items, {
    String? error,
    required void Function(String?) onChanged,
  }) => Column(
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
              error,
              style: const TextStyle(color: Colors.red, fontSize: 11),
            ),
          ],
        ),
      ],
    ],
  );

  Widget _hint(String text) => Padding(
    padding: const EdgeInsets.only(top: 5),
    child: Text(
      text,
      style: TextStyle(fontSize: 11, color: Colors.grey.shade500),
    ),
  );

  Widget _bulletHints(List<String> items) => Padding(
    padding: const EdgeInsets.only(top: 5),
    child: Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: items
          .map(
            (t) => Padding(
              padding: const EdgeInsets.only(bottom: 2),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    '• ',
                    style: TextStyle(fontSize: 11, color: Colors.grey.shade500),
                  ),
                  Expanded(
                    child: Text(
                      t,
                      style: TextStyle(
                        fontSize: 11,
                        color: Colors.grey.shade500,
                      ),
                    ),
                  ),
                ],
              ),
            ),
          )
          .toList(),
    ),
  );
}

// ── Dialog action row ──────────────────────────────────────────────────────────
Widget _actionRow({
  required VoidCallback onBatal,
  required VoidCallback onConfirm,
  required String confirmLabel,
  required Color confirmColor,
}) => Row(
  children: [
    Expanded(
      child: OutlinedButton(
        onPressed: onBatal,
        style: OutlinedButton.styleFrom(
          side: BorderSide(color: Colors.grey.shade300),
          padding: const EdgeInsets.symmetric(vertical: 12),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(10),
          ),
        ),
        child: const Text(
          'Batal',
          style: TextStyle(color: Colors.grey, fontWeight: FontWeight.w600),
        ),
      ),
    ),
    const SizedBox(width: 10),
    Expanded(
      child: ElevatedButton(
        onPressed: onConfirm,
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
);
