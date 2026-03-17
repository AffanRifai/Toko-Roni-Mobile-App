import 'package:flutter/material.dart';

class TambahKategoriPage extends StatelessWidget {
  const TambahKategoriPage({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Tambah Kategori'),
      ),
      body: const Center(
        child: Text('Halaman Tambah Kategori'),
      ),
    );
  }
}