import 'package:flutter/material.dart';

class DashboardCheckerPage extends StatelessWidget {
  const DashboardCheckerPage({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Dashboard Checker'),
      ),
      body: const Center(
        child: Text(
          'Selamat datang di Dashboard Checker!',
          style: TextStyle(
            fontSize: 24,
            fontWeight: FontWeight.bold,
          ),
        ),
      ),
    );
  }
}