import 'dart:async';
import 'dart:collection';
import 'dart:convert';

import 'package:http/http.dart' as http;
import 'package:web_socket_channel/io.dart';
import 'package:web_socket_channel/web_socket_channel.dart';

import '../config/api_config.dart';
import 'auth_service.dart';

typedef RealtimeNotificationHandler = void Function(Map<String, dynamic> raw);

class RealtimeNotificationService {
  RealtimeNotificationService._();
  static final RealtimeNotificationService instance =
      RealtimeNotificationService._();

  WebSocketChannel? _channel;
  StreamSubscription? _subscription;
  RealtimeNotificationHandler? _onNotification;

  bool _connected = false;
  String? _activeUserId;
  String? _channelName;
  String? _token;
  _RealtimeConfig? _config;
  String? _socketId;
  Future<void>? _connectInFlight;
  static const int _recentNotificationCacheLimit = 300;
  final Set<String> _recentNotificationIds = <String>{};
  final ListQueue<String> _recentNotificationOrder = ListQueue<String>();

  bool get isConnected => _connected;

  Future<void> connect({
    required String userId,
    required RealtimeNotificationHandler onNotification,
  }) async {
    final inFlight = _connectInFlight;
    if (inFlight != null) return inFlight;

    final trimmedUserId = userId.trim();
    if (trimmedUserId.isEmpty) return;

    final completer = Completer<void>();
    _connectInFlight = completer.future;

    try {
      final token = await AuthService.getToken();
      if (token == null || token.trim().isEmpty) return;

      final nextChannelName = 'private-App.Models.User.$trimmedUserId';
      final alreadyConnected =
          _connected &&
          _channel != null &&
          _activeUserId == trimmedUserId &&
          _channelName == nextChannelName;

      _token = token;
      _onNotification = onNotification;
      if (alreadyConnected) return;

      if (_activeUserId != null && _activeUserId != trimmedUserId) {
        _recentNotificationIds.clear();
        _recentNotificationOrder.clear();
      }

      _activeUserId = trimmedUserId;
      _channelName = nextChannelName;

      _config ??= await _loadRealtimeConfig(token: token);
      if (_config == null || _config!.appKey.isEmpty) return;

      await disconnect();
      await _openSocket();
    } finally {
      _connectInFlight = null;
      if (!completer.isCompleted) completer.complete();
    }
  }

  Future<void> disconnect() async {
    _connected = false;
    _socketId = null;
    await _subscription?.cancel();
    _subscription = null;
    try {
      await _channel?.sink.close();
    } catch (_) {}
    _channel = null;
  }

  Future<void> _openSocket() async {
    final cfg = _config;
    final token = _token;
    final channelName = _channelName;
    if (cfg == null || token == null || channelName == null) return;

    final uri = Uri(
      scheme: cfg.useTls ? 'wss' : 'ws',
      host: cfg.wsHost,
      port: cfg.wsPort,
      path: '/app/${cfg.appKey}',
      queryParameters: {
        'protocol': '7',
        'client': 'flutter',
        'version': '1.0.0',
        'flash': 'false',
      },
    );

    _channel = IOWebSocketChannel.connect(uri);
    _subscription = _channel!.stream.listen(
      (dynamic raw) async {
        await _handleSocketMessage(raw);
      },
      onDone: () {
        _connected = false;
      },
      onError: (_) {
        _connected = false;
      },
      cancelOnError: false,
    );
  }

  Future<void> _handleSocketMessage(dynamic raw) async {
    final envelope = _decodeMap(raw);
    if (envelope.isEmpty) return;

    final event = (envelope['event'] ?? '').toString();

    if (event == 'pusher:connection_established') {
      final data = _decodeMap(envelope['data']);
      final socketId = (data['socket_id'] ?? '').toString().trim();
      if (socketId.isEmpty) return;

      _socketId = socketId;
      _connected = true;
      await _authorizeAndSubscribe();
      return;
    }

    if (event == 'pusher:ping') {
      _channel?.sink.add(jsonEncode({'event': 'pusher:pong', 'data': {}}));
      return;
    }

    if (event == 'notification.created') {
      final payload = _decodeMap(envelope['data']);
      final notification = _decodeMap(payload['notification']).isNotEmpty
          ? _decodeMap(payload['notification'])
          : payload;
      if (notification.isEmpty) return;
      final notificationId = (notification['id'] ?? '').toString().trim();
      if (_isDuplicateNotificationEvent(notificationId)) return;
      _onNotification?.call(notification);
    }
  }

  bool _isDuplicateNotificationEvent(String notificationId) {
    if (notificationId.isEmpty) return false;
    if (_recentNotificationIds.contains(notificationId)) return true;

    _recentNotificationIds.add(notificationId);
    _recentNotificationOrder.addLast(notificationId);
    if (_recentNotificationOrder.length > _recentNotificationCacheLimit) {
      final oldestId = _recentNotificationOrder.removeFirst();
      _recentNotificationIds.remove(oldestId);
    }
    return false;
  }

  Future<void> _authorizeAndSubscribe() async {
    final token = _token;
    final cfg = _config;
    final channelName = _channelName;
    final socketId = _socketId;
    if (token == null ||
        cfg == null ||
        channelName == null ||
        socketId == null) {
      return;
    }

    try {
      final res = await http
          .post(
            Uri.parse(cfg.authEndpoint),
            headers: {
              'Authorization': 'Bearer $token',
              'Accept': 'application/json',
              'Content-Type': 'application/json',
            },
            body: jsonEncode({
              'socket_id': socketId,
              'channel_name': channelName,
            }),
          )
          .timeout(const Duration(seconds: 10));

      if (res.statusCode < 200 || res.statusCode >= 300) return;
      final body = _decodeMap(res.body);
      final auth = (body['auth'] ?? '').toString().trim();
      if (auth.isEmpty) return;

      _channel?.sink.add(
        jsonEncode({
          'event': 'pusher:subscribe',
          'data': {'channel': channelName, 'auth': auth},
        }),
      );
    } catch (_) {}
  }

  Future<_RealtimeConfig?> _loadRealtimeConfig({required String token}) async {
    try {
      final res = await http
          .get(
            Uri.parse(ApiConfig.realtimeConfig),
            headers: {
              'Authorization': 'Bearer $token',
              'Accept': 'application/json',
            },
          )
          .timeout(const Duration(seconds: 10));

      if (res.statusCode >= 200 && res.statusCode < 300) {
        final body = jsonDecode(res.body);
        if (body is Map<String, dynamic>) {
          final data = _decodeMap(body['data']);
          final appKey = (data['app_key'] ?? '').toString().trim();
          final wsHost = (data['ws_host'] ?? '').toString().trim();
          final wsPort = int.tryParse('${data['ws_port'] ?? ''}') ?? 8080;
          final scheme = (data['scheme'] ?? 'http').toString().toLowerCase();
          final authEndpoint = (data['auth_endpoint'] ?? '').toString().trim();

          if (appKey.isNotEmpty &&
              wsHost.isNotEmpty &&
              authEndpoint.isNotEmpty) {
            return _RealtimeConfig(
              appKey: appKey,
              wsHost: wsHost,
              wsPort: wsPort,
              useTls: scheme == 'https',
              authEndpoint: authEndpoint,
            );
          }
        }
      }
    } catch (_) {}

    return null;
  }

  Map<String, dynamic> _decodeMap(dynamic raw) {
    if (raw is Map<String, dynamic>) return raw;
    if (raw is Map) {
      return raw.map((k, v) => MapEntry(k.toString(), v));
    }
    if (raw is String && raw.trim().isNotEmpty) {
      try {
        final decoded = jsonDecode(raw);
        if (decoded is Map<String, dynamic>) return decoded;
        if (decoded is Map) {
          return decoded.map((k, v) => MapEntry(k.toString(), v));
        }
      } catch (_) {}
    }
    return {};
  }
}

class _RealtimeConfig {
  const _RealtimeConfig({
    required this.appKey,
    required this.wsHost,
    required this.wsPort,
    required this.useTls,
    required this.authEndpoint,
  });

  final String appKey;
  final String wsHost;
  final int wsPort;
  final bool useTls;
  final String authEndpoint;
}
