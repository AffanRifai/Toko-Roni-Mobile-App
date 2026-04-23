import 'package:flutter/material.dart';

class DashboardGudangPage extends StatelessWidget {
  const DashboardGudangPage({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Dashboard Gudang'),
      ),
      body: const Center(
        child: Text(
          'Selamat datang di Dashboard Gudang!',
          style: TextStyle(
            fontSize: 24,
            fontWeight: FontWeight.bold,
          ),
        ),
      ),
    );
  }
}