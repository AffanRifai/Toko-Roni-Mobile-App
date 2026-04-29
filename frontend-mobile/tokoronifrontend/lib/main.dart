import 'dart:async';

import 'package:flutter/material.dart';

import 'core/state/app_state.dart';
import 'core/ui/app_interaction_wrapper.dart';
import 'features/splash/splash_page.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();

  runApp(const MyApp());
  // Run global app state init in background to avoid blocking first frame.
  unawaited(AppState.instance.init());
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
        fontFamily: 'Poppins',
      ),
      home: const SplashPage(),
    );
  }
}
