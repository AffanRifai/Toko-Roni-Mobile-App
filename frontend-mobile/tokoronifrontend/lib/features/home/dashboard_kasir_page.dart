import 'package:flutter/material.dart';

class DashboardKasirPage extends StatelessWidget {
  const DashboardKasirPage({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Dashboard Kasir'),
      ),
      body: const Center(
        child: Text(
          'Selamat datang di Dashboard Kasir!',
          style: TextStyle(
            fontSize: 24,
            fontWeight: FontWeight.bold,
          ),
        ),
      ),
    );
  }
}