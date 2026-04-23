import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../../core/services/delivery_service.dart';
import '../../models/pengiriman_model.dart';

const _statusAwalList = ['Pending', 'Processing'];

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

class TambahPengirimanPage extends StatefulWidget {
  const TambahPengirimanPage({super.key});

  @override
  State<TambahPengirimanPage> createState() => _TambahPengirimanPageState();
}

class _TambahPengirimanPageState extends State<TambahPengirimanPage> {
  static const _blue = Color(0xFF3B6FE8);

  final _searchInvoiceCtrl = TextEditingController();
  final _asalCtrl = TextEditingController(text: 'Toko Roni Juntinyuat');
  final _tujuanCtrl = TextEditingController();
  final _jumlahItemCtrl = TextEditingController();
  final _beratCtrl = TextEditingController();
  final _volumeCtrl = TextEditingController();
  final _catatanCtrl = TextEditingController();
  final _tglEstimasiCtrl = TextEditingController();

  DeliveryInvoiceOption? _selectedInvoice;
  List<DeliveryInvoiceOption> _invoiceResults = [];
  bool _showSuggestions = false;
  bool _isSearchingInvoice = false;
  bool _isSaving = false;
  String _statusAwal = 'Pending';
  DateTime? _estimasiTgl;
  Timer? _searchDebounce;

  final Map<String, String?> _errors = {};

  @override
  void dispose() {
    _searchDebounce?.cancel();
    for (final c in [
      _searchInvoiceCtrl,
      _asalCtrl,
      _tujuanCtrl,
      _jumlahItemCtrl,
      _beratCtrl,
      _volumeCtrl,
      _catatanCtrl,
      _tglEstimasiCtrl,
    ]) {
      c.dispose();
    }
    super.dispose();
  }

  List<DeliveryInvoiceOption> get _suggestions => _invoiceResults;

  Future<void> _searchInvoices(String query) async {
    final clean = query.trim();
    if (clean.isEmpty) {
      if (!mounted) return;
      setState(() {
        _invoiceResults = [];
        _isSearchingInvoice = false;
      });
      return;
    }

    setState(() => _isSearchingInvoice = true);
    try {
      final results = await DeliveryService.searchTransactions(
        search: clean,
        perPage: 30,
      );
      if (!mounted) return;
      setState(() {
        _invoiceResults = results;
        _isSearchingInvoice = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() {
        _invoiceResults = [];
        _isSearchingInvoice = false;
      });
    }
  }

  void _onInvoiceChanged(String value) {
    setState(() {
      _showSuggestions = value.trim().isNotEmpty;
      if (_selectedInvoice != null &&
          value !=
              '${_selectedInvoice!.invoice} - ${_selectedInvoice!.customer}') {
        _selectedInvoice = null;
      }
    });

    _searchDebounce?.cancel();
    _searchDebounce = Timer(
      const Duration(milliseconds: 350),
      () => _searchInvoices(value),
    );
  }

  void _selectInvoice(DeliveryInvoiceOption inv) {
    setState(() {
      _selectedInvoice = inv;
      _searchInvoiceCtrl.text = '${inv.invoice} - ${inv.customer}';
      _jumlahItemCtrl.text = inv.jumlahItem.toString();
      if (_tujuanCtrl.text.trim().isEmpty && inv.tujuanDefault.isNotEmpty) {
        _tujuanCtrl.text = inv.tujuanDefault;
      }
      _showSuggestions = false;
      _errors.remove('invoice');
    });
  }

  Future<void> _pickEstimasi() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: _estimasiTgl ?? DateTime.now().add(const Duration(days: 1)),
      firstDate: DateTime.now(),
      lastDate: DateTime.now().add(const Duration(days: 90)),
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

    if (picked != null && mounted) {
      setState(() {
        _estimasiTgl = picked;
        _tglEstimasiCtrl.text =
            '${picked.day.toString().padLeft(2, '0')}/${picked.month.toString().padLeft(2, '0')}/${picked.year}';
      });
    }
  }

  bool _validate() {
    final e = <String, String?>{};
    if (_selectedInvoice == null) {
      e['invoice'] = 'Pilih transaksi terlebih dahulu';
    }
    if (_tujuanCtrl.text.trim().isEmpty) {
      e['tujuan'] = 'Tujuan pengiriman wajib diisi';
    }

    setState(() {
      _errors
        ..clear()
        ..addAll(e);
    });
    return e.isEmpty;
  }

  Future<void> _submitCreate() async {
    if (!_validate()) {
      _snack('Harap isi semua field yang wajib (*)', Colors.red);
      return;
    }

    if (_isSaving) return;
    setState(() => _isSaving = true);

    try {
      final itemCount =
          int.tryParse(_jumlahItemCtrl.text.trim()) ??
          _selectedInvoice!.jumlahItem;
      final totalWeight = _parseNullableDouble(_beratCtrl.text);
      final totalVolume = _parseNullableDouble(_volumeCtrl.text);
      final statusApi = _statusAwal.toLowerCase() == 'processing'
          ? 'processing'
          : 'pending';

      final estimated = _estimasiTgl == null
          ? null
          : DateTime(
              _estimasiTgl!.year,
              _estimasiTgl!.month,
              _estimasiTgl!.day,
              23,
              59,
            );

      final created = await DeliveryService.createDelivery(
        transactionId: _selectedInvoice!.id,
        origin: _asalCtrl.text.trim(),
        destination: _tujuanCtrl.text.trim(),
        totalItems: itemCount <= 0 ? 1 : itemCount,
        totalWeight: totalWeight,
        totalVolume: totalVolume,
        estimatedDeliveryTime: estimated,
        statusApi: statusApi,
        notes: _catatanCtrl.text.trim(),
      );

      if (!mounted) return;
      Navigator.pop(context); // close confirm dialog
      Navigator.pop(
        context,
        'Pengiriman ${created.kodePengiriman.isEmpty ? _selectedInvoice!.invoice : created.kodePengiriman} berhasil dibuat',
      );
    } catch (e) {
      if (!mounted) return;
      Navigator.pop(context); // close confirm dialog
      _snack(
        e.toString().replaceFirst('Exception: ', ''),
        const Color(0xFFE53E3E),
      );
    } finally {
      if (mounted) setState(() => _isSaving = false);
    }
  }

  void _simpan() {
    if (!_validate()) {
      _snack('Harap isi semua field yang wajib (*)', Colors.red);
      return;
    }

    showDialog(
      context: context,
      barrierDismissible: !_isSaving,
      builder: (_) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        contentPadding: const EdgeInsets.fromLTRB(24, 20, 24, 0),
        actionsPadding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 60,
              height: 60,
              decoration: BoxDecoration(
                color: _blue.withOpacity(0.08),
                shape: BoxShape.circle,
              ),
              child: const Icon(
                Icons.local_shipping_rounded,
                color: _blue,
                size: 28,
              ),
            ),
            const SizedBox(height: 14),
            const Text(
              'Simpan Pengiriman?',
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.bold,
                color: Color(0xFF2D3748),
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'Pengiriman untuk ${_selectedInvoice!.invoice}\nakan dibuat dengan status $_statusAwal.',
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
                  onPressed: _isSaving ? null : () => Navigator.pop(context),
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
                  onPressed: _isSaving ? null : _submitCreate,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: _blue,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 11),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(10),
                    ),
                    elevation: 0,
                  ),
                  child: _isSaving
                      ? const SizedBox(
                          width: 16,
                          height: 16,
                          child: CircularProgressIndicator(
                            strokeWidth: 2,
                            color: Colors.white,
                          ),
                        )
                      : const Text(
                          'Simpan',
                          style: TextStyle(fontWeight: FontWeight.w600),
                        ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  void _batal() {
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
              width: 60,
              height: 60,
              decoration: BoxDecoration(
                color: Colors.orange.withOpacity(0.08),
                shape: BoxShape.circle,
              ),
              child: const Icon(
                Icons.refresh_rounded,
                color: Colors.orange,
                size: 28,
              ),
            ),
            const SizedBox(height: 14),
            const Text(
              'Batalkan Form?',
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.bold,
                color: Color(0xFF2D3748),
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'Data yang sudah diisi akan hilang.',
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
                    'Tidak',
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
                    Navigator.pop(context);
                  },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.orange,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 11),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(10),
                    ),
                    elevation: 0,
                  ),
                  child: const Text(
                    'Ya, Batalkan',
                    style: TextStyle(fontWeight: FontWeight.w600),
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  void _snack(String msg, Color color) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(msg),
        backgroundColor: color,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
      ),
    );
  }

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
          'Tambah Pengiriman',
          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18),
        ),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new_rounded),
          onPressed: _batal,
        ),
      ),
      body: GestureDetector(
        onTap: () => setState(() => _showSuggestions = false),
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(16),
          child: Column(
            children: [_buildFormCard(), const SizedBox(height: 24)],
          ),
        ),
      ),
    );
  }

  Widget _buildFormCard() => Container(
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
        Container(
          width: double.infinity,
          padding: const EdgeInsets.fromLTRB(20, 16, 20, 16),
          decoration: const BoxDecoration(
            color: _blue,
            borderRadius: BorderRadius.vertical(top: Radius.circular(16)),
          ),
          child: const Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                'Form Tambah Pengiriman',
                style: TextStyle(
                  color: Colors.white,
                  fontSize: 17,
                  fontWeight: FontWeight.bold,
                ),
              ),
              SizedBox(height: 3),
              Text(
                'Isi detail pengiriman baru',
                style: TextStyle(color: Colors.white70, fontSize: 12),
              ),
            ],
          ),
        ),
        Container(
          padding: const EdgeInsets.fromLTRB(20, 20, 20, 24),
          decoration: const BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.vertical(bottom: Radius.circular(16)),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              _lbl('Cari Transaksi (Invoice / Customer)', req: true),
              _buildSearchInvoice(),
              if (_errors['invoice'] != null) _errMsg(_errors['invoice']!),
              const SizedBox(height: 4),
              if (_selectedInvoice != null) ...[
                const SizedBox(height: 6),
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: const Color(0xFFF0F4FF),
                    borderRadius: BorderRadius.circular(10),
                    border: Border.all(color: const Color(0xFFBEE3F8)),
                  ),
                  child: Column(
                    children: [
                      _previewRow('Invoice', _selectedInvoice!.invoice),
                      _previewRow('Customer', _selectedInvoice!.customer),
                      _previewRow(
                        'Jumlah Item',
                        '${_selectedInvoice!.jumlahItem} item',
                      ),
                      _previewRow('Total', _rp(_selectedInvoice!.totalBelanja)),
                    ],
                  ),
                ),
              ],
              const SizedBox(height: 16),
              _lbl('Asal Pengiriman'),
              _fld(_asalCtrl, 'Asal pengiriman', enabled: false),
              const SizedBox(height: 14),
              _lbl('Tujuan Pengiriman', req: true),
              _fld(
                _tujuanCtrl,
                'Masukan alamat tujuan',
                maxLines: 2,
                error: _errors['tujuan'],
                onChanged: (_) => setState(() => _errors.remove('tujuan')),
              ),
              const SizedBox(height: 14),
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        _lbl('Jumlah Item (barang)'),
                        _fld(
                          _jumlahItemCtrl,
                          '0',
                          type: TextInputType.number,
                          formatters: [FilteringTextInputFormatter.digitsOnly],
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        _lbl('Total Berat (kg)'),
                        _fld(
                          _beratCtrl,
                          '0.0',
                          type: const TextInputType.numberWithOptions(
                            decimal: true,
                          ),
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
                        _lbl('Total Volume (m3)'),
                        _fld(
                          _volumeCtrl,
                          '0.00',
                          type: const TextInputType.numberWithOptions(
                            decimal: true,
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
                        _lbl('Estimasi Tgl Sampai'),
                        TextField(
                          controller: _tglEstimasiCtrl,
                          readOnly: true,
                          style: const TextStyle(fontSize: 13),
                          decoration: _dec('Pilih tanggal').copyWith(
                            suffixIcon: IconButton(
                              onPressed: _pickEstimasi,
                              icon: Icon(
                                Icons.calendar_month_rounded,
                                size: 18,
                                color: Colors.grey.shade500,
                              ),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 14),
              _lbl('Status Awal Pengiriman'),
              Wrap(
                spacing: 10,
                children: _statusAwalList.map((s) {
                  final active = _statusAwal == s;
                  return GestureDetector(
                    onTap: () => setState(() => _statusAwal = s),
                    child: Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 16,
                        vertical: 9,
                      ),
                      decoration: BoxDecoration(
                        color: active ? _blue : Colors.grey.shade100,
                        borderRadius: BorderRadius.circular(24),
                        border: Border.all(
                          color: active ? _blue : Colors.grey.shade300,
                        ),
                      ),
                      child: Text(
                        s,
                        style: TextStyle(
                          fontSize: 13,
                          fontWeight: active
                              ? FontWeight.w600
                              : FontWeight.normal,
                          color: active ? Colors.white : Colors.grey.shade600,
                        ),
                      ),
                    ),
                  );
                }).toList(),
              ),
              const SizedBox(height: 14),
              _lbl('Catatan Pengiriman'),
              _fld(
                _catatanCtrl,
                'Tambahkan catatan untuk kurir atau logistik...',
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
                      onPressed: _isSaving ? null : _simpan,
                      icon: _isSaving
                          ? const SizedBox(
                              width: 16,
                              height: 16,
                              child: CircularProgressIndicator(
                                strokeWidth: 2,
                                color: Colors.white,
                              ),
                            )
                          : const Icon(Icons.save_rounded, size: 18),
                      label: Text(
                        _isSaving ? 'Menyimpan...' : 'Simpan Pengiriman',
                        style: const TextStyle(
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

  Widget _buildSearchInvoice() => Column(
    children: [
      TextField(
        controller: _searchInvoiceCtrl,
        style: const TextStyle(fontSize: 13),
        onChanged: _onInvoiceChanged,
        decoration: _dec('Cari nomor invoice atau nama customer').copyWith(
          prefixIcon: Icon(
            Icons.search_rounded,
            size: 18,
            color: Colors.grey.shade500,
          ),
          border: OutlineInputBorder(
            borderRadius: BorderRadius.vertical(
              top: const Radius.circular(10),
              bottom: Radius.circular(_showSuggestions ? 0 : 10),
            ),
            borderSide: BorderSide(color: Colors.grey.shade300),
          ),
          enabledBorder: OutlineInputBorder(
            borderRadius: BorderRadius.vertical(
              top: const Radius.circular(10),
              bottom: Radius.circular(_showSuggestions ? 0 : 10),
            ),
            borderSide: BorderSide(color: Colors.grey.shade300),
          ),
          focusedBorder: OutlineInputBorder(
            borderRadius: BorderRadius.vertical(
              top: const Radius.circular(10),
              bottom: Radius.circular(_showSuggestions ? 0 : 10),
            ),
            borderSide: const BorderSide(color: _blue, width: 1.5),
          ),
        ),
      ),
      if (_showSuggestions)
        Container(
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: const BorderRadius.vertical(
              bottom: Radius.circular(10),
            ),
            border: Border.all(color: const Color(0xFF3B6FE8)),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withOpacity(0.08),
                blurRadius: 8,
                offset: const Offset(0, 4),
              ),
            ],
          ),
          child: _isSearchingInvoice
              ? const Padding(
                  padding: EdgeInsets.all(14),
                  child: Center(
                    child: SizedBox(
                      width: 18,
                      height: 18,
                      child: CircularProgressIndicator(strokeWidth: 2),
                    ),
                  ),
                )
              : _suggestions.isEmpty
              ? Padding(
                  padding: const EdgeInsets.all(14),
                  child: Row(
                    children: [
                      Icon(
                        Icons.search_off_rounded,
                        size: 16,
                        color: Colors.grey.shade400,
                      ),
                      const SizedBox(width: 8),
                      Text(
                        'Transaksi tidak ditemukan',
                        style: TextStyle(
                          fontSize: 13,
                          color: Colors.grey.shade500,
                        ),
                      ),
                    ],
                  ),
                )
              : Column(
                  children: _suggestions
                      .map(
                        (inv) => InkWell(
                          onTap: () => _selectInvoice(inv),
                          child: Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 14,
                              vertical: 10,
                            ),
                            decoration: BoxDecoration(
                              border: Border(
                                top: BorderSide(color: Colors.grey.shade100),
                              ),
                            ),
                            child: Row(
                              children: [
                                Container(
                                  padding: const EdgeInsets.all(7),
                                  decoration: BoxDecoration(
                                    color: const Color(
                                      0xFF3B6FE8,
                                    ).withOpacity(0.10),
                                    borderRadius: BorderRadius.circular(8),
                                  ),
                                  child: const Icon(
                                    Icons.receipt_rounded,
                                    color: Color(0xFF3B6FE8),
                                    size: 16,
                                  ),
                                ),
                                const SizedBox(width: 10),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment:
                                        CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        inv.invoice,
                                        style: const TextStyle(
                                          fontSize: 12,
                                          fontWeight: FontWeight.w600,
                                          color: Color(0xFF2D3748),
                                        ),
                                      ),
                                      Text(
                                        '${inv.customer}  -  ${_rp(inv.totalBelanja)}',
                                        style: TextStyle(
                                          fontSize: 11,
                                          color: Colors.grey.shade500,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                                Text(
                                  '${inv.jumlahItem} item',
                                  style: TextStyle(
                                    fontSize: 11,
                                    color: Colors.grey.shade500,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      )
                      .toList(),
                ),
        ),
    ],
  );

  Widget _lbl(String text, {bool req = false}) => Padding(
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
    List<TextInputFormatter>? formatters,
    bool enabled = true,
    String? error,
    void Function(String)? onChanged,
  }) => Column(
    crossAxisAlignment: CrossAxisAlignment.start,
    children: [
      TextField(
        controller: ctrl,
        maxLines: maxLines,
        keyboardType: type,
        inputFormatters: formatters,
        enabled: enabled,
        onChanged: onChanged,
        style: const TextStyle(fontSize: 13),
        decoration: _dec(hint).copyWith(
          fillColor: enabled ? const Color(0xFFF8F9FA) : Colors.grey.shade100,
        ),
      ),
      if (error != null) _errMsg(error),
    ],
  );

  Widget _errMsg(String msg) => Padding(
    padding: const EdgeInsets.only(top: 4),
    child: Row(
      children: [
        const Icon(Icons.error_outline_rounded, color: Colors.red, size: 13),
        const SizedBox(width: 4),
        Text(msg, style: const TextStyle(color: Colors.red, fontSize: 11)),
      ],
    ),
  );

  Widget _previewRow(String label, String value) => Padding(
    padding: const EdgeInsets.symmetric(vertical: 3),
    child: Row(
      children: [
        Expanded(
          child: Text(
            label,
            style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
          ),
        ),
        Text(
          value,
          style: const TextStyle(
            fontSize: 12,
            fontWeight: FontWeight.w600,
            color: Color(0xFF2D3748),
          ),
        ),
      ],
    ),
  );

  double? _parseNullableDouble(String raw) {
    final clean = raw.trim();
    if (clean.isEmpty) return null;
    return double.tryParse(clean.replaceAll(',', '.'));
  }
}
