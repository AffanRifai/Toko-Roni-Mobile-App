import 'package:flutter/material.dart';

class KendaraanItem {
  final int id;
  String nama;
  String platNomor;
  String jenis;
  String status;
  String tanggalMaintenance;
  double kapasitasBerat;
  double kapasitasVolume;
  String catatan;
  String warna;
  String tahun;

  KendaraanItem({
    required this.id,
    required this.nama,
    required this.platNomor,
    required this.jenis,
    required this.status,
    required this.tanggalMaintenance,
    this.kapasitasBerat = 0,
    this.kapasitasVolume = 0,
    this.catatan = '',
    this.warna = '',
    this.tahun = '',
  });

  String get kode => 'KND-${id.toString().padLeft(3, '0')}';

  factory KendaraanItem.fromJson(Map<String, dynamic> json) {
    final id = _toInt(json['id']);
    final nama = (json['name'] ?? json['nama'] ?? '').toString().trim();
    final plat = (json['license_plate'] ?? json['plat_nomor'] ?? '')
        .toString()
        .trim();

    return KendaraanItem(
      id: id,
      nama: nama.isEmpty ? 'Kendaraan #$id' : nama,
      platNomor: plat,
      jenis: kendaraanTypeLabelFromApi((json['type'] ?? '').toString()),
      status: kendaraanStatusLabelFromApi((json['status'] ?? '').toString()),
      tanggalMaintenance: formatDateForDisplay(
        (json['last_maintenance'] ?? '').toString(),
      ),
      kapasitasBerat: _toDouble(json['capacity_weight']),
      kapasitasVolume: _toDouble(json['capacity_volume']),
      catatan: (json['notes'] ?? json['catatan'] ?? '').toString().trim(),
      warna: (json['warna'] ?? '').toString().trim(),
      tahun: (json['tahun'] ?? '').toString().trim(),
    );
  }

  Map<String, dynamic> toUpsertPayload() {
    final payload = <String, dynamic>{
      'name': nama.trim(),
      'license_plate': platNomor.trim().toUpperCase(),
      'type': kendaraanTypeApiFromLabel(jenis),
      'status': kendaraanStatusApiFromLabel(status),
      'capacity_weight': kapasitasBerat,
      'capacity_volume': kapasitasVolume,
    };

    final maintenance = formatDateForApi(tanggalMaintenance);
    if (maintenance != null) {
      payload['last_maintenance'] = maintenance;
    }

    if (catatan.trim().isNotEmpty) {
      payload['notes'] = catatan.trim();
    }

    return payload;
  }

  KendaraanItem copyWith({
    int? id,
    String? nama,
    String? platNomor,
    String? jenis,
    String? status,
    String? tanggalMaintenance,
    double? kapasitasBerat,
    double? kapasitasVolume,
    String? catatan,
    String? warna,
    String? tahun,
  }) {
    return KendaraanItem(
      id: id ?? this.id,
      nama: nama ?? this.nama,
      platNomor: platNomor ?? this.platNomor,
      jenis: jenis ?? this.jenis,
      status: status ?? this.status,
      tanggalMaintenance: tanggalMaintenance ?? this.tanggalMaintenance,
      kapasitasBerat: kapasitasBerat ?? this.kapasitasBerat,
      kapasitasVolume: kapasitasVolume ?? this.kapasitasVolume,
      catatan: catatan ?? this.catatan,
      warna: warna ?? this.warna,
      tahun: tahun ?? this.tahun,
    );
  }
}

const jenisKendaraanList = ['Motor', 'Mobil Pick-up', 'Van', 'Truck'];
const statusKendaraanList = ['Tersedia', 'Sedang Digunakan', 'Servis'];
const statusFilterKendaraanList = [
  'Semua',
  'Tersedia',
  'Sedang Digunakan',
  'Servis',
];
const jenisFilterKendaraanList = [
  'Semua Jenis',
  'Motor',
  'Mobil Pick-up',
  'Van',
  'Truck',
];

Color kendaraanStatusColor(String statusLabel) {
  switch (statusLabel) {
    case 'Tersedia':
      return const Color(0xFF38A169);
    case 'Sedang Digunakan':
      return const Color(0xFFD69E2E);
    case 'Servis':
      return const Color(0xFFE53E3E);
    default:
      return const Color(0xFF718096);
  }
}

String kendaraanTypeLabelFromApi(String api) {
  switch (api.trim().toLowerCase()) {
    case 'motor':
    case 'motorcycle':
      return 'Motor';
    case 'pickup':
    case 'pick-up':
      return 'Mobil Pick-up';
    case 'van':
      return 'Van';
    case 'truck':
      return 'Truck';
    default:
      final raw = api.trim();
      if (raw.isEmpty) return 'Motor';
      return raw[0].toUpperCase() + raw.substring(1).toLowerCase();
  }
}

String kendaraanTypeApiFromLabel(String label) {
  switch (label.trim().toLowerCase()) {
    case 'motor':
      return 'motorcycle';
    case 'mobil pick-up':
    case 'mobil pickup':
    case 'pickup':
      return 'pickup';
    case 'van':
      return 'van';
    case 'truck':
      return 'truck';
    default:
      return label.trim().toLowerCase().replaceAll(' ', '_');
  }
}

String kendaraanStatusLabelFromApi(String api) {
  switch (api.trim().toLowerCase()) {
    case 'available':
      return 'Tersedia';
    case 'in_use':
    case 'in use':
      return 'Sedang Digunakan';
    case 'maintenance':
      return 'Servis';
    default:
      return 'Tersedia';
  }
}

String kendaraanStatusApiFromLabel(String label) {
  switch (label.trim().toLowerCase()) {
    case 'tersedia':
      return 'available';
    case 'sedang digunakan':
      return 'in_use';
    case 'servis':
      return 'maintenance';
    default:
      return 'available';
  }
}

DateTime? parseFlexibleDate(String raw) {
  final value = raw.trim();
  if (value.isEmpty || value == '-') return null;

  final slash = RegExp(r'^(\d{2})/(\d{2})/(\d{4})$').firstMatch(value);
  if (slash != null) {
    final day = int.tryParse(slash.group(1) ?? '');
    final month = int.tryParse(slash.group(2) ?? '');
    final year = int.tryParse(slash.group(3) ?? '');
    if (day != null && month != null && year != null) {
      return DateTime(year, month, day);
    }
  }

  return DateTime.tryParse(value)?.toLocal();
}

String formatDateForDisplay(String raw) {
  final parsed = parseFlexibleDate(raw);
  if (parsed == null) return '-';
  return formatDateOnly(parsed);
}

String? formatDateForApi(String displayOrRaw) {
  final parsed = parseFlexibleDate(displayOrRaw);
  if (parsed == null) return null;
  return '${parsed.year.toString().padLeft(4, '0')}-${parsed.month.toString().padLeft(2, '0')}-${parsed.day.toString().padLeft(2, '0')}';
}

String formatDateOnly(DateTime dt) {
  return '${dt.day.toString().padLeft(2, '0')}/${dt.month.toString().padLeft(2, '0')}/${dt.year}';
}

int _toInt(dynamic value) {
  if (value == null) return 0;
  if (value is int) return value;
  if (value is num) return value.toInt();
  return int.tryParse(value.toString()) ?? 0;
}

double _toDouble(dynamic value) {
  if (value == null) return 0;
  if (value is double) return value;
  if (value is num) return value.toDouble();
  return double.tryParse(value.toString()) ?? 0;
}
