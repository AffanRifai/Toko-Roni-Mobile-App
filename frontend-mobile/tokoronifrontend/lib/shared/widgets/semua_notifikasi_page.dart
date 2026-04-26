// ============================================================
// lib/widgets/semua_notifikasi_page.dart
// ============================================================

import 'package:flutter/material.dart';
import 'notifikasi_widget.dart';
import '../../core/state/app_state.dart';

class SemuaNotifikasiPage extends StatefulWidget {
  const SemuaNotifikasiPage({super.key});

  @override
  State<SemuaNotifikasiPage> createState() => _SemuaNotifikasiPageState();
}

class _SemuaNotifikasiPageState extends State<SemuaNotifikasiPage>
    with SingleTickerProviderStateMixin {
  late TabController _tabCtrl;
  List<NotifItem> _list = [];
  bool _isLoading = true;
  bool _hasError = false;

  static const _blue = Color(0xFF3B6FE8);

  // ── Filter tipe untuk tab "Kategori" ─────────────────────────────────────

  @override
  void initState() {
    super.initState();
    _tabCtrl = TabController(length: 3, vsync: this);
    _loadData();
  }

  @override
  void dispose() {
    _tabCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadData() async {
    if (!mounted) return;
    setState(() {
      _isLoading = true;
      _hasError = false;
    });
    try {
      await AppState.instance.refreshNotifications(force: true);
      if (mounted) {
        setState(() {
          _list = List.from(AppState.instance.notifications.value);
          _isLoading = false;
        });
      }
    } catch (_) {
      if (mounted) {
        setState(() {
          _isLoading = false;
          _hasError = true;
        });
      }
    }
  }

  List<NotifItem> get _semua => _list;
  List<NotifItem> get _belumDibaca =>
      _list.where((n) => !n.sudahDibaca).toList();
  List<NotifItem> get _penting => _list
      .where(
        (n) =>
            n.isImportant ||
            ['stock', 'expiry', 'receivable', 'report'].contains(n.tipe),
      )
      .toList();
  int get _unreadCount => _belumDibaca.length;

  // ── Mark as read ────────────────────────────────────────────────────────
  Future<void> _tandaiDibaca(String id) async {
    await AppState.instance.markNotifRead(id);
    if (mounted) {
      setState(() {
        _list = List.from(AppState.instance.notifications.value);
      });
    }
  }

  Future<void> _tandaiSemuaDibaca() async {
    await AppState.instance.markAllRead();
    if (mounted) {
      setState(() {
        _list = List.from(AppState.instance.notifications.value);
      });
    }
    _snack('Semua notifikasi ditandai sudah dibaca', const Color(0xFF48BB78));
  }

  Future<void> _hapus(String id) async {
    await AppState.instance.deleteNotif(id);
    if (mounted) {
      setState(() {
        _list = List.from(AppState.instance.notifications.value);
      });
    }
    _snack('Notifikasi dihapus', Colors.red);
  }

  Future<void> _hapusSemua() async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Hapus Semua Notifikasi'),
        content: const Text('Yakin ingin menghapus semua notifikasi?'),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Batal'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFFE53E3E),
              foregroundColor: Colors.white,
            ),
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Hapus Semua'),
          ),
        ],
      ),
    );
    if (confirm != true) return;
    await AppState.instance.clearAllNotif();
    if (mounted) setState(() => _list.clear());
    _snack('Semua notifikasi dihapus', Colors.red);
  }

  void _snack(String msg, Color color) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(msg),
        backgroundColor: color,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF3F4F8),
      appBar: AppBar(
        backgroundColor: _blue,
        foregroundColor: Colors.white,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new_rounded),
          onPressed: () => Navigator.pop(context),
        ),
        title: Row(
          children: [
            const Text(
              'Notifikasi',
              style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18),
            ),
            const SizedBox(width: 8),
            if (_unreadCount > 0)
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: const Color(0xFFE53E3E),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Text(
                  '$_unreadCount',
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 12,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
          ],
        ),
        actions: [
          // Refresh
          IconButton(
            icon: const Icon(Icons.refresh_rounded, color: Colors.white),
            onPressed: _loadData,
            tooltip: 'Refresh',
          ),
          // Menu: baca semua / hapus semua
          PopupMenuButton<String>(
            icon: const Icon(Icons.more_vert_rounded, color: Colors.white),
            color: Colors.white,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(12),
            ),
            onSelected: (val) {
              if (val == 'baca_semua') _tandaiSemuaDibaca();
              if (val == 'hapus_semua') _hapusSemua();
            },
            itemBuilder: (_) => [
              if (_unreadCount > 0)
                const PopupMenuItem(
                  value: 'baca_semua',
                  child: Row(
                    children: [
                      Icon(
                        Icons.done_all_rounded,
                        size: 18,
                        color: Color(0xFF3B6FE8),
                      ),
                      SizedBox(width: 10),
                      Text('Tandai semua dibaca'),
                    ],
                  ),
                ),
              if (_list.isNotEmpty)
                const PopupMenuItem(
                  value: 'hapus_semua',
                  child: Row(
                    children: [
                      Icon(
                        Icons.delete_sweep_rounded,
                        size: 18,
                        color: Color(0xFFE53E3E),
                      ),
                      SizedBox(width: 10),
                      Text(
                        'Hapus semua',
                        style: TextStyle(color: Color(0xFFE53E3E)),
                      ),
                    ],
                  ),
                ),
            ],
          ),
        ],
        bottom: TabBar(
          controller: _tabCtrl,
          indicatorColor: Colors.white,
          indicatorWeight: 3,
          labelColor: Colors.white,
          unselectedLabelColor: Colors.white70,
          labelStyle: const TextStyle(
            fontWeight: FontWeight.w600,
            fontSize: 13,
          ),
          unselectedLabelStyle: const TextStyle(fontSize: 13),
          tabs: [
            Tab(text: 'Semua (${_semua.length})'),
            Tab(
              text: 'Belum Dibaca${_unreadCount > 0 ? ' ($_unreadCount)' : ''}',
            ),
            Tab(text: 'Penting (${_penting.length})'),
          ],
        ),
      ),
      body: _isLoading
          ? const Center(
              child: CircularProgressIndicator(color: Color(0xFF3B6FE8)),
            )
          : _hasError
          ? _ErrorView(onRetry: _loadData)
          : TabBarView(
              controller: _tabCtrl,
              children: [
                _NotifList(
                  items: _semua,
                  onMarkRead: _tandaiDibaca,
                  onHapus: _hapus,
                  onRefresh: _loadData,
                ),
                _NotifList(
                  items: _belumDibaca,
                  onMarkRead: _tandaiDibaca,
                  onHapus: _hapus,
                  onRefresh: _loadData,
                  emptyMessage: 'Semua notifikasi sudah dibaca',
                  emptyIcon: Icons.notifications_active_rounded,
                ),
                _NotifList(
                  items: _penting,
                  onMarkRead: _tandaiDibaca,
                  onHapus: _hapus,
                  onRefresh: _loadData,
                  emptyMessage: 'Tidak ada notifikasi penting',
                  emptyIcon: Icons.notifications_off_rounded,
                ),
              ],
            ),
    );
  }
}

// ── Error view ────────────────────────────────────────────────────────────────
class _ErrorView extends StatelessWidget {
  final VoidCallback onRetry;
  const _ErrorView({required this.onRetry});

  @override
  Widget build(BuildContext context) => Center(
    child: Column(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        Icon(Icons.cloud_off_rounded, size: 64, color: Colors.grey.shade400),
        const SizedBox(height: 16),
        const Text(
          'Gagal memuat notifikasi',
          style: TextStyle(fontSize: 15, color: Color(0xFF2D3748)),
        ),
        const SizedBox(height: 8),
        Text(
          'Periksa koneksi ke server',
          style: TextStyle(fontSize: 12, color: Colors.grey.shade500),
        ),
        const SizedBox(height: 24),
        ElevatedButton.icon(
          onPressed: onRetry,
          icon: const Icon(Icons.refresh_rounded),
          label: const Text('Coba Lagi'),
          style: ElevatedButton.styleFrom(
            backgroundColor: const Color(0xFF3B6FE8),
            foregroundColor: Colors.white,
          ),
        ),
      ],
    ),
  );
}

// ════════════════════════════════════════════════════════════════════════════
// LIST WIDGET
// ════════════════════════════════════════════════════════════════════════════
class _NotifList extends StatelessWidget {
  final List<NotifItem> items;
  final Future<void> Function(String) onMarkRead;
  final Future<void> Function(String) onHapus;
  final Future<void> Function() onRefresh;
  final String emptyMessage;
  final IconData emptyIcon;

  const _NotifList({
    required this.items,
    required this.onMarkRead,
    required this.onHapus,
    required this.onRefresh,
    this.emptyMessage = 'Belum ada notifikasi',
    this.emptyIcon = Icons.notifications_none_rounded,
  });

  @override
  Widget build(BuildContext context) {
    if (items.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              width: 80,
              height: 80,
              decoration: BoxDecoration(
                color: Colors.grey.shade100,
                shape: BoxShape.circle,
              ),
              child: Icon(emptyIcon, size: 40, color: Colors.grey.shade400),
            ),
            const SizedBox(height: 16),
            Text(
              emptyMessage,
              style: const TextStyle(
                fontSize: 15,
                fontWeight: FontWeight.w600,
                color: Color(0xFF2D3748),
              ),
            ),
            const SizedBox(height: 6),
            Text(
              'Tidak ada notifikasi untuk ditampilkan',
              style: TextStyle(fontSize: 12, color: Colors.grey.shade500),
            ),
          ],
        ),
      );
    }

    return RefreshIndicator(
      color: const Color(0xFF3B6FE8),
      onRefresh: onRefresh,
      child: ListView.builder(
        padding: const EdgeInsets.only(top: 12, bottom: 24),
        itemCount: items.length,
        itemBuilder: (_, i) {
          final n = items[i];
          return Dismissible(
            key: Key(n.id),
            direction: DismissDirection.endToStart,
            background: Container(
              margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
              decoration: BoxDecoration(
                color: const Color(0xFFE53E3E),
                borderRadius: BorderRadius.circular(14),
              ),
              alignment: Alignment.centerRight,
              padding: const EdgeInsets.only(right: 20),
              child: const Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.delete_rounded, color: Colors.white, size: 24),
                  SizedBox(height: 4),
                  Text(
                    'Hapus',
                    style: TextStyle(
                      color: Colors.white,
                      fontSize: 11,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                ],
              ),
            ),
            confirmDismiss: (_) async => true,
            onDismissed: (_) => onHapus(n.id),
            child: NotifTile(
              item: n,
              onTap: n.sudahDibaca ? null : () => onMarkRead(n.id),
            ),
          );
        },
      ),
    );
  }
}
