// lib/user/edit_pengguna_page.dart
import 'package:flutter/material.dart';
import '../../core/services/user_service.dart';
import '../../models/pengguna_model.dart';

class EditPenggunaPage extends StatefulWidget {
  final PenggunaData pengguna;
  const EditPenggunaPage({super.key, required this.pengguna});

  @override
  State<EditPenggunaPage> createState() => _EditPenggunaPageState();
}

class _EditPenggunaPageState extends State<EditPenggunaPage> {
  static const _blue = Color(0xFF3B6FE8);

  late TextEditingController _namaCtrl;
  late TextEditingController _emailCtrl;
  final _passCtrl = TextEditingController();
  final _konfPassCtrl = TextEditingController();

  late String? _role;
  late String? _jenisToko;
  late bool _aktif;

  bool _obscurePass = true;
  bool _obscureKonfPass = true;

  final Map<String, String?> _errors = {};
  bool _isSubmitting = false;

  @override
  void initState() {
    super.initState();
    final p = widget.pengguna;
    _namaCtrl = TextEditingController(text: p.nama);
    _emailCtrl = TextEditingController(text: p.email);
    // Role & jenis toko: validasi apakah ada di list
    _role = roleList.contains(p.role) ? p.role : null;
    _jenisToko = jenisTokoList.contains(p.jenisToko) ? p.jenisToko : null;
    _aktif = p.aktif;
  }

  @override
  void dispose() {
    for (final c in [_namaCtrl, _emailCtrl, _passCtrl, _konfPassCtrl]) {
      c.dispose();
    }
    super.dispose();
  }

  void _clearErr(String key) => setState(() => _errors.remove(key));

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
    // Password hanya wajib sama jika diisi
    if (_passCtrl.text.isNotEmpty && _passCtrl.text.length < 8) {
      e['pass'] = 'Password minimal 8 karakter';
    }
    if (_passCtrl.text.isNotEmpty && _konfPassCtrl.text != _passCtrl.text) {
      e['konfPass'] = 'Password tidak sama';
    }
    setState(
      () => _errors
        ..clear()
        ..addAll(e),
    );
    return e.isEmpty;
  }

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
              'Batalkan Perubahan?',
              style: TextStyle(
                fontSize: 17,
                fontWeight: FontWeight.bold,
                color: Color(0xFF2D3748),
              ),
            ),
            const SizedBox(height: 10),
            Text(
              'Perubahan yang belum disimpan akan hilang.',
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
              child: const Icon(Icons.edit_rounded, color: _blue, size: 32),
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
              'Apakah perubahan data pengguna "${_namaCtrl.text.trim()}" sudah benar?',
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
                    await _submitUpdate();
                  },
            confirmLabel: 'Ya, Simpan',
            confirmColor: _blue,
          ),
        ],
      ),
    );
  }

  Future<void> _submitUpdate() async {
    if ((_role ?? '').isEmpty || (_jenisToko ?? '').isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: const Text('Role dan jenis toko wajib dipilih'),
          backgroundColor: const Color(0xFFE53E3E),
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(10),
          ),
        ),
      );
      return;
    }

    if (_isSubmitting) return;
    setState(() => _isSubmitting = true);

    try {
      final updated = await UserService.updateUser(
        userId: widget.pengguna.id,
        nama: _namaCtrl.text.trim(),
        email: _emailCtrl.text.trim(),
        role: roleApiFromLabel(_role ?? ''),
        jenisToko: jenisTokoApiFromLabel(_jenisToko ?? ''),
        aktif: _aktif,
        password: _passCtrl.text.trim().isEmpty ? null : _passCtrl.text.trim(),
        telepon: widget.pengguna.telepon,
        alamat: widget.pengguna.alamat,
      );

      if (!mounted) return;
      Navigator.pop(context, 'Data "${updated.nama}" berhasil diperbarui!');
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
      backgroundColor: const Color(0xFFF3F4F8),
      appBar: AppBar(
        backgroundColor: _blue,
        foregroundColor: Colors.white,
        elevation: 0,
        shape: const RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(bottom: Radius.circular(20)),
        ),
        title: const Text(
          'Edit Pengguna',
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
                          'Form Edit Pengguna',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 17,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 4),
                        const Text(
                          'Edit data jika dibutuhkan',
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
                        const SizedBox(height: 16),

                        // Password Baru (opsional)
                        _label(
                          'Password Baru',
                          required: false,
                          opsional: true,
                        ),
                        _passwordField(
                          _passCtrl,
                          'Kosongkan jika tidak diubah',
                          obscure: _obscurePass,
                          onToggle: () =>
                              setState(() => _obscurePass = !_obscurePass),
                          error: _errors['pass'],
                          onChanged: (_) => _clearErr('pass'),
                        ),
                        const SizedBox(height: 16),

                        // Konfirmasi Password
                        _label('Konfirmasi Password', required: false),
                        _passwordField(
                          _konfPassCtrl,
                          'Kosongkan jika tidak diubah',
                          obscure: _obscureKonfPass,
                          onToggle: () => setState(
                            () => _obscureKonfPass = !_obscureKonfPass,
                          ),
                          error: _errors['konfPass'],
                          onChanged: (_) => _clearErr('konfPass'),
                        ),
                        const SizedBox(height: 16),

                        // Role + Jenis Toko (2 kolom)
                        Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  _label('Role', required: false),
                                  _dropdown(
                                    _role,
                                    'Pilih role',
                                    roleList,
                                    onChanged: (v) => setState(() => _role = v),
                                  ),
                                ],
                              ),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  _label('Jenis Toko', required: false),
                                  _dropdown(
                                    _jenisToko,
                                    'Pilih jenis toko',
                                    jenisTokoList,
                                    onChanged: (v) =>
                                        setState(() => _jenisToko = v),
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
                                    'Akun aktif',
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
                              Row(
                                children: [
                                  Icon(
                                    Icons.info_outline_rounded,
                                    size: 13,
                                    color: Colors.grey.shade500,
                                  ),
                                  const SizedBox(width: 5),
                                  Text(
                                    'Pengguna dapat login jika status aktif',
                                    style: TextStyle(
                                      fontSize: 11,
                                      color: Colors.grey.shade500,
                                    ),
                                  ),
                                ],
                              ),
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
            const SizedBox(height: 16),

            // ── Catatan card ───────────────────────────────────────────────────
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: const Color(0xFFEBF4FF),
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: const Color(0xFFBEE3F8)),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Catatan',
                    style: TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w600,
                      color: Color(0xFF2D3748),
                    ),
                  ),
                  const SizedBox(height: 8),
                  ...[
                    'Password hanya berubah jika diisi',
                    'Role menentukan hak akses',
                    'User nonaktif tidak bisa login',
                  ].map(
                    (t) => Padding(
                      padding: const EdgeInsets.only(bottom: 4),
                      child: Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            '• ',
                            style: TextStyle(
                              fontSize: 12,
                              color: Colors.grey.shade600,
                            ),
                          ),
                          Expanded(
                            child: Text(
                              t,
                              style: TextStyle(
                                fontSize: 12,
                                color: Colors.grey.shade600,
                              ),
                            ),
                          ),
                        ],
                      ),
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

  // ── Field helpers (sama dengan tambah_pengguna tapi inline di sini) ───────
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
    TextInputType type = TextInputType.text,
    void Function(String)? onChanged,
  }) => Column(
    crossAxisAlignment: CrossAxisAlignment.start,
    children: [
      TextField(
        controller: ctrl,
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
    required void Function(String?) onChanged,
  }) => Container(
    padding: const EdgeInsets.symmetric(horizontal: 12),
    decoration: BoxDecoration(
      color: const Color(0xFFF8F9FA),
      borderRadius: BorderRadius.circular(10),
      border: Border.all(color: Colors.grey.shade300),
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
  );
}

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
