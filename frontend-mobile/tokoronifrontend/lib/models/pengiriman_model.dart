class PengirimanItem {
  final int id;
  final int? transactionId;
  final String kodePengiriman;
  final String invoice;
  final String tujuan;
  final String asal;
  final DateTime createdAt;
  final String tanggalDibuat;
  final String jamDibuat;
  final String namaCustomer;
  final int totalBelanja;
  final int totalItem;
  String status;
  String statusApi;
  int? kurirId;
  int? kendaraanId;
  String? namaKurir;
  String? nomorKurir;
  String? kendaraan;
  final String catatan;
  final String estimatedDeliveryRaw;
  final String deliveredAtRaw;

  PengirimanItem({
    required this.id,
    required this.transactionId,
    required this.kodePengiriman,
    required this.invoice,
    required this.tujuan,
    required this.asal,
    required this.createdAt,
    required this.tanggalDibuat,
    required this.jamDibuat,
    required this.namaCustomer,
    required this.totalBelanja,
    required this.totalItem,
    required this.status,
    required this.statusApi,
    this.kurirId,
    this.kendaraanId,
    this.namaKurir,
    this.nomorKurir,
    this.kendaraan,
    required this.catatan,
    required this.estimatedDeliveryRaw,
    required this.deliveredAtRaw,
  });

  factory PengirimanItem.fromJson(Map<String, dynamic> json) {
    final transaction = _asMap(json['transaction']);
    final user = _asMap(json['user']);
    final vehicle = _asMap(json['vehicle']);

    final createdAtRaw = (json['created_at'] ?? '').toString().trim();
    final createdAt = _parseDateTime(createdAtRaw) ?? DateTime.now();

    final tujuan = (json['destination'] ?? json['delivery_address'] ?? '')
        .toString();
    final asal = (json['origin'] ?? '').toString();

    final statusApi = (json['status'] ?? '').toString().trim().toLowerCase();
    final userRole = (user['role'] ?? '').toString().trim().toLowerCase();
    final showAssignedInfo = const {
      'assigned',
      'picked_up',
      'on_delivery',
      'delivered',
    }.contains(statusApi);
    final isDeliveryRole = const {
      'kurir',
      'logistik',
      'staff_logistik',
    }.contains(userRole);
    final namaVehicle = (vehicle['name'] ?? '').toString().trim();
    final plate = (vehicle['license_plate'] ?? '').toString().trim();
    final vehicleLabel = _joinVehicleLabel(
      name: namaVehicle,
      plate: plate,
      type: (vehicle['type'] ?? '').toString().trim(),
    );

    final invoice =
        (transaction['invoice_number'] ?? json['invoice_number'] ?? '-')
            .toString()
            .trim();
    final customerName =
        (transaction['customer_name'] ??
                json['recipient_name'] ??
                json['customer_name'] ??
                'Pelanggan Umum')
            .toString()
            .trim();

    final totalBelanja = _toInt(
      transaction['total_amount'] ??
          transaction['grand_total'] ??
          transaction['total'] ??
          json['total_amount'] ??
          json['grand_total'] ??
          json['total'] ??
          0,
    );

    return PengirimanItem(
      id: _toInt(json['id']),
      transactionId: _toNullableInt(
        json['transaction_id'] ?? transaction['id'],
      ),
      kodePengiriman: (json['delivery_code'] ?? json['kode_pengiriman'] ?? '-')
          .toString(),
      invoice: invoice.isEmpty ? '-' : invoice,
      tujuan: tujuan.trim().isEmpty ? '-' : tujuan.trim(),
      asal: asal.trim().isEmpty ? '-' : asal.trim(),
      createdAt: createdAt,
      tanggalDibuat: _formatDate(createdAt),
      jamDibuat: _formatTime(createdAt),
      namaCustomer: customerName.isEmpty ? 'Pelanggan Umum' : customerName,
      totalBelanja: totalBelanja,
      totalItem: _toInt(json['total_items'] ?? 0),
      status: deliveryStatusLabelFromApi(statusApi),
      statusApi: statusApi.isEmpty ? 'pending' : statusApi,
      kurirId: showAssignedInfo
          ? _toNullableInt(json['user_id'] ?? user['id'])
          : null,
      kendaraanId: showAssignedInfo
          ? _toNullableInt(json['vehicle_id'] ?? vehicle['id'])
          : null,
      namaKurir: showAssignedInfo && isDeliveryRole
          ? _nullableTrim(user['name'])
          : null,
      nomorKurir: showAssignedInfo && isDeliveryRole
          ? _nullableTrim(user['phone'])
          : null,
      kendaraan: showAssignedInfo ? vehicleLabel : null,
      catatan: (json['notes'] ?? '').toString(),
      estimatedDeliveryRaw: (json['estimated_delivery_time'] ?? '').toString(),
      deliveredAtRaw: (json['delivered_at'] ?? '').toString(),
    );
  }

  PengirimanItem copyWith({
    int? id,
    int? transactionId,
    String? kodePengiriman,
    String? invoice,
    String? tujuan,
    String? asal,
    DateTime? createdAt,
    String? tanggalDibuat,
    String? jamDibuat,
    String? namaCustomer,
    int? totalBelanja,
    int? totalItem,
    String? status,
    String? statusApi,
    int? kurirId,
    int? kendaraanId,
    String? namaKurir,
    String? nomorKurir,
    String? kendaraan,
    String? catatan,
    String? estimatedDeliveryRaw,
    String? deliveredAtRaw,
  }) {
    return PengirimanItem(
      id: id ?? this.id,
      transactionId: transactionId ?? this.transactionId,
      kodePengiriman: kodePengiriman ?? this.kodePengiriman,
      invoice: invoice ?? this.invoice,
      tujuan: tujuan ?? this.tujuan,
      asal: asal ?? this.asal,
      createdAt: createdAt ?? this.createdAt,
      tanggalDibuat: tanggalDibuat ?? this.tanggalDibuat,
      jamDibuat: jamDibuat ?? this.jamDibuat,
      namaCustomer: namaCustomer ?? this.namaCustomer,
      totalBelanja: totalBelanja ?? this.totalBelanja,
      totalItem: totalItem ?? this.totalItem,
      status: status ?? this.status,
      statusApi: statusApi ?? this.statusApi,
      kurirId: kurirId ?? this.kurirId,
      kendaraanId: kendaraanId ?? this.kendaraanId,
      namaKurir: namaKurir ?? this.namaKurir,
      nomorKurir: nomorKurir ?? this.nomorKurir,
      kendaraan: kendaraan ?? this.kendaraan,
      catatan: catatan ?? this.catatan,
      estimatedDeliveryRaw: estimatedDeliveryRaw ?? this.estimatedDeliveryRaw,
      deliveredAtRaw: deliveredAtRaw ?? this.deliveredAtRaw,
    );
  }
}

class DeliveryDriverOption {
  final int id;
  final String name;
  final String phone;

  const DeliveryDriverOption({
    required this.id,
    required this.name,
    required this.phone,
  });

  factory DeliveryDriverOption.fromJson(Map<String, dynamic> json) {
    return DeliveryDriverOption(
      id: _toInt(json['id']),
      name: (json['name'] ?? '').toString().trim(),
      phone: (json['phone'] ?? '').toString().trim(),
    );
  }
}

class DeliveryVehicleOption {
  final int id;
  final String name;
  final String plate;
  final String type;
  final String status;

  const DeliveryVehicleOption({
    required this.id,
    required this.name,
    required this.plate,
    required this.type,
    required this.status,
  });

  String get displayLabel {
    final base = _joinVehicleLabel(name: name, plate: plate, type: type);
    if (base == null) return 'Kendaraan #$id';
    return base;
  }

  factory DeliveryVehicleOption.fromJson(Map<String, dynamic> json) {
    return DeliveryVehicleOption(
      id: _toInt(json['id']),
      name: (json['name'] ?? '').toString().trim(),
      plate: (json['license_plate'] ?? '').toString().trim(),
      type: (json['type'] ?? '').toString().trim(),
      status: (json['status'] ?? '').toString().trim().toLowerCase(),
    );
  }
}

class DeliveryInvoiceOption {
  final int id;
  final String invoice;
  final String customer;
  final int totalBelanja;
  final int jumlahItem;
  final String tujuanDefault;

  const DeliveryInvoiceOption({
    required this.id,
    required this.invoice,
    required this.customer,
    required this.totalBelanja,
    required this.jumlahItem,
    required this.tujuanDefault,
  });

  factory DeliveryInvoiceOption.fromJson(Map<String, dynamic> json) {
    final itemsRaw = json['items'];
    final jumlahItem = itemsRaw is List
        ? itemsRaw.length
        : _toInt(json['items_count'] ?? json['total_items'] ?? 0);

    return DeliveryInvoiceOption(
      id: _toInt(json['id']),
      invoice: (json['invoice_number'] ?? '-').toString().trim(),
      customer: (json['customer_name'] ?? 'Pelanggan Umum').toString().trim(),
      totalBelanja: _toInt(json['total_amount'] ?? json['total'] ?? 0),
      jumlahItem: jumlahItem,
      tujuanDefault: (json['delivery_address'] ?? '').toString().trim(),
    );
  }
}

String deliveryStatusLabelFromApi(String statusApi) {
  switch (statusApi.toLowerCase().trim()) {
    case 'pending':
      return 'Pending';
    case 'processing':
      return 'Diproses';
    case 'assigned':
      return 'Assigned';
    case 'picked_up':
      return 'Diambil';
    case 'on_delivery':
      return 'Dalam Perjalanan';
    case 'delivered':
      return 'Terkirim';
    case 'failed':
      return 'Gagal';
    case 'cancelled':
      return 'Dibatalkan';
    default:
      return 'Pending';
  }
}

String deliveryStatusApiFromLabel(String statusLabel) {
  switch (statusLabel.toLowerCase().trim()) {
    case 'pending':
      return 'pending';
    case 'diproses':
      return 'processing';
    case 'assigned':
      return 'assigned';
    case 'diambil':
      return 'picked_up';
    case 'dalam perjalanan':
      return 'on_delivery';
    case 'terkirim':
      return 'delivered';
    case 'gagal':
      return 'failed';
    case 'dibatalkan':
      return 'cancelled';
    default:
      return 'pending';
  }
}

String _formatDate(DateTime dt) =>
    '${dt.day.toString().padLeft(2, '0')}/${dt.month.toString().padLeft(2, '0')}/${dt.year}';

String _formatTime(DateTime dt) =>
    '${dt.hour.toString().padLeft(2, '0')}:${dt.minute.toString().padLeft(2, '0')}';

DateTime? _parseDateTime(String raw) {
  if (raw.trim().isEmpty) return null;
  return DateTime.tryParse(raw)?.toLocal();
}

int _toInt(dynamic value) {
  if (value == null) return 0;
  if (value is int) return value;
  if (value is num) return value.toInt();
  final text = value.toString().trim();
  if (text.isEmpty) return 0;

  final intVal = int.tryParse(text);
  if (intVal != null) return intVal;

  final normalized = text
      .replaceAll('Rp', '')
      .replaceAll('rp', '')
      .replaceAll(' ', '');
  final decimalVal = double.tryParse(normalized);
  if (decimalVal != null) return decimalVal.round();

  final withoutDotThousands = normalized.replaceAll('.', '');
  final commaDecimal = withoutDotThousands.replaceAll(',', '.');
  final localeVal = double.tryParse(commaDecimal);
  if (localeVal != null) return localeVal.round();

  return 0;
}

int? _toNullableInt(dynamic value) {
  final parsed = _toInt(value);
  if (parsed <= 0) return null;
  return parsed;
}

Map<String, dynamic> _asMap(dynamic raw) {
  if (raw is Map<String, dynamic>) return raw;
  if (raw is Map) {
    return raw.map((k, v) => MapEntry(k.toString(), v));
  }
  return {};
}

String? _nullableTrim(dynamic value) {
  final text = (value ?? '').toString().trim();
  return text.isEmpty ? null : text;
}

String? _joinVehicleLabel({
  required String name,
  required String plate,
  required String type,
}) {
  final parts = <String>[];
  if (type.trim().isNotEmpty) parts.add(type.trim());
  if (name.trim().isNotEmpty) parts.add(name.trim());
  final first = parts.join(' - ');
  final cleanPlate = plate.trim();

  if (first.isEmpty && cleanPlate.isEmpty) return null;
  if (first.isEmpty) return cleanPlate;
  if (cleanPlate.isEmpty) return first;
  return '$first - $cleanPlate';
}
