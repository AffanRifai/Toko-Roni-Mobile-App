<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toko Roni - Login</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- FaceAPI.js -->
    <script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api@latest/dist/face-api.min.js"></script>

    <style>
        @keyframes float {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        @keyframes pulse-ring {
            0% {
                transform: scale(0.8);
                opacity: 0.8;
            }

            100% {
                transform: scale(1.2);
                opacity: 0;
            }
        }

        .float-animation {
            animation: float 3s ease-in-out infinite;
        }

        .pulse-ring {
            animation: pulse-ring 2s infinite;
        }

        .spinner {
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top: 3px solid white;
            width: 24px;
            height: 24px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .text-gradient {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .face-canvas-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
        }

        .detection-box {
            position: absolute;
            border: 3px solid #10B981;
            border-radius: 8px;
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.3);
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">

    <!-- Login Container -->
    <div class="w-full max-w-6xl bg-white rounded-3xl shadow-2xl overflow-hidden flex flex-col lg:flex-row">

        <!-- Left Panel - Branding -->
        <div class="lg:w-1/2 bg-gradient-to-br from-blue-600 to-blue-800 p-12 text-white flex flex-col justify-center">
            <div class="mb-12">
                <h1 class="text-4xl font-bold mb-2">Toko Roni</h1>
                <p class="text-blue-200">Management System</p>
            </div>

            <div class="space-y-8">
                <div>
                    <h2 class="text-3xl font-bold mb-4">Login dengan Wajah</h2>
                    <p class="text-blue-100 mb-6">Teknologi face recognition yang aman dan cepat</p>
                </div>

                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-blue-500/20 flex items-center justify-center">
                            <i class="fas fa-bolt text-blue-300"></i>
                        </div>
                        <div>
                            <p class="font-semibold">Login 3x Lebih Cepat</p>
                            <p class="text-sm text-blue-200">Tanpa perlu mengingat password</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-blue-500/20 flex items-center justify-center">
                            <i class="fas fa-shield-alt text-blue-300"></i>
                        </div>
                        <div>
                            <p class="font-semibold">Keamanan Tingkat Tinggi</p>
                            <p class="text-sm text-blue-200">Identifikasi biometrik yang unik</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-blue-500/20 flex items-center justify-center">
                            <i class="fas fa-user-check text-blue-300"></i>
                        </div>
                        <div>
                            <p class="font-semibold">Akurasi 99.5%</p>
                            <p class="text-sm text-blue-200">Deteksi wajah dengan presisi tinggi</p>
                        </div>
                    </div>
                </div>

                <!-- Stats -->
                <div class="mt-12 p-6 bg-blue-500/20 rounded-2xl">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="text-center">
                            <p class="text-2xl font-bold">{{ $totalUsers }}</p>
                            <p class="text-sm text-blue-200">Total User</p>
                        </div>
                        <div class="text-center">
                            <p class="text-2xl font-bold">{{ $faceRegisteredUsers }}</p>
                            <p class="text-sm text-blue-200">Wajah Terdaftar</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel - Login Form -->
        <div class="lg:w-1/2 p-12">
            <!-- Login Tabs -->
            <div class="flex border-b mb-8">
                <button id="tabPassword"
                    class="flex-1 py-4 font-semibold text-blue-600 border-b-2 border-blue-600 text-center">
                    <i class="fas fa-key mr-2"></i>Password
                </button>
                <button id="tabFace"
                    class="flex-1 py-4 font-semibold text-gray-500 hover:text-blue-600 transition text-center">
                    <i class="fas fa-user-circle mr-2"></i>Face Recognition
                </button>
            </div>

            <!-- Password Login Form -->
            <form method="POST" action="{{ route('login') }}" id="passwordForm" class="space-y-6">
                @csrf

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Email Address</label>
                    <input type="email" name="email"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                        placeholder="you@example.com" required autofocus value="{{ old('email') }}">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                    <div class="relative">
                        <input type="password" name="password" id="password"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition pr-12"
                            placeholder="Enter your password" required>
                        <button type="button" id="togglePassword"
                            class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-blue-600">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <input type="checkbox" name="remember"
                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-600">Remember me</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-sm text-blue-600 hover:text-blue-800">

                        </a>
                    @endif
                </div>

                <button type="submit"
                    class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold py-3 rounded-xl hover:shadow-lg hover:shadow-blue-200 hover:-translate-y-0.5 transition-all duration-300">
                    <i class="fas fa-sign-in-alt mr-2"></i>Sign In
                </button>

                @if (auth()->check() && in_array(auth()->user()->role, ['owner', 'admin']))
                    <button type="button" id="registerFace"
                        class="w-full border-2 border-blue-600 text-blue-600 font-semibold py-3 rounded-xl hover:bg-blue-50 transition-all duration-300 mt-4">
                        <i class="fas fa-user-plus mr-2"></i>Daftarkan Wajah Baru
                    </button>
                @endif
            </form>

            <!-- Face Recognition Login -->
            <div id="faceForm" class="space-y-6 hidden">
                <div class="text-center mb-6">
                    <div
                        class="w-24 h-24 rounded-full bg-gradient-to-br from-blue-100 to-blue-200 mx-auto mb-6 flex items-center justify-center relative">
                        <i class="fas fa-user-circle text-blue-500 text-4xl"></i>
                        <div class="absolute inset-0 rounded-full border-4 border-blue-400 pulse-ring"></div>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Face Recognition Login</h3>
                    <p class="text-gray-600">Hadapkan wajah Anda ke kamera untuk login</p>
                </div>

                <!-- Face Camera -->
                <div class="relative bg-gray-900 rounded-2xl overflow-hidden">
                    <video id="faceVideo" autoplay playsinline class="w-full h-64 object-cover"></video>
                    <canvas id="faceCanvas" class="face-canvas-overlay"></canvas>

                    <!-- Loading Overlay -->
                    <div id="faceDetectionOverlay"
                        class="absolute inset-0 bg-black/70 flex items-center justify-center hidden">
                        <div class="text-center">
                            <div class="spinner mb-4 mx-auto"></div>
                            <p class="text-white font-medium">Memproses wajah...</p>
                            <p class="text-white/80 text-sm mt-1">Harap tunggu</p>
                        </div>
                    </div>

                    <!-- Success Overlay -->
                    <div id="faceMatchedOverlay"
                        class="absolute inset-0 bg-green-600/90 flex items-center justify-center hidden">
                        <div class="text-center">
                            <i class="fas fa-check-circle text-white text-5xl mb-3"></i>
                            <p class="text-white font-bold text-xl">Wajah Dikenali!</p>
                            <p class="text-white/90 mt-1">Login berhasil</p>
                        </div>
                    </div>

                    <!-- Capture Button -->
                    <button id="captureFace"
                        class="absolute bottom-4 left-1/2 transform -translate-x-1/2 w-14 h-14 rounded-full bg-white shadow-xl flex items-center justify-center hover:scale-110 transition-all">
                        <i class="fas fa-camera text-blue-600 text-xl"></i>
                    </button>
                </div>

                <!-- Status -->
                <div id="faceStatus" class="hidden">
                    <!-- Status akan muncul di sini -->
                </div>

                <!-- Action Buttons -->
                <div class="space-y-4">
                    <button id="startFaceRecognition"
                        class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold py-3 rounded-xl hover:shadow-lg hover:shadow-blue-200 hover:-translate-y-0.5 transition-all duration-300">
                        <i class="fas fa-play mr-2"></i>Mulai Face Recognition
                    </button>

                    <button id="switchToPassword"
                        class="w-full border border-gray-300 text-gray-700 font-semibold py-3 rounded-xl hover:bg-gray-50 transition-all duration-300">
                        <i class="fas fa-key mr-2"></i>Login dengan Password
                    </button>
                </div>

                <!-- Info -->
                <div class="text-center text-sm text-gray-500 mt-6">
                    <p><i class="fas fa-info-circle mr-2"></i>Pastikan pencahayaan cukup dan wajah terlihat jelas</p>
                </div>
            </div>

            <!-- Error Messages -->
            @if ($errors->any())
                <div class="mt-6 p-4 bg-red-50 border border-red-200 rounded-xl">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                        <div>
                            <p class="font-medium text-red-800">Login Gagal</p>
                            <p class="text-red-600 text-sm">{{ $errors->first() }}</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Face Registration Modal -->
    <div id="faceRegisterModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center p-4 z-50">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center">
                            <i class="fas fa-camera text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-xl">Pendaftaran Wajah Baru</h3>
                            <p class="text-blue-100 text-sm">Registrasi wajah untuk login sistem</p>
                        </div>
                    </div>
                    <button id="closeModal" class="text-white/80 hover:text-white text-2xl">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="p-6">
                <div class="grid md:grid-cols-2 gap-6">
                    <!-- Camera Preview -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Kamera Preview</label>
                        <div class="bg-gray-900 rounded-xl overflow-hidden relative h-64">
                            <video id="registerVideo" autoplay playsinline class="w-full h-full object-cover"></video>
                            <canvas id="registerCanvas" class="face-canvas-overlay"></canvas>

                            <!-- Overlay message -->
                            <div id="registerOverlay"
                                class="absolute inset-0 bg-black/50 flex items-center justify-center">
                                <div class="text-center text-white">
                                    <i class="fas fa-video-slash text-2xl mb-2"></i>
                                    <p>Menyalakan kamera...</p>
                                </div>
                            </div>

                            <!-- Capture button -->
                            <button id="registerCapture"
                                class="absolute bottom-4 left-1/2 transform -translate-x-1/2 w-14 h-14 rounded-full bg-white shadow-xl flex items-center justify-center hover:scale-110 transition-all">
                                <i class="fas fa-camera text-blue-600 text-xl"></i>
                            </button>
                        </div>

                        <div class="flex justify-center gap-3 mt-4">
                            <button id="switchCamera"
                                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition text-sm">
                                <i class="fas fa-sync-alt mr-2"></i>Ganti Kamera
                            </button>
                            <button id="retakePhoto"
                                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition text-sm hidden">
                                <i class="fas fa-redo mr-2"></i>Ambil Ulang
                            </button>
                        </div>
                    </div>

                    <!-- Form -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-3">Informasi Pengguna</label>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Pilih User</label>
                                <select id="userSelect"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                                    <option value="">-- Pilih Pengguna --</option>
                                    @foreach ($usersForRegistration as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }}) -
                                            {{ $user->role }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Wajah
                                    (Opsional)</label>
                                <input type="text" id="faceName"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                                    placeholder="Contoh: Wajah Utama">
                            </div>

                            <!-- Preview captured image -->
                            <div id="capturedPreview" class="hidden">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Foto yang Diambil</label>
                                <div class="border-2 border-dashed border-gray-300 rounded-xl p-3">
                                    <img id="previewImage" class="w-full h-40 object-cover rounded-lg">
                                </div>
                            </div>

                            <!-- Status message -->
                            <div id="registerStatus" class="hidden">
                                <!-- Status akan dimasukkan di sini -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="border-t p-6">
                <div class="flex justify-end gap-3">
                    <button id="cancelRegister"
                        class="px-6 py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl hover:bg-gray-200 transition">
                        Batal
                    </button>
                    <button id="saveFace"
                        class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-xl hover:shadow-lg hover:shadow-blue-200 disabled:opacity-50 disabled:cursor-not-allowed transition"
                        disabled>
                        <i class="fas fa-save mr-2"></i>Simpan Wajah
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden Form untuk Face Login -->
    <form id="faceLoginForm" method="POST" action="{{ route('face.login.direct') }}" style="display: none;">
        @csrf
        <input type="hidden" name="user_id" id="faceLoginUserId">
    </form>

    <!-- Hidden Form untuk Face Registration -->
    <form id="faceRegisterForm" method="POST" action="{{ route('face.register.direct') }}" style="display: none;">
        @csrf
        <input type="hidden" name="user_id" id="registerUserId">
        <input type="hidden" name="face_descriptor" id="registerDescriptor">
        <input type="hidden" name="face_score" id="registerScore">
    </form>

    <!-- Pass data dari Laravel ke JavaScript -->
    <script>
        // Pass PHP data to JavaScript
        window.faceDescriptorsData = @json($faceDescriptors);
        window.usersForRegistration = @json($usersForRegistration);
    </script>

    <script>
        // ==============================
        // FACE RECOGNITION SYSTEM - FINAL VERSION
        // ==============================
        class FaceRecognitionSystem {
            constructor() {
                this.isInitialized = false;
                this.isCameraActive = false;
                this.faceDescriptors = [];
                this.videoStream = null;
                this.registerStream = null;
                this.detectionInterval = null;
                this.useFrontCamera = true;
                this.currentDetection = null;

                // Initialize from Laravel data
                this.initializeFaceDescriptors();
                this.initializeElements();
                this.bindEvents();
            }

            // Load face descriptors from Laravel - FIXED FOR CONTROLLER DATA
            initializeFaceDescriptors() {
                console.log('=== INITIALIZING FACE DESCRIPTORS ===');

                this.faceDescriptors = [];

                if (window.faceDescriptorsData && Array.isArray(window.faceDescriptorsData)) {
                    console.log(`Found ${window.faceDescriptorsData.length} users with face registration`);

                    window.faceDescriptorsData.forEach((user, index) => {
                        console.log(`Processing user ${index + 1}: ${user.name} (ID: ${user.id})`);

                        let descriptorArray = null;

                        // Controller mengirimkan field 'descriptor'
                        if (user.descriptor && Array.isArray(user.descriptor)) {
                            descriptorArray = user.descriptor;
                            console.log(`  ✓ Using descriptor field (${descriptorArray.length} values)`);
                        }
                        // Fallback: jika masih ada face_descriptor string
                        else if (user.face_descriptor && typeof user.face_descriptor === 'string') {
                            try {
                                descriptorArray = JSON.parse(user.face_descriptor);
                                console.log(
                                    `  ✓ Parsed from face_descriptor string (${descriptorArray?.length || 0} values)`
                                );
                            } catch (e) {
                                console.error(`  ✗ Failed to parse face_descriptor: ${e.message}`);
                            }
                        }

                        // Validate descriptor
                        if (descriptorArray &&
                            Array.isArray(descriptorArray) &&
                            descriptorArray.length === 128) {

                            // Ensure all values are numbers
                            const normalizedDescriptor = descriptorArray.map(v => Number(v));

                            this.faceDescriptors.push({
                                user_id: user.id,
                                name: user.name,
                                email: user.email,
                                descriptor: normalizedDescriptor,
                                score: user.score || 0.95
                            });

                            console.log(`  ✅ Valid descriptor loaded for ${user.name}`);
                            console.log(
                                `    Sample: [${normalizedDescriptor.slice(0, 3).map(v => v.toFixed(4)).join(', ')}...]`
                            );

                        } else {
                            console.warn(`  ❌ Invalid descriptor for user ${user.name}:`, {
                                hasDescriptor: !!user.descriptor,
                                descriptorType: typeof user.descriptor,
                                descriptorLength: user.descriptor?.length,
                                isArray: Array.isArray(descriptorArray),
                                arrayLength: descriptorArray?.length
                            });
                        }
                    });

                    console.log(`Total valid descriptors loaded: ${this.faceDescriptors.length}`);

                    if (this.faceDescriptors.length === 0) {
                        console.warn('⚠️ No valid face descriptors loaded! Face recognition will not work.');
                        console.log('Raw data for debugging:', window.faceDescriptorsData);
                    }

                } else {
                    console.error('window.faceDescriptorsData is not an array or is empty:', window
                        .faceDescriptorsData);
                }
            }

            initializeElements() {
                // Password elements
                this.passwordInput = document.getElementById('password');
                this.togglePasswordBtn = document.getElementById('togglePassword');

                // Tab elements
                this.tabPassword = document.getElementById('tabPassword');
                this.tabFace = document.getElementById('tabFace');
                this.passwordForm = document.getElementById('passwordForm');
                this.faceForm = document.getElementById('faceForm');

                // Face recognition elements
                this.faceVideo = document.getElementById('faceVideo');
                this.faceCanvas = document.getElementById('faceCanvas');
                this.faceDetectionOverlay = document.getElementById('faceDetectionOverlay');
                this.faceMatchedOverlay = document.getElementById('faceMatchedOverlay');
                this.startFaceRecognitionBtn = document.getElementById('startFaceRecognition');
                this.captureFaceBtn = document.getElementById('captureFace');
                this.switchToPasswordBtn = document.getElementById('switchToPassword');
                this.faceStatus = document.getElementById('faceStatus');

                // Face registration modal elements
                this.registerFaceBtn = document.getElementById('registerFace');
                this.faceRegisterModal = document.getElementById('faceRegisterModal');
                this.closeModalBtn = document.getElementById('closeModal');
                this.cancelRegisterBtn = document.getElementById('cancelRegister');
                this.registerVideo = document.getElementById('registerVideo');
                this.registerOverlay = document.getElementById('registerOverlay');
                this.registerCaptureBtn = document.getElementById('registerCapture');
                this.saveFaceBtn = document.getElementById('saveFace');
                this.userSelect = document.getElementById('userSelect');
                this.switchCameraBtn = document.getElementById('switchCamera');
                this.retakePhotoBtn = document.getElementById('retakePhoto');
                this.capturedPreview = document.getElementById('capturedPreview');
                this.previewImage = document.getElementById('previewImage');
                this.faceNameInput = document.getElementById('faceName');
                this.registerStatus = document.getElementById('registerStatus');

                // Hidden forms
                this.faceLoginForm = document.getElementById('faceLoginForm');
                this.faceLoginUserId = document.getElementById('faceLoginUserId');
                this.faceRegisterForm = document.getElementById('faceRegisterForm');
                this.registerUserId = document.getElementById('registerUserId');
                this.registerDescriptor = document.getElementById('registerDescriptor');
                this.registerScore = document.getElementById('registerScore');

                this.capturedImage = null;
                this.isProcessing = false;
            }

            bindEvents() {
                // Password toggle
                if (this.togglePasswordBtn) {
                    this.togglePasswordBtn.addEventListener('click', () => this.togglePasswordVisibility());
                }

                // Tab switching
                if (this.tabPassword) {
                    this.tabPassword.addEventListener('click', () => this.switchToPasswordLogin());
                }

                if (this.tabFace) {
                    this.tabFace.addEventListener('click', () => this.switchToFaceLogin());
                }

                // Face recognition buttons
                if (this.startFaceRecognitionBtn) {
                    this.startFaceRecognitionBtn.addEventListener('click', () => this.startFaceRecognition());
                }

                if (this.captureFaceBtn) {
                    this.captureFaceBtn.addEventListener('click', () => this.captureAndLogin());
                }

                if (this.switchToPasswordBtn) {
                    this.switchToPasswordBtn.addEventListener('click', () => this.switchToPasswordLogin());
                }

                // Face registration modal
                if (this.registerFaceBtn) {
                    this.registerFaceBtn.addEventListener('click', () => this.openFaceRegistrationModal());
                }

                if (this.closeModalBtn) {
                    this.closeModalBtn.addEventListener('click', () => this.closeFaceRegistrationModal());
                }

                if (this.cancelRegisterBtn) {
                    this.cancelRegisterBtn.addEventListener('click', () => this.closeFaceRegistrationModal());
                }

                if (this.registerCaptureBtn) {
                    this.registerCaptureBtn.addEventListener('click', () => this.captureRegistrationPhoto());
                }

                if (this.saveFaceBtn) {
                    this.saveFaceBtn.addEventListener('click', () => this.saveFaceRegistration());
                }

                if (this.switchCameraBtn) {
                    this.switchCameraBtn.addEventListener('click', () => this.switchRegistrationCamera());
                }

                if (this.retakePhotoBtn) {
                    this.retakePhotoBtn.addEventListener('click', () => this.retakeRegistrationPhoto());
                }

                if (this.userSelect) {
                    this.userSelect.addEventListener('change', () => this.updateSaveButtonState());
                }

                // Cleanup on page unload
                window.addEventListener('beforeunload', () => this.cleanup());
                window.addEventListener('unload', () => this.cleanup());
            }

            // ========================
            // PASSWORD LOGIN FUNCTIONS
            // ========================
            togglePasswordVisibility() {
                if (!this.passwordInput) return;

                const type = this.passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                this.passwordInput.setAttribute('type', type);

                const icon = this.togglePasswordBtn.querySelector('i');
                if (type === 'password') {
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                } else {
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                }
            }

            switchToPasswordLogin() {
                this.tabPassword.classList.add('border-b-2', 'border-blue-600', 'text-blue-600');
                this.tabFace.classList.remove('border-b-2', 'border-blue-600', 'text-blue-600');
                this.tabFace.classList.add('text-gray-500');

                this.passwordForm.classList.remove('hidden');
                this.faceForm.classList.add('hidden');

                this.stopFaceRecognition();
            }

            // ========================
            // FACE RECOGNITION FUNCTIONS
            // ========================
            switchToFaceLogin() {
                this.tabFace.classList.add('border-b-2', 'border-blue-600', 'text-blue-600');
                this.tabPassword.classList.remove('border-b-2', 'border-blue-600', 'text-blue-600');
                this.tabPassword.classList.add('text-gray-500');

                this.faceForm.classList.remove('hidden');
                this.passwordForm.classList.add('hidden');

                if (!this.isInitialized) {
                    this.initializeFaceRecognition();
                }
            }

            async initializeFaceRecognition() {
                try {
                    if (this.isInitialized) {
                        await this.startFaceCamera();
                        return;
                    }

                    this.showFaceStatus('Memuat sistem face recognition...', 'loading');

                    // Load FaceAPI models
                    await this.loadFaceModels();

                    // Start camera
                    await this.startFaceCamera();

                    this.isInitialized = true;
                    this.showFaceStatus('Face recognition siap! Hadapkan wajah ke kamera', 'success', 3000);

                } catch (error) {
                    console.error('Error initializing face recognition:', error);
                    this.showFaceStatus('Gagal memuat face recognition', 'error');

                    Swal.fire({
                        icon: 'error',
                        title: 'Face Recognition Error',
                        text: 'Gagal memuat sistem face recognition. Silakan gunakan login password.',
                        confirmButtonColor: '#3b82f6'
                    });
                }
            }

            async loadFaceModels() {
                try {
                    const MODEL_PATH = '/models';

                    // Load required models
                    await Promise.all([
                        faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_PATH),
                        faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_PATH),
                        faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_PATH)
                    ]);

                    console.log('FaceAPI models loaded successfully');

                } catch (error) {
                    console.error('Error loading FaceAPI models:', error);
                    throw new Error('Gagal memuat model face recognition');
                }
            }

            async startFaceCamera() {
                try {
                    if (this.isCameraActive) return;

                    const stream = await navigator.mediaDevices.getUserMedia({
                        video: {
                            width: {
                                ideal: 640
                            },
                            height: {
                                ideal: 480
                            },
                            facingMode: 'user'
                        },
                        audio: false
                    });

                    this.faceVideo.srcObject = stream;
                    this.videoStream = stream;
                    this.isCameraActive = true;

                    // Wait for video to be ready
                    await new Promise((resolve) => {
                        this.faceVideo.onloadedmetadata = () => {
                            this.faceVideo.play();
                            resolve();
                        };
                    });

                    // Start face detection
                    this.startFaceDetection();

                } catch (error) {
                    console.error('Error accessing camera:', error);

                    let errorMessage = 'Tidak dapat mengakses kamera. ';
                    if (error.name === 'NotAllowedError') {
                        errorMessage += 'Harap izinkan akses kamera di pengaturan browser.';
                    } else if (error.name === 'NotFoundError') {
                        errorMessage += 'Kamera tidak ditemukan di perangkat ini.';
                    } else {
                        errorMessage += 'Silakan cek koneksi kamera.';
                    }

                    throw new Error(errorMessage);
                }
            }

            startFaceDetection() {
                const video = this.faceVideo;
                const canvas = this.faceCanvas;

                // Set canvas dimensions
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;

                // Clear existing interval
                if (this.detectionInterval) {
                    clearInterval(this.detectionInterval);
                }

                this.detectionInterval = setInterval(async () => {
                    try {
                        if (video.readyState !== 4 || video.videoWidth === 0 || this.isProcessing) {
                            return;
                        }

                        const detections = await faceapi.detectAllFaces(video,
                                new faceapi.TinyFaceDetectorOptions({
                                    inputSize: 320,
                                    scoreThreshold: 0.5
                                }))
                            .withFaceLandmarks()
                            .withFaceDescriptors();

                        // Clear canvas
                        const ctx = canvas.getContext('2d');
                        ctx.clearRect(0, 0, canvas.width, canvas.height);

                        if (detections.length > 0) {
                            // Resize detections
                            const resizedDetections = faceapi.resizeResults(detections, {
                                width: canvas.width,
                                height: canvas.height
                            });

                            // Draw detections
                            faceapi.draw.drawDetections(canvas, resizedDetections);
                            faceapi.draw.drawFaceLandmarks(canvas, resizedDetections);

                            // Store current detection for auto-login
                            this.currentDetection = detections[0];

                            // Try to match with stored descriptors
                            if (this.faceDescriptors.length > 0) {
                                const capturedDescriptor = Array.from(detections[0].descriptor);
                                let bestMatch = null;
                                let bestDistance = Infinity;

                                for (const user of this.faceDescriptors) {
                                    if (!user.descriptor || user.descriptor.length !== 128) continue;

                                    const distance = this.calculateEuclideanDistance(capturedDescriptor,
                                        user.descriptor);
                                    if (distance < bestDistance) {
                                        bestDistance = distance;
                                        bestMatch = user;
                                    }
                                }

                                // Auto-login if match is found and good enough
                                const threshold = 0.6;
                                if (bestMatch && bestDistance < threshold) {
                                    // Draw green box for matched face
                                    const {
                                        x,
                                        y,
                                        width,
                                        height
                                    } = resizedDetections[0].detection.box;

                                    ctx.strokeStyle = '#10B981';
                                    ctx.lineWidth = 3;
                                    ctx.strokeRect(x, y, width, height);

                                    ctx.fillStyle = '#10B981';
                                    ctx.fillRect(x, y - 30, width, 30);
                                    ctx.fillStyle = 'white';
                                    ctx.font = '14px Arial';
                                    ctx.fillText(`✓ ${bestMatch.name} (${bestDistance.toFixed(2)})`, x + 10,
                                        y - 10);

                                    // Auto-login after 2 seconds
                                    setTimeout(() => {
                                        if (!this.isProcessing) {
                                            this.loginWithFace(bestMatch.user_id);
                                        }
                                    }, 2000);

                                    return;
                                } else {
                                    // Draw yellow box for detected but unmatched face
                                    const {
                                        x,
                                        y,
                                        width,
                                        height
                                    } = resizedDetections[0].detection.box;
                                    ctx.strokeStyle = '#F59E0B';
                                    ctx.lineWidth = 2;
                                    ctx.strokeRect(x, y, width, height);

                                    ctx.fillStyle = '#F59E0B';
                                    ctx.fillRect(x, y - 30, width, 30);
                                    ctx.fillStyle = 'white';
                                    ctx.font = '14px Arial';
                                    ctx.fillText(`Wajah Terdeteksi (${bestDistance?.toFixed(2) || '?'})`,
                                        x + 10, y - 10);
                                }
                            } else {
                                // Draw blue box for detected face (no registered users)
                                const {
                                    x,
                                    y,
                                    width,
                                    height
                                } = resizedDetections[0].detection.box;
                                ctx.strokeStyle = '#3B82F6';
                                ctx.lineWidth = 2;
                                ctx.strokeRect(x, y, width, height);

                                ctx.fillStyle = '#3B82F6';
                                ctx.fillRect(x, y - 30, width, 30);
                                ctx.fillStyle = 'white';
                                ctx.font = '14px Arial';
                                ctx.fillText('Wajah Terdeteksi', x + 10, y - 10);
                            }
                        }

                    } catch (error) {
                        console.error('Face detection error:', error);
                    }
                }, 100); // Check every 100ms for responsive detection
            }

            async captureAndLogin() {
                console.log('=== CAPTURE AND LOGIN STARTED ===');

                if (!this.isCameraActive) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Kamera Error',
                        text: 'Harap mulai kamera terlebih dahulu',
                        confirmButtonColor: '#3b82f6'
                    });
                    return;
                }

                if (this.isProcessing) {
                    console.log('Already processing, skipping...');
                    return;
                }

                try {
                    this.isProcessing = true;
                    this.showFaceDetectionOverlay(true);
                    this.captureFaceBtn.disabled = true;

                    // Use current detection if available
                    let detection = this.currentDetection;

                    if (!detection) {
                        // Capture frame
                        const canvas = document.createElement('canvas');
                        canvas.width = this.faceVideo.videoWidth;
                        canvas.height = this.faceVideo.videoHeight;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(this.faceVideo, 0, 0);

                        console.log('Detecting face from snapshot...');

                        // Detect face from snapshot
                        const detections = await faceapi.detectAllFaces(canvas,
                                new faceapi.TinyFaceDetectorOptions())
                            .withFaceLandmarks()
                            .withFaceDescriptors();

                        if (detections.length === 0) {
                            throw new Error('Tidak ada wajah yang terdeteksi. Pastikan wajah Anda terlihat jelas.');
                        }

                        detection = detections[0];
                    }

                    const capturedDescriptor = Array.from(detection.descriptor);
                    console.log(`Captured descriptor: ${capturedDescriptor.length} values`);

                    // Check if we have registered faces
                    if (this.faceDescriptors.length === 0) {
                        throw new Error('Belum ada wajah yang terdaftar di sistem.');
                    }

                    // Find match with detailed logging
                    let bestMatch = null;
                    let bestDistance = Infinity;
                    let allDistances = [];

                    console.log(`Comparing with ${this.faceDescriptors.length} registered faces...`);

                    for (const user of this.faceDescriptors) {
                        if (!user.descriptor || user.descriptor.length !== 128) {
                            console.warn(`Skipping user ${user.name} - invalid descriptor`);
                            continue;
                        }

                        const distance = this.calculateEuclideanDistance(capturedDescriptor, user.descriptor);
                        allDistances.push({
                            user_id: user.user_id,
                            name: user.name,
                            distance: distance
                        });

                        if (distance < bestDistance) {
                            bestDistance = distance;
                            bestMatch = user;
                        }
                    }

                    // Log all distances
                    console.table(allDistances.map(d => ({
                        User: d.name,
                        Distance: d.distance.toFixed(4)
                    })));

                    console.log(`Best match: ${bestMatch?.name} (distance: ${bestDistance?.toFixed(4)})`);

                    // Threshold untuk face recognition
                    const threshold = 0.6;

                    if (bestMatch && bestDistance < threshold) {
                        console.log(`✅ MATCH FOUND! Logging in as ${bestMatch.name}`);
                        await this.loginWithFace(bestMatch.user_id);
                    } else {
                        console.log(`❌ NO MATCH FOUND`);
                        console.log(`Best distance ${bestDistance?.toFixed(4)} > threshold ${threshold}`);

                        this.showFaceDetectionOverlay(false);
                        this.captureFaceBtn.disabled = false;
                        this.isProcessing = false;

                        let errorMessage = `Wajah tidak dikenali.\n\n`;
                        errorMessage += `Jarak terdekat: ${bestDistance?.toFixed(4)} (threshold: ${threshold})\n\n`;

                        if (this.faceDescriptors.length > 0) {
                            errorMessage += `User terdaftar:\n`;
                            allDistances.slice(0, 3).forEach(d => {
                                errorMessage += `• ${d.name}: ${d.distance.toFixed(4)}\n`;
                            });
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Login Gagal',
                            text: errorMessage,
                            confirmButtonColor: '#3b82f6'
                        });
                    }

                } catch (error) {
                    console.error('Capture and login error:', error);
                    this.showFaceDetectionOverlay(false);
                    this.captureFaceBtn.disabled = false;
                    this.isProcessing = false;

                    Swal.fire({
                        icon: 'error',
                        title: 'Login Gagal',
                        text: error.message,
                        confirmButtonColor: '#3b82f6'
                    });
                }
            }

            async loginWithFace(userId) {
                try {
                    console.log(`Attempting face login for user ID: ${userId}`);

                    this.showFaceMatchedOverlay(true);
                    this.disableAllButtons(true);
                    this.isProcessing = true;

                    this.showFaceStatus('Wajah dikenali! Mengarahkan ke dashboard...', 'success');

                    const response = await fetch(this.faceLoginForm.action, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                        },
                        body: JSON.stringify({
                            user_id: userId
                        })
                    });

                    const result = await response.json();
                    console.log('Face login response:', result);

                    if (result.success && result.redirect) {
                        window.location.href = result.redirect;
                    } else {
                        throw new Error(result.message || 'Login gagal');
                    }

                } catch (error) {
                    console.error('Face login error:', error);

                    this.showFaceMatchedOverlay(false);
                    this.disableAllButtons(false);
                    this.isProcessing = false;

                    Swal.fire({
                        icon: 'error',
                        title: 'Login Gagal',
                        text: error.message,
                        confirmButtonColor: '#3b82f6'
                    });
                }
            }

            disableAllButtons(disabled) {
                const buttons = [
                    this.captureFaceBtn,
                    this.startFaceRecognitionBtn,
                    this.switchToPasswordBtn
                ];

                buttons.forEach(btn => {
                    if (btn) btn.disabled = disabled;
                });
            }

            // ========================
            // FACE REGISTRATION FUNCTIONS
            // ========================
            async openFaceRegistrationModal() {
                try {
                    this.faceRegisterModal.classList.remove('hidden');
                    this.faceRegisterModal.classList.add('flex');

                    await this.startRegistrationCamera();

                } catch (error) {
                    console.error('Error opening registration modal:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Registration Error',
                        text: 'Gagal membuka modal registrasi: ' + error.message,
                        confirmButtonColor: '#3b82f6'
                    });
                }
            }

            closeFaceRegistrationModal() {
                this.faceRegisterModal.classList.add('hidden');
                this.faceRegisterModal.classList.remove('flex');

                this.stopRegistrationCamera();
                this.resetRegistrationForm();
            }

            async startRegistrationCamera() {
                try {
                    this.stopRegistrationCamera();

                    const constraints = {
                        video: {
                            width: {
                                ideal: 640
                            },
                            height: {
                                ideal: 480
                            },
                            facingMode: this.useFrontCamera ? 'user' : 'environment'
                        },
                        audio: false
                    };

                    this.registerStream = await navigator.mediaDevices.getUserMedia(constraints);
                    this.registerVideo.srcObject = this.registerStream;
                    this.registerOverlay.classList.add('hidden');

                } catch (error) {
                    console.error('Error accessing registration camera:', error);
                    this.showRegisterStatus('error', 'Tidak dapat mengakses kamera: ' + error.message);

                    this.registerOverlay.innerHTML = `
                <div class="text-center text-white">
                    <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                    <p class="font-semibold">Kamera Error</p>
                    <p class="text-sm mt-1">${error.message}</p>
                </div>
            `;
                }
            }

            stopRegistrationCamera() {
                if (this.registerStream) {
                    this.registerStream.getTracks().forEach(track => track.stop());
                    this.registerStream = null;
                }

                if (this.registerVideo) {
                    this.registerVideo.srcObject = null;
                }

                this.registerOverlay.classList.remove('hidden');
                this.registerOverlay.innerHTML = `
            <div class="text-center text-white">
                <i class="fas fa-video-slash text-2xl mb-2"></i>
                <p>Menyalakan kamera...</p>
            </div>
        `;
            }

            switchRegistrationCamera() {
                this.useFrontCamera = !this.useFrontCamera;
                this.startRegistrationCamera();
            }

            captureRegistrationPhoto() {
                try {
                    const canvas = document.createElement('canvas');
                    canvas.width = this.registerVideo.videoWidth;
                    canvas.height = this.registerVideo.videoHeight;
                    canvas.getContext('2d').drawImage(this.registerVideo, 0, 0);

                    this.capturedImage = canvas.toDataURL('image/jpeg', 0.8);
                    this.previewImage.src = this.capturedImage;
                    this.capturedPreview.classList.remove('hidden');
                    this.retakePhotoBtn.classList.remove('hidden');
                    this.registerCaptureBtn.classList.add('hidden');

                    this.showRegisterStatus('success', 'Wajah berhasil diambil! Pilih pengguna dan simpan.');
                    this.updateSaveButtonState();

                } catch (error) {
                    console.error('Error capturing photo:', error);
                    this.showRegisterStatus('error', 'Gagal mengambil foto: ' + error.message);
                }
            }

            retakeRegistrationPhoto() {
                this.capturedImage = null;
                this.capturedPreview.classList.add('hidden');
                this.retakePhotoBtn.classList.add('hidden');
                this.registerCaptureBtn.classList.remove('hidden');
                this.saveFaceBtn.disabled = true;
                this.registerStatus.classList.add('hidden');
            }

            updateSaveButtonState() {
                this.saveFaceBtn.disabled = !this.userSelect.value || !this.capturedImage;
            }

            async saveFaceRegistration() {
                const userId = this.userSelect.value;
                const faceName = this.faceNameInput.value;

                if (!userId || !this.capturedImage) {
                    this.showRegisterStatus('error', 'Harap pilih pengguna dan ambil foto terlebih dahulu');
                    return;
                }

                try {
                    this.showRegisterStatus('loading', 'Memproses data wajah...');
                    this.saveFaceBtn.disabled = true;

                    // Extract face descriptor
                    const img = await faceapi.fetchImage(this.capturedImage);
                    const detection = await faceapi.detectSingleFace(img,
                            new faceapi.TinyFaceDetectorOptions())
                        .withFaceLandmarks()
                        .withFaceDescriptor();

                    if (!detection) {
                        throw new Error('Tidak ada wajah yang terdeteksi dalam foto. Pastikan wajah terlihat jelas.');
                    }

                    // Convert descriptor to array
                    const descriptor = Array.from(detection.descriptor);

                    // Validate descriptor
                    if (descriptor.length !== 128) {
                        throw new Error('Deskriptor wajah tidak valid. Diharapkan 128 nilai.');
                    }

                    // Calculate score
                    const score = this.calculateFaceScore(descriptor);

                    // Submit form to Laravel
                    this.registerUserId.value = userId;
                    this.registerDescriptor.value = JSON.stringify(descriptor);
                    this.registerScore.value = score;

                    console.log('Saving face registration...');
                    console.log('User ID:', userId);
                    console.log('Descriptor length:', descriptor.length);
                    console.log('Score:', score);

                    this.faceRegisterForm.submit();

                } catch (error) {
                    console.error('Error saving face:', error);

                    let errorMessage = 'Gagal menyimpan wajah: ';
                    errorMessage += error.message;

                    this.showRegisterStatus('error', errorMessage);
                    this.saveFaceBtn.disabled = false;
                }
            }

            resetRegistrationForm() {
                if (this.userSelect) this.userSelect.value = '';
                if (this.faceNameInput) this.faceNameInput.value = '';
                this.capturedImage = null;
                if (this.saveFaceBtn) this.saveFaceBtn.disabled = true;
                if (this.capturedPreview) this.capturedPreview.classList.add('hidden');
                if (this.retakePhotoBtn) this.retakePhotoBtn.classList.add('hidden');
                if (this.registerCaptureBtn) this.registerCaptureBtn.classList.remove('hidden');
                if (this.registerStatus) this.registerStatus.classList.add('hidden');
            }

            // ========================
            // HELPER FUNCTIONS
            // ========================
            calculateEuclideanDistance(desc1, desc2) {
                if (!desc1 || !desc2 || !Array.isArray(desc1) || !Array.isArray(desc2)) {
                    console.error('Invalid descriptors for distance calculation');
                    return Infinity;
                }

                if (desc1.length !== 128 || desc2.length !== 128) {
                    console.error(`Descriptor length mismatch: ${desc1.length} vs ${desc2.length}`);
                    return Infinity;
                }

                let sum = 0;

                for (let i = 0; i < 128; i++) {
                    const diff = desc1[i] - desc2[i];
                    sum += diff * diff;
                }

                return Math.sqrt(sum);
            }

            calculateFaceScore(descriptor) {
                if (!descriptor || descriptor.length !== 128) {
                    return 0.5;
                }

                // Calculate variance
                const mean = descriptor.reduce((a, b) => a + b, 0) / descriptor.length;
                const variance = descriptor.reduce((a, b) => a + Math.pow(b - mean, 2), 0) / descriptor.length;

                // Higher variance usually means better descriptor
                const varianceScore = Math.min(variance * 10, 1);

                // Check for extreme values
                const validValues = descriptor.filter(v => v >= -2 && v <= 2).length;
                const validityScore = validValues / descriptor.length;

                // Final score
                const finalScore = (varianceScore * 0.6) + (validityScore * 0.4);

                return Math.max(0.5, Math.min(0.99, finalScore));
            }

            showFaceStatus(message, type = 'info', duration = 0) {
                if (!this.faceStatus) return;

                this.faceStatus.classList.remove('hidden');

                const colors = {
                    loading: 'bg-blue-50 border-blue-200 text-blue-800',
                    success: 'bg-green-50 border-green-200 text-green-800',
                    error: 'bg-red-50 border-red-200 text-red-800',
                    info: 'bg-gray-50 border-gray-200 text-gray-800'
                };

                const icons = {
                    loading: 'fas fa-spinner fa-spin',
                    success: 'fas fa-check-circle',
                    error: 'fas fa-exclamation-circle',
                    info: 'fas fa-info-circle'
                };

                this.faceStatus.innerHTML = `
            <div class="${colors[type] || colors.info} border rounded-xl p-4">
                <div class="flex items-center">
                    <i class="${icons[type] || icons.info} mr-3"></i>
                    <div>${message}</div>
                </div>
            </div>
        `;

                if (duration > 0) {
                    setTimeout(() => {
                        this.faceStatus.classList.add('hidden');
                    }, duration);
                }
            }

            showFaceDetectionOverlay(show) {
                if (this.faceDetectionOverlay) {
                    this.faceDetectionOverlay.classList.toggle('hidden', !show);
                }
            }

            showFaceMatchedOverlay(show) {
                if (this.faceMatchedOverlay) {
                    this.faceMatchedOverlay.classList.toggle('hidden', !show);
                }
            }

            showRegisterStatus(type, message) {
                if (!this.registerStatus) return;

                this.registerStatus.classList.remove('hidden');

                const colors = {
                    loading: 'bg-blue-50 border-blue-200 text-blue-800',
                    success: 'bg-green-50 border-green-200 text-green-800',
                    error: 'bg-red-50 border-red-200 text-red-800'
                };

                const icons = {
                    loading: 'fas fa-spinner fa-spin',
                    success: 'fas fa-check-circle',
                    error: 'fas fa-exclamation-circle'
                };

                this.registerStatus.innerHTML = `
            <div class="${colors[type] || 'bg-gray-50'} border rounded-xl p-4">
                <div class="flex items-center">
                    <i class="${icons[type] || 'fas fa-info-circle'} mr-3"></i>
                    <div>${message}</div>
                </div>
            </div>
        `;
            }

            startFaceRecognition() {
                this.initializeFaceRecognition();
            }

            stopFaceRecognition() {
                if (this.detectionInterval) {
                    clearInterval(this.detectionInterval);
                    this.detectionInterval = null;
                }

                if (this.videoStream) {
                    this.videoStream.getTracks().forEach(track => {
                        track.stop();
                    });
                    this.videoStream = null;
                }

                if (this.faceVideo) {
                    this.faceVideo.srcObject = null;
                }

                this.isCameraActive = false;
                this.currentDetection = null;
            }

            cleanup() {
                this.stopFaceRecognition();
                this.stopRegistrationCamera();
            }
        }

        // Initialize the system when DOM is loaded
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof faceapi === 'undefined') {
                console.error('FaceAPI is not loaded');
                Swal.fire({
                    icon: 'error',
                    title: 'Missing Library',
                    text: 'FaceAPI library is required for face recognition.',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#3b82f6'
                });
                return;
            }

            try {
                // Create global instance
                window.faceRecognitionSystem = new FaceRecognitionSystem();
                console.log('Face Recognition System initialized');

                // Debug info
                console.log('=== SYSTEM INFO ===');
                console.log('Face descriptors loaded:', window.faceRecognitionSystem.faceDescriptors.length);
                console.log('Raw data count:', window.faceDescriptorsData?.length || 0);

            } catch (error) {
                console.error('Failed to initialize Face Recognition System:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Initialization Error',
                    text: 'Failed to initialize face recognition system: ' + error.message,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#3b82f6'
                });
            }
        });
    </script>
    {{-- <!-- Debug Info Panel (can be hidden in production) -->
    <div id="debugPanel"
        style="position: fixed; bottom: 10px; right: 10px; background: rgba(0,0,0,0.8); color: white; padding: 10px; border-radius: 5px; font-size: 12px; max-width: 300px; z-index: 9999;">
        <h3 style="margin: 0 0 5px 0;">Debug Info</h3>
        <div id="debugContent"></div>
    </div> --}}

    <script>
        // Request camera permission on page load
        async function requestCameraPermission() {
            try {
                const constraints = {
                    video: {
                        facingMode: {
                            ideal: 'user'
                        },
                        width: {
                            ideal: 640
                        },
                        height: {
                            ideal: 480
                        }
                    },
                    audio: false
                };

                const stream = await navigator.mediaDevices.getUserMedia(constraints);

                // Stop the stream immediately after permission is granted
                stream.getTracks().forEach(track => track.stop());

                console.log('Camera permission granted');
                updateDebugInfo('Camera permission: ✓ Granted');

            } catch (error) {
                console.warn('Camera permission denied or unavailable:', error);
                console.error('Error details:', error.name, error.message);
                updateDebugInfo(`Camera permission: ✗ ${error.name}`);

                // Show error to user on mobile
                if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Izin Kamera Diperlukan',
                        text: 'Silakan izinkan akses kamera di pengaturan browser untuk menggunakan face recognition.',
                        confirmButtonColor: '#3b82f6'
                    });
                }
            }
        };


        // Debug function to update panel
        function updateDebugInfo(info) {
            const debugContent = document.getElementById('debugContent');
            if (debugContent) {
                debugContent.innerHTML = info;
            }
        }

        // Update debug info when system initializes
        document.addEventListener('DOMContentLoaded', function() {
            // Initial debug info
            updateDebugInfo(`
        Face Descriptors from DB: ${window.faceDescriptorsData?.length || 0}<br>
        Users for Registration: ${window.usersForRegistration?.length || 0}
    `);

            // Update when face recognition system loads
            setTimeout(() => {
                if (window.faceRecognitionSystem) {
                    updateDebugInfo(`
                System: Initialized<br>
                Loaded Faces: ${window.faceRecognitionSystem.faceDescriptors.length}<br>
                Camera: ${window.faceRecognitionSystem.isCameraActive ? 'Active' : 'Inactive'}
            `);
                }
            }, 1000);
        });
    </script>
</body>

</html>
