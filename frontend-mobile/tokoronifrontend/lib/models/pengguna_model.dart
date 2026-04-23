// lib/user/pengguna_model.dart

const List<String> roleList = [
  'Owner',
  'Manager',
  'Kasir',
  'Kepala Gudang',
  'Staff Logistik',
  'Checker Barang',
];

const List<String> jenisTokoList = ['Grosir', 'Eceran'];

class PenggunaData {
  final int id;
  final String kode;
  String nama;
  String email;
  String role;
  String jenisToko;
  bool aktif;
  String bergabung;
  String telepon;
  String alamat;

  PenggunaData({
    required this.id,
    required this.kode,
    required this.nama,
    required this.email,
    required this.role,
    required this.jenisToko,
    required this.aktif,
    required this.bergabung,
    this.telepon = '',
    this.alamat = '',
  });
}

String roleLabelFromApi(String raw) {
  final key = raw.toLowerCase().trim();
  switch (key) {
    case 'owner':
      return 'Owner';
    case 'manager':
      return 'Manager';
    case 'kasir':
      return 'Kasir';
    case 'gudang':
    case 'kepala_gudang':
      return 'Kepala Gudang';
    case 'staff_logistik':
    case 'logistik':
      return 'Staff Logistik';
    case 'checker_barang':
      return 'Checker Barang';
    default:
      return raw.trim().isEmpty ? '-' : raw.trim();
  }
}

String roleApiFromLabel(String label) {
  final key = label.toLowerCase().trim();
  switch (key) {
    case 'owner':
      return 'owner';
    case 'manager':
      return 'manager';
    case 'kasir':
      return 'kasir';
    case 'kepala gudang':
      return 'kepala_gudang';
    case 'logistik':
    case 'staff logistik':
      return 'logistik';
    case 'checker barang':
      return 'checker_barang';
    default:
      return label.trim().toLowerCase().replaceAll(' ', '_');
  }
}

String jenisTokoLabelFromApi(String raw) {
  final key = raw.toLowerCase().trim();
  switch (key) {
    case 'grosir':
      return 'Grosir';
    case 'eceran':
    case 'retail':
      return 'Eceran';
    default:
      return raw.trim().isEmpty ? '-' : raw.trim();
  }
}

String jenisTokoApiFromLabel(String label) {
  final key = label.toLowerCase().trim();
  if (key == 'grosir') return 'grosir';
  if (key == 'eceran') return 'eceran';
  return key;
}

String formatTanggalGabung(String raw) {
  final value = raw.trim();
  if (value.isEmpty) return '-';
  DateTime? dt;
  try {
    dt = DateTime.parse(value).toLocal();
  } catch (_) {
    dt = null;
  }
  if (dt == null) return value;

  const month = <String>[
    'Jan',
    'Feb',
    'Mar',
    'Apr',
    'Mei',
    'Jun',
    'Jul',
    'Agu',
    'Sep',
    'Okt',
    'Nov',
    'Des',
  ];

  return '${dt.day} ${month[dt.month - 1]} ${dt.year}';
}

final List<PenggunaData> dummyPenggunaList = [];
