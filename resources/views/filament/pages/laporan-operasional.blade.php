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
            </form>
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="fi-section rounded-xl bg-indigo-600 p-5 text-white shadow-sm">
                <p class="text-sm font-medium text-indigo-100">Kehadiran Rate</p>
                <p class="mt-2 text-2xl font-extrabold">{{ $cards['kehadiran_rate'] ?? 0 }}%</p>
            </div>
            <div class="fi-section rounded-xl bg-amber-600 p-5 text-white shadow-sm">
                <p class="text-sm font-medium text-amber-100">Rata2 Lembur/jam</p>
                <p class="mt-2 text-2xl font-extrabold">{{ $cards['rata_overtime'] ?? 0 }} jam</p>
            </div>
            <div class="fi-section rounded-xl bg-purple-600 p-5 text-white shadow-sm">
                <p class="text-sm font-medium text-purple-100">Cuti Terpakai</p>
                <p class="mt-2 text-2xl font-extrabold">{{ number_format($cards['cuti_terpakai'] ?? 0, 0, ',', '.') }} hari</p>
            </div>
            <div class="fi-section rounded-xl bg-emerald-600 p-5 text-white shadow-sm">
                <p class="text-sm font-medium text-emerald-100">Project Completion</p>
                <p class="mt-2 text-2xl font-extrabold">{{ $cards['completion_rate'] ?? 0 }}%</p>
            </div>
        </div>

        {{-- NLG Summary Card --}}
        @if($nlgSummary)
        <div class="fi-section rounded-xl bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-950/30 dark:to-purple-950/30 border border-indigo-200 dark:border-indigo-800 p-5 shadow-sm">
            <div class="flex items-center gap-2 mb-3">
                <span class="text-lg">&#10024;</span>
                <h3 class="text-sm font-bold text-indigo-800 dark:text-indigo-300">Ringkasan AI</h3>
            </div>
            <div class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap leading-relaxed">{!! nl2br(e($nlgSummary)) !!}</div>
        </div>
        @endif

        {{-- Charts Row --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-800 dark:ring-white/10 lg:col-span-2">
                <h3 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">Tren Kehadiran</h3>
                <div class="relative" style="height: 350px;">
                    <canvas id="attendanceChart"></canvas>
                </div>
            </div>
            <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-800 dark:ring-white/10">
                <h3 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">Status Project</h3>
                <div class="relative" style="height: 350px;">
                    <canvas id="projectStatusChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Top Performers Table --}}
        <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-800 dark:ring-white/10">
            <h3 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">Top Performers</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700/50">
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">#</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Nama</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Department</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Jam</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($topPerformers as $i => $p)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $i + 1 }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">{{ $p['name'] }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $p['department'] }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($p['total_jam'], 1) }} jam</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">Tidak ada data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const isDark = document.documentElement.classList.contains('dark');
            const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
            const textColor = isDark ? '#9ca3af' : '#6b7280';

            var ctx1 = document.getElementById('attendanceChart').getContext('2d');
            new Chart(ctx1, {
                type: 'line',
                data: {
                    labels: @json($attendanceLabels),
                    datasets: [{
                        label: 'Kehadiran',
                        data: @json($attendanceData),
                        borderColor: 'rgba(99, 102, 241, 1)',
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 2,
                        pointHoverRadius: 6,
                        borderWidth: 2,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { color: gridColor }, ticks: { color: textColor, maxTicksLimit: 15 } },
                        y: {
                            grid: { color: gridColor },
                            ticks: { color: textColor },
                            beginAtZero: true
                        }
                    }
                }
            });

            var ctx2 = document.getElementById('projectStatusChart').getContext('2d');
            new Chart(ctx2, {
                type: 'doughnut',
                data: {
                    labels: @json($projectStatusLabels),
                    datasets: [{
                        data: @json($projectStatusData),
                        backgroundColor: [
                            'rgba(16, 185, 129, 0.8)',
                            'rgba(99, 102, 241, 0.8)',
                            'rgba(245, 158, 11, 0.8)',
                            'rgba(239, 68, 68, 0.8)',
                            'rgba(139, 92, 246, 0.8)',
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
