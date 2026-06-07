<?php $__env->startSection('title', 'Dashboard Kasir'); ?>
<?php $__env->startSection('page-title', 'Dashboard Kasir'); ?>
<?php $__env->startSection('page-subtitle', 'Transaksi & Penjualan Hari Ini'); ?>

<?php $__env->startSection('content'); ?>
<div class="min-h-screen bg-gradient-to-br from-blue-50/50 to-emerald-50/30 p-4 md:p-6">
    <!-- Welcome Header Kasir -->
    <div class="glass-effect rounded-3xl p-6 md:p-8 shadow-elegant mb-6 md:mb-8 animate-fade-in">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 md:gap-6">
            <div class="flex-1">
                <div class="flex items-center gap-3 md:gap-4 mb-4">
                    <div class="relative">
                        <div class="w-12 h-12 md:w-16 md:h-16 rounded-2xl bg-gradient-to-br from-emerald-500 to-green-600 flex items-center justify-center shadow-lg">
                            <i class="fas fa-cash-register text-xl md:text-2xl text-white"></i>
                        </div>
                        <div class="absolute -inset-1 md:-inset-2 bg-gradient-to-r from-emerald-500 to-green-600 rounded-2xl blur-xl opacity-20"></div>
                    </div>
                    <div>
                        <h1 class="text-xl md:text-3xl font-bold text-gray-800">Halo, <span class="gradient-text"><?php echo e(Auth::user()->name); ?>!</span> 💰</h1>
                        <p class="text-sm md:text-base text-gray-600 mt-1 md:mt-2">Siap melayani transaksi hari ini?</p>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                            <i class="fas fa-user-tie text-emerald-600 text-sm"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Shift</p>
                            <p class="text-sm font-semibold text-gray-800">Pagi (08:00-16:00)</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                            <i class="fas fa-clock text-blue-600 text-sm"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Kasir ID</p>
                            <p class="text-sm font-semibold text-gray-800">KSR-<?php echo e(str_pad(Auth::id(), 3, '0', STR_PAD_LEFT)); ?></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center">
                            <i class="fas fa-clock text-purple-600 text-sm"></i>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Waktu</p>
                            <p class="text-sm font-semibold text-gray-800 current-time">--:--</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-4 lg:mt-0">
                <a href="<?php echo e(route('transactions.create')); ?>"
                   class="inline-flex items-center gap-2 px-6 py-3 md:py-4 bg-gradient-to-r from-emerald-500 to-green-600 text-white font-semibold rounded-xl hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
                    <i class="fas fa-plus-circle"></i>
                    <span>Transaksi Baru</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Grid Kasir -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-6 md:mb-8">
        <!-- Total Transaksi Hari Ini -->
        <div class="stat-card group">
            <div class="stat-card-glow bg-gradient-to-r from-emerald-500 to-green-500"></div>
            <div class="stat-card-content relative z-10">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="p-3 rounded-xl bg-gradient-to-br from-emerald-500 to-green-600 shadow-lg">
                            <i class="fas fa-receipt text-lg text-white"></i>
                        </div>
                        <div>
                            <p class="text-xs md:text-sm text-gray-500 font-medium">Transaksi Hari Ini</p>
                            <h3 class="text-xl md:text-2xl font-bold text-gray-800 mt-1"><?php echo e(number_format($todayTransactions ?? 0)); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center text-xs">
                        <div class="w-6 h-6 rounded-lg bg-emerald-100 flex items-center justify-center mr-2">
                            <i class="fas fa-arrow-up text-emerald-600 text-xs"></i>
                        </div>
                        <span class="text-emerald-600 font-semibold">+<?php echo e($transactionGrowth ?? 0); ?>%</span>
                        <span class="text-gray-500 ml-2">dari kemarin</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Pendapatan Hari Ini -->
        <div class="stat-card group">
            <div class="stat-card-glow bg-gradient-to-r from-blue-500 to-cyan-500"></div>
            <div class="stat-card-content relative z-10">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="p-3 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-600 shadow-lg">
                            <i class="fas fa-money-bill-wave text-lg text-white"></i>
                        </div>
                        <div>
                            <p class="text-xs md:text-sm text-gray-500 font-medium">Pendapatan Hari Ini</p>
                            <h3 class="text-xl md:text-2xl font-bold text-gray-800 mt-1">Rp <?php echo e(number_format($todayRevenue ?? 0, 0, ',', '.')); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center text-xs">
                        <span class="text-gray-500">Target: Rp 5.000.000</span>
                    </div>
                    <div class="w-24 h-2 bg-gray-200 rounded-full overflow-hidden">
                        <div class="h-full bg-emerald-500 rounded-full"
                             style="width: <?php echo e(min(100, (($todayRevenue ?? 0) / 5000000) * 100)); ?>%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rata-rata Transaksi -->
        <div class="stat-card group">
            <div class="stat-card-glow bg-gradient-to-r from-purple-500 to-pink-500"></div>
            <div class="stat-card-content relative z-10">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="p-3 rounded-xl bg-gradient-to-br from-purple-500 to-pink-600 shadow-lg">
                            <i class="fas fa-chart-bar text-lg text-white"></i>
                        </div>
                        <div>
                            <p class="text-xs md:text-sm text-gray-500 font-medium">Rata-rata Transaksi</p>
                            <h3 class="text-xl md:text-2xl font-bold text-gray-800 mt-1">Rp <?php echo e(number_format($avgTransaction ?? 0, 0, ',', '.')); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center text-xs">
                        <div class="w-6 h-6 rounded-lg bg-emerald-100 flex items-center justify-center mr-2">
                            <i class="fas fa-arrow-up text-emerald-600 text-xs"></i>
                        </div>
                        <span class="text-emerald-600 font-semibold">+8%</span>
                        <span class="text-gray-500 ml-2">lebih tinggi</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Produk Terjual Hari Ini -->
        <div class="stat-card group">
            <div class="stat-card-glow bg-gradient-to-r from-amber-500 to-orange-500"></div>
            <div class="stat-card-content relative z-10">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="p-3 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 shadow-lg">
                            <i class="fas fa-shopping-cart text-lg text-white"></i>
                        </div>
                        <div>
                            <p class="text-xs md:text-sm text-gray-500 font-medium">Produk Terjual</p>
                            <h3 class="text-xl md:text-2xl font-bold text-gray-800 mt-1"><?php echo e(number_format($todayItemsSold ?? 0)); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center text-xs">
                        <span class="text-gray-500 truncate max-w-[120px]"><?php echo e($topProductToday ?? 'Produk terlaris'); ?></span>
                        <span class="text-amber-600 font-semibold ml-2">#1</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-6">
        <!-- Transaksi Terbaru -->
        <div class="lg:col-span-2">
            <div class="glass-effect rounded-3xl overflow-hidden shadow-elegant h-full">
                <div class="p-4 md:p-6 border-b border-gray-100/50">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg md:text-xl font-bold text-gray-800">Transaksi Terbaru</h3>
                            <p class="text-xs md:text-sm text-gray-600 mt-1">5 transaksi terakhir Anda</p>
                        </div>
                        <a href="<?php echo e(route('transactions.index')); ?>"
                           class="text-emerald-600 hover:text-emerald-700 font-medium text-sm flex items-center gap-1">
                            Lihat Semua <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>
                <div class="divide-y divide-gray-100/50">
                    <?php $__empty_1 = true; $__currentLoopData = $recentTransactions ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="p-4 md:p-6 hover:bg-white/30 transition-colors duration-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center
                                    <?php echo e($transaction->payment_method === 'cash' ? 'bg-emerald-100 text-emerald-600' :
                                       ($transaction->payment_method === 'debit_card' ? 'bg-blue-100 text-blue-600' : 'bg-purple-100 text-purple-600')); ?>">
                                    <?php if($transaction->payment_method === 'cash'): ?>
                                        <i class="fas fa-money-bill-wave"></i>
                                    <?php elseif($transaction->payment_method === 'debit_card'): ?>
                                        <i class="fas fa-credit-card"></i>
                                    <?php else: ?>
                                        <i class="fas fa-qrcode"></i>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900"><?php echo e($transaction->transaction_code); ?></h4>
                                    <p class="text-xs text-gray-500 mt-1">
                                        <?php echo e(\Carbon\Carbon::parse($transaction->created_at)->format('H:i')); ?> •
                                        <?php echo e($transaction->items->count() ?? 0); ?> items
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-lg font-bold text-gray-900">Rp <?php echo e(number_format($transaction->total_amount, 0, ',', '.')); ?></div>
                                <span class="inline-block mt-1 px-2 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                    Selesai
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="p-8 text-center">
                        <div class="flex flex-col items-center justify-center text-gray-400">
                            <i class="fas fa-receipt text-3xl mb-3"></i>
                            <p class="text-sm">Belum ada transaksi hari ini</p>
                            <a href="<?php echo e(route('transactions.create')); ?>" class="mt-2 text-emerald-600 text-sm font-medium">
                                Mulai transaksi baru
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick Actions & Product Search -->
        <div class="space-y-4 md:space-y-6">
            <!-- Quick Transaction -->
            <div class="glass-effect rounded-3xl p-4 md:p-6 shadow-elegant">
                <h3 class="text-lg md:text-xl font-bold text-gray-800 mb-4">Transaksi Cepat</h3>
                <div class="space-y-3">
                    <a href="<?php echo e(route('transactions.create')); ?>?type=regular"
                       class="flex items-center gap-3 p-3 rounded-xl bg-emerald-50 hover:bg-emerald-100 transition-colors">
                        <div class="w-10 h-10 rounded-lg bg-emerald-500 flex items-center justify-center">
                            <i class="fas fa-shopping-cart text-white"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">Transaksi Regular</h4>
                            <p class="text-xs text-gray-600">Pelanggan umum</p>
                        </div>
                    </a>
                    <a href="<?php echo e(route('transactions.create')); ?>?type=member"
                       class="flex items-center gap-3 p-3 rounded-xl bg-blue-50 hover:bg-blue-100 transition-colors">
                        <div class="w-10 h-10 rounded-lg bg-blue-500 flex items-center justify-center">
                            <i class="fas fa-user-friends text-white"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">Transaksi Member</h4>
                            <p class="text-xs text-gray-600">Member terdaftar</p>
                        </div>
                    </a>
                    <a href="<?php echo e(route('transactions.create')); ?>?type=wholesale"
                       class="flex items-center gap-3 p-3 rounded-xl bg-purple-50 hover:bg-purple-100 transition-colors">
                        <div class="w-10 h-10 rounded-lg bg-purple-500 flex items-center justify-center">
                            <i class="fas fa-boxes text-white"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">Transaksi Grosir</h4>
                            <p class="text-xs text-gray-600">Minimal 10 item</p>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Product Search with Barcode Scanner -->
            <div class="glass-effect rounded-3xl p-4 md:p-6 shadow-elegant">
                <h3 class="text-lg md:text-xl font-bold text-gray-800 mb-4">Cari & Scan Produk</h3>
                
                <!-- Barcode Scanner Area -->
                <div id="scannerContainer" class="hidden mb-4">
                    <div class="relative">
                        <video id="scannerVideo" class="w-full rounded-xl border-2 border-emerald-500" style="min-height: 250px;"></video>
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                            <div class="w-64 h-32 border-2 border-emerald-500 rounded-lg"></div>
                        </div>
                        <button onclick="stopScanner()" 
                                class="absolute top-2 right-2 bg-red-500 text-white p-2 rounded-lg hover:bg-red-600 transition z-10">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <p class="text-xs text-center text-gray-500 mt-2">Arahkan kamera ke barcode produk</p>
                </div>

                <!-- Search Input -->
                <div class="relative">
                    <input type="text"
                           id="productSearch"
                           placeholder="Scan barcode atau ketik nama produk..."
                           class="w-full px-4 py-3 bg-white/50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                           autocomplete="off">
                    <button class="absolute right-3 top-3 text-gray-400">
                        <i class="fas fa-search"></i>
                    </button>
                </div>

                <!-- Search Results -->
                <div id="searchResults" class="hidden mt-3 bg-white rounded-xl shadow-lg border border-gray-100 max-h-64 overflow-y-auto"></div>

                <div class="mt-4 grid grid-cols-2 gap-2">
                    <button onclick="startScanner()" 
                            class="p-3 rounded-lg bg-blue-50 hover:bg-blue-100 transition-colors text-center">
                        <i class="fas fa-camera text-blue-600 text-lg mb-1"></i>
                        <p class="text-xs font-medium text-gray-700">Scan Barcode</p>
                    </button>
                    <a href="<?php echo e(route('products.index')); ?>" 
                       class="p-3 rounded-lg bg-emerald-50 hover:bg-emerald-100 transition-colors text-center block">
                        <i class="fas fa-keyboard text-emerald-600 text-lg mb-1"></i>
                        <p class="text-xs font-medium text-gray-700">Lihat Semua</p>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Popular Products -->
    <div class="mt-6 md:mt-8">
        <div class="glass-effect rounded-3xl p-4 md:p-6 shadow-elegant">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg md:text-xl font-bold text-gray-800">Produk Populer</h3>
                    <p class="text-xs md:text-sm text-gray-600 mt-1">Sering dibeli hari ini</p>
                </div>
                <a href="<?php echo e(route('products.index')); ?>" class="text-emerald-600 hover:text-emerald-700 text-sm font-medium">
                    Lihat semua produk
                </a>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                <?php $__empty_1 = true; $__currentLoopData = $popularProducts ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="group cursor-pointer" onclick="quickAddToCart(<?php echo e($product->id); ?>, '<?php echo e($product->name); ?>', <?php echo e($product->price); ?>)">
                    <div class="aspect-square rounded-2xl bg-gradient-to-br from-gray-50 to-gray-100 flex items-center justify-center p-4 mb-2 group-hover:shadow-md transition-shadow">
                        <?php if($product->image): ?>
                            <img src="<?php echo e(Storage::url($product->image)); ?>" alt="<?php echo e($product->name); ?>" class="w-full h-full object-cover rounded-xl">
                        <?php else: ?>
                            <i class="fas fa-box text-3xl text-gray-400 group-hover:text-emerald-500 transition-colors"></i>
                        <?php endif; ?>
                    </div>
                    <div class="text-center">
                        <p class="text-xs font-medium text-gray-900 truncate"><?php echo e($product->name); ?></p>
                        <p class="text-xs text-gray-500 mt-1">Rp <?php echo e(number_format($product->price, 0, ',', '.')); ?></p>
                        <div class="flex items-center justify-center mt-1">
                            <i class="fas fa-shopping-cart text-xs text-emerald-500 mr-1"></i>
                            <span class="text-xs text-gray-600"><?php echo e($product->sold_today ?? 0); ?> terjual</span>
                        </div>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="col-span-full p-6 text-center text-gray-400">
                    <i class="fas fa-box-open text-2xl mb-2"></i>
                    <p class="text-sm">Belum ada data produk populer</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div id="toast" class="fixed bottom-4 right-4 bg-gray-800 text-white px-6 py-3 rounded-lg shadow-lg z-50 hidden transition-all duration-300">
    <div class="flex items-center gap-3">
        <i id="toastIcon" class="fas fa-info-circle"></i>
        <span id="toastMessage"></span>
    </div>
</div>

<!-- Include QuaggaJS untuk barcode scanner -->
<script src="https://cdn.jsdelivr.net/npm/@ericblade/quagga2@1.8.2/dist/quagga.min.js"></script>

<script>
    let scannerActive = false;
    let searchTimeout = null;

    // Update current time
    function updateTime() {
        const now = new Date();
        const options = {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        };
        const timeString = now.toLocaleTimeString('id-ID', options);
        document.querySelectorAll('.current-time').forEach(el => {
            if (el) el.textContent = timeString;
        });
    }

    updateTime();
    setInterval(updateTime, 1000);

    // Show toast notification
    function showToast(message, type = 'success') {
        const toast = document.getElementById('toast');
        const toastIcon = document.getElementById('toastIcon');
        const toastMessage = document.getElementById('toastMessage');
        
        if (!toast) return;
        
        const icons = {
            success: 'fas fa-check-circle text-green-400',
            error: 'fas fa-exclamation-circle text-red-400',
            info: 'fas fa-info-circle text-blue-400',
            warning: 'fas fa-exclamation-triangle text-yellow-400'
        };
        
        toastIcon.className = icons[type] || icons.info;
        toastMessage.textContent = message;
        
        toast.classList.remove('hidden');
        toast.style.opacity = '1';
        
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => {
                toast.classList.add('hidden');
                toast.style.opacity = '1';
            }, 300);
        }, 3000);
    }

    // Search produk
    async function searchProducts(keyword) {
        if (!keyword || keyword.length < 2) {
            document.getElementById('searchResults').classList.add('hidden');
            return;
        }
        
        try {
            const response = await fetch(`/api/v1/products/search?q=${encodeURIComponent(keyword)}`);
            const result = await response.json();
            
            if (result.status && result.data && result.data.length > 0) {
                displaySearchResults(result.data);
            } else {
                document.getElementById('searchResults').classList.add('hidden');
            }
        } catch (error) {
            console.error('Search error:', error);
        }
    }
    
    // Display search results
    function displaySearchResults(products) {
        const resultsDiv = document.getElementById('searchResults');
        if (!resultsDiv) return;
        
        if (!products || products.length === 0) {
            resultsDiv.classList.add('hidden');
            return;
        }
        
        resultsDiv.innerHTML = products.map(product => `
            <div onclick="quickAddToCart(${product.id}, '${product.name}', ${product.price})" 
                 class="p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-0 flex items-center justify-between">
                <div>
                    <p class="font-medium text-gray-900">${product.name}</p>
                    <p class="text-xs text-gray-500">${product.code || '-'} • Stok: ${product.stock}</p>
                </div>
                <div class="text-right">
                    <p class="font-semibold text-emerald-600">Rp ${formatNumber(product.price)}</p>
                    <p class="text-xs text-gray-500">${product.unit || 'pcs'}</p>
                </div>
            </div>
        `).join('');
        
        resultsDiv.classList.remove('hidden');
    }
    
    // Format number
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }
    
    // Quick add to cart
    function quickAddToCart(productId, productName, price) {
        // Simpan ke sessionStorage untuk digunakan di halaman transaksi
        const cartItem = {
            id: productId,
            name: productName,
            price: price,
            qty: 1
        };
        
        let pendingItems = JSON.parse(sessionStorage.getItem('pending_cart_items') || '[]');
        pendingItems.push(cartItem);
        sessionStorage.setItem('pending_cart_items', JSON.stringify(pendingItems));
        
        showToast(`${productName} ditambahkan ke keranjang`, 'success');
        
        // Redirect ke halaman transaksi
        setTimeout(() => {
            window.location.href = "<?php echo e(route('transactions.create')); ?>";
        }, 500);
    }
    
    // Start barcode scanner
    function startScanner() {
        const scannerContainer = document.getElementById('scannerContainer');
        if (!scannerContainer) return;
        
        scannerContainer.classList.remove('hidden');
        
        Quagga.init({
            inputStream: {
                name: "Live",
                type: "LiveStream",
                target: document.querySelector('#scannerVideo'),
                constraints: {
                    width: { ideal: 640 },
                    height: { ideal: 480 },
                    facingMode: "environment"
                },
            },
            decoder: {
                readers: [
                    "code_128_reader",
                    "ean_reader",
                    "ean_8_reader",
                    "code_39_reader",
                    "code_93_reader",
                    "codabar_reader",
                    "upc_reader",
                    "upc_e_reader",
                    "i2of5_reader"
                ]
            },
            locate: true,
        }, function(err) {
            if (err) {
                console.error("Scanner init error:", err);
                showToast("Gagal mengakses kamera. Pastikan izin kamera diberikan.", "error");
                scannerContainer.classList.add('hidden');
                return;
            }
            
            Quagga.start();
            scannerActive = true;
            showToast("Scanner siap. Arahkan kamera ke barcode.", "info");
        });
        
        Quagga.onDetected(function(result) {
            if (!scannerActive) return;
            
            const code = result.codeResult.code;
            if (code) {
                stopScanner();
                processBarcode(code);
            }
        });
    }
    
    // Stop scanner
    function stopScanner() {
        if (Quagga && scannerActive) {
            Quagga.stop();
            scannerActive = false;
        }
        
        const scannerContainer = document.getElementById('scannerContainer');
        if (scannerContainer) {
            scannerContainer.classList.add('hidden');
        }
        
        // Reset video element
        const video = document.getElementById('scannerVideo');
        if (video && video.srcObject) {
            const tracks = video.srcObject.getTracks();
            tracks.forEach(track => track.stop());
            video.srcObject = null;
        }
    }
    
    // Process barcode result
    async function processBarcode(code) {
        showToast(`Memproses barcode: ${code}`, 'info');
        
        try {
            // Cari produk berdasarkan barcode
            const response = await fetch(`/api/v1/products/search?q=${encodeURIComponent(code)}`);
            const result = await response.json();
            
            if (result.status && result.data && result.data.length > 0) {
                const product = result.data[0];
                quickAddToCart(product.id, product.name, product.price);
            } else {
                // Coba cari berdasarkan code
                const codeResponse = await fetch(`/api/v1/products/search?q=${encodeURIComponent(code)}`);
                const codeResult = await codeResponse.json();
                
                if (codeResult.status && codeResult.data && codeResult.data.length > 0) {
                    const product = codeResult.data[0];
                    quickAddToCart(product.id, product.name, product.price);
                } else {
                    showToast(`Produk dengan barcode ${code} tidak ditemukan`, 'error');
                }
            }
        } catch (error) {
            console.error('Barcode processing error:', error);
            showToast('Gagal memproses barcode', 'error');
        }
    }
    
    // Product search with debounce
    const searchInput = document.getElementById('productSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            const keyword = e.target.value;
            
            searchTimeout = setTimeout(() => {
                searchProducts(keyword);
            }, 300);
        });
        
        // Enter key handler
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const searchTerm = e.target.value;
                if (searchTerm && searchTerm.length >= 2) {
                    searchProducts(searchTerm);
                }
            }
        });
        
        // Hide results when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !document.getElementById('searchResults')?.contains(e.target)) {
                document.getElementById('searchResults')?.classList.add('hidden');
            }
        });
    }
    
    // Auto refresh stats every 30 seconds
    let autoRefreshInterval = null;

    function startAutoRefresh() {
        if (autoRefreshInterval) clearInterval(autoRefreshInterval);
        
        autoRefreshInterval = setInterval(() => {
            fetch(window.location.href, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                // Update stats numbers
                const stats = doc.querySelectorAll('.stat-card .text-xl');
                const currentStats = document.querySelectorAll('.stat-card .text-xl');
                
                if (stats.length && currentStats.length) {
                    for (let i = 0; i < Math.min(stats.length, currentStats.length); i++) {
                        if (stats[i].innerHTML !== currentStats[i].innerHTML) {
                            currentStats[i].innerHTML = stats[i].innerHTML;
                        }
                    }
                }
            })
            .catch(error => console.log('Auto refresh error:', error));
        }, 30000);
    }

    // Clean up scanner when page unloads
    window.addEventListener('beforeunload', function() {
        if (Quagga && scannerActive) {
            Quagga.stop();
        }
    });
</script>

<style>
    .stat-card {
        position: relative;
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(10px);
        border-radius: 1rem;
        padding: 1.25rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.5);
        transition: all 0.3s ease;
        overflow: hidden;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.02);
    }

    .stat-card-glow {
        position: absolute;
        inset: -4px;
        border-radius: 1rem;
        filter: blur(12px);
        opacity: 0;
        transition: opacity 0.3s ease;
        z-index: 0;
    }

    .stat-card:hover .stat-card-glow {
        opacity: 0.15;
    }

    .stat-card-content {
        position: relative;
        z-index: 1;
    }

    .glass-effect {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(16, 185, 129, 0.1);
    }

    .gradient-text {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .animate-fade-in {
        animation: fadeIn 0.6s ease-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Custom scrollbar */
    .overflow-y-auto::-webkit-scrollbar {
        width: 6px;
    }

    .overflow-y-auto::-webkit-scrollbar-track {
        background: rgba(16, 185, 129, 0.1);
        border-radius: 10px;
    }

    .overflow-y-auto::-webkit-scrollbar-thumb {
        background: linear-gradient(135deg, #10b981 0%, #14b8a6 100%);
        border-radius: 10px;
    }
    
    /* Scanner animation */
    @keyframes scanLine {
        0% {
            top: 0%;
        }
        100% {
            top: 100%;
        }
    }
    
    #scannerVideo {
        transform: scaleX(-1);
    }
</style>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\laravel project 3\Toko-Roni-Mobile-App\resources\views/dashboard/kasir.blade.php ENDPATH**/ ?>