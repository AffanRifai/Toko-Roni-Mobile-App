import 'dart:convert';
import 'package:http/http.dart' as http;

class ApiService {

  static const baseUrl = "http://10.0.2.2:8000/api";

  static Future getProducts() async {

    var url = Uri.parse("$baseUrl/products");

    var response = await http.get(url);

    if (response.statusCode == 200) {

      var data = jsonDecode(response.body);

      return data['data'];

    } else {
      throw Exception("Failed to load products");
    }
  }
}