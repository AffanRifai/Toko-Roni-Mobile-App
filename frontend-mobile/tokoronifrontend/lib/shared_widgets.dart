// ============================================================
// lib/shared_widgets.dart
// ============================================================

import 'package:flutter/material.dart';
import 'core/auth_service.dart';
import 'home/beranda_page.dart';
import '/home/menu_pages.dart';
import 'product/daftar_produk_page.dart';
import 'auth/login_page.dart'; // sesuaikan path

// ════════════════════════════════════════════════════════════════════════════
// WAVE PAINTER
// ════════════════════════════════════════════════════════════════════════════
class AppWavePainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final p1 = Paint()..color = Colors.white.withOpacity(0.08);
    final path1 = Path()
      ..moveTo(0, size.height * 0.6)
      ..quadraticBezierTo(size.width * 0.3, size.height * 0.45,
          size.width * 0.6, size.height * 0.65)
      ..quadraticBezierTo(size.width * 0.8, size.height * 0.75,
          size.width, size.height * 0.55)
      ..lineTo(size.width, size.height)
      ..lineTo(0, size.height);
    canvas.drawPath(path1, p1);

    final p2 = Paint()..color = Colors.white.withOpacity(0.06);
    final path2 = Path()
      ..moveTo(0, size.height * 0.75)
      ..quadraticBezierTo(size.width * 0.4, size.height * 0.55,
          size.width * 0.7, size.height * 0.78)
      ..quadraticBezierTo(size.width * 0.85, size.height * 0.88,
          size.width, size.height * 0.72)
      ..lineTo(size.width, size.height)
      ..lineTo(0, size.height);
    canvas.drawPath(path2, p2);
  }

  @override
  bool shouldRepaint(_) => false;
}

// ════════════════════════════════════════════════════════════════════════════
// SIDEBAR ITEM
// ════════════════════════════════════════════════════════════════════════════
class AppSidebarItem extends StatelessWidget {
  final String label;
  final IconData icon;
  final bool isActive;
  final VoidCallback onTap;

  const AppSidebarItem({
    super.key,
    required this.label,
    required this.icon,
    required this.isActive,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        margin: const EdgeInsets.symmetric(vertical: 2),
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        decoration: BoxDecoration(
          color: isActive ? const Color(0xFF4169E1) : Colors.transparent,
          borderRadius: BorderRadius.circular(12),
        ),
        child: Row(children: [
          Icon(icon,
              color: isActive ? Colors.white : const Color(0xFF4A5568),
              size: 22),
          const SizedBox(width: 16),
          Text(label,
              style: TextStyle(
                fontSize: 15,
                fontWeight: isActive ? FontWeight.w600 : FontWeight.w500,
                color: isActive ? Colors.white : const Color(0xFF4A5568),
              )),
        ]),
      ),
    );
  }
}

// ════════════════════════════════════════════════════════════════════════════
// APP SIDEBAR — nama & email otomatis dari database (via SharedPreferences)
// ════════════════════════════════════════════════════════════════════════════
class AppSidebar extends StatefulWidget {
  final VoidCallback onClose;
  final String activeMenu;
  final void Function(String) onMenuTap;
  final VoidCallback onLogout; // dipanggil setelah logout berhasil

  const AppSidebar({
    super.key,
    required this.onClose,
    required this.activeMenu,
    required this.onMenuTap,
    required this.onLogout,
  });

  static const _menus = <Map<String, Object>>[
    {'label': 'Dashboard',         'icon': Icons.home_rounded},
    {'label': 'Pengguna',          'icon': Icons.group_rounded},
    {'label': 'Member',            'icon': Icons.people_alt_rounded},
    {'label': 'Laporan',           'icon': Icons.show_chart_rounded},
    {'label': 'Riwayat Transaksi', 'icon': Icons.history_rounded},
    {'label': 'Kasir',             'icon': Icons.computer_rounded},
    {'label': 'Produk',            'icon': Icons.inventory_2_rounded},
    {'label': 'Kategori',          'icon': Icons.label_rounded},
    {'label': 'Pengiriman',        'icon': Icons.local_shipping_rounded},
    {'label': 'Kendaraan',         'icon': Icons.directions_car_rounded},
    {'label': 'Profile',           'icon': Icons.person_rounded},
  ];

  @override
  State<AppSidebar> createState() => _AppSidebarState();
}

class _AppSidebarState extends State<AppSidebar> {
  String _userName  = '';
  String _userEmail = '';
  bool   _loggingOut = false;

  @override
  void initState() {
    super.initState();
    _loadUser();
  }

  Future<void> _loadUser() async {
    final name  = await AuthService.getUserName();
    final email = await AuthService.getUserEmail();
    if (mounted) setState(() { _userName = name; _userEmail = email; });
  }

  Future<void> _handleLogout() async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Konfirmasi Keluar'),
        content: const Text('Apakah kamu yakin ingin keluar?'),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: Text('Batal', style: TextStyle(color: Colors.grey.shade600)),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFFE53E3E),
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
            ),
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Keluar'),
          ),
        ],
      ),
    );
    if (confirm != true || !mounted) return;

    setState(() => _loggingOut = true);
    await AuthService.logout(); // hapus token dari SharedPreferences
    if (!mounted) return;
    widget.onLogout();          // navigasi dihandle oleh caller
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      width: MediaQuery.of(context).size.width * 0.78,
      height: double.infinity,
      color: Colors.white,
      child: SafeArea(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // ── Tombol close ──
            Align(
              alignment: Alignment.topRight,
              child: Padding(
                padding: const EdgeInsets.only(top: 16, right: 20),
                child: GestureDetector(
                  onTap: widget.onClose,
                  behavior: HitTestBehavior.opaque,
                  child: Padding(
                    padding: const EdgeInsets.all(8),
                    child: Row(mainAxisSize: MainAxisSize.min, children: [
                      Text('close',
                          style: TextStyle(
                              color: Colors.grey.shade600, fontSize: 14)),
                      const SizedBox(width: 4),
                      Icon(Icons.close, color: Colors.grey.shade600, size: 20),
                    ]),
                  ),
                ),
              ),
            ),

            // ── Avatar + nama + email dari database ──
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  CircleAvatar(
                    radius: 38,
                    backgroundColor: const Color(0xFFE8EDFF),
                    child: _userName.isEmpty
                        ? const Icon(Icons.person, size: 38, color: Color(0xFF4169E1))
                        : Text(
                            _userName[0].toUpperCase(),
                            style: const TextStyle(
                              fontSize: 28,
                              fontWeight: FontWeight.bold,
                              color: Color(0xFF4169E1),
                            ),
                          ),
                  ),
                  const SizedBox(height: 12),
                  Text(
                    _userName.isEmpty ? '...' : _userName,
                    style: const TextStyle(
                        fontSize: 22,
                        fontWeight: FontWeight.bold,
                        color: Color(0xFF2D3748)),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    _userEmail,
                    style: TextStyle(fontSize: 13, color: Colors.grey.shade500),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),

            // ── Menu list ──
            Expanded(
              child: ListView(
                padding: const EdgeInsets.symmetric(horizontal: 16),
                children: AppSidebar._menus.map((m) {
                  final label = m['label'] as String;
                  final icon  = m['icon']  as IconData;
                  return AppSidebarItem(
                    label   : label,
                    icon    : icon,
                    isActive: label == widget.activeMenu,
                    onTap   : () => widget.onMenuTap(label),
                  );
                }).toList(),
              ),
            ),

            // ── Tombol Keluar ──
            Padding(
              padding: const EdgeInsets.fromLTRB(20, 8, 20, 24),
              child: SizedBox(
                width: double.infinity,
                child: ElevatedButton.icon(
                  onPressed: _loggingOut ? null : _handleLogout,
                  icon: _loggingOut
                      ? const SizedBox(
                          width: 18, height: 18,
                          child: CircularProgressIndicator(
                              color: Colors.white, strokeWidth: 2))
                      : const Icon(Icons.logout_rounded,
                          color: Colors.white, size: 18),
                  label: Text(
                    _loggingOut ? 'Keluar...' : 'Keluar',
                    style: const TextStyle(
                        color: Colors.white, fontWeight: FontWeight.w600),
                  ),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFFE53E3E),
                    padding: const EdgeInsets.symmetric(
                        horizontal: 24, vertical: 12),
                    shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(24)),
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ════════════════════════════════════════════════════════════════════════════
// SIDEBAR MIXIN
// ════════════════════════════════════════════════════════════════════════════
mixin SidebarMixin<T extends StatefulWidget> on State<T> {
  bool sidebarOpen = false;
  late AnimationController sidebarAnimController;
  late Animation<Offset> sidebarSlideAnim;
  late Animation<double> sidebarFadeAnim;

  void initSidebar(TickerProvider vsync) {
    sidebarAnimController = AnimationController(
        vsync: vsync, duration: const Duration(milliseconds: 280));
    sidebarSlideAnim =
        Tween<Offset>(begin: const Offset(-1, 0), end: Offset.zero).animate(
            CurvedAnimation(
                parent: sidebarAnimController, curve: Curves.easeOut));
    sidebarFadeAnim = Tween<double>(begin: 0, end: 0.5).animate(
        CurvedAnimation(
            parent: sidebarAnimController, curve: Curves.easeOut));
  }

  void disposeSidebar() => sidebarAnimController.dispose();

  void openSidebar() {
    setState(() => sidebarOpen = true);
    sidebarAnimController.forward();
  }

  void closeSidebar() {
    sidebarAnimController.reverse().then((_) {
      if (mounted) setState(() => sidebarOpen = false);
    });
  }

  List<Widget> buildSidebarLayer({
    required String activeMenu,
    required void Function(String) onMenuTap,
    VoidCallback? onLogout, // ← opsional; default: navigasi ke LoginPage
  }) {
    if (!sidebarOpen) return [];
    return [
      AnimatedBuilder(
        animation: sidebarFadeAnim,
        builder: (_, __) => GestureDetector(
          onTap: closeSidebar,
          child: Container(
              color: Colors.black.withOpacity(sidebarFadeAnim.value)),
        ),
      ),
      SlideTransition(
        position: sidebarSlideAnim,
        child: AppSidebar(
          onClose   : closeSidebar,
          activeMenu: activeMenu,
          onMenuTap : onMenuTap,
          onLogout  : onLogout ?? _goToLogin,
        ),
      ),
    ];
  }

  void _goToLogin() {
    Navigator.of(context).pushAndRemoveUntil(
      MaterialPageRoute(builder: (_) => const LoginPage()),
      (_) => false,
    );
  }
}

// ════════════════════════════════════════════════════════════════════════════
// BURGER MENU BUTTON
// ════════════════════════════════════════════════════════════════════════════
class BurgerMenuButton extends StatelessWidget {
  final VoidCallback onTap;
  const BurgerMenuButton({super.key, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      behavior: HitTestBehavior.opaque,
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Column(mainAxisSize: MainAxisSize.min, children: [
          _line(), const SizedBox(height: 5),
          _line(), const SizedBox(height: 5),
          _line(),
        ]),
      ),
    );
  }

  Widget _line() => Container(
      width: 24,
      height: 3,
      decoration:
          BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(2)));
}

// ════════════════════════════════════════════════════════════════════════════
// SUMMARY CARD
// ════════════════════════════════════════════════════════════════════════════
class SummaryCard extends StatelessWidget {
  final String label;
  final String value;
  final IconData icon;
  final Color color;
  final VoidCallback? onArrowTap;

  const SummaryCard({
    super.key,
    required this.label,
    required this.value,
    required this.icon,
    required this.color,
    this.onArrowTap,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 140,
      margin: const EdgeInsets.only(right: 12),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
              color: Colors.black.withOpacity(0.06),
              blurRadius: 8,
              offset: const Offset(0, 3)),
        ],
      ),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Row(children: [
          Container(
            padding: const EdgeInsets.all(7),
            decoration: BoxDecoration(
              color: color.withOpacity(0.15),
              borderRadius: BorderRadius.circular(8),
            ),
            child: Icon(icon, color: color, size: 18),
          ),
          const Spacer(),
          GestureDetector(
            onTap: onArrowTap,
            child: Container(
              padding: const EdgeInsets.all(5),
              decoration: BoxDecoration(
                  color: Colors.grey.shade100,
                  borderRadius: BorderRadius.circular(8)),
              child: Icon(Icons.arrow_forward_rounded,
                  size: 14, color: Colors.grey.shade500),
            ),
          ),
        ]),
        const Spacer(),
        Text(value,
            style: const TextStyle(
                fontSize: 24,
                fontWeight: FontWeight.bold,
                color: Color(0xFF2D3748))),
        Text(label,
            style: TextStyle(fontSize: 11, color: Colors.grey.shade500),
            maxLines: 1,
            overflow: TextOverflow.ellipsis),
      ]),
    );
  }
}