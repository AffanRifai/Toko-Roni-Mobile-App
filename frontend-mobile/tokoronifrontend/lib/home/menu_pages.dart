import 'package:flutter/material.dart';
import '/product/daftar_produk_page.dart';

// ═══════════════════════════════════════════════════════════════════════════════
// BASE PAGE — reusable scaffold untuk semua halaman menu
// ═══════════════════════════════════════════════════════════════════════════════
class _BasePage extends StatelessWidget {
  final String title;
  final IconData icon;
  final Color color;
  final List<Widget> children;

  const _BasePage({
    required this.title,
    required this.icon,
    required this.color,
    this.children = const [],
  });

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF3F4F8),
      appBar: AppBar(
        backgroundColor: color,
        foregroundColor: Colors.white,
        elevation: 0,
        title: Text(
          title,
          style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 18),
        ),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new_rounded),
          onPressed: () => Navigator.pop(context),
        ),
      ),
      body: Column(
        children: [
          // Header wave decoration
          Container(
            height: 60,
            decoration: BoxDecoration(
              color: color,
              borderRadius: const BorderRadius.only(
                bottomLeft: Radius.circular(28),
                bottomRight: Radius.circular(28),
              ),
            ),
          ),
          Expanded(
            child: children.isEmpty
                ? _EmptyState(icon: icon, label: title, color: color)
                : ListView(
                    padding: const EdgeInsets.all(16),
                    children: children,
                  ),
          ),
        ],
      ),
    );
  }
}

// ── Empty state widget ───────────────────────────────────────────────────────
class _EmptyState extends StatelessWidget {
  final IconData icon;
  final String label;
  final Color color;

  const _EmptyState({
    required this.icon,
    required this.label,
    required this.color,
  });

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            width: 100,
            height: 100,
            decoration: BoxDecoration(
              color: color.withOpacity(0.1),
              shape: BoxShape.circle,
            ),
            child: Icon(icon, size: 52, color: color),
          ),
          const SizedBox(height: 20),
          Text(
            'Halaman $label',
            style: TextStyle(
              fontSize: 20,
              fontWeight: FontWeight.bold,
              color: const Color(0xFF2D3748),
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'Konten halaman ini akan tersedia\nsetelah terhubung dengan API.',
            textAlign: TextAlign.center,
            style: TextStyle(
              fontSize: 14,
              color: Colors.grey.shade500,
              height: 1.6,
            ),
          ),
          const SizedBox(height: 28),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
            decoration: BoxDecoration(
              color: color.withOpacity(0.12),
              borderRadius: BorderRadius.circular(24),
              border: Border.all(color: color.withOpacity(0.3)),
            ),
            child: Text(
              'Coming Soon',
              style: TextStyle(
                color: color,
                fontWeight: FontWeight.w600,
                fontSize: 13,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

// ── Reusable info card ───────────────────────────────────────────────────────
class _InfoTile extends StatelessWidget {
  final IconData icon;
  final String title;
  final String subtitle;
  final Color color;
  final VoidCallback? onTap;

  const _InfoTile({
    required this.icon,
    required this.title,
    required this.subtitle,
    required this.color,
    this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        margin: const EdgeInsets.only(bottom: 12),
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(14),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.05),
              blurRadius: 8,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(
                color: color.withOpacity(0.12),
                borderRadius: BorderRadius.circular(10),
              ),
              child: Icon(icon, color: color, size: 22),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: const TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w600,
                      color: Color(0xFF2D3748),
                    ),
                  ),
                  const SizedBox(height: 3),
                  Text(
                    subtitle,
                    style: TextStyle(fontSize: 12, color: Colors.grey.shade500),
                  ),
                ],
              ),
            ),
            Icon(
              Icons.chevron_right_rounded,
              color: Colors.grey.shade400,
              size: 20,
            ),
          ],
        ),
      ),
    );
  }
}

// ═══════════════════════════════════════════════════════════════════════════════
// 1. PENGGUNA
// ═══════════════════════════════════════════════════════════════════════════════
class PenggunaPage extends StatelessWidget {
  const PenggunaPage({super.key});

  @override
  Widget build(BuildContext context) {
    return _BasePage(
      title: 'Pengguna',
      icon: Icons.group_rounded,
      color: const Color(0xFF4169E1),
      children: [
        _SectionHeader(
          title: 'Daftar Pengguna',
          subtitle: 'Total 15 pengguna aktif',
        ),
        const SizedBox(height: 8),
        ..._dummyUsers.map(
          (u) => _UserTile(
            name: u['name']!,
            email: u['email']!,
            role: u['role']!,
            color: const Color(0xFF4169E1),
          ),
        ),
        const SizedBox(height: 16),
        _AddButton(label: 'Tambah Pengguna', color: const Color(0xFF4169E1)),
      ],
    );
  }

  static const _dummyUsers = [
    {'name': 'Alex Johnson', 'email': 'alex@gmail.com', 'role': 'Admin'},
    {'name': 'Budi Santoso', 'email': 'budi@gmail.com', 'role': 'Kasir'},
    {'name': 'Citra Dewi', 'email': 'citra@gmail.com', 'role': 'Kasir'},
    {'name': 'Doni Pratama', 'email': 'doni@gmail.com', 'role': 'Gudang'},
  ];
}

// ═══════════════════════════════════════════════════════════════════════════════
// 2. MEMBER
// ═══════════════════════════════════════════════════════════════════════════════
class MemberPage extends StatelessWidget {
  const MemberPage({super.key});

  @override
  Widget build(BuildContext context) {
    return _BasePage(
      title: 'Member',
      icon: Icons.people_alt_rounded,
      color: const Color(0xFF9B59B6),
      children: [
        _SectionHeader(
          title: 'Daftar Member',
          subtitle: 'Total 42 member terdaftar',
        ),
        const SizedBox(height: 8),
        ..._dummyMembers.map(
          (m) => _UserTile(
            name: m['name']!,
            email: m['email']!,
            role: m['poin']!,
            color: const Color(0xFF9B59B6),
          ),
        ),
        const SizedBox(height: 16),
        _AddButton(label: 'Tambah Member', color: const Color(0xFF9B59B6)),
      ],
    );
  }

  static const _dummyMembers = [
    {
      'name': 'Roni Juntinyuat',
      'email': 'roni@gmail.com',
      'poin': '1.250 poin',
    },
    {'name': 'Sari Wulandari', 'email': 'sari@gmail.com', 'poin': '890 poin'},
    {'name': 'Hendra Kusuma', 'email': 'hendra@gmail.com', 'poin': '560 poin'},
    {'name': 'Fitri Amalia', 'email': 'fitri@gmail.com', 'poin': '320 poin'},
  ];
}

// ═══════════════════════════════════════════════════════════════════════════════
// 3. LAPORAN
// ═══════════════════════════════════════════════════════════════════════════════
class LaporanPage extends StatelessWidget {
  const LaporanPage({super.key});

  @override
  Widget build(BuildContext context) {
    return _BasePage(
      title: 'Laporan',
      icon: Icons.show_chart_rounded,
      color: const Color(0xFF27AE60),
      children: [
        _SectionHeader(
          title: 'Jenis Laporan',
          subtitle: 'Pilih laporan yang ingin ditampilkan',
        ),
        const SizedBox(height: 8),
        _InfoTile(
          icon: Icons.receipt_long_rounded,
          title: 'Laporan Penjualan',
          subtitle: 'Rekap transaksi penjualan harian/bulanan',
          color: const Color(0xFF27AE60),
        ),
        _InfoTile(
          icon: Icons.inventory_rounded,
          title: 'Laporan Stok',
          subtitle: 'Pergerakan stok masuk dan keluar',
          color: const Color(0xFF27AE60),
        ),
        _InfoTile(
          icon: Icons.monetization_on_rounded,
          title: 'Laporan Keuangan',
          subtitle: 'Pemasukan dan pengeluaran toko',
          color: const Color(0xFF27AE60),
        ),
        _InfoTile(
          icon: Icons.people_rounded,
          title: 'Laporan Karyawan',
          subtitle: 'Absensi dan performa karyawan',
          color: const Color(0xFF27AE60),
        ),
        _InfoTile(
          icon: Icons.local_shipping_rounded,
          title: 'Laporan Pengiriman',
          subtitle: 'Status dan riwayat pengiriman barang',
          color: const Color(0xFF27AE60),
        ),
      ],
    );
  }
}

// ═══════════════════════════════════════════════════════════════════════════════
// 4. RIWAYAT TRANSAKSI
// ═══════════════════════════════════════════════════════════════════════════════
class RiwayatTransaksiPage extends StatelessWidget {
  const RiwayatTransaksiPage({super.key});

  static const _data = [
    {
      'id': 'INV20260271',
      'produk': 'Sukro',
      'waktu': '10 Feb 2026',
      'total': 'Rp 13.000',
      'success': true,
    },
    {
      'id': 'INV20260371',
      'produk': 'Sunlight',
      'waktu': '18 Feb 2026',
      'total': 'Rp 28.000',
      'success': true,
    },
    {
      'id': 'INV20260431',
      'produk': 'LOQ',
      'waktu': '29 Feb 2026',
      'total': 'Rp 12.000',
      'success': true,
    },
    {
      'id': 'INV20260932',
      'produk': 'TWS',
      'waktu': '09 Feb 2026',
      'total': 'Rp 9.000',
      'success': true,
    },
    {
      'id': 'INV20260932',
      'produk': 'TWS',
      'waktu': '09 Feb 2026',
      'total': 'Rp 9.000',
      'success': false,
    },
    {
      'id': 'INV20250234',
      'produk': 'Mouse',
      'waktu': '01 Feb 2026',
      'total': 'Rp 40.000',
      'success': false,
    },
  ];

  @override
  Widget build(BuildContext context) {
    return _BasePage(
      title: 'Riwayat Transaksi',
      icon: Icons.history_rounded,
      color: const Color(0xFFE67E22),
      children: [
        _SectionHeader(title: 'Transaksi Terbaru', subtitle: '7 hari terakhir'),
        const SizedBox(height: 8),
        ..._data.map((d) {
          final isSuccess = d['success'] as bool;
          return Container(
            margin: const EdgeInsets.only(bottom: 10),
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(14),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withOpacity(0.04),
                  blurRadius: 6,
                  offset: const Offset(0, 2),
                ),
              ],
            ),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(9),
                  decoration: BoxDecoration(
                    color: isSuccess
                        ? const Color(0xFF48BB78).withOpacity(0.12)
                        : const Color(0xFFE53E3E).withOpacity(0.12),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Icon(
                    isSuccess
                        ? Icons.check_circle_outline_rounded
                        : Icons.cancel_outlined,
                    color: isSuccess
                        ? const Color(0xFF48BB78)
                        : const Color(0xFFE53E3E),
                    size: 20,
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        d['id'] as String,
                        style: const TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.w600,
                          color: Color(0xFF2D3748),
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        '${d['produk']} • ${d['waktu']}',
                        style: TextStyle(
                          fontSize: 11,
                          color: Colors.grey.shade500,
                        ),
                      ),
                    ],
                  ),
                ),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    Text(
                      d['total'] as String,
                      style: const TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.bold,
                        color: Color(0xFF2D3748),
                      ),
                    ),
                    const SizedBox(height: 4),
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 8,
                        vertical: 2,
                      ),
                      decoration: BoxDecoration(
                        color: isSuccess
                            ? const Color(0xFF48BB78)
                            : const Color(0xFFE53E3E),
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: Text(
                        isSuccess ? 'success' : 'gagal',
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 10,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          );
        }),
      ],
    );
  }
}

// ═══════════════════════════════════════════════════════════════════════════════
// 5. KASIR
// ═══════════════════════════════════════════════════════════════════════════════
class KasirPage extends StatelessWidget {
  const KasirPage({super.key});

  @override
  Widget build(BuildContext context) {
    return _BasePage(
      title: 'Kasir',
      icon: Icons.computer_rounded,
      color: const Color(0xFF2ECC71),
      children: [
        _SectionHeader(
          title: 'Point of Sale',
          subtitle: 'Kelola transaksi penjualan',
        ),
        const SizedBox(height: 8),
        _InfoTile(
          icon: Icons.add_shopping_cart_rounded,
          title: 'Transaksi Baru',
          subtitle: 'Mulai transaksi penjualan baru',
          color: const Color(0xFF2ECC71),
        ),
        _InfoTile(
          icon: Icons.qr_code_scanner_rounded,
          title: 'Scan Barcode',
          subtitle: 'Tambah produk dengan scan barcode',
          color: const Color(0xFF2ECC71),
        ),
        _InfoTile(
          icon: Icons.pending_actions_rounded,
          title: 'Transaksi Tertunda',
          subtitle: 'Lihat transaksi yang belum selesai',
          color: const Color(0xFF2ECC71),
        ),
        _InfoTile(
          icon: Icons.discount_rounded,
          title: 'Kelola Diskon',
          subtitle: 'Atur diskon dan promo produk',
          color: const Color(0xFF2ECC71),
        ),
      ],
    );
  }
}

// ═══════════════════════════════════════════════════════════════════════════════
// 6. PRODUK
// ═══════════════════════════════════════════════════════════════════════════════
class ProdukPage extends StatelessWidget {
  const ProdukPage({super.key});

  static const _dummyProduk = [
    {
      'nama': 'Indomie Goreng',
      'kategori': 'Makanan',
      'stok': '192',
      'harga': 'Rp 3.500',
    },
    {
      'nama': 'Aqua 600ml',
      'kategori': 'Minuman',
      'stok': '120',
      'harga': 'Rp 4.000',
    },
    {
      'nama': 'Sabun Lifebuoy',
      'kategori': 'Sabun',
      'stok': '45',
      'harga': 'Rp 8.500',
    },
    {
      'nama': 'Kopi Kapal Api',
      'kategori': 'Minuman',
      'stok': '80',
      'harga': 'Rp 2.000',
    },
    {
      'nama': 'Tolak Angin',
      'kategori': 'Obat',
      'stok': '50',
      'harga': 'Rp 6.000',
    },
  ];

  @override
  Widget build(BuildContext context) {
    return _BasePage(
      title: 'Produk',
      icon: Icons.inventory_2_rounded,
      color: const Color(0xFF3498DB),
      children: [
        _SectionHeader(title: 'Daftar Produk', subtitle: 'Total 285 produk'),
        const SizedBox(height: 8),
        ..._dummyProduk.map(
          (p) => Container(
            margin: const EdgeInsets.only(bottom: 10),
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(14),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withOpacity(0.04),
                  blurRadius: 6,
                  offset: const Offset(0, 2),
                ),
              ],
            ),
            child: Row(
              children: [
                Container(
                  width: 46,
                  height: 46,
                  decoration: BoxDecoration(
                    color: const Color(0xFF3498DB).withOpacity(0.1),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: const Icon(
                    Icons.inventory_2_rounded,
                    color: Color(0xFF3498DB),
                    size: 22,
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        p['nama']!,
                        style: const TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.w600,
                          color: Color(0xFF2D3748),
                        ),
                      ),
                      const SizedBox(height: 3),
                      Text(
                        p['kategori']!,
                        style: TextStyle(
                          fontSize: 11,
                          color: Colors.grey.shade500,
                        ),
                      ),
                    ],
                  ),
                ),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    Text(
                      p['harga']!,
                      style: const TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.bold,
                        color: Color(0xFF2D3748),
                      ),
                    ),
                    const SizedBox(height: 3),
                    Text(
                      'Stok: ${p['stok']}',
                      style: TextStyle(
                        fontSize: 11,
                        color: Colors.grey.shade500,
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ),
        const SizedBox(height: 16),
        _AddButton(label: 'Tambah Produk', color: const Color(0xFF3498DB)),
      ],
    );
  }
}

// ═══════════════════════════════════════════════════════════════════════════════
// 7. KATEGORI
// ═══════════════════════════════════════════════════════════════════════════════
class KategoriPage extends StatelessWidget {
  const KategoriPage({super.key});

  static const _kategori = [
    {
      'nama': 'Makanan',
      'jumlah': '85 produk',
      'icon': Icons.fastfood_rounded,
      'color': Color(0xFFE74C3C),
    },
    {
      'nama': 'Minuman',
      'jumlah': '62 produk',
      'icon': Icons.local_drink_rounded,
      'color': Color(0xFF3498DB),
    },
    {
      'nama': 'Sabun',
      'jumlah': '38 produk',
      'icon': Icons.soap_rounded,
      'color': Color(0xFF9B59B6),
    },
    {
      'nama': 'Obat',
      'jumlah': '45 produk',
      'icon': Icons.medication_rounded,
      'color': Color(0xFFE67E22),
    },
    {
      'nama': 'Snack',
      'jumlah': '55 produk',
      'icon': Icons.cookie_rounded,
      'color': Color(0xFF27AE60),
    },
  ];

  @override
  Widget build(BuildContext context) {
    return _BasePage(
      title: 'Kategori',
      icon: Icons.label_rounded,
      color: const Color(0xFFF39C12),
      children: [
        _SectionHeader(
          title: 'Daftar Kategori',
          subtitle: 'Total 5 kategori produk',
        ),
        const SizedBox(height: 8),
        ..._kategori.map(
          (k) => _InfoTile(
            icon: k['icon'] as IconData,
            title: k['nama'] as String,
            subtitle: k['jumlah'] as String,
            color: k['color'] as Color,
          ),
        ),
        const SizedBox(height: 16),
        _AddButton(label: 'Tambah Kategori', color: const Color(0xFFF39C12)),
      ],
    );
  }
}

// ═══════════════════════════════════════════════════════════════════════════════
// 8. PENGIRIMAN
// ═══════════════════════════════════════════════════════════════════════════════
class PengirimanPage extends StatelessWidget {
  const PengirimanPage({super.key});

  static const _pengiriman = [
    {
      'id': 'SHP-001',
      'tujuan': 'Jl. Merdeka No. 12, Indramayu',
      'status': 'Dikirim',
      'success': true,
    },
    {
      'id': 'SHP-002',
      'tujuan': 'Jl. Sudirman No. 45, Cirebon',
      'status': 'Proses',
      'success': null,
    },
    {
      'id': 'SHP-003',
      'tujuan': 'Jl. Pahlawan No. 7, Majalengka',
      'status': 'Sampai',
      'success': true,
    },
    {
      'id': 'SHP-004',
      'tujuan': 'Jl. Diponegoro No. 23, Subang',
      'status': 'Gagal',
      'success': false,
    },
  ];

  @override
  Widget build(BuildContext context) {
    return _BasePage(
      title: 'Pengiriman',
      icon: Icons.local_shipping_rounded,
      color: const Color(0xFF1ABC9C),
      children: [
        _SectionHeader(
          title: 'Status Pengiriman',
          subtitle: 'Pantau pengiriman barang',
        ),
        const SizedBox(height: 8),
        ..._pengiriman.map((p) {
          final isSuccess = p['success'];
          Color statusColor;
          if (isSuccess == null)
            statusColor = const Color(0xFFECC94B);
          else if (isSuccess == true)
            statusColor = const Color(0xFF48BB78);
          else
            statusColor = const Color(0xFFE53E3E);

          return Container(
            margin: const EdgeInsets.only(bottom: 10),
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(14),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withOpacity(0.04),
                  blurRadius: 6,
                  offset: const Offset(0, 2),
                ),
              ],
            ),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(9),
                  decoration: BoxDecoration(
                    color: const Color(0xFF1ABC9C).withOpacity(0.12),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: const Icon(
                    Icons.local_shipping_rounded,
                    color: Color(0xFF1ABC9C),
                    size: 20,
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        p['id'] as String,
                        style: const TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.w600,
                          color: Color(0xFF2D3748),
                        ),
                      ),
                      const SizedBox(height: 3),
                      Text(
                        p['tujuan'] as String,
                        style: TextStyle(
                          fontSize: 11,
                          color: Colors.grey.shade500,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 10,
                    vertical: 4,
                  ),
                  decoration: BoxDecoration(
                    color: statusColor,
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Text(
                    p['status'] as String,
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 11,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                ),
              ],
            ),
          );
        }),
        const SizedBox(height: 16),
        _AddButton(label: 'Tambah Pengiriman', color: const Color(0xFF1ABC9C)),
      ],
    );
  }
}

// ═══════════════════════════════════════════════════════════════════════════════
// 9. KENDARAAN
// ═══════════════════════════════════════════════════════════════════════════════
class KendaraanPage extends StatelessWidget {
  const KendaraanPage({super.key});

  static const _kendaraan = [
    {
      'plat': 'E 1234 AB',
      'jenis': 'Pickup',
      'driver': 'Budi Santoso',
      'status': 'Tersedia',
    },
    {
      'plat': 'E 5678 CD',
      'jenis': 'Motor',
      'driver': 'Doni Pratama',
      'status': 'Digunakan',
    },
    {
      'plat': 'E 9012 EF',
      'jenis': 'Truk Kecil',
      'driver': 'Hendra K.',
      'status': 'Servis',
    },
  ];

  @override
  Widget build(BuildContext context) {
    return _BasePage(
      title: 'Kendaraan',
      icon: Icons.directions_car_rounded,
      color: const Color(0xFF8E44AD),
      children: [
        _SectionHeader(
          title: 'Armada Kendaraan',
          subtitle: 'Total 3 kendaraan',
        ),
        const SizedBox(height: 8),
        ..._kendaraan.map((k) {
          Color statusColor;
          switch (k['status']) {
            case 'Tersedia':
              statusColor = const Color(0xFF48BB78);
              break;
            case 'Digunakan':
              statusColor = const Color(0xFF4169E1);
              break;
            default:
              statusColor = const Color(0xFFECC94B);
          }
          return Container(
            margin: const EdgeInsets.only(bottom: 10),
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(14),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withOpacity(0.04),
                  blurRadius: 6,
                  offset: const Offset(0, 2),
                ),
              ],
            ),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(9),
                  decoration: BoxDecoration(
                    color: const Color(0xFF8E44AD).withOpacity(0.12),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: const Icon(
                    Icons.directions_car_rounded,
                    color: Color(0xFF8E44AD),
                    size: 20,
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        k['plat']!,
                        style: const TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.w700,
                          color: Color(0xFF2D3748),
                          letterSpacing: 1,
                        ),
                      ),
                      const SizedBox(height: 3),
                      Text(
                        '${k['jenis']} • ${k['driver']}',
                        style: TextStyle(
                          fontSize: 11,
                          color: Colors.grey.shade500,
                        ),
                      ),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 10,
                    vertical: 4,
                  ),
                  decoration: BoxDecoration(
                    color: statusColor,
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Text(
                    k['status']!,
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 11,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                ),
              ],
            ),
          );
        }),
        const SizedBox(height: 16),
        _AddButton(label: 'Tambah Kendaraan', color: const Color(0xFF8E44AD)),
      ],
    );
  }
}

// ═══════════════════════════════════════════════════════════════════════════════
// 10. PROFILE
// ═══════════════════════════════════════════════════════════════════════════════
class ProfilePage extends StatelessWidget {
  const ProfilePage({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF3F4F8),
      appBar: AppBar(
        backgroundColor: const Color(0xFF4169E1),
        foregroundColor: Colors.white,
        elevation: 0,
        title: const Text(
          'Profile',
          style: TextStyle(fontWeight: FontWeight.w600, fontSize: 18),
        ),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new_rounded),
          onPressed: () => Navigator.pop(context),
        ),
      ),
      body: SingleChildScrollView(
        child: Column(
          children: [
            // Header biru dengan avatar
            Container(
              width: double.infinity,
              padding: const EdgeInsets.fromLTRB(20, 0, 20, 32),
              decoration: const BoxDecoration(
                color: Color(0xFF4169E1),
                borderRadius: BorderRadius.only(
                  bottomLeft: Radius.circular(28),
                  bottomRight: Radius.circular(28),
                ),
              ),
              child: Column(
                children: [
                  CircleAvatar(
                    radius: 48,
                    backgroundColor: Colors.white24,
                    child: const Icon(
                      Icons.person,
                      size: 52,
                      color: Colors.white,
                    ),
                  ),
                  const SizedBox(height: 12),
                  const Text(
                    'Alex Johnson',
                    style: TextStyle(
                      color: Colors.white,
                      fontSize: 22,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    'alexander@gmail.com',
                    style: TextStyle(
                      color: Colors.white.withOpacity(0.8),
                      fontSize: 14,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 16,
                      vertical: 5,
                    ),
                    decoration: BoxDecoration(
                      color: Colors.white24,
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: const Text(
                      'Admin',
                      style: TextStyle(color: Colors.white, fontSize: 12),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 20),

            // Info tiles
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: Column(
                children: [
                  _ProfileTile(
                    icon: Icons.person_outline_rounded,
                    label: 'Nama Lengkap',
                    value: 'Alex Johnson',
                  ),
                  _ProfileTile(
                    icon: Icons.email_outlined,
                    label: 'Email',
                    value: 'alexander@gmail.com',
                  ),
                  _ProfileTile(
                    icon: Icons.phone_outlined,
                    label: 'No. Telepon',
                    value: '+62 812-3456-7890',
                  ),
                  _ProfileTile(
                    icon: Icons.badge_outlined,
                    label: 'Role',
                    value: 'Admin',
                  ),
                  _ProfileTile(
                    icon: Icons.calendar_today_outlined,
                    label: 'Bergabung',
                    value: '01 Januari 2025',
                  ),
                  const SizedBox(height: 16),
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton.icon(
                      onPressed: () {},
                      icon: const Icon(Icons.edit_rounded, size: 18),
                      label: const Text('Edit Profile'),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF4169E1),
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(14),
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(height: 10),
                  SizedBox(
                    width: double.infinity,
                    child: OutlinedButton.icon(
                      onPressed: () {},
                      icon: const Icon(Icons.lock_outline_rounded, size: 18),
                      label: const Text('Ganti Password'),
                      style: OutlinedButton.styleFrom(
                        foregroundColor: const Color(0xFF4169E1),
                        side: const BorderSide(color: Color(0xFF4169E1)),
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(14),
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 32),
          ],
        ),
      ),
    );
  }
}

class _ProfileTile extends StatelessWidget {
  final IconData icon;
  final String label;
  final String value;

  const _ProfileTile({
    required this.icon,
    required this.label,
    required this.value,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.04),
            blurRadius: 6,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Row(
        children: [
          Icon(icon, color: const Color(0xFF4169E1), size: 20),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  label,
                  style: TextStyle(fontSize: 11, color: Colors.grey.shade500),
                ),
                const SizedBox(height: 2),
                Text(
                  value,
                  style: const TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w500,
                    color: Color(0xFF2D3748),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

// ═══════════════════════════════════════════════════════════════════════════════
// SHARED WIDGETS
// ═══════════════════════════════════════════════════════════════════════════════
class _SectionHeader extends StatelessWidget {
  final String title;
  final String subtitle;

  const _SectionHeader({required this.title, required this.subtitle});

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          title,
          style: const TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.bold,
            color: Color(0xFF2D3748),
          ),
        ),
        const SizedBox(height: 3),
        Text(
          subtitle,
          style: TextStyle(fontSize: 12, color: Colors.grey.shade500),
        ),
      ],
    );
  }
}

class _UserTile extends StatelessWidget {
  final String name;
  final String email;
  final String role;
  final Color color;

  const _UserTile({
    required this.name,
    required this.email,
    required this.role,
    required this.color,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.04),
            blurRadius: 6,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Row(
        children: [
          CircleAvatar(
            radius: 22,
            backgroundColor: color.withOpacity(0.15),
            child: Text(
              name[0],
              style: TextStyle(
                color: color,
                fontWeight: FontWeight.bold,
                fontSize: 16,
              ),
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  name,
                  style: const TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w600,
                    color: Color(0xFF2D3748),
                  ),
                ),
                const SizedBox(height: 3),
                Text(
                  email,
                  style: TextStyle(fontSize: 11, color: Colors.grey.shade500),
                ),
              ],
            ),
          ),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
            decoration: BoxDecoration(
              color: color.withOpacity(0.12),
              borderRadius: BorderRadius.circular(20),
            ),
            child: Text(
              role,
              style: TextStyle(
                color: color,
                fontSize: 11,
                fontWeight: FontWeight.w600,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _AddButton extends StatelessWidget {
  final String label;
  final Color color;

  const _AddButton({required this.label, required this.color});

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: double.infinity,
      child: ElevatedButton.icon(
        onPressed: () {},
        icon: const Icon(Icons.add_rounded, color: Colors.white, size: 20),
        label: Text(
          label,
          style: const TextStyle(
            color: Colors.white,
            fontWeight: FontWeight.w600,
          ),
        ),
        style: ElevatedButton.styleFrom(
          backgroundColor: color,
          padding: const EdgeInsets.symmetric(vertical: 14),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(14),
          ),
          elevation: 0,
        ),
      ),
    );
  }
}
