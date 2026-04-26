import 'package:flutter/material.dart';

import '../../core/state/app_state.dart';
import '../../shared/widgets/notifikasi_widget.dart';
import '../../shared/widgets/profile_widget.dart';
import '../../shared/widgets/semua_notifikasi_page.dart';
import '../../shared/widgets/shared_widgets.dart';
import '../category/manajemen_kategori_page.dart';
import '../delivery/manajemen_pengiriman_page.dart';
import '../member/daftar_member_page.dart';
import '../product/daftar_produk_page.dart';
import '../profile/profile_page.dart';
import '../report/laporan_penjualan_page.dart';
import '../transaction/kasir_page.dart';
import '../transaction/riwayat_transaksi_page.dart';
import '../user/manajemen_pengguna_page.dart';
import '../vehicle/manajemen_kendaraan_page.dart';

class RoleSimpleDashboardPage extends StatefulWidget {
  final String title;

  const RoleSimpleDashboardPage({super.key, required this.title});

  @override
  State<RoleSimpleDashboardPage> createState() =>
      _RoleSimpleDashboardPageState();
}

class _RoleSimpleDashboardPageState extends State<RoleSimpleDashboardPage>
    with SingleTickerProviderStateMixin, SidebarMixin {
  @override
  void initState() {
    super.initState();
    initSidebar(this);
  }

  @override
  void dispose() {
    disposeSidebar();
    super.dispose();
  }

  void _handleMenuTap(String menu) {
    if (menu == 'Dashboard') {
      closeSidebar();
      return;
    }

    Widget? page;
    switch (menu) {
      case 'Pengguna':
        page = const ManajemenPenggunaPage();
        break;
      case 'Member':
        page = const DaftarMemberPage();
        break;
      case 'Laporan':
        page = const LaporanPenjualanPage();
        break;
      case 'Riwayat Transaksi':
        page = const RiwayatTransaksiPage();
        break;
      case 'Kasir':
        page = const KasirPage();
        break;
      case 'Produk':
        page = const DaftarProdukPage();
        break;
      case 'Kategori':
        page = const ManajemenKategoriPage();
        break;
      case 'Pengiriman':
        page = const ManajemenPengirimanPage();
        break;
      case 'Kendaraan':
        page = const ManajemenKendaraanPage();
        break;
      case 'Profile':
        page = const ProfilePage();
        break;
    }

    if (page != null) {
      closeSidebarThenNavigate(
        () => Navigator.push(context, MaterialPageRoute(builder: (_) => page!)),
      );
    }
  }

  String _formatRole(String rawRole) {
    final normalized = rawRole.trim().toLowerCase();
    switch (normalized) {
      case 'owner':
        return 'Owner';
      case 'manager':
        return 'Manager';
      case 'kasir':
        return 'Kasir';
      case 'kepala_gudang':
      case 'gudang':
      case 'kepala gudang':
        return 'Kepala Gudang';
      case 'staff_logistik':
      case 'logistik':
      case 'staff logistik':
        return 'Staff Logistik';
      case 'checker':
      case 'checker_barang':
      case 'checker barang':
        return 'Checker Barang';
      default:
        if (rawRole.trim().isEmpty) return '-';
        return rawRole.trim();
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF3F4F8),
      body: Stack(
        children: [
          Column(
            children: [
              Container(
                decoration: const BoxDecoration(
                  gradient: LinearGradient(
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                    colors: [
                      Color(0xFF6B9FFF),
                      Color(0xFF3B6FE8),
                      Color(0xFF2B55D0),
                    ],
                  ),
                ),
                child: SafeArea(
                  child: Padding(
                    padding: const EdgeInsets.fromLTRB(20, 16, 20, 24),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            BurgerMenuButton(onTap: openSidebar),
                            const Spacer(),
                            NotifikasiBell(
                              onLihatSemua: () => Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (_) => const SemuaNotifikasiPage(),
                                ),
                              ),
                            ),
                            const SizedBox(width: 12),
                            const ProfileWidget.fromAuth(),
                          ],
                        ),
                        const SizedBox(height: 20),
                        Text(
                          widget.title,
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 22,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 6),
                        const Text(
                          'Tampilan dashboard sesuai role pengguna',
                          style: TextStyle(
                            color: Colors.white70,
                            fontSize: 14,
                            fontWeight: FontWeight.w500,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
              Expanded(
                child: Center(
                  child: Padding(
                    padding: const EdgeInsets.all(20),
                    child: Container(
                      width: double.infinity,
                      constraints: const BoxConstraints(maxWidth: 520),
                      padding: const EdgeInsets.all(20),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(18),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withValues(alpha: 0.05),
                            blurRadius: 18,
                            offset: const Offset(0, 8),
                          ),
                        ],
                      ),
                      child: ValueListenableBuilder<String>(
                        valueListenable: AppState.instance.userName,
                        builder: (context, name, child) {
                          return ValueListenableBuilder<String>(
                            valueListenable: AppState.instance.userRole,
                            builder: (context, role, child) {
                              final displayName = name.trim().isEmpty
                                  ? '-'
                                  : name.trim();
                              final displayRole = _formatRole(role);
                              return Column(
                                mainAxisSize: MainAxisSize.min,
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  const Text(
                                    'Informasi Pengguna',
                                    style: TextStyle(
                                      fontSize: 18,
                                      fontWeight: FontWeight.w700,
                                      color: Color(0xFF1F2937),
                                    ),
                                  ),
                                  const SizedBox(height: 18),
                                  _InfoTile(label: 'Nama', value: displayName),
                                  const SizedBox(height: 10),
                                  _InfoTile(label: 'Role', value: displayRole),
                                ],
                              );
                            },
                          );
                        },
                      ),
                    ),
                  ),
                ),
              ),
            ],
          ),
          ...buildSidebarLayer(
            activeMenu: 'Dashboard',
            onMenuTap: _handleMenuTap,
          ),
        ],
      ),
    );
  }
}

class _InfoTile extends StatelessWidget {
  final String label;
  final String value;

  const _InfoTile({required this.label, required this.value});

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      decoration: BoxDecoration(
        color: const Color(0xFFF6F7FB),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Row(
        children: [
          SizedBox(
            width: 72,
            child: Text(
              label,
              style: const TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.w600,
                color: Color(0xFF4B5563),
              ),
            ),
          ),
          const Text(
            ': ',
            style: TextStyle(
              fontSize: 14,
              fontWeight: FontWeight.w600,
              color: Color(0xFF4B5563),
            ),
          ),
          Expanded(
            child: Text(
              value,
              style: const TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.w600,
                color: Color(0xFF111827),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
