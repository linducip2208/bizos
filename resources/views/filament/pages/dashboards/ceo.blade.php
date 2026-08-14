<x-filament-panels::page>
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    @endpush

    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Dashboard Eksekutif</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Ringkasan performa bisnis, keuangan, dan operasional perusahaan
                </p>
            </div>
            <span class="text-xs text-gray-400 bg-gray-100 dark:bg-gray-800 px-3 py-1.5 rounded-full">
                Data per {{ now()->translatedFormat('d F Y') }}
            </span>
        </div>

        {{-- Top Stats Row --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            {{-- Revenue --}}
            <div class="flex flex-col p-4 rounded-xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-900/20">
                <span class="text-xs font-medium text-emerald-600 dark:text-emerald-400">Revenue Bulan Ini</span>
                <span class="text-xl font-extrabold text-emerald-900 dark:text-emerald-200 mt-1">Rp{{ number_format(($revenueCurrent['total_revenue'] ?? 0) / 1000000, 1) }}M</span>
                <span class="text-[11px] {{ $revenueGrowth >= 0 ? 'text-emerald-600' : 'text-red-600' }} mt-0.5">
                    @if($revenueGrowth >= 0)▲ @else ▼ @endif {{ abs($revenueGrowth) }}% vs bulan lalu
                </span>
            </div>

            {{-- Profit Margin --}}
            <div class="flex flex-col p-4 rounded-xl border border-indigo-200 dark:border-indigo-800 bg-indigo-50 dark:bg-indigo-900/20">
                <span class="text-xs font-medium text-indigo-600 dark:text-indigo-400">Margin Laba</span>
                <span class="text-xl font-extrabold text-indigo-900 dark:text-indigo-200 mt-1">{{ $profitMargin }}%</span>
                <span class="text-[11px] text-indigo-600 dark:text-indigo-400 mt-0.5">Bulan berjalan</span>
            </div>

            {{-- Cash Balance --}}
            <div class="flex flex-col p-4 rounded-xl border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20">
                <span class="text-xs font-medium text-blue-600 dark:text-blue-400">Saldo Kas</span>
                <span class="text-xl font-extrabold text-blue-900 dark:text-blue-200 mt-1">Rp{{ number_format($cashBalance / 1000000, 1) }}M</span>
                <span class="text-[11px] text-blue-600 dark:text-blue-400 mt-0.5">Total kas & bank</span>
            </div>

            {{-- AR Aging --}}
            <div class="flex flex-col p-4 rounded-xl border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20">
                <span class="text-xs font-medium text-amber-600 dark:text-amber-400">AR Overdue</span>
                <span class="text-xl font-extrabold text-amber-900 dark:text-amber-200 mt-1">Rp{{ number_format(($arAging['overdue'] ?? 0) / 1000000, 1) }}M</span>
                <span class="text-[11px] text-amber-600 dark:text-amber-400 mt-0.5">Total AR: Rp{{ number_format(($arAging['total_ar'] ?? 0) / 1000000, 1) }}M</span>
            </div>

            {{-- Employees --}}
            <div class="flex flex-col p-4 rounded-xl border border-purple-200 dark:border-purple-800 bg-purple-50 dark:bg-purple-900/20">
                <span class="text-xs font-medium text-purple-600 dark:text-purple-400">Karyawan Aktif</span>
                <span class="text-xl font-extrabold text-purple-900 dark:text-purple-200 mt-1">{{ number_format($employeeCount) }}</span>
                <span class="text-[11px] text-purple-600 dark:text-purple-400 mt-0.5">{{ $activeProjects }} proyek aktif</span>
            </div>

            {{-- Pipeline --}}
            <div class="flex flex-col p-4 rounded-xl border border-rose-200 dark:border-rose-800 bg-rose-50 dark:bg-rose-900/20">
                <span class="text-xs font-medium text-rose-600 dark:text-rose-400">Pipeline Sales</span>
                <span class="text-xl font-extrabold text-rose-900 dark:text-rose-200 mt-1">Rp{{ number_format($totalPipelineValue / 1000000, 1) }}M</span>
                <span class="text-[11px] text-rose-600 dark:text-rose-400 mt-0.5">Total peluang</span>
            </div>
        </div>

        {{-- Charts Row --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Revenue Trend --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                <h2 class="text-base font-bold text-gray-900 dark:text-white mb-4">Trend Pendapatan (6 Bulan)</h2>
                <div class="h-72">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            {{-- Branch Performance --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                <h2 class="text-base font-bold text-gray-900 dark:text-white mb-4">Performa Cabang</h2>
                <div class="space-y-3">
                    @foreach($branchPerformance as $branch)
                        <div class="flex items-center justify-between p-3 rounded-lg border border-gray-100 dark:border-gray-700">
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $branch['name'] }}</p>
                                <p class="text-xs text-gray-500">{{ $branch['employee_count'] }} karyawan</p>
                            </div>
                            <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400">Rp{{ number_format($branch['revenue'] / 1000000, 1) }}M</span>
                        </div>
                    @endforeach
                    @if(empty($branchPerformance))
                        <p class="text-sm text-gray-400 text-center py-6">Belum ada data cabang</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Tables Row --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Top Customers --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="p-5 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">Top 5 Pelanggan</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-700">
                                <th class="text-left p-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Pelanggan</th>
                                <th class="text-right p-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Revenue</th>
                                <th class="text-right p-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Faktur</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topCustomers as $customer)
                                <tr class="border-b border-gray-50 dark:border-gray-700/50 hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                    <td class="p-4 font-medium text-gray-900 dark:text-white">{{ $customer['name'] }}</td>
                                    <td class="p-4 text-right font-mono text-gray-700 dark:text-gray-300">Rp{{ number_format($customer['revenue'] / 1000000, 1) }}M</td>
                                    <td class="p-4 text-right text-gray-500">{{ $customer['invoice_count'] }}</td>
                                </tr>
                            @endforeach
                            @if(empty($topCustomers))
                                <tr><td colspan="3" class="p-4 text-center text-gray-400">Belum ada data</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- AR Aging Detail --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="p-5 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">Aging Piutang</h2>
                </div>
                <div class="p-5 space-y-4">
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Overdue</span>
                            <span class="text-sm font-bold text-red-600">Rp{{ number_format(($arAging['overdue'] ?? 0) / 1000000, 1) }}M</span>
                        </div>
                        <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2">
                            @php $overduePct = ($arAging['total_ar'] ?? 0) > 0 ? ($arAging['overdue'] / $arAging['total_ar']) * 100 : 0; @endphp
                            <div class="bg-red-500 h-2 rounded-full" style="width: {{ min($overduePct, 100) }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between mb-1">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Jatuh Tempo Minggu Ini</span>
                            <span class="text-sm font-bold text-amber-600">Rp{{ number_format(($arAging['due_this_week'] ?? 0) / 1000000, 1) }}M</span>
                        </div>
                        <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2">
                            @php $weekPct = ($arAging['total_ar'] ?? 0) > 0 ? ($arAging['due_this_week'] / $arAging['total_ar']) * 100 : 0; @endphp
                            <div class="bg-amber-500 h-2 rounded-full" style="width: {{ min($weekPct, 100) }}%"></div>
                        </div>
                    </div>
                    <div class="pt-3 border-t border-gray-100 dark:border-gray-700">
                        <div class="flex justify-between">
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">Total Piutang</span>
                            <span class="text-sm font-bold text-gray-900 dark:text-white">Rp{{ number_format(($arAging['total_ar'] ?? 0) / 1000000, 1) }}M</span>
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

        var ctx = document.getElementById('revenueChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: @json($revenueChartData['labels'] ?? []),
                    datasets: [{
                        label: 'Revenue (Rp)',
                        data: @json($revenueChartData['data'] ?? []),
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
