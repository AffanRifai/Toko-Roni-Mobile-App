<?php $__env->startSection('title', 'Registrasi Wajah'); ?>

<?php $__env->startSection('content'); ?>
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Registrasi Wajah</h1>
                        <p class="mt-2 text-gray-600">
                            Registrasi biometrik untuk <span class="font-semibold"><?php echo e($user->name); ?></span>
                        </p>
                    </div>
                    <a href="<?php echo e(route('users.index')); ?>"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Kembali
                    </a>
                </div>
            </div>

            <!-- User Info -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-8">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <?php if($user->profile_image): ?>
                                <img src="<?php echo e(asset('storage/' . $user->profile_image)); ?>" alt="<?php echo e($user->name); ?>"
                                    class="w-16 h-16 rounded-full object-cover">
                            <?php else: ?>
                                <div class="w-16 h-16 rounded-full bg-blue-100 flex items-center justify-center">
                                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900"><?php echo e($user->name); ?></h2>
                            <div class="mt-1 flex flex-wrap items-center gap-4">
                                <div class="flex items-center text-gray-600">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    <span class="text-sm"><?php echo e($user->email); ?></span>
                                </div>
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                    <?php echo e($user->role === 'admin'
                                        ? 'bg-purple-100 text-purple-800'
                                        : ($user->role === 'owner'
                                            ? 'bg-yellow-100 text-yellow-800'
                                            : ($user->role === 'gudang'
                                                ? 'bg-green-100 text-green-800'
                                                : 'bg-blue-100 text-blue-800'))); ?>">
                                    <?php echo e(ucfirst($user->role)); ?>

                                </span>
                                <?php if($user->face_registered_at): ?>
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Terdaftar
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php if($user->face_registered_at): ?>
                        <div class="text-right">
                            <p class="text-sm text-gray-500">Terdaftar pada</p>
                            <p class="font-semibold text-gray-900"><?php echo e($user->face_registered_at->format('d/m/Y H:i')); ?></p>
                            <?php if($user->face_score): ?>
                                <div class="flex items-center justify-end mt-1">
                                    <span class="text-sm font-medium text-green-600 mr-2">
                                        Skor: <?php echo e(number_format($user->face_score * 100, 1)); ?>%
                                    </span>
                                    <div class="w-24 bg-gray-200 rounded-full h-2">
                                        <div class="bg-green-500 h-2 rounded-full"
                                            style="width: <?php echo e($user->face_score * 100); ?>%"></div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Loading Models -->
            <div id="loadingModels" class="text-center bg-white rounded-xl shadow-sm border border-gray-200 p-12 mb-8">
                <div class="inline-block animate-spin rounded-full h-16 w-16 border-t-2 border-b-2 border-blue-500 mb-6">
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Memuat Sistem Pengenalan Wajah</h3>
                <p class="text-gray-600 mb-6">Menyiapkan model AI untuk deteksi wajah...</p>
                <div class="w-full max-w-md mx-auto bg-gray-200 rounded-full h-2">
                    <div id="loadingProgress" class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                        style="width: 0%"></div>
                </div>
                <p id="loadingText" class="text-sm text-gray-500 mt-3">Mengunduh model AI...</p>
            </div>

            <!-- Camera Section -->
            <div id="cameraSection" class="hidden">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Left Column - Camera & Controls -->
                    <div class="lg:col-span-2">
                        <!-- Camera Container -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
                            <div class="p-6 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                    Kamera
                                </h3>
                            </div>
                            <div class="relative bg-black aspect-video flex items-center justify-center">
                                <video id="video" class="w-full h-auto max-h-[480px]" autoplay playsinline
                                    muted></video>
                                <canvas id="canvas"
                                    class="absolute top-0 left-0 w-full h-full pointer-events-none"></canvas>

                                <!-- Status Indicator -->
                                <div id="statusIndicator"
                                    class="absolute top-4 right-4 bg-black/75 text-white px-3 py-2 rounded-full text-sm flex items-center backdrop-blur-sm">
                                    <div class="w-2 h-2 rounded-full bg-yellow-500 mr-2"></div>
                                    <span>Menyiapkan</span>
                                </div>

                                <!-- Face Count -->
                                <div id="faceCount"
                                    class="absolute bottom-4 left-4 bg-black/75 text-white px-3 py-2 rounded-full text-sm flex items-center hidden backdrop-blur-sm">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13 0h-6" />
                                    </svg>
                                    <span>Wajah: <span id="faceCountText">0</span></span>
                                </div>

                                <!-- Overlay Grid -->
                                <div
                                    class="absolute inset-0 pointer-events-none border-2 border-blue-400/30 rounded-lg m-2">
                                    <div
                                        class="absolute top-1/2 left-1/2 w-48 h-48 -translate-x-1/2 -translate-y-1/2 border-2 border-white/50 rounded-lg">
                                    </div>
                                </div>
                            </div>

                            <!-- Camera Details -->
                            <div class="p-4 bg-gray-50 border-t border-gray-200">
                                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2">
                                    <div>
                                        <span id="cameraStatus" class="text-gray-700">Kamera belum diaktifkan</span>
                                        <div id="cameraDetails" class="text-sm text-gray-500"></div>
                                    </div>
                                    <div class="text-gray-600 text-sm">
                                        <span id="faceCountDisplay">0</span> wajah terdeteksi
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Score Display -->
                        <div id="scoreContainer"
                            class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6 hidden">
                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start mb-4 gap-4">
                                <div class="flex-1">
                                    <h4 class="text-lg font-semibold text-gray-900 flex items-center">
                                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                        </svg>
                                        Kualitas Deteksi Wajah
                                    </h4>
                                    <div class="flex items-baseline gap-2 mt-2">
                                        <p id="scoreValue" class="text-3xl font-bold text-green-600">0%</p>
                                        <p class="text-sm text-gray-500">(Minimal 60% disarankan)</p>
                                    </div>
                                </div>
                                <div id="scoreBadge">
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.346 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                        </svg>
                                        Perlu Perbaikan
                                    </span>
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="flex justify-between text-sm text-gray-600 mb-1">
                                    <span>Rendah</span>
                                    <span>Tinggi</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div id="scoreProgressBar"
                                        class="bg-green-500 h-2 rounded-full transition-all duration-300"
                                        style="width: 0%"></div>
                                </div>
                            </div>

                            <div id="scoreRating" class="flex items-center text-gray-700 bg-gray-50 p-3 rounded-lg">
                                <svg id="ratingIcon" class="w-6 h-6 mr-2 text-yellow-500" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span id="ratingText" class="font-medium">Belum diukur</span>
                            </div>
                        </div>

                        <!-- Camera Controls -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
                            <button id="startCamera"
                                class="w-full px-6 py-4 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                Aktifkan Kamera
                            </button>
                            <button id="capture" disabled
                                class="w-full px-6 py-4 bg-gray-300 text-gray-500 font-medium rounded-lg cursor-not-allowed flex items-center justify-center transition-all duration-200 disabled:opacity-50">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Simpan Wajah
                            </button>
                        </div>

                        <div class="text-center">
                            <p class="text-sm text-gray-500 flex items-center justify-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.346 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                </svg>
                                Pastikan skor deteksi minimal 60% untuk hasil terbaik
                            </p>
                        </div>
                    </div>

                    <!-- Right Column - Instructions & Stats -->
                    <div class="space-y-6">
                        <!-- Instructions -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Panduan Registrasi
                            </h3>
                            <ol class="space-y-3">
                                <?php $__currentLoopData = ['Aktifkan kamera dengan tombol di samping', 'Izinkan akses kamera jika browser meminta', 'Posisikan wajah di tengah frame kamera', 'Pastikan pencahayaan cukup dan wajah jelas', 'Tunggu indikator hijau menyala', 'Pastikan hanya satu wajah dalam frame', 'Klik Simpan Wajah untuk menyimpan data']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $instruction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li class="flex items-start">
                                        <span
                                            class="flex-shrink-0 w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-sm font-medium mr-3">
                                            <?php echo e($index + 1); ?>

                                        </span>
                                        <span class="text-gray-700"><?php echo e($instruction); ?></span>
                                    </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ol>

                            <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                <div class="flex">
                                    <svg class="w-5 h-5 text-yellow-400 mr-2 flex-shrink-0 mt-0.5" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                    </svg>
                                    <div>
                                        <p class="text-sm font-medium text-yellow-800">Tips Optimal</p>
                                        <p class="text-sm text-yellow-700">Gunakan background polos, cahaya dari depan, dan
                                            hindari topi/kacamata gelap</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Status Indicators -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Status Deteksi</h3>
                            <div class="space-y-4">
                                <div class="flex items-center" id="statusNotReady">
                                    <div class="w-3 h-3 rounded-full bg-red-500 mr-3"></div>
                                    <span class="text-gray-700">Belum Siap</span>
                                </div>
                                <div class="flex items-center" id="statusCameraActive">
                                    <div class="w-3 h-3 rounded-full bg-gray-300 mr-3"></div>
                                    <span class="text-gray-700">Kamera Aktif</span>
                                </div>
                                <div class="flex items-center" id="statusReady">
                                    <div class="w-3 h-3 rounded-full bg-gray-300 mr-3"></div>
                                    <span class="text-gray-700">Siap Simpan</span>
                                </div>
                            </div>
                        </div>

                        <!-- System Info -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Sistem</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Model AI:</span>
                                    <span id="modelStatus" class="font-medium text-gray-600">Memuat...</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Wajah Terdeteksi:</span>
                                    <span id="detectionStatus" class="font-medium text-red-600">Tidak</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Frame Rate:</span>
                                    <span id="frameRate" class="font-medium text-gray-900">0 fps</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Kualitas:</span>
                                    <span id="qualityStatus" class="font-medium text-gray-900">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-8 pt-6 border-t border-gray-200">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="text-sm text-gray-600 flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        Data wajah dienkripsi dan disimpan dengan aman
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <?php if($user->face_registered_at): ?>
                            <button id="resetFace" onclick="showResetModal()"
                                class="px-4 py-2 border border-red-300 text-red-700 font-medium rounded-lg hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200 flex items-center whitespace-nowrap">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Reset Registrasi
                            </button>
                        <?php endif; ?>
                        <button onclick="location.reload()"
                            class="px-4 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200 flex items-center whitespace-nowrap">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Refresh Halaman
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reset Modal -->
    <div id="resetModal" class="hidden fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.346 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Konfirmasi Reset</h3>
                        <p class="text-gray-600">Reset registrasi wajah?</p>
                    </div>
                </div>

                <div class="mb-6">
                    <p class="text-gray-700 mb-3">Untuk pengguna:</p>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <h4 class="font-semibold text-gray-900"><?php echo e($user->name); ?></h4>
                        <p class="text-gray-600"><?php echo e($user->email); ?></p>
                    </div>
                    <p class="text-red-600 text-sm mt-4 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.346 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                        Data wajah akan dihapus permanen dan tidak dapat dikembalikan
                    </p>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeResetModal()"
                        class="px-4 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                        Batal
                    </button>
                    <button type="button" onclick="resetFaceRegistration()"
                        class="px-4 py-2 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200">
                        Reset Sekarang
                    </button>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
    <style>
        @keyframes fade-in {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fade-in 0.5s ease-out;
        }

        #canvas {
            border: 2px solid rgba(255, 255, 255, 0.3);
            box-shadow: inset 0 0 20px rgba(102, 126, 234, 0.3);
        }

        .status-active {
            background-color: #3b82f6 !important;
        }

        .status-ready {
            background-color: #10b981 !important;
        }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <script>
        // State management
        const FaceRegistration = {
            // DOM Elements
            video: document.getElementById('video'),
            canvas: document.getElementById('canvas'),
            ctx: null,
            startBtn: document.getElementById('startCamera'),
            captureBtn: document.getElementById('capture'),
            loadingBox: document.getElementById('loadingModels'),
            cameraSection: document.getElementById('cameraSection'),

            // State variables
            stream: null,
            detectInterval: null,
            isCameraActive: false,
            modelsLoaded: false,
            currentDescriptor: null,
            currentScore: 0,

            // Configuration
            MODEL_URL: '/models',
            DETECTION_INTERVAL: 500, // ms
            MIN_SCORE: 0.6,

            // Initialize
            init() {
                console.log('Initializing Face Registration System...');

                // Initialize canvas context
                this.ctx = this.canvas.getContext('2d');

                // Setup event listeners
                this.setupEventListeners();

                // Load models
                this.loadModels();
            },

            // Event Listeners
            setupEventListeners() {
                if (this.startBtn) {
                    this.startBtn.addEventListener('click', () => this.toggleCamera());
                }

                if (this.captureBtn) {
                    this.captureBtn.addEventListener('click', () => this.captureFace());
                }

                // Handle page visibility changes
                document.addEventListener('visibilitychange', () => {
                    if (document.hidden && this.isCameraActive) {
                        this.stopCamera();
                    }
                });

                // Handle window unload
                window.addEventListener('beforeunload', () => {
                    if (this.isCameraActive) {
                        this.stopCamera();
                    }
                });
            },

            // Model Loading
            async loadModels() {
                try {
                    this.updateLoading('Memuat model AI...', 20);

                    // Try multiple model loading strategies
                    await this.loadModelsWithFallback();

                    this.updateLoading('Model AI berhasil dimuat', 100);
                    this.modelsLoaded = true;
                    document.getElementById('modelStatus').textContent = 'Siap';
                    document.getElementById('modelStatus').className = 'font-medium text-green-600';

                    // Show camera section
                    setTimeout(() => {
                        this.loadingBox.classList.add('hidden');
                        this.cameraSection.classList.remove('hidden');
                        this.cameraSection.classList.add('animate-fade-in');
                    }, 500);

                } catch (error) {
                    console.error('Error loading models:', error);
                    this.showModelError(error);
                }
            },

            async loadModelsWithFallback() {
                console.log('Starting model loading...');

                // Path models - sesuaikan dengan lokasi folder public/models
                const modelPaths = [
                    '/models', // Local
                    '/face-api/models', // Alternative path
                    'https://justadudewhohacks.github.io/face-api.js/models', // CDN
                ];

                let modelsLoaded = false;
                let lastError = null;

                for (let i = 0; i < modelPaths.length; i++) {
                    const path = modelPaths[i];
                    console.log(`Trying path: ${path}`);

                    try {
                        this.updateLoading(`Memuat model dari: ${path}`, 30 + (i * 20));

                        // Try loading multiple model types
                        try {
                            console.log('Loading TinyFaceDetector...');
                            await faceapi.loadTinyFaceDetectorModel(path);
                        } catch (e) {
                            console.log('TinyFaceDetector failed, trying SsdMobilenetv1...');
                            await faceapi.loadSsdMobilenetv1Model(path);
                        }

                        // Load additional models
                        console.log('Loading FaceLandmarkModel...');
                        await faceapi.loadFaceLandmarkModel(path);

                        console.log('Loading FaceRecognitionModel...');
                        await faceapi.loadFaceRecognitionModel(path);

                        console.log('All models loaded successfully from:', path);
                        modelsLoaded = true;
                        this.modelPath = path;
                        break;

                    } catch (error) {
                        console.log(`Failed to load from ${path}:`, error.message);
                        lastError = error;
                        continue;
                    }
                }

                if (!modelsLoaded) {
                    throw lastError || new Error('Gagal memuat semua model AI');
                }
            },

            // Camera Functions
            async toggleCamera() {
                if (!this.modelsLoaded) {
                    this.showAlert('error', 'Model AI belum siap', 'Tunggu sampai model AI selesai dimuat');
                    return;
                }

                if (this.isCameraActive) {
                    this.stopCamera();
                } else {
                    await this.startCamera();
                }
            },

            async startCamera() {
                try {
                    this.updateUIState('starting');

                    const constraints = {
                        video: {
                            width: {
                                ideal: 640
                            },
                            height: {
                                ideal: 480
                            },
                            facingMode: 'user',
                            frameRate: {
                                ideal: 30
                            }
                        },
                        audio: false
                    };

                    this.stream = await navigator.mediaDevices.getUserMedia(constraints);
                    this.video.srcObject = this.stream;

                    // Wait for video to be ready
                    await new Promise((resolve, reject) => {
                        this.video.onloadedmetadata = () => {
                            this.video.play().then(resolve).catch(reject);
                        };
                        this.video.onerror = reject;

                        setTimeout(() => {
                            if (this.video.readyState < 2) {
                                reject(new Error('Video loading timeout'));
                            }
                        }, 5000);
                    });

                    // Setup canvas
                    this.canvas.width = this.video.videoWidth;
                    this.canvas.height = this.video.videoHeight;

                    this.isCameraActive = true;
                    this.updateUIState('active');
                    this.startFaceDetection();

                } catch (error) {
                    console.error('Camera error:', error);
                    this.handleCameraError(error);
                }
            },

            stopCamera() {
                // Stop stream
                if (this.stream) {
                    this.stream.getTracks().forEach(track => track.stop());
                    this.stream = null;
                }

                // Clear interval
                if (this.detectInterval) {
                    clearInterval(this.detectInterval);
                    this.detectInterval = null;
                }

                // Clear canvas
                if (this.ctx) {
                    this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
                }

                // Reset video
                this.video.srcObject = null;

                // Reset state
                this.isCameraActive = false;
                this.currentDescriptor = null;
                this.currentScore = 0;

                // Update UI
                this.updateUIState('inactive');
            },

            // Face Detection
            // startFaceDetection() - GANTI SEMUA
            startFaceDetection() {
                if (this.detectInterval) {
                    clearInterval(this.detectInterval);
                }

                // Setup canvas size matching video
                this.canvas.width = this.video.videoWidth;
                this.canvas.height = this.video.videoHeight;
                const displaySize = {
                    width: this.video.videoWidth,
                    height: this.video.videoHeight
                };

                faceapi.matchDimensions(this.canvas, displaySize);

                let fpsCounter = 0;
                let lastFpsUpdate = Date.now();

                this.detectInterval = setInterval(async () => {
                    if (!this.isCameraActive || !this.video || this.video.readyState < 2) return;

                    try {
                        // Clear canvas
                        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);

                        // Draw video frame on canvas
                        this.ctx.drawImage(this.video, 0, 0, this.canvas.width, this.canvas.height);

                        // Update FPS counter
                        fpsCounter++;
                        const now = Date.now();
                        if (now - lastFpsUpdate >= 1000) {
                            document.getElementById('frameRate').textContent = `${fpsCounter} fps`;
                            fpsCounter = 0;
                            lastFpsUpdate = now;
                        }

                        // Try multiple detection methods
                        let detections = [];

                        // Method 1: TinyFaceDetector
                        try {
                            detections = await faceapi
                                .detectAllFaces(this.video, new faceapi.TinyFaceDetectorOptions({
                                    inputSize: 128, // Smaller size for faster detection
                                    scoreThreshold: 0.1 // Lower threshold
                                }))
                                .withFaceLandmarks()
                                .withFaceDescriptors();
                        } catch (e) {
                            console.log('TinyFaceDetector failed, trying SsdMobilenetv1');

                            // Method 2: SsdMobilenetv1
                            try {
                                detections = await faceapi
                                    .detectAllFaces(this.video, new faceapi.SsdMobilenetv1Options({
                                        minConfidence: 0.3 // Lower confidence
                                    }))
                                    .withFaceLandmarks()
                                    .withFaceDescriptors();
                            } catch (e2) {
                                console.log('SsdMobilenetv1 also failed');
                            }
                        }

                        // Update face count
                        const faceCount = detections.length;
                        this.updateFaceCount(faceCount);

                        if (detections.length > 0) {
                            // Draw detections on canvas
                            const resizedDetections = faceapi.resizeResults(detections, displaySize);

                            // Draw detection boxes
                            resizedDetections.forEach(detection => {
                                const box = detection.detection.box;

                                // Draw box
                                this.ctx.strokeStyle = '#00ff00';
                                this.ctx.lineWidth = 2;
                                this.ctx.strokeRect(box.x, box.y, box.width, box.height);

                                // Draw landmarks
                                if (detection.landmarks) {
                                    this.ctx.fillStyle = '#ff0000';
                                    detection.landmarks.positions.forEach(point => {
                                        this.ctx.beginPath();
                                        this.ctx.arc(point.x, point.y, 2, 0, 2 * Math.PI);
                                        this.ctx.fill();
                                    });
                                }
                            });

                            // Get first face for registration
                            const detection = detections[0];
                            this.currentDescriptor = detection.descriptor;

                            // Calculate score (simplified)
                            this.currentScore = this.calculateSimpleScore(detection.detection.score);

                            // Update display
                            this.updateScoreDisplay(this.currentScore);

                            // Enable capture button if score is good
                            if (this.currentScore >= this.MIN_SCORE) {
                                this.captureBtn.disabled = false;
                                this.captureBtn.classList.remove('bg-gray-300', 'cursor-not-allowed',
                                    'text-gray-500');
                                this.captureBtn.classList.add('bg-green-600', 'hover:bg-green-700',
                                    'text-white');
                                this.updateUIState('ready');

                                // Draw green overlay for ready state
                                const box = detection.detection.box;
                                this.ctx.strokeStyle = '#00ff00';
                                this.ctx.lineWidth = 3;
                                this.ctx.strokeRect(box.x, box.y, box.width, box.height);

                                // Draw "READY" text
                                this.ctx.fillStyle = '#00ff00';
                                this.ctx.font = 'bold 16px Arial';
                                this.ctx.fillText('✓ SIAP', box.x, box.y - 10);
                            } else {
                                this.captureBtn.disabled = true;
                                this.updateUIState('active');
                            }
                        } else {
                            this.currentDescriptor = null;
                            this.currentScore = 0;
                            this.hideScoreDisplay();
                            this.captureBtn.disabled = true;
                            this.captureBtn.classList.remove('bg-green-600', 'hover:bg-green-700',
                                'text-white');
                            this.captureBtn.classList.add('bg-gray-300', 'cursor-not-allowed',
                                'text-gray-500');
                            this.updateUIState('active');

                            // Draw "NO FACE" message
                            this.ctx.fillStyle = '#ff0000';
                            this.ctx.font = 'bold 20px Arial';
                            this.ctx.textAlign = 'center';
                            this.ctx.fillText('TIDAK ADA WAJAH TERDETEKSI', this.canvas.width / 2, 40);
                        }

                    } catch (error) {
                        console.error('Detection error:', error);
                        // Show error on canvas
                        this.ctx.fillStyle = '#ff0000';
                        this.ctx.font = '16px Arial';
                        this.ctx.textAlign = 'center';
                        this.ctx.fillText(`Error: ${error.message}`, this.canvas.width / 2, 30);
                    }
                }, 300); // Increase interval to 300ms for slower devices
            },

            // Tambahkan fungsi baru untuk score calculation sederhana
            calculateSimpleScore(confidenceScore) {
                if (!confidenceScore) return 0.5;

                // Normalize score: 0.5-0.9 → 0.6-1.0
                let score = Math.max(0.5, Math.min(0.9, confidenceScore));
                score = 0.6 + (score - 0.5) * 1.0; // Scale to 0.6-1.0 range

                return Math.round(score * 100) / 100;
            },


            // Score Calculation
            calculateDescriptorScore(descriptor) {
                if (!descriptor || descriptor.length === 0) return 0;

                try {
                    // Normalize descriptor values
                    const normalized = descriptor.map(v => Math.max(-1, Math.min(1, v)));

                    // Calculate variance (higher variance = better quality)
                    const mean = normalized.reduce((a, b) => a + b, 0) / normalized.length;
                    const variance = normalized.reduce((a, b) => a + Math.pow(b - mean, 2), 0) / normalized.length;

                    // Normalize score between 0.3 and 1.0
                    let score = Math.min(Math.max(variance * 5, 0.3), 1.0);

                    // Round to 2 decimal places
                    return Math.round(score * 100) / 100;
                } catch (error) {
                    console.error('Score calculation error:', error);
                    return 0;
                }
            },

            // Face Capture
            async captureFace() {
                if (!this.currentDescriptor) {
                    this.showAlert('error', 'Tidak ada wajah', 'Pastikan wajah terlihat jelas di kamera');
                    return;
                }

                const confirmed = await this.showConfirm(
                    'Konfirmasi Simpan',
                    `Simpan wajah dengan skor ${Math.round(this.currentScore * 100)}% untuk <?php echo e($user->name); ?>?`
                );

                if (!confirmed) return;

                try {
                    this.updateButtonState('saving');

                    // Hapus _method dari body karena kita pakai POST langsung
                    const response = await fetch("<?php echo e(route('users.face.store', $user->id)); ?>", {
                        method: 'POST', // Gunakan POST sesuai route
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': "<?php echo e(csrf_token()); ?>",
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            descriptor: Array.from(this.currentDescriptor),
                            score: this.currentScore
                            // HAPUS _method: 'PUT'
                        })
                    });

                    // Handle response error
                    if (!response.ok) {
                        const errorData = await response.json();
                        throw new Error(errorData.message || `HTTP ${response.status}`);
                    }

                    const data = await response.json();

                    if (data.success) {
                        this.showAlert('success', 'Berhasil!', 'Wajah berhasil diregistrasi');
                        setTimeout(() => {
                            window.location.href = "<?php echo e(route('users.index')); ?>";
                        }, 2000);
                    } else {
                        throw new Error(data.message || 'Gagal menyimpan wajah');
                    }

                } catch (error) {
                    console.error('Save error:', error);
                    this.showAlert('error', 'Gagal!', error.message || 'Terjadi kesalahan saat menyimpan');
                    this.updateButtonState('error');

                    // Reset button after 3 seconds
                    setTimeout(() => {
                        this.updateButtonState('ready');
                    }, 3000);
                }
            },

            // UI Updates
            updateUIState(state) {
                const statusIndicator = document.getElementById('statusIndicator');
                const cameraStatus = document.getElementById('cameraStatus');
                const cameraDetails = document.getElementById('cameraDetails');
                const faceCountBadge = document.getElementById('faceCount');

                switch (state) {
                    case 'starting':
                        statusIndicator.innerHTML =
                            '<div class="flex items-center"><div class="w-2 h-2 rounded-full bg-yellow-500 mr-2 animate-pulse"></div><span>Menyiapkan Kamera</span></div>';
                        cameraStatus.innerHTML = '<span class="text-yellow-600">Mengaktifkan kamera...</span>';
                        cameraDetails.innerHTML = '';
                        this.startBtn.disabled = true;
                        break;

                    case 'active':
                        statusIndicator.innerHTML =
                            '<div class="flex items-center"><div class="w-2 h-2 rounded-full bg-blue-500 mr-2"></div><span>Kamera Aktif</span></div>';
                        cameraStatus.innerHTML = '<span class="text-green-600">Kamera aktif</span>';
                        cameraDetails.innerHTML =
                            `<span class="text-sm text-gray-500">${this.video.videoWidth} × ${this.video.videoHeight}</span>`;
                        this.startBtn.innerHTML =
                            '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Matikan Kamera';
                        this.startBtn.disabled = false;
                        this.startBtn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                        this.startBtn.classList.add('bg-red-600', 'hover:bg-red-700');
                        break;

                    case 'ready':
                        statusIndicator.innerHTML =
                            '<div class="flex items-center"><div class="w-2 h-2 rounded-full bg-green-500 mr-2 animate-pulse"></div><span>Siap Simpan</span></div>';
                        break;

                    case 'multiple-faces':
                        cameraDetails.innerHTML =
                            '<span class="text-sm text-yellow-600">Terlalu banyak wajah. Pastikan hanya satu wajah dalam frame.</span>';
                        break;

                    case 'inactive':
                        statusIndicator.innerHTML =
                            '<div class="flex items-center"><div class="w-2 h-2 rounded-full bg-yellow-500 mr-2"></div><span>Menyiapkan</span></div>';
                        cameraStatus.innerHTML = '<span class="text-gray-700">Kamera belum diaktifkan</span>';
                        cameraDetails.innerHTML = '';
                        this.startBtn.innerHTML =
                            '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>Aktifkan Kamera';
                        this.startBtn.disabled = false;
                        this.startBtn.classList.remove('bg-red-600', 'hover:bg-red-700');
                        this.startBtn.classList.add('bg-blue-600', 'hover:bg-blue-700');

                        if (faceCountBadge) faceCountBadge.classList.add('hidden');
                        document.getElementById('faceCountDisplay').textContent = '0';
                        document.getElementById('detectionStatus').textContent = 'Tidak';
                        document.getElementById('detectionStatus').className = 'font-medium text-red-600';
                        break;
                }
            },

            updateFaceCount(count) {
                document.getElementById('faceCountText').textContent = count;
                document.getElementById('faceCountDisplay').textContent = count;

                const faceCountBadge = document.getElementById('faceCount');
                if (faceCountBadge) {
                    if (count > 0) {
                        faceCountBadge.classList.remove('hidden');
                    } else {
                        faceCountBadge.classList.add('hidden');
                    }
                }

                const detectionStatus = document.getElementById('detectionStatus');
                if (count === 1) {
                    detectionStatus.textContent = 'Ya';
                    detectionStatus.className = 'font-medium text-green-600';
                } else if (count > 1) {
                    detectionStatus.textContent = 'Banyak';
                    detectionStatus.className = 'font-medium text-yellow-600';
                } else {
                    detectionStatus.textContent = 'Tidak';
                    detectionStatus.className = 'font-medium text-red-600';
                }
            },

            updateScoreDisplay(score) {
                const percentage = Math.round(score * 100);

                // Update score value
                document.getElementById('scoreValue').textContent = `${percentage}%`;

                // Update progress bar
                const progressBar = document.getElementById('scoreProgressBar');
                progressBar.style.width = `${percentage}%`;

                // Update progress bar color
                if (percentage < 50) {
                    progressBar.className = 'bg-red-500 h-2 rounded-full transition-all duration-300';
                } else if (percentage < 70) {
                    progressBar.className = 'bg-yellow-500 h-2 rounded-full transition-all duration-300';
                } else if (percentage < 85) {
                    progressBar.className = 'bg-blue-500 h-2 rounded-full transition-all duration-300';
                } else {
                    progressBar.className = 'bg-green-500 h-2 rounded-full transition-all duration-300';
                }

                // Update rating
                let rating = 'Buruk';
                let badgeClass = 'bg-red-100 text-red-800';
                let badgeText = 'Perlu Perbaikan';
                let ratingText = 'Kualitas gambar buruk';

                if (percentage >= 90) {
                    rating = 'Sangat Baik';
                    badgeClass = 'bg-green-100 text-green-800';
                    badgeText = 'Sangat Baik';
                    ratingText = 'Kualitas gambar sangat baik';
                } else if (percentage >= 75) {
                    rating = 'Baik';
                    badgeClass = 'bg-blue-100 text-blue-800';
                    badgeText = 'Baik';
                    ratingText = 'Kualitas gambar baik';
                } else if (percentage >= 60) {
                    rating = 'Cukup';
                    badgeClass = 'bg-yellow-100 text-yellow-800';
                    badgeText = 'Cukup';
                    ratingText = 'Kualitas gambar cukup';
                }

                // Update badge
                document.getElementById('scoreBadge').innerHTML = `
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ${badgeClass}">
                    ${badgeText}
                </span>
            `;

                // Update rating text
                document.getElementById('ratingText').textContent = ratingText;

                // Update quality status
                document.getElementById('qualityStatus').textContent = rating;
                document.getElementById('qualityStatus').className =
                    `font-medium ${badgeClass.replace('bg-', 'text-').split(' ')[0]}`;

                // Show score container
                document.getElementById('scoreContainer').classList.remove('hidden');
            },

            hideScoreDisplay() {
                document.getElementById('scoreContainer').classList.add('hidden');
                document.getElementById('qualityStatus').textContent = '-';
                document.getElementById('qualityStatus').className = 'font-medium text-gray-900';
            },

            updateButtonState(state) {
                switch (state) {
                    case 'saving':
                        this.captureBtn.disabled = true;
                        this.captureBtn.innerHTML =
                            '<span class="flex items-center"><span class="animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2"></span>Menyimpan...</span>';
                        break;

                    case 'ready':
                        this.captureBtn.disabled = false;
                        this.captureBtn.innerHTML =
                            '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>Simpan Wajah';
                        break;

                    case 'error':
                        this.captureBtn.disabled = false;
                        this.captureBtn.innerHTML =
                            '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.346 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>Coba Lagi';
                        break;
                }
            },

            updateLoading(text, progress) {
                document.getElementById('loadingText').textContent = text;
                document.getElementById('loadingProgress').style.width = `${progress}%`;
            },

            // Error Handling
            handleCameraError(error) {
                let message = 'Kamera tidak bisa diakses';

                if (error.name === 'NotAllowedError') {
                    message = 'Izin kamera ditolak. Izinkan akses kamera di pengaturan browser.';
                } else if (error.name === 'NotFoundError') {
                    message = 'Kamera tidak ditemukan. Pastikan kamera terhubung.';
                } else if (error.name === 'NotReadableError') {
                    message = 'Kamera sedang digunakan aplikasi lain.';
                } else if (error.message === 'Video loading timeout') {
                    message = 'Kamera timeout. Coba refresh halaman.';
                }

                document.getElementById('cameraStatus').innerHTML = `<span class="text-red-600">${message}</span>`;
                this.startBtn.disabled = false;
                this.startBtn.innerHTML =
                    '<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>Aktifkan Kamera';
            },

            showModelError(error) {
                this.loadingBox.innerHTML = `
                <div class="bg-red-50 border border-red-200 rounded-lg p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mr-4">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.346 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">Gagal Memuat Model AI</h3>
                            <p class="text-gray-600">${error.message || 'Terjadi kesalahan saat memuat model'}</p>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <button onclick="FaceRegistration.loadModels()"
                                class="px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 flex items-center justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Coba Lagi
                        </button>
                        <button onclick="location.reload()"
                                class="px-4 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200 flex items-center justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            Refresh Halaman
                        </button>
                    </div>
                </div>
            `;
            },

            // Utility Functions
            showAlert(type, title, message) {
                // Remove existing alerts
                document.querySelectorAll('.custom-alert').forEach(alert => alert.remove());

                const alertDiv = document.createElement('div');
                alertDiv.className =
                    `custom-alert fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 transform transition-all duration-300 ${this.getAlertClass(type)}`;
                alertDiv.innerHTML = `
                <div class="flex items-start">
                    <div class="flex-shrink-0 mt-0.5">
                        ${this.getAlertIcon(type)}
                    </div>
                    <div class="ml-3">
                        <h3 class="font-medium">${title}</h3>
                        <p class="text-sm opacity-90 mt-1">${message}</p>
                    </div>
                </div>
            `;

                document.body.appendChild(alertDiv);

                // Remove after 5 seconds
                setTimeout(() => {
                    if (alertDiv.parentNode) {
                        alertDiv.remove();
                    }
                }, 5000);
            },

            getAlertClass(type) {
                switch (type) {
                    case 'success':
                        return 'bg-green-50 text-green-800 border border-green-200';
                    case 'error':
                        return 'bg-red-50 text-red-800 border border-red-200';
                    case 'warning':
                        return 'bg-yellow-50 text-yellow-800 border border-yellow-200';
                    default:
                        return 'bg-blue-50 text-blue-800 border border-blue-200';
                }
            },

            getAlertIcon(type) {
                const baseClass = 'w-5 h-5';
                switch (type) {
                    case 'success':
                        return `<svg class="${baseClass} text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>`;
                    case 'error':
                        return `<svg class="${baseClass} text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.346 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>`;
                    default:
                        return `<svg class="${baseClass} text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>`;
                }
            },

            showConfirm(title, message) {
                return new Promise((resolve) => {
                    const confirmed = window.confirm(`${title}\n\n${message}`);
                    resolve(confirmed);
                });
            }
        };

        // Modal Functions
        function showResetModal() {
            document.getElementById('resetModal').classList.remove('hidden');
        }

        function closeResetModal() {
            document.getElementById('resetModal').classList.add('hidden');
        }

        async function resetFaceRegistration() {
            closeResetModal();

            const confirmed = await FaceRegistration.showConfirm(
                'Reset Registrasi Wajah',
                `Data wajah untuk <?php echo e($user->name); ?> akan dihapus permanen. Lanjutkan?`
            );

            if (!confirmed) return;

            try {
                const response = await fetch("<?php echo e(route('users.face.destroy', $user->id)); ?>", {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': "<?php echo e(csrf_token()); ?>",
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    FaceRegistration.showAlert('success', 'Berhasil!', 'Registrasi wajah berhasil direset');
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    throw new Error(data.message || 'Gagal mereset registrasi');
                }
            } catch (error) {
                console.error('Reset error:', error);
                FaceRegistration.showAlert('error', 'Gagal!', error.message);
            }
        }

        // Initialize on DOM ready
        document.addEventListener('DOMContentLoaded', function() {
            FaceRegistration.init();

            // Close modal when clicking outside
            document.getElementById('resetModal').addEventListener('click', (e) => {
                if (e.target === e.currentTarget) {
                    closeResetModal();
                }
            });

            // Close modal with Escape key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    closeResetModal();
                }
            });
        });
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\PROJECT3\Toko-Roni-Mobile-App\tokoroni-app\resources\views\users\face-registration.blade.php ENDPATH**/ ?>