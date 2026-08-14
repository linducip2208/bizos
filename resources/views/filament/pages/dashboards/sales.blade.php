<x-filament-panels::page>
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    @endpush

    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Dashboard Penjualan</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Pipeline, deal, revenue, dan performa tim sales — {{ now()->translatedFormat('F Y') }}
                </p>
            </div>
            <span class="text-xs text-gray-400 bg-gray-100 dark:bg-gray-800 px-3 py-1.5 rounded-full">
                Deals aktif: {{ $activeDeals }} &middot; Leads baru: {{ $newLeadsThisMonth }}
            </span>
        </div>

        {{-- Top Stats Row --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            {{-- Pipeline Value --}}
            <div class="flex flex-col p-4 rounded-xl border border-indigo-200 dark:border-indigo-800 bg-indigo-50 dark:bg-indigo-900/20">
                <span class="text-xs font-medium text-indigo-600 dark:text-indigo-400">Pipeline Value</span>
                <span class="text-xl font-extrabold text-indigo-900 dark:text-indigo-200 mt-1">Rp{{ number_format($pipelineValue / 1000000, 1) }}M</span>
                <span class="text-[11px] text-indigo-600 dark:text-indigo-400 mt-0.5">{{ $pipelineCount }} deals</span>
            </div>

            {{-- Deals Won --}}
            <div class="flex flex-col p-4 rounded-xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-900/20">
                <span class="text-xs font-medium text-emerald-600 dark:text-emerald-400">Deals Won</span>
                <span class="text-xl font-extrabold text-emerald-900 dark:text-emerald-200 mt-1">{{ $dealsWonCount }}</span>
                <span class="text-[11px] text-emerald-600 dark:text-emerald-400 mt-0.5">Rp{{ number_format($dealsWonThisMonth / 1000000, 1) }}M</span>
            </div>

            {{-- Conversion Rate --}}
            <div class="flex flex-col p-4 rounded-xl border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20">
                <span class="text-xs font-medium text-blue-600 dark:text-blue-400">Conversion Rate</span>
                <span class="text-xl font-extrabold text-blue-900 dark:text-blue-200 mt-1">{{ $conversionRate }}%</span>
                <span class="text-[11px] text-blue-600 dark:text-blue-400 mt-0.5">Lead → Won</span>
            </div>

            {{-- Revenue --}}
            <div class="flex flex-col p-4 rounded-xl border border-purple-200 dark:border-purple-800 bg-purple-50 dark:bg-purple-900/20">
                <span class="text-xs font-medium text-purple-600 dark:text-purple-400">Revenue</span>
                <span class="text-xl font-extrabold text-purple-900 dark:text-purple-200 mt-1">Rp{{ number_format($revenueThisMonth / 1000000, 1) }}M</span>
                <span class="text-[11px] text-purple-600 dark:text-purple-400 mt-0.5">Bulan ini</span>
            </div>

            {{-- Revenue Target --}}
            <div class="flex flex-col p-4 rounded-xl border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20">
                <span class="text-xs font-medium text-amber-600 dark:text-amber-400">Target Revenue</span>
                <span class="text-xl font-extrabold text-amber-900 dark:text-amber-200 mt-1">Rp{{ number_format($revenueTarget / 1000000, 1) }}M</span>
                <span class="text-[11px] {{ $revenueProgress >= 80 ? 'text-emerald-600' : ($revenueProgress >= 50 ? 'text-amber-600' : 'text-red-600') }} mt-0.5">{{ $revenueProgress }}% tercapai</span>
            </div>

            {{-- New Leads --}}
            <div class="flex flex-col p-4 rounded-xl border border-rose-200 dark:border-rose-800 bg-rose-50 dark:bg-rose-900/20">
                <span class="text-xs font-medium text-rose-600 dark:text-rose-400">Leads Baru</span>
                <span class="text-xl font-extrabold text-rose-900 dark:text-rose-200 mt-1">{{ $newLeadsThisMonth }}</span>
                <span class="text-[11px] text-rose-600 dark:text-rose-400 mt-0.5">Bulan ini</span>
            </div>
        </div>

        {{-- Revenue Progress Bar --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-base font-bold text-gray-900 dark:text-white">Progress Revenue vs Target</h2>
                <span class="text-sm font-bold {{ $revenueProgress >= 80 ? 'text-emerald-600' : ($revenueProgress >= 50 ? 'text-amber-600' : 'text-red-600') }}">{{ $revenueProgress }}%</span>
            </div>
            <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-4">
                <div class="h-4 rounded-full bg-gradient-to-r {{ $revenueProgress >= 80 ? 'from-emerald-500 to-emerald-600' : ($revenueProgress >= 50 ? 'from-amber-500 to-amber-600' : 'from-red-500 to-red-600') }}" style="width: {{ min($revenueProgress, 100) }}%"></div>
            </div>
            <div class="flex justify-between mt-2 text-xs text-gray-500">
                <span>Tercapai: Rp{{ number_format($revenueThisMonth / 1000000, 1) }}M</span>
                <span>Target: Rp{{ number_format($revenueTarget / 1000000, 1) }}M</span>
            </div>
        </div>

        {{-- Charts Row --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Pipeline Trend --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                <h2 class="text-base font-bold text-gray-900 dark:text-white mb-4">Pipeline Value (6 Bulan)</h2>
                <div class="h-72">
                    <canvas id="pipelineChart"></canvas>
                </div>
            </div>

            {{-- Revenue Trend --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                <h2 class="text-base font-bold text-gray-900 dark:text-white mb-4">Revenue (6 Bulan)</h2>
                <div class="h-72">
                    <canvas id="salesRevenueChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Tables Row --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Top Salespeople --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="p-5 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">Top Salesperson</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-700">
                                <th class="text-left p-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama</th>
                                <th class="text-center p-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Deals Won</th>
                                <th class="text-right p-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topSalespeople as $sp)
                                <tr class="border-b border-gray-50 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                    <td class="p-4">
                                        <p class="font-medium text-gray-900 dark:text-white">{{ $sp['name'] }}</p>
                                        <p class="text-xs text-gray-500">{{ $sp['position'] }}</p>
                                    </td>
                                    <td class="p-4 text-center">
                                        <span class="inline-flex items-center justify-center w-8 h-8 text-xs font-bold rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400">{{ $sp['won_deals'] }}</span>
                                    </td>
                                    <td class="p-4 text-right font-mono font-bold text-emerald-600">Rp{{ number_format($sp['won_value'] / 1000000, 1) }}M</td>
                                </tr>
                            @endforeach
                            @if(empty($topSalespeople))
                                <tr><td colspan="3" class="p-4 text-center text-gray-400">Belum ada deals won</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Recent Won Deals --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="p-5 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">Deals Won Terbaru</h2>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-700 max-h-80 overflow-y-auto">
                    @foreach($recentWonDeals as $deal)
                        <a href="{{ url('/admin/deals/' . $deal['id']) }}"
                           class="flex items-center gap-3 p-3 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <div class="w-8 h-8 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center flex-shrink-0">
                                <x-heroicon-o-trophy class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $deal['title'] }}</p>
                                <p class="text-xs text-gray-500">{{ $deal['client_name'] }} &middot; {{ $deal['salesperson'] ?? 'N/A' }}</p>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="text-sm font-bold text-emerald-600">Rp{{ number_format($deal['value'] / 1000000, 1) }}M</p>
                                <p class="text-[10px] text-gray-400">{{ $deal['closed_date'] }}</p>
                            </div>
                        </a>
                    @endforeach
                    @if(empty($recentWonDeals))
                        <p class="p-4 text-sm text-gray-400 text-center">Belum ada deals won</p>
                    @endif
                </div>
            </div>

            {{-- Pipeline Stages --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="p-5 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">Pipeline per Stage</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-700">
                                <th class="text-left p-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Stage</th>
                                <th class="text-center p-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Deals</th>
                                <th class="text-right p-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pipelineStages as $stage)
                                <tr class="border-b border-gray-50 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                    <td class="p-4">
                                        <div class="flex items-center gap-2">
                                            <div class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $stage['color'] ?? '#6366f1' }}"></div>
                                            <span class="font-medium text-gray-900 dark:text-white">{{ $stage['name'] }}</span>
                                        </div>
                                    </td>
                                    <td class="p-4 text-center font-bold text-gray-700 dark:text-gray-300">{{ $stage['deals_count'] }}</td>
                                    <td class="p-4 text-right font-mono text-gray-700 dark:text-gray-300">Rp{{ number_format(($stage['deals_value'] ?? 0) / 1000000, 1) }}M</td>
                                </tr>
                            @endforeach
                            @if(empty($pipelineStages))
                                <tr><td colspan="3" class="p-4 text-center text-gray-400">Belum ada pipeline stage</td></tr>
                            @endif
                        </tbody>
                    </table>
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

        var pipelineCtx = document.getElementById('pipelineChart');
        if (pipelineCtx) {
            new Chart(pipelineCtx, {
                type: 'bar',
                data: {
                    labels: @json($pipelineChartLabels),
                    datasets: [{
                        label: 'Pipeline Value',
                        data: @json($pipelineChartData),
                        backgroundColor: 'rgba(99,102,241,0.7)',
                        borderColor: 'rgba(99,102,241,1)',
                        borderWidth: 1,
                        borderRadius: 6,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { labels: { color: textColor } }
                    },
                    scales: {
                        x: { grid: { color: gridColor }, ticks: { color: textColor } },
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

        var revCtx = document.getElementById('salesRevenueChart');
        if (revCtx) {
            new Chart(revCtx, {
                type: 'line',
                data: {
                    labels: @json($revenueChartLabels),
                    datasets: [{
                        label: 'Revenue',
                        data: @json($revenueChartData),
                        borderColor: 'rgba(16,185,129,1)',
                        backgroundColor: 'rgba(16,185,129,0.1)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: 'rgba(16,185,129,1)',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { labels: { color: textColor } }
                    },
                    scales: {
                        x: { grid: { color: gridColor }, ticks: { color: textColor } },
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
