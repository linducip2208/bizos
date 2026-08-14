<x-filament-panels::page>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

    <div class="space-y-6">
        {{-- Filter Bar --}}
        <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-800 dark:ring-white/10">
            <form method="get" class="flex flex-wrap items-end gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Filter Grade</label>
                    <select name="grade"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm"
                        onchange="this.form.submit()">
                        <option value="">Semua Grade</option>
                        <option value="A" {{ $gradeFilter === 'A' ? 'selected' : '' }}>Grade A</option>
                        <option value="B" {{ $gradeFilter === 'B' ? 'selected' : '' }}>Grade B</option>
                        <option value="C" {{ $gradeFilter === 'C' ? 'selected' : '' }}>Grade C</option>
                        <option value="D" {{ $gradeFilter === 'D' ? 'selected' : '' }}>Grade D</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Urutkan</label>
                    <select name="sort"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm"
                        onchange="this.form.submit()">
                        <option value="score_desc" {{ $sortBy === 'score_desc' ? 'selected' : '' }}>Skor Tertinggi</option>
                        <option value="score_asc" {{ $sortBy === 'score_asc' ? 'selected' : '' }}>Skor Terendah</option>
                        <option value="total_orders_desc" {{ $sortBy === 'total_orders_desc' ? 'selected' : '' }}>Jumlah Pesanan</option>
                        <option value="total_value_desc" {{ $sortBy === 'total_value_desc' ? 'selected' : '' }}>Nilai Pembelian</option>
                        <option value="name_asc" {{ $sortBy === 'name_asc' ? 'selected' : '' }}>Nama (A-Z)</option>
                    </select>
                </div>
                <div>
                    <button type="submit"
                        class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        Terapkan
                    </button>
                </div>
            </form>
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="fi-section rounded-xl bg-indigo-600 p-5 text-white shadow-sm">
                <p class="text-sm font-medium text-indigo-100">Total Vendor Aktif</p>
                <p class="mt-2 text-2xl font-extrabold">{{ number_format($summary['total_suppliers'] ?? 0, 0, ',', '.') }}</p>
            </div>
            <div class="fi-section rounded-xl bg-emerald-600 p-5 text-white shadow-sm">
                <p class="text-sm font-medium text-emerald-100">Rata-rata Skor</p>
                <p class="mt-2 text-2xl font-extrabold">{{ number_format($summary['avg_score'] ?? 0, 1, ',', '.') }}</p>
            </div>
            <div class="fi-section rounded-xl bg-amber-600 p-5 text-white shadow-sm">
                <p class="text-sm font-medium text-amber-100">Vendor Terbaik</p>
                <p class="mt-2 text-xl font-extrabold leading-tight">{{ $summary['top_performer']['supplier_name'] ?? '—' }}</p>
                <p class="mt-1 text-sm text-amber-100">
                    @if(isset($summary['top_performer']))
                        Skor {{ number_format($summary['top_performer']['overall_score'], 1, ',', '.') }} · Grade {{ $summary['top_performer']['grade'] }}
                    @else
                        Belum ada data
                    @endif
                </p>
            </div>
            <div class="fi-section rounded-xl bg-red-600 p-5 text-white shadow-sm">
                <p class="text-sm font-medium text-red-100">Vendor Terburuk</p>
                <p class="mt-2 text-xl font-extrabold leading-tight">{{ $summary['worst_performer']['supplier_name'] ?? '—' }}</p>
                <p class="mt-1 text-sm text-red-100">
                    @if(isset($summary['worst_performer']))
                        Skor {{ number_format($summary['worst_performer']['overall_score'], 1, ',', '.') }} · Grade {{ $summary['worst_performer']['grade'] }}
                    @else
                        Belum ada data
                    @endif
                </p>
            </div>
        </div>

        {{-- Radar Chart --}}
        @if(!empty($radar))
        <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-800 dark:ring-white/10">
            <h3 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">
                Dimensi Performa Vendor Terbaik — {{ $radar['supplier_name'] }}
            </h3>
            <div class="relative mx-auto" style="height: 360px; max-width: 560px;">
                <canvas id="radarChart"></canvas>
            </div>
        </div>
        @endif

        {{-- Ranking Table --}}
        <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-800 dark:ring-white/10"
            x-data="{ open: null }">
            <h3 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">Peringkat Vendor</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700/50">
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">#</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Vendor</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Grade</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Skor</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Pesanan</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Nilai</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Detail</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($scorecards as $index => $card)
                            @php
                                $grade = $card['grade'] ?? 'D';
                                $gradeClasses = match ($grade) {
                                    'A' => 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300',
                                    'B' => 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300',
                                    'C' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300',
                                    default => 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300',
                                };
                            @endphp
                            <tr class="cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/30" @click="open = open === {{ $card['supplier_id'] }} ? null : {{ $card['supplier_id'] }}">
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $index + 1 }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm">
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $card['supplier_name'] }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $card['supplier_code'] }}</div>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-center text-sm">
                                    <span class="inline-flex rounded-md px-2 py-0.5 text-xs font-bold {{ $gradeClasses }}">{{ $grade }}</span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-center text-sm">
                                    <div class="font-semibold text-gray-900 dark:text-white">{{ number_format($card['overall_score'], 1, ',', '.') }}</div>
                                    <div class="mt-1 h-1.5 w-full min-w-[80px] overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                        <div class="h-full rounded-full bg-indigo-500" style="width: {{ min(100, $card['overall_score']) }}%"></div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-700 dark:text-gray-300">{{ number_format($card['total_orders'] ?? 0, 0, ',', '.') }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-700 dark:text-gray-300">Rp {{ number_format($card['total_value'] ?? 0, 0, ',', '.') }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-center text-sm text-gray-500 dark:text-gray-400">
                                    <svg class="mx-auto h-4 w-4 transition-transform" :class="{ 'rotate-180': open === {{ $card['supplier_id'] }} }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </td>
                            </tr>
                            <tr x-show="open === {{ $card['supplier_id'] }}" x-cloak>
                                <td colspan="7" class="bg-gray-50 px-6 py-4 dark:bg-gray-700/30">
                                    @if(!$card['has_data'])
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada data performa untuk vendor ini.</p>
                                    @else
                                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                            <div class="rounded-lg bg-white p-4 ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700">
                                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Pengiriman Tepat Waktu (40%)</p>
                                                <p class="mt-1 text-xl font-bold text-gray-900 dark:text-white">{{ number_format($card['on_time_delivery'], 1, ',', '.') }}%</p>
                                                <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                                    <div class="h-full rounded-full bg-indigo-500" style="width: {{ min(100, $card['on_time_delivery']) }}%"></div>
                                                </div>
                                            </div>
                                            <div class="rounded-lg bg-white p-4 ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700">
                                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Kualitas (35%)</p>
                                                <p class="mt-1 text-xl font-bold text-gray-900 dark:text-white">{{ number_format($card['quality_acceptance'], 1, ',', '.') }}%</p>
                                                <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                                    <div class="h-full rounded-full bg-emerald-500" style="width: {{ min(100, $card['quality_acceptance']) }}%"></div>
                                                </div>
                                            </div>
                                            <div class="rounded-lg bg-white p-4 ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700">
                                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Daya Saing Harga (15%)</p>
                                                <p class="mt-1 text-xl font-bold text-gray-900 dark:text-white">{{ number_format($card['price_competitiveness'], 1, ',', '.') }}%</p>
                                                <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                                    <div class="h-full rounded-full bg-amber-500" style="width: {{ min(100, $card['price_competitiveness']) }}%"></div>
                                                </div>
                                            </div>
                                            <div class="rounded-lg bg-white p-4 ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700">
                                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Respons (10%)</p>
                                                <p class="mt-1 text-xl font-bold text-gray-900 dark:text-white">{{ number_format($card['response_time'], 1, ',', '.') }}%</p>
                                                <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                                    <div class="h-full rounded-full bg-purple-500" style="width: {{ min(100, $card['response_time']) }}%"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3">
                                            <div>
                                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Pesanan</p>
                                                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($card['total_orders'], 0, ',', '.') }}</p>
                                            </div>
                                            <div>
                                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Nilai</p>
                                                <p class="text-sm font-semibold text-gray-900 dark:text-white">Rp {{ number_format($card['total_value'], 0, ',', '.') }}</p>
                                            </div>
                                            <div>
                                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Rata-rata Waktu Kirim</p>
                                                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($card['avg_delivery_days'], 1, ',', '.') }} hari</p>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">Tidak ada data vendor</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if(!empty($radar))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const isDark = document.documentElement.classList.contains('dark');
            const gridColor = isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.08)';
            const textColor = isDark ? '#9ca3af' : '#6b7280';

            var ctx = document.getElementById('radarChart').getContext('2d');
            new Chart(ctx, {
                type: 'radar',
                data: {
                    labels: @json($radar['labels']),
                    datasets: [{
                        label: @json($radar['supplier_name']),
                        data: @json($radar['values']),
                        backgroundColor: 'rgba(99, 102, 241, 0.25)',
                        borderColor: 'rgba(99, 102, 241, 1)',
                        borderWidth: 2,
                        pointBackgroundColor: 'rgba(99, 102, 241, 1)',
                        pointRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        r: {
                            min: 0,
                            max: 100,
                            angleLines: { color: gridColor },
                            grid: { color: gridColor },
                            pointLabels: { color: textColor, font: { size: 12 } },
                            ticks: {
                                color: textColor,
                                backdropColor: 'transparent',
                                stepSize: 20,
                            },
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { color: textColor, usePointStyle: true, padding: 16 }
                        }
                    }
                }
            });
        });
    </script>
    @endif
</x-filament-panels::page>
