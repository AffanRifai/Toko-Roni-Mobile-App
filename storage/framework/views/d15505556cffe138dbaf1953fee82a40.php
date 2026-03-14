<?php $__env->startSection('title', 'Transaksi Kasir'); ?>
<?php $__env->startSection('subtitle', 'Sistem Point of Sale - Input penjualan baru'); ?>

<?php $__env->startSection('content'); ?>
    <div class="min-h-screen bg-gray-50 p-3 md:p-4 lg:p-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-6">
            <!-- Left Column: Product Catalog -->
            <div class="lg:col-span-2 bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden flex flex-col">
                <!-- Products Header -->
                <div class="p-4 md:p-6 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <h3 class="text-lg md:text-xl font-bold text-gray-800 flex items-center gap-2">
                                <i class="fas fa-boxes text-blue-600"></i>
                                Katalog Produk
                            </h3>
                            <p class="text-xs md:text-sm text-gray-600 mt-1">Pilih produk untuk ditambahkan ke keranjang</p>
                        </div>
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 md:gap-4">
                            <!-- Search -->
                            <div class="relative flex-1 w-full max-w-md">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400 text-sm"></i>
                                </div>
                                <input type="text" id="searchProduct"
                                    class="pl-9 pr-9 py-2 w-full border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-sm"
                                    placeholder="Cari produk..." autocomplete="off">
                                <button id="clearSearch" class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                    type="button">
                                    <i class="fas fa-times text-gray-400 hover:text-gray-600 text-sm"></i>
                                </button>
                            </div>
                            <!-- Products Count -->
                            <div
                                class="bg-blue-100 text-blue-800 text-xs md:text-sm font-medium px-3 py-1.5 rounded-full whitespace-nowrap">
                                <span id="productsCount"><?php echo e($products->count()); ?></span> produk tersedia
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Category Filter -->
                <div class="px-4 md:px-6 py-3 border-b border-gray-200 bg-white overflow-x-auto">
                    <div class="flex gap-2 min-w-max pb-1">
                        <button
                            class="category-btn bg-blue-600 text-white px-3 md:px-4 py-1.5 md:py-2 rounded-full text-xs md:text-sm font-medium flex items-center gap-1 md:gap-2 whitespace-nowrap transition-colors"
                            data-category="all" type="button">
                            <i class="fas fa-layer-group text-xs md:text-sm"></i> Semua
                        </button>
                        <?php if(isset($categories) && count($categories) > 0): ?>
                            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <button
                                    class="category-btn bg-gray-100 text-gray-700 hover:bg-gray-200 px-3 md:px-4 py-1.5 md:py-2 rounded-full text-xs md:text-sm font-medium flex items-center gap-1 md:gap-2 whitespace-nowrap transition-colors"
                                    data-category="<?php echo e($category->id); ?>" type="button">
                                    <i class="fas fa-tag text-xs md:text-sm"></i> <?php echo e($category->name); ?>

                                </button>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Products Grid -->
                <div class="flex-1 p-3 md:p-4 lg:p-6 overflow-y-auto">
                    <div
                        class="products-grid grid grid-cols-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 md:gap-4">
                        <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="product-card bg-white border border-gray-200 rounded-lg md:rounded-xl p-2 md:p-3 hover:border-blue-400 hover:shadow-md transition-all duration-200 relative flex flex-col h-full min-h-[180px] md:min-h-[200px]"
                                data-id="<?php echo e($product->id); ?>" data-name="<?php echo e(strtolower($product->name)); ?>"
                                data-price="<?php echo e($product->price); ?>" data-stock="<?php echo e($product->stock); ?>"
                                data-category="<?php echo e($product->category_id ?? ''); ?>" data-code="<?php echo e($product->code ?? ''); ?>">

                                <!-- Stock Badge -->
                                <div class="absolute top-1.5 md:top-2 right-1.5 md:right-2 z-10">
                                    <?php if($product->stock > 20): ?>
                                        <span
                                            class="bg-green-100 text-green-800 text-xs font-medium px-1.5 md:px-2.5 py-0.5 rounded-full flex items-center gap-1 whitespace-nowrap">
                                            <i class="fas fa-check-circle text-green-600 text-xs"></i>
                                            <span class="hidden sm:inline"><?php echo e($product->stock); ?></span>
                                            <span class="sm:hidden"><?php echo e($product->stock); ?></span>
                                        </span>
                                    <?php elseif($product->stock > 0): ?>
                                        <span
                                            class="bg-yellow-100 text-yellow-800 text-xs font-medium px-1.5 md:px-2.5 py-0.5 rounded-full flex items-center gap-1 whitespace-nowrap">
                                            <i class="fas fa-exclamation-triangle text-yellow-600 text-xs"></i>
                                            <span class="hidden sm:inline"><?php echo e($product->stock); ?></span>
                                            <span class="sm:hidden"><?php echo e($product->stock); ?></span>
                                        </span>
                                    <?php else: ?>
                                        <span
                                            class="bg-red-100 text-red-800 text-xs font-medium px-1.5 md:px-2.5 py-0.5 rounded-full flex items-center gap-1 whitespace-nowrap">
                                            <i class="fas fa-times-circle text-red-600 text-xs"></i>
                                            <span class="hidden sm:inline">Habis</span>
                                            <span class="sm:hidden">0</span>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <!-- Product Info -->
                                <div class="mb-2 md:mb-3 flex-1">
                                    <div class="text-xs text-gray-500 font-mono mb-1 truncate">
                                        <?php echo e($product->code ?? 'PRD-' . $product->id); ?>

                                    </div>
                                    <h4
                                        class="font-semibold text-gray-800 text-xs md:text-sm mb-1 line-clamp-2 leading-tight md:leading-normal min-h-[2.5rem] md:min-h-[3rem]">
                                        <?php echo e($product->name); ?>

                                    </h4>
                                    <div class="text-xs text-gray-600 mb-1 md:mb-2 flex items-center gap-1 truncate">
                                        <i class="fas fa-tag text-gray-400 text-xs"></i>
                                        <span class="truncate"><?php echo e($product->category->name ?? 'Tanpa Kategori'); ?></span>
                                    </div>
                                    <div class="text-sm md:text-base font-bold text-blue-600 mt-auto">
                                        Rp <?php echo e(number_format($product->price, 0, ',', '.')); ?>

                                    </div>
                                </div>

                                <!-- Add to Cart Button -->
                                <button type="button"
                                    class="add-to-cart-btn w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-1.5 md:py-2 px-2 md:px-3 rounded-md md:rounded-lg flex items-center justify-center gap-1 md:gap-2 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed text-xs md:text-sm mt-auto"
                                    data-product-id="<?php echo e($product->id); ?>" <?php echo e($product->stock <= 0 ? 'disabled' : ''); ?>>
                                    <i class="fas fa-plus text-xs"></i>
                                    <span class="truncate">Tambah</span>
                                </button>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="col-span-full flex flex-col items-center justify-center py-8 md:py-12 text-center">
                                <div class="text-gray-300 text-4xl md:text-5xl mb-3 md:mb-4">
                                    <i class="fas fa-box-open"></i>
                                </div>
                                <h4 class="text-lg md:text-xl font-semibold text-gray-700 mb-2">Tidak Ada Produk</h4>
                                <p class="text-gray-600 text-sm md:text-base mb-4 md:mb-6 px-4">Belum ada produk yang
                                    tersedia untuk dijual</p>
                                <?php if(auth()->user()->role === 'owner' || auth()->user()->role === 'gudang'): ?>
                                    <a href="<?php echo e(route('products.create')); ?>"
                                        class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 md:py-2.5 px-4 md:px-6 rounded-lg flex items-center gap-2 transition-colors text-sm md:text-base">
                                        <i class="fas fa-plus"></i> Tambah Produk
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right Column: Cart & Transaction -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden flex flex-col">
                <!-- Cart Header -->
                <div class="p-4 md:p-6 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg md:text-xl font-bold text-gray-800 flex items-center gap-2">
                            <i class="fas fa-shopping-cart text-blue-600"></i>
                            <span class="truncate">Keranjang Belanja</span>
                        </h3>
                        <button id="clearCartBtn" type="button"
                            class="bg-red-600 hover:bg-red-700 text-white font-medium py-1.5 md:py-2 px-2 md:px-4 rounded-lg flex items-center gap-1 md:gap-2 text-xs md:text-sm transition-colors whitespace-nowrap">
                            <i class="fas fa-trash-alt text-xs md:text-sm"></i>
                            <span class="hidden sm:inline"></span>
                        </button>
                    </div>
                </div>

                <!-- Cart Container -->
                <div class="flex-1 p-3 md:p-4 lg:p-6 overflow-y-auto">
                    <form action="<?php echo e(route('transactions.store')); ?>" method="POST" id="transactionForm">
                        <?php echo csrf_field(); ?>

                        <!-- ========== FITUR MEMBER BARU ========== -->
                        <!-- Member Selection -->
                        <div
                            class="bg-gradient-to-r from-purple-50 to-blue-50 rounded-lg md:rounded-xl border border-purple-200 overflow-hidden mb-4 md:mb-6">
                            <div class="bg-purple-600 border-b border-purple-700 px-3 md:px-4 py-2 md:py-3">
                                <div class="flex items-center gap-2 text-white font-medium text-sm md:text-base">
                                    <i class="fas fa-crown text-xs md:text-sm"></i>
                                    <span>Member Area</span>
                                </div>
                            </div>
                            <div class="p-3 md:p-4">
                                <div class="grid grid-cols-1 gap-3">
                                    <div>
                                        <label for="member_id"
                                            class="block text-xs md:text-sm font-medium text-gray-700 mb-1">
                                            Pilih Member (Opsional)
                                        </label>
                                        <div class="flex gap-2">
                                            <select id="member_id" name="member_id"
                                                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors text-sm">
                                                <option value="">Transaksi Non-Member</option>
                                                <?php if(isset($members) && count($members) > 0): ?>
                                                    <?php $__currentLoopData = $members; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($member->id); ?>"
                                                            data-limit="<?php echo e($member->limit_kredit); ?>"
                                                            data-piutang="<?php echo e($member->total_piutang); ?>"
                                                            data-nama="<?php echo e($member->nama); ?>"
                                                            data-kode="<?php echo e($member->kode_member); ?>">
                                                            <?php echo e($member->kode_member); ?> - <?php echo e($member->nama); ?>

                                                            (<?php echo e(ucfirst($member->tipe_member)); ?>)
                                                        </option>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                <?php endif; ?>
                                            </select>
                                            <button type="button" id="searchMemberBtn"
                                                class="px-3 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Member Info Card (hidden by default) -->
                                    <div id="memberInfoCard"
                                        class="hidden bg-white border border-purple-200 rounded-lg p-3 mt-2">
                                        <div class="flex items-start gap-3">
                                            <div
                                                class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center">
                                                <i class="fas fa-user-circle text-purple-600 text-xl"></i>
                                            </div>
                                            <div class="flex-1">
                                                <div class="flex items-center justify-between">
                                                    <h4 id="memberName" class="font-semibold text-gray-900">Nama Member
                                                    </h4>
                                                    <span id="memberTipe"
                                                        class="badge bg-purple-100 text-purple-800 text-xs px-2 py-1 rounded-full">Platinum</span>
                                                </div>
                                                <p id="memberKode" class="text-xs text-gray-500 mt-1">Kode: MBR0001</p>
                                                <div class="grid grid-cols-2 gap-2 mt-2 text-xs">
                                                    <div>
                                                        <span class="text-gray-600">Limit:</span>
                                                        <span id="memberLimit" class="font-medium ml-1">Rp 0</span>
                                                    </div>
                                                    <div>
                                                        <span class="text-gray-600">Piutang:</span>
                                                        <span id="memberPiutang"
                                                            class="font-medium text-amber-600 ml-1">Rp 0</span>
                                                    </div>
                                                    <div class="col-span-2">
                                                        <span class="text-gray-600">Sisa Limit:</span>
                                                        <span id="memberSisaLimit"
                                                            class="font-medium text-green-600 ml-1">Rp 0</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Credit Limit Warning -->
                                    <div id="creditLimitWarning"
                                        class="hidden bg-red-50 border border-red-200 rounded-lg p-3 text-sm text-red-700">
                                        <i class="fas fa-exclamation-triangle mr-2"></i>
                                        <span id="limitWarningText">Melebihi limit kredit!</span>
                                    </div>

                                    <!-- Due Date for Credit Payment -->
                                    <div id="creditDueDateSection" class="hidden">
                                        <label for="due_date"
                                            class="block text-xs md:text-sm font-medium text-gray-700 mb-1">
                                            Tanggal Jatuh Tempo <span class="text-red-500">*</span>
                                        </label>
                                        <input type="date" id="due_date" name="due_date"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors text-sm"
                                            min="<?php echo e(now()->addDay()->format('Y-m-d')); ?>"
                                            value="<?php echo e(now()->addDays(30)->format('Y-m-d')); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Customer Information -->
                        <div
                            class="bg-gray-50 rounded-lg md:rounded-xl border border-gray-200 overflow-hidden mb-4 md:mb-6">
                            <div class="bg-blue-50 border-b border-gray-200 px-3 md:px-4 py-2 md:py-3">
                                <div class="flex items-center gap-2 text-blue-700 font-medium text-sm md:text-base">
                                    <i class="fas fa-user text-xs md:text-sm"></i>
                                    <span>Informasi Pelanggan</span>
                                </div>
                            </div>
                            <div class="p-3 md:p-4 grid grid-cols-1 gap-3 md:gap-4">
                                <div>
                                    <label for="customer_name"
                                        class="block text-xs md:text-sm font-medium text-gray-700 mb-1">
                                        Nama Pelanggan *
                                    </label>
                                    <input type="text" id="customer_name" name="customer_name" required
                                        class="w-full px-3 py-1.5 md:py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-sm"
                                        placeholder="Pelanggan Umum"
                                        value="<?php echo e(old('customer_name', 'Pelanggan Umum')); ?>">
                                </div>
                                <div>
                                    <label for="customer_phone"
                                        class="block text-xs md:text-sm font-medium text-gray-700 mb-1">
                                        No. Telepon
                                    </label>
                                    <input type="tel" id="customer_phone" name="customer_phone"
                                        class="w-full px-3 py-1.5 md:py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors text-sm"
                                        placeholder="08xxxxxxxxxx" value="<?php echo e(old('customer_phone')); ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Cart Items -->
                        <div
                            class="bg-white border border-gray-200 rounded-lg md:rounded-xl overflow-hidden mb-4 md:mb-6 min-h-[150px] md:min-h-[200px] relative">
                            <!-- Empty Cart State -->
                            <div id="emptyCart"
                                class="absolute inset-0 flex flex-col items-center justify-center p-4 md:p-8 text-center">
                                <div class="text-gray-300 text-3xl md:text-4xl mb-2 md:mb-3">
                                    <i class="fas fa-shopping-cart"></i>
                                </div>
                                <p class="text-sm md:text-base font-semibold text-gray-700 mb-1 md:mb-2">Keranjang masih
                                    kosong</p>
                                <p class="text-gray-600 text-xs md:text-sm">Klik produk untuk menambah ke keranjang</p>
                            </div>

                            <!-- Cart Table -->
                            <div id="cartTable" class="w-full hidden">
                                <div class="overflow-x-auto">
                                    <table class="w-full min-w-[500px]">
                                        <thead class="bg-gray-50 border-b border-gray-200">
                                            <tr>
                                                <th
                                                    class="px-3 md:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Produk</th>
                                                <th
                                                    class="px-3 md:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Qty</th>
                                                <th
                                                    class="px-3 md:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Harga</th>
                                                <th
                                                    class="px-3 md:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Subtotal</th>
                                                <th
                                                    class="px-3 md:px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody id="cartItems" class="divide-y divide-gray-200">
                                            <!-- Items will be inserted here by JavaScript -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Transaction Summary -->
                        <div id="transactionSummary"
                            class="bg-white border border-gray-200 rounded-lg md:rounded-xl overflow-hidden hidden">
                            <!-- Summary Header -->
                            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 md:px-6 py-3 md:py-4">
                                <h4 class="text-base md:text-lg font-bold text-white flex items-center gap-2">
                                    <i class="fas fa-receipt"></i>
                                    <span>Ringkasan Transaksi</span>
                                </h4>
                            </div>

                            <!-- Summary Body -->
                            <div class="p-3 md:p-4 lg:p-6">
                                <!-- Summary Rows -->
                                <div class="space-y-2 md:space-y-3 mb-4 md:mb-6">
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600 text-sm">Total Item</span>
                                        <span id="totalItems" class="font-semibold text-sm md:text-base">0</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600 text-sm">Subtotal</span>
                                        <span id="subtotal" class="font-semibold text-sm md:text-base">Rp 0</span>
                                    </div>

                                    <!-- Discount -->
                                    <div class="flex justify-between items-center pt-2 md:pt-3 border-t border-gray-100">
                                        <div>
                                            <span class="text-gray-600 text-sm">Diskon</span>
                                            <span class="text-xs text-gray-500">(%)</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <input type="number" id="discount" name="discount" value="0"
                                                min="0" max="100" step="0.1"
                                                class="w-16 md:w-20 px-2 md:px-3 py-1 md:py-1.5 border border-gray-300 rounded-lg text-center font-semibold text-sm">
                                            <span class="text-gray-500 text-sm">%</span>
                                        </div>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600 text-sm">Jumlah Diskon</span>
                                        <span id="discountAmount"
                                            class="font-semibold text-red-600 text-sm md:text-base">Rp 0</span>
                                    </div>
                                    <div class="flex justify-between items-center pt-2 md:pt-3 border-t border-gray-200">
                                        <span class="text-gray-800 font-semibold text-base md:text-lg">Total Bayar</span>
                                        <span id="grandTotal" class="text-blue-600 font-bold text-lg md:text-xl">Rp
                                            0</span>
                                    </div>
                                </div>

                                <!-- Payment Method -->
                                <div class="mb-4 md:mb-6">
                                    <label class="block text-xs md:text-sm font-medium text-gray-700 mb-2 md:mb-3">
                                        Metode Pembayaran *
                                    </label>
                                    <div class="grid grid-cols-2 gap-2">
                                        <label class="payment-option relative cursor-pointer">
                                            <input type="radio" name="payment_method" value="cash" checked required
                                                class="sr-only peer">
                                            <div
                                                class="flex flex-col items-center p-2 bg-gray-50 border-2 border-gray-200 rounded-lg peer-checked:border-blue-500 peer-checked:bg-blue-50 transition-colors">
                                                <i
                                                    class="fas fa-money-bill-wave text-gray-600 peer-checked:text-blue-600 text-base md:text-lg mb-1"></i>
                                                <span class="text-xs md:text-sm font-medium">Tunai</span>
                                            </div>
                                        </label>
                                        <label class="payment-option relative cursor-pointer">
                                            <input type="radio" name="payment_method" value="debit_card" required
                                                class="sr-only peer">
                                            <div
                                                class="flex flex-col items-center p-2 bg-gray-50 border-2 border-gray-200 rounded-lg peer-checked:border-blue-500 peer-checked:bg-blue-50 transition-colors">
                                                <i
                                                    class="fas fa-credit-card text-gray-600 peer-checked:text-blue-600 text-base md:text-lg mb-1"></i>
                                                <span class="text-xs md:text-sm font-medium">Debit</span>
                                            </div>
                                        </label>
                                        <label class="payment-option relative cursor-pointer">
                                            <input type="radio" name="payment_method" value="credit_card" required
                                                class="sr-only peer">
                                            <div
                                                class="flex flex-col items-center p-2 bg-gray-50 border-2 border-gray-200 rounded-lg peer-checked:border-purple-500 peer-checked:bg-purple-50 transition-colors">
                                                <i
                                                    class="fas fa-hand-holding-usd text-gray-600 peer-checked:text-purple-600 text-base md:text-lg mb-1"></i>
                                                <span class="text-xs md:text-sm font-medium">Hutang</span>
                                            </div>
                                        </label>
                                        <label class="payment-option relative cursor-pointer">
                                            <input type="radio" name="payment_method" value="e_wallet" required
                                                class="sr-only peer">
                                            <div
                                                class="flex flex-col items-center p-2 bg-gray-50 border-2 border-gray-200 rounded-lg peer-checked:border-blue-500 peer-checked:bg-blue-50 transition-colors">
                                                <i
                                                    class="fas fa-mobile-alt text-gray-600 peer-checked:text-blue-600 text-base md:text-lg mb-1"></i>
                                                <span class="text-xs md:text-sm font-medium">E-Wallet</span>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <!-- Cash Input Section -->
                                <div id="cashInputSection" class="mb-4 md:mb-6 max-w-sm">
                                    <label for="cash_received"
                                        class="block text-xs md:text-sm font-medium text-gray-700 mb-1 md:mb-2">
                                        Uang Diterima *
                                    </label>
                                    <div class="flex items-stretch shadow-sm">
                                        <span
                                            class="flex items-center bg-gray-100 border border-r-0 border-gray-300 rounded-l-lg px-3 py-1.5 md:py-2 font-bold text-gray-600 text-sm">
                                            Rp
                                        </span>
                                        <input type="number" id="cash_received" name="cash_received" value="0"
                                            min="0" step="100" required
                                            class="block w-full px-3 py-1.5 md:py-2 border border-gray-300 rounded-r-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-right font-semibold text-sm md:text-base outline-none transition-all"
                                            placeholder="0">
                                    </div>
                                    <div class="mt-2 flex justify-between text-[10px] md:text-xs text-gray-500">
                                        <span>Min. Pembayaran: <span id="min_payment_label">0</span></span>
                                        <span class="font-medium text-blue-600">Kembalian: <span
                                                id="change_amount">0</span></span>
                                    </div>
                                </div>

                                <!-- Change Row -->
                                <div id="changeRow"
                                    class="max-w-sm bg-green-50 border border-green-200 rounded-lg p-3 md:p-4 mb-4 md:mb-6 shadow-sm hidden transition-all duration-300">
                                    <div class="flex justify-between items-center">
                                        <div class="flex items-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-600"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span class="text-green-800 font-medium text-xs md:text-sm">Kembalian</span>
                                        </div>
                                        <div class="text-right">
                                            <span id="changeAmount"
                                                class="text-green-600 font-extrabold text-base md:text-lg">
                                                Rp 0
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="grid grid-cols-1 gap-2 md:gap-3">
                                    <button type="button" id="resetBtn"
                                        class="w-full bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 md:py-3 px-4 rounded-lg flex items-center justify-center gap-2 transition-colors text-sm md:text-base">
                                        <i class="fas fa-redo text-xs md:text-sm"></i>
                                        Reset
                                    </button>
                                    <button type="submit" id="processBtn" disabled
                                        class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 md:py-3 px-4 rounded-lg flex items-center justify-center gap-2 transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-sm md:text-base">
                                        <i class="fas fa-cash-register text-xs md:text-sm"></i>
                                        Proses Transaksi
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Hidden Inputs -->
                        <input type="hidden" id="itemsData" name="items" value="">
                        <input type="hidden" id="total_amount" name="total_amount" value="0">
                        <input type="hidden" id="change_amount" name="change" value="0">
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
    <style>
        /* Custom styles for POS System */
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            word-break: break-word;
        }

        .clicked {
            animation: clickAnimation 0.3s ease;
        }

        @keyframes clickAnimation {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(0.95);
            }

            100% {
                transform: scale(1);
            }
        }

        /* ===== PERBAIKAN TOAST NOTIFICATION ===== */
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            min-width: 280px;
            max-width: 350px;
            padding: 12px 16px;
            border-radius: 8px;
            color: white;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 9999;
            animation: slideIn 0.3s ease;
            line-height: 1.4;
            height: auto;
            min-height: 50px;
            backdrop-filter: blur(4px);
        }

        .toast i {
            font-size: 18px;
            flex-shrink: 0;
        }

        .toast span {
            flex: 1;
            word-break: break-word;
        }

        .toast-success {
            background: #10b981;
            border-left: 4px solid #059669;
        }

        .toast-error {
            background: #ef4444;
            border-left: 4px solid #dc2626;
        }

        .toast-info {
            background: #3b82f6;
            border-left: 4px solid #2563eb;
        }

        .toast-warning {
            background: #f59e0b;
            border-left: 4px solid #d97706;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }

            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }

        /* Scrollbar Styling */
        .overflow-y-auto::-webkit-scrollbar {
            width: 6px;
        }

        .overflow-y-auto::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }

        .overflow-y-auto::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }

        .overflow-y-auto::-webkit-scrollbar-thumb:hover {
            background: #a1a1a1;
        }

        /* Custom Animation for Cart Item */
        .cart-item-enter {
            animation: slideInUp 0.3s ease;
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Product card responsive adjustments */
        @media (max-width: 640px) {
            .products-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            /* Toast mobile */
            .toast {
                top: 16px;
                right: 16px;
                left: 16px;
                min-width: auto;
                max-width: none;
            }
        }

        @media (min-width: 641px) and (max-width: 1024px) {
            .products-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (min-width: 1025px) {
            .products-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        @media (min-width: 1280px) {
            .products-grid {
                grid-template-columns: repeat(5, 1fr);
            }
        }

        /* Truncate long text */
        .truncate {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* Badge styles */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 500;
        }
    </style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        /* =========================================================
                                   POS SYSTEM - RESPONSIVE VERSION
                                   ========================================================= */

        let cart = {};
        let products = {};
        let searchTimer = null;
        let selectedMember = null;

        /* =========================
           INITIALIZATION
        ========================= */
        document.addEventListener('DOMContentLoaded', () => {
            console.log('POS System Initialized');

            // Inisialisasi variabel
            cart = {};
            products = {};

            initProducts();
            initEvents();
            loadCart();
            handlePaymentMethod();
            updateProductsCount();
            initMemberEvents();
        });

        /* =========================
           PRODUCTS INITIALIZATION
        ========================= */
        function initProducts() {
            products = {};
            document.querySelectorAll('.product-card').forEach(card => {
                const id = card.dataset.id;
                const nameElement = card.querySelector('h4');
                products[id] = {
                    id: id,
                    name: nameElement ? nameElement.innerText : card.dataset.name || '',
                    price: parseInt(card.dataset.price) || 0,
                    stock: parseInt(card.dataset.stock) || 0,
                    category: card.dataset.category || '',
                    code: card.dataset.code || 'PRD-' + id,
                    element: card
                };
            });
            console.log('Products initialized:', Object.keys(products).length);
        }

        function updateProductsCount() {
            const visibleCount = document.querySelectorAll('.product-card:not([style*="display: none"])').length;
            const countElement = document.getElementById('productsCount');
            if (countElement) {
                countElement.textContent = visibleCount;
            }
        }

        /* =========================
           MEMBER FUNCTIONS
        ========================= */
        function initMemberEvents() {
            const memberSelect = document.getElementById('member_id');
            const searchMemberBtn = document.getElementById('searchMemberBtn');
            const creditRadio = document.querySelector('input[name="payment_method"][value="credit_card"]');

            if (memberSelect) {
                memberSelect.addEventListener('change', handleMemberChange);
            }

            if (searchMemberBtn) {
                searchMemberBtn.addEventListener('click', openMemberSearchModal);
            }

            if (creditRadio) {
                creditRadio.addEventListener('change', handleCreditPayment);
            }

            // Handle payment method change untuk credit
            document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
                radio.addEventListener('change', handlePaymentMethodWithCredit);
            });
        }

        function handleMemberChange() {
            const select = document.getElementById('member_id');
            const selectedOption = select.options[select.selectedIndex];
            const memberInfoCard = document.getElementById('memberInfoCard');
            const creditLimitWarning = document.getElementById('creditLimitWarning');
            const creditDueDateSection = document.getElementById('creditDueDateSection');

            if (select.value) {
                // Ambil data member
                const limit = parseFloat(selectedOption.dataset.limit) || 0;
                const piutang = parseFloat(selectedOption.dataset.piutang) || 0;
                const sisaLimit = limit - piutang;

                // Update info card
                document.getElementById('memberName').textContent = selectedOption.dataset.nama || '';
                document.getElementById('memberKode').textContent = 'Kode: ' + (selectedOption.dataset.kode || '');
                document.getElementById('memberTipe').textContent = selectedOption.text.split('-')[1]?.trim() || 'Member';
                document.getElementById('memberLimit').textContent = 'Rp ' + formatNumber(limit);
                document.getElementById('memberPiutang').textContent = 'Rp ' + formatNumber(piutang);
                document.getElementById('memberSisaLimit').textContent = 'Rp ' + formatNumber(sisaLimit);

                memberInfoCard.classList.remove('hidden');

                // Cek limit untuk pembayaran credit
                const paymentMethod = document.querySelector('input[name="payment_method"]:checked')?.value;
                if (paymentMethod === 'credit_card') {
                    const total = parseFloat(document.getElementById('total_amount').value) || 0;
                    if (total > sisaLimit) {
                        creditLimitWarning.classList.remove('hidden');
                        document.getElementById('limitWarningText').textContent =
                            `Total transaksi (Rp ${formatNumber(total)}) melebihi sisa limit (Rp ${formatNumber(sisaLimit)})`;
                    } else {
                        creditLimitWarning.classList.add('hidden');
                    }
                }
            } else {
                memberInfoCard.classList.add('hidden');
                creditLimitWarning.classList.add('hidden');
            }
        }

        function handleCreditPayment() {
            const memberSelect = document.getElementById('member_id');
            const creditDueDateSection = document.getElementById('creditDueDateSection');
            const cashInputSection = document.getElementById('cashInputSection');
            const creditLimitWarning = document.getElementById('creditLimitWarning');

            if (!memberSelect.value) {
                toast('error', 'Pilih member terlebih dahulu untuk transaksi kredit');
                document.querySelector('input[name="payment_method"][value="cash"]').checked = true;
                handlePaymentMethod();
                return;
            }

            // Tampilkan section due date
            creditDueDateSection.classList.remove('hidden');
            cashInputSection.classList.add('hidden');

            // Set default due date 30 hari dari sekarang
            const dueDate = new Date();
            dueDate.setDate(dueDate.getDate() + 30);
            document.getElementById('due_date').value = dueDate.toISOString().split('T')[0];

            // Cek limit
            const selectedOption = memberSelect.options[memberSelect.selectedIndex];
            const limit = parseFloat(selectedOption.dataset.limit) || 0;
            const piutang = parseFloat(selectedOption.dataset.piutang) || 0;
            const sisaLimit = limit - piutang;
            const total = parseFloat(document.getElementById('total_amount').value) || 0;

            if (total > sisaLimit) {
                creditLimitWarning.classList.remove('hidden');
                document.getElementById('limitWarningText').textContent =
                    `Total transaksi (Rp ${formatNumber(total)}) melebihi sisa limit (Rp ${formatNumber(sisaLimit)})`;
            } else {
                creditLimitWarning.classList.add('hidden');
            }
        }

        function handlePaymentMethodWithCredit() {
            const method = document.querySelector('input[name="payment_method"]:checked')?.value;
            const creditDueDateSection = document.getElementById('creditDueDateSection');
            const cashInputSection = document.getElementById('cashInputSection');
            const changeRow = document.getElementById('changeRow');
            const creditLimitWarning = document.getElementById('creditLimitWarning');

            if (method === 'credit_card') {
                handleCreditPayment();
            } else {
                creditDueDateSection.classList.add('hidden');
                creditLimitWarning.classList.add('hidden');
                if (method === 'cash') {
                    cashInputSection.classList.remove('hidden');
                } else {
                    cashInputSection.classList.add('hidden');
                }
            }

            // Panggil fungsi asli
            handlePaymentMethod();
        }

        function openMemberSearchModal() {
            // Bisa diimplementasikan modal pencarian member
            // Untuk sekarang, fokus ke select dropdown
            document.getElementById('member_id').focus();
        }

        /* =========================
           EVENT HANDLERS
        ========================= */
        function initEvents() {
            console.log('Initializing events...');

            // Search functionality
            const searchInput = document.getElementById('searchProduct');
            if (searchInput) {
                searchInput.addEventListener('input', () => {
                    clearTimeout(searchTimer);
                    searchTimer = setTimeout(searchProducts, 300);
                });
            }

            const clearSearchBtn = document.getElementById('clearSearch');
            if (clearSearchBtn) {
                clearSearchBtn.addEventListener('click', () => {
                    if (searchInput) {
                        searchInput.value = '';
                        searchProducts();
                    }
                });
            }

            // Category filtering
            document.querySelectorAll('.category-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    document.querySelectorAll('.category-btn').forEach(b => {
                        b.classList.remove('bg-blue-600', 'text-white');
                        b.classList.add('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
                    });
                    btn.classList.remove('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
                    btn.classList.add('bg-blue-600', 'text-white');
                    filterCategory(btn.dataset.category);
                });
            });

            // Add to cart buttons
            document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    addToCart(btn.dataset.productId);
                });
            });

            // Discount and cash input
            const discountEl = document.getElementById('discount');
            if (discountEl) {
                discountEl.addEventListener('input', updateSummary);
            }

            const cashReceivedEl = document.getElementById('cash_received');
            if (cashReceivedEl) {
                cashReceivedEl.addEventListener('input', calculateChange);
            }

            // Clear cart and reset
            const clearCartBtn = document.getElementById('clearCartBtn');
            if (clearCartBtn) {
                clearCartBtn.addEventListener('click', clearCart);
            }

            const resetBtn = document.getElementById('resetBtn');
            if (resetBtn) {
                resetBtn.addEventListener('click', resetTransaction);
            }

            // Payment method
            document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
                radio.addEventListener('change', handlePaymentMethod);
            });

            // Form submission
            const transactionForm = document.getElementById('transactionForm');
            if (transactionForm) {
                transactionForm.addEventListener('submit', handleFormSubmit);
            }
        }

        /* =========================
           CART FUNCTIONS
        ========================= */
        function addToCart(id) {
            console.log('Adding to cart:', id);

            const product = products[id];
            if (!product) {
                console.error('Product not found:', id);
                toast('error', 'Produk tidak ditemukan');
                return;
            }

            if (product.stock <= 0) {
                toast('error', 'Stok habis');
                return;
            }

            if (!cart[id]) {
                cart[id] = {
                    id: product.id,
                    name: product.name,
                    price: product.price,
                    stock: product.stock,
                    code: product.code,
                    qty: 1,
                    subtotal: product.price
                };
            } else {
                if (cart[id].qty >= product.stock) {
                    toast('error', 'Stok tidak cukup');
                    return;
                }
                cart[id].qty++;
            }

            updateCart();
            saveCart();

            // Visual feedback
            const btn = document.querySelector(`[data-product-id="${id}"]`);
            if (btn) {
                btn.classList.add('clicked');
                setTimeout(() => btn.classList.remove('clicked'), 300);
            }

            toast('success', `${product.name} ditambahkan`);
        }

        function increaseQuantity(id) {
            if (cart[id] && cart[id].qty < cart[id].stock) {
                cart[id].qty++;
                updateCart();
                saveCart();
            } else {
                toast('error', 'Stok tidak cukup');
            }
        }

        function decreaseQuantity(id) {
            if (!cart[id]) return;
            cart[id].qty--;
            if (cart[id].qty <= 0) {
                delete cart[id];
            }
            updateCart();
            saveCart();
        }

        function updateQuantity(id, value) {
            if (!cart[id]) return;

            let val = parseInt(value);
            if (isNaN(val) || val < 1) {
                val = 1;
            }

            const maxStock = cart[id].stock;
            if (val > maxStock) {
                toast('error', `Stok hanya tersedia ${maxStock} unit`);
                cart[id].qty = maxStock;
            } else {
                cart[id].qty = val;
            }

            updateCart();
            saveCart();
        }

        function removeFromCart(id) {
            if (!cart[id]) return;

            if (!confirm('Hapus produk dari keranjang?')) return;
            delete cart[id];
            updateCart();
            saveCart();
            toast('info', 'Produk dihapus dari keranjang');
        }

        /* =========================
           CART UI UPDATES
        ========================= */
        function updateCart() {
            console.log('Updating cart...');

            const tbody = document.getElementById('cartItems');
            const emptyCart = document.getElementById('emptyCart');
            const cartTable = document.getElementById('cartTable');
            const summary = document.getElementById('transactionSummary');
            const processBtn = document.getElementById('processBtn');

            if (!tbody || !emptyCart || !cartTable || !summary) {
                console.error('Required cart elements not found');
                return;
            }

            tbody.innerHTML = '';
            let totalItems = 0;
            let subtotal = 0;

            Object.values(cart).forEach(item => {
                const itemSubtotal = item.qty * item.price;
                totalItems += item.qty;
                subtotal += itemSubtotal;

                const row = document.createElement('tr');
                row.className = 'cart-item-enter';
                row.innerHTML = `
                    <td class="px-2 md:px-4 py-2">
                        <div>
                            <div class="font-medium text-gray-900 text-xs md:text-sm truncate max-w-[100px] md:max-w-none">${item.name}</div>
                            <div class="text-xs text-gray-500">${item.code}</div>
                        </div>
                    </td>
                    <td class="px-2 md:px-4 py-2">
                        <div class="flex items-center gap-1 md:gap-2">
                            <button type="button" class="w-6 h-6 md:w-7 md:h-7 flex items-center justify-center border border-gray-300 rounded hover:bg-gray-100 minus-btn">
                                <i class="fas fa-minus text-xs text-gray-600"></i>
                            </button>
                            <input type="number" value="${item.qty}" min="1" max="${item.stock}" class="w-12 md:w-16 px-1 md:px-2 py-1 text-center border border-gray-300 rounded text-xs md:text-sm qty-input">
                            <button type="button" class="w-6 h-6 md:w-7 md:h-7 flex items-center justify-center border border-gray-300 rounded hover:bg-gray-100 plus-btn">
                                <i class="fas fa-plus text-xs text-gray-600"></i>
                            </button>
                        </div>
                    </td>
                    <td class="px-2 md:px-4 py-2 font-medium text-xs md:text-sm">Rp ${formatNumber(item.price)}</td>
                    <td class="px-2 md:px-4 py-2 font-bold text-xs md:text-sm">Rp ${formatNumber(itemSubtotal)}</td>
                    <td class="px-2 md:px-4 py-2">
                        <button type="button" class="text-red-600 hover:text-red-800 p-1 delete-btn">
                            <i class="fas fa-trash text-xs md:text-sm"></i>
                        </button>
                    </td>
                `;

                // Add event listeners to buttons
                const minusBtn = row.querySelector('.minus-btn');
                const plusBtn = row.querySelector('.plus-btn');
                const qtyInput = row.querySelector('.qty-input');
                const deleteBtn = row.querySelector('.delete-btn');

                minusBtn.addEventListener('click', () => decreaseQuantity(item.id));
                plusBtn.addEventListener('click', () => increaseQuantity(item.id));
                qtyInput.addEventListener('change', (e) => updateQuantity(item.id, e.target.value));
                deleteBtn.addEventListener('click', () => removeFromCart(item.id));

                tbody.appendChild(row);
            });

            if (totalItems === 0) {
                emptyCart.classList.remove('hidden');
                cartTable.classList.add('hidden');
                summary.classList.add('hidden');
                if (processBtn) processBtn.disabled = true;
                console.log('Cart is empty');
                return;
            }

            emptyCart.classList.add('hidden');
            cartTable.classList.remove('hidden');
            summary.classList.remove('hidden');
            if (processBtn) processBtn.disabled = false;

            const totalItemsEl = document.getElementById('totalItems');
            const subtotalEl = document.getElementById('subtotal');

            if (totalItemsEl) totalItemsEl.textContent = totalItems;
            if (subtotalEl) subtotalEl.textContent = `Rp ${formatNumber(subtotal)}`;

            updateSummary();
        }

        /* =========================
           CALCULATION FUNCTIONS
        ========================= */
        function updateSummary() {
            let subtotal = 0;
            Object.values(cart).forEach(item => {
                subtotal += item.qty * item.price;
            });

            const discountEl = document.getElementById('discount');
            const discount = Math.min(Math.max(parseFloat(discountEl.value) || 0, 0), 100);
            if (discountEl) discountEl.value = discount;

            const discountAmount = subtotal * discount / 100;
            const total = subtotal - discountAmount;

            const discountAmountEl = document.getElementById('discountAmount');
            const grandTotalEl = document.getElementById('grandTotal');
            const totalAmountInput = document.getElementById('total_amount');

            if (discountAmountEl) discountAmountEl.textContent = `Rp ${Math.round(discountAmount).toLocaleString('id-ID')}`;
            if (grandTotalEl) grandTotalEl.textContent = `Rp ${Math.round(total).toLocaleString('id-ID')}`;
            if (totalAmountInput) totalAmountInput.value = total;

            // Update limit warning untuk credit payment
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked')?.value;
            if (paymentMethod === 'credit_card') {
                const memberSelect = document.getElementById('member_id');
                if (memberSelect.value) {
                    const selectedOption = memberSelect.options[memberSelect.selectedIndex];
                    const limit = parseFloat(selectedOption.dataset.limit) || 0;
                    const piutang = parseFloat(selectedOption.dataset.piutang) || 0;
                    const sisaLimit = limit - piutang;
                    const creditLimitWarning = document.getElementById('creditLimitWarning');

                    if (total > sisaLimit) {
                        creditLimitWarning.classList.remove('hidden');
                        document.getElementById('limitWarningText').textContent =
                            `Total transaksi (Rp ${formatNumber(total)}) melebihi sisa limit (Rp ${formatNumber(sisaLimit)})`;
                    } else {
                        creditLimitWarning.classList.add('hidden');
                    }
                }
            }

            calculateChange();
            return total;
        }

        function calculateChange() {
            const cashEl = document.getElementById('cash_received');
            const cash = parseFloat(cashEl.value) || 0;
            const total = parseFloat(document.getElementById('total_amount').value) || 0;

            const changeRow = document.getElementById('changeRow');
            const changeAmount = document.getElementById('changeAmount');
            const hiddenChangeEl = document.getElementById('change_amount');

            if (!changeRow || !changeAmount) return;

            const paymentMethodEl = document.querySelector('input[name="payment_method"]:checked');
            if (!paymentMethodEl) return;

            const paymentMethod = paymentMethodEl.value;

            if (paymentMethod !== 'cash' || cash <= 0) {
                changeRow.classList.add('hidden');
                if (hiddenChangeEl) hiddenChangeEl.value = 0;
                return;
            }

            const change = cash - total;

            if (hiddenChangeEl) hiddenChangeEl.value = change;

            if (change >= 0) {
                changeRow.classList.remove('hidden');
                changeAmount.textContent = `Rp ${Math.round(change).toLocaleString('id-ID')}`;
                changeRow.className =
                    'max-w-sm bg-green-50 border border-green-200 rounded-lg p-3 md:p-4 mb-4 md:mb-6 shadow-sm transition-all duration-300';
            } else {
                changeRow.classList.remove('hidden');
                changeAmount.textContent = `Rp ${Math.abs(Math.round(change)).toLocaleString('id-ID')} (Kurang)`;
                changeRow.className =
                    'max-w-sm bg-red-50 border border-red-200 rounded-lg p-3 md:p-4 mb-4 md:mb-6 shadow-sm transition-all duration-300';
            }
        }

        function formatNumber(number) {
            return new Intl.NumberFormat('id-ID').format(Math.round(number));
        }

        /* =========================
           PAYMENT METHOD HANDLER
        ========================= */
        function handlePaymentMethod() {
            const methodEl = document.querySelector('input[name="payment_method"]:checked');
            if (!methodEl) return;

            const method = methodEl.value;
            const cashBox = document.getElementById('cashInputSection');
            const cashInput = document.getElementById('cash_received');
            const changeRow = document.getElementById('changeRow');

            if (method === 'cash') {
                if (cashBox) cashBox.classList.remove('hidden');
                if (cashInput) {
                    cashInput.disabled = false;
                    cashInput.required = true;
                    setTimeout(() => cashInput.focus(), 100);
                }
                calculateChange();
            } else if (method === 'credit_card') {
                // Already handled by handleCreditPayment
                if (cashBox) cashBox.classList.add('hidden');
                if (cashInput) {
                    cashInput.disabled = true;
                    cashInput.required = false;
                    cashInput.value = 0;
                }
                if (changeRow) changeRow.classList.add('hidden');
                const hiddenChangeEl = document.getElementById('change_amount');
                if (hiddenChangeEl) hiddenChangeEl.value = 0;
            } else {
                if (cashBox) cashBox.classList.add('hidden');
                if (cashInput) {
                    cashInput.disabled = true;
                    cashInput.required = false;
                    cashInput.value = 0;
                }
                if (changeRow) changeRow.classList.add('hidden');
                const hiddenChangeEl = document.getElementById('change_amount');
                if (hiddenChangeEl) hiddenChangeEl.value = 0;
            }
        }

        /* =========================
           FORM SUBMISSION
        ========================= */
        function handleFormSubmit(e) {
            e.preventDefault();
            console.log('Form submission started...');

            // Validation
            if (Object.keys(cart).length === 0) {
                toast('error', 'Keranjang belanja kosong');
                return false;
            }

            // Validate customer name
            const customerName = document.getElementById('customer_name').value.trim();
            if (!customerName) {
                toast('error', 'Nama pelanggan wajib diisi');
                document.getElementById('customer_name').focus();
                return false;
            }

            // Validate items stock
            for (const [id, item] of Object.entries(cart)) {
                if (item.qty > item.stock) {
                    toast('error', `Stok ${item.name} tidak mencukupi`);
                    return false;
                }
            }

            // Validate member untuk credit payment
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked')?.value;
            if (paymentMethod === 'credit_card') {
                const memberId = document.getElementById('member_id').value;
                if (!memberId) {
                    toast('error', 'Pilih member untuk transaksi kredit');
                    document.getElementById('member_id').focus();
                    return false;
                }

                const dueDate = document.getElementById('due_date').value;
                if (!dueDate) {
                    toast('error', 'Tanggal jatuh tempo wajib diisi');
                    document.getElementById('due_date').focus();
                    return false;
                }

                // Check limit
                const selectedOption = document.getElementById('member_id').options[document.getElementById('member_id')
                    .selectedIndex];
                const limit = parseFloat(selectedOption.dataset.limit) || 0;
                const piutang = parseFloat(selectedOption.dataset.piutang) || 0;
                const sisaLimit = limit - piutang;
                const total = parseFloat(document.getElementById('total_amount').value) || 0;

                if (total > sisaLimit) {
                    toast('error', 'Melebihi limit kredit member');
                    return false;
                }
            }

            // Validate cash payment
            if (paymentMethod === 'cash') {
                const cashReceived = parseFloat(document.getElementById('cash_received').value) || 0;
                const totalAmount = parseFloat(document.getElementById('total_amount').value) || 0;

                if (cashReceived < totalAmount) {
                    toast('error', 'Uang diterima kurang dari total pembayaran');
                    document.getElementById('cash_received').focus();
                    return false;
                }
            }

            // Prepare items data
            const items = [];
            Object.values(cart).forEach(item => {
                items.push({
                    product_id: parseInt(item.id),
                    qty: parseInt(item.qty),
                    price: parseFloat(item.price),
                    subtotal: parseFloat(item.qty * item.price)
                });
            });

            // Set hidden inputs
            const itemsDataInput = document.getElementById('itemsData');
            if (itemsDataInput) {
                itemsDataInput.value = JSON.stringify(items);
            }

            // Show loading state
            const submitBtn = document.getElementById('processBtn');
            if (submitBtn) {
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
                submitBtn.disabled = true;
            }

            // Clear cart from localStorage
            localStorage.removeItem('pos_cart');

            // Submit the form
            console.log('Submitting form...');
            const form = document.getElementById('transactionForm');
            form.submit();

            return false;
        }

        /* =========================
           LOCAL STORAGE
        ========================= */
        function saveCart() {
            try {
                const cartData = {};
                Object.entries(cart).forEach(([key, item]) => {
                    cartData[key] = {
                        id: item.id,
                        qty: item.qty,
                        price: item.price,
                        name: item.name,
                        code: item.code,
                        stock: item.stock
                    };
                });
                localStorage.setItem('pos_cart', JSON.stringify(cartData));
                console.log('Cart saved to localStorage');
            } catch (e) {
                console.error('Error saving cart:', e);
            }
        }

        function loadCart() {
            try {
                const saved = localStorage.getItem('pos_cart');
                if (saved) {
                    const savedCart = JSON.parse(saved);
                    cart = {};

                    Object.entries(savedCart).forEach(([key, item]) => {
                        const product = products[key];
                        if (product) {
                            cart[key] = {
                                id: product.id,
                                name: product.name,
                                price: product.price,
                                stock: product.stock,
                                code: product.code,
                                qty: Math.min(item.qty, product.stock)
                            };
                        }
                    });

                    console.log('Cart loaded from localStorage:', Object.keys(cart).length);
                    updateCart();
                } else {
                    console.log('No saved cart found in localStorage');
                }
            } catch (e) {
                console.error('Error loading cart:', e);
                localStorage.removeItem('pos_cart');
                cart = {};
            }
        }

        /* =========================
           SEARCH & FILTER
        ========================= */
        function searchProducts() {
            const searchInput = document.getElementById('searchProduct');
            if (!searchInput) return;

            const searchValue = searchInput.value.toLowerCase().trim();
            const activeCategoryEl = document.querySelector('.category-btn.bg-blue-600');
            const activeCategory = activeCategoryEl ? activeCategoryEl.dataset.category : 'all';

            let count = 0;

            document.querySelectorAll('.product-card').forEach(card => {
                const name = card.dataset.name.toLowerCase();
                const category = card.dataset.category;
                const code = (card.dataset.code || '').toLowerCase();

                const matchesSearch = name.includes(searchValue) || code.includes(searchValue);
                const matchesCategory = activeCategory === 'all' || category === activeCategory;

                if (matchesSearch && matchesCategory) {
                    card.style.display = 'flex';
                    count++;
                } else {
                    card.style.display = 'none';
                }
            });

            updateProductsCount();
        }

        function filterCategory(category) {
            const searchInput = document.getElementById('searchProduct');
            const searchValue = searchInput ? searchInput.value.toLowerCase().trim() : '';

            let count = 0;

            document.querySelectorAll('.product-card').forEach(card => {
                const name = card.dataset.name.toLowerCase();
                const cardCategory = card.dataset.category;
                const code = (card.dataset.code || '').toLowerCase();

                const matchesSearch = name.includes(searchValue) || code.includes(searchValue);
                const matchesCategory = category === 'all' || cardCategory === category;

                if (matchesSearch && matchesCategory) {
                    card.style.display = 'flex';
                    count++;
                } else {
                    card.style.display = 'none';
                }
            });

            updateProductsCount();
        }

        /* =========================
           UTILITY FUNCTIONS
        ========================= */
        function clearCart() {
            if (Object.keys(cart).length === 0) {
                toast('info', 'Keranjang sudah kosong');
                return;
            }

            if (!confirm('Apakah Anda yakin ingin mengosongkan keranjang?')) return;

            cart = {};
            updateCart();
            saveCart();
            toast('success', 'Keranjang dikosongkan');
        }

        function resetTransaction() {
            if (!confirm('Apakah Anda yakin ingin mereset transaksi? Semua data akan dihapus.')) return;

            cart = {};

            // Reset form
            const transactionForm = document.getElementById('transactionForm');
            if (transactionForm) {
                transactionForm.reset();

                // Set default customer name
                const customerNameInput = document.getElementById('customer_name');
                if (customerNameInput) {
                    customerNameInput.value = 'Pelanggan Umum';
                }
            }

            // Reset member selection
            const memberSelect = document.getElementById('member_id');
            if (memberSelect) {
                memberSelect.value = '';
                document.getElementById('memberInfoCard').classList.add('hidden');
                document.getElementById('creditDueDateSection').classList.add('hidden');
                document.getElementById('creditLimitWarning').classList.add('hidden');
            }

            // Reset category filter
            document.querySelectorAll('.category-btn').forEach(btn => {
                btn.classList.remove('bg-blue-600', 'text-white');
                btn.classList.add('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
            });
            const allCategoryBtn = document.querySelector('.category-btn[data-category="all"]');
            if (allCategoryBtn) {
                allCategoryBtn.classList.remove('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
                allCategoryBtn.classList.add('bg-blue-600', 'text-white');
            }

            // Reset search
            const searchInput = document.getElementById('searchProduct');
            if (searchInput) searchInput.value = '';

            // Reset payment method to cash
            const cashRadio = document.querySelector('input[name="payment_method"][value="cash"]');
            if (cashRadio) {
                cashRadio.checked = true;
                cashRadio.dispatchEvent(new Event('change'));
            }

            updateCart();
            saveCart();
            searchProducts();

            toast('info', 'Transaksi direset');
        }

        /* =========================
       TOAST NOTIFICATION - PERBAIKAN
    ========================= */
        function toast(type, message) {
            // Hapus toast yang sudah ada
            document.querySelectorAll('.toast').forEach(toast => {
                toast.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            });

            // Buat elemen toast baru
            const toastEl = document.createElement('div');
            toastEl.className = `toast toast-${type}`;
            toastEl.setAttribute('role', 'alert');

            const icons = {
                success: 'fa-check-circle',
                error: 'fa-exclamation-circle',
                warning: 'fa-exclamation-triangle',
                info: 'fa-info-circle'
            };

            toastEl.innerHTML = `
        <i class="fas ${icons[type] || icons.info}"></i>
        <span>${message}</span>
    `;

            document.body.appendChild(toastEl);

            // Auto remove setelah 3 detik
            setTimeout(() => {
                if (toastEl.parentNode) {
                    toastEl.style.animation = 'slideOut 0.3s ease';
                    setTimeout(() => {
                        if (toastEl.parentNode) toastEl.remove();
                    }, 300);
                }
            }, 3000);
        }
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\tokoroni-app\resources\views/transactions/create.blade.php ENDPATH**/ ?>