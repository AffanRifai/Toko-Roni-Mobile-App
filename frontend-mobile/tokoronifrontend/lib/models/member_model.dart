// lib/member/member_model.dart

// ── Konstanta ─────────────────────────────────────────────────────────────────
const List<String> tipeMemberList = ['Biasa', 'Gold', 'Platinum'];
const List<String> statusMemberList = ['Semua Status', 'Aktif', 'Nonaktif'];
const List<String> tipeMemberFilterList = [
  'Semua Tipe',
  'Biasa',
  'Gold',
  'Platinum',
];

// ════════════════════════════════════════════════════════════════════════════
// MODEL MEMBER
// ════════════════════════════════════════════════════════════════════════════
class MemberData {
  final int id;
  final String kode;
  String nama;
  String email;
  String telepon;
  String alamat;
  String tipe;
  int limitKredit;
  int piutang;
  bool aktif;
  String tanggalRegistrasi;
  String terdaftarSejak;
  String terakhirUpdate;

  MemberData({
    this.id = 0,
    required this.kode,
    required this.nama,
    required this.email,
    required this.telepon,
    required this.alamat,
    required this.tipe,
    required this.limitKredit,
    required this.piutang,
    required this.aktif,
    required this.tanggalRegistrasi,
    required this.terdaftarSejak,
    required this.terakhirUpdate,
  });

  int get sisaLimit => limitKredit - piutang;
  double get persenPiutang =>
      limitKredit == 0 ? 0 : (piutang / limitKredit).clamp(0.0, 1.0);
}

String tipeMemberLabelFromApi(String raw) {
  switch (raw.trim().toLowerCase()) {
    case 'gold':
      return 'Gold';
    case 'platinum':
      return 'Platinum';
    case 'biasa':
      return 'Biasa';
    default:
      return raw.trim().isEmpty ? 'Biasa' : raw.trim();
  }
}

String tipeMemberApiFromLabel(String label) {
  switch (label.trim().toLowerCase()) {
    case 'gold':
      return 'gold';
    case 'platinum':
      return 'platinum';
    case 'biasa':
      return 'biasa';
    default:
      return 'biasa';
  }
}

String formatTanggalMember(String raw) {
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

String relativeTimeFromRaw(String raw) {
  final value = raw.trim();
  if (value.isEmpty) return '-';
  DateTime? dt;
  try {
    dt = DateTime.parse(value).toLocal();
  } catch (_) {
    dt = null;
  }
  if (dt == null) return '-';

  final diff = DateTime.now().difference(dt);
  if (diff.inSeconds < 60) return 'Baru saja';
  if (diff.inMinutes < 60) return '${diff.inMinutes} menit lalu';
  if (diff.inHours < 24) return '${diff.inHours} jam lalu';
  if (diff.inDays < 7) return '${diff.inDays} hari lalu';
  if (diff.inDays < 30) return '${(diff.inDays / 7).floor()} minggu lalu';
  if (diff.inDays < 365) return '${(diff.inDays / 30).floor()} bulan lalu';
  return '${(diff.inDays / 365).floor()} tahun lalu';
}

// ════════════════════════════════════════════════════════════════════════════
// MODEL PIUTANG
// ════════════════════════════════════════════════════════════════════════════
class PiutangData {
  final String noPiutang;
  final String invoice;
  final String tanggal;
  final int totalPiutang;
  final int sisaLimit;
  final int totalLimit;
  final int totalTransaksiKredit;
  final String jatuhTempo;
  final String status; // 'Lunas' | 'Menunggak' | 'Belum Jatuh Tempo'

  const PiutangData({
    required this.noPiutang,
    required this.invoice,
    required this.tanggal,
    required this.totalPiutang,
    required this.sisaLimit,
    required this.totalLimit,
    required this.totalTransaksiKredit,
    required this.jatuhTempo,
    required this.status,
  });
}

// ── Dummy data ────────────────────────────────────────────────────────────────
final List<MemberData> dummyMemberList = [
  MemberData(
    kode: 'MBR0001',
    nama: 'Asep Saepudin',
    email: 'asep123@gmail.com',
    telepon: '0812343567890',
    alamat: 'Indramayu',
    tipe: 'Gold',
    limitKredit: 3000000,
    piutang: 1000000,
    aktif: true,
    tanggalRegistrasi: '12 Februari 2026',
    terdaftarSejak: '1 Week ago',
    terakhirUpdate: '1 jam lalu',
  ),
  MemberData(
    kode: 'MBR0002',
    nama: 'Jomod',
    email: 'jomod23@gmail.com',
    telepon: '0812343567890',
    alamat: 'Jakarta',
    tipe: 'Biasa',
    limitKredit: 2000000,
    piutang: 800000,
    aktif: true,
    tanggalRegistrasi: '1 Februari 2026',
    terdaftarSejak: '2 Weeks ago',
    terakhirUpdate: '3 jam lalu',
  ),
  MemberData(
    kode: 'MBR0003',
    nama: 'Udin Petot',
    email: 'petot@gmail.com',
    telepon: '0812343567890',
    alamat: 'Bandung',
    tipe: 'Platinum',
    limitKredit: 4000000,
    piutang: 500000,
    aktif: true,
    tanggalRegistrasi: '5 Februari 2026',
    terdaftarSejak: '2 Weeks ago',
    terakhirUpdate: '1 hari lalu',
  ),
  MemberData(
    kode: 'MBR0004',
    nama: 'Munip',
    email: 'munip23@gmail.com',
    telepon: '0812343567890',
    alamat: 'Cirebon',
    tipe: 'Gold',
    limitKredit: 5000000,
    piutang: 0,
    aktif: true,
    tanggalRegistrasi: '10 Februari 2026',
    terdaftarSejak: '1 Week ago',
    terakhirUpdate: '2 hari lalu',
  ),
  MemberData(
    kode: 'MBR0005',
    nama: 'Mastem',
    email: 'mastem123@gmail.com',
    telepon: '0812343567890',
    alamat: 'Subang',
    tipe: 'Biasa',
    limitKredit: 1000000,
    piutang: 0,
    aktif: false,
    tanggalRegistrasi: '20 Januari 2026',
    terdaftarSejak: '3 Weeks ago',
    terakhirUpdate: '5 hari lalu',
  ),
];

// Dummy piutang per member
final Map<String, PiutangData> dummyPiutangMap = {
  'MBR0001': const PiutangData(
    noPiutang: 'PTG-913874532',
    invoice: 'INV202602280305',
    tanggal: '16 Feb 2026',
    totalPiutang: 1000000,
    sisaLimit: 2000000,
    totalLimit: 3000000,
    totalTransaksiKredit: 5,
    jatuhTempo: '16 Apr 2026',
    status: 'Menunggak',
  ),
  'MBR0002': const PiutangData(
    noPiutang: 'PTG-823451234',
    invoice: 'INV202602150201',
    tanggal: '10 Feb 2026',
    totalPiutang: 800000,
    sisaLimit: 1200000,
    totalLimit: 2000000,
    totalTransaksiKredit: 3,
    jatuhTempo: '10 Apr 2026',
    status: 'Belum Jatuh Tempo',
  ),
  'MBR0003': const PiutangData(
    noPiutang: 'PTG-712345678',
    invoice: 'INV202602050302',
    tanggal: '5 Feb 2026',
    totalPiutang: 500000,
    sisaLimit: 3500000,
    totalLimit: 4000000,
    totalTransaksiKredit: 2,
    jatuhTempo: '5 Apr 2026',
    status: 'Belum Jatuh Tempo',
  ),
};

// ── Helper format rupiah ───────────────────────────────────────────────────────
String rupiahFormat(int n) {
  if (n == 0) return 'Rp 0';
  final s = n.toString();
  final buf = StringBuffer('Rp ');
  for (int i = 0; i < s.length; i++) {
    if (i > 0 && (s.length - i) % 3 == 0) buf.write('.');
    buf.write(s[i]);
  }
  return buf.toString();
}
