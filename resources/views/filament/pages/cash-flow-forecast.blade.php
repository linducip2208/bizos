<x-filament-panels::page>
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    @endpush

    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Proyeksi Arus Kas</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Proyeksi posisi kas harian hingga {{ $days }} hari ke depan
                </p>
            </div>

            <div class="flex items-center gap-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-1">
                @foreach([30, 60, 90, 180] as $option)
                    <button type="button" wire:click="setDays({{ $option }})"
                        class="px-3 py-1.5 text-sm font-medium rounded-md transition
                            {{ $days === $option
                                ? 'bg-primary-600 text-white shadow-sm'
                                : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                        {{ $option }} Hari
                    </button>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-xs text-gray-500 dark:text-gray-400">Kas Saat Ini</p>
                <p class="text-xl font-bold text-gray-900 dark:text-white mt-1">Rp {{ number_format($currentCash, 0, ',', '.') }}</p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-xs text-gray-500 dark:text-gray-400">Saldo Akhir ({{ $days }} hari)</p>
                <p class="text-xl font-bold mt-1 @if($this->getProjectedEnding() < 0) text-red-600 @elseif($this->getProjectedEnding() < $currentCash) text-amber-600 @else text-emerald-600 @endif">
                    Rp {{ number_format($this->getProjectedEnding(), 0, ',', '.') }}
                </p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-xs text-gray-500 dark:text-gray-400">Hari Runway (Kas Bertahan)</p>
                <p class="text-xl font-bold mt-1 @if($this->getRunwayDays() < 30) text-red-600 @elseif($this->getRunwayDays() < $days) text-amber-600 @else text-emerald-600 @endif">
                    {{ $this->getRunwayDays() }} hari
                </p>
                @if($this->getRunwayDays() < $days)
                    <p class="text-[11px] text-red-500 mt-0.5">Kas habis sebelum akhir periode</p>
                @endif
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-xs text-gray-500 dark:text-gray-400">Perubahan Bersih</p>
                <p class="text-xl font-bold mt-1 @if($this->getNetChange() >= 0) text-emerald-600 @else text-red-600 @endif">
                    {{ $this->getNetChange() >= 0 ? '+' : '-' }} Rp {{ number_format(abs($this->getNetChange()), 0, ',', '.') }}
                </p>
            </div>
        </div>

        @if(!empty($lowBalanceDays))
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4">
            <h3 class="font-semibold text-red-800 dark:text-red-300 mb-2">
                Peringatan Saldo Rendah ({{ count($lowBalanceDays) }} hari)
            </h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-2">
                @foreach(array_slice($lowBalanceDays, 0, 12) as $low)
                <div class="rounded-lg p-2 text-sm @if($low['level'] === 'critical') bg-red-100 dark:bg-red-900/40 @else bg-amber-100 dark:bg-amber-900/40 @endif">
                    <span class="font-medium">{{ \Carbon\Carbon::parse($low['date'])->format('d M') }}</span>
                    <span class="block text-xs @if($low['level'] === 'critical') text-red-600 dark:text-red-300 @else text-amber-600 dark:text-amber-300 @endif">
                        {{ $low['label'] }} · Rp {{ number_format($low['balance'], 0, ',', '.') }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Saldo Kas Kumulatif ({{ $days }} Hari)</h3>
            <canvas id="cumulativeChart" height="110"></canvas>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Arus Masuk vs Arus Keluar Harian</h3>
            <canvas id="flowChart" height="110"></canvas>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Rincian Sumber Arus Kas</h3>
                </div>
                <div class="p-4 space-y-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-emerald-600 dark:text-emerald-400 mb-2">Arus Masuk</p>
                        @php
                            $inflowTotal = array_sum($sources['inflows'] ?? []);
                            $inflowLabels = ['invoice' => 'Piutang Usaha', 'pos' => 'Penjualan POS', 'recurring' => 'Invoice Berulang', 'other' => 'Lainnya'];
                        @endphp
                        @foreach($sources['inflows'] ?? [] as $key => $value)
                        <div class="flex items-center justify-between py-1 text-sm">
                            <span class="text-gray-600 dark:text-gray-300">{{ $inflowLabels[$key] ?? $key }}</span>
                            <span class="font-medium text-gray-900 dark:text-white">Rp {{ number_format($value, 0, ',', '.') }}</span>
                        </div>
                        @endforeach
                        <div class="flex items-center justify-between py-1 text-sm border-t border-gray-100 dark:border-gray-700 mt-1">
                            <span class="font-semibold text-gray-800 dark:text-gray-100">Total</span>
                            <span class="font-semibold text-emerald-600">Rp {{ number_format($inflowTotal, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-red-600 dark:text-red-400 mb-2">Arus Keluar</p>
                        @php
                            $outflowTotal = array_sum($sources['outflows'] ?? []);
                            $outflowLabels = ['payable' => 'Utang Usaha', 'payroll' => 'Payroll', 'recurring' => 'Biaya Berulang', 'contract' => 'Kewajiban Kontrak', 'other' => 'Lainnya'];
                        @endphp
                        @foreach($sources['outflows'] ?? [] as $key => $value)
                        <div class="flex items-center justify-between py-1 text-sm">
                            <span class="text-gray-600 dark:text-gray-300">{{ $outflowLabels[$key] ?? $key }}</span>
                            <span class="font-medium text-gray-900 dark:text-white">Rp {{ number_format($value, 0, ',', '.') }}</span>
                        </div>
                        @endforeach
                        <div class="flex items-center justify-between py-1 text-sm border-t border-gray-100 dark:border-gray-700 mt-1">
                            <span class="font-semibold text-gray-800 dark:text-gray-100">Total</span>
                            <span class="font-semibold text-red-600">Rp {{ number_format($outflowTotal, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Rekomendasi</h3>
                </div>
                <div class="p-4 space-y-3">
                    @foreach($recommendations as $rec)
                    <div class="rounded-lg border p-3 @if($rec['severity'] === 'critical') border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-900/20 @elseif($rec['severity'] === 'warning') border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 @else border-indigo-200 dark:border-indigo-800 bg-indigo-50 dark:bg-indigo-900/20 @endif">
                        <p class="font-semibold text-sm @if($rec['severity'] === 'critical') text-red-800 dark:text-red-300 @elseif($rec['severity'] === 'warning') text-amber-800 dark:text-amber-300 @else text-indigo-800 dark:text-indigo-300 @endif">
                            {{ $rec['title'] }}
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">{{ $rec['detail'] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <script>
        const cumulativeCanvas = document.getElementById('cumulativeChart');
        if (cumulativeCanvas) {
            new Chart(cumulativeCanvas, {
                type: 'line',
                data: {
                    labels: @json($this->getChartLabels()),
                    datasets: [{
                        label: 'Saldo Kas Kumulatif',
                        data: @json($this->getChartCumulative()),
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99,102,241,0.12)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 0,
                    }],
                },
                options: {
                    responsive: true,
                    plugins: { legend: { position: 'top' } },
                    scales: {
                        x: { ticks: { maxTicksLimit: 15 } },
                        y: { ticks: { callback: v => 'Rp ' + (v/1000000).toFixed(1) + 'M' } },
                    },
                },
            });
        }

        const flowCanvas = document.getElementById('flowChart');
        if (flowCanvas) {
            new Chart(flowCanvas, {
                type: 'bar',
                data: {
                    labels: @json($this->getChartLabels()),
                    datasets: [
                        {
                            label: 'Arus Masuk',
                            data: @json($this->getChartInflows()),
                            backgroundColor: 'rgba(34,197,94,0.6)',
                            borderRadius: 3,
                        },
                        {
                            label: 'Arus Keluar',
                            data: @json($this->getChartOutflows()),
                            backgroundColor: 'rgba(239,68,68,0.6)',
                            borderRadius: 3,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    plugins: { legend: { position: 'top' } },
                    scales: {
                        x: { ticks: { maxTicksLimit: 15 } },
                        y: { ticks: { callback: v => 'Rp ' + (v/1000000).toFixed(1) + 'M' } },
                    },
                },
            });
        }
    </script>
</x-filament-panels::page>
