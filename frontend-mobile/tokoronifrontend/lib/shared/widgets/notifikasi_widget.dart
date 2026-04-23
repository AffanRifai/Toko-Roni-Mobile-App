// ============================================================
// lib/widgets/notifikasi_widget.dart
// ============================================================

import 'package:flutter/material.dart';
import '../../core/services/notifikasi_service.dart';
import '../../core/state/app_state.dart';

// Re-export NotifItem supaya file lain tetap bisa import dari sini
export '../../core/services/notifikasi_service.dart'
    show NotifItem, NotifikasiService;

// ════════════════════════════════════════════════════════════════════════════
// NOTIFIKASI BELL — load dari database, auto-refresh setiap buka dropdown
// ════════════════════════════════════════════════════════════════════════════
class NotifikasiBell extends StatefulWidget {
  final VoidCallback onLihatSemua;

  const NotifikasiBell({super.key, required this.onLihatSemua});

  @override
  State<NotifikasiBell> createState() => _NotifikasiBellState();
}

class _NotifikasiBellState extends State<NotifikasiBell>
    with SingleTickerProviderStateMixin {
  OverlayEntry? _overlay;
  bool _isOpen = false;

  final _bellKey = GlobalKey();
  static const double _dropdownWidth = 320;

  late AnimationController _animCtrl;
  late Animation<double> _fadeAnim;
  late Animation<Offset> _slideAnim;

  // Ambil langsung dari AppState — tidak perlu state lokal
  List<NotifItem> get _notifList => AppState.instance.notifications.value;
  bool get _loading => AppState.instance.notifLoading.value;
  int get _unreadCount => AppState.instance.unreadCount.value;

  @override
  void initState() {
    super.initState();
    _animCtrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 220),
      reverseDuration: const Duration(milliseconds: 160),
    );
    _fadeAnim = CurvedAnimation(parent: _animCtrl, curve: Curves.easeOut);
    _slideAnim = Tween<Offset>(
      begin: const Offset(0, -0.05),
      end: Offset.zero,
    ).animate(CurvedAnimation(parent: _animCtrl, curve: Curves.easeOut));

    // Listen AppState untuk update badge count
    AppState.instance.unreadCount.addListener(_onNotifChanged);
  }

  @override
  void dispose() {
    AppState.instance.unreadCount.removeListener(_onNotifChanged);
    _animCtrl.dispose();
    _overlay?.remove();
    _overlay = null;
    super.dispose();
  }

  void _onNotifChanged() {
    if (mounted) setState(() {});
    _overlay?.markNeedsBuild();
  }

  Future<void> _loadAll() async {
    await AppState.instance.refreshNotifications(force: true);
    _overlay?.markNeedsBuild();
    if (mounted) setState(() {});
  }

  void _toggleDropdown() {
    if (_isOpen) {
      _closeDropdown();
    } else {
      _openDropdown();
    }
  }

  void _openDropdown() {
    if (_notifList.isEmpty && !_loading) {
      _loadAll();
    }
    _overlay = _buildOverlay();
    Overlay.of(context).insert(_overlay!);
    _animCtrl.forward();
    setState(() => _isOpen = true);
  }

  void _closeDropdown() {
    _animCtrl.reverse().then((_) {
      _overlay?.remove();
      _overlay = null;
    });
    if (mounted) setState(() => _isOpen = false);
  }

  Future<void> _markRead(String id) async {
    await AppState.instance.markNotifRead(id);
    _overlay?.markNeedsBuild();
    if (mounted) setState(() {});
  }

  Future<void> _markAllRead() async {
    await AppState.instance.markAllRead();
    _overlay?.markNeedsBuild();
    if (mounted) setState(() {});
  }

  OverlayEntry _buildOverlay() {
    final renderBox = _bellKey.currentContext?.findRenderObject() as RenderBox?;
    final screenSize = MediaQuery.of(context).size;
    double top = 80, right = 16;

    if (renderBox != null) {
      final offset = renderBox.localToGlobal(Offset.zero);
      final size = renderBox.size;
      top = offset.dy + size.height + 8;
      right = screenSize.width - (offset.dx + size.width);
      final leftEdge = screenSize.width - right - _dropdownWidth;
      if (leftEdge < 12) right = screenSize.width - _dropdownWidth - 12;
    }

    return OverlayEntry(
      builder: (_) => GestureDetector(
        onTap: _closeDropdown,
        behavior: HitTestBehavior.translucent,
        child: Stack(
          children: [
            Positioned.fill(child: Container(color: Colors.transparent)),
            Positioned(
              top: top,
              right: right,
              width: _dropdownWidth,
              child: FadeTransition(
                opacity: _fadeAnim,
                child: SlideTransition(
                  position: _slideAnim,
                  child: Material(
                    color: Colors.transparent,
                    child: _NotifDropdown(
                      notifList: _notifList.take(6).toList(),
                      unreadCount: _unreadCount,
                      isLoading: _loading,
                      onClose: _closeDropdown,
                      onLihatSemua: () {
                        _closeDropdown();
                        widget.onLihatSemua();
                      },
                      onMarkRead: _markRead,
                      onMarkAllRead: _markAllRead,
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

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: _toggleDropdown,
      child: Stack(
        clipBehavior: Clip.none,
        children: [
          Container(
            key: _bellKey,
            width: 42,
            height: 42,
            decoration: BoxDecoration(
              color: _isOpen
                  ? Colors.white.withValues(alpha: 0.35)
                  : Colors.white24,
              borderRadius: BorderRadius.circular(12),
            ),
            child: const Icon(
              Icons.notifications_rounded,
              color: Colors.white,
              size: 22,
            ),
          ),
          if (_unreadCount > 0)
            Positioned(
              top: -4,
              right: -4,
              child: Container(
                width: 18,
                height: 18,
                decoration: BoxDecoration(
                  color: const Color(0xFFE53E3E),
                  shape: BoxShape.circle,
                  border: Border.all(color: Colors.white, width: 1.5),
                ),
                child: Center(
                  child: Text(
                    _unreadCount > 9 ? '9+' : '$_unreadCount',
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 9,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
              ),
            ),
        ],
      ),
    );
  }
}

// ════════════════════════════════════════════════════════════════════════════
// DROPDOWN CARD
// ════════════════════════════════════════════════════════════════════════════
class _NotifDropdown extends StatelessWidget {
  final List<NotifItem> notifList;
  final int unreadCount;
  final bool isLoading;
  final VoidCallback onClose;
  final VoidCallback onLihatSemua;
  final Future<void> Function(String) onMarkRead;
  final Future<void> Function() onMarkAllRead;

  const _NotifDropdown({
    required this.notifList,
    required this.unreadCount,
    required this.isLoading,
    required this.onClose,
    required this.onLihatSemua,
    required this.onMarkRead,
    required this.onMarkAllRead,
  });

  @override
  Widget build(BuildContext context) {
    final unread = unreadCount;

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.15),
            blurRadius: 20,
            offset: const Offset(0, 8),
          ),
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 6,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          // ── Header ──
          Container(
            padding: const EdgeInsets.fromLTRB(16, 14, 12, 12),
            decoration: const BoxDecoration(
              color: Color(0xFF3B6FE8),
              borderRadius: BorderRadius.vertical(top: Radius.circular(16)),
            ),
            child: Row(
              children: [
                const Icon(
                  Icons.notifications_rounded,
                  color: Colors.white,
                  size: 18,
                ),
                const SizedBox(width: 8),
                const Text(
                  'Notifikasi',
                  style: TextStyle(
                    color: Colors.white,
                    fontSize: 15,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(width: 6),
                if (unread > 0)
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 7,
                      vertical: 2,
                    ),
                    decoration: BoxDecoration(
                      color: const Color(0xFFE53E3E),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Text(
                      '$unread baru',
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 10,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ),
                if (unread > 0) ...[
                  const SizedBox(width: 8),
                  GestureDetector(
                    onTap: () => onMarkAllRead(),
                    child: Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 8,
                        vertical: 3,
                      ),
                      decoration: BoxDecoration(
                        color: Colors.white.withValues(alpha: 0.2),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: const Text(
                        'Baca semua',
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 10,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ),
                  ),
                ],
                const Spacer(),
                GestureDetector(
                  onTap: onClose,
                  child: Container(
                    padding: const EdgeInsets.all(4),
                    decoration: BoxDecoration(
                      color: Colors.white.withValues(alpha: 0.2),
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: const Icon(
                      Icons.close_rounded,
                      color: Colors.white,
                      size: 16,
                    ),
                  ),
                ),
              ],
            ),
          ),

          // ── Body: loading / kosong / list ──
          if (isLoading)
            const Padding(
              padding: EdgeInsets.symmetric(vertical: 28),
              child: Center(
                child: CircularProgressIndicator(
                  color: Color(0xFF3B6FE8),
                  strokeWidth: 2.5,
                ),
              ),
            )
          else if (notifList.isEmpty)
            Padding(
              padding: const EdgeInsets.symmetric(vertical: 28),
              child: Column(
                children: [
                  Icon(
                    Icons.notifications_none_rounded,
                    size: 40,
                    color: Colors.grey.shade300,
                  ),
                  const SizedBox(height: 8),
                  Text(
                    'Tidak ada notifikasi',
                    style: TextStyle(fontSize: 13, color: Colors.grey.shade500),
                  ),
                ],
              ),
            )
          else
            ...notifList.map(
              (n) => _NotifDropdownTile(
                item: n,
                onMarkRead: () => onMarkRead(n.id),
              ),
            ),

          // ── Footer ──
          GestureDetector(
            onTap: onLihatSemua,
            child: Container(
              width: double.infinity,
              padding: const EdgeInsets.symmetric(vertical: 13),
              decoration: const BoxDecoration(
                color: Color(0xFFF7F8FF),
                borderRadius: BorderRadius.vertical(
                  bottom: Radius.circular(16),
                ),
                border: Border(top: BorderSide(color: Color(0xFFE8ECFF))),
              ),
              child: const Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Text(
                    'Lihat semua notifikasi',
                    style: TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w600,
                      color: Color(0xFF3B6FE8),
                    ),
                  ),
                  SizedBox(width: 6),
                  Icon(
                    Icons.arrow_forward_rounded,
                    size: 14,
                    color: Color(0xFF3B6FE8),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}

// ── Tile di dropdown ──────────────────────────────────────────────────────────
class _NotifDropdownTile extends StatelessWidget {
  final NotifItem item;
  final VoidCallback onMarkRead;
  const _NotifDropdownTile({required this.item, required this.onMarkRead});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: item.sudahDibaca ? null : onMarkRead,
      child: Container(
        padding: const EdgeInsets.fromLTRB(12, 10, 12, 10),
        decoration: BoxDecoration(
          color: item.sudahDibaca ? Colors.white : const Color(0xFFF0F4FF),
          border: const Border(bottom: BorderSide(color: Color(0xFFF0F0F0))),
        ),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: item.iconColor.withValues(alpha: 0.12),
                borderRadius: BorderRadius.circular(9),
              ),
              child: Icon(item.icon, color: item.iconColor, size: 18),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Expanded(
                        child: Text(
                          item.judul,
                          style: TextStyle(
                            fontSize: 12,
                            fontWeight: item.sudahDibaca
                                ? FontWeight.w500
                                : FontWeight.bold,
                            color: const Color(0xFF2D3748),
                          ),
                        ),
                      ),
                      if (!item.sudahDibaca)
                        Container(
                          width: 7,
                          height: 7,
                          decoration: const BoxDecoration(
                            color: Color(0xFF3B6FE8),
                            shape: BoxShape.circle,
                          ),
                        ),
                    ],
                  ),
                  const SizedBox(height: 2),
                  Text(
                    item.pesan,
                    style: TextStyle(
                      fontSize: 11,
                      color: Colors.grey.shade600,
                      height: 1.3,
                    ),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 4),
                  Row(
                    children: [
                      Icon(
                        Icons.access_time_rounded,
                        size: 10,
                        color: Colors.grey.shade400,
                      ),
                      const SizedBox(width: 3),
                      Text(
                        item.waktu,
                        style: TextStyle(
                          fontSize: 10,
                          color: Colors.grey.shade400,
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ════════════════════════════════════════════════════════════════════════════
// NOTIF TILE — dipakai di halaman semua notifikasi
// ════════════════════════════════════════════════════════════════════════════
class NotifTile extends StatelessWidget {
  final NotifItem item;
  final VoidCallback? onTap;
  const NotifTile({super.key, required this.item, this.onTap});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: item.sudahDibaca ? Colors.white : const Color(0xFFEBF4FF),
          borderRadius: BorderRadius.circular(14),
          border: Border.all(
            color: item.sudahDibaca
                ? Colors.grey.shade100
                : const Color(0xFFBEE3F8),
          ),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.04),
              blurRadius: 6,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(
                color: item.iconColor.withValues(alpha: 0.12),
                borderRadius: BorderRadius.circular(10),
              ),
              child: Icon(item.icon, color: item.iconColor, size: 22),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Expanded(
                        child: Text(
                          item.judul,
                          style: TextStyle(
                            fontSize: 13,
                            fontWeight: item.sudahDibaca
                                ? FontWeight.w500
                                : FontWeight.bold,
                            color: const Color(0xFF2D3748),
                          ),
                        ),
                      ),
                      if (!item.sudahDibaca)
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 7,
                            vertical: 2,
                          ),
                          decoration: BoxDecoration(
                            color: const Color(0xFF3B6FE8),
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: const Text(
                            'Baru',
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: 10,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                        ),
                    ],
                  ),
                  const SizedBox(height: 4),
                  Text(
                    item.pesan,
                    style: TextStyle(
                      fontSize: 12,
                      color: Colors.grey.shade600,
                      height: 1.4,
                    ),
                  ),
                  const SizedBox(height: 6),
                  Row(
                    children: [
                      Icon(
                        Icons.access_time_rounded,
                        size: 12,
                        color: Colors.grey.shade400,
                      ),
                      const SizedBox(width: 4),
                      Text(
                        item.waktu,
                        style: TextStyle(
                          fontSize: 11,
                          color: Colors.grey.shade400,
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
