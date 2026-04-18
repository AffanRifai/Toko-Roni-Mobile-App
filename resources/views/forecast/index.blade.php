@extends('layouts.app')

@section('title', 'AI Sales Forecasting & Restock Planner')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">AI Sales Forecasting & Restock Planner</h1>
            <p class="text-gray-600">Prediksi penjualan dan rekomendasi restock berdasarkan data historis transaksi.</p>
        </div>
        <div class="bg-indigo-100 p-3 rounded-lg flex items-center shadow-sm">
            <i class="fas fa-robot text-indigo-600 text-2xl mr-3"></i>
            <div>
                <span class="block text-xs text-indigo-500 font-bold uppercase tracking-wider">Engine Status</span>
                <span class="text-sm font-semibold text-indigo-800">Real-time Analytics Active</span>
            </div>
        </div>
    </div>

    <!-- Stats Summary -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm">Prediksi 7 Hari</p>
                    <p class="text-2xl font-bold">Rp {{ number_format(array_sum(array_column($salesForecast ?? [], 'value')), 0, ',', '.') }}</p>
                </div>
                <i class="fas fa-chart-line text-3xl opacity-50"></i>
            </div>
        </div>
        <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-xl p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm">Rekomendasi Restock</p>
                    <p class="text-2xl font-bold">{{ $restockPlan['total_urgent_items'] ?? 0 }} Produk</p>
                </div>
                <i class="fas fa-boxes text-3xl opacity-50"></i>
            </div>
        </div>
        <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm">Rata-rata Harian</p>
                    <p class="text-2xl font-bold">Rp {{ number_format(($salesForecast[0]['value'] ?? 0), 0, ',', '.') }}</p>
                </div>
                <i class="fas fa-calendar-day text-3xl opacity-50"></i>
            </div>
        </div>
        <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-xl p-4 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-100 text-sm">Confidence Level</p>
                    <p class="text-2xl font-bold">{{ $salesForecast[0]['confidence'] ?? 85 }}%</p>
                </div>
                <i class="fas fa-chart-simple text-3xl opacity-50"></i>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Daily Chart Section -->
        <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
            <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                <i class="fas fa-calendar-day text-indigo-500 mr-2"></i>
                Prediksi Penjualan Harian (7 Hari ke Depan)
            </h2>
            <div class="h-80 w-full">
                <canvas id="forecastChart"></canvas>
            </div>
            <div class="mt-4 grid grid-cols-7 gap-2 text-center text-xs">
                @foreach($salesForecast ?? [] as $day)
                <div class="p-2 rounded-lg {{ $loop->index == 0 ? 'bg-indigo-50' : '' }}">
                    <p class="font-semibold">{{ $day['day'] }}</p>
                    <p class="text-indigo-600 font-bold">Rp {{ number_format($day['value'], 0, ',', '.') }}</p>
                    <p class="text-gray-400 text-[10px]">Conf: {{ $day['confidence'] }}%</p>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Monthly Chart Section -->
        <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
            <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                <i class="fas fa-calendar-alt text-blue-500 mr-2"></i>
                Proyeksi Penjualan Bulanan
            </h2>
            <div class="h-80 w-full">
                <canvas id="monthlyChart"></canvas>
            </div>
            <div class="mt-4 text-center text-sm text-gray-500">
                <span class="inline-flex items-center mr-4">
                    <div class="w-3 h-3 bg-blue-500 rounded-full mr-1"></div> Histori
                </span>
                <span class="inline-flex items-center">
                    <div class="w-3 h-3 bg-indigo-600 rounded-full mr-1"></div> Prediksi
                </span>
            </div>
        </div>
    </div>

    <!-- Restock Recommendations -->
    <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 mb-8">
        <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
            <i class="fas fa-clipboard-list text-orange-500 mr-2"></i>
            Rekomendasi Restock Produk
        </h2>
        
        @if(!empty($restockPlan['urgent']) && count($restockPlan['urgent']) > 0)
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-red-600 mb-3 flex items-center">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                Urgent - Segera Restock
            </h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produk</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Stok Saat Ini</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Prediksi Terjual (14 Hari)</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Kebutuhan Restock</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Rata-rata Harian</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($restockPlan['urgent'] as $product)
                        <tr class="hover:bg-red-50 transition">
                            <td class="px-4 py-3">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $product['name'] }}</p>
                                    <p class="text-xs text-gray-500">{{ $product['code'] }}</p>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    {{ $product['current_stock'] }} {{ $product['unit'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center font-semibold text-orange-600">
                                {{ $product['projected_demand'] }} {{ $product['unit'] }}
                            </td>
                            <td class="px-4 py-3 text-center font-bold text-red-600">
                                {{ $product['needed_qty'] }} {{ $product['unit'] }}
                            </td>
                            <td class="px-4 py-3 text-center text-gray-600">
                                {{ $product['daily_avg_sales'] }} {{ $product['unit'] }}/hari
                            </td>
                            <td class="px-4 py-3 text-center">
                                <button onclick="openRestockModal({{ $product['id'] }}, '{{ $product['name'] }}', {{ $product['current_stock'] }}, {{ $product['needed_qty'] }})"
                                        class="px-3 py-1 bg-green-600 text-white text-xs rounded-lg hover:bg-green-700 transition">
                                    <i class="fas fa-boxes mr-1"></i> Restock
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        @if(!empty($restockPlan['planned']) && count($restockPlan['planned']) > 0)
        <div>
            <h3 class="text-lg font-semibold text-yellow-600 mb-3 flex items-center">
                <i class="fas fa-clock mr-2"></i>
                Planned - Persiapkan Restock
            </h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produk</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Stok Saat Ini</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Prediksi Terjual (14 Hari)</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Kebutuhan Restock</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Rata-rata Harian</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($restockPlan['planned'] as $product)
                        <tr class="hover:bg-yellow-50 transition">
                            <td class="px-4 py-3">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $product['name'] }}</p>
                                    <p class="text-xs text-gray-500">{{ $product['code'] }}</p>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    {{ $product['current_stock'] }} {{ $product['unit'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center font-semibold text-orange-600">
                                {{ $product['projected_demand'] }} {{ $product['unit'] }}
                            </td>
                            <td class="px-4 py-3 text-center font-bold text-yellow-600">
                                {{ $product['needed_qty'] }} {{ $product['unit'] }}
                            </td>
                            <td class="px-4 py-3 text-center text-gray-600">
                                {{ $product['daily_avg_sales'] }} {{ $product['unit'] }}/hari
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        @if(empty($restockPlan['urgent']) && empty($restockPlan['planned']))
        <div class="text-center py-8">
            <i class="fas fa-check-circle text-green-500 text-5xl mb-3"></i>
            <p class="text-gray-500">Semua produk dalam kondisi stok aman. Tidak ada rekomendasi restock.</p>
        </div>
        @endif
    </div>

    <!-- AI Insights -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-gradient-to-r from-indigo-50 to-blue-50 rounded-xl p-6">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-brain text-indigo-600"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-800 mb-2">AI Insights</h3>
                    <ul class="space-y-2 text-sm text-gray-700">
                        @php
                            $peakDay = $salesForecast ? array_reduce($salesForecast, function($carry, $item) {
                                return (!$carry || $item['value'] > $carry['value']) ? $item : $carry;
                            }, null) : null;
                        @endphp
                        @if($peakDay)
                        <li class="flex items-start gap-2">
                            <i class="fas fa-chart-line text-indigo-500 mt-1"></i>
                            <span>Puncak penjualan diprediksi pada <strong>{{ $peakDay['day'] }}</strong> dengan estimasi Rp {{ number_format($peakDay['value'], 0, ',', '.') }}</span>
                        </li>
                        @endif
                        @if(!empty($restockPlan['urgent']))
                        <li class="flex items-start gap-2">
                            <i class="fas fa-boxes text-orange-500 mt-1"></i>
                            <span>Segera restock <strong>{{ count($restockPlan['urgent']) }} produk</strong> untuk menghindari kehabisan stok</span>
                        </li>
                        @endif
                        <li class="flex items-start gap-2">
                            <i class="fas fa-chart-simple text-green-500 mt-1"></i>
                            <span>Rekomendasi safety stock: <strong>20%</strong> dari proyeksi kebutuhan</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-green-50 to-teal-50 rounded-xl p-6">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-lightbulb text-green-600"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-800 mb-2">Strategic Recommendations</h3>
                    <ul class="space-y-2 text-sm text-gray-700">
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-green-500 mt-1"></i>
                            <span>Prioritaskan restock produk dengan <strong>turnover tinggi</strong></span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-green-500 mt-1"></i>
                            <span>Gunakan <strong>diskon</strong> untuk produk dengan stok berlebih</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-green-500 mt-1"></i>
                            <span>Persiapkan stok <strong>+30%</strong> untuk akhir pekan dan hari libur</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Restock Modal -->
<div id="restockModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center">
    <div class="bg-white rounded-2xl max-w-md w-full mx-4 animate-slide-up">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-900">Restock Produk</h3>
                <button onclick="closeRestockModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="restockForm" method="POST">
                @csrf
                <input type="hidden" id="productId" name="product_id">
                
                <div class="mb-4 p-3 bg-blue-50 rounded-lg">
                    <p class="text-sm text-gray-700 mb-1" id="productNameDisplay"></p>
                    <p class="text-xs text-gray-500">Stok saat ini: <span id="currentStockDisplay"></span></p>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Jumlah Restock <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="quantity" id="restockQuantity" required min="1"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Catatan (Opsional)
                    </label>
                    <textarea name="note" rows="2"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent"
                        placeholder="Catatan restock..."></textarea>
                </div>
                
                <div class="flex gap-3">
                    <button type="button" onclick="closeRestockModal()"
                        class="flex-1 px-4 py-3 border border-gray-300 rounded-xl text-gray-700 font-medium hover:bg-gray-50">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex-1 px-4 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-xl transition">
                        <i class="fas fa-boxes mr-2"></i> Restock Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Daily Chart
    const ctx = document.getElementById('forecastChart');
    const dailyData = @json($salesForecast);
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: dailyData.map(d => d.label),
            datasets: [{
                label: 'Proyeksi Penjualan (Rp)',
                data: dailyData.map(d => d.value),
                borderColor: '#4f46e5',
                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                fill: true,
                tension: 0.4,
                pointBackgroundColor: dailyData.map((d, i) => i === 0 ? '#10b981' : '#4f46e5'),
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `Rp ${new Intl.NumberFormat('id-ID').format(context.raw)}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                        }
                    }
                }
            }
        }
    });

    // Monthly Chart
    const ctxMonthly = document.getElementById('monthlyChart');
    const monthlyData = @json($monthlyForecast);
    
    const labels = [...monthlyData.history.map(h => h.label), ...monthlyData.prediction.map(p => p.label)];
    const dataValues = [...monthlyData.history.map(h => h.value), ...monthlyData.prediction.map(p => p.value)];
    const colors = [...monthlyData.history.map(() => '#3b82f6'), ...monthlyData.prediction.map(() => '#4f46e5')];

    new Chart(ctxMonthly, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Penjualan Bulanan (Rp)',
                data: dataValues,
                backgroundColor: colors,
                borderRadius: 8,
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `Rp ${new Intl.NumberFormat('id-ID').format(context.raw)}`;
                        }
                    }
                },
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + new Intl.NumberFormat('id-ID').format(value / 1000000) + 'jt';
                        }
                    }
                }
            }
        }
    });

    // Restock Modal Functions
    function openRestockModal(productId, productName, currentStock, neededQty) {
        document.getElementById('productId').value = productId;
        document.getElementById('productNameDisplay').innerHTML = `<strong>Produk:</strong> ${productName}`;
        document.getElementById('currentStockDisplay').innerHTML = `${currentStock} unit`;
        document.getElementById('restockQuantity').value = neededQty;
        document.getElementById('restockForm').action = `/products/${productId}/restock`;
        document.getElementById('restockModal').classList.remove('hidden');
        document.getElementById('restockModal').classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closeRestockModal() {
        document.getElementById('restockModal').classList.add('hidden');
        document.getElementById('restockModal').classList.remove('flex');
        document.body.style.overflow = 'auto';
    }

    // Handle restock form submission
    document.getElementById('restockForm')?.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const action = this.action;
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Memproses...';
        submitBtn.disabled = true;
        
        try {
            const response = await fetch(action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert(data.message || 'Gagal melakukan restock');
            }
        } catch (error) {
            alert('Terjadi kesalahan: ' + error.message);
        } finally {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
            closeRestockModal();
        }
    });
</script>

<style>
    .animate-slide-up {
        animation: slideUp 0.3s ease-out;
    }
    
    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
@endsection