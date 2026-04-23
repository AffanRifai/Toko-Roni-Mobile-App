// ════════════════════════════════════════════════════════════════════════════
// produk_model.dart
// Taruh file ini di: lib/product/produk_model.dart
//
// File ini adalah SINGLE SOURCE OF TRUTH untuk semua model produk.
// Import file ini di semua file yang butuh data produk:
//   import '../product/produk_model.dart';
// ════════════════════════════════════════════════════════════════════════════

// ── Model untuk TABEL daftar produk (data dari API / dummy) ──────────────────
class ProdukItem {
  final int? id;
  final String kode;
  final String nama;
  final String kategori;
  final String deskripsi;
  final String jenis;
  final int harga;
  final int hargaModal;
  final int stok;
  final int stokMinimum;
  final String barcode;
  final String berat;
  final String dimensi;
  final String kadaluarsa;
  final bool aktif;

  const ProdukItem({
    this.id,
    required this.kode,
    required this.nama,
    required this.kategori,
    this.deskripsi = '',
    required this.jenis,
    required this.harga,
    this.hargaModal = 0,
    required this.stok,
    this.stokMinimum = 0,
    this.barcode = '',
    this.berat = '',
    this.dimensi = '',
    required this.kadaluarsa,
    required this.aktif,
  });

  /// Buat dari JSON response API Laravel
  factory ProdukItem.fromJson(Map<String, dynamic> json) {
    final kategoriRaw = json['category'] ?? json['kategori'];
    final kategori = kategoriRaw is Map
        ? (kategoriRaw['name'] ?? kategoriRaw['nama'] ?? '').toString()
        : (json['category_name'] ?? kategoriRaw ?? '').toString();

    final kadaluarsaRaw =
        json['expiry_date'] ?? json['tanggal_kadaluarsa'] ?? json['kadaluarsa'];

    return ProdukItem(
      id: _toIntOrNull(json['id']),
      kode: (json['code'] ?? json['kode_produk'] ?? json['kode'] ?? '')
          .toString(),
      nama: (json['name'] ?? json['nama_produk'] ?? json['nama'] ?? '')
          .toString(),
      kategori: kategori.isEmpty ? '-' : kategori,
      deskripsi: (json['description'] ?? json['deskripsi'] ?? '').toString(),
      jenis: (json['unit'] ?? json['satuan'] ?? json['jenis'] ?? '-')
          .toString(),
      harga: _toInt(json['price'] ?? json['harga_jual'] ?? json['harga']),
      hargaModal: _toInt(
        json['cost_price'] ?? json['harga_modal'] ?? json['modal'],
      ),
      stok: _toInt(json['stock'] ?? json['stok_awal'] ?? json['stok']),
      stokMinimum: _toInt(
        json['min_stock'] ?? json['stok_minimum'] ?? json['stok_min'],
      ),
      barcode: (json['barcode'] ?? '').toString(),
      berat: _toWeightString(json['weight'] ?? json['berat']),
      dimensi: (json['dimensions'] ?? json['dimensi'] ?? '').toString(),
      kadaluarsa: _formatDate(kadaluarsaRaw),
      aktif: _toBool(json['is_active'] ?? json['aktif'], defaultValue: true),
    );
  }

  static int _toInt(dynamic value) {
    if (value == null) return 0;
    if (value is int) return value;
    if (value is num) return value.toInt();
    final raw = value.toString().trim();
    if (raw.isEmpty) return 0;

    final plain = int.tryParse(raw);
    if (plain != null) return plain;

    final sanitized = raw.replaceAll(RegExp(r'[^0-9,.\-]'), '');
    if (sanitized.isEmpty) return 0;

    String normalized = sanitized;
    final hasDot = sanitized.contains('.');
    final hasComma = sanitized.contains(',');

    if (hasDot && hasComma) {
      final commaLast = sanitized.lastIndexOf(',');
      final dotLast = sanitized.lastIndexOf('.');
      if (commaLast > dotLast) {
        // 12.345,67 -> 12345.67
        normalized = sanitized.replaceAll('.', '').replaceAll(',', '.');
      } else {
        // 12,345.67 -> 12345.67
        normalized = sanitized.replaceAll(',', '');
      }
    } else if (hasComma) {
      final split = sanitized.split(',');
      final decimalPart = split.last;
      normalized = decimalPart.length <= 2
          ? sanitized.replaceAll(',', '.')
          : sanitized.replaceAll(',', '');
    } else if (hasDot) {
      final split = sanitized.split('.');
      final decimalPart = split.last;
      normalized = decimalPart.length <= 2
          ? sanitized
          : sanitized.replaceAll('.', '');
    }

    final asDouble = double.tryParse(normalized);
    if (asDouble != null) return asDouble.round();
    return 0;
  }

  static int? _toIntOrNull(dynamic value) {
    if (value == null) return null;
    if (value is int) return value;
    if (value is num) return value.toInt();
    final raw = value.toString().trim();
    if (raw.isEmpty) return null;
    final parsed = int.tryParse(raw);
    if (parsed != null) return parsed;
    final normalized = _toInt(raw);
    return normalized == 0 ? null : normalized;
  }

  static bool _toBool(dynamic value, {required bool defaultValue}) {
    if (value == null) return defaultValue;
    if (value is bool) return value;
    if (value is num) return value != 0;
    final raw = value.toString().toLowerCase().trim();
    if (raw == 'true' || raw == '1' || raw == 'yes') return true;
    if (raw == 'false' || raw == '0' || raw == 'no') return false;
    return defaultValue;
  }

  static String _formatDate(dynamic value) {
    if (value == null) return '-';
    final raw = value.toString().trim();
    if (raw.isEmpty) return '-';

    try {
      final dt = DateTime.parse(raw);
      final dd = dt.day.toString().padLeft(2, '0');
      final mm = dt.month.toString().padLeft(2, '0');
      return '$dd-$mm-${dt.year}';
    } catch (_) {
      return raw;
    }
  }

  static String _toWeightString(dynamic value) {
    if (value == null) return '';
    final raw = value.toString().trim();
    if (raw.isEmpty) return '';
    final asNum = num.tryParse(raw.replaceAll(',', '.'));
    if (asNum == null) return raw;
    if (asNum == 0) return '';
    if (asNum % 1 == 0) return asNum.toInt().toString();
    return asNum.toString();
  }
}

// ── Model untuk FORM tambah/edit produk ──────────────────────────────────────
class ProdukFormModel {
  String kode;
  String nama;
  String kategori;
  String deskripsi;
  String hargaJual;
  String hargaModal;
  String stokAwal;
  String stokMinimum;
  String satuan;
  String barcode;
  String berat;
  String dimensi;
  DateTime? kadaluarsa;
  bool aktif;

  ProdukFormModel({
    this.kode = '',
    this.nama = '',
    this.kategori = '',
    this.deskripsi = '',
    this.hargaJual = '',
    this.hargaModal = '',
    this.stokAwal = '',
    this.stokMinimum = '',
    this.satuan = 'Dus',
    this.barcode = '',
    this.berat = '',
    this.dimensi = '',
    this.kadaluarsa,
    this.aktif = true,
  });

  /// Buat dari ProdukItem (untuk pre-fill form edit)
  factory ProdukFormModel.fromItem(ProdukItem item) {
    DateTime? tgl;
    try {
      // Support format DD-MM-YYYY atau YYYY-MM-DD
      final s = item.kadaluarsa;
      if (s.contains('-')) {
        final parts = s.split('-');
        if (parts.length == 3) {
          if (parts[0].length == 4) {
            // YYYY-MM-DD
            tgl = DateTime(
              int.parse(parts[0]),
              int.parse(parts[1]),
              int.parse(parts[2]),
            );
          } else {
            // DD-MM-YYYY
            tgl = DateTime(
              int.parse(parts[2]),
              int.parse(parts[1]),
              int.parse(parts[0]),
            );
          }
        }
      }
    } catch (_) {}

    return ProdukFormModel(
      kode: item.kode,
      nama: item.nama,
      kategori: item.kategori,
      deskripsi: item.deskripsi,
      hargaJual: item.harga.toString(),
      hargaModal: item.hargaModal > 0 ? item.hargaModal.toString() : '',
      stokAwal: item.stok.toString(),
      stokMinimum: item.stokMinimum > 0 ? item.stokMinimum.toString() : '',
      satuan: item.jenis,
      barcode: item.barcode,
      berat: item.berat,
      dimensi: item.dimensi,
      kadaluarsa: tgl,
      aktif: item.aktif,
    );
  }

  /// Convert ke Map untuk dikirim ke API Laravel
  Map<String, dynamic> toJson() => {
    'kode_produk': kode,
    'nama_produk': nama,
    'kategori': kategori,
    'deskripsi': deskripsi,
    'harga_jual': int.tryParse(hargaJual) ?? 0,
    'harga_modal': int.tryParse(hargaModal) ?? 0,
    'stok_awal': int.tryParse(stokAwal) ?? 0,
    'stok_minimum': int.tryParse(stokMinimum) ?? 0,
    'satuan': satuan,
    'barcode': barcode,
    'berat': int.tryParse(berat) ?? 0,
    'dimensi': dimensi,
    'tanggal_kadaluarsa': kadaluarsa != null
        ? '${kadaluarsa!.year}-${kadaluarsa!.month.toString().padLeft(2, '0')}-${kadaluarsa!.day.toString().padLeft(2, '0')}'
        : null,
    'aktif': aktif,
  };
}

// ── Model Kategori ───────────────────────────────────────────────────────────
class KategoriItem {
  final int? id;
  final String nama;
  final String deskripsi;

  KategoriItem({this.id, required this.nama, this.deskripsi = ''});

  factory KategoriItem.fromJson(Map<String, dynamic> json) {
    final idRaw = json['id'];
    return KategoriItem(
      id: idRaw is int ? idRaw : int.tryParse(idRaw?.toString() ?? ''),
      nama: (json['name'] ?? json['nama'] ?? '').toString(),
      deskripsi: (json['description'] ?? json['deskripsi'] ?? '').toString(),
    );
  }

  Map<String, dynamic> toJson() => {
    'name': nama,
    'description': deskripsi,
    'nama': nama,
    'deskripsi': deskripsi,
  };
}
