// ============================================================
// lib/main.dart — tambahkan inisialisasi AppState
// ============================================================

import 'package:flutter/material.dart';
import 'core/state/app_state.dart';
import 'core/ui/app_interaction_wrapper.dart';
import 'features/splash/splash_page.dart'; // SplashPage cek token & route ke login/dashboard

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // Init AppState — load cache dulu, fetch API di background
  await AppState.instance.init();

  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Toko Roni',
      debugShowCheckedModeBanner: false,
      scrollBehavior: const AppScrollBehavior(),
      builder: (context, child) =>
          AppInteractionWrapper(child: child ?? const SizedBox.shrink()),
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(seedColor: const Color(0xFF3B6FE8)),
        useMaterial3: true,
        fontFamily: 'Poppins', // ganti sesuai font project kamu
      ),
      home: const SplashPage(),
    );
  }
}
