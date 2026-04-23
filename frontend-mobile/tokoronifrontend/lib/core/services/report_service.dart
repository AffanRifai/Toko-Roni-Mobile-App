import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;
import 'package:open_filex/open_filex.dart';
import 'package:path_provider/path_provider.dart';
import '../config/api_config.dart';
import 'auth_service.dart';

class ReportExportResult {
  final String filePath;

  const ReportExportResult({required this.filePath});
}

class ReportService {
  static Future<ReportExportResult> exportSalesPdf({
    String? date,
    String? month,
    String sort = 'latest',
  }) async {
    final query = <String, String>{'type': 'sales', 'sort': sort};
    if (date != null && date.trim().isNotEmpty) {
      query['date'] = date.trim();
    } else if (month != null && month.trim().isNotEmpty) {
      query['month'] = month.trim();
    }

    final uri = Uri.parse(
      ApiConfig.reportExportPdf,
    ).replace(queryParameters: query);

    final headers = await AuthService.authHeaders();
    headers['Accept'] = 'application/pdf,application/json';

    final response = await http
        .get(uri, headers: headers)
        .timeout(const Duration(seconds: 35));

    if (response.statusCode == 401) {
      throw Exception('Sesi login habis, silakan login ulang');
    }

    if (response.statusCode < 200 || response.statusCode >= 300) {
      throw Exception(
        _extractErrorMessage(response, 'Gagal export PDF laporan'),
      );
    }

    final contentType = response.headers['content-type']?.toLowerCase() ?? '';
    if (contentType.contains('application/json')) {
      throw Exception(
        _extractErrorMessage(response, 'Server belum mengirim file PDF'),
      );
    }

    final bytes = response.bodyBytes;
    if (bytes.isEmpty) {
      throw Exception('File PDF kosong');
    }

    final fileName = _resolveFileName(response.headers['content-disposition']);
    final dir = await getTemporaryDirectory();
    final file = File('${dir.path}/$fileName');
    await file.writeAsBytes(bytes, flush: true);

    return ReportExportResult(filePath: file.path);
  }

  static Future<bool> openPdf(String path) async {
    final result = await OpenFilex.open(path);
    return result.type == ResultType.done;
  }

  static String _resolveFileName(String? disposition) {
    final fallback =
        'laporan-penjualan-${DateTime.now().millisecondsSinceEpoch}.pdf';
    if (disposition == null || disposition.trim().isEmpty) return fallback;

    final utf8Match = RegExp(
      "filename\\*=UTF-8''([^;]+)",
      caseSensitive: false,
    ).firstMatch(disposition);
    if (utf8Match != null) {
      final encoded = utf8Match.group(1) ?? '';
      final decoded = Uri.decodeFull(encoded).trim();
      if (decoded.isNotEmpty) return decoded.replaceAll('"', '');
    }

    final plainMatch = RegExp(
      'filename="?([^";]+)"?',
      caseSensitive: false,
    ).firstMatch(disposition);
    if (plainMatch != null) {
      final plain = plainMatch.group(1)?.trim() ?? '';
      if (plain.isNotEmpty) return plain;
    }

    return fallback;
  }

  static String _extractErrorMessage(http.Response response, String fallback) {
    try {
      final parsed = jsonDecode(response.body);
      if (parsed is Map<String, dynamic>) {
        final message = parsed['message']?.toString().trim() ?? '';
        final error = parsed['error']?.toString().trim() ?? '';
        if (message.isNotEmpty) return message;
        if (error.isNotEmpty) return error;
      }
    } catch (_) {}
    return '$fallback (HTTP ${response.statusCode})';
  }
}
