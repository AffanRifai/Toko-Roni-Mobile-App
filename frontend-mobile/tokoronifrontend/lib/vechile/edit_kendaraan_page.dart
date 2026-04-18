// lib/delivery/edit_kendaraan_page.dart
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'manajemen_kendaraan_page.dart';

class EditKendaraanPage extends StatefulWidget {
  final KendaraanItem kendaraan;
  const EditKendaraanPage({super.key, required this.kendaraan});

  @override
  State<EditKendaraanPage> createState() => _EditKendaraanPageState();
}

class _EditKendaraanPageState extends State<EditKendaraanPage> {
  static const _blue = Color(0xFF3B6FE8);

  late TextEditingController _namaCtrl;
  late TextEditingController _platCtrl;
  late TextEditingController _beratCtrl;
  late TextEditingController _volumeCtrl;
  late TextEditingController _maintenanceCtrl;
  late TextEditingController _catatanCtrl;
  late String _jenis;
  late String _status;
  DateTime? _maintenanceTgl;
  final Map<String, String?> _errors = {};

  @override
  void initState() {
    super.initState();
    final k = widget.kendaraan;
    _namaCtrl = TextEditingController(text: k.nama);
    _platCtrl = TextEditingController(text: k.platNomor);
    _beratCtrl = TextEditingController(
      text: k.kapasitasBerat > 0 ? k.kapasitasBerat.toString() : '',
    );
    _volumeCtrl = TextEditingController(
      text: k.kapasitasVolume > 0 ? k.kapasitasVolume.toString() : '',
    );
    _maintenanceCtrl = TextEditingController(
      text: k.tanggalMaintenance == '-' ? '' : k.tanggalMaintenance,
    );
    _catatanCtrl = TextEditingController(text: k.catatan);
    _jenis = k.jenis;
    _status = k.status;
  }

  @override
  void dispose() {
    for (final c in [
      _namaCtrl,
      _platCtrl,
      _beratCtrl,
      _volumeCtrl,
      _maintenanceCtrl,
      _catatanCtrl,
    ])
      c.dispose();
    super.dispose();
  }

  Future<void> _pickDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: _maintenanceTgl ?? DateTime.now(),
      firstDate: DateTime(2020),
      lastDate: DateTime.now().add(const Duration(days: 365)),
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
        _maintenanceTgl = picked;
        _maintenanceCtrl.text =
            '${picked.day.toString().padLeft(2, '0')}/'
            '${picked.month.toString().padLeft(2, '0')}/'
            '${picked.year}';
      });
  }

  bool _validate() {
    final e = <String, String?>{};
    if (_namaCtrl.text.trim().isEmpty) e['nama'] = 'Nama kendaraan wajib diisi';
    if (_platCtrl.text.trim().isEmpty) e['plat'] = 'Plat nomor wajib diisi';
    setState(() {
      _errors
        ..clear()
        ..addAll(e);
    });
    return e.isEmpty;
  }

  void _simpan() {
    if (!_validate()) {
      _snack('Harap isi field yang wajib (*)', Colors.red);
      return;
    }
    showDialog(
      context: context,
      builder: (_) => _ConfirmDialog(
        icon: Icons.save_rounded,
        iconColor: _blue,
        title: 'Simpan Perubahan?',
        message:
            '${_namaCtrl.text.trim()} (${_platCtrl.text.trim().toUpperCase()}) akan diperbarui.',
        confirmLabel: 'Simpan',
        confirmColor: _blue,
        onConfirm: () {
          final result = KendaraanItem(
            id: widget.kendaraan.id,
            nama: _namaCtrl.text.trim(),
            platNomor: _platCtrl.text.trim().toUpperCase(),
            jenis: _jenis,
            status: _status,
            tanggalMaintenance: _maintenanceCtrl.text.isEmpty
                ? '-'
                : _maintenanceCtrl.text,
            kapasitasBerat: double.tryParse(_beratCtrl.text) ?? 0,
            kapasitasVolume: double.tryParse(_volumeCtrl.text) ?? 0,
            catatan: _catatanCtrl.text.trim(),
          );
          Navigator.pop(context);
          Navigator.pop(context, result);
        },
      ),
    );
  }

  void _batal() => showDialog(
    context: context,
    builder: (_) => _ConfirmDialog(
      icon: Icons.refresh_rounded,
      iconColor: Colors.orange,
      title: 'Batalkan Perubahan?',
      message: 'Perubahan yang belum disimpan akan hilang.',
      confirmLabel: 'Ya, Batalkan',
      confirmColor: Colors.orange,
      onConfirm: () {
        Navigator.pop(context);
        Navigator.pop(context);
      },
    ),
  );

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
          'Edit Kendaraan',
          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18),
        ),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new_rounded),
          onPressed: _batal,
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(children: [_buildForm(), const SizedBox(height: 32)]),
      ),
    );
  }

  Widget _buildForm() => Container(
    decoration: BoxDecoration(
      borderRadius: BorderRadius.circular(16),
      boxShadow: [
        BoxShadow(
          color: Colors.black.withOpacity(0.07),
          blurRadius: 12,
          offset: const Offset(0, 4),
        ),
      ],
    ),
    child: Column(
      children: [
        // Header — tampil ID badge
        Container(
          width: double.infinity,
          padding: const EdgeInsets.fromLTRB(20, 16, 20, 16),
          decoration: const BoxDecoration(
            color: _blue,
            borderRadius: BorderRadius.vertical(top: Radius.circular(16)),
          ),
          child: Row(
            children: [
              const Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Form Edit Kendaraan',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 17,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    SizedBox(height: 3),
                    Text(
                      'Perbarui data kendaraan',
                      style: TextStyle(color: Colors.white70, fontSize: 12),
                    ),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 10,
                  vertical: 5,
                ),
                decoration: BoxDecoration(
                  color: Colors.white.withOpacity(0.2),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Text(
                  widget.kendaraan.id,
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 11,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ),
            ],
          ),
        ),
        // Body — sama persis dengan tambah, tapi pre-fill
        Container(
          padding: const EdgeInsets.fromLTRB(20, 22, 20, 24),
          decoration: const BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.vertical(bottom: Radius.circular(16)),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              _lbl('Nama Kendaraan', req: true),
              _fld(
                _namaCtrl,
                'Contoh: Honda Beat, Mitsubishi L300',
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
                        _lbl('Plat Nomor', req: true),
                        _fld(
                          _platCtrl,
                          'B 1234 XYZ',
                          inputFormatters: [_UpperCaseFormatter()],
                          error: _errors['plat'],
                          onChanged: (_) =>
                              setState(() => _errors.remove('plat')),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        _lbl('Jenis Kendaraan'),
                        _dropField(
                          _jenis,
                          jenisKendaraanList,
                          (v) => setState(() => _jenis = v!),
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
                        _lbl('Kapasitas Berat (kg)'),
                        _fld(
                          _beratCtrl,
                          '0',
                          type: const TextInputType.numberWithOptions(
                            decimal: true,
                          ),
                          inputFormatters: [
                            FilteringTextInputFormatter.allow(
                              RegExp(r'^\d*\.?\d*'),
                            ),
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
                        _lbl('Kapasitas Volume (m³)'),
                        _fld(
                          _volumeCtrl,
                          '0.0',
                          type: const TextInputType.numberWithOptions(
                            decimal: true,
                          ),
                          inputFormatters: [
                            FilteringTextInputFormatter.allow(
                              RegExp(r'^\d*\.?\d*'),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 14),

              _lbl('Terakhir Maintenance'),
              TextField(
                controller: _maintenanceCtrl,
                readOnly: true,
                style: const TextStyle(fontSize: 13),
                decoration: _dec('Pilih tanggal maintenance').copyWith(
                  suffixIcon: IconButton(
                    onPressed: _pickDate,
                    icon: Icon(
                      Icons.calendar_month_rounded,
                      size: 18,
                      color: Colors.grey.shade500,
                    ),
                  ),
                ),
              ),
              const SizedBox(height: 14),

              _lbl('Status Kendaraan'),
              ...statusKendaraanList.map((s) {
                final sel = _status == s;
                final c = kendaraanStatusColor(s);
                return GestureDetector(
                  onTap: () => setState(() => _status = s),
                  child: Container(
                    margin: const EdgeInsets.only(bottom: 8),
                    padding: const EdgeInsets.symmetric(
                      horizontal: 14,
                      vertical: 11,
                    ),
                    decoration: BoxDecoration(
                      color: sel
                          ? c.withOpacity(0.07)
                          : const Color(0xFFF8F9FA),
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(
                        color: sel ? c : Colors.grey.shade200,
                        width: sel ? 1.5 : 1,
                      ),
                    ),
                    child: Row(
                      children: [
                        Container(
                          width: 18,
                          height: 18,
                          decoration: BoxDecoration(
                            shape: BoxShape.circle,
                            border: Border.all(
                              color: sel ? c : Colors.grey.shade400,
                              width: 2,
                            ),
                            color: sel ? c : Colors.transparent,
                          ),
                          child: sel
                              ? const Icon(
                                  Icons.check_rounded,
                                  color: Colors.white,
                                  size: 11,
                                )
                              : null,
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Text(
                            s,
                            style: TextStyle(
                              fontSize: 13,
                              fontWeight: sel
                                  ? FontWeight.w600
                                  : FontWeight.normal,
                              color: sel ? c : const Color(0xFF2D3748),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                );
              }),
              const SizedBox(height: 14),

              _lbl('Catatan'),
              _fld(
                _catatanCtrl,
                'Catatan tambahan tentang kendaraan ini...',
                maxLines: 3,
              ),
              const SizedBox(height: 24),

              Row(
                children: [
                  Expanded(
                    child: OutlinedButton.icon(
                      onPressed: _batal,
                      icon: const Icon(Icons.close_rounded, size: 16),
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
                    flex: 2,
                    child: ElevatedButton.icon(
                      onPressed: _simpan,
                      icon: const Icon(Icons.save_rounded, size: 18),
                      label: const Text(
                        'Simpan Perubahan',
                        style: TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: _blue,
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                        elevation: 0,
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
            fontSize: 13,
            fontWeight: FontWeight.w500,
            color: Color(0xFF4A5568),
          ),
        ),
        if (req)
          const Text(' *', style: TextStyle(color: Colors.red, fontSize: 13)),
      ],
    ),
  );

  InputDecoration _dec(String hint) => InputDecoration(
    hintText: hint,
    hintStyle: TextStyle(color: Colors.grey.shade400, fontSize: 13),
    filled: true,
    fillColor: const Color(0xFFF8F9FA),
    contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
    border: OutlineInputBorder(
      borderRadius: BorderRadius.circular(10),
      borderSide: BorderSide(color: Colors.grey.shade300),
    ),
    enabledBorder: OutlineInputBorder(
      borderRadius: BorderRadius.circular(10),
      borderSide: BorderSide(color: Colors.grey.shade300),
    ),
    focusedBorder: OutlineInputBorder(
      borderRadius: BorderRadius.circular(10),
      borderSide: const BorderSide(color: _blue, width: 1.5),
    ),
  );

  Widget _fld(
    TextEditingController ctrl,
    String hint, {
    int maxLines = 1,
    TextInputType type = TextInputType.text,
    List<TextInputFormatter>? inputFormatters,
    String? error,
    void Function(String)? onChanged,
  }) => Column(
    crossAxisAlignment: CrossAxisAlignment.start,
    children: [
      TextField(
        controller: ctrl,
        maxLines: maxLines,
        keyboardType: type,
        inputFormatters: inputFormatters,
        onChanged: onChanged,
        style: const TextStyle(fontSize: 13),
        decoration: _dec(hint),
      ),
      if (error != null)
        Padding(
          padding: const EdgeInsets.only(top: 4),
          child: Row(
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
        ),
    ],
  );

  Widget _dropField(
    String value,
    List<String> items,
    void Function(String?) fn,
  ) => Container(
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
        style: const TextStyle(fontSize: 13, color: Color(0xFF2D3748)),
        icon: const Icon(Icons.keyboard_arrow_down_rounded, size: 22),
        items: items
            .map((e) => DropdownMenuItem(value: e, child: Text(e)))
            .toList(),
        onChanged: fn,
      ),
    ),
  );
}

// ── Shared ────────────────────────────────────────────────────────────────────
class _UpperCaseFormatter extends TextInputFormatter {
  @override
  TextEditingValue formatEditUpdate(TextEditingValue o, TextEditingValue n) =>
      n.copyWith(text: n.text.toUpperCase());
}

class _ConfirmDialog extends StatelessWidget {
  final IconData icon;
  final Color iconColor;
  final String title, message, confirmLabel;
  final Color confirmColor;
  final VoidCallback onConfirm;
  const _ConfirmDialog({
    required this.icon,
    required this.iconColor,
    required this.title,
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
          width: 56,
          height: 56,
          decoration: BoxDecoration(
            color: iconColor.withOpacity(0.08),
            shape: BoxShape.circle,
          ),
          child: Icon(icon, color: iconColor, size: 26),
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
                backgroundColor: confirmColor,
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
