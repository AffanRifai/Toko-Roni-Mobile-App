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
  final String kode;
  final String nama;
  final String kategori;
  final String jenis;
  final int harga;
  final int stok;
  final String kadaluarsa;
  final bool aktif;

  const ProdukItem({
    required this.kode,
    required this.nama,
    required this.kategori,
    required this.jenis,
    required this.harga,
    required this.stok,
    required this.kadaluarsa,
    required this.aktif,
  });

  /// Buat dari JSON response API Laravel
  factory ProdukItem.fromJson(Map<String, dynamic> json) => ProdukItem(
        kode: json['kode_produk'] ?? '',
        nama: json['nama_produk'] ?? '',
        kategori: json['kategori']?['nama'] ?? json['kategori'] ?? '',
        jenis: json['satuan'] ?? '',
        harga: int.tryParse(json['harga_jual'].toString()) ?? 0,
        stok: int.tryParse(json['stok_awal'].toString()) ?? 0,
        kadaluarsa: json['tanggal_kadaluarsa'] ?? '',
        aktif: json['aktif'] == true || json['aktif'] == 1,
      );
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
            tgl = DateTime(int.parse(parts[0]), int.parse(parts[1]), int.parse(parts[2]));
          } else {
            // DD-MM-YYYY
            tgl = DateTime(int.parse(parts[2]), int.parse(parts[1]), int.parse(parts[0]));
          }
        }
      }
    } catch (_) {}

    return ProdukFormModel(
      kode: item.kode,
      nama: item.nama,
      kategori: item.kategori,
      hargaJual: item.harga.toString(),
      stokAwal: item.stok.toString(),
      satuan: item.jenis,
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

  factory KategoriItem.fromJson(Map<String, dynamic> json) => KategoriItem(
        id: json['id'],
        nama: json['nama'] ?? '',
        deskripsi: json['deskripsi'] ?? '',
      );

  Map<String, dynamic> toJson() => {'nama': nama, 'deskripsi': deskripsi};
}

// ── Dummy data (hapus & ganti API call saat sudah connect ke backend) ─────────
final List<ProdukItem> dummyProdukList = [
  const ProdukItem(kode:'PRD-482931', nama:'Beras Premium 5kg',  kategori:'Sembako',         jenis:'Per Kg',    harga:68000,  stok:120, kadaluarsa:'29-09-2027', aktif:true),
  const ProdukItem(kode:'PRD-739205', nama:'Minyak Goreng 2L',   kategori:'Sembako',         jenis:'Per Liter', harga:36500,  stok:95,  kadaluarsa:'29-09-2027', aktif:true),
  const ProdukItem(kode:'PRD-158640', nama:'Gula Pasir 1kg',     kategori:'Sembako',         jenis:'Per Kg',    harga:14000,  stok:200, kadaluarsa:'29-09-2027', aktif:true),
  const ProdukItem(kode:'PRD-864219', nama:'Mie Instan Ayam',    kategori:'Makanan',         jenis:'Dus',       harga:115000, stok:60,  kadaluarsa:'29-09-2027', aktif:true),
  const ProdukItem(kode:'PRD-307518', nama:'Teh Celup',          kategori:'Minuman',         jenis:'Pcs',       harga:22000,  stok:140, kadaluarsa:'29-09-2027', aktif:true),
  const ProdukItem(kode:'PRD-591726', nama:'Susu UHT 1L',        kategori:'Minuman',         jenis:'Per Liter', harga:18500,  stok:85,  kadaluarsa:'29-09-2027', aktif:true),
  const ProdukItem(kode:'PRD-246803', nama:'Sabun Cuci Piring',  kategori:'Kebutuhan Rumah', jenis:'Pcs',       harga:12000,  stok:110, kadaluarsa:'29-09-2027', aktif:true),
  const ProdukItem(kode:'PRD-975134', nama:'Tisu Gulung',        kategori:'Kebutuhan Rumah', jenis:'Pcs',       harga:45000,  stok:70,  kadaluarsa:'29-09-2027', aktif:true),
  const ProdukItem(kode:'PRD-624890', nama:'Kopi Sachet (Box)',  kategori:'Minuman',         jenis:'Dus',       harga:52000,  stok:130, kadaluarsa:'29-09-2027', aktif:true),
  const ProdukItem(kode:'PRD-813457', nama:'Tepung Terigu 1kg',  kategori:'Sembako',         jenis:'Per Kg',    harga:13500,  stok:175, kadaluarsa:'29-09-2027', aktif:true),
  const ProdukItem(kode:'PRD-112233', nama:'Sabun Mandi Dove',   kategori:'Kebutuhan Rumah', jenis:'Pcs',       harga:8500,   stok:15,  kadaluarsa:'29-09-2027', aktif:false),
  const ProdukItem(kode:'PRD-445566', nama:'Tolak Angin',        kategori:'Obat',            jenis:'Pcs',       harga:6000,   stok:0,   kadaluarsa:'30-02-2026', aktif:true),
];

final List<KategoriItem> dummyKategoriList = [
  KategoriItem(id: 1, nama: 'Makanan'),
  KategoriItem(id: 2, nama: 'Minuman'),
  KategoriItem(id: 3, nama: 'Sabun'),
  KategoriItem(id: 4, nama: 'Obat'),
];