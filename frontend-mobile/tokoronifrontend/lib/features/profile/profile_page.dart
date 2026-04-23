import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';

import '../../core/state/app_state.dart';
import '../auth/login_page.dart';

String _profileInitial(String? rawName) {
  final text = (rawName ?? '').trim();
  if (text.isEmpty) return '?';
  for (final rune in text.runes) {
    final char = String.fromCharCode(rune);
    if (RegExp(r'[A-Za-z0-9]').hasMatch(char)) return char.toUpperCase();
  }
  return text.substring(0, 1).toUpperCase();
}

class ProfilePage extends StatefulWidget {
  const ProfilePage({super.key});

  @override
  State<ProfilePage> createState() => _ProfilePageState();
}

class _ProfilePageState extends State<ProfilePage> {
  static const _blue = Color(0xFF3B6FE8);

  bool _loggingOut = false;

  @override
  void initState() {
    super.initState();
    AppState.instance.refreshProfile();
  }

  String _formatRole(String raw) {
    switch (raw.toLowerCase()) {
      case 'owner':
        return 'Owner';
      case 'kasir':
        return 'Kasir';
      case 'kepala_gudang':
        return 'Kepala Gudang';
      case 'checker':
        return 'Checker';
      case 'logistik':
        return 'Logistik';
      case 'kurir':
        return 'Kurir';
      case 'admin':
        return 'Admin';
      default:
        if (raw.isEmpty) return '-';
        return raw[0].toUpperCase() + raw.substring(1);
    }
  }

  String _formatJoinedAt(String raw) {
    final value = raw.trim();
    if (value.isEmpty) return '-';

    final iso = DateTime.tryParse(value);
    if (iso != null) return _fmtDateTime(iso.toLocal());

    final match = RegExp(
      r'^(\d{2})/(\d{2})/(\d{4})(?:\s+(\d{2}):(\d{2})(?::\d{2})?)?$',
    ).firstMatch(value);
    if (match != null) {
      final day = int.tryParse(match.group(1) ?? '');
      final month = int.tryParse(match.group(2) ?? '');
      final year = int.tryParse(match.group(3) ?? '');
      final hour = int.tryParse(match.group(4) ?? '0');
      final minute = int.tryParse(match.group(5) ?? '0');
      if (day != null &&
          month != null &&
          year != null &&
          hour != null &&
          minute != null) {
        return _fmtDateTime(DateTime(year, month, day, hour, minute));
      }
    }

    return value;
  }

  String _fmtDateTime(DateTime dt) {
    final dd = dt.day.toString().padLeft(2, '0');
    final mm = dt.month.toString().padLeft(2, '0');
    final hh = dt.hour.toString().padLeft(2, '0');
    final mi = dt.minute.toString().padLeft(2, '0');
    return '$dd/$mm/${dt.year} $hh:$mi';
  }

  Future<void> _handleLogout() async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        contentPadding: const EdgeInsets.fromLTRB(24, 20, 24, 0),
        actionsPadding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 64,
              height: 64,
              decoration: BoxDecoration(
                color: const Color(0xFFE53E3E).withOpacity(0.10),
                shape: BoxShape.circle,
              ),
              child: const Icon(
                Icons.logout_rounded,
                color: Color(0xFFE53E3E),
                size: 32,
              ),
            ),
            const SizedBox(height: 16),
            const Text(
              'Konfirmasi Keluar',
              style: TextStyle(
                fontSize: 17,
                fontWeight: FontWeight.bold,
                color: Color(0xFF2D3748),
              ),
            ),
            const SizedBox(height: 10),
            Text(
              'Apakah kamu yakin ingin keluar dari aplikasi?',
              textAlign: TextAlign.center,
              style: TextStyle(
                fontSize: 13,
                color: Colors.grey.shade600,
                height: 1.5,
              ),
            ),
            const SizedBox(height: 20),
          ],
        ),
        actions: [
          _dialogRow(
            onBatal: () => Navigator.pop(context),
            onConfirm: () => Navigator.pop(context, true),
            confirmLabel: 'Ya, Keluar',
            confirmColor: const Color(0xFFE53E3E),
          ),
        ],
      ),
    );

    if (confirm != true || !mounted) return;

    setState(() => _loggingOut = true);
    await AppState.instance.logout();
    if (!mounted) return;
    Navigator.of(context).pushAndRemoveUntil(
      MaterialPageRoute(builder: (_) => const LoginPage()),
      (_) => false,
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
        shape: const RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(bottom: Radius.circular(20)),
        ),
        title: const Text(
          'Profile',
          style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
        ),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new_rounded),
          onPressed: () => Navigator.pop(context),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.fromLTRB(0, 20, 0, 40),
        child: Column(
          children: [
            _buildProfileCard(),
            const SizedBox(height: 20),
            _buildLogoutButton(),
          ],
        ),
      ),
    );
  }

  Widget _buildProfileCard() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.06),
              blurRadius: 10,
              offset: const Offset(0, 3),
            ),
          ],
        ),
        child: Column(
          children: [
            Container(
              width: double.infinity,
              padding: const EdgeInsets.fromLTRB(20, 24, 20, 24),
              decoration: const BoxDecoration(
                gradient: LinearGradient(
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                  colors: [Color(0xFF4169E1), Color(0xFF6A8FFF)],
                ),
                borderRadius: BorderRadius.vertical(top: Radius.circular(16)),
              ),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.center,
                children: [
                  ValueListenableBuilder<String?>(
                    valueListenable: AppState.instance.userPhoto,
                    builder: (_, photo, __) {
                      if (photo != null && photo.isNotEmpty) {
                        return CircleAvatar(
                          radius: 48,
                          backgroundColor: Colors.white30,
                          backgroundImage: NetworkImage(photo),
                          onBackgroundImageError: (_, __) {},
                        );
                      }
                      return ValueListenableBuilder<String>(
                        valueListenable: AppState.instance.userName,
                        builder: (_, name, __) => CircleAvatar(
                          radius: 48,
                          backgroundColor: Colors.white30,
                          child: Text(
                            _profileInitial(name),
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 36,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                      );
                    },
                  ),
                  const SizedBox(width: 16),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        ValueListenableBuilder<String>(
                          valueListenable: AppState.instance.userName,
                          builder: (_, name, __) => Text(
                            name.trim().isEmpty ? '...' : name.trim(),
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                              height: 1.2,
                            ),
                            maxLines: 2,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                        const SizedBox(height: 8),
                        ValueListenableBuilder<String>(
                          valueListenable: AppState.instance.userRole,
                          builder: (_, role, __) => role.isEmpty
                              ? const SizedBox.shrink()
                              : Container(
                                  padding: const EdgeInsets.symmetric(
                                    horizontal: 12,
                                    vertical: 4,
                                  ),
                                  decoration: BoxDecoration(
                                    color: Colors.white.withOpacity(0.3),
                                    borderRadius: BorderRadius.circular(20),
                                  ),
                                  child: Text(
                                    _formatRole(role),
                                    style: const TextStyle(
                                      color: Colors.white,
                                      fontSize: 12,
                                      fontWeight: FontWeight.w600,
                                    ),
                                  ),
                                ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            Padding(
              padding: const EdgeInsets.fromLTRB(20, 24, 20, 24),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _buildInfoRow(
                    icon: Icons.email_outlined,
                    label: 'Email',
                    valueListenable: AppState.instance.userEmail,
                  ),
                  const SizedBox(height: 20),
                  _buildInfoRow(
                    icon: Icons.phone_outlined,
                    label: 'Kontak',
                    valueListenable: AppState.instance.userPhone,
                  ),
                  const SizedBox(height: 20),
                  ValueListenableBuilder<String>(
                    valueListenable: AppState.instance.userAddress,
                    builder: (_, address, __) => _buildInfoRowStatic(
                      icon: Icons.location_on_outlined,
                      label: 'Alamat',
                      value: address.isEmpty ? '-' : address,
                      maxLines: 3,
                    ),
                  ),
                  const SizedBox(height: 20),
                  ValueListenableBuilder<String>(
                    valueListenable: AppState.instance.userRole,
                    builder: (_, role, __) => _buildInfoRowStatic(
                      icon: Icons.admin_panel_settings_outlined,
                      label: 'Role',
                      value: role.isEmpty ? '-' : _formatRole(role),
                    ),
                  ),
                  const SizedBox(height: 20),
                  ValueListenableBuilder<String>(
                    valueListenable: AppState.instance.userJoinedAt,
                    builder: (_, joinedAt, __) => _buildInfoRowStatic(
                      icon: Icons.calendar_today_outlined,
                      label: 'Waktu Bergabung',
                      value: _formatJoinedAt(joinedAt),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildInfoRow({
    required IconData icon,
    required String label,
    required ValueListenable<String> valueListenable,
  }) {
    return ValueListenableBuilder<String>(
      valueListenable: valueListenable,
      builder: (_, value, __) => _buildInfoRowStatic(
        icon: icon,
        label: label,
        value: value.isEmpty ? '-' : value,
      ),
    );
  }

  Widget _buildInfoRowStatic({
    required IconData icon,
    required String label,
    required String value,
    int maxLines = 2,
  }) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Container(
          width: 40,
          height: 40,
          decoration: BoxDecoration(
            color: const Color(0xFFEBF4FF),
            borderRadius: BorderRadius.circular(10),
          ),
          child: Icon(icon, size: 20, color: const Color(0xFF4169E1)),
        ),
        const SizedBox(width: 14),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Text(
                label,
                style: TextStyle(
                  fontSize: 12,
                  color: Colors.grey.shade500,
                  fontWeight: FontWeight.w500,
                ),
              ),
              const SizedBox(height: 4),
              Text(
                value,
                style: const TextStyle(
                  fontSize: 14,
                  color: Color(0xFF2D3748),
                  fontWeight: FontWeight.w600,
                ),
                maxLines: maxLines,
                overflow: TextOverflow.ellipsis,
              ),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildLogoutButton() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: SizedBox(
        width: double.infinity,
        child: ElevatedButton.icon(
          onPressed: _loggingOut ? null : _handleLogout,
          icon: _loggingOut
              ? const SizedBox(
                  width: 20,
                  height: 20,
                  child: CircularProgressIndicator(
                    color: Colors.white,
                    strokeWidth: 2,
                  ),
                )
              : const Icon(Icons.logout_rounded, size: 18),
          label: Text(
            _loggingOut ? 'Keluar...' : 'Keluar',
            style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w600),
          ),
          style: ElevatedButton.styleFrom(
            backgroundColor: const Color(0xFFE53E3E),
            foregroundColor: Colors.white,
            padding: const EdgeInsets.symmetric(vertical: 14),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(12),
            ),
            elevation: 0,
          ),
        ),
      ),
    );
  }
}

Widget _dialogRow({
  required VoidCallback onBatal,
  required VoidCallback onConfirm,
  required String confirmLabel,
  required Color confirmColor,
}) => Row(
  children: [
    Expanded(
      child: OutlinedButton(
        onPressed: onBatal,
        style: OutlinedButton.styleFrom(
          side: BorderSide(color: Colors.grey.shade300),
          padding: const EdgeInsets.symmetric(vertical: 12),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(10),
          ),
        ),
        child: const Text(
          'Batal',
          style: TextStyle(color: Colors.grey, fontWeight: FontWeight.w600),
        ),
      ),
    ),
    const SizedBox(width: 10),
    Expanded(
      child: ElevatedButton(
        onPressed: onConfirm,
        style: ElevatedButton.styleFrom(
          backgroundColor: confirmColor,
          foregroundColor: Colors.white,
          padding: const EdgeInsets.symmetric(vertical: 12),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(10),
          ),
          elevation: 0,
        ),
        child: Text(
          confirmLabel,
          style: const TextStyle(fontWeight: FontWeight.w600),
        ),
      ),
    ),
  ],
);
