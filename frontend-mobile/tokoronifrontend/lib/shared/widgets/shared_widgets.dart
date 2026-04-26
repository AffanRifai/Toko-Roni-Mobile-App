// ============================================================
// lib/widgets/shared_widgets.dart  (atau lib/shared_widgets.dart)
// ============================================================

import 'package:flutter/material.dart';
import '../../core/access/role_access.dart';
import '../../core/state/app_state.dart';
import '../../features/auth/login_page.dart'; // sesuaikan path

String _avatarInitial(String? rawName) {
  final text = (rawName ?? '').trim();
  if (text.isEmpty) return '?';
  for (final rune in text.runes) {
    final char = String.fromCharCode(rune);
    if (RegExp(r'[A-Za-z0-9]').hasMatch(char)) return char.toUpperCase();
  }
  return text.substring(0, 1).toUpperCase();
}

// ════════════════════════════════════════════════════════════════════════════
// PAGE TRANSITIONS
// ════════════════════════════════════════════════════════════════════════════
Route<T> slideRoute<T>(Widget page) => PageRouteBuilder<T>(
  pageBuilder: (_, animation, __) => page,
  transitionDuration: const Duration(milliseconds: 320),
  reverseTransitionDuration: const Duration(milliseconds: 260),
  transitionsBuilder: (_, animation, secondaryAnimation, child) {
    final slideIn = Tween<Offset>(
      begin: const Offset(1.0, 0.0),
      end: Offset.zero,
    ).animate(CurvedAnimation(parent: animation, curve: Curves.easeOutCubic));
    final slideOut =
        Tween<Offset>(
          begin: Offset.zero,
          end: const Offset(-0.25, 0.0),
        ).animate(
          CurvedAnimation(
            parent: secondaryAnimation,
            curve: Curves.easeOutCubic,
          ),
        );
    return SlideTransition(
      position: slideOut,
      child: SlideTransition(position: slideIn, child: child),
    );
  },
);

Route<T> fadeScaleRoute<T>(Widget page) => PageRouteBuilder<T>(
  pageBuilder: (_, animation, __) => page,
  transitionDuration: const Duration(milliseconds: 350),
  reverseTransitionDuration: const Duration(milliseconds: 280),
  transitionsBuilder: (_, animation, __, child) {
    final fade = Tween<double>(
      begin: 0.0,
      end: 1.0,
    ).animate(CurvedAnimation(parent: animation, curve: Curves.easeOut));
    final scale = Tween<double>(
      begin: 0.94,
      end: 1.0,
    ).animate(CurvedAnimation(parent: animation, curve: Curves.easeOutCubic));
    return FadeTransition(
      opacity: fade,
      child: ScaleTransition(scale: scale, child: child),
    );
  },
);

Route<T> slideUpRoute<T>(Widget page) => PageRouteBuilder<T>(
  pageBuilder: (_, animation, __) => page,
  transitionDuration: const Duration(milliseconds: 350),
  reverseTransitionDuration: const Duration(milliseconds: 280),
  transitionsBuilder: (_, animation, __, child) {
    final slide = Tween<Offset>(
      begin: const Offset(0.0, 1.0),
      end: Offset.zero,
    ).animate(CurvedAnimation(parent: animation, curve: Curves.easeOutCubic));
    final fade = Tween<double>(
      begin: 0.0,
      end: 1.0,
    ).animate(CurvedAnimation(parent: animation, curve: Curves.easeOut));
    return FadeTransition(
      opacity: fade,
      child: SlideTransition(position: slide, child: child),
    );
  },
);

Route<T> fadeRoute<T>(Widget page) => PageRouteBuilder<T>(
  pageBuilder: (_, animation, __) => page,
  transitionDuration: const Duration(milliseconds: 280),
  reverseTransitionDuration: const Duration(milliseconds: 220),
  transitionsBuilder: (_, animation, __, child) => FadeTransition(
    opacity: CurvedAnimation(parent: animation, curve: Curves.easeInOut),
    child: child,
  ),
);

class AppRoute {
  AppRoute._();
  static Route<T> slide<T>(Widget page) => slideRoute<T>(page);
  static Route<T> slideUp<T>(Widget page) => slideUpRoute<T>(page);
  static Route<T> fade<T>(Widget page) => fadeRoute<T>(page);
  static Route<T> fadeScale<T>(Widget page) => fadeScaleRoute<T>(page);
}

// ════════════════════════════════════════════════════════════════════════════
// WAVE PAINTER
// ════════════════════════════════════════════════════════════════════════════
class AppWavePainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final p1 = Paint()..color = Colors.white.withOpacity(0.08);
    canvas.drawPath(
      Path()
        ..moveTo(0, size.height * 0.6)
        ..quadraticBezierTo(
          size.width * 0.3,
          size.height * 0.45,
          size.width * 0.6,
          size.height * 0.65,
        )
        ..quadraticBezierTo(
          size.width * 0.8,
          size.height * 0.75,
          size.width,
          size.height * 0.55,
        )
        ..lineTo(size.width, size.height)
        ..lineTo(0, size.height),
      p1,
    );
    final p2 = Paint()..color = Colors.white.withOpacity(0.06);
    canvas.drawPath(
      Path()
        ..moveTo(0, size.height * 0.75)
        ..quadraticBezierTo(
          size.width * 0.4,
          size.height * 0.55,
          size.width * 0.7,
          size.height * 0.78,
        )
        ..quadraticBezierTo(
          size.width * 0.85,
          size.height * 0.88,
          size.width,
          size.height * 0.72,
        )
        ..lineTo(size.width, size.height)
        ..lineTo(0, size.height),
      p2,
    );
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
        child: Row(
          children: [
            Icon(
              icon,
              color: isActive ? Colors.white : const Color(0xFF4A5568),
              size: 22,
            ),
            const SizedBox(width: 16),
            Text(
              label,
              style: TextStyle(
                fontSize: 15,
                fontWeight: isActive ? FontWeight.w600 : FontWeight.w500,
                color: isActive ? Colors.white : const Color(0xFF4A5568),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ════════════════════════════════════════════════════════════════════════════
// APP SIDEBAR — nama, email, role, foto dari database via AuthService
// ════════════════════════════════════════════════════════════════════════════
class AppSidebar extends StatefulWidget {
  final VoidCallback onClose;
  final String activeMenu;
  final void Function(String) onMenuTap;

  /// Dipanggil setelah logout berhasil — biasanya navigasi ke LoginPage
  final VoidCallback onLogout;

  const AppSidebar({
    super.key,
    required this.onClose,
    required this.activeMenu,
    required this.onMenuTap,
    required this.onLogout,
  });

  static const _menuIcons = <String, IconData>{
    'Dashboard': Icons.home_rounded,
    'Pengguna': Icons.group_rounded,
    'Member': Icons.people_alt_rounded,
    'Laporan': Icons.show_chart_rounded,
    'Riwayat Transaksi': Icons.history_rounded,
    'Kasir': Icons.computer_rounded,
    'Produk': Icons.inventory_2_rounded,
    'Kategori': Icons.label_rounded,
    'Pengiriman': Icons.local_shipping_rounded,
    'Kendaraan': Icons.directions_car_rounded,
    'Profile': Icons.person_rounded,
  };

  @override
  State<AppSidebar> createState() => _AppSidebarState();
}

class _AppSidebarState extends State<AppSidebar> {
  // ── Data dari database (via SharedPreferences hasil login) ────────────────
  String _userName = '';
  String _userEmail = '';
  String _userRoleRaw = '';
  String _userRole = '';
  String? _photoUrl;
  bool _loggingOut = false;

  @override
  void initState() {
    super.initState();
    _loadUserData();
  }

  @override
  void dispose() {
    AppState.instance.userName.removeListener(_onUserDataChanged);
    AppState.instance.userEmail.removeListener(_onUserDataChanged);
    AppState.instance.userRole.removeListener(_onUserDataChanged);
    AppState.instance.userPhoto.removeListener(_onUserDataChanged);
    super.dispose();
  }

  /// Ambil data user dari AppState (sudah sync dengan API)
  void _loadUserData() {
    // AppState sudah fetch dari API — langsung ambil valuenya
    setState(() {
      _userName = AppState.instance.userName.value.trim();
      _userEmail = AppState.instance.userEmail.value.trim();
      _userRoleRaw = AppState.instance.userRole.value;
      _userRole = _formatRole(_userRoleRaw);
      _photoUrl = AppState.instance.userPhoto.value;
    });

    // Listen perubahan — kalau user edit profil di web, sidebar langsung update
    AppState.instance.userName.addListener(_onUserDataChanged);
    AppState.instance.userEmail.addListener(_onUserDataChanged);
    AppState.instance.userRole.addListener(_onUserDataChanged);
    AppState.instance.userPhoto.addListener(_onUserDataChanged);
  }

  void _onUserDataChanged() {
    if (!mounted) return;
    setState(() {
      _userName = AppState.instance.userName.value.trim();
      _userEmail = AppState.instance.userEmail.value.trim();
      _userRoleRaw = AppState.instance.userRole.value;
      _userRole = _formatRole(_userRoleRaw);
      _photoUrl = AppState.instance.userPhoto.value;
    });
  }

  /// Format role dari database ke label yang lebih ramah
  String _formatRole(String raw) {
    switch (raw.toLowerCase()) {
      case 'owner':
        return 'Owner';
      case 'manager':
        return 'Manager';
      case 'kasir':
        return 'Kasir';
      case 'kepala_gudang':
      case 'gudang':
        return 'Kepala Gudang';
      case 'checker':
      case 'checker_barang':
        return 'Checker Barang';
      case 'logistik':
      case 'staff_logistik':
        return 'Staff Logistik';
      case 'kurir':
        return 'Kurir';
      case 'admin':
        return 'Admin';
      default:
        return raw.isNotEmpty ? raw[0].toUpperCase() + raw.substring(1) : '-';
    }
  }

  List<String> _menusByRole() => RoleAccess.sidebarMenusForRole(_userRoleRaw);

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
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(8),
              ),
            ),
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Keluar'),
          ),
        ],
      ),
    );

    if (confirm != true || !mounted) return;

    setState(() => _loggingOut = true);
    await AppState.instance.logout(); // hapus token + bersihkan semua state
    if (!mounted) return;
    widget.onLogout(); // navigasi ke LoginPage — dihandle caller
  }

  // ── Avatar builder ────────────────────────────────────────────────────────
  Widget _buildAvatar() {
    // Jika ada foto dari database, tampilkan
    if (_photoUrl != null && _photoUrl!.isNotEmpty) {
      return CircleAvatar(
        radius: 38,
        backgroundColor: const Color(0xFFE8EDFF),
        backgroundImage: NetworkImage(_photoUrl!),
        onBackgroundImageError: (_, __) {},
      );
    }

    // Fallback: inisial nama dari database
    final initial = _avatarInitial(_userName);
    return CircleAvatar(
      radius: 38,
      backgroundColor: const Color(0xFFE8EDFF),
      child: Text(
        initial,
        style: const TextStyle(
          fontSize: 28,
          fontWeight: FontWeight.bold,
          color: Color(0xFF4169E1),
        ),
      ),
    );
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
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Text(
                          'close',
                          style: TextStyle(
                            color: Colors.grey.shade600,
                            fontSize: 14,
                          ),
                        ),
                        const SizedBox(width: 4),
                        Icon(
                          Icons.close,
                          color: Colors.grey.shade600,
                          size: 20,
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ),

            // ── Avatar + nama + email + role dari database ──
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _buildAvatar(),
                  const SizedBox(height: 12),

                  // Nama dari database
                  Text(
                    _userName.trim().isEmpty ? '...' : _userName.trim(),
                    style: const TextStyle(
                      fontSize: 20,
                      fontWeight: FontWeight.bold,
                      color: Color(0xFF2D3748),
                    ),
                  ),
                  const SizedBox(height: 2),

                  // Email dari database
                  Text(
                    _userEmail,
                    style: TextStyle(fontSize: 12, color: Colors.grey.shade500),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 4),

                  // Role dari database — badge kecil
                  if (_userRole.isNotEmpty)
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 10,
                        vertical: 3,
                      ),
                      decoration: BoxDecoration(
                        color: const Color(0xFFEBF4FF),
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: Text(
                        _userRole,
                        style: const TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w600,
                          color: Color(0xFF4169E1),
                        ),
                      ),
                    ),
                ],
              ),
            ),
            const SizedBox(height: 20),

            // ── Divider ──
            Divider(color: Colors.grey.shade100, height: 1),
            const SizedBox(height: 8),

            // ── Menu list ──
            Expanded(
              child: RepaintBoundary(
                child: ListView(
                  padding: const EdgeInsets.symmetric(horizontal: 16),
                  physics: const BouncingScrollPhysics(),
                  children: _menusByRole().map((label) {
                    final icon = AppSidebar._menuIcons[label] ?? Icons.circle;
                    return AppSidebarItem(
                      label: label,
                      icon: icon,
                      isActive: label == widget.activeMenu,
                      onTap: () => widget.onMenuTap(label),
                    );
                  }).toList(),
                ),
              ),
            ),

            // ── Tombol Keluar — fungsional ──
            Padding(
              padding: const EdgeInsets.fromLTRB(20, 8, 20, 24),
              child: SizedBox(
                width: double.infinity,
                child: ElevatedButton.icon(
                  onPressed: _loggingOut ? null : _handleLogout,
                  icon: _loggingOut
                      ? const SizedBox(
                          width: 18,
                          height: 18,
                          child: CircularProgressIndicator(
                            color: Colors.white,
                            strokeWidth: 2,
                          ),
                        )
                      : const Icon(
                          Icons.logout_rounded,
                          color: Colors.white,
                          size: 18,
                        ),
                  label: Text(
                    _loggingOut ? 'Keluar...' : 'Keluar',
                    style: const TextStyle(
                      color: Colors.white,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFFE53E3E),
                    padding: const EdgeInsets.symmetric(
                      horizontal: 24,
                      vertical: 12,
                    ),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(24),
                    ),
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
      vsync: vsync,
      duration: const Duration(milliseconds: 320),
      reverseDuration: const Duration(milliseconds: 260),
    );
    sidebarSlideAnim =
        Tween<Offset>(begin: const Offset(-1, 0), end: Offset.zero).animate(
          CurvedAnimation(
            parent: sidebarAnimController,
            curve: Curves.easeInOutCubic,
            reverseCurve: Curves.easeInCubic,
          ),
        );
    sidebarFadeAnim = Tween<double>(begin: 0, end: 0.45).animate(
      CurvedAnimation(
        parent: sidebarAnimController,
        curve: Curves.easeInOutCubic,
        reverseCurve: Curves.easeInCubic,
      ),
    );
  }

  void disposeSidebar() => sidebarAnimController.dispose();

  void openSidebar() {
    setState(() => sidebarOpen = true);
    sidebarAnimController.forward();
  }

  void closeSidebar({VoidCallback? then}) {
    sidebarAnimController.reverse().then((_) {
      if (!mounted) return;
      setState(() => sidebarOpen = false);
      then?.call();
    });
  }

  void closeSidebarThenNavigate(VoidCallback navigate) {
    closeSidebar(then: navigate);
  }

  /// [onLogout] opsional — default: navigasi ke LoginPage & hapus semua history
  List<Widget> buildSidebarLayer({
    required String activeMenu,
    required void Function(String) onMenuTap,
    VoidCallback? onLogout,
  }) {
    if (!sidebarOpen) return [];
    return [
      AnimatedBuilder(
        animation: sidebarFadeAnim,
        builder: (_, __) => GestureDetector(
          onTap: closeSidebar,
          behavior: HitTestBehavior.opaque,
          child: Container(
            color: Colors.black.withOpacity(sidebarFadeAnim.value),
          ),
        ),
      ),
      SlideTransition(
        position: sidebarSlideAnim,
        child: RepaintBoundary(
          child: AppSidebar(
            onClose: closeSidebar,
            activeMenu: activeMenu,
            onMenuTap: onMenuTap,
            onLogout: onLogout ?? _goToLogin,
          ),
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
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            _line(),
            const SizedBox(height: 5),
            _line(),
            const SizedBox(height: 5),
            _line(),
          ],
        ),
      ),
    );
  }

  Widget _line() => Container(
    width: 24,
    height: 3,
    decoration: BoxDecoration(
      color: Colors.white,
      borderRadius: BorderRadius.circular(2),
    ),
  );
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
      width: 150,
      margin: const EdgeInsets.only(right: 12),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.06),
            blurRadius: 8,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
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
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Icon(
                    Icons.arrow_forward_rounded,
                    size: 14,
                    color: Colors.grey.shade500,
                  ),
                ),
              ),
            ],
          ),
          const Spacer(),
          FittedBox(
            fit: BoxFit.scaleDown,
            alignment: Alignment.centerLeft,
            child: Text(
              value,
              style: const TextStyle(
                fontSize: 22,
                fontWeight: FontWeight.bold,
                color: Color(0xFF2D3748),
              ),
            ),
          ),
          Text(
            label,
            style: TextStyle(fontSize: 11, color: Colors.grey.shade500),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
        ],
      ),
    );
  }
}
