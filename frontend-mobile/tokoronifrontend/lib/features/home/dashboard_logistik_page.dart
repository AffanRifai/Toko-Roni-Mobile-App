import 'package:flutter/material.dart';

class DashboardLogistikPage extends StatelessWidget {
  const DashboardLogistikPage({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Dashboard Logistik'),
      ),
      body: const Center(
        child: Text(
          'Selamat datang di Dashboard Logistik!',
          style: TextStyle(
            fontSize: 24,
            fontWeight: FontWeight.bold,
          ),
        ),
      ),
    );
  }
}