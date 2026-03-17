import 'package:flutter/material.dart';

class FaceLogin extends StatelessWidget {

  @override
  Widget build(BuildContext context) {
    return Scaffold(

      appBar: AppBar(
        title: Text("Face Login"),
      ),

      body: Center(
        child: Text(
          "Login Berhasil!",
          style: TextStyle(fontSize: 24),
        ),
      ),

    );
  }
}