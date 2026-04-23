// lib/member/tambah_member_page.dart
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../../core/services/member_service.dart';
import '../../models/member_model.dart';

class TambahMemberPage extends StatefulWidget {
  const TambahMemberPage({super.key});
  @override
  State<TambahMemberPage> createState() => _TambahMemberPageState();
}

class _TambahMemberPageState extends State<TambahMemberPage> {
  static const _blue = Color(0xFF3B6FE8);

  final _namaCtrl = TextEditingController();
  final _emailCtrl = TextEditingController();
  final _telCtrl = TextEditingController();
  final _alamatCtrl = TextEditingController();
  final _limitCtrl = TextEditingController(text: '0');

  String? _tipe;
  bool _aktif = true;
  bool _isSubmitting = false;
  final Map<String, String?> _errors = {};

  @override
  void dispose() {
    for (final c in [
      _namaCtrl,
      _emailCtrl,
      _telCtrl,
      _alamatCtrl,
      _limitCtrl,
    ]) {
      c.dispose();
    }
    super.dispose();
  }

  void _clearErr(String k) => setState(() => _errors.remove(k));

  bool _validate() {
    final e = <String, String?>{};
    if (_namaCtrl.text.trim().isEmpty) e['nama'] = 'Nama lengkap wajib diisi';
    if (_tipe == null || _tipe!.trim().isEmpty) {
      e['tipe'] = 'Tipe member wajib dipilih';
    }
    if (_limitCtrl.text.trim().isEmpty || _limitCtrl.text.trim() == '0') {
      e['limit'] = 'Limit kredit wajib diisi';
    }

    final email = _emailCtrl.text.trim();
    if (email.isNotEmpty && !RegExp(r'^[\w.]+@[\w.]+\.\w+$').hasMatch(email)) {
      e['email'] = 'Format email tidak valid';
    }

    setState(
      () => _errors
        ..clear()
        ..addAll(e),
    );
    return e.isEmpty;
  }

  void _showBatalDialog() => showDialog(
    context: context,
    builder: (_) => _ConfirmDialog(
      title: 'Batalkan Form?',
      icon: Icons.refresh_rounded,
      iconColor: Colors.orange,
      message: 'Data yang sudah diisi akan hilang.',
      confirmLabel: 'Ya, Batalkan',
      confirmColor: Colors.orange,
      onConfirm: () {
        Navigator.pop(context); // Tutup dialog
        Navigator.pop(context); // Kembali ke daftar member
      },
    ),
  );

  void _showSimpanDialog() {
    if (!_validate()) {
      _errSnack();
      return;
    }
    showDialog(
      context: context,
      builder: (_) => _ConfirmDialog(
        title: 'Simpan Member',
        icon: Icons.person_add_rounded,
        iconColor: _blue,
        message: 'Apakah data member "${_namaCtrl.text.trim()}" sudah benar?',
        confirmLabel: 'Ya, Simpan',
        confirmColor: _blue,
        onConfirm: _isSubmitting
            ? () {}
            : () async {
                Navigator.pop(context); // Tutup dialog
                await _submitCreate();
              },
      ),
    );
  }

  Future<void> _submitCreate() async {
    if (_isSubmitting) return;
    setState(() => _isSubmitting = true);
    try {
      final created = await MemberService.createMember(
        nama: _namaCtrl.text.trim(),
        tipeMember: tipeMemberApiFromLabel(_tipe ?? ''),
        limitKredit: double.tryParse(_limitCtrl.text.trim()) ?? 0,
        isActive: _aktif,
        email: _emailCtrl.text.trim(),
        noTelepon: _telCtrl.text.trim(),
        alamat: _alamatCtrl.text.trim(),
      );

      if (!mounted) return;
      Navigator.pop(context, 'Member "${created.nama}" berhasil ditambahkan!');
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

  void _errSnack() => ScaffoldMessenger.of(context).showSnackBar(
    SnackBar(
      content: const Text('Harap isi semua field yang wajib diisi (*)'),
      backgroundColor: const Color(0xFFE53E3E),
      behavior: SnackBarBehavior.floating,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
    ),
  );

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
          'Tambah Member',
          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18),
        ),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new_rounded),
          onPressed: () => Navigator.pop(context),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(children: [_formCard(), const SizedBox(height: 24)]),
      ),
    );
  }

  Widget _formCard() => Container(
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
            color: Color(0xFF3B6FE8),
            borderRadius: BorderRadius.vertical(top: Radius.circular(16)),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text(
                'Form Tambah Member',
                style: TextStyle(
                  color: Colors.white,
                  fontSize: 17,
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(height: 4),
              const Text(
                'Tambahkan member baru',
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
            borderRadius: BorderRadius.vertical(bottom: Radius.circular(16)),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              _lbl('Nama Lengkap', req: true),
              _fld(
                _namaCtrl,
                'masukan nama',
                error: _errors['nama'],
                onChanged: (_) => _clearErr('nama'),
              ),
              const SizedBox(height: 16),

              _lbl('Email'),
              _fld(
                _emailCtrl,
                'masukan email',
                type: TextInputType.emailAddress,
                error: _errors['email'],
                onChanged: (_) => _clearErr('email'),
              ),
              const SizedBox(height: 16),

              _lbl('No Telepon'),
              _fld(
                _telCtrl,
                'masukan nomer telepon',
                type: TextInputType.phone,
              ),
              const SizedBox(height: 16),

              _lbl('Alamat'),
              _fld(_alamatCtrl, 'Tambahkan alamat', maxLines: 3),
              const SizedBox(height: 16),

              // Tipe + Limit (2 kolom)
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        _lbl('Tipe Member'),
                        _drop(
                          _tipe,
                          'Pilih tipe',
                          tipeMemberList,
                          error: _errors['tipe'],
                          onChanged: (v) => setState(() {
                            _tipe = v;
                            _clearErr('tipe');
                          }),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        _lbl('Limit Kredit (Rp)', req: true),
                        _fld(
                          _limitCtrl,
                          'Rp 0',
                          type: TextInputType.number,
                          formatters: [FilteringTextInputFormatter.digitsOnly],
                          error: _errors['limit'],
                          onChanged: (_) => _clearErr('limit'),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 20),

              // Status toggle
              _lbl('Aktifkan Member'),
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 14,
                  vertical: 10,
                ),
                decoration: BoxDecoration(
                  color: const Color(0xFFF8F9FA),
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(color: Colors.grey.shade300),
                ),
                child: Row(
                  children: [
                    Switch(
                      value: _aktif,
                      onChanged: (v) => setState(() => _aktif = v),
                      activeColor: Colors.white,
                      activeTrackColor: const Color(0xFF48BB78),
                      inactiveTrackColor: Colors.grey.shade400,
                    ),
                    const SizedBox(width: 8),
                    Text(
                      'aktif',
                      style: TextStyle(
                        fontSize: 14,
                        color: _aktif ? const Color(0xFF48BB78) : Colors.grey,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 28),

              // Buttons
              Row(
                children: [
                  Expanded(
                    child: OutlinedButton.icon(
                      onPressed: _showBatalDialog,
                      icon: const Icon(Icons.refresh_rounded, size: 16),
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
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: ElevatedButton(
                      onPressed: _isSubmitting ? null : _showSimpanDialog,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF3B6FE8),
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                        elevation: 0,
                      ),
                      child: Text(
                        _isSubmitting ? 'Menyimpan...' : 'Simpan',
                        style: TextStyle(
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
  );

  Widget _lbl(String t, {bool req = false}) => Padding(
    padding: const EdgeInsets.only(bottom: 7),
    child: Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Text(
          t,
          style: const TextStyle(
            fontSize: 14,
            fontWeight: FontWeight.w600,
            color: Color(0xFF2D3748),
          ),
        ),
        if (req) ...[
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

  Widget _fld(
    TextEditingController ctrl,
    String hint, {
    String? error,
    int maxLines = 1,
    TextInputType type = TextInputType.text,
    List<TextInputFormatter>? formatters,
    void Function(String)? onChanged,
  }) => Column(
    crossAxisAlignment: CrossAxisAlignment.start,
    children: [
      TextField(
        controller: ctrl,
        maxLines: maxLines,
        keyboardType: type,
        inputFormatters: formatters,
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
              color: error != null ? Colors.red : const Color(0xFF3B6FE8),
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

  Widget _drop(
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
}

// ── Reusable confirm dialog ────────────────────────────────────────────────────
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
            color: iconColor.withOpacity(0.10),
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
                side: BorderSide(color: Colors.grey.shade300),
                padding: const EdgeInsets.symmetric(vertical: 12),
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
      ),
    ],
  );
}
