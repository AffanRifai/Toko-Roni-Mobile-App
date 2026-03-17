import 'dart:convert';
import 'package:http/http.dart' as http;

class AuthService {

  static const String baseUrl = "http://10.0.2.2:8000/api/v1";

  static Future login(String email, String password) async {

    var url = Uri.parse("$baseUrl/login");

    var response = await http.post(
      url,
      headers: {
        "Accept": "application/json",
        "Content-Type": "application/json"
      },
      body: jsonEncode({
        "email": email,
        "password": password
      }),
    );

    var data = jsonDecode(response.body);

    if (response.statusCode == 200) {
      return data;
    } else {
      throw Exception(data["message"] ?? "Login gagal");
    }
  }
}