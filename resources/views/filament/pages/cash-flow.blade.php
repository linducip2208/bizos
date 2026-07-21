<x-filament-panels::page>
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    @endpush

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Forecast Cash Flow AI</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Proyeksi posisi kas dengan simulasi skenario what-if
                </p>
            </div>
            <x-filament::button wire:click="loadForecast" color="gray" size="sm" outlined>
                Refresh Baseline
            </x-filament::button>
        </div>

        {{ $this->form }}

        @if(!empty($forecast))
            @php $stats = $this->getStats(); @endphp
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-xl border p-4">
                    <p class="text-xs text-gray-500">Saldo Saat Ini</p>
                    <p class="text-xl font-bold">Rp {{ number_format($stats['current'], 0, ',', '.') }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl border p-4">
                    <p class="text-xs text-gray-500">Saldo Akhir</p>
                    <p class="text-xl font-bold @if($stats['end'] < 0) text-red-600 @elseif($stats['end'] < $stats['current']) text-yellow-600 @else text-green-600 @endif">
                        Rp {{ number_format($stats['end'], 0, ',', '.') }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl border p-4">
                    <p class="text-xs text-gray-500">Saldo Terendah</p>
                    <p class="text-xl font-bold @if($stats['min'] < 0) text-red-600 @endif">Rp {{ number_format($stats['min'], 0, ',', '.') }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl border p-4">
                    <p class="text-xs text-gray-500">Saldo Tertinggi</p>
                    <p class="text-xl font-bold text-green-600">Rp {{ number_format($stats['max'], 0, ',', '.') }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl border p-4">
                    <p class="text-xs text-gray-500 text-red-500">Hari Kritis</p>
                    <p class="text-xl font-bold text-red-600">{{ $stats['critical_days'] }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl border p-4">
                    <p class="text-xs text-gray-500 text-yellow-600">Hari Warning</p>
                    <p class="text-xl font-bold text-yellow-600">{{ $stats['warning_days'] }}</p>
                </div>
            </div>

            @if(!empty($alerts))
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4">
                <h3 class="font-semibold text-red-800 dark:text-red-300 mb-2">
                    Peringatan Kekurangan Kas ({{ count($alerts) }} hari)
                </h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                    @foreach(array_slice($alerts, 0, 8) as $alert)
                    <div class="bg-red-100 dark:bg-red-900/40 rounded-lg p-2 text-sm">
                        <span class="font-medium">{{ \Carbon\Carbon::parse($alert['date'])->format('d M') }}</span>
                        <span class="text-red-600 block text-xs">
                            Rp {{ number_format($alert['closing_balance'], 0, ',', '.') }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="bg-white dark:bg-gray-800 rounded-xl border p-6">
                <h3 class="font-semibold mb-4">
                    {{ $mode === 'scenario' ? 'Proyeksi Skenario' : 'Proyeksi Kas 30 Hari' }}
                </h3>
                <canvas id="cashFlowChart" height="120"></canvas>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl border p-6">
                <h3 class="font-semibold mb-4">Simulasi What-If</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <x-filament::input.wrapper>
                        <x-filament::input type="number" wire:model="data.delay_receivables" placeholder="Tunda piutang (hari)" min="0" />
                    </x-filament::input.wrapper>
                    <x-filament::input.wrapper>
                        <x-filament::input type="number" wire:model="data.additional_expense" placeholder="Biaya tambahan (Rp)" min="0" />
                    </x-filament::input.wrapper>
                    <x-filament::input.wrapper>
                        <x-filament::input type="date" wire:model="data.expense_date" />
                    </x-filament::input.wrapper>
                    <x-filament::input.wrapper>
                        <x-filament::input type="number" wire:model="data.additional_income" placeholder="Pemasukan tambahan (Rp)" min="0" />
                    </x-filament::input.wrapper>
                    <x-filament::input.wrapper>
                        <x-filament::input type="date" wire:model="data.income_date" />
                    </x-filament::input.wrapper>
                </div>
                <x-filament::button wire:click="runScenario" color="warning">
                    Jalankan Simulasi
                </x-filament::button>
            </div>

            <script>
                const cfCtx = document.getElementById('cashFlowChart');
                if (cfCtx) {
                    new Chart(cfCtx, {
                        type: 'line',
                        data: {
                            labels: @json($this->getChartLabels()),
                            datasets: [
                                {
                                    label: 'Saldo Proyeksi',
                                    data: @json($this->getChartBalance()),
                                    borderColor: '#6366f1',
                                    backgroundColor: 'rgba(99,102,241,0.1)',
                                    fill: true,
                                    tension: 0.3,
                                },
                                {
                                    label: 'Inflow',
                                    data: @json($this->getChartInflow()),
                                    borderColor: '#22c55e',
                                    borderDash: [3, 3],
                                    pointRadius: 0,
                                    fill: false,
                                },
                                {
                                    label: 'Outflow',
                                    data: @json($this->getChartOutflow()),
                                    borderColor: '#ef4444',
                                    borderDash: [3, 3],
                                    pointRadius: 0,
                                    fill: false,
                                },
                            ],
                        },
                        options: {
                            responsive: true,
                            plugins: { legend: { position: 'top' } },
                            scales: {
                                y: { ticks: { callback: v => 'Rp ' + (v/1000000).toFixed(1) + 'M' } },
                            },
                        },
                    });
                }
            </script>

            {{-- Liquidity Ratios --}}
            @if(!empty($liquidityRatios))
            <div class="bg-white dark:bg-gray-800 rounded-xl border">
                <div class="p-4 border-b">
                    <h3 class="font-semibold">Rasio Likuiditas</h3>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                        <div class="text-center p-3 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg">
                            <p class="text-xs text-gray-500">Current Ratio</p>
                            <p class="text-xl font-bold">{{ $liquidityRatios['current_ratio_formatted'] ?? 'N/A' }}</p>
                            <p class="text-xs text-gray-400">{{ $liquidityRatios['current_ratio_interpretation'] ?? '' }}</p>
                        </div>
                        <div class="text-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                            <p class="text-xs text-gray-500">Quick Ratio</p>
                            <p class="text-xl font-bold">{{ $liquidityRatios['quick_ratio_formatted'] ?? 'N/A' }}</p>
                            <p class="text-xs text-gray-400">{{ $liquidityRatios['quick_ratio_interpretation'] ?? '' }}</p>
                        </div>
                        <div class="text-center p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                            <p class="text-xs text-gray-500">Cash Ratio</p>
                            <p class="text-xl font-bold">{{ $liquidityRatios['cash_ratio_formatted'] ?? 'N/A' }}</p>
                        </div>
                        <div class="text-center p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                            <p class="text-xs text-gray-500">Modal Kerja</p>
                            <p class="text-lg font-bold">{{ $liquidityRatios['working_capital_formatted'] ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="text-center p-2 bg-gray-50 dark:bg-gray-900/20 rounded-lg text-sm">
                            <span class="text-gray-500">Debt to Equity:</span>
                            <span class="font-bold">{{ $liquidityRatios['debt_to_equity_formatted'] ?? 'N/A' }}</span>
                        </div>
                        <div class="text-center p-2 bg-gray-50 dark:bg-gray-900/20 rounded-lg text-sm">
                            <span class="text-gray-500">Debt to Assets:</span>
                            <span class="font-bold">{{ $liquidityRatios['debt_to_assets_formatted'] ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Treasury Daily Cash Position --}}
            @if(!empty($treasuryDailyPositions['daily_positions']))
            <div class="bg-white dark:bg-gray-800 rounded-xl border">
                <div class="p-4 border-b">
                    <h3 class="font-semibold">Posisi Kas Harian (Treasury)</h3>
                </div>
                <div class="p-4">
                    <p class="text-sm text-gray-500 mb-3">
                        Saldo awal: {{ $treasuryDailyPositions['opening_balance_formatted'] ?? 'Rp 0' }}
                        &middot; Hari alert: {{ $treasuryDailyPositions['alert_days'] ?? 0 }}
                        &middot; Saldo terendah: Rp {{ number_format($treasuryDailyPositions['lowest_balance'] ?? 0, 0, ',', '.') }}
                    </p>
                    <canvas id="treasuryDailyChart" height="100"></canvas>
                </div>
            </div>

            <script>
                const tdCtx = document.getElementById('treasuryDailyChart');
                if (tdCtx) {
                    const positions = @json($treasuryDailyPositions['daily_positions'] ?? []);
                    new Chart(tdCtx, {
                        type: 'bar',
                        data: {
                            labels: positions.map(p => p.date),
                            datasets: [
                                {
                                    label: 'Inflow',
                                    data: positions.map(p => p.inflows),
                                    backgroundColor: 'rgba(34,197,94,0.6)',
                                    borderRadius: 4,
                                },
                                {
                                    label: 'Outflow',
                                    data: positions.map(p => p.outflows),
                                    backgroundColor: 'rgba(239,68,68,0.6)',
                                    borderRadius: 4,
                                },
                                {
                                    label: 'Saldo Akhir',
                                    data: positions.map(p => p.closing_balance),
                                    type: 'line',
                                    borderColor: '#6366f1',
                                    backgroundColor: 'transparent',
                                    tension: 0.3,
                                    pointRadius: 0,
                                },
                            ],
                        },
                        options: {
                            responsive: true,
                            plugins: { legend: { position: 'top' } },
                            scales: {
                                y: { ticks: { callback: v => 'Rp ' + (v/1000000).toFixed(1) + 'M' } },
                            },
                        },
                    });
                }
            </script>
            @endif
        @endif
    </div>
</x-filament-panels::page>
