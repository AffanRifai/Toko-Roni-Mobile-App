// lib/auth/face_login_page.dart
//
// Dependencies (pubspec.yaml):
// ─────────────────────────────
//   camera: ^0.11.0+2
//   google_mlkit_face_detection: ^0.11.0
//   permission_handler: ^11.3.1
//
// AndroidManifest.xml:
//   <uses-permission android:name="android.permission.CAMERA"/>
//   <uses-feature android:name="android.hardware.camera"/>
//   <uses-feature android:name="android.hardware.camera.front" android:required="false"/>
//
// android/app/build.gradle → minSdkVersion 21
//
// ios/Runner/Info.plist:
//   <key>NSCameraUsageDescription</key>
//   <string>Digunakan untuk login pengenalan wajah</string>
// ─────────────────────────────

import 'dart:async';
import 'dart:convert';
import 'dart:io';
import 'dart:math' as math;
import 'dart:ui' as ui;
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:camera/camera.dart';
import 'package:flutter/services.dart';
import 'package:google_mlkit_face_detection/google_mlkit_face_detection.dart';
import 'package:http/http.dart' as http;
import 'package:permission_handler/permission_handler.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:tokoronifrontend/features/auth/login_page.dart';
import 'package:tokoronifrontend/core/config/api_config.dart';
import 'package:tokoronifrontend/core/state/app_state.dart';
import 'package:tokoronifrontend/features/home/dashboard_page.dart';

// Uncomment saat halaman sudah siap dihubungkan:
// import '../home/beranda_page.dart';
// import 'login_page.dart';

// ════════════════════════════════════════════════════════════════════════════
// FACE LOGIN PAGE
// ════════════════════════════════════════════════════════════════════════════
class FaceLoginPage extends StatefulWidget {
  const FaceLoginPage({super.key});

  @override
  State<FaceLoginPage> createState() => _FaceLoginPageState();
}

class _FaceLoginPageState extends State<FaceLoginPage>
    with WidgetsBindingObserver {
  static const _blue = Color(0xFF3B6FE8);

  // ── Kamera ────────────────────────────────────────────────────────────────
  List<CameraDescription> _cameras = [];
  CameraController? _controller;
  bool _cameraReady = false;
  bool _cameraError = false;
  bool _permissionDenied = false;
  int _camIndex = 0; // 0 = depan (default)
  bool _flashOn = false;

  // ── Face detection ────────────────────────────────────────────────────────
  final FaceDetector _faceDetector = FaceDetector(
    options: FaceDetectorOptions(
      enableClassification: true,
      enableTracking: true,
      minFaceSize: 0.15,
      performanceMode: FaceDetectorMode.accurate,
    ),
  );

  bool _isDetecting = false;
  bool _isProcessing = false;
  bool _isRealtimeDetecting = false;
  bool _isImageStreamActive = false;
  DateTime _lastRealtimeTick = DateTime.fromMillisecondsSinceEpoch(0);
  static const Map<DeviceOrientation, int> _androidOrientations = {
    DeviceOrientation.portraitUp: 0,
    DeviceOrientation.landscapeLeft: 90,
    DeviceOrientation.portraitDown: 180,
    DeviceOrientation.landscapeRight: 270,
  };
  bool _faceFound = false;
  String _statusMsg = 'Pastikan pencahayaan cukup dan wajah terlihat jelas';
  Color _statusColor = const Color(0xFF718096);
  DateTime _rateLimitedUntil = DateTime.fromMillisecondsSinceEpoch(0);

  // Bounding box hasil deteksi (dalam koordinat preview, satuan px)
  Rect? _faceBox;

  // ── Preview size untuk skala bounding box ─────────────────────────────────
  Size _previewSize = const Size(280, 280);

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    _initCamera();
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    if (_controller?.value.isStreamingImages ?? false) {
      _controller?.stopImageStream();
    }
    _isImageStreamActive = false;
    _controller?.dispose();
    _faceDetector.close();
    super.dispose();
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.inactive) {
      if (_controller?.value.isStreamingImages ?? false) {
        _controller?.stopImageStream();
      }
      _isImageStreamActive = false;
      _controller?.dispose();
    } else if (state == AppLifecycleState.resumed) {
      _initCamera();
    }
  }

  // ── Init kamera ───────────────────────────────────────────────────────────
  Future<void> _initCamera() async {
    final status = await Permission.camera.request();
    if (!status.isGranted) {
      setState(() {
        _permissionDenied = true;
        _cameraError = true;
      });
      return;
    }
    try {
      _cameras = await availableCameras();
      if (_cameras.isEmpty) {
        setState(() => _cameraError = true);
        return;
      }

      // Default: kamera depan
      final front = _cameras.indexWhere(
        (c) => c.lensDirection == CameraLensDirection.front,
      );
      _camIndex = front >= 0 ? front : 0;

      await _startCamera(_cameras[_camIndex]);
    } catch (_) {
      setState(() => _cameraError = true);
    }
  }

  Future<void> _startCamera(CameraDescription cam) async {
    await _stopRealtimeFaceTracking();
    await _controller?.dispose();
    final ctrl = CameraController(
      cam,
      ResolutionPreset.high,
      enableAudio: false,
      imageFormatGroup: defaultTargetPlatform == TargetPlatform.iOS
          ? ImageFormatGroup.bgra8888
          : ImageFormatGroup.nv21,
    );
    _controller = ctrl;
    try {
      await ctrl.initialize();
      // Jangan langsung start realtime tracking - tunggu user klik "Mulai deteksi"
      if (!mounted) return;
      // Matikan flash saat kamera depan
      if (cam.lensDirection == CameraLensDirection.front && _flashOn) {
        _flashOn = false;
        await ctrl.setFlashMode(FlashMode.off);
      }
      setState(() {
        _cameraReady = true;
        _cameraError = false;
        _faceBox = null;
      });
    } catch (_) {
      setState(() => _cameraError = true);
    }
  }

  // TODO: Bisa diaktifkan lagi di masa depan jika perlu realtime preview tracking
  // ignore: unused_element
  Future<void> _startRealtimeFaceTracking(CameraDescription cam) async {
    final controller = _controller;
    if (controller == null || !controller.value.isInitialized) return;
    if (controller.value.isStreamingImages) {
      _isImageStreamActive = true;
      return;
    }

    try {
      await controller.startImageStream((CameraImage image) async {
        if (!mounted || _isProcessing || _isDetecting || _isRealtimeDetecting) {
          return;
        }

        final now = DateTime.now();
        if (now.difference(_lastRealtimeTick).inMilliseconds < 130) return;
        _lastRealtimeTick = now;
        _isRealtimeDetecting = true;

        try {
          final inputImage = _inputImageFromCameraImage(image, cam);
          if (inputImage == null) return;

          final faces = await _faceDetector.processImage(inputImage);
          if (!mounted) return;

          if (faces.isEmpty) {
            if (_faceFound || _faceBox != null) {
              setState(() {
                _faceFound = false;
                _faceBox = null;
              });
            }
            return;
          }

          final face = _largestFace(faces);
          final rotation = inputImage.metadata?.rotation;
          final rawSize = inputImage.metadata?.size;
          if (rotation == null || rawSize == null) return;

          final rotatedFaceRect = _rotateRectByInputRotation(
            rect: face.boundingBox,
            imageSize: rawSize,
            rotation: rotation,
          );
          final rotatedImageSize = _rotateSizeByInputRotation(
            rawSize,
            rotation,
          );

          final mapped = _mapFaceBoxToPreview(
            imageRect: rotatedFaceRect,
            imageSize: rotatedImageSize,
            previewSize: _previewSize,
            mirrorX: cam.lensDirection == CameraLensDirection.front,
          );

          setState(() {
            _faceFound = !mapped.isEmpty;
            _faceBox = mapped.isEmpty ? null : mapped;
          });
        } catch (_) {
          // Abaikan frame yang gagal diproses.
        } finally {
          _isRealtimeDetecting = false;
        }
      });
      _isImageStreamActive = true;
    } catch (_) {
      _isImageStreamActive = false;
    }
  }

  Future<void> _stopRealtimeFaceTracking() async {
    final controller = _controller;
    if (controller == null) return;

    if (controller.value.isStreamingImages) {
      try {
        await controller.stopImageStream();
      } catch (_) {
        // ignore
      }
    }
    _isImageStreamActive = false;
    _isRealtimeDetecting = false;
  }

  Face _largestFace(List<Face> faces) {
    Face selected = faces.first;
    double selectedArea =
        selected.boundingBox.width * selected.boundingBox.height;

    for (final face in faces.skip(1)) {
      final area = face.boundingBox.width * face.boundingBox.height;
      if (area > selectedArea) {
        selected = face;
        selectedArea = area;
      }
    }
    return selected;
  }

  InputImage? _inputImageFromCameraImage(
    CameraImage image,
    CameraDescription cam,
  ) {
    if (image.planes.isEmpty) return null;

    final rotation = _getInputImageRotation(cam);
    if (rotation == null) return null;

    final format = InputImageFormatValue.fromRawValue(image.format.raw);
    if (format == null) return null;
    if (defaultTargetPlatform == TargetPlatform.android &&
        format != InputImageFormat.nv21) {
      return null;
    }
    if (defaultTargetPlatform == TargetPlatform.iOS &&
        format != InputImageFormat.bgra8888) {
      return null;
    }

    // Untuk ML Kit real-time, format satu-plane (NV21/BGRA8888) paling stabil.
    if (image.planes.length != 1) return null;

    final plane = image.planes.first;
    final bytes = Uint8List.fromList(plane.bytes);

    final metadata = InputImageMetadata(
      size: Size(image.width.toDouble(), image.height.toDouble()),
      rotation: rotation,
      format: format,
      bytesPerRow: plane.bytesPerRow,
    );

    return InputImage.fromBytes(bytes: bytes, metadata: metadata);
  }

  InputImageRotation? _getInputImageRotation(CameraDescription cam) {
    final sensorOrientation = cam.sensorOrientation;

    if (defaultTargetPlatform == TargetPlatform.iOS) {
      return InputImageRotationValue.fromRawValue(sensorOrientation);
    }

    final deviceOrientation = _controller?.value.deviceOrientation;
    final deviceDegrees =
        _androidOrientations[deviceOrientation] ??
        _androidOrientations[DeviceOrientation.portraitUp]!;

    final rotationCompensation = cam.lensDirection == CameraLensDirection.front
        ? (sensorOrientation + deviceDegrees) % 360
        : (sensorOrientation - deviceDegrees + 360) % 360;

    return InputImageRotationValue.fromRawValue(rotationCompensation);
  }

  Rect _rotateRectByInputRotation({
    required Rect rect,
    required Size imageSize,
    required InputImageRotation rotation,
  }) {
    switch (rotation) {
      case InputImageRotation.rotation0deg:
        return rect;
      case InputImageRotation.rotation90deg:
        return Rect.fromLTRB(
          imageSize.height - rect.bottom,
          rect.left,
          imageSize.height - rect.top,
          rect.right,
        );
      case InputImageRotation.rotation180deg:
        return Rect.fromLTRB(
          imageSize.width - rect.right,
          imageSize.height - rect.bottom,
          imageSize.width - rect.left,
          imageSize.height - rect.top,
        );
      case InputImageRotation.rotation270deg:
        return Rect.fromLTRB(
          rect.top,
          imageSize.width - rect.right,
          rect.bottom,
          imageSize.width - rect.left,
        );
    }
  }

  Size _rotateSizeByInputRotation(Size size, InputImageRotation rotation) {
    switch (rotation) {
      case InputImageRotation.rotation90deg:
      case InputImageRotation.rotation270deg:
        return Size(size.height, size.width);
      case InputImageRotation.rotation0deg:
      case InputImageRotation.rotation180deg:
        return size;
    }
  }

  bool get _isFrontCamera =>
      _cameras.isNotEmpty &&
      _cameras[_camIndex].lensDirection == CameraLensDirection.front;

  // ── Toggle kamera ─────────────────────────────────────────────────────────
  Future<void> _flipCamera() async {
    if (_cameras.length < 2 || _isDetecting || _isProcessing) return;
    setState(() {
      _cameraReady = false;
      _faceBox = null;
    });
    _camIndex = (_camIndex + 1) % _cameras.length;
    await _startCamera(_cameras[_camIndex]);
  }

  // ── Toggle flash ─────────────────────────────────────────────────────────
  Future<void> _toggleFlash() async {
    if (_controller == null || !_cameraReady) return;
    // Flash hanya untuk kamera belakang
    if (_isFrontCamera) {
      _snack('Flash tidak tersedia untuk kamera depan', Colors.orange);
      return;
    }
    setState(() => _flashOn = !_flashOn);
    await _controller!.setFlashMode(_flashOn ? FlashMode.torch : FlashMode.off);
  }

  // ════════════════════════════════════════════════════════════════════════
  // MULAI DETEKSI
  // ════════════════════════════════════════════════════════════════════════
  Future<void> _startDetection() async {
    if (_controller == null || !_cameraReady || _isDetecting || _isProcessing) {
      return;
    }
    final now = DateTime.now();
    if (now.isBefore(_rateLimitedUntil)) {
      final remaining = _rateLimitedUntil
          .difference(now)
          .inSeconds
          .clamp(1, 3600);
      _onFailed(
        'Terlalu banyak percobaan login wajah. Coba lagi dalam $remaining detik.',
      );
      return;
    }

    setState(() {
      _isDetecting = true;
      _faceBox = null;
      _faceFound = false;
      _statusMsg = 'Mendeteksi wajah...';
      _statusColor = _blue;
    });

    // STOP realtime tracking saat detection dimulai
    await _stopRealtimeFaceTracking();

    try {
      // Ambil foto
      XFile? file;
      try {
        file = await _controller!.takePicture();
      } catch (_) {
        setState(() {
          _isDetecting = false;
          _statusMsg = 'Gagal mengambil gambar. Coba lagi.';
          _statusColor = const Color(0xFFE53E3E);
        });
        return;
      }

      // Jalankan ML Kit
      final inputImage = InputImage.fromFilePath(file.path);
      List<Face> faces;
      try {
        faces = await _faceDetector.processImage(inputImage);
      } catch (_) {
        faces = [];
      }

      if (!mounted) return;

      if (faces.isEmpty) {
        setState(() {
          _isDetecting = false;
          _faceFound = false;
          _faceBox = null;
          _statusMsg = 'Wajah tidak terdeteksi. Pastikan wajah terlihat jelas.';
          _statusColor = const Color(0xFFE53E3E);
        });
        return;
      }

      // Ambil wajah pertama, lalu map ke koordinat preview yang sedang tampil
      final face = faces.first;
      Rect? mappedFaceBox;
      try {
        final imageSize = await _readImageSize(file);
        mappedFaceBox = _mapFaceBoxToPreview(
          imageRect: face.boundingBox,
          imageSize: imageSize,
          previewSize: _previewSize,
          mirrorX: _isFrontCamera,
        );
      } catch (_) {
        final fallbackPreviewSize = _controller?.value.previewSize;
        if (fallbackPreviewSize == null) {
          mappedFaceBox = null;
        } else {
          mappedFaceBox = _mapFaceBoxToPreview(
            imageRect: face.boundingBox,
            imageSize: Size(
              fallbackPreviewSize.height,
              fallbackPreviewSize.width,
            ),
            previewSize: _previewSize,
            mirrorX: _isFrontCamera,
          );
        }
      }

      setState(() {
        _faceFound = true;
        _isProcessing = true;
        _faceBox = mappedFaceBox;
        _statusMsg = 'Wajah terdeteksi! Memverifikasi...';
        _statusColor = _blue;
      });

      await _verifyFaceWithServer(file.path);
    } finally {
      if (mounted && _cameraReady && !_isImageStreamActive) {
        // Jangan restart realtime tracking - tetap menampilkan face box dari hasil capture
        // await _startRealtimeFaceTracking(activeCam);
      }
    }
  }

  Future<Size> _readImageSize(XFile file) async {
    final bytes = await file.readAsBytes();
    final completer = Completer<Size>();
    ui.decodeImageFromList(bytes, (image) {
      completer.complete(Size(image.width.toDouble(), image.height.toDouble()));
    });
    return completer.future;
  }

  Rect _mapFaceBoxToPreview({
    required Rect imageRect,
    required Size imageSize,
    required Size previewSize,
    required bool mirrorX,
  }) {
    Rect rect = imageRect;

    // Mirror jika kamera depan
    if (mirrorX) {
      rect = Rect.fromLTRB(
        imageSize.width - rect.right,
        rect.top,
        imageSize.width - rect.left,
        rect.bottom,
      );
    }

    // Hitung scale - gunakan fit.cover
    final scaleX = previewSize.width / imageSize.width;
    final scaleY = previewSize.height / imageSize.height;
    final scale = math.max(scaleX, scaleY); // fit.cover: ambil scale terbesar

    // Hitung ukuran saat di-scale
    final scaledImageWidth = imageSize.width * scale;
    final scaledImageHeight = imageSize.height * scale;

    // Hitung offset centering
    final offsetX = (previewSize.width - scaledImageWidth) / 2;
    final offsetY = (previewSize.height - scaledImageHeight) / 2;

    // Map koordinat rect ke preview dengan scale dan offset
    final mappedRect = Rect.fromLTRB(
      rect.left * scale + offsetX,
      rect.top * scale + offsetY,
      rect.right * scale + offsetX,
      rect.bottom * scale + offsetY,
    );

    // Clip ke bounds preview
    final previewBounds = Rect.fromLTWH(
      0,
      0,
      previewSize.width,
      previewSize.height,
    );
    final clipped = mappedRect.intersect(previewBounds);

    // Return empty rect jika di luar bounds
    return clipped.isEmpty ? Rect.zero : clipped;
  }

  // ── Verifikasi ke server ──────────────────────────────────────────────────
  Future<void> _verifyFaceWithServer(String imagePath) async {
    try {
      final responseBody = await _performFaceLogin(imagePath);
      await _saveSessionFromFaceLogin(responseBody);
      final name = _extractUserName(responseBody);
      await _onSuccess(name);
    } on TimeoutException {
      _onFailed('Koneksi ke server timeout. Coba lagi.');
    } on SocketException {
      _onFailed(
        'Tidak ada koneksi internet atau server tidak dapat dijangkau.',
      );
    } on http.ClientException {
      _onFailed('Tidak dapat terhubung ke server. Periksa koneksi internet.');
    } on Exception catch (e) {
      _onFailed(e.toString().replaceFirst('Exception: ', ''));
    } catch (_) {
      _onFailed('Koneksi gagal. Periksa jaringan.');
    }
  }

  Future<Map<String, dynamic>> _performFaceLogin(String imagePath) async {
    final file = File(imagePath);
    if (!await file.exists()) {
      throw Exception('File wajah tidak ditemukan.');
    }

    final imageBytes = await file.readAsBytes();
    if (imageBytes.isEmpty) {
      throw Exception('File wajah kosong atau tidak valid.');
    }

    final encodedImage = base64Encode(imageBytes);
    final uri = Uri.parse(ApiConfig.faceLogin);
    final preferServerDescriptor = ApiConfig.faceServerComputesDescriptor;
    final allowLegacyFallback = ApiConfig.faceLegacyDescriptorFallback;
    final shouldPrepareLegacyDescriptor =
        !preferServerDescriptor || allowLegacyFallback;
    final legacyDescriptor = shouldPrepareLegacyDescriptor
        ? _buildPseudoFaceDescriptor(imageBytes)
        : null;

    // Penting: jangan banyak percobaan dalam sekali klik karena backend
    // face-login biasanya diproteksi rate limiter.
    final primaryResponse = await _sendFaceLoginMultipartRequest(
      uri: uri,
      imagePath: imagePath,
      descriptor: preferServerDescriptor ? null : legacyDescriptor,
      imageFieldName: 'image',
    );
    final primaryBody = _safeDecodeBody(primaryResponse.body);
    if (_isFaceLoginSuccess(primaryResponse, primaryBody)) {
      return primaryBody;
    }
    if (!kReleaseMode) {
      debugPrint(
        '[FaceLogin] primary failed (${primaryResponse.statusCode}): ${primaryResponse.body}',
      );
    }
    if (primaryResponse.statusCode == 429) {
      _applyRateLimitFromResponse(primaryResponse);
      throw _responseToException(
        response: primaryResponse,
        fallbackMessage: 'Terlalu banyak percobaan login wajah.',
      );
    }

    if (preferServerDescriptor &&
        !allowLegacyFallback &&
        _looksLikeDescriptorRequired(primaryBody)) {
      throw Exception(
        'Server masih mewajibkan face_descriptor. '
        'Agar konsisten web/mobile, backend harus menghitung descriptor dari image upload.',
      );
    }

    if (preferServerDescriptor &&
        allowLegacyFallback &&
        _looksLikeDescriptorRequired(primaryBody)) {
      final legacyResponse = await _sendFaceLoginMultipartRequest(
        uri: uri,
        imagePath: imagePath,
        descriptor: legacyDescriptor,
        imageFieldName: 'image',
      );
      final legacyBody = _safeDecodeBody(legacyResponse.body);
      if (_isFaceLoginSuccess(legacyResponse, legacyBody)) {
        return legacyBody;
      }
      if (legacyResponse.statusCode == 429) {
        _applyRateLimitFromResponse(legacyResponse);
      }
      if (!kReleaseMode) {
        debugPrint(
          '[FaceLogin] legacy multipart failed (${legacyResponse.statusCode}): ${legacyResponse.body}',
        );
      }
    }

    if (!_isPayloadRetryableStatus(primaryResponse.statusCode)) {
      throw _responseToException(
        response: primaryResponse,
        fallbackMessage: 'Login wajah gagal',
      );
    }

    // Fallback satu kali saja dengan payload JSON.
    final fallbackPayload = _buildFaceLoginJsonPayload(
      encodedImage: encodedImage,
      descriptor: preferServerDescriptor ? null : legacyDescriptor,
    );
    final fallbackResponse = await http
        .post(
          uri,
          headers: const {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
          },
          body: jsonEncode(fallbackPayload),
        )
        .timeout(const Duration(seconds: 25));
    final fallbackBody = _safeDecodeBody(fallbackResponse.body);
    if (_isFaceLoginSuccess(fallbackResponse, fallbackBody)) {
      return fallbackBody;
    }
    if (!kReleaseMode) {
      debugPrint(
        '[FaceLogin] fallback failed (${fallbackResponse.statusCode}): ${fallbackResponse.body}',
      );
    }
    if (fallbackResponse.statusCode == 429) {
      _applyRateLimitFromResponse(fallbackResponse);
    }

    if (preferServerDescriptor &&
        !allowLegacyFallback &&
        _looksLikeDescriptorRequired(fallbackBody)) {
      throw Exception(
        'Server masih mewajibkan face_descriptor. '
        'Agar konsisten web/mobile, backend harus menghitung descriptor dari image upload.',
      );
    }

    if (preferServerDescriptor &&
        allowLegacyFallback &&
        _looksLikeDescriptorRequired(fallbackBody)) {
      final legacyPayload = _buildFaceLoginJsonPayload(
        encodedImage: encodedImage,
        descriptor: legacyDescriptor,
      );
      final legacyFallbackResponse = await http
          .post(
            uri,
            headers: const {
              'Accept': 'application/json',
              'Content-Type': 'application/json',
            },
            body: jsonEncode(legacyPayload),
          )
          .timeout(const Duration(seconds: 25));
      final legacyFallbackBody = _safeDecodeBody(legacyFallbackResponse.body);
      if (_isFaceLoginSuccess(legacyFallbackResponse, legacyFallbackBody)) {
        return legacyFallbackBody;
      }
      if (legacyFallbackResponse.statusCode == 429) {
        _applyRateLimitFromResponse(legacyFallbackResponse);
      }
      if (!kReleaseMode) {
        debugPrint(
          '[FaceLogin] legacy json failed (${legacyFallbackResponse.statusCode}): ${legacyFallbackResponse.body}',
        );
      }
      throw _responseToException(
        response: legacyFallbackResponse,
        fallbackMessage: 'Login wajah gagal',
      );
    }

    throw _responseToException(
      response: fallbackResponse,
      fallbackMessage: 'Login wajah gagal',
    );
  }

  Future<http.Response> _sendFaceLoginMultipartRequest({
    required Uri uri,
    required String imagePath,
    List<double>? descriptor,
    required String imageFieldName,
  }) async {
    final imageFieldNames = <String>{
      imageFieldName,
      'image',
      'face_image',
      'photo',
      'face',
      'file',
    };

    final request = http.MultipartRequest('POST', uri)
      ..headers['Accept'] = 'application/json'
      ..fields['platform'] = 'mobile';

    if (descriptor != null) {
      request.fields['face_descriptor'] = jsonEncode(descriptor);
      request.fields['descriptor'] = jsonEncode(descriptor);
      for (var i = 0; i < descriptor.length; i++) {
        request.fields['face_descriptor[$i]'] = descriptor[i].toString();
        request.fields['descriptor[$i]'] = descriptor[i].toString();
      }
    }

    for (final field in imageFieldNames) {
      request.files.add(await http.MultipartFile.fromPath(field, imagePath));
    }

    final streamed = await request.send().timeout(const Duration(seconds: 30));
    return http.Response.fromStream(streamed);
  }

  bool _isPayloadRetryableStatus(int statusCode) {
    return statusCode == 400 ||
        statusCode == 405 ||
        statusCode == 415 ||
        statusCode == 422;
  }

  void _applyRateLimitFromResponse(http.Response response) {
    final retryAfter = _parseRetryAfterSeconds(response.headers['retry-after']);
    final waitSeconds = retryAfter ?? 30;
    _rateLimitedUntil = DateTime.now().add(Duration(seconds: waitSeconds));
  }

  int? _parseRetryAfterSeconds(String? rawValue) {
    if (rawValue == null || rawValue.trim().isEmpty) return null;
    return int.tryParse(rawValue.trim());
  }

  List<double> _buildPseudoFaceDescriptor(List<int> bytes) {
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

  Map<String, dynamic> _buildFaceLoginJsonPayload({
    required String encodedImage,
    List<double>? descriptor,
  }) {
    final payload = <String, dynamic>{
      'image': encodedImage,
      'face_image': encodedImage,
      'photo': encodedImage,
      'platform': 'mobile',
    };
    if (descriptor != null) {
      payload['face_descriptor'] = descriptor;
      payload['descriptor'] = descriptor;
    }
    return payload;
  }

  bool _looksLikeDescriptorRequired(Map<String, dynamic> body) {
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

  bool _isFaceLoginSuccess(http.Response response, Map<String, dynamic> body) {
    if (response.statusCode < 200 || response.statusCode >= 300) return false;
    final successFlag = body['success'];
    if (successFlag is bool) return successFlag;
    return true;
  }

  Future<void> _saveSessionFromFaceLogin(Map<String, dynamic> body) async {
    final data = _asMap(body['data']);
    final nestedData = _asMap(data['data']);
    final token = _firstNotEmpty([
      _readString(data['token']),
      _readString(data['access_token']),
      _readString(nestedData['token']),
      _readString(nestedData['access_token']),
      _readString(body['token']),
      _readString(body['access_token']),
    ]);

    if (token.isEmpty) {
      throw Exception(
        'Token login wajah tidak ditemukan pada response server.',
      );
    }

    final userData = _asMap(data['user']);
    final nestedUserData = _asMap(nestedData['user']);
    final user = userData.isNotEmpty ? userData : _asMap(body['user']);
    final resolvedUser = user.isNotEmpty
        ? user
        : (nestedUserData.isNotEmpty ? nestedUserData : <String, dynamic>{});

    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('auth_token', token);
    await prefs.setString(
      'user_name',
      _firstNotEmpty([
        _readString(resolvedUser['name']),
        _readString(data['name']),
        _readString(nestedData['name']),
        _readString(body['name']),
      ]),
    );
    await prefs.setString(
      'user_email',
      _firstNotEmpty([
        _readString(resolvedUser['email']),
        _readString(data['email']),
        _readString(nestedData['email']),
        _readString(body['email']),
      ]),
    );
    await prefs.setString(
      'user_role',
      _firstNotEmpty([
        _readString(resolvedUser['role']),
        _readString(data['role']),
        _readString(nestedData['role']),
        _readString(body['role']),
      ]),
    );
    await prefs.setString(
      'user_photo',
      _firstNotEmpty([
        _readString(resolvedUser['avatar']),
        _readString(data['avatar']),
        _readString(nestedData['avatar']),
        _readString(body['avatar']),
      ]),
    );
    await prefs.setString(
      'user_id',
      _firstNotEmpty([
        _readString(resolvedUser['id']),
        _readString(data['id']),
        _readString(nestedData['id']),
        _readString(body['id']),
        _readString(data['user_id']),
        _readString(nestedData['user_id']),
        _readString(body['user_id']),
      ]),
    );
  }

  String _extractUserName(Map<String, dynamic> body) {
    final data = _asMap(body['data']);
    final nestedData = _asMap(data['data']);
    final userData = _asMap(data['user']);
    final nestedUserData = _asMap(nestedData['user']);
    final user = userData.isNotEmpty ? userData : _asMap(body['user']);
    final resolvedUser = user.isNotEmpty
        ? user
        : (nestedUserData.isNotEmpty ? nestedUserData : <String, dynamic>{});

    return _firstNotEmpty([
      _readString(resolvedUser['name']),
      _readString(data['name']),
      _readString(nestedData['name']),
      _readString(body['name']),
      'Pengguna',
    ]);
  }

  Map<String, dynamic> _asMap(dynamic value) {
    if (value is Map<String, dynamic>) return value;
    if (value is Map) {
      return value.map((key, val) => MapEntry(key.toString(), val));
    }
    return <String, dynamic>{};
  }

  String _readString(dynamic value) {
    if (value == null) return '';
    return value.toString().trim();
  }

  String _firstNotEmpty(List<String> values) {
    for (final value in values) {
      final cleaned = value.trim();
      if (cleaned.isNotEmpty) return cleaned;
    }
    return '';
  }

  Map<String, dynamic> _safeDecodeBody(String raw) {
    try {
      final decoded = jsonDecode(raw);
      if (decoded is Map<String, dynamic>) return decoded;
      if (decoded is Map) {
        return decoded.map((key, value) => MapEntry(key.toString(), value));
      }
      if (decoded is List) return {'data': decoded};
    } catch (_) {}
    return <String, dynamic>{};
  }

  Exception _responseToException({
    required http.Response response,
    required String fallbackMessage,
  }) {
    final body = _safeDecodeBody(response.body);
    final message = _extractErrorMessage(
      body: body,
      fallbackMessage: fallbackMessage,
      statusCode: response.statusCode,
      retryAfterHeader: response.headers['retry-after'],
    );
    return Exception(message);
  }

  String _extractErrorMessage({
    required Map<String, dynamic> body,
    required String fallbackMessage,
    required int statusCode,
    String? retryAfterHeader,
  }) {
    if (statusCode == 422) {
      final validationMessage = _extractValidationMessage(body);
      if (validationMessage != null && validationMessage.isNotEmpty) {
        return validationMessage;
      }
    }

    if (statusCode == 429) {
      final retryAfter = _parseRetryAfterSeconds(retryAfterHeader);
      if (retryAfter != null && retryAfter > 0) {
        return 'Terlalu banyak percobaan login wajah. Coba lagi dalam $retryAfter detik.';
      }
      return 'Terlalu banyak percobaan login wajah. Tunggu sebentar lalu coba lagi.';
    }

    final directMessage = body['message']?.toString();
    if (directMessage != null && directMessage.trim().isNotEmpty) {
      return directMessage.trim();
    }

    final error = body['error'];
    if (error is String && error.trim().isNotEmpty) {
      return error.trim();
    }

    final errors = body['errors'];
    if (errors is Map) {
      for (final value in errors.values) {
        if (value is List && value.isNotEmpty) {
          final first = value.first.toString().trim();
          if (first.isNotEmpty) return first;
        }
        final text = value?.toString().trim() ?? '';
        if (text.isNotEmpty) return text;
      }
    }

    if (statusCode == 401) {
      return 'Wajah tidak dikenali atau akun tidak memiliki akses.';
    }
    if (statusCode == 403) {
      return 'Akun tidak diizinkan untuk login.';
    }
    if (statusCode == 404) {
      return 'Endpoint login wajah tidak ditemukan di server.';
    }
    return fallbackMessage;
  }

  String? _extractValidationMessage(Map<String, dynamic> body) {
    final errors = body['errors'];
    if (errors is Map) {
      for (final value in errors.values) {
        if (value is List && value.isNotEmpty) {
          final first = value.first.toString().trim();
          if (first.isNotEmpty) return first;
        }
        final text = value?.toString().trim() ?? '';
        if (text.isNotEmpty) return text;
      }
    }
    return null;
  }

  Future<void> _onSuccess(String name) async {
    if (!mounted) return;
    setState(() {
      _isDetecting = _isProcessing = false;
      _statusMsg = 'Berhasil! Selamat datang, $name';
      _statusColor = const Color(0xFF48BB78);
    });

    await Future.delayed(const Duration(milliseconds: 700));
    if (!mounted) return;

    await AppState.instance.init();
    if (!mounted) return;

    Navigator.pushAndRemoveUntil(
      context,
      MaterialPageRoute(builder: (_) => const BerandaPage()),
      (_) => false,
    );
  }

  void _onFailed(String msg) {
    if (!mounted) return;
    setState(() {
      _isDetecting = _isProcessing = _faceFound = false;
      _faceBox = null;
      _statusMsg = msg;
      _statusColor = const Color(0xFFE53E3E);
    });
    // Reset UI untuk coba lagi setelah 2 detik
    Future.delayed(const Duration(seconds: 2), () {
      if (!mounted) return;
      setState(() {
        _statusMsg = 'Pastikan pencahayaan cukup dan wajah terlihat jelas';
        _statusColor = const Color(0xFF718096);
        _faceBox = null;
        _faceFound = false;
      });
    });
  }

  void _snack(String msg, Color color) =>
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(msg),
          backgroundColor: color,
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(10),
          ),
        ),
      );

  // ════════════════════════════════════════════════════════════════════════
  // BUILD
  // ════════════════════════════════════════════════════════════════════════
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Stack(
        children: [
          const _BackgroundCityscape(),
          SafeArea(
            child: SingleChildScrollView(
              child: Column(
                children: [
                  const SizedBox(height: 140),
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 22),
                    child: _buildCard(),
                  ),
                  const SizedBox(height: 40),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildCard() => Container(
    decoration: BoxDecoration(
      color: Colors.white,
      borderRadius: BorderRadius.circular(24),
      boxShadow: [
        BoxShadow(
          color: Colors.black.withOpacity(0.13),
          blurRadius: 24,
          offset: const Offset(0, 8),
        ),
      ],
    ),
    padding: const EdgeInsets.fromLTRB(24, 28, 24, 28),
    child: Column(
      children: [
        // Title
        const Text(
          'Login',
          style: TextStyle(
            fontSize: 26,
            fontWeight: FontWeight.w600,
            color: Color(0xFF2D3748),
            letterSpacing: 0.3,
          ),
        ),
        const SizedBox(height: 10),
        const Icon(
          Icons.person_outline_rounded,
          size: 46,
          color: Color(0xFF4A5568),
        ),
        const SizedBox(height: 12),
        const Text(
          'Face Recognition Login',
          style: TextStyle(
            fontSize: 18,
            fontWeight: FontWeight.bold,
            color: Color(0xFF2D3748),
          ),
        ),
        const SizedBox(height: 4),
        Text(
          'Hadapkan wajah Anda ke kamera untuk login',
          textAlign: TextAlign.center,
          style: TextStyle(fontSize: 13, color: Colors.grey.shade500),
        ),
        const SizedBox(height: 20),

        // ── Camera box ───────────────────────────────────────────────────
        _buildCameraBox(),

        const SizedBox(height: 14),

        // Status indicator
        if (_statusMsg.isNotEmpty)
          AnimatedContainer(
            duration: const Duration(milliseconds: 300),
            width: double.infinity,
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
            decoration: BoxDecoration(
              color: _statusColor.withOpacity(0.08),
              borderRadius: BorderRadius.circular(8),
            ),
            child: Row(
              children: [
                Icon(
                  _isProcessing
                      ? Icons.hourglass_top_rounded
                      : _faceFound
                      ? Icons.check_circle_rounded
                      : Icons.info_outline_rounded,
                  size: 13,
                  color: _statusColor,
                ),
                const SizedBox(width: 6),
                Expanded(
                  child: Text(
                    _statusMsg,
                    style: TextStyle(
                      fontSize: 11,
                      color: _statusColor,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                ),
              ],
            ),
          ),
        const SizedBox(height: 18),

        // Tombol Mulai Deteksi
        SizedBox(
          width: double.infinity,
          child: ElevatedButton.icon(
            onPressed: (_isDetecting || _isProcessing || !_cameraReady)
                ? null
                : _startDetection,
            icon: _isProcessing
                ? const SizedBox(
                    width: 16,
                    height: 16,
                    child: CircularProgressIndicator(
                      strokeWidth: 2,
                      color: Colors.white,
                    ),
                  )
                : const Icon(Icons.play_arrow_rounded, size: 22),
            label: Text(
              _isProcessing
                  ? 'Memverifikasi...'
                  : _isDetecting
                  ? 'Mendeteksi...'
                  : 'Mulai deteksi wajah',
              style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold),
            ),
            style: ElevatedButton.styleFrom(
              backgroundColor: _blue,
              disabledBackgroundColor: Colors.grey.shade300,
              foregroundColor: Colors.white,
              padding: const EdgeInsets.symmetric(vertical: 14),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(30),
              ),
              elevation: 2,
            ),
          ),
        ),
        const SizedBox(height: 18),

        // Login dengan password
        GestureDetector(
          onTap: () {
            // TODO: Navigator.pushReplacement
            Navigator.pushReplacement(
              context,
              MaterialPageRoute(builder: (_) => const LoginPage()),
            );
          },
          child: Row(
            mainAxisAlignment: MainAxisAlignment.end,
            children: [
              Text(
                'Login dengan password',
                style: TextStyle(
                  fontSize: 13,
                  color: Colors.grey.shade700,
                  decoration: TextDecoration.underline,
                  decorationColor: Colors.grey.shade600,
                ),
              ),
              const SizedBox(width: 5),
              Icon(
                Icons.lock_outline_rounded,
                size: 15,
                color: Colors.grey.shade700,
              ),
            ],
          ),
        ),
      ],
    ),
  );

  // ── Camera box ─────────────────────────────────────────────────────────────
  Widget _buildCameraBox() {
    return LayoutBuilder(
      builder: (ctx, constraints) {
        final w = constraints.maxWidth;
        final h = w * (4 / 3); // rasio 4:3
        _previewSize = Size(w, h);

        return ClipRRect(
          borderRadius: BorderRadius.circular(14),
          child: SizedBox(
            width: w,
            height: h,
            child: Stack(
              fit: StackFit.expand,
              children: [
                // ── Background ────────────────────────────────────────────
                Container(color: Colors.grey.shade900),

                // ── Camera preview ────────────────────────────────────────
                if (_cameraReady && _controller != null)
                  _buildProportionalPreview(w, h)
                else if (_cameraError)
                  _buildCameraErrorWidget()
                else
                  const Center(
                    child: CircularProgressIndicator(color: Colors.white54),
                  ),

                // ── Face bounding box ─────────────────────────────────────
                if (_faceBox != null && !_faceBox!.isEmpty && _cameraReady)
                  Positioned(
                    left: _faceBox!.left,
                    top: _faceBox!.top,
                    width: _faceBox!.width,
                    height: _faceBox!.height,
                    child: _FaceBoundingBox(
                      color: _isProcessing ? _blue : const Color(0xFFE53E3E),
                    ),
                  ),

                // ── Bottom controls: Senter (kiri) + Putar Kamera (kanan) ─
                if (_cameraReady)
                  Positioned(
                    left: 0,
                    right: 0,
                    bottom: 0,
                    child: Container(
                      padding: const EdgeInsets.fromLTRB(14, 10, 14, 14),
                      decoration: BoxDecoration(
                        gradient: LinearGradient(
                          begin: Alignment.bottomCenter,
                          end: Alignment.topCenter,
                          colors: [
                            Colors.black.withOpacity(0.55),
                            Colors.transparent,
                          ],
                        ),
                      ),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          // Senter
                          _InCamBtn(
                            icon: _flashOn
                                ? Icons.flashlight_on_rounded
                                : Icons.flashlight_off_rounded,
                            label: 'Senter',
                            active: _flashOn,
                            onTap: _toggleFlash,
                          ),
                          // Putar Kamera
                          _InCamBtn(
                            icon: Icons.flip_camera_ios_rounded,
                            label: 'Putar Kamera',
                            onTap: _cameras.length > 1 ? _flipCamera : null,
                          ),
                        ],
                      ),
                    ),
                  ),
              ],
            ),
          ),
        );
      },
    );
  }

  Widget _buildProportionalPreview(double w, double h) {
    final controller = _controller!;
    final size = controller.value.previewSize;

    if (size == null) {
      return CameraPreview(controller);
    }

    // previewSize dari camera plugin biasanya landscape, jadi untuk tampilan
    // portrait kita tukar width/height agar rasio wajah tidak gepeng.
    final previewWidth = size.height;
    final previewHeight = size.width;

    return SizedBox(
      width: w,
      height: h,
      child: FittedBox(
        fit: BoxFit.cover,
        child: SizedBox(
          width: previewWidth,
          height: previewHeight,
          child: CameraPreview(controller),
        ),
      ),
    );
  }

  Widget _buildCameraErrorWidget() => Center(
    child: Padding(
      padding: const EdgeInsets.all(24),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(
            _permissionDenied
                ? Icons.no_photography_rounded
                : Icons.videocam_off_rounded,
            color: Colors.white54,
            size: 48,
          ),
          const SizedBox(height: 12),
          Text(
            _permissionDenied
                ? 'Izin kamera ditolak.\nBuka Pengaturan > Izin > Kamera'
                : 'Kamera tidak tersedia di perangkat ini.',
            textAlign: TextAlign.center,
            style: const TextStyle(
              color: Colors.white54,
              fontSize: 13,
              height: 1.5,
            ),
          ),
          if (_permissionDenied) ...[
            const SizedBox(height: 14),
            ElevatedButton.icon(
              onPressed: openAppSettings,
              icon: const Icon(Icons.settings_rounded, size: 16),
              label: const Text('Buka Pengaturan'),
              style: ElevatedButton.styleFrom(
                backgroundColor: _blue,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(
                  horizontal: 16,
                  vertical: 10,
                ),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(10),
                ),
                elevation: 0,
              ),
            ),
          ],
        ],
      ),
    ),
  );
}

// ════════════════════════════════════════════════════════════════════════════
// FACE BOUNDING BOX — 4 sudut kotak merah seperti gambar referensi
// ════════════════════════════════════════════════════════════════════════════
class _FaceBoundingBox extends StatelessWidget {
  final Color color;
  const _FaceBoundingBox({required this.color});

  @override
  Widget build(BuildContext context) =>
      CustomPaint(painter: _BBoxPainter(color: color));
}

class _BBoxPainter extends CustomPainter {
  final Color color;
  const _BBoxPainter({required this.color});

  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = color
      ..strokeWidth = 3.5
      ..style = PaintingStyle.stroke
      ..strokeCap = StrokeCap.round;

    const len = 22.0; // panjang tiap sudut
    final w = size.width;
    final h = size.height;

    // Kiri-atas
    canvas.drawLine(Offset(0, len), Offset(0, 0), paint);
    canvas.drawLine(Offset(0, 0), Offset(len, 0), paint);
    // Kanan-atas
    canvas.drawLine(Offset(w, len), Offset(w, 0), paint);
    canvas.drawLine(Offset(w, 0), Offset(w - len, 0), paint);
    // Kiri-bawah
    canvas.drawLine(Offset(0, h - len), Offset(0, h), paint);
    canvas.drawLine(Offset(0, h), Offset(len, h), paint);
    // Kanan-bawah
    canvas.drawLine(Offset(w, h - len), Offset(w, h), paint);
    canvas.drawLine(Offset(w, h), Offset(w - len, h), paint);
  }

  @override
  bool shouldRepaint(_BBoxPainter old) => old.color != color;
}

// ════════════════════════════════════════════════════════════════════════════
// IN-CAM BUTTON — Senter & Putar Kamera di dalam preview
// ════════════════════════════════════════════════════════════════════════════
class _InCamBtn extends StatelessWidget {
  final IconData icon;
  final String label;
  final bool active;
  final VoidCallback? onTap;
  const _InCamBtn({
    required this.icon,
    required this.label,
    this.active = false,
    this.onTap,
  });

  @override
  Widget build(BuildContext context) => GestureDetector(
    onTap: onTap,
    child: Opacity(
      opacity: onTap == null ? 0.35 : 1.0,
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(
            icon,
            color: active ? const Color(0xFFECC94B) : Colors.white,
            size: 26,
          ),
          const SizedBox(height: 4),
          Text(
            label,
            style: TextStyle(
              color: active ? const Color(0xFFECC94B) : Colors.white,
              fontSize: 11,
              fontWeight: FontWeight.w500,
            ),
          ),
        ],
      ),
    ),
  );
}

// ════════════════════════════════════════════════════════════════════════════
// BACKGROUND CITYSCAPE — identik dengan login_page.dart
// ════════════════════════════════════════════════════════════════════════════
class _BackgroundCityscape extends StatelessWidget {
  const _BackgroundCityscape();

  @override
  Widget build(BuildContext context) => SizedBox(
    height: MediaQuery.of(context).size.height * 0.45,
    width: double.infinity,
    child: CustomPaint(painter: _CityscapePainter()),
  );
}

class _CityscapePainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final sky = Paint()
      ..shader = const LinearGradient(
        begin: Alignment.topCenter,
        end: Alignment.bottomCenter,
        colors: [Color(0xFF87CEEB), Color(0xFF4FC3F7), Color(0xFF29B6F6)],
      ).createShader(Rect.fromLTWH(0, 0, size.width, size.height));
    canvas.drawRect(Rect.fromLTWH(0, 0, size.width, size.height), sky);

    for (final c in [
      [0.08, 0.08, 0.18],
      [0.38, 0.05, 0.14],
      [0.62, 0.10, 0.20],
      [0.82, 0.06, 0.13],
      [0.20, 0.18, 0.16],
      [0.50, 0.20, 0.12],
    ]) {
      _cloud(canvas, size, c[0], c[1], c[2]);
    }

    _layer(canvas, size, const Color(0xFF1565C0), 0);
    _layer(canvas, size, const Color(0xFF1976D2), 1);
    _layer(canvas, size, const Color(0xFF2196F3), 2);

    canvas.drawRect(
      Rect.fromLTWH(0, size.height * 0.85, size.width, size.height * 0.15),
      Paint()..color = const Color(0xFF1565C0),
    );
  }

  void _cloud(Canvas canvas, Size s, double x, double y, double w) {
    final p = Paint()..color = Colors.white.withOpacity(0.85);
    final cx = s.width * x;
    final cy = s.height * y;
    final fw = s.width * w;
    final h = fw * 0.35;
    canvas.drawOval(
      Rect.fromCenter(center: Offset(cx, cy), width: fw, height: h),
      p,
    );
    canvas.drawCircle(Offset(cx - fw * .25, cy), h * .65, p);
    canvas.drawCircle(Offset(cx, cy - h * .3), h * .70, p);
    canvas.drawCircle(Offset(cx + fw * .22, cy), h * .55, p);
  }

  void _layer(Canvas canvas, Size s, Color color, int l) {
    final data = _data(l);
    for (final b in data) {
      final left = s.width * b[0];
      final w = s.width * b[1];
      final top = s.height * (1 - b[2]);
      canvas.drawRect(
        Rect.fromLTWH(left, top, w, s.height * b[2]),
        Paint()..color = color,
      );
      final wp = Paint()..color = Colors.white.withOpacity(0.25);
      double y = top + 10;
      while (y + 4 < top + s.height * b[2] - 10) {
        double x = left + 8;
        while (x + 4 < left + w - 8) {
          canvas.drawRect(Rect.fromLTWH(x, y, 4, 4), wp);
          x += 8;
        }
        y += 8;
      }
    }
  }

  List<List<double>> _data(int l) => l == 0
      ? [
          [0.0, .08, .45],
          [.10, .06, .40],
          [.18, .09, .50],
          [.28, .07, .38],
          [.36, .08, .52],
          [.45, .06, .42],
          [.52, .09, .48],
          [.62, .07, .44],
          [.70, .08, .55],
          [.79, .07, .40],
          [.87, .07, .46],
          [.92, .08, .35],
        ]
      : l == 1
      ? [
          [0.0, .10, .55],
          [.12, .08, .48],
          [.22, .11, .62],
          [.34, .09, .50],
          [.44, .10, .58],
          [.55, .08, .44],
          [.64, .11, .60],
          [.76, .09, .52],
          [.86, .08, .46],
          [.92, .08, .40],
        ]
      : [
          [0.0, .12, .65],
          [.14, .10, .55],
          [.26, .13, .70],
          [.40, .11, .60],
          [.52, .12, .68],
          [.65, .10, .58],
          [.76, .12, .63],
          [.89, .11, .50],
        ];

  @override
  bool shouldRepaint(covariant CustomPainter _) => false;
}
