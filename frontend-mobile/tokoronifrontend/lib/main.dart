import 'package:flutter/material.dart';
import 'auth/login_page.dart';
import 'home/splash_page.dart';

void main() {
  runApp(MyApp());
}

class MyApp extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Toko Roni App',
      debugShowCheckedModeBanner: false,
      home: const SplashPage(),
    );
  }
}