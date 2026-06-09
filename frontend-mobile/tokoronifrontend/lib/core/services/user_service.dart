import 'dart:async';
import 'dart:convert';
import 'dart:io';
import 'dart:math' as math;
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../config/api_config.dart';
import '../offline/offline_utils.dart';
import '../offline/sync_manager.dart';
import '../offline/sync_queue_repository.dart';
import '../offline/sync_types.dart';
import '../offline/user_local_repository.dart';
import 'auth_service.dart';
import 'notification_refresh_helper.dart';

class UserRecord {
  final int id;
  final String kode;
  final String nama;
  final String email;
  final String role;
  final String jenisToko;
  final bool aktif;
  final String telepon;
  final String alamat;
  final String bergabungRaw;

  const UserRecord({
    required this.id,
    required this.kode,
    required this.nama,
    required this.email,
    required this.role,
    required this.jenisToko,
    required this.aktif,
    required this.telepon,
    required this.alamat,
    required this.bergabungRaw,
  });

  factory UserRecord.fromJson(Map<String, dynamic> json) {
    final id = _toInt(json['id'] ?? json['user_id']);
    final name =
        (json['name'] ??
                json['nama'] ??
                json['full_name'] ??
                json['user_name'] ??
                '')
            .toString()
            .trim();

    final code =
        (json['code'] ??
                json['kode'] ??
                json['employee_code'] ??
                json['user_code'] ??
                '')
            .toString()
            .trim();

    return UserRecord(
      id: id,
      kode: code.isEmpty ? _fallbackKode(id) : code,
      nama: name,
      email: (json['email'] ?? '').toString().trim(),
      role:
          (json['role'] ??
                  json['role_name'] ??
                  json['jabatan'] ??
                  json['position'] ??
                  '')
              .toString()
              .trim(),
      jenisToko:
          (json['jenis_toko'] ??
                  json['store_type'] ??
                  json['jenisToko'] ??
                  json['toko_type'] ??
                  json['shop_type'] ??
                  '')
              .toString()
              .trim(),
      aktif: _toBool(
        json['is_active'] ??
            json['aktif'] ??
            json['active'] ??
            json['status'] ??
            json['isActive'],
        defaultValue: true,
      ),
      telepon:
          (json['phone'] ??
                  json['telepon'] ??
                  json['no_telepon'] ??
                  json['nomor_telepon'] ??
                  '')
              .toString()
              .trim(),
      alamat:
          (json['address'] ??
                  json['alamat'] ??
                  json['domisili'] ??
                  json['residence'] ??
                  '')
              .toString()
              .trim(),
      bergabungRaw:
          (json['joined_at'] ??
                  json['created_at'] ??
                  json['bergabung'] ??
                  json['registered_at'] ??
                  '')
              .toString()
              .trim(),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'kode': kode,
      'name': nama,
      'email': email,
      'role': role,
      'jenis_toko': jenisToko,
      'is_active': aktif ? 1 : 0,
      'phone': telepon,
      'address': alamat,
      'created_at': bergabungRaw,
    };
  }
}

class UserService {
  static const String _usersCacheKey = 'user_list_cache_v1';

  static Future<List<UserRecord>> getUsers({int perPage = 200}) async {
    Exception? lastError;
    Exception? prioritizedError;
    var primaryAllNotFound = true;
    for (final url in _primaryIndexCandidates(perPage)) {
      try {
        return await _fetchUsersByUrl(url);
      } catch (e) {
        final err = _asException(e);
        lastError = err;
        if (!_isHttp404Exception(err)) {
          primaryAllNotFound = false;
          prioritizedError = err;
        }
      }
    }

    if (primaryAllNotFound) {
      for (final url in _legacyIndexCandidates(perPage)) {
        try {
          return await _fetchUsersByUrl(url);
        } catch (e) {
          final err = _asException(e);
          lastError = err;
          if (!_isHttp404Exception(err)) {
            prioritizedError = err;
          }
        }
      }
    }

    final localRows = await UserLocalRepository.instance.getUsers();
    if (localRows.isNotEmpty && isNetworkReachabilityError(lastError ?? Exception(''))) {
      return localRows;
    }

    final cached = await _loadUsersCache();
    if (cached.isNotEmpty) {
      return cached;
    }

    throw prioritizedError ??
        lastError ??
        Exception('Gagal memuat data pengguna');
  }

  static Future<UserRecord> createUser({
    required String nama,
    required String email,
    required String password,
    required String role,
    required String jenisToko,
    required bool aktif,
    String? telepon,
    String? alamat,
  }) async {
    final cleanName = nama.trim();
    final cleanEmail = email.trim();
    final cleanRole = role.trim();
    final cleanJenisToko = jenisToko.trim();
    final cleanPhone = (telepon ?? '').trim();
    final cleanAddress = (alamat ?? '').trim();
    final cleanPassword = password.trim();
    final payload = <String, dynamic>{
      'name': cleanName,
      'email': cleanEmail,
      'role': cleanRole,
      'jenis_toko': cleanJenisToko,
      'is_active': aktif ? 1 : 0,
      'password': cleanPassword,
      'password_confirmation': cleanPassword,
      if (cleanPhone.isNotEmpty) 'phone': cleanPhone,
      if (cleanAddress.isNotEmpty) 'address': cleanAddress,
    };

    Exception? lastError;
    for (final url in _mutationIndexCandidates()) {
      final uri = Uri.parse(url);
      final attempts = <Future<http.Response> Function()>[
        () => _sendJson(method: 'POST', url: uri, body: payload),
        () => _sendForm(method: 'POST', url: uri, body: payload),
      ];

      var endpointNotFound = false;
      for (final attempt in attempts) {
        http.Response response;
        try {
          response = await _performRequest(
            () => attempt().timeout(const Duration(seconds: 20)),
          );
        } catch (e) {
          lastError = _asException(e);
          continue;
        }

        if (_isSuccessStatus(response.statusCode)) {
          final parsed = _decode(
            response,
            fallbackMessage: 'Gagal menambah pengguna',
            allowEmptyBody: true,
          );
          final data = _asMap(parsed['data']);
          if (data.isNotEmpty) {
            final created = UserRecord.fromJson(data);
            await _upsertUsersCache(created);
            await NotificationRefreshHelper.refreshSafely();
            return created;
          }
          final created = UserRecord.fromJson({
            'id': 0,
            'name': cleanName,
            'email': cleanEmail,
            'role': cleanRole,
            'jenis_toko': cleanJenisToko,
            'is_active': aktif ? 1 : 0,
            'phone': cleanPhone,
            'address': cleanAddress,
            'created_at': DateTime.now().toIso8601String(),
          });
          await _upsertUsersCache(created);
          await NotificationRefreshHelper.refreshSafely();
          return created;
        }

        if (response.statusCode == 404) {
          endpointNotFound = true;
          break;
        }

        lastError = _responseToException(
          response,
          fallbackMessage: 'Gagal menambah pengguna',
        );

        // Jangan retry payload lain saat email sudah terlanjur dianggap duplicate.
        if (_isDuplicateEmailError(response)) {
          throw lastError;
        }

        if (!_isRetryableMutationStatus(response.statusCode)) {
          throw lastError;
        }
      }

      if (endpointNotFound) continue;
    }

    if (lastError != null && isNetworkReachabilityError(lastError)) {
      final localId = 'loc-${DateTime.now().microsecondsSinceEpoch}';
      final localRow = {
        'id': generateTempId(),
        'code': '',
        ...payload,
        'local_id': localId,
        'server_id': null,
        'sync_status': SyncStatus.pendingCreate.value,
        'created_at': DateTime.now().toIso8601String(),
      };
      await UserLocalRepository.instance.upsertLocalRow(localRow);
      await SyncQueueRepository.instance.enqueue(
        entityType: LocalEntityType.user,
        entityLocalId: localId,
        operation: SyncOperation.create,
        payload: payload,
      );
      unawaited(SyncManager.instance.triggerSync());
      await NotificationRefreshHelper.notifyLocalAction(
        title: 'Pengguna disimpan offline',
        message: 'Akan sinkron otomatis saat online.',
        type: 'user',
        priority: 'high',
        important: true,
        enqueueSync: true,
      );
      return UserRecord.fromJson(localRow);
    }

    throw lastError ?? Exception('Gagal menambah pengguna');
  }

  static Future<UserRecord> updateUser({
    required int userId,
    required String nama,
    required String email,
    required String role,
    required String jenisToko,
    required bool aktif,
    String? password,
    String? telepon,
    String? alamat,
  }) async {
    final payloadCandidates = _buildPayloadCandidates(
      nama: nama,
      email: email,
      password: (password ?? '').trim(),
      role: role,
      jenisToko: jenisToko,
      aktif: aktif,
      telepon: telepon,
      alamat: alamat,
      includePassword: (password ?? '').trim().isNotEmpty,
    );

    Exception? lastError;
    for (final baseUrl in _mutationIndexCandidates()) {
      final uri = Uri.parse('$baseUrl/$userId');
      var endpointNotFound = false;

      for (final payload in payloadCandidates) {
        final attempts = <Future<http.Response> Function()>[
          () => _sendJson(method: 'PUT', url: uri, body: payload),
          () => _sendJson(method: 'PATCH', url: uri, body: payload),
          () => _sendForm(method: 'PUT', url: uri, body: payload),
          () => _sendForm(method: 'PATCH', url: uri, body: payload),
          () => _sendForm(
            method: 'POST',
            url: uri,
            body: {...payload, '_method': 'PUT'},
          ),
          () => _sendMultipart(
            method: 'POST',
            url: uri,
            body: {...payload, '_method': 'PUT'},
          ),
        ];

        var switchPayload = false;
        for (final attempt in attempts) {
          http.Response response;
          try {
            response = await _performRequest(
              () => attempt().timeout(const Duration(seconds: 20)),
            );
          } catch (e) {
            lastError = _asException(e);
            continue;
          }

          if (_isSuccessStatus(response.statusCode)) {
            final parsed = _decode(
              response,
              fallbackMessage: 'Gagal memperbarui pengguna',
              allowEmptyBody: true,
            );
            final data = _asMap(parsed['data']);
            if (data.isNotEmpty) {
              final updated = UserRecord.fromJson(data);
              await _upsertUsersCache(updated);
              await NotificationRefreshHelper.refreshSafely();
              return updated;
            }
            final updated = UserRecord.fromJson({
              'id': userId,
              'name': nama,
              'email': email,
              'role': role,
              'jenis_toko': jenisToko,
              'is_active': aktif ? 1 : 0,
              'phone': telepon ?? '',
              'address': alamat ?? '',
              'updated_at': DateTime.now().toIso8601String(),
            });
            await _upsertUsersCache(updated);
            await NotificationRefreshHelper.refreshSafely();
            return updated;
          }

          if (response.statusCode == 404) {
            endpointNotFound = true;
            break;
          }

          lastError = _responseToException(
            response,
            fallbackMessage: 'Gagal memperbarui pengguna',
          );

          if (_looksLikeSqlError(response)) {
            switchPayload = true;
            break;
          }

          if (!_isRetryableMutationStatus(response.statusCode)) {
            throw lastError;
          }
        }

        if (endpointNotFound) break;
        if (switchPayload) continue;
      }

      if (endpointNotFound) continue;
    }

    if (lastError != null && isNetworkReachabilityError(lastError)) {
      final payload = payloadCandidates.isNotEmpty
          ? payloadCandidates.first
          : <String, dynamic>{};
      final localRow = {
        'id': userId,
        'name': nama,
        'email': email,
        'role': role,
        'jenis_toko': jenisToko,
        'is_active': aktif ? 1 : 0,
        if ((telepon ?? '').trim().isNotEmpty) 'phone': telepon!.trim(),
        if ((alamat ?? '').trim().isNotEmpty) 'address': alamat!.trim(),
        'local_id': 'srv-$userId',
        'server_id': userId,
        'sync_status': SyncStatus.pendingUpdate.value,
        'updated_at': DateTime.now().toIso8601String(),
      };
      await UserLocalRepository.instance.upsertLocalRow(localRow);
      await SyncQueueRepository.instance.enqueue(
        entityType: LocalEntityType.user,
        entityLocalId: 'srv-$userId',
        operation: SyncOperation.update,
        payload: payload,
      );
      unawaited(SyncManager.instance.triggerSync());
      await NotificationRefreshHelper.notifyLocalAction(
        title: 'Perubahan pengguna disimpan offline',
        message: 'Akan sinkron otomatis saat online.',
        type: 'user',
        priority: 'high',
        important: true,
        enqueueSync: true,
      );
      return UserRecord.fromJson(localRow);
    }

    throw lastError ?? Exception('Gagal memperbarui pengguna');
  }

  static Future<void> deleteUser({required int userId}) async {
    Exception? lastError;
    for (final baseUrl in _mutationIndexCandidates()) {
      final uri = Uri.parse('$baseUrl/$userId');
      final attempts = <Future<http.Response> Function()>[
        () async => http
            .delete(uri, headers: await AuthService.authHeaders())
            .timeout(const Duration(seconds: 20)),
        () => _sendForm(method: 'POST', url: uri, body: {'_method': 'DELETE'}),
      ];

      for (final attempt in attempts) {
        http.Response response;
        try {
          response = await _performRequest(() => attempt());
        } catch (e) {
          lastError = _asException(e);
          continue;
        }

        if (_isSuccessStatus(response.statusCode)) {
          _decode(
            response,
            fallbackMessage: 'Gagal menghapus pengguna',
            allowEmptyBody: true,
          );
          await _removeUsersCache(userId);
          await NotificationRefreshHelper.refreshSafely();
          return;
        }

        if (response.statusCode == 404) break;

        lastError = _responseToException(
          response,
          fallbackMessage: 'Gagal menghapus pengguna',
        );
        if (!_isRetryableMutationStatus(response.statusCode)) {
          throw lastError;
        }
      }
    }
    if (lastError != null && isNetworkReachabilityError(lastError)) {
      await UserLocalRepository.instance.removeByLocalId('srv-$userId');
      await SyncQueueRepository.instance.enqueue(
        entityType: LocalEntityType.user,
        entityLocalId: 'srv-$userId',
        operation: SyncOperation.delete,
        payload: {'user_id': userId},
      );
      unawaited(SyncManager.instance.triggerSync());
      await NotificationRefreshHelper.notifyLocalAction(
        title: 'Hapus pengguna disimpan offline',
        message: 'Akan sinkron otomatis saat online.',
        type: 'user',
        priority: 'high',
        important: true,
        enqueueSync: true,
      );
      return;
    }
    throw lastError ?? Exception('Gagal menghapus pengguna');
  }

  static Future<void> registerFace({
    required int userId,
    required String imagePath,
    double? qualityScore,
  }) async {
    if (userId <= 0) {
      throw Exception('ID pengguna tidak valid untuk registrasi wajah.');
    }

    final file = File(imagePath);
    if (!await file.exists()) {
      throw Exception('File wajah tidak ditemukan.');
    }

    final imageBytes = await file.readAsBytes();
    if (imageBytes.isEmpty) {
      throw Exception('File wajah kosong atau tidak valid.');
    }

    final descriptor = _buildPseudoFaceDescriptor(imageBytes);
    final encodedImage = base64Encode(imageBytes);
    final normalizedScore = _normalizeFaceScore(qualityScore);
    final preferServerDescriptor = ApiConfig.faceServerComputesDescriptor;
    final allowLegacyFallback = ApiConfig.faceLegacyDescriptorFallback;
    final strictServerMode = preferServerDescriptor && !allowLegacyFallback;

    final jsonPayloads = <Map<String, dynamic>>[
      ..._buildFaceRegisterPayloads(
        userId: userId,
        base64Image: encodedImage,
        normalizedScore: normalizedScore,
        descriptor: preferServerDescriptor ? null : descriptor,
      ),
      if (preferServerDescriptor && allowLegacyFallback)
        ..._buildFaceRegisterPayloads(
          userId: userId,
          base64Image: encodedImage,
          normalizedScore: normalizedScore,
          descriptor: descriptor,
        ),
    ];

    Exception? lastError;
    var foundEndpoint = false;
    for (final url in _faceRegisterCandidates(userId)) {
      var endpointMissing = false;

      for (final payload in jsonPayloads) {
        http.Response response;
        try {
          response = await _performRequest(
            () => _sendJson(
              method: 'POST',
              url: Uri.parse(url),
              body: payload,
            ).timeout(const Duration(seconds: 25)),
          );
        } catch (e) {
          lastError = _asException(e);
          continue;
        }

        if (_isSuccessStatus(response.statusCode)) {
          foundEndpoint = true;
          _decode(
            response,
            fallbackMessage: 'Gagal menyimpan registrasi wajah',
            allowEmptyBody: true,
          );
          return;
        }

        if (strictServerMode &&
            _looksLikeFaceDescriptorRequiredResponse(response)) {
          throw Exception(
            'Server masih mewajibkan face_descriptor. '
            'Agar konsisten web/mobile, backend harus menghitung descriptor dari image upload.',
          );
        }

        if (response.statusCode == 404) {
          endpointMissing = true;
          break;
        }

        foundEndpoint = true;
        lastError = _responseToException(
          response,
          fallbackMessage: 'Gagal menyimpan registrasi wajah',
        );

        // Endpoint ketemu, coba variasi payload berikutnya.
        if (response.statusCode == 400 ||
            response.statusCode == 405 ||
            response.statusCode == 415 ||
            response.statusCode == 422) {
          continue;
        }

        if (!_isRetryableMutationStatus(response.statusCode)) {
          throw lastError;
        }
      }

      if (endpointMissing && !foundEndpoint) {
        continue;
      }

      methodLoop:
      for (final method in const ['POST', 'PUT', 'PATCH']) {
        for (final fieldName in const [
          'face_image',
          'image',
          'photo',
          'face',
          'file',
        ]) {
          try {
            final baseFields = <String, dynamic>{
              'user_id': userId,
              'platform': 'mobile',
              if (normalizedScore != null)
                'face_score': normalizedScore.toStringAsFixed(2),
            };
            final legacyFields = <String, dynamic>{
              ...baseFields,
              'descriptor': jsonEncode(descriptor),
              'face_descriptor': jsonEncode(descriptor),
            };
            final fieldCandidates = <Map<String, dynamic>>[
              if (preferServerDescriptor) baseFields else legacyFields,
              if (preferServerDescriptor && allowLegacyFallback) legacyFields,
              if (!preferServerDescriptor) baseFields,
            ];

            for (final fields in fieldCandidates) {
              final response = await _performRequest(
                () => _sendFaceMultipart(
                  method: method,
                  url: url,
                  imageField: fieldName,
                  imagePath: imagePath,
                  qualityScore: qualityScore,
                  extraFields: fields,
                ).timeout(const Duration(seconds: 30)),
              );

              if (_isSuccessStatus(response.statusCode)) {
                foundEndpoint = true;
                _decode(
                  response,
                  fallbackMessage: 'Gagal menyimpan registrasi wajah',
                  allowEmptyBody: true,
                );
                return;
              }

              if (strictServerMode &&
                  _looksLikeFaceDescriptorRequiredResponse(response)) {
                throw Exception(
                  'Server masih mewajibkan face_descriptor. '
                  'Agar konsisten web/mobile, backend harus menghitung descriptor dari image upload.',
                );
              }

              if (response.statusCode == 404) {
                endpointMissing = true;
                break methodLoop;
              }
              if (response.statusCode == 405) {
                foundEndpoint = true;
                continue;
              }
              if (response.statusCode == 400 ||
                  response.statusCode == 422 ||
                  response.statusCode == 415) {
                foundEndpoint = true;
                lastError = _responseToException(
                  response,
                  fallbackMessage: 'Gagal menyimpan registrasi wajah',
                );
                continue;
              }

              foundEndpoint = true;
              lastError = _responseToException(
                response,
                fallbackMessage: 'Gagal menyimpan registrasi wajah',
              );
              if (!_isRetryableMutationStatus(response.statusCode)) {
                throw lastError;
              }
            }
          } catch (e) {
            lastError = _asException(e);
          }
        }
      }

      if (endpointMissing && !foundEndpoint) {
        continue;
      }
    }

    if (!foundEndpoint && lastError == null) {
      throw Exception(
        'Endpoint registrasi wajah tidak ditemukan di server (HTTP 404).',
      );
    }

    if (lastError != null && isNetworkReachabilityError(lastError)) {
      await UserLocalRepository.instance.cacheFaceMeta({
        'user_id': userId,
        'image_path': imagePath,
        'quality_score': qualityScore,
        'updated_at': DateTime.now().toIso8601String(),
        'sync_status': SyncStatus.pendingCreate.value,
      });
      await NotificationRefreshHelper.notifyLocalAction(
        title: 'Registrasi wajah disimpan offline',
        message: 'Metadata tersimpan lokal dan akan diproses saat online.',
        type: 'user',
        priority: 'high',
        important: true,
        enqueueSync: true,
      );
      return;
    }

    throw lastError ?? Exception('Gagal menyimpan registrasi wajah');
  }

  static Future<List<UserRecord>> _fetchUsersByUrl(String url) async {
    final response = await _performRequest(
      () async => http
          .get(Uri.parse(url), headers: await AuthService.authHeaders())
          .timeout(const Duration(seconds: 15)),
    );

    if (response.statusCode == 404) {
      throw Exception('Gagal memuat data pengguna (HTTP 404)');
    }

    final json = _decode(
      response,
      fallbackMessage: 'Gagal memuat data pengguna',
    );
    final list = _extractList(json['data']).isNotEmpty
        ? _extractList(json['data'])
        : _extractList(json);

    final records = list
        .map(_asMap)
        .where((m) => m.isNotEmpty)
        .map(UserRecord.fromJson)
        .toList();

    if (records.isNotEmpty) {
      await _saveUsersCache(records);
      await UserLocalRepository.instance.cacheUsers(
        records.map((e) => e.toJson()).toList(growable: false),
      );
    }

    return records;
  }

  static List<String> _primaryIndexCandidates(int perPage) => [
    ApiConfig.userIndex,
    '${ApiConfig.userIndex}?per_page=$perPage',
    '${ApiConfig.userIndex}?limit=$perPage',
    '${ApiConfig.userIndex}/index',
    '${ApiConfig.userIndex}/list',
  ].where((url) => url.trim().isNotEmpty).toSet().toList();

  static List<String> _legacyIndexCandidates(int perPage) => [
    ApiConfig.userLegacyIndex,
    '${ApiConfig.userLegacyIndex}?per_page=$perPage',
    '${ApiConfig.userLegacyIndex}?limit=$perPage',
    '${ApiConfig.userLegacyIndex}/index',
    '${ApiConfig.userLegacyIndex}/list',
  ].where((url) => url.trim().isNotEmpty).toSet().toList();

  static List<String> _mutationIndexCandidates() => [
    ApiConfig.userIndex,
    ApiConfig.userLegacyIndex,
  ];

  static List<String> _faceRegisterCandidates(int userId) => [
    ApiConfig.userFaceRegisterEndpoint,
    ApiConfig.userFaceRegisterLegacyEndpoint,
    ApiConfig.userFaceRegister(userId),
    ApiConfig.userFaceRegisterLegacy(userId),
    '${ApiConfig.userIndex}/$userId/register-face',
    '${ApiConfig.userLegacyIndex}/$userId/register-face',
    '${ApiConfig.userIndex}/$userId/face/register',
    '${ApiConfig.userLegacyIndex}/$userId/face/register',
    '${ApiConfig.userIndex}/register-face/$userId',
    '${ApiConfig.userLegacyIndex}/register-face/$userId',
    '${ApiConfig.userIndex}/face-register/$userId',
    '${ApiConfig.userLegacyIndex}/face-register/$userId',
    '${ApiConfig.userIndex}/faces/register',
    '${ApiConfig.userLegacyIndex}/faces/register',
    '${ApiConfig.baseUrl}/auth/face-register/$userId',
    '${ApiConfig.baseUrl}/auth/register-face/$userId',
    '${ApiConfig.baseUrl}/face-register/$userId',
    '${ApiConfig.baseUrl}/register-face/$userId',
    '${ApiConfig.baseUrl}/face-register',
    '${ApiConfig.baseUrl}/register-face',
  ].where((url) => url.trim().isNotEmpty).toSet().toList();

  static List<Map<String, dynamic>> _buildFaceRegisterPayloads({
    required int userId,
    required String base64Image,
    double? normalizedScore,
    List<double>? descriptor,
  }) {
    final payloads = <Map<String, dynamic>>[
      {
        'user_id': userId,
        'image': base64Image,
        'face_image': base64Image,
        'photo': base64Image,
        'platform': 'mobile',
      },
      {
        'user_id': userId,
        'image': base64Image,
        'face_image': base64Image,
        'photo': base64Image,
        'platform': 'mobile',
      },
      {
        'user_id': userId,
        'image_base64': base64Image,
        'face_image_base64': base64Image,
        'platform': 'mobile',
      },
    ];

    if (descriptor != null) {
      payloads.addAll([
        {
          'user_id': userId,
          'descriptor': descriptor,
          'face_name': 'user_$userId',
          'image': base64Image,
          'platform': 'mobile',
        },
        {
          'user_id': userId,
          'face_descriptor': descriptor,
          'face_score': normalizedScore ?? 0.85,
          'platform': 'mobile',
        },
        {
          'user_id': userId,
          'face_descriptor': jsonEncode(descriptor),
          'face_score': normalizedScore ?? 0.85,
          'platform': 'mobile',
        },
      ]);
    }

    if (normalizedScore != null) {
      payloads[0]['quality_score'] = normalizedScore;
      payloads[0]['face_score'] = normalizedScore;
      payloads[1]['quality_score'] = normalizedScore;
      payloads[2]['quality_score'] = normalizedScore;
      for (var i = 3; i < payloads.length; i++) {
        payloads[i]['quality_score'] = normalizedScore;
      }
    }

    return _uniquePayloads(payloads);
  }

  static bool _looksLikeFaceDescriptorRequiredResponse(http.Response response) {
    if (response.statusCode != 422) return false;
    final body = _safeDecodeBody(response.body);
    final message = (body['message']?.toString() ?? '').toLowerCase();
    if (message.contains('face descriptor') && message.contains('required')) {
      return true;
    }
    final errors = body['errors'];
    if (errors is Map) {
      for (final entry in errors.entries) {
        final key = entry.key.toString().toLowerCase();
        final val = entry.value?.toString().toLowerCase() ?? '';
        if (key.contains('face_descriptor') && val.contains('required')) {
          return true;
        }
      }
    }
    return false;
  }

  static double? _normalizeFaceScore(double? qualityScore) {
    if (qualityScore == null) return null;
    final scaled = qualityScore > 1 ? qualityScore / 100 : qualityScore;
    return scaled.clamp(0.0, 1.0).toDouble();
  }

  static List<double> _buildPseudoFaceDescriptor(List<int> bytes) {
    if (bytes.isEmpty) {
      return List<double>.filled(128, 0);
    }

    final descriptor = List<double>.filled(128, 0);
    final chunkSize = math.max(1, (bytes.length / 128).ceil());

    for (var i = 0; i < descriptor.length; i++) {
      final start = i * chunkSize;
      if (start >= bytes.length) {
        descriptor[i] = 0;
        continue;
      }

      final end = math.min(start + chunkSize, bytes.length);
      var sum = 0;
      for (var idx = start; idx < end; idx++) {
        sum += bytes[idx];
      }

      final avg = sum / (end - start);
      final normalized = (avg / 127.5) - 1.0;
      descriptor[i] = normalized.clamp(-1.0, 1.0).toDouble();
    }

    return descriptor;
  }

  static List<Map<String, dynamic>> _buildPayloadCandidates({
    required String nama,
    required String email,
    required String role,
    required String jenisToko,
    required bool aktif,
    String password = '',
    String? telepon,
    String? alamat,
    bool includePassword = true,
  }) {
    final cleanName = nama.trim();
    final cleanEmail = email.trim();
    final cleanRole = role.trim();
    final cleanJenisToko = jenisToko.trim();
    final cleanPhone = (telepon ?? '').trim();
    final cleanAddress = (alamat ?? '').trim();
    final cleanPassword = password.trim();

    final canonical = <String, dynamic>{
      'name': cleanName,
      'email': cleanEmail,
      'role': cleanRole,
      'jenis_toko': cleanJenisToko,
      'is_active': aktif ? 1 : 0,
      if (cleanPhone.isNotEmpty) 'phone': cleanPhone,
      if (cleanAddress.isNotEmpty) 'address': cleanAddress,
    };
    final legacy = <String, dynamic>{
      'nama': cleanName,
      'email': cleanEmail,
      'jabatan': cleanRole,
      'jenis_toko': cleanJenisToko,
      'aktif': aktif ? 1 : 0,
      if (cleanPhone.isNotEmpty) 'telepon': cleanPhone,
      if (cleanAddress.isNotEmpty) 'alamat': cleanAddress,
    };
    final alternative = <String, dynamic>{
      'name': cleanName,
      'email': cleanEmail,
      'role': cleanRole,
      'store_type': cleanJenisToko,
      'active': aktif ? 1 : 0,
      if (cleanPhone.isNotEmpty) 'phone': cleanPhone,
      if (cleanAddress.isNotEmpty) 'address': cleanAddress,
    };

    final candidates = <Map<String, dynamic>>[canonical, legacy, alternative];

    if (includePassword && cleanPassword.isNotEmpty) {
      for (final payload in candidates) {
        payload['password'] = cleanPassword;
      }

      // Beberapa backend membutuhkan confirmed password.
      candidates.add({
        ...canonical,
        'password': cleanPassword,
        'password_confirmation': cleanPassword,
      });
    }

    return _uniquePayloads(candidates);
  }

  static Future<http.Response> _sendFaceMultipart({
    required String method,
    required String url,
    required String imageField,
    required String imagePath,
    double? qualityScore,
    Map<String, dynamic>? extraFields,
  }) async {
    final headers = await AuthService.authHeaders();
    headers.remove('Content-Type');
    final req = http.MultipartRequest(method, Uri.parse(url))
      ..headers.addAll(headers)
      ..files.add(await http.MultipartFile.fromPath(imageField, imagePath));

    if (qualityScore != null) {
      req.fields['quality_score'] = qualityScore.toStringAsFixed(2);
    }
    if (extraFields != null) {
      req.fields.addAll(_toFormFields(extraFields));
    }

    final streamed = await req.send();
    return http.Response.fromStream(streamed);
  }

  static Future<http.Response> _sendJson({
    required String method,
    required Uri url,
    required Map<String, dynamic> body,
  }) async {
    final headers = await AuthService.authHeaders();
    final req = http.Request(method, url)
      ..headers.addAll(headers)
      ..body = jsonEncode(body);
    final streamed = await req.send();
    return http.Response.fromStream(streamed);
  }

  static Future<http.Response> _sendForm({
    required String method,
    required Uri url,
    required Map<String, dynamic> body,
  }) async {
    final headers = await AuthService.authHeaders();
    headers['Content-Type'] = 'application/x-www-form-urlencoded';
    final req = http.Request(method, url)
      ..headers.addAll(headers)
      ..bodyFields = _toFormFields(body);
    final streamed = await req.send();
    return http.Response.fromStream(streamed);
  }

  static Future<http.Response> _sendMultipart({
    required String method,
    required Uri url,
    required Map<String, dynamic> body,
  }) async {
    final headers = await AuthService.authHeaders();
    headers.remove('Content-Type');
    final req = http.MultipartRequest(method, url)
      ..headers.addAll(headers)
      ..fields.addAll(_toFormFields(body));
    final streamed = await req.send();
    return http.Response.fromStream(streamed);
  }

  static Map<String, String> _toFormFields(Map<String, dynamic> body) {
    final fields = <String, String>{};
    body.forEach((key, value) {
      if (value == null) return;
      fields[key] = value.toString();
    });
    return fields;
  }

  static Future<http.Response> _performRequest(
    Future<http.Response> Function() request,
  ) async {
    try {
      final response = await request();
      // Debug log untuk 403 errors
      if (response.statusCode == 403) {
        print('[UserService] 403 FORBIDDEN');
        print('[UserService] Response: ${response.body}');
      }
      return response;
    } on TimeoutException {
      throw Exception(
        'Koneksi ke server timeout. Periksa koneksi internet lalu coba lagi.',
      );
    } on SocketException {
      throw Exception(
        'Tidak ada koneksi internet atau server tidak dapat dijangkau.',
      );
    } on http.ClientException {
      throw Exception(
        'Tidak dapat terhubung ke server. Periksa koneksi internet Anda.',
      );
    }
  }

  static Map<String, dynamic> _decode(
    http.Response response, {
    required String fallbackMessage,
    bool allowEmptyBody = false,
  }) {
    final body = _safeDecodeBody(response.body);

    if (response.statusCode == 401) {
      throw Exception('Sesi login habis, silakan login ulang');
    }

    if (response.statusCode < 200 || response.statusCode >= 300) {
      throw Exception(
        _buildErrorMessage(body, fallbackMessage, response.statusCode),
      );
    }

    if (body.isEmpty && !allowEmptyBody) {
      throw Exception(fallbackMessage);
    }

    final success = body['success'];
    if (success is bool && !success) {
      throw Exception(
        _buildErrorMessage(body, fallbackMessage, response.statusCode),
      );
    }

    return body;
  }

  static Map<String, dynamic> _safeDecodeBody(String raw) {
    try {
      final decoded = jsonDecode(raw);
      if (decoded is Map<String, dynamic>) return decoded;
      if (decoded is List) return {'data': decoded};
    } catch (_) {}
    return {};
  }

  static Exception _responseToException(
    http.Response response, {
    required String fallbackMessage,
  }) {
    final body = _safeDecodeBody(response.body);
    return Exception(
      _buildErrorMessage(body, fallbackMessage, response.statusCode),
    );
  }

  static bool _looksLikeSqlError(http.Response response) {
    final body = _safeDecodeBody(response.body);
    final message = body['message']?.toString() ?? '';
    final error = body['error']?.toString() ?? '';
    final raw = '$message $error';
    return _looksLikeSqlErrorText(raw);
  }

  static bool _isDuplicateEmailError(http.Response response) {
    final body = _safeDecodeBody(response.body);
    final message = body['message']?.toString().toLowerCase() ?? '';
    final error = body['error']?.toString().toLowerCase() ?? '';
    final errors = body['errors'];
    if (message.contains('email') && message.contains('taken')) return true;
    if (error.contains('email') && error.contains('taken')) return true;
    if (errors is Map) {
      for (final entry in errors.entries) {
        if (entry.key.toString().toLowerCase() != 'email') continue;
        final value = entry.value;
        if (value is List) {
          final joined = value.map((e) => e.toString().toLowerCase()).join(' ');
          if (joined.contains('taken') || joined.contains('sudah')) return true;
        } else if (value != null) {
          final text = value.toString().toLowerCase();
          if (text.contains('taken') || text.contains('sudah')) return true;
        }
      }
    }
    return false;
  }

  static List<Map<String, dynamic>> _uniquePayloads(
    List<Map<String, dynamic>> payloads,
  ) {
    final seen = <String>{};
    final unique = <Map<String, dynamic>>[];
    for (final payload in payloads) {
      final cleaned = <String, dynamic>{}
        ..addAll(payload)
        ..removeWhere((_, value) => value == null);
      final keys = cleaned.keys.toList()..sort();
      final signature = keys
          .map((key) => '$key=${cleaned[key]?.toString() ?? ''}')
          .join('&');
      if (seen.add(signature)) {
        unique.add(cleaned);
      }
    }
    return unique;
  }

  static Future<void> _saveUsersCache(List<UserRecord> users) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final encoded = jsonEncode(users.map((e) => e.toJson()).toList());
      await prefs.setString(_usersCacheKey, encoded);
    } catch (_) {}
  }

  static Future<List<UserRecord>> _loadUsersCache() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final raw = prefs.getString(_usersCacheKey) ?? '';
      if (raw.trim().isEmpty) return <UserRecord>[];
      final decoded = jsonDecode(raw);
      if (decoded is! List) return <UserRecord>[];
      final records = decoded
          .whereType<Map>()
          .map((e) => e.map((k, v) => MapEntry(k.toString(), v)))
          .map(UserRecord.fromJson)
          .toList(growable: true);
      return records;
    } catch (_) {
      return <UserRecord>[];
    }
  }

  static Future<void> _upsertUsersCache(UserRecord record) async {
    final users = List<UserRecord>.from(await _loadUsersCache());
    final index = users.indexWhere((u) {
      if (record.id > 0 && u.id > 0) return u.id == record.id;
      return u.email.toLowerCase() == record.email.toLowerCase();
    });
    if (index >= 0) {
      users[index] = record;
    } else {
      users.insert(0, record);
    }
    await _saveUsersCache(users);
    await UserLocalRepository.instance.upsertLocalRow({
      ...record.toJson(),
      'local_id': record.id > 0 ? 'srv-${record.id}' : 'loc-${DateTime.now().microsecondsSinceEpoch}',
      'server_id': record.id > 0 ? record.id : null,
      'sync_status': SyncStatus.synced.value,
    });
  }

  static Future<void> _removeUsersCache(int userId) async {
    if (userId <= 0) return;
    final users = List<UserRecord>.from(await _loadUsersCache());
    users.removeWhere((u) => u.id == userId);
    await _saveUsersCache(users);
    await UserLocalRepository.instance.removeByLocalId('srv-$userId');
  }

  static String _buildErrorMessage(
    Map<String, dynamic> body,
    String fallbackMessage,
    int statusCode,
  ) {
    // Handle 403 FORBIDDEN specifically
    if (statusCode == 403) {
      final message = body['message']?.toString();
      if (message != null && message.trim().isNotEmpty) {
        return message;
      }
      return 'Anda tidak memiliki izin akses untuk melakukan operasi ini. Pastikan role Anda memiliki permission yang tepat.';
    }

    final message = body['message']?.toString() ?? '';
    final error = body['error']?.toString() ?? '';
    final errors = body['errors'];

    // Check for SQL errors
    if (_looksLikeSqlErrorText(message)) {
      return 'Terjadi kesalahan database di server. Silakan hubungi administrator atau coba lagi nanti.';
    }
    if (_looksLikeSqlErrorText(error)) {
      return 'Terjadi kesalahan database di server. Silakan hubungi administrator atau coba lagi nanti.';
    }

    final validationMessages = <String>[];
    if (errors is Map) {
      for (final value in errors.values) {
        if (value is List && value.isNotEmpty) {
          validationMessages.add(value.first.toString());
        } else if (value != null) {
          validationMessages.add(value.toString());
        }
      }
    }

    final details = validationMessages.join(' | ').trim();
    if (details.isNotEmpty && message.isNotEmpty) {
      return '$message: $details';
    }
    if (details.isNotEmpty) return details;
    if (error.isNotEmpty) return error;
    if (message.isNotEmpty) return message;
    return '$fallbackMessage (HTTP $statusCode)';
  }

  static List<dynamic> _extractList(dynamic raw) {
    if (raw is List) return raw;
    if (raw is Map<String, dynamic>) {
      for (final key in [
        'data',
        'items',
        'users',
        'pengguna',
        'results',
        'records',
        'rows',
      ]) {
        final nested = raw[key];
        if (nested is List) return nested;
      }
    }
    return const [];
  }

  static Map<String, dynamic> _asMap(dynamic raw) {
    if (raw is Map<String, dynamic>) return raw;
    if (raw is Map) {
      return raw.map((k, v) => MapEntry(k.toString(), v));
    }
    return {};
  }

  static bool _isSuccessStatus(int statusCode) =>
      statusCode >= 200 && statusCode < 300;

  static bool _isRetryableMutationStatus(int statusCode) =>
      statusCode == 400 ||
      statusCode == 403 ||
      statusCode == 404 ||
      statusCode == 405 ||
      statusCode == 415 ||
      statusCode == 422 ||
      statusCode >= 500;

  static bool _looksLikeSqlErrorText(String raw) {
    final value = raw.toLowerCase();
    return value.contains('sqlstate') ||
        value.contains('unknown column') ||
        value.contains("doesn't have a default value");
  }

  static bool _isHttp404Exception(Exception error) {
    final value = error.toString().toLowerCase();
    return value.contains('http 404') ||
        value.contains('(http 404)') ||
        value.contains('status code: 404');
  }
}

int _toInt(dynamic value) {
  if (value == null) return 0;
  if (value is int) return value;
  if (value is num) return value.toInt();
  return int.tryParse(value.toString()) ?? 0;
}

bool _toBool(dynamic value, {required bool defaultValue}) {
  if (value == null) return defaultValue;
  if (value is bool) return value;
  if (value is num) return value != 0;
  final raw = value.toString().toLowerCase().trim();
  if (raw == 'true' || raw == '1' || raw == 'yes' || raw == 'aktif') {
    return true;
  }
  if (raw == 'false' || raw == '0' || raw == 'no' || raw == 'nonaktif') {
    return false;
  }
  return defaultValue;
}

String _fallbackKode(int id) {
  if (id <= 0) return '-';
  return 'USR-${id.toString().padLeft(3, '0')}';
}

Exception _asException(Object e) {
  if (e is Exception) return e;
  return Exception(e.toString());
}
