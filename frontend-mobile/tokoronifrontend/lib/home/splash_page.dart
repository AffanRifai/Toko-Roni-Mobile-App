// ============================================================
// lib/home/splash_page.dart
//
// CARA PAKAI DI main.dart:
//   import 'home/splash_page.dart';
//
//   class MyApp extends StatelessWidget {
//     @override
//     Widget build(BuildContext context) {
//       return MaterialApp(
//         home: const SplashPage(),  // ← ganti dari LoginPage
//         ...
//       );
//     }
//   }
// ============================================================

import 'package:flutter/material.dart';
import '../core/auth_service.dart';
import 'beranda_page.dart';        // BerandaPage
import '../auth/login_page.dart';  // LoginPage — sesuaikan path

class SplashPage extends StatefulWidget {
  const SplashPage({super.key});

  @override
  State<SplashPage> createState() => _SplashPageState();
}

class _SplashPageState extends State<SplashPage> {

  @override
  void initState() {
    super.initState();
    _checkAuthAndRoute();
  }

  Future<void> _checkAuthAndRoute() async {
    // Beri sedikit jeda agar splash terlihat
    await Future.delayed(const Duration(milliseconds: 800));

    if (!mounted) return;

    final loggedIn = await AuthService.isLoggedIn();

    if (loggedIn) {
      final name = await AuthService.getUserName();
      if (!mounted) return;
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(builder: (_) => BerandaPage(userName: name)),
      );
    } else {
      if (!mounted) return;
      Navigator.pushReplacement(
        context,
        MaterialPageRoute(builder: (_) => const LoginPage()),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF3B6FE8),
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            // Logo / nama app
            Container(
              width: 80,
              height: 80,
              decoration: BoxDecoration(
                color: Colors.white.withOpacity(0.2),
                borderRadius: BorderRadius.circular(20),
              ),
              child: const Icon(Icons.store_rounded, color: Colors.white, size: 44),
            ),
            const SizedBox(height: 20),
            const Text(
              'Toko Roni',
              style: TextStyle(
                color: Colors.white,
                fontSize: 28,
                fontWeight: FontWeight.bold,
                letterSpacing: 0.5,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'Juntinyuat',
              style: TextStyle(
                color: Colors.white.withOpacity(0.75),
                fontSize: 15,
              ),
            ),
            const SizedBox(height: 48),
            const SizedBox(
              width: 28,
              height: 28,
              child: CircularProgressIndicator(
                color: Colors.white,
                strokeWidth: 2.5,
              ),
            ),
          ],
        ),
      ),
    );
  }
}