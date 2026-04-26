import 'package:flutter/material.dart';
import '../../core/access/role_access.dart';
import '../../core/state/app_state.dart';
import '../../core/services/delivery_service.dart';
import '../../models/pengiriman_model.dart';

class _TimelineStep {
  final String label;
  final IconData icon;
  final String? sublabel1;
  final String? sublabel2;
  final bool isDone;
  final bool isActive;
  final bool isPending;

  const _TimelineStep({
    required this.label,
    required this.icon,
    this.sublabel1,
    this.sublabel2,
    this.isDone = false,
    this.isActive = false,
    this.isPending = false,
  });
}

class DetailPengirimanPage extends StatefulWidget {
  final PengirimanItem pengiriman;
  const DetailPengirimanPage({super.key, required this.pengiriman});

  @override
  State<DetailPengirimanPage> createState() => _DetailPengirimanPageState();
}

class _DetailPengirimanPageState extends State<DetailPengirimanPage> {
  static const _blue = Color(0xFF3B6FE8);
  static const _green = Color(0xFF38A169);

  late PengirimanItem _p;
  List<DeliveryDriverOption> _drivers = [];
  List<DeliveryVehicleOption> _vehicles = [];

  bool _isLoading = true;
  bool _isSubmitting = false;
  bool _hasChanges = false;
  String _errorMessage = '';
  bool get _isStaffLogistik =>
      RoleAccess.isStaffLogistik(AppState.instance.userRole.value);

  @override
  void initState() {
    super.initState();
    _p = widget.pengiriman;
    _loadDetailContext();
  }

  Future<void> _loadDetailContext() async {
    if (!mounted) return;
    setState(() {
      _isLoading = true;
      _errorMessage = '';
    });

    try {
      final detail = await DeliveryService.getDeliveryDetail(deliveryId: _p.id);
      List<DeliveryDriverOption> drivers = [];
      List<DeliveryVehicleOption> vehicles = [];

      try {
        drivers = await DeliveryService.getAvailableDrivers();
      } catch (_) {}
      try {
        vehicles = await DeliveryService.getAvailableVehicles();
      } catch (_) {}

      if (!mounted) return;
      setState(() {
        _p = detail;
        _drivers = drivers;
        _vehicles = vehicles;
        _isLoading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _isLoading = false;
        _errorMessage = e.toString().replaceFirst('Exception: ', '');
      });
    }
  }

  Future<void> _reloadDetail() async {
    try {
      final detail = await DeliveryService.getDeliveryDetail(deliveryId: _p.id);
      if (!mounted) return;
      setState(() => _p = detail);
    } catch (_) {}
  }

  void _goBack() {
    Navigator.pop(context, _hasChanges);
  }

  Future<void> _updateStatus(String statusApi, String successMessage) async {
    if (_isSubmitting) return;
    setState(() => _isSubmitting = true);
    try {
      await DeliveryService.updateStatus(
        deliveryId: _p.id,
        statusApi: statusApi,
      );
      await _reloadDetail();
      if (!mounted) return;
      setState(() {
        _isSubmitting = false;
        _hasChanges = true;
      });
      _snack(successMessage, _green);
    } catch (e) {
      if (!mounted) return;
      setState(() => _isSubmitting = false);
      _snack(
        e.toString().replaceFirst('Exception: ', ''),
        const Color(0xFFE53E3E),
      );
    }
  }

  void _batalkan() {
    showDialog(
      context: context,
      builder: (_) => _ConfirmDialog(
        icon: Icons.cancel_rounded,
        title: 'Batalkan Pengiriman?',
        message: 'Pengiriman ${_p.kodePengiriman} akan dibatalkan.',
        confirmLabel: 'Ya, Batalkan',
        confirmColor: const Color(0xFFE53E3E),
        onConfirm: () => _updateStatus('cancelled', 'Pengiriman dibatalkan'),
      ),
    );
  }

  void _selesaikan() {
    showDialog(
      context: context,
      builder: (_) => _ConfirmDialog(
        icon: Icons.check_circle_rounded,
        title: 'Selesaikan Pengiriman?',
        message: 'Tandai pengiriman ${_p.kodePengiriman} sebagai terkirim?',
        confirmLabel: 'Selesaikan',
        confirmColor: _green,
        onConfirm: () =>
            _updateStatus('delivered', 'Status diperbarui: Terkirim'),
      ),
    );
  }

  void _mulaiPengiriman() {
    _updateStatus('on_delivery', 'Status diperbarui: Dalam Perjalanan');
  }

  void _paketDiambil() {
    _updateStatus('picked_up', 'Status diperbarui: Diambil');
  }

  void _showAssignKurir() {
    if (_drivers.isEmpty) {
      _snack('Daftar kurir tidak tersedia', const Color(0xFFE53E3E));
      return;
    }

    int? selectedDriverId = _p.kurirId;
    int? selectedVehicleId = _p.kendaraanId;

    showDialog(
      context: context,
      builder: (_) => StatefulBuilder(
        builder: (ctx, setS) => AlertDialog(
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(20),
          ),
          titlePadding: const EdgeInsets.fromLTRB(20, 20, 20, 0),
          contentPadding: const EdgeInsets.fromLTRB(20, 12, 20, 0),
          actionsPadding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
          title: Row(
            children: [
              const Icon(Icons.person_add_rounded, color: _blue, size: 20),
              const SizedBox(width: 8),
              const Text(
                'Assign Kurir & Kendaraan',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
              ),
              const Spacer(),
              GestureDetector(
                onTap: () => Navigator.pop(ctx),
                child: Icon(Icons.close, size: 20, color: Colors.grey.shade400),
              ),
            ],
          ),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const SizedBox(height: 8),
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: const Color(0xFFF0F4FF),
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(color: const Color(0xFFBEE3F8)),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Row(
                      children: [
                        Icon(
                          Icons.info_outline_rounded,
                          color: _blue,
                          size: 14,
                        ),
                        SizedBox(width: 6),
                        Text(
                          'Informasi Pengiriman',
                          style: TextStyle(
                            fontSize: 12,
                            color: _blue,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    _iRow('Kode:', _p.kodePengiriman),
                    _iRow('Tujuan:', _p.tujuan),
                    _iRow('Total Item:', '${_p.totalItem} barang'),
                  ],
                ),
              ),
              const SizedBox(height: 14),
              _dropLabel('Pilih Kurir', Icons.person_rounded, true),
              const SizedBox(height: 6),
              _dropContainer(
                child: DropdownButtonHideUnderline(
                  child: DropdownButton<int>(
                    value: selectedDriverId,
                    isExpanded: true,
                    hint: Text(
                      '-- Pilih Kurir --',
                      style: TextStyle(
                        color: Colors.grey.shade400,
                        fontSize: 13,
                      ),
                    ),
                    items: _drivers
                        .map(
                          (d) => DropdownMenuItem<int>(
                            value: d.id,
                            child: Text(
                              d.phone.isEmpty
                                  ? d.name
                                  : '${d.name} (${d.phone})',
                              style: const TextStyle(fontSize: 13),
                            ),
                          ),
                        )
                        .toList(),
                    onChanged: (v) => setS(() => selectedDriverId = v),
                  ),
                ),
              ),
              const SizedBox(height: 12),
              _dropLabel(
                'Pilih Kendaraan',
                Icons.local_shipping_rounded,
                false,
              ),
              const SizedBox(height: 6),
              _dropContainer(
                child: DropdownButtonHideUnderline(
                  child: DropdownButton<int>(
                    value: selectedVehicleId,
                    isExpanded: true,
                    hint: Text(
                      _vehicles.isEmpty
                          ? 'Tidak ada kendaraan tersedia'
                          : '-- Pilih Kendaraan --',
                      style: TextStyle(
                        color: Colors.grey.shade400,
                        fontSize: 13,
                      ),
                    ),
                    items: _vehicles
                        .map(
                          (v) => DropdownMenuItem<int>(
                            value: v.id,
                            child: Text(
                              v.displayLabel,
                              style: const TextStyle(fontSize: 12),
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                        )
                        .toList(),
                    onChanged: _vehicles.isEmpty
                        ? null
                        : (v) => setS(() => selectedVehicleId = v),
                  ),
                ),
              ),
              const SizedBox(height: 8),
            ],
          ),
          actions: [
            Row(
              children: [
                Expanded(
                  child: ElevatedButton.icon(
                    onPressed: () async {
                      if (selectedDriverId == null) {
                        _snack('Pilih kurir terlebih dahulu', Colors.red);
                        return;
                      }

                      Navigator.pop(ctx);
                      setState(() => _isSubmitting = true);

                      try {
                        await DeliveryService.assignDelivery(
                          deliveryId: _p.id,
                          userId: selectedDriverId!,
                          vehicleId: selectedVehicleId,
                        );
                        await _reloadDetail();
                        if (!mounted) return;
                        setState(() {
                          _isSubmitting = false;
                          _hasChanges = true;
                        });
                        _snack('Kurir berhasil di-assign!', _green);
                      } catch (e) {
                        if (!mounted) return;
                        setState(() => _isSubmitting = false);
                        _snack(
                          e.toString().replaceFirst('Exception: ', ''),
                          const Color(0xFFE53E3E),
                        );
                      }
                    },
                    icon: const Icon(Icons.check_rounded, size: 16),
                    label: const Text(
                      'Assign Sekarang',
                      style: TextStyle(fontWeight: FontWeight.w600),
                    ),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: _blue,
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 12),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(10),
                      ),
                      elevation: 0,
                    ),
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: OutlinedButton(
                    onPressed: () => Navigator.pop(ctx),
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
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _dropLabel(String label, IconData icon, bool requiredField) => Row(
    mainAxisSize: MainAxisSize.min,
    children: [
      Icon(icon, size: 14, color: const Color(0xFF4A5568)),
      const SizedBox(width: 5),
      Text(
        label,
        style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w500),
      ),
      if (requiredField)
        const Text(' *', style: TextStyle(color: Colors.red, fontSize: 13)),
    ],
  );

  Widget _dropContainer({required Widget child}) => Container(
    padding: const EdgeInsets.symmetric(horizontal: 12),
    decoration: BoxDecoration(
      borderRadius: BorderRadius.circular(10),
      border: Border.all(color: Colors.grey.shade300),
    ),
    child: child,
  );

  Widget _iRow(String label, String val) => Padding(
    padding: const EdgeInsets.symmetric(vertical: 2),
    child: Row(
      children: [
        Text(
          label,
          style: TextStyle(fontSize: 11, color: Colors.grey.shade600),
        ),
        const SizedBox(width: 8),
        Expanded(
          child: Text(
            val,
            textAlign: TextAlign.right,
            style: const TextStyle(
              fontSize: 11,
              fontWeight: FontWeight.w600,
              color: Color(0xFF2D3748),
            ),
          ),
        ),
      ],
    ),
  );

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

  int _statusRank(String statusApi) {
    final normalized = statusApi
        .trim()
        .toLowerCase()
        .replaceAll('-', '_')
        .replaceAll(' ', '_');

    switch (normalized) {
      case 'pending':
      case 'failed':
      case 'cancelled':
        return 0;
      case 'diproses':
      case 'processed':
      case 'processing':
        return 1;
      case 'assigned':
        return 2;
      case 'pickup':
      case 'pickedup':
      case 'diambil':
      case 'picked_up':
        return 3;
      case 'ondelivery':
      case 'dalam_perjalanan':
      case 'on_delivery':
        return 4;
      case 'terkirim':
      case 'delivered':
        return 5;
      default:
        return 0;
    }
  }

  String _fmtDateTime(DateTime dt) {
    final dd = dt.day.toString().padLeft(2, '0');
    final mm = dt.month.toString().padLeft(2, '0');
    final hh = dt.hour.toString().padLeft(2, '0');
    final mi = dt.minute.toString().padLeft(2, '0');
    return '$dd/$mm/${dt.year} $hh:$mi';
  }

  String _fmtEstimatedArrival(String raw) {
    final text = raw.trim();
    if (text.isEmpty) return '-';
    final parsed = DateTime.tryParse(text)?.toLocal();
    if (parsed == null) return '-';
    return _fmtDateTime(parsed);
  }

  List<_TimelineStep> get _steps {
    final idx = _statusRank(_p.statusApi);
    final createdAtText = '${_p.tanggalDibuat} ${_p.jamDibuat}';
    final processedAtText = idx >= 1 ? createdAtText : null;
    final deliveredAt = DateTime.tryParse(_p.deliveredAtRaw.trim())?.toLocal();
    final deliveredAtText = deliveredAt == null
        ? null
        : _fmtDateTime(deliveredAt);

    _TimelineStep buildStep({
      required int rank,
      required String label,
      required IconData icon,
      String? sublabel1,
      String? sublabel2,
    }) {
      return _TimelineStep(
        label: label,
        icon: icon,
        sublabel1: sublabel1,
        sublabel2: sublabel2,
        isDone: idx > rank,
        isActive: idx == rank,
        isPending: idx < rank,
      );
    }

    return [
      buildStep(
        rank: 0,
        label: 'Pengiriman Dibuat',
        icon: Icons.check_rounded,
        sublabel1: createdAtText,
      ),
      buildStep(
        rank: 1,
        label: 'Diproses Logistik',
        icon: Icons.check_rounded,
        sublabel1: processedAtText,
      ),
      buildStep(
        rank: 2,
        label: 'Ditugaskan ke Kurir',
        icon: Icons.person_rounded,
        sublabel1: _p.namaKurir,
        sublabel2: _p.nomorKurir,
      ),
      buildStep(
        rank: 3,
        label: 'Paket Diambil Kurir',
        icon: Icons.inventory_2_rounded,
      ),
      buildStep(
        rank: 4,
        label: 'Dalam Perjalanan',
        icon: Icons.local_shipping_rounded,
      ),
      buildStep(
        rank: 5,
        label: 'Terkirim',
        icon: Icons.check_circle_rounded,
        sublabel1: deliveredAtText,
      ),
    ];
  }

  @override
  Widget build(BuildContext context) {
    final isBatalable =
        _p.status != 'Terkirim' &&
        _p.status != 'Dibatalkan' &&
        _p.status != 'Gagal';

    return WillPopScope(
      onWillPop: () async {
        _goBack();
        return false;
      },
      child: Scaffold(
        backgroundColor: const Color(0xFFF3F4F8),
        appBar: AppBar(
          backgroundColor: Colors.white,
          foregroundColor: const Color(0xFF2D3748),
          elevation: 0,
          titleSpacing: 0,
          leading: IconButton(
            icon: const Icon(Icons.arrow_back_ios_new_rounded, size: 18),
            onPressed: _goBack,
          ),
          title: Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: _blue,
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Icon(
                  Icons.local_shipping_rounded,
                  color: Colors.white,
                  size: 16,
                ),
              ),
              const SizedBox(width: 10),
              Flexible(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Detail Pengiriman',
                      overflow: TextOverflow.ellipsis,
                      style: TextStyle(
                        fontSize: 15,
                        fontWeight: FontWeight.bold,
                        color: Color(0xFF2D3748),
                      ),
                    ),
                    Text(
                      _p.kodePengiriman,
                      overflow: TextOverflow.ellipsis,
                      style: TextStyle(
                        fontSize: 10,
                        color: Colors.grey.shade500,
                        fontWeight: FontWeight.w400,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          actions: [
            Padding(
              padding: const EdgeInsets.only(right: 10),
              child: ElevatedButton.icon(
                onPressed: _isSubmitting
                    ? null
                    : () => _snack('Mencetak surat jalan...', _green),
                icon: const Icon(Icons.print_rounded, size: 13),
                label: const Text(
                  'Cetak',
                  style: TextStyle(fontSize: 11, fontWeight: FontWeight.w600),
                ),
                style: ElevatedButton.styleFrom(
                  backgroundColor: _green,
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(
                    horizontal: 10,
                    vertical: 7,
                  ),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(8),
                  ),
                  elevation: 0,
                ),
              ),
            ),
          ],
          bottom: PreferredSize(
            preferredSize: const Size.fromHeight(1),
            child: Container(height: 1, color: Colors.grey.shade200),
          ),
        ),
        body: _isLoading
            ? const Center(
                child: CircularProgressIndicator(color: Color(0xFF3B6FE8)),
              )
            : SingleChildScrollView(
                padding: const EdgeInsets.all(16),
                child: Column(
                  children: [
                    if (_errorMessage.isNotEmpty) ...[
                      Container(
                        width: double.infinity,
                        margin: const EdgeInsets.only(bottom: 12),
                        padding: const EdgeInsets.all(10),
                        decoration: BoxDecoration(
                          color: const Color(0xFFFFF7E6),
                          borderRadius: BorderRadius.circular(10),
                          border: Border.all(color: const Color(0xFFFBD38D)),
                        ),
                        child: Text(
                          _errorMessage,
                          style: const TextStyle(
                            fontSize: 12,
                            color: Color(0xFF744210),
                          ),
                        ),
                      ),
                    ],
                    _buildStatusBar(isBatalable),
                    const SizedBox(height: 14),
                    _buildInfoTransaksi(),
                    const SizedBox(height: 14),
                    _buildInfoRute(),
                    const SizedBox(height: 14),
                    _buildTimeline(),
                    const SizedBox(height: 32),
                  ],
                ),
              ),
      ),
    );
  }

  Widget _buildStatusBar(bool isBatalable) => Container(
    padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
    decoration: BoxDecoration(
      color: Colors.white,
      borderRadius: BorderRadius.circular(12),
      boxShadow: [
        BoxShadow(
          color: Colors.black.withOpacity(0.05),
          blurRadius: 6,
          offset: const Offset(0, 2),
        ),
      ],
    ),
    child: Row(
      children: [
        const Text(
          'Status: ',
          style: TextStyle(fontSize: 13, color: Color(0xFF4A5568)),
        ),
        _statusChip(_p.status),
        const Spacer(),
        if (!_isStaffLogistik)
          Wrap(
            spacing: 8,
            children: [
              if (_p.status == 'Pending' || _p.status == 'Diproses')
                _actionBtn(
                  'Assign Kurir',
                  Icons.person_add_rounded,
                  const Color(0xFF3B6FE8),
                  _isSubmitting ? null : _showAssignKurir,
                ),
              if (_p.status == 'Assigned')
                _actionBtn(
                  'Paket Diambil',
                  Icons.inventory_2_rounded,
                  const Color(0xFF6B5CE7),
                  _isSubmitting ? null : _paketDiambil,
                ),
              if (_p.status == 'Diambil')
                _actionBtn(
                  'Mulai Pengiriman',
                  Icons.local_shipping_rounded,
                  const Color(0xFFED8936),
                  _isSubmitting ? null : _mulaiPengiriman,
                ),
              if (_p.status != 'Terkirim' &&
                  _p.status != 'Dibatalkan' &&
                  _p.status != 'Gagal')
                _actionBtn(
                  'Selesaikan',
                  Icons.check_circle_rounded,
                  _green,
                  _isSubmitting ? null : _selesaikan,
                ),
              if (isBatalable)
                _actionBtn(
                  'Batalkan',
                  Icons.cancel_rounded,
                  const Color(0xFFE53E3E),
                  _isSubmitting ? null : _batalkan,
                ),
            ],
          ),
      ],
    ),
  );

  Widget _actionBtn(
    String label,
    IconData icon,
    Color color,
    VoidCallback? onTap,
  ) => ElevatedButton.icon(
    onPressed: onTap,
    icon: Icon(icon, size: 13),
    label: Text(
      label,
      style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600),
    ),
    style: ElevatedButton.styleFrom(
      backgroundColor: color,
      foregroundColor: Colors.white,
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
      elevation: 0,
    ),
  );

  Widget _buildInfoTransaksi() => _InfoCard(
    title: 'Informasi Transaksi',
    icon: Icons.receipt_long_rounded,
    child: Column(
      children: [
        Row(
          children: [
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'No. Invoice',
                    style: TextStyle(fontSize: 11, color: Colors.grey.shade500),
                  ),
                  const SizedBox(height: 3),
                  Text(
                    _p.invoice,
                    style: const TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.bold,
                      color: Color(0xFF2D3748),
                    ),
                  ),
                ],
              ),
            ),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Tanggal Transaksi',
                    style: TextStyle(fontSize: 11, color: Colors.grey.shade500),
                  ),
                  const SizedBox(height: 3),
                  Text(
                    '${_p.tanggalDibuat} ${_p.jamDibuat}',
                    style: const TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.bold,
                      color: Color(0xFF2D3748),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
        const SizedBox(height: 12),
        Row(
          children: [
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Customer',
                    style: TextStyle(fontSize: 11, color: Colors.grey.shade500),
                  ),
                  const SizedBox(height: 3),
                  Text(
                    _p.namaCustomer,
                    style: const TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.bold,
                      color: Color(0xFF2D3748),
                    ),
                  ),
                ],
              ),
            ),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Total Belanja',
                    style: TextStyle(fontSize: 11, color: Colors.grey.shade500),
                  ),
                  const SizedBox(height: 3),
                  Text(
                    _rp(_p.totalBelanja),
                    style: const TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.bold,
                      color: _green,
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
        const SizedBox(height: 12),
        const Divider(height: 1, color: Color(0xFFF0F0F0)),
        const SizedBox(height: 10),
        Row(
          children: [
            Text(
              'Total Item',
              style: TextStyle(fontSize: 13, color: Colors.grey.shade500),
            ),
            const Spacer(),
            Text(
              '${_p.totalItem} item',
              style: const TextStyle(
                fontSize: 13,
                fontWeight: FontWeight.bold,
                color: Color(0xFF2D3748),
              ),
            ),
          ],
        ),
      ],
    ),
  );

  Widget _buildTimeline() => _InfoCard(
    title: 'Timeline Pengiriman',
    icon: Icons.schedule_rounded,
    child: Column(
      children: [
        ...List.generate(_steps.length, (i) {
          final step = _steps[i];
          final isLast = i == _steps.length - 1;
          return _TimelineTile(step: step, isLast: isLast);
        }),
      ],
    ),
  );

  Widget _buildInfoRute() => _InfoCard(
    title: 'Informasi Rute',
    icon: Icons.route_rounded,
    child: Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Asal',
          style: TextStyle(fontSize: 11, color: Colors.grey.shade500),
        ),
        const SizedBox(height: 4),
        Container(
          width: double.infinity,
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
          decoration: BoxDecoration(
            color: const Color(0xFFF8F9FA),
            borderRadius: BorderRadius.circular(8),
            border: Border.all(color: Colors.grey.shade200),
          ),
          child: Text(
            _p.asal,
            style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w500),
          ),
        ),
        const SizedBox(height: 12),
        Text(
          'Tujuan',
          style: TextStyle(fontSize: 11, color: Colors.grey.shade500),
        ),
        const SizedBox(height: 4),
        Container(
          width: double.infinity,
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
          decoration: BoxDecoration(
            color: const Color(0xFFF8F9FA),
            borderRadius: BorderRadius.circular(8),
            border: Border.all(color: Colors.grey.shade200),
          ),
          child: Text(
            _p.tujuan,
            style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w500),
          ),
        ),
        const SizedBox(height: 12),
        Container(
          width: double.infinity,
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
          decoration: BoxDecoration(
            color: const Color(0xFFEEF2FF),
            borderRadius: BorderRadius.circular(8),
            border: Border.all(color: const Color(0xFFC7D2FE)),
          ),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Icon(
                Icons.schedule_rounded,
                size: 16,
                color: Color(0xFF3B6FE8),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Estimasi Pengiriman Akan Tiba',
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w700,
                        color: Color(0xFF1E40AF),
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      _fmtEstimatedArrival(_p.estimatedDeliveryRaw),
                      style: const TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w600,
                        color: Color(0xFF2D3748),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ],
    ),
  );

  Widget _statusChip(String status) {
    const iconMap = {
      'Pending': Icons.hourglass_empty_rounded,
      'Diproses': Icons.settings_rounded,
      'Assigned': Icons.person_rounded,
      'Diambil': Icons.inventory_2_rounded,
      'Dalam Perjalanan': Icons.local_shipping_rounded,
      'Terkirim': Icons.check_circle_rounded,
      'Gagal': Icons.cancel_rounded,
      'Dibatalkan': Icons.block_rounded,
    };
    final color = _statusColorOf(status);
    final icon = iconMap[status] ?? Icons.circle;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
      decoration: BoxDecoration(
        color: color.withOpacity(0.12),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: color.withOpacity(0.3)),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 13, color: color),
          const SizedBox(width: 5),
          Text(
            status,
            style: TextStyle(
              fontSize: 12,
              fontWeight: FontWeight.w600,
              color: color,
            ),
          ),
        ],
      ),
    );
  }

  static Color _statusColorOf(String s) {
    switch (s) {
      case 'Terkirim':
        return const Color(0xFF38A169);
      case 'Dalam Perjalanan':
        return const Color(0xFF3B6FE8);
      case 'Diambil':
        return const Color(0xFF6B5CE7);
      case 'Assigned':
        return const Color(0xFF805AD5);
      case 'Diproses':
        return const Color(0xFFD69E2E);
      case 'Pending':
        return const Color(0xFFD69E2E);
      case 'Gagal':
        return const Color(0xFFE53E3E);
      case 'Dibatalkan':
        return const Color(0xFF718096);
      default:
        return const Color(0xFF4A5568);
    }
  }
}

class _TimelineTile extends StatelessWidget {
  final _TimelineStep step;
  final bool isLast;
  const _TimelineTile({required this.step, required this.isLast});

  static const _green = Color(0xFF38A169);

  @override
  Widget build(BuildContext context) {
    final dotColor = (step.isDone || step.isActive)
        ? _green
        : Colors.grey.shade300;
    final iconColor = step.isDone || step.isActive
        ? Colors.white
        : Colors.grey.shade400;
    final textColor = step.isPending && !step.isDone && !step.isActive
        ? Colors.grey.shade400
        : const Color(0xFF2D3748);

    return IntrinsicHeight(
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 44,
            child: Column(
              mainAxisAlignment: MainAxisAlignment.start,
              children: [
                Container(
                  width: 36,
                  height: 36,
                  decoration: BoxDecoration(
                    color: dotColor,
                    shape: BoxShape.circle,
                    border: step.isActive
                        ? Border.all(color: _green.withOpacity(0.35), width: 2)
                        : null,
                  ),
                  child: Icon(step.icon, color: iconColor, size: 18),
                ),
                if (!isLast)
                  Expanded(
                    child: Container(
                      width: 2,
                      margin: const EdgeInsets.symmetric(vertical: 3),
                      decoration: BoxDecoration(
                        color: step.isDone
                            ? _green.withOpacity(0.3)
                            : Colors.grey.shade200,
                        borderRadius: BorderRadius.circular(1),
                      ),
                    ),
                  ),
              ],
            ),
          ),
          Expanded(
            child: Padding(
              padding: const EdgeInsets.only(left: 10, bottom: 20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const SizedBox(height: 8),
                  Text(
                    step.label,
                    style: TextStyle(
                      fontSize: 14,
                      fontWeight: (step.isDone || step.isActive)
                          ? FontWeight.bold
                          : FontWeight.normal,
                      color: textColor,
                    ),
                  ),
                  if (step.sublabel1 != null && step.sublabel1!.isNotEmpty) ...[
                    const SizedBox(height: 2),
                    Text(
                      step.sublabel1!,
                      style: TextStyle(
                        fontSize: 12,
                        color: Colors.grey.shade500,
                      ),
                    ),
                  ],
                  if (step.sublabel2 != null && step.sublabel2!.isNotEmpty) ...[
                    const SizedBox(height: 1),
                    Text(
                      step.sublabel2!,
                      style: TextStyle(
                        fontSize: 12,
                        color: Colors.grey.shade500,
                      ),
                    ),
                  ],
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _InfoCard extends StatelessWidget {
  final String title;
  final IconData icon;
  final Widget child;
  const _InfoCard({
    required this.title,
    required this.icon,
    required this.child,
  });

  @override
  Widget build(BuildContext context) => Container(
    width: double.infinity,
    decoration: BoxDecoration(
      color: Colors.white,
      borderRadius: BorderRadius.circular(12),
      boxShadow: [
        BoxShadow(
          color: Colors.black.withOpacity(0.05),
          blurRadius: 8,
          offset: const Offset(0, 2),
        ),
      ],
    ),
    child: Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Container(
          width: double.infinity,
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
          decoration: BoxDecoration(
            color: const Color(0xFFF0F4FF),
            borderRadius: const BorderRadius.vertical(top: Radius.circular(12)),
            border: Border(bottom: BorderSide(color: Colors.grey.shade200)),
          ),
          child: Row(
            children: [
              Icon(icon, size: 15, color: const Color(0xFF3B6FE8)),
              const SizedBox(width: 8),
              Text(
                title,
                style: const TextStyle(
                  fontSize: 13,
                  fontWeight: FontWeight.w600,
                  color: Color(0xFF2D3748),
                ),
              ),
            ],
          ),
        ),
        Padding(padding: const EdgeInsets.all(16), child: child),
      ],
    ),
  );
}

class _ConfirmDialog extends StatelessWidget {
  final IconData icon;
  final String title;
  final String message;
  final String confirmLabel;
  final Color confirmColor;
  final VoidCallback onConfirm;

  const _ConfirmDialog({
    required this.icon,
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
            color: confirmColor.withOpacity(0.08),
            shape: BoxShape.circle,
          ),
          child: Icon(icon, color: confirmColor, size: 26),
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
