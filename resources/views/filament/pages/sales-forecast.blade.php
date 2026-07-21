<x-filament-panels::page>
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    @endpush

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Forecast Penjualan AI</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Proyeksi penjualan berbasis AI dengan Holt-Winters & narrative explanation
                </p>
            </div>
        </div>

        {{ $this->form }}

        <x-filament::button wire:click="loadForecast" color="primary" size="sm">
            Jalankan Forecast
        </x-filament::button>

        @if(!empty($forecast) || !empty($revenueForecast))
            @php $trend = $this->getTrendSummary(); @endphp
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-xl border p-4">
                    <p class="text-sm text-gray-500">Tren</p>
                    <p class="text-2xl font-bold @if($trend['direction']==='up') text-green-600 @elseif($trend['direction']==='down') text-red-600 @else text-gray-600 @endif">
                        {{ $trend['label'] }}
                    </p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl border p-4">
                    <p class="text-sm text-gray-500">Total Proyeksi</p>
                    <p class="text-2xl font-bold">{{ number_format($trend['total_proyeksi'], 0, ',', '.') }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl border p-4">
                    <p class="text-sm text-gray-500">Rata-rata Harian</p>
                    <p class="text-2xl font-bold">{{ number_format($trend['rata_rata_harian'], 1, ',', '.') }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl border p-4">
                    <p class="text-sm text-gray-500">Hari Naik / Turun</p>
                    <p class="text-2xl font-bold">
                        <span class="text-green-600">{{ $trend['hari_naik'] }}</span>
                        <span class="text-gray-400"> / </span>
                        <span class="text-red-600">{{ $trend['hari_turun'] }}</span>
                    </p>
                </div>
            </div>

            @if($accuracy)
            <div class="grid grid-cols-3 gap-4 bg-white dark:bg-gray-800 rounded-xl border p-4">
                <div class="text-center">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">MAPE (Error %)</p>
                    <p class="text-lg font-bold @if($accuracy['mape'] < 15) text-green-600 @elseif($accuracy['mape'] < 30) text-yellow-600 @else text-red-600 @endif">
                        {{ $accuracy['mape'] }}%
                    </p>
                </div>
                <div class="text-center">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">RMSE</p>
                    <p class="text-lg font-bold">{{ number_format($accuracy['rmse'], 2, ',', '.') }}</p>
                </div>
                <div class="text-center">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Bias</p>
                    <p class="text-lg font-bold">{{ number_format($accuracy['bias'], 2, ',', '.') }}</p>
                </div>
            </div>
            @endif

            <div class="bg-white dark:bg-gray-800 rounded-xl border p-6">
                <h3 class="font-semibold mb-4">Proyeksi {{ count($forecast ?: $revenueForecast) }} Hari Ke Depan</h3>
                <canvas id="forecastChart" height="120"></canvas>
            </div>

            @if($narrative)
            <div class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 rounded-xl p-6">
                <h3 class="font-semibold text-indigo-900 dark:text-indigo-300 mb-2">Narasi Analisis AI</h3>
                <div class="prose prose-sm dark:prose-invert max-w-none text-gray-700 dark:text-gray-300">
                    {!! nl2br(e($narrative)) !!}
                </div>
            </div>
            @endif

            <script>
                const ctx = document.getElementById('forecastChart');
                if (ctx) {
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: @json($this->getChartLabels()),
                            datasets: [
                                {
                                    label: 'Prediksi',
                                    data: @json($this->getChartPredicted()),
                                    borderColor: '#6366f1',
                                    backgroundColor: 'rgba(99,102,241,0.1)',
                                    fill: false,
                                    tension: 0.3,
                                    pointRadius: 2,
                                },
                                {
                                    label: 'Batas Atas (95%)',
                                    data: @json($this->getChartHigh()),
                                    borderColor: 'rgba(99,102,241,0.3)',
                                    backgroundColor: 'rgba(99,102,241,0.05)',
                                    fill: 1,
                                    tension: 0.3,
                                    pointRadius: 0,
                                    borderDash: [4, 4],
                                },
                                {
                                    label: 'Batas Bawah (95%)',
                                    data: @json($this->getChartLow()),
                                    borderColor: 'rgba(99,102,241,0.3)',
                                    backgroundColor: 'rgba(99,102,241,0.05)',
                                    fill: false,
                                    tension: 0.3,
                                    pointRadius: 0,
                                    borderDash: [4, 4],
                                },
                            ],
                        },
                        options: {
                            responsive: true,
                            plugins: { legend: { position: 'top' } },
                            scales: {
                                y: { beginAtZero: false, ticks: { callback: v => v.toLocaleString('id-ID') } },
                            },
                        },
                    });
                }
            </script>
        @endif
    </div>
</x-filament-panels::page>
