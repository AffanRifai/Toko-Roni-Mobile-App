// ============================================================
// lib/auth/login_page.dart
// ============================================================

import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import 'dart:convert';

import '../core/api_config.dart';
import '../home/beranda_page.dart';
import 'face_login.dart'; // sesuaikan path jika berbeda
import '../product/tambah_produk_page.dart'; // sesuaikan path jika berbeda

class LoginPage extends StatefulWidget {
  const LoginPage({super.key});

  @override
  State<LoginPage> createState() => _LoginPageState();
}

class _LoginPageState extends State<LoginPage> {
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  bool _isLoading = false;
  bool _obscurePassword = true;

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  // ── Handle Login ─────────────────────────────────────────
  Future<void> _handleLogin() async {
    final email = _emailController.text.trim();
    final password = _passwordController.text.trim();

    if (email.isEmpty || password.isEmpty) {
      _showSnack('Email dan password tidak boleh kosong');
      return;
    }

    setState(() => _isLoading = true);

    try {
      final response = await http
          .post(
            Uri.parse(ApiConfig.login),
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
            },
            body: jsonEncode({'email': email, 'password': password}),
          )
          .timeout(const Duration(seconds: 15));

      final data = jsonDecode(response.body) as Map<String, dynamic>;

      if (response.statusCode == 200 && data['status'] == true) {
        // ── Simpan token & info user ke SharedPreferences ──
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString('auth_token', data['token'] as String);

        final user = data['user'] as Map<String, dynamic>;
        await prefs.setString('user_name', user['name'] as String? ?? '');
        await prefs.setString('user_email', user['email'] as String? ?? '');
        await prefs.setString('user_role', user['role'] as String? ?? '');

        if (!mounted) return;

        // ── Navigasi ke dashboard ──────────────────────────
        Navigator.pushReplacement(
          context,
          MaterialPageRoute(
            builder: (_) =>
                BerandaPage(userName: user['name'] as String? ?? 'User'),
          ),
        );
      } else {
        // 401 email/password salah | 403 user tidak aktif
        _showSnack(data['message'] as String? ?? 'Login gagal');
      }
    } on Exception catch (e) {
      // Timeout, no internet, dll.
      _showSnack('Gagal terhubung ke server. Periksa koneksi kamu.');
      debugPrint('Login error: $e');
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  // ── Handle Face Login ────────────────────────────────────
  void _handleFaceLogin() {
    Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => const FaceLoginPage()),
    );
  }

  void _showSnack(String msg) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(msg)));
  }

  // ════════════════════════════════════════════════════════
  // BUILD
  // ════════════════════════════════════════════════════════
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Stack(
        children: [
          _BackgroundCityscape(),
          SafeArea(
            child: Center(
              child: SingleChildScrollView(
                child: Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 24),
                  child: ConstrainedBox(
                    constraints: const BoxConstraints(maxWidth: 420),
                    child: Container(
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(24),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withOpacity(0.12),
                            blurRadius: 20,
                            offset: const Offset(0, 8),
                          ),
                        ],
                      ),
                      padding: const EdgeInsets.fromLTRB(28, 32, 28, 32),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.center,
                        children: [
                          const Text(
                            'Login',
                            style: TextStyle(
                              fontSize: 26,
                              fontWeight: FontWeight.w600,
                              color: Color(0xFF2D3748),
                              letterSpacing: 0.3,
                            ),
                          ),
                          const SizedBox(height: 12),
                          const Icon(
                            Icons.person_outline_rounded,
                            size: 52,
                            color: Color(0xFF4A5568),
                          ),
                          const SizedBox(height: 28),

                          // ── Email ──
                          _buildLabel('email'),
                          const SizedBox(height: 6),
                          _buildTextField(
                            controller: _emailController,
                            hint: 'masukan email anda',
                            inputType: TextInputType.emailAddress,
                          ),
                          const SizedBox(height: 16),

                          // ── Password ──
                          _buildLabel('password'),
                          const SizedBox(height: 6),
                          _buildTextField(
                            controller: _passwordController,
                            hint: 'masukan password anda',
                            obscureText: _obscurePassword,
                            suffixIcon: IconButton(
                              icon: Icon(
                                _obscurePassword
                                    ? Icons.visibility_off_outlined
                                    : Icons.visibility_outlined,
                                color: Colors.grey.shade400,
                                size: 20,
                              ),
                              onPressed: () => setState(
                                () => _obscurePassword = !_obscurePassword,
                              ),
                            ),
                          ),
                          const SizedBox(height: 24),

                          // ── Tombol Login ──
                          SizedBox(
                            width: double.infinity,
                            child: OutlinedButton(
                              onPressed: _isLoading ? null : _handleLogin,
                              style: OutlinedButton.styleFrom(
                                padding: const EdgeInsets.symmetric(
                                  vertical: 14,
                                ),
                                side: const BorderSide(
                                  color: Color(0xFF2D3748),
                                  width: 1.5,
                                ),
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(30),
                                ),
                                backgroundColor: Colors.white,
                              ),
                              child: _isLoading
                                  ? const SizedBox(
                                      height: 20,
                                      width: 20,
                                      child: CircularProgressIndicator(
                                        strokeWidth: 2,
                                        color: Color(0xFF2D3748),
                                      ),
                                    )
                                  : const Text(
                                      'Login',
                                      style: TextStyle(
                                        fontSize: 15,
                                        fontWeight: FontWeight.w600,
                                        color: Color(0xFF2D3748),
                                      ),
                                    ),
                            ),
                          ),
                          const SizedBox(height: 20),

                          // ── Divider ──
                          Row(
                            children: [
                              Expanded(
                                child: Divider(color: Colors.grey.shade300),
                              ),
                              Padding(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 8,
                                ),
                                child: Text(
                                  '-atau-',
                                  style: TextStyle(
                                    fontSize: 12,
                                    color: Colors.grey.shade500,
                                  ),
                                ),
                              ),
                              Expanded(
                                child: Divider(color: Colors.grey.shade300),
                              ),
                            ],
                          ),
                          const SizedBox(height: 10),

                          // ── Face Login ──
                          Text(
                            'Login dengan pengenalan wajah',
                            style: TextStyle(
                              fontSize: 13,
                              color: Colors.grey.shade600,
                            ),
                          ),
                          const SizedBox(height: 12),
                          GestureDetector(
                            onTap: _handleFaceLogin,
                            child: Column(
                              children: [
                                Text(
                                  'klik disini',
                                  style: TextStyle(
                                    fontSize: 11,
                                    color: Colors.grey.shade500,
                                    decoration: TextDecoration.underline,
                                  ),
                                ),
                                const SizedBox(height: 6),
                                Container(
                                  width: 56,
                                  height: 56,
                                  decoration: BoxDecoration(
                                    border: Border.all(
                                      color: Colors.grey.shade400,
                                      width: 1.5,
                                    ),
                                    borderRadius: BorderRadius.circular(12),
                                  ),
                                  child: Icon(
                                    Icons.face_outlined,
                                    size: 32,
                                    color: Colors.grey.shade500,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  // ── Helper widgets ────────────────────────────────────────
  Widget _buildLabel(String text) => Align(
    alignment: Alignment.centerLeft,
    child: Text(
      text,
      style: TextStyle(
        fontSize: 13,
        color: Colors.grey.shade600,
        fontWeight: FontWeight.w500,
      ),
    ),
  );

  Widget _buildTextField({
    required TextEditingController controller,
    required String hint,
    TextInputType inputType = TextInputType.text,
    bool obscureText = false,
    Widget? suffixIcon,
  }) {
    return TextField(
      controller: controller,
      keyboardType: inputType,
      obscureText: obscureText,
      decoration: InputDecoration(
        hintText: hint,
        hintStyle: TextStyle(color: Colors.grey.shade400, fontSize: 13),
        filled: true,
        fillColor: const Color(0xFFF5F7FA),
        suffixIcon: suffixIcon,
        contentPadding: const EdgeInsets.symmetric(
          horizontal: 18,
          vertical: 14,
        ),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(30),
          borderSide: BorderSide.none,
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(30),
          borderSide: BorderSide(color: Colors.grey.shade200, width: 1),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(30),
          borderSide: const BorderSide(color: Color(0xFF4DA8DA), width: 1.5),
        ),
      ),
    );
  }
}

// ════════════════════════════════════════════════════════════
// BACKGROUND CITYSCAPE (tidak berubah dari versi asli)
// ════════════════════════════════════════════════════════════
class _BackgroundCityscape extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return SizedBox(
      height: MediaQuery.of(context).size.height * 0.45,
      width: double.infinity,
      child: CustomPaint(painter: _CityscapePainter()),
    );
  }
}

class _CityscapePainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final skyPaint = Paint()
      ..shader = const LinearGradient(
        begin: Alignment.topCenter,
        end: Alignment.bottomCenter,
        colors: [Color(0xFF87CEEB), Color(0xFF4FC3F7), Color(0xFF29B6F6)],
      ).createShader(Rect.fromLTWH(0, 0, size.width, size.height));
    canvas.drawRect(Rect.fromLTWH(0, 0, size.width, size.height), skyPaint);

    _drawCloud(canvas, size, 0.08, 0.08, 0.18);
    _drawCloud(canvas, size, 0.38, 0.05, 0.14);
    _drawCloud(canvas, size, 0.62, 0.10, 0.20);
    _drawCloud(canvas, size, 0.82, 0.06, 0.13);
    _drawCloud(canvas, size, 0.20, 0.18, 0.16);
    _drawCloud(canvas, size, 0.50, 0.20, 0.12);

    _drawBuildings(
      canvas,
      size,
      Paint()..color = const Color(0xFF1565C0),
      layer: 0,
    );
    _drawBuildings(
      canvas,
      size,
      Paint()..color = const Color(0xFF1976D2),
      layer: 1,
    );
    _drawBuildings(
      canvas,
      size,
      Paint()..color = const Color(0xFF2196F3),
      layer: 2,
    );

    canvas.drawRect(
      Rect.fromLTWH(0, size.height * 0.85, size.width, size.height * 0.15),
      Paint()..color = const Color(0xFF1565C0),
    );
  }

  void _drawCloud(Canvas c, Size s, double xR, double yR, double wR) {
    final p = Paint()..color = Colors.white.withOpacity(0.85);
    final cx = s.width * xR;
    final cy = s.height * yR;
    final w = s.width * wR;
    final h = w * 0.35;
    c.drawOval(Rect.fromCenter(center: Offset(cx, cy), width: w, height: h), p);
    c.drawCircle(Offset(cx - w * 0.25, cy), h * 0.65, p);
    c.drawCircle(Offset(cx, cy - h * 0.3), h * 0.7, p);
    c.drawCircle(Offset(cx + w * 0.22, cy), h * 0.55, p);
  }

  void _drawBuildings(
    Canvas canvas,
    Size size,
    Paint paint, {
    required int layer,
  }) {
    for (final b in _getBuildingData(layer)) {
      final left = size.width * b[0];
      final width = size.width * b[1];
      final top = size.height * (1 - b[2]);
      canvas.drawRect(
        Rect.fromLTWH(left, top, width, size.height * b[2]),
        paint,
      );
      _drawWindows(canvas, left, top, width, size.height * b[2]);
    }
  }

  void _drawWindows(Canvas c, double l, double t, double w, double h) {
    final p = Paint()..color = Colors.white.withOpacity(0.25);
    double y = t + 10;
    while (y + 4 < t + h - 10) {
      double x = l + 8;
      while (x + 4 < l + w - 8) {
        c.drawRect(Rect.fromLTWH(x, y, 4, 4), p);
        x += 8;
      }
      y += 8;
    }
  }

  List<List<double>> _getBuildingData(int layer) {
    switch (layer) {
      case 0:
        return [
          [0.0, 0.08, 0.45],
          [0.10, 0.06, 0.40],
          [0.18, 0.09, 0.50],
          [0.28, 0.07, 0.38],
          [0.36, 0.08, 0.52],
          [0.45, 0.06, 0.42],
          [0.52, 0.09, 0.48],
          [0.62, 0.07, 0.44],
          [0.70, 0.08, 0.55],
          [0.79, 0.07, 0.40],
          [0.87, 0.07, 0.46],
          [0.92, 0.08, 0.35],
        ];
      case 1:
        return [
          [0.0, 0.10, 0.55],
          [0.12, 0.08, 0.48],
          [0.22, 0.11, 0.62],
          [0.34, 0.09, 0.50],
          [0.44, 0.10, 0.58],
          [0.55, 0.08, 0.44],
          [0.64, 0.11, 0.60],
          [0.76, 0.09, 0.52],
          [0.86, 0.08, 0.46],
          [0.92, 0.08, 0.40],
        ];
      case 2:
        return [
          [0.0, 0.12, 0.65],
          [0.14, 0.10, 0.55],
          [0.26, 0.13, 0.70],
          [0.40, 0.11, 0.60],
          [0.52, 0.12, 0.68],
          [0.65, 0.10, 0.58],
          [0.76, 0.12, 0.63],
          [0.89, 0.11, 0.50],
        ];
      default:
        return [];
    }
  }

  @override
  bool shouldRepaint(covariant CustomPainter _) => false;
}
