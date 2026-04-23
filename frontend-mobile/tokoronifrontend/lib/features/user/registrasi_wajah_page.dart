import 'package:camera/camera.dart';
import 'package:flutter/material.dart';
import 'package:google_mlkit_face_detection/google_mlkit_face_detection.dart';
import 'package:permission_handler/permission_handler.dart';
import '../../core/services/user_service.dart';

class RegistrasiWajahPage extends StatefulWidget {
  final String namaUser;
  final int userId;

  const RegistrasiWajahPage({
    super.key,
    required this.namaUser,
    required this.userId,
  });

  @override
  State<RegistrasiWajahPage> createState() => _RegistrasiWajahPageState();
}

class _RegistrasiWajahPageState extends State<RegistrasiWajahPage>
    with WidgetsBindingObserver {
  static const _blue = Color(0xFF3B6FE8);
  static const _green = Color(0xFF38A169);
  static const _red = Color(0xFFE53E3E);
  static const _minScore = 60.0;

  List<CameraDescription> _cameras = [];
  CameraController? _ctrl;
  bool _cameraReady = false;
  bool _cameraActive = false;
  bool _cameraError = false;
  bool _permissionDenied = false;
  int _camIndex = 0;
  bool _flashOn = false;

  final FaceDetector _detector = FaceDetector(
    options: FaceDetectorOptions(
      enableClassification: true,
      enableLandmarks: true,
      enableTracking: true,
      minFaceSize: 0.10,
      performanceMode: FaceDetectorMode.accurate,
    ),
  );

  bool _isDetecting = false;
  bool _isSaving = false;
  int _faceCount = 0;
  double _qualityScore = 0;
  Rect? _faceBox;
  String _qualityLabel = '';
  String? _lastCapturePath;
  bool _liveDetectRunning = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    _loadCameras();
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    _liveDetectRunning = false;
    _ctrl?.dispose();
    _detector.close();
    super.dispose();
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.inactive && _cameraActive) {
      _ctrl?.dispose();
    } else if (state == AppLifecycleState.resumed && _cameraActive) {
      _startCamera();
    }
  }

  Future<void> _loadCameras() async {
    try {
      _cameras = await availableCameras();
      final fi = _cameras.indexWhere(
        (c) => c.lensDirection == CameraLensDirection.front,
      );
      _camIndex = fi >= 0 ? fi : 0;
    } catch (_) {
      _cameras = [];
    }
  }

  Future<void> _activateCamera() async {
    final status = await Permission.camera.request();
    if (!status.isGranted) {
      setState(() {
        _permissionDenied = true;
        _cameraError = true;
      });
      return;
    }
    if (_cameras.isEmpty) await _loadCameras();
    if (_cameras.isEmpty) {
      setState(() => _cameraError = true);
      return;
    }

    setState(() {
      _cameraActive = true;
      _cameraReady = false;
      _cameraError = false;
      _permissionDenied = false;
    });
    await _startCamera();
  }

  Future<void> _startCamera() async {
    await _ctrl?.dispose();
    if (_cameras.isEmpty) return;

    final cam = _cameras[_camIndex];
    final ctrl = CameraController(
      cam,
      ResolutionPreset.high,
      enableAudio: false,
      imageFormatGroup: ImageFormatGroup.nv21,
    );
    _ctrl = ctrl;

    try {
      await ctrl.initialize();
      if (!mounted) return;
      if (cam.lensDirection == CameraLensDirection.front && _flashOn) {
        _flashOn = false;
        await ctrl.setFlashMode(FlashMode.off);
      }
      setState(() {
        _cameraReady = true;
        _cameraError = false;
      });
      _startLiveDetection();
    } catch (_) {
      setState(() {
        _cameraError = true;
        _cameraReady = false;
      });
    }
  }

  Future<void> _deactivateCamera() async {
    _liveDetectRunning = false;
    await _ctrl?.dispose();
    _ctrl = null;
    if (!mounted) return;
    setState(() {
      _cameraActive = false;
      _cameraReady = false;
      _faceBox = null;
      _faceCount = 0;
      _qualityScore = 0;
      _qualityLabel = '';
      _flashOn = false;
      _lastCapturePath = null;
    });
  }

  Future<void> _flipCamera() async {
    if (_cameras.length < 2 || _isDetecting || _isSaving) return;
    _liveDetectRunning = false;
    setState(() {
      _cameraReady = false;
      _faceBox = null;
      _faceCount = 0;
      _qualityScore = 0;
    });
    _camIndex = (_camIndex + 1) % _cameras.length;
    await _startCamera();
  }

  bool get _isFrontCamera =>
      _cameras.isNotEmpty &&
      _cameras[_camIndex].lensDirection == CameraLensDirection.front;

  Future<void> _toggleFlash() async {
    if (_ctrl == null || !_cameraReady) return;
    if (_isFrontCamera) {
      _snack('Flash tidak tersedia untuk kamera depan', Colors.orange);
      return;
    }
    setState(() => _flashOn = !_flashOn);
    await _ctrl!.setFlashMode(_flashOn ? FlashMode.torch : FlashMode.off);
  }

  void _startLiveDetection() {
    if (_liveDetectRunning) return;
    _liveDetectRunning = true;
    _liveDetectLoop();
  }

  Future<void> _liveDetectLoop() async {
    while (_liveDetectRunning && mounted && _cameraReady && _ctrl != null) {
      await Future.delayed(const Duration(milliseconds: 1500));
      if (!_liveDetectRunning || !mounted || _isSaving) break;
      await _detectFrame();
    }
  }

  Future<void> _detectFrame() async {
    if (_ctrl == null || !_cameraReady || _isDetecting || _isSaving) return;
    _isDetecting = true;

    XFile? file;
    try {
      file = await _ctrl!.takePicture();
    } catch (_) {
      _isDetecting = false;
      return;
    }

    final input = InputImage.fromFilePath(file.path);
    List<Face> faces;
    try {
      faces = await _detector.processImage(input);
    } catch (_) {
      faces = [];
    }

    if (!mounted || !_liveDetectRunning) {
      _isDetecting = false;
      return;
    }

    if (faces.isEmpty) {
      setState(() {
        _faceCount = 0;
        _faceBox = null;
        _qualityScore = 0;
        _qualityLabel = '';
        _lastCapturePath = null;
      });
    } else {
      faces.sort(
        (a, b) => (b.boundingBox.width * b.boundingBox.height).compareTo(
          a.boundingBox.width * a.boundingBox.height,
        ),
      );
      final face = faces.first;

      double score = _calculateQuality(face);

      final imgW = _ctrl!.value.previewSize?.height ?? 720;
      final imgH = _ctrl!.value.previewSize?.width ?? 1280;
      final box = face.boundingBox;
      final norm = Rect.fromLTRB(
        (box.left / imgW).clamp(0.0, 1.0),
        (box.top / imgH).clamp(0.0, 1.0),
        (box.right / imgW).clamp(0.0, 1.0),
        (box.bottom / imgH).clamp(0.0, 1.0),
      );

      setState(() {
        _faceCount = faces.length;
        _faceBox = norm;
        _qualityScore = score;
        _qualityLabel = _scoreLabel(score);
        _lastCapturePath = file?.path;
      });
    }
    _isDetecting = false;
  }

  double _calculateQuality(Face face) {
    double score = 0;

    final imgW = _ctrl!.value.previewSize?.height ?? 720;
    final imgH = _ctrl!.value.previewSize?.width ?? 1280;
    final faceArea = face.boundingBox.width * face.boundingBox.height;
    final frameArea = imgW * imgH;
    final sizeRatio = (faceArea / frameArea).clamp(0.0, 0.5);
    score += (sizeRatio / 0.5) * 40;

    final leftEye = face.leftEyeOpenProbability ?? 0.5;
    final rightEye = face.rightEyeOpenProbability ?? 0.5;
    score += ((leftEye + rightEye) / 2) * 30;

    final yaw = (face.headEulerAngleY ?? 0).abs();
    final pitch = (face.headEulerAngleX ?? 0).abs();
    final anglePenalty = ((yaw + pitch) / 60).clamp(0.0, 1.0);
    score += (1 - anglePenalty) * 20;

    score += 10;

    return score.clamp(0, 100);
  }

  String _scoreLabel(double s) {
    if (s >= 85) return 'Kualitas gambar sangat baik';
    if (s >= 70) return 'Kualitas gambar baik';
    if (s >= 60) return 'Kualitas gambar cukup';
    if (s >= 40) return 'Kualitas gambar kurang';
    return 'Kualitas gambar buruk — hadapkan wajah ke kamera';
  }

  Color _scoreColor(double s) {
    if (s >= 60) return _green;
    if (s >= 40) return const Color(0xFFECC94B);
    return _red;
  }

  Future<void> _simpanWajah() async {
    if (_lastCapturePath == null || _faceCount == 0) {
      _snack('Tidak ada wajah terdeteksi. Hadapkan wajah ke kamera.', _red);
      return;
    }
    if (_faceCount > 1) {
      _snack('Pastikan hanya 1 wajah dalam frame kamera.', Colors.orange);
      return;
    }
    if (_qualityScore < _minScore) {
      _snack(
        'Skor kualitas terlalu rendah (${_qualityScore.toInt()}%). Minimal ${_minScore.toInt()}%',
        Colors.orange,
      );
      return;
    }

    final confirm = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        contentPadding: const EdgeInsets.fromLTRB(24, 20, 24, 0),
        actionsPadding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 56,
              height: 56,
              decoration: BoxDecoration(
                color: _blue.withValues(alpha: 0.08),
                shape: BoxShape.circle,
              ),
              child: const Icon(Icons.face_rounded, color: _blue, size: 28),
            ),
            const SizedBox(height: 14),
            const Text(
              'Simpan Registrasi Wajah?',
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.bold,
                color: Color(0xFF2D3748),
              ),
            ),
            const SizedBox(height: 8),
            Text(
              'Wajah ${widget.namaUser} akan didaftarkan ke sistem dengan skor kualitas ${_qualityScore.toInt()}%.',
              textAlign: TextAlign.center,
              style: TextStyle(
                fontSize: 13,
                color: Colors.grey.shade600,
                height: 1.4,
              ),
            ),
            const SizedBox(height: 20),
          ],
        ),
        actions: [
          Row(
            children: [
              Expanded(
                child: OutlinedButton(
                  onPressed: () => Navigator.pop(context, false),
                  style: OutlinedButton.styleFrom(
                    side: BorderSide(color: Colors.grey.shade300),
                    padding: const EdgeInsets.symmetric(vertical: 11),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(10),
                    ),
                  ),
                  child: const Text(
                    'Batal',
                    style: TextStyle(
                      color: Colors.grey,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: ElevatedButton(
                  onPressed: () => Navigator.pop(context, true),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: _blue,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 11),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(10),
                    ),
                    elevation: 0,
                  ),
                  child: const Text(
                    'Simpan',
                    style: TextStyle(fontWeight: FontWeight.w600),
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );

    if (confirm != true) return;

    setState(() => _isSaving = true);
    _liveDetectRunning = false;

    try {
      await UserService.registerFace(
        userId: widget.userId,
        imagePath: _lastCapturePath!,
        qualityScore: _qualityScore,
      );

      if (!mounted) return;
      setState(() => _isSaving = false);

      await showDialog(
        context: context,
        barrierDismissible: false,
        builder: (_) => AlertDialog(
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(20),
          ),
          contentPadding: const EdgeInsets.fromLTRB(24, 24, 24, 0),
          actionsPadding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                width: 64,
                height: 64,
                decoration: BoxDecoration(
                  color: _green.withValues(alpha: 0.10),
                  shape: BoxShape.circle,
                ),
                child: const Icon(
                  Icons.check_circle_rounded,
                  color: _green,
                  size: 32,
                ),
              ),
              const SizedBox(height: 14),
              const Text(
                'Wajah Berhasil Didaftarkan!',
                style: TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                  color: Color(0xFF2D3748),
                ),
              ),
              const SizedBox(height: 8),
              Text(
                'Wajah ${widget.namaUser} telah berhasil didaftarkan ke sistem.',
                textAlign: TextAlign.center,
                style: TextStyle(
                  fontSize: 13,
                  color: Colors.grey.shade600,
                  height: 1.4,
                ),
              ),
              const SizedBox(height: 20),
            ],
          ),
          actions: [
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: () {
                  Navigator.pop(context);
                  Navigator.pop(context, true);
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: _green,
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 12),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(10),
                  ),
                  elevation: 0,
                ),
                child: const Text(
                  'Selesai',
                  style: TextStyle(fontWeight: FontWeight.w600),
                ),
              ),
            ),
          ],
        ),
      );
    } catch (e) {
      if (!mounted) return;
      setState(() => _isSaving = false);
      _liveDetectRunning = true;
      _liveDetectLoop();
      _snack(
        'Gagal menyimpan: ${e.toString().replaceFirst('Exception: ', '')}',
        _red,
      );
    }
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

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF3F4F8),
      appBar: AppBar(
        backgroundColor: _blue,
        foregroundColor: Colors.white,
        elevation: 0,
        shape: const RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(bottom: Radius.circular(20)),
        ),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new_rounded),
          onPressed: () => Navigator.pop(context),
        ),
        title: const Text(
          'Registrasi Wajah',
          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            _buildMainCard(),
            const SizedBox(height: 14),
            _buildGuideCard(),
            const SizedBox(height: 32),
          ],
        ),
      ),
    );
  }

  Widget _buildMainCard() => Container(
    decoration: BoxDecoration(
      color: Colors.white,
      borderRadius: BorderRadius.circular(14),
      boxShadow: [
        BoxShadow(
          color: Colors.black.withValues(alpha: 0.07),
          blurRadius: 10,
          offset: const Offset(0, 3),
        ),
      ],
    ),
    padding: const EdgeInsets.fromLTRB(16, 18, 16, 18),
    child: Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Registrasi Wajah',
          style: TextStyle(
            fontSize: 18,
            fontWeight: FontWeight.bold,
            color: Color(0xFF2D3748),
          ),
        ),
        const SizedBox(height: 3),
        Text(
          'Registrasi biometrik untuk ${widget.namaUser}',
          style: TextStyle(fontSize: 13, color: Colors.grey.shade500),
        ),
        const SizedBox(height: 14),

        if (!_cameraActive)
          GestureDetector(
            onTap: _activateCamera,
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
              decoration: BoxDecoration(
                color: const Color(0xFFEBF0FF),
                borderRadius: BorderRadius.circular(8),
                border: Border.all(color: const Color(0xFFBECEFF)),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: const [
                  Icon(Icons.videocam_rounded, color: _blue, size: 18),
                  SizedBox(width: 6),
                  Text(
                    'Kamera',
                    style: TextStyle(
                      color: _blue,
                      fontSize: 13,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ],
              ),
            ),
          ),
        if (_cameraActive) const SizedBox(height: 2),

        const SizedBox(height: 10),

        LayoutBuilder(
          builder: (ctx, cons) {
            final w = cons.maxWidth;
            final h = w * (4 / 3);
            return _buildCameraPreview(w, h);
          },
        ),
        const SizedBox(height: 8),

        if (_cameraActive)
          Row(
            children: [
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 10,
                  vertical: 4,
                ),
                decoration: BoxDecoration(
                  color: (_cameraReady ? _green : Colors.orange).withValues(
                    alpha: 0.10,
                  ),
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(
                    color: (_cameraReady ? _green : Colors.orange).withValues(
                      alpha: 0.3,
                    ),
                  ),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Container(
                      width: 7,
                      height: 7,
                      decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        color: _cameraReady ? _green : Colors.orange,
                      ),
                    ),
                    const SizedBox(width: 5),
                    Text(
                      _cameraReady ? 'Kamera Aktif' : 'Memuat...',
                      style: TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.w600,
                        color: _cameraReady ? _green : Colors.orange,
                      ),
                    ),
                  ],
                ),
              ),
              const Spacer(),
              if (_cameraReady)
                Text(
                  _faceCount == 0
                      ? 'Tidak ada wajah'
                      : '$_faceCount Wajah terdeteksi',
                  style: TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w500,
                    color: _faceCount == 1
                        ? _green
                        : _faceCount > 1
                        ? Colors.orange
                        : Colors.grey.shade500,
                  ),
                ),
            ],
          ),
        const SizedBox(height: 12),

        if (_cameraActive && _cameraReady && _faceCount > 0)
          _buildQualityCard(),
        if (_cameraActive && _cameraReady && _faceCount > 0)
          Padding(
            padding: const EdgeInsets.only(top: 8, bottom: 4),
            child: Row(
              children: [
                Icon(
                  Icons.info_outline_rounded,
                  size: 13,
                  color: Colors.grey.shade500,
                ),
                const SizedBox(width: 5),
                Expanded(
                  child: Text(
                    'Pastikan skor deteksi minimal ${_minScore.toInt()}% untuk hasil terbaik',
                    style: TextStyle(fontSize: 11, color: Colors.grey.shade500),
                  ),
                ),
              ],
            ),
          ),
        if (_cameraActive && _cameraReady) const SizedBox(height: 12),

        if (_cameraActive)
          Row(
            children: [
              Expanded(
                child: ElevatedButton(
                  onPressed: _isSaving ? null : _deactivateCamera,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: _red,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 13),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(10),
                    ),
                    elevation: 0,
                  ),
                  child: const Text(
                    'Matikan Kamera',
                    style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600),
                  ),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: ElevatedButton(
                  onPressed:
                      (_isSaving ||
                          _faceCount == 0 ||
                          _qualityScore < _minScore)
                      ? null
                      : _simpanWajah,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: _blue,
                    disabledBackgroundColor: Colors.grey.shade300,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 13),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(10),
                    ),
                    elevation: 0,
                  ),
                  child: _isSaving
                      ? const SizedBox(
                          width: 18,
                          height: 18,
                          child: CircularProgressIndicator(
                            strokeWidth: 2,
                            color: Colors.white,
                          ),
                        )
                      : const Text(
                          'Simpan Wajah',
                          style: TextStyle(
                            fontSize: 14,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                ),
              ),
            ],
          ),
      ],
    ),
  );

  Widget _buildProportionalPreview(double w, double h) {
    final controller = _ctrl!;
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

  Widget _buildCameraPreview(double w, double h) {
    return ClipRRect(
      borderRadius: BorderRadius.circular(10),
      child: SizedBox(
        width: w,
        height: h,
        child: Stack(
          fit: StackFit.expand,
          children: [
            Container(color: const Color(0xFF1A1A1A)),

            if (_cameraActive && _cameraReady && _ctrl != null)
              _buildProportionalPreview(w, h)
            else if (_cameraActive && _cameraError)
              _buildErrorWidget()
            else if (_cameraActive)
              const Center(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    CircularProgressIndicator(color: Colors.white54),
                    SizedBox(height: 12),
                    Text(
                      'Memuat kamera...',
                      style: TextStyle(color: Colors.white54, fontSize: 12),
                    ),
                  ],
                ),
              )
            else
              Center(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(
                      Icons.videocam_off_rounded,
                      size: 48,
                      color: Colors.grey.shade600,
                    ),
                    const SizedBox(height: 10),
                    Text(
                      'Kamera tidak aktif',
                      style: TextStyle(
                        color: Colors.grey.shade600,
                        fontSize: 13,
                      ),
                    ),
                    const SizedBox(height: 14),
                    ElevatedButton.icon(
                      onPressed: _activateCamera,
                      icon: const Icon(Icons.videocam_rounded, size: 16),
                      label: const Text('Aktifkan Kamera'),
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
                ),
              ),

            if (_faceBox != null && _cameraReady && _cameraActive)
              Positioned(
                left: _faceBox!.left * w,
                top: _faceBox!.top * h,
                width: (_faceBox!.width * w).clamp(40.0, w),
                height: (_faceBox!.height * h).clamp(40.0, h),
                child: _BoundingBox(
                  color: _qualityScore >= _minScore ? _green : Colors.orange,
                ),
              ),

            if (_cameraActive && _cameraReady)
              Positioned(
                left: 0,
                right: 0,
                bottom: 0,
                child: Container(
                  padding: const EdgeInsets.fromLTRB(14, 8, 14, 12),
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      begin: Alignment.bottomCenter,
                      end: Alignment.topCenter,
                      colors: [
                        Colors.black.withValues(alpha: 0.6),
                        Colors.transparent,
                      ],
                    ),
                  ),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      _InCamBtn(
                        icon: _flashOn
                            ? Icons.flashlight_on_rounded
                            : Icons.flashlight_off_rounded,
                        label: 'Senter',
                        active: _flashOn,
                        onTap: _toggleFlash,
                      ),
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
  }

  Widget _buildErrorWidget() => Center(
    child: Padding(
      padding: const EdgeInsets.all(20),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(
            _permissionDenied
                ? Icons.no_photography_rounded
                : Icons.error_rounded,
            color: Colors.white54,
            size: 42,
          ),
          const SizedBox(height: 10),
          Text(
            _permissionDenied
                ? 'Izin kamera ditolak.\nBuka Pengaturan > Izin > Kamera'
                : 'Kamera tidak tersedia.',
            textAlign: TextAlign.center,
            style: const TextStyle(
              color: Colors.white54,
              fontSize: 12,
              height: 1.5,
            ),
          ),
          if (_permissionDenied) ...[
            const SizedBox(height: 10),
            TextButton(
              onPressed: openAppSettings,
              child: const Text(
                'Buka Pengaturan',
                style: TextStyle(color: Colors.lightBlueAccent, fontSize: 12),
              ),
            ),
          ],
        ],
      ),
    ),
  );

  Widget _buildQualityCard() {
    final score = _qualityScore;
    final color = _scoreColor(score);
    final prog = (score / 100).clamp(0.0, 1.0);

    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: Colors.grey.shade200),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.04),
            blurRadius: 6,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Kualitas Deteksi Wajah',
            style: TextStyle(
              fontSize: 14,
              fontWeight: FontWeight.bold,
              color: Color(0xFF2D3748),
            ),
          ),
          const SizedBox(height: 6),

          Row(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Text(
                '${score.toInt()}%',
                style: TextStyle(
                  fontSize: 32,
                  fontWeight: FontWeight.bold,
                  color: color,
                ),
              ),
              const SizedBox(width: 8),
              Padding(
                padding: const EdgeInsets.only(bottom: 5),
                child: Text(
                  '(minimal ${_minScore.toInt()}% disarankan)',
                  style: TextStyle(fontSize: 11, color: Colors.grey.shade500),
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),

          Row(
            children: [
              Text(
                'Rendah',
                style: TextStyle(fontSize: 10, color: Colors.grey.shade500),
              ),
              Expanded(
                child: Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 8),
                  child: ClipRRect(
                    borderRadius: BorderRadius.circular(10),
                    child: LinearProgressIndicator(
                      value: prog,
                      minHeight: 8,
                      backgroundColor: Colors.grey.shade200,
                      valueColor: AlwaysStoppedAnimation<Color>(color),
                    ),
                  ),
                ),
              ),
              Text(
                'Tinggi',
                style: TextStyle(fontSize: 10, color: Colors.grey.shade500),
              ),
            ],
          ),
          const SizedBox(height: 8),

          Row(
            children: [
              Icon(
                score >= _minScore
                    ? Icons.check_circle_rounded
                    : Icons.warning_rounded,
                size: 14,
                color: color,
              ),
              const SizedBox(width: 6),
              Text(
                _qualityLabel,
                style: TextStyle(
                  fontSize: 12,
                  color: color,
                  fontWeight: FontWeight.w500,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildGuideCard() => Container(
    width: double.infinity,
    padding: const EdgeInsets.all(16),
    decoration: BoxDecoration(
      color: const Color(0xFFEBF0FF),
      borderRadius: BorderRadius.circular(12),
      border: Border.all(color: const Color(0xFFBECEFF)),
    ),
    child: Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Panduan Registrasi',
          style: TextStyle(
            fontSize: 14,
            fontWeight: FontWeight.bold,
            color: Color(0xFF2D3748),
          ),
        ),
        const SizedBox(height: 10),
        ...const [
          '1. Aktifkan kamera dengan tombol di samping',
          '2. Izinkan akses kamera jika diminta',
          '3. Posisikan wajah di tengah frame kamera',
          '4. Pastikan pencahayaan cukup dan wajah terlihat jelas',
          '5. Tunggu indikator hijau menyala',
          '6. Pastikan hanya satu wajah dalam frame',
          '7. Klik Simpan Wajah untuk menyimpan data',
        ].map(
          (s) => Padding(
            padding: const EdgeInsets.symmetric(vertical: 2.5),
            child: Text(
              s,
              style: const TextStyle(
                fontSize: 13,
                color: Color(0xFF4A5568),
                height: 1.4,
              ),
            ),
          ),
        ),
      ],
    ),
  );
}

class _BoundingBox extends StatelessWidget {
  final Color color;
  const _BoundingBox({required this.color});

  @override
  Widget build(BuildContext context) =>
      CustomPaint(painter: _BBoxPainter(color: color));
}

class _BBoxPainter extends CustomPainter {
  final Color color;
  const _BBoxPainter({required this.color});

  @override
  void paint(Canvas canvas, Size size) {
    final p = Paint()
      ..color = color
      ..strokeWidth = 3.5
      ..style = PaintingStyle.stroke
      ..strokeCap = StrokeCap.round;

    const L = 22.0;
    final w = size.width;
    final h = size.height;

    canvas.drawLine(const Offset(0, L), const Offset(0, 0), p);
    canvas.drawLine(const Offset(0, 0), Offset(L, 0), p);
    canvas.drawLine(Offset(w, L), Offset(w, 0), p);
    canvas.drawLine(Offset(w, 0), Offset(w - L, 0), p);
    canvas.drawLine(Offset(0, h - L), Offset(0, h), p);
    canvas.drawLine(Offset(0, h), Offset(L, h), p);
    canvas.drawLine(Offset(w, h - L), Offset(w, h), p);
    canvas.drawLine(Offset(w, h), Offset(w - L, h), p);
  }

  @override
  bool shouldRepaint(_BBoxPainter o) => o.color != color;
}

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
