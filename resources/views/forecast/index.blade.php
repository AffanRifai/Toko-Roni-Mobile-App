@extends('layouts.app')

@section('title', 'AI Sales Forecasting')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">AI Sales Forecasting</h1>
            <p class="text-gray-600">Prediksi penjualan dan stok produk menggunakan kecerdasan buatan.</p>
        </div>
        <div class="bg-indigo-100 p-3 rounded-lg flex items-center shadow-sm">
            <i class="fas fa-robot text-indigo-600 text-2xl mr-3"></i>
            <div>
                <span class="block text-xs text-indigo-500 font-bold uppercase tracking-wider">Engine Status</span>
                <span class="text-sm font-semibold text-indigo-800">Linear Regression Active</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Daily Chart Section -->
        <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
            <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                <i class="fas fa-calendar-day text-indigo-500 mr-2"></i>
                Prediksi Harian (7 Hari)
            </h2>
            <div class="h-64 w-full bg-gray-50 rounded-lg border border-dashed border-gray-200 flex items-center justify-center relative overflow-hidden">
                <canvas id="forecastChart"></canvas>
            </div>
        </div>

        <!-- Monthly Chart Section -->
        <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
            <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                <i class="fas fa-calendar-alt text-blue-500 mr-2"></i>
                Proyeksi Bulanan (3 Bulan)
            </h2>
            <div class="h-64 w-full bg-gray-50 rounded-lg border border-dashed border-gray-200 flex items-center justify-center relative overflow-hidden">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Demand Prediction -->
        <div class="lg:col-span-2 bg-white rounded-xl shadow-md p-6 border border-gray-100">
            <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                <i class="fas fa-boxes text-orange-500 mr-2"></i>
                Prediksi Permintaan Produk & Strategi Stok
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($demandForecast as $item)
                <div class="p-4 rounded-lg border {{ $item['stock_status'] == 'danger' ? 'bg-red-50 border-red-100' : 'bg-green-50 border-green-100' }}">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <h3 class="font-bold text-gray-800">{{ $item['name'] }}</h3>
                            <span class="text-xs text-gray-500">{{ $item['code'] }}</span>
                        </div>
                        <span class="px-2 py-1 rounded text-[10px] font-bold uppercase {{ $item['stock_status'] == 'danger' ? 'bg-red-200 text-red-800' : 'bg-green-200 text-green-800' }}">
                            {{ $item['recommendation'] }}
                        </span>
                    </div>
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-600">Stok: <strong>{{ $item['current_stock'] }}</strong></span>
                        <span class="text-gray-600">Butuh: <strong>{{ $item['projected_demand'] }}</strong></span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Expert Recommendations -->
        <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
            <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                <i class="fas fa-lightbulb text-yellow-500 mr-2"></i>
                Rekomendasi Strategis
            </h2>
            <div class="space-y-4">
                <div class="p-3 bg-blue-50 border-l-4 border-blue-500 text-sm">
                    <p class="font-bold text-blue-800 italic">"Gunakan data historis untuk mengoptimalkan cash flow."</p>
                </div>
                <div class="text-sm text-gray-600 space-y-2">
                    <div class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                        <span>Fokuskan 80% modal pada barang Fast Moving (Beras, Minyak).</span>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                        <span>Tingkatkan stok 20% sebelum periode ramai (Awal Bulan).</span>
                    </div>
                    <div class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                        <span>Pantau margin profit harian untuk mengimbangi inflasi.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-8 grid grid-cols-1 md:grid-cols-4 gap-4">
        @foreach($salesForecast as $index => $day)
        @if($index < 4)
        <div class="bg-indigo-600 rounded-xl p-6 text-white shadow-lg transform hover:scale-105 transition duration-300">
            <span class="text-indigo-200 text-xs font-bold uppercase">{{ $day['label'] }}</span>
            <div class="mt-2 text-2xl font-black">Rp {{ number_format($day['value'], 0, ',', '.') }}</div>
            <div class="mt-4 flex items-center text-xs">
                <span class="bg-indigo-500 bg-opacity-50 px-2 py-1 rounded-full mr-2">Confidence: {{ $day['confidence'] }}%</span>
            </div>
        </div>
        @endif
        @endforeach
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Daily Chart
        const ctx = document.getElementById('forecastChart');
        const dailyData = @json($salesForecast);
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: dailyData.map(d => d.label),
                datasets: [{
                    label: 'Proyeksi Harian (IDR)',
                    data: dailyData.map(d => d.value),
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
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
                    label: 'Penjualan Bulanan (IDR)',
                    data: dataValues,
                    backgroundColor: colors,
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                }
            }
        });
    });
</script>
@endsection
