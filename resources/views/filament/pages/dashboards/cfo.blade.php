<x-filament-panels::page>
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    @endpush

    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Dashboard Keuangan</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Revenue, budget, cash flow, AR/AP, dan profitabilitas
                </p>
            </div>
            <span class="text-xs text-gray-400 bg-gray-100 dark:bg-gray-800 px-3 py-1.5 rounded-full">
                Periode: {{ now()->translatedFormat('F Y') }}
            </span>
        </div>

        {{-- Top Stats Row --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <div class="flex flex-col p-4 rounded-xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-900/20">
                <span class="text-xs font-medium text-emerald-600 dark:text-emerald-400">Revenue</span>
                <span class="text-xl font-extrabold text-emerald-900 dark:text-emerald-200 mt-1">Rp{{ number_format($revenueThisMonth / 1000000, 1) }}M</span>
                <span class="text-[11px] text-emerald-600 dark:text-emerald-400 mt-0.5">Bulan ini</span>
            </div>

            <div class="flex flex-col p-4 rounded-xl border border-indigo-200 dark:border-indigo-800 bg-indigo-50 dark:bg-indigo-900/20">
                <span class="text-xs font-medium text-indigo-600 dark:text-indigo-400">Budget Variance</span>
                <span class="text-xl font-extrabold {{ $budgetVariance >= 0 ? 'text-emerald-700' : 'text-red-700' }} dark:text-emerald-300 mt-1">Rp{{ number_format($budgetVariance / 1000000, 1) }}M</span>
                <span class="text-[11px] text-indigo-600 dark:text-indigo-400 mt-0.5">Planned: Rp{{ number_format($budgetPlanned / 1000000, 1) }}M</span>
            </div>

            <div class="flex flex-col p-4 rounded-xl border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20">
                <span class="text-xs font-medium text-blue-600 dark:text-blue-400">Net Cash Flow</span>
                <span class="text-xl font-extrabold {{ $netCashflow >= 0 ? 'text-emerald-700' : 'text-red-700' }} dark:text-emerald-300 mt-1">Rp{{ number_format(abs($netCashflow) / 1000000, 1) }}M</span>
                <span class="text-[11px] {{ $netCashflow >= 0 ? 'text-emerald-600' : 'text-red-600' }} mt-0.5">In: Rp{{ number_format($cashInflow / 1000000, 1) }}M</span>
            </div>

            <div class="flex flex-col p-4 rounded-xl border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20">
                <span class="text-xs font-medium text-amber-600 dark:text-amber-400">Total AR</span>
                <span class="text-xl font-extrabold text-amber-900 dark:text-amber-200 mt-1">Rp{{ number_format($totalAR / 1000000, 1) }}M</span>
                <span class="text-[11px] text-amber-600 dark:text-amber-400 mt-0.5">Piutang usaha</span>
            </div>

            <div class="flex flex-col p-4 rounded-xl border border-purple-200 dark:border-purple-800 bg-purple-50 dark:bg-purple-900/20">
                <span class="text-xs font-medium text-purple-600 dark:text-purple-400">Total AP</span>
                <span class="text-xl font-extrabold text-purple-900 dark:text-purple-200 mt-1">Rp{{ number_format($totalAP / 1000000, 1) }}M</span>
                <span class="text-[11px] text-purple-600 dark:text-purple-400 mt-0.5">Hutang usaha</span>
            </div>

            <div class="flex flex-col p-4 rounded-xl border border-rose-200 dark:border-rose-800 bg-rose-50 dark:bg-rose-900/20">
                <span class="text-xs font-medium text-rose-600 dark:text-rose-400">Net Profit</span>
                <span class="text-xl font-extrabold {{ $plNetProfit >= 0 ? 'text-emerald-700' : 'text-red-700' }} dark:text-emerald-300 mt-1">Rp{{ number_format($plNetProfit / 1000000, 1) }}M</span>
                <span class="text-[11px] text-rose-600 dark:text-rose-400 mt-0.5">Rev: Rp{{ number_format($plRevenue / 1000000, 1) }}M</span>
            </div>
        </div>

        {{-- Charts Row --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Expense Pie Chart --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                <h2 class="text-base font-bold text-gray-900 dark:text-white mb-4">Breakdown Pengeluaran</h2>
                <div class="h-72">
                    <canvas id="expensePieChart"></canvas>
                </div>
            </div>

            {{-- Cashflow Chart --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                <h2 class="text-base font-bold text-gray-900 dark:text-white mb-4">Cash Flow (6 Bulan)</h2>
                <div class="h-72">
                    <canvas id="cashflowChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Tables Row --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- P&L Summary --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="p-5 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">Profit & Loss Summary</h2>
                </div>
                <div class="p-5 space-y-3">
                    <div class="flex justify-between py-2 border-b border-gray-50 dark:border-gray-700/50">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Revenue</span>
                        <span class="text-sm font-bold text-emerald-600">Rp{{ number_format($plRevenue, 2, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-gray-50 dark:border-gray-700/50">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Expenses</span>
                        <span class="text-sm font-bold text-red-600">Rp{{ number_format($plExpenses, 2, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">Net Profit</span>
                        <span class="text-sm font-bold {{ $plNetProfit >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                            Rp{{ number_format($plNetProfit, 2, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Bank Balances --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="p-5 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">Saldo Rekening Bank</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-700">
                                <th class="text-left p-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Rekening</th>
                                <th class="text-right p-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Saldo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bankBalances as $bank)
                                <tr class="border-b border-gray-50 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                    <td class="p-4">
                                        <p class="font-medium text-gray-900 dark:text-white">{{ $bank['account_name'] }}</p>
                                        <p class="text-xs text-gray-500">{{ $bank['account_number'] }}</p>
                                    </td>
                                    <td class="p-4 text-right font-mono font-bold text-gray-700 dark:text-gray-300">Rp{{ number_format($bank['balance'], 2, ',', '.') }}</td>
                                </tr>
                            @endforeach
                            @if(empty($bankBalances))
                                <tr><td colspan="2" class="p-4 text-center text-gray-400">Belum ada data rekening</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Expense Table --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="p-5 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">Rincian Pengeluaran</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-700">
                                <th class="text-left p-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Kategori</th>
                                <th class="text-right p-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($expenseBreakdown as $expense)
                                <tr class="border-b border-gray-50 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                    <td class="p-4">
                                        <p class="font-medium text-gray-900 dark:text-white">{{ $expense['category'] }}</p>
                                        <p class="text-xs text-gray-500">{{ $expense['code'] }}</p>
                                    </td>
                                    <td class="p-4 text-right font-mono text-red-600">Rp{{ number_format($expense['total'], 2, ',', '.') }}</td>
                                </tr>
                            @endforeach
                            @if(empty($expenseBreakdown))
                                <tr><td colspan="2" class="p-4 text-center text-gray-400">Belum ada pengeluaran bulan ini</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Budget Progress --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="p-5 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">Budget vs Actual</h2>
                </div>
                <div class="p-5 space-y-4">
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Progress</span>
                            <span class="text-sm font-bold text-indigo-600">
                                @php $budgetPct = $budgetPlanned > 0 ? round(($budgetActual / $budgetPlanned) * 100, 1) : 0; @endphp
                                {{ $budgetPct }}%
                            </span>
                        </div>
                        <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-3">
                            <div class="bg-indigo-500 h-3 rounded-full" style="width: {{ min($budgetPct, 100) }}%"></div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 pt-2">
                        <div class="p-3 rounded-lg bg-emerald-50 dark:bg-emerald-900/20">
                            <p class="text-xs text-emerald-600 dark:text-emerald-400">Planned</p>
                            <p class="text-lg font-bold text-emerald-800 dark:text-emerald-200">Rp{{ number_format($budgetPlanned / 1000000, 1) }}M</p>
                        </div>
                        <div class="p-3 rounded-lg bg-blue-50 dark:bg-blue-900/20">
                            <p class="text-xs text-blue-600 dark:text-blue-400">Actual</p>
                            <p class="text-lg font-bold text-blue-800 dark:text-blue-200">Rp{{ number_format($budgetActual / 1000000, 1) }}M</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const isDark = document.documentElement.classList.contains('dark');
        const textColor = isDark ? '#9ca3af' : '#6b7280';
        const gridColor = isDark ? 'rgba(75,85,99,0.2)' : 'rgba(209,213,219,0.5)';

        var pieCtx = document.getElementById('expensePieChart');
        if (pieCtx) {
            var expenseLabels = @json(collect($expenseBreakdown)->map(fn($e) => $e['category'] . ' (' . $e['code'] . ')')->values()->toArray());
            var expenseData = @json(collect($expenseBreakdown)->pluck('total')->values()->toArray());
            new Chart(pieCtx, {
                type: 'doughnut',
                data: {
                    labels: expenseLabels,
                    datasets: [{
                        data: expenseData,
                        backgroundColor: [
                            'rgba(239,68,68,0.7)', 'rgba(245,158,11,0.7)', 'rgba(16,185,129,0.7)',
                            'rgba(59,130,246,0.7)', 'rgba(139,92,246,0.7)', 'rgba(236,72,153,0.7)',
                            'rgba(20,184,166,0.7)', 'rgba(251,146,60,0.7)',
                        ],
                        borderColor: isDark ? 'rgba(31,41,55,0.8)' : 'rgba(255,255,255,0.8)',
                        borderWidth: 2,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: { color: textColor, padding: 16, font: { size: 11 } }
                        }
                    }
                }
            });
        }

        var cfCtx = document.getElementById('cashflowChart');
        if (cfCtx) {
            new Chart(cfCtx, {
                type: 'bar',
                data: {
                    labels: @json($cashflowChartLabels),
                    datasets: [
                        {
                            label: 'Inflow',
                            data: @json(collect($cashflowChartData)->pluck('inflow')->values()->toArray()),
                            backgroundColor: 'rgba(16,185,129,0.7)',
                            borderColor: 'rgba(16,185,129,1)',
                            borderWidth: 1,
                            borderRadius: 6,
                        },
                        {
                            label: 'Outflow',
                            data: @json(collect($cashflowChartData)->pluck('outflow')->values()->toArray()),
                            backgroundColor: 'rgba(239,68,68,0.7)',
                            borderColor: 'rgba(239,68,68,1)',
                            borderWidth: 1,
                            borderRadius: 6,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { labels: { color: textColor } }
                    },
                    scales: {
                        x: {
                            grid: { color: gridColor },
                            ticks: { color: textColor }
                        },
                        y: {
                            grid: { color: gridColor },
                            ticks: {
                                color: textColor,
                                callback: function(v) { return 'Rp' + (v/1000000).toFixed(0) + 'M'; }
                            }
                        }
                    }
                }
            });
        }
    });
    </script>
    @endpush
</x-filament-panels::page>
