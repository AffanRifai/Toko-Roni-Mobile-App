import 'dart:convert';

String nowIsoUtc() => DateTime.now().toUtc().toIso8601String();

DateTime? tryParseDate(dynamic value) {
  if (value == null) return null;
  final raw = value.toString().trim();
  if (raw.isEmpty) return null;
  return DateTime.tryParse(raw)?.toUtc();
}

bool isNetworkReachabilityError(Object error) {
  final msg = error.toString().toLowerCase();
  return msg.contains('timeout') ||
      msg.contains('koneksi') ||
      msg.contains('internet') ||
      msg.contains('server tidak dapat dijangkau') ||
      msg.contains('socketexception') ||
      msg.contains('clientexception');
}

String encodeJson(Map<String, dynamic> value) => jsonEncode(value);

Map<String, dynamic> decodeJsonObject(String? raw) {
  if (raw == null || raw.trim().isEmpty) return {};
  try {
    final decoded = jsonDecode(raw);
    if (decoded is Map<String, dynamic>) return decoded;
    if (decoded is Map) {
      return decoded.map((k, v) => MapEntry(k.toString(), v));
    }
  } catch (_) {}
  return {};
}

List<dynamic> decodeJsonList(String? raw) {
  if (raw == null || raw.trim().isEmpty) return const [];
  try {
    final decoded = jsonDecode(raw);
    if (decoded is List) return decoded;
  } catch (_) {}
  return const [];
}

int generateTempId() => -DateTime.now().microsecondsSinceEpoch;
