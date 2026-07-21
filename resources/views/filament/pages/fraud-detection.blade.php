<x-filament-panels::page>
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    @endpush

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Deteksi Fraud Invoice</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Pemindaian otomatis invoice dengan Benford's Law + analisis anomali AI
                </p>
            </div>
        </div>

        <div class="flex items-center gap-4">
            {{ $this->form }}
            <x-filament::button wire:click="runScan" color="primary" :loading="$isScanning">
                Scan Invoice
            </x-filament::button>
            @if(!empty($scanResult))
            <x-filament::button wire:click="generateReport" color="gray" size="sm">
                Generate Laporan AI
            </x-filament::button>
            @endif
        </div>

        @if(!empty($scanResult))
            @php
                $summary = $scanResult['summary'] ?? ['total_invoices' => 0, 'flagged' => 0, 'high_risk' => 0, 'medium_risk' => 0, 'low_risk' => 0];
            @endphp

            <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-xl border p-4">
                    <p class="text-xs text-gray-500">Total Invoice</p>
                    <p class="text-2xl font-bold">{{ $summary['total_invoices'] }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl border p-4">
                    <p class="text-xs text-red-500">Terindikasi</p>
                    <p class="text-2xl font-bold text-red-600">{{ $summary['flagged'] }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl border p-4">
                    <p class="text-xs text-red-500">Risiko Tinggi</p>
                    <p class="text-2xl font-bold text-red-600">{{ $summary['high_risk'] }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl border p-4">
                    <p class="text-xs text-yellow-500">Risiko Sedang</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $summary['medium_risk'] }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl border p-4">
                    <p class="text-xs text-gray-500">Total Flag</p>
                    <p class="text-2xl font-bold">{{ $summary['total_flags'] ?? 0 }}</p>
                </div>
            </div>

            @if($activeTab === 'report' && $fraudReport)
            <div class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 rounded-xl p-6">
                <h3 class="font-semibold text-indigo-900 dark:text-indigo-300 mb-3">Laporan AI</h3>
                <div class="prose prose-sm dark:prose-invert max-w-none text-gray-700 dark:text-gray-300">
                    {!! nl2br(e($fraudReport)) !!}
                </div>
            </div>
            @endif

            @php $flagged = $scanResult['flagged_invoices'] ?? []; @endphp
            @if(!empty($flagged))
            <div class="bg-white dark:bg-gray-800 rounded-xl border overflow-hidden">
                <div class="p-4 border-b"><h3 class="font-semibold">Invoice Terindikasi ({{ count($flagged) }})</h3></div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-700/50 text-left text-gray-500 dark:text-gray-400">
                                <th class="p-3">Invoice #</th>
                                <th class="p-3 text-right">Total</th>
                                <th class="p-3 text-center">Skor</th>
                                <th class="p-3 text-center">Level</th>
                                <th class="p-3">Flag</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($flagged as $inv)
                            <tr class="border-t">
                                <td class="p-3 font-medium">{{ $inv['invoice_number'] }}</td>
                                <td class="p-3 text-right">Rp {{ number_format($inv['total'], 0, ',', '.') }}</td>
                                <td class="p-3 text-center font-bold">{{ $inv['risk_score'] }}</td>
                                <td class="p-3 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-bold" style="background-color: {{ $this->getRiskLevelColor($inv['risk_level']) }}20; color: {{ $this->getRiskLevelColor($inv['risk_level']) }}">
                                        {{ $inv['risk_level'] }}
                                    </span>
                                </td>
                                <td class="p-3 text-xs">
                                    @foreach(array_slice($inv['flags'] ?? [], 0, 3) as $flag)
                                    <div class="text-gray-500">{{ $flag['description'] }}</div>
                                    @endforeach
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            @if(!empty($benfordResult))
            <div class="bg-white dark:bg-gray-800 rounded-xl border p-6">
                <h3 class="font-semibold mb-2">Benford's Law Analysis</h3>
                <p class="text-sm @if($benfordResult['invoice_suspicious']) text-red-600 @else text-green-600 @endif mb-4">
                    {{ $benfordResult['verdict'] }}
                </p>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <canvas id="benfordChart" height="100"></canvas>
                    <div class="text-sm space-y-1">
                        <p><strong>Invoice:</strong> {{ $benfordResult['invoice_count'] }} data, Deviasi: {{ $benfordResult['invoice_total_deviation'] }}%</p>
                        <p><strong>Pembayaran:</strong> {{ $benfordResult['payment_count'] }} data, Deviasi: {{ $benfordResult['payment_total_deviation'] }}%</p>
                    </div>
                </div>
            </div>

            <script>
                const bCtx = document.getElementById('benfordChart');
                if (bCtx) {
                    new Chart(bCtx, {
                        type: 'bar',
                        data: {
                            labels: @json($this->getBenfordChartLabels()),
                            datasets: [
                                { label: 'Aktual', data: @json($this->getBenfordActualData()), backgroundColor: '#6366f1', borderRadius: 4 },
                                { label: 'Benford Expected', data: @json($this->getBenfordExpectedData()), backgroundColor: 'rgba(99,102,241,0.2)', borderColor: '#6366f1', borderWidth: 1, borderDash: [5, 5], type: 'line', pointRadius: 0 },
                            ],
                        },
                        options: {
                            responsive: true,
                            plugins: { legend: { position: 'top' } },
                            scales: { y: { title: { display: true, text: 'Persentase %' }, ticks: { callback: v => v + '%' } } },
                        },
                    });
                }
            </script>
            @endif
        @endif
    </div>
</x-filament-panels::page>
