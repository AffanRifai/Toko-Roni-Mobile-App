import 'package:flutter/material.dart';

class FaceLoginPage extends StatelessWidget {
  const FaceLoginPage({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text("Face Login"),
      ),
      body: const Center(
        child: Text("Halaman Face Recognition"),
      ),
    );
  }
}