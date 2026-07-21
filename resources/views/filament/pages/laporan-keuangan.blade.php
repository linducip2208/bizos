<x-filament-panels::page>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

    <div class="space-y-6">
        {{-- Filter Bar --}}
        <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-800 dark:ring-white/10">
            <form method="get" class="flex flex-wrap items-end gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Dari</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sampai</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm">
                </div>
                <div>
                    <button type="submit"
                        class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        Filter
                    </button>
                </div>
                <div>
                    <button type="button"
                        onclick="window.print()"
                        class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Export PDF
                    </button>
                </div>
            </form>
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="fi-section rounded-xl bg-emerald-600 p-5 text-white shadow-sm">
                <p class="text-sm font-medium text-emerald-100">Total Pendapatan</p>
                <p class="mt-2 text-2xl font-extrabold">Rp {{ number_format($cards['total_pendapatan'] ?? 0, 0, ',', '.') }}</p>
            </div>
            <div class="fi-section rounded-xl bg-rose-600 p-5 text-white shadow-sm">
                <p class="text-sm font-medium text-rose-100">Total Beban</p>
                <p class="mt-2 text-2xl font-extrabold">Rp {{ number_format($cards['total_beban'] ?? 0, 0, ',', '.') }}</p>
            </div>
            <div class="fi-section rounded-xl p-5 text-white shadow-sm {{ ($cards['laba_rugi'] ?? 0) >= 0 ? 'bg-indigo-600' : 'bg-red-600' }}">
                <p class="text-sm font-medium text-indigo-100 dark:text-red-100">{{ ($cards['laba_rugi'] ?? 0) >= 0 ? 'Laba' : 'Rugi' }}</p>
                <p class="mt-2 text-2xl font-extrabold">Rp {{ number_format(abs($cards['laba_rugi'] ?? 0), 0, ',', '.') }}</p>
            </div>
            <div class="fi-section rounded-xl p-5 text-white shadow-sm {{ ($cards['margin'] ?? 0) >= 0 ? 'bg-sky-600' : 'bg-red-600' }}">
                <p class="text-sm font-medium text-sky-100 dark:text-red-100">Margin</p>
                <p class="mt-2 text-2xl font-extrabold">{{ number_format($cards['margin'] ?? 0, 1) }}%</p>
            </div>
        </div>

        {{-- Charts Row --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-800 dark:ring-white/10 lg:col-span-2">
                <h3 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">Revenue vs Expense</h3>
                <div class="relative" style="height: 350px;">
                    <canvas id="revenueExpenseChart"></canvas>
                </div>
            </div>
            <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-800 dark:ring-white/10">
                <h3 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">Beban per Kategori</h3>
                <div class="relative" style="height: 350px;">
                    <canvas id="expenseCategoryChart"></canvas>
                </div>
            </div>
        </div>

        {{-- P&L Summary Table --}}
        <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-800 dark:ring-white/10">
            <h3 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">Ringkasan Laba Rugi</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    @foreach ($pnlSummary as $row)
                        @if ($row['type'] === 'header')
                            <tr>
                                <td colspan="2" class="pt-4 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ $row['label'] }}</td>
                            </tr>
                        @elseif ($row['type'] === 'subtotal')
                            <tr class="border-t border-gray-300 dark:border-gray-600">
                                <td class="pl-6 pt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ $row['label'] }}</td>
                                <td class="pt-2 text-right text-sm font-semibold text-gray-900 dark:text-white">Rp {{ number_format($row['amount'], 0, ',', '.') }}</td>
                            </tr>
                        @elseif ($row['type'] === 'total')
                            <tr class="border-t-2 border-gray-400 dark:border-gray-500 bg-gray-50 dark:bg-gray-700/30">
                                <td class="py-2 pl-6 text-sm font-extrabold {{ $row['amount'] >= 0 ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-700 dark:text-rose-400' }}">{{ $row['label'] }}</td>
                                <td class="py-2 text-right text-sm font-extrabold {{ $row['amount'] >= 0 ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-700 dark:text-rose-400' }}">Rp {{ number_format($row['amount'], 0, ',', '.') }}</td>
                            </tr>
                        @else
                            <tr>
                                <td class="py-1 pl-6 text-sm text-gray-600 dark:text-gray-400">{{ $row['label'] }}</td>
                                <td class="py-1 text-right text-sm text-gray-700 dark:text-gray-300">Rp {{ number_format($row['amount'], 0, ',', '.') }}</td>
                            </tr>
                        @endif
                    @endforeach
                </table>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const isDark = document.documentElement.classList.contains('dark');
            const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
            const textColor = isDark ? '#9ca3af' : '#6b7280';

            var ctx1 = document.getElementById('revenueExpenseChart').getContext('2d');
            new Chart(ctx1, {
                type: 'bar',
                data: {
                    labels: @json($revenueLabels),
                    datasets: [
                        {
                            label: 'Pendapatan',
                            data: @json($revenueData),
                            backgroundColor: 'rgba(16, 185, 129, 0.8)',
                            borderColor: 'rgba(16, 185, 129, 1)',
                            borderWidth: 1,
                            borderRadius: 6,
                        },
                        {
                            label: 'Beban',
                            data: @json($expenseData),
                            backgroundColor: 'rgba(239, 68, 68, 0.8)',
                            borderColor: 'rgba(239, 68, 68, 1)',
                            borderWidth: 1,
                            borderRadius: 6,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            labels: { color: textColor, usePointStyle: true, pointStyleWidth: 12 }
                        }
                    },
                    scales: {
                        x: { grid: { color: gridColor }, ticks: { color: textColor } },
                        y: {
                            grid: { color: gridColor },
                            ticks: {
                                color: textColor,
                                callback: function(v) { return 'Rp ' + v.toLocaleString('id-ID'); }
                            }
                        }
                    }
                }
            });

            var ctx2 = document.getElementById('expenseCategoryChart').getContext('2d');
            new Chart(ctx2, {
                type: 'doughnut',
                data: {
                    labels: @json($expenseCategoryLabels),
                    datasets: [{
                        data: @json($expenseCategoryData),
                        backgroundColor: [
                            'rgba(239, 68, 68, 0.8)',
                            'rgba(245, 158, 11, 0.8)',
                            'rgba(99, 102, 241, 0.8)',
                            'rgba(139, 92, 246, 0.8)',
                            'rgba(6, 182, 212, 0.8)',
                            'rgba(236, 72, 153, 0.8)',
                            'rgba(168, 85, 247, 0.8)',
                            'rgba(34, 197, 94, 0.8)',
                        ],
                        borderWidth: 2,
                        borderColor: isDark ? '#1f2937' : '#ffffff',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { color: textColor, padding: 16, usePointStyle: true }
                        }
                    }
                }
            });
        });
    </script>
</x-filament-panels::page>
