<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Stats Overview --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <x-filament::section class="p-4 !rounded-xl">
                <div class="text-center">
                    <div class="text-3xl font-extrabold text-indigo-600">{{ Number::format($stats['total_users']) }}</div>
                    <div class="text-xs text-gray-500 mt-1">Pengguna Aktif</div>
                </div>
            </x-filament::section>
            <x-filament::section class="p-4 !rounded-xl">
                <div class="text-center">
                    <div class="text-3xl font-extrabold text-emerald-600">{{ Number::format($stats['total_points']) }}</div>
                    <div class="text-xs text-gray-500 mt-1">Total Poin</div>
                </div>
            </x-filament::section>
            <x-filament::section class="p-4 !rounded-xl">
                <div class="text-center">
                    <div class="text-3xl font-extrabold text-amber-600">{{ Number::format($stats['total_badges']) }}</div>
                    <div class="text-xs text-gray-500 mt-1">Badge Diberikan</div>
                </div>
            </x-filament::section>
            <x-filament::section class="p-4 !rounded-xl">
                <div class="text-center">
                    <div class="text-3xl font-extrabold text-rose-600">{{ Number::format($stats['total_recognitions']) }}</div>
                    <div class="text-xs text-gray-500 mt-1">Pengakuan Rekan</div>
                </div>
            </x-filament::section>
            <x-filament::section class="p-4 !rounded-xl">
                <div class="text-center">
                    <div class="text-3xl font-extrabold text-purple-600">{{ Number::format($stats['active_challenges']) }}</div>
                    <div class="text-xs text-gray-500 mt-1">Tantangan Aktif</div>
                </div>
            </x-filament::section>
            <x-filament::section class="p-4 !rounded-xl">
                <div class="text-center">
                    <div class="text-3xl font-extrabold text-blue-600">{{ Number::format($stats['avg_points_per_user']) }}</div>
                    <div class="text-xs text-gray-500 mt-1">Rata-rata Poin/User</div>
                </div>
            </x-filament::section>
        </div>

        {{-- Leaderboard + Level Distribution --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
                <x-filament::section>
                    <x-slot name="heading">
                        <div class="flex items-center justify-between w-full">
                            <span>Papan Peringkat</span>
                            <div class="flex gap-1">
                                @foreach(['weekly' => 'Mingguan', 'monthly' => 'Bulanan', 'all_time' => 'Semua'] as $key => $label)
                                    <x-filament::button
                                        size="xs"
                                        :color="$leaderboardPeriod === $key ? 'primary' : 'gray'"
                                        wire:click="setPeriod('{{ $key }}')"
                                    >
                                        {{ $label }}
                                    </x-filament::button>
                                @endforeach
                            </div>
                        </div>
                    </x-slot>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b text-left text-xs uppercase text-gray-500">
                                    <th class="p-3 w-12">#</th>
                                    <th class="p-3">Karyawan</th>
                                    <th class="p-3 text-right">Poin</th>
                                    <th class="p-3 text-right">Badge</th>
                                    <th class="p-3">Departemen</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($leaderboard as $entry)
                                    <tr class="border-b hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                                        <td class="p-3 font-bold">
                                            @if($entry['rank'] === 1) <span class="text-amber-500 text-lg">&#x1F947;</span>
                                            @elseif($entry['rank'] === 2) <span class="text-gray-400 text-lg">&#x1F948;</span>
                                            @elseif($entry['rank'] === 3) <span class="text-amber-700 text-lg">&#x1F949;</span>
                                            @else <span class="text-gray-500">{{ $entry['rank'] }}</span>
                                            @endif
                                        </td>
                                        <td class="p-3">
                                            <div class="flex items-center gap-2">
                                                <x-filament::avatar
                                                    :src="$entry['avatar'] ? asset('storage/' . $entry['avatar']) : ''"
                                                    size="w-8 h-8"
                                                />
                                                <span class="font-medium">{{ $entry['name'] }}</span>
                                            </div>
                                        </td>
                                        <td class="p-3 text-right font-semibold text-indigo-600">{{ Number::format($entry['points']) }}</td>
                                        <td class="p-3 text-right">{{ $entry['badges_count'] }}</td>
                                        <td class="p-3 text-gray-500">{{ $entry['department'] }}</td>
                                    </tr>
                                @endforeach
                                @if(empty($leaderboard))
                                    <tr>
                                        <td colspan="5" class="p-8 text-center text-gray-400">Belum ada data poin.</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </x-filament::section>
            </div>
            <div>
                <x-filament::section>
                    <x-slot name="heading">Distribusi Level</x-slot>
                    <div class="h-64">
                        <canvas id="levelDistChart"></canvas>
                    </div>
                </x-filament::section>
            </div>
        </div>

        {{-- Points Distribution + Top Badges --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <x-filament::section>
                <x-slot name="heading">Distribusi Poin (30 Hari Terakhir)</x-slot>
                <div class="h-72">
                    <canvas id="pointsDistChart"></canvas>
                </div>
            </x-filament::section>
            <x-filament::section>
                <x-slot name="heading">Top Badge Paling Banyak Dimiliki</x-slot>
                <div class="space-y-3 max-h-72 overflow-y-auto">
                    @foreach($topBadges as $badge)
                        <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                            <div class="flex items-center gap-3">
                                <x-filament::icon :icon="$badge['icon']" class="w-5 h-5 text-{{ $badge['color'] }}-500" />
                                <span class="font-medium">{{ $badge['name'] }}</span>
                            </div>
                            <span class="text-sm font-bold text-gray-600">{{ $badge['count'] }}x</span>
                        </div>
                    @endforeach
                    @if(empty($topBadges))
                        <div class="p-8 text-center text-gray-400">Belum ada badge diberikan.</div>
                    @endif
                </div>
            </x-filament::section>
        </div>

        {{-- Most Recognized --}}
        @if(!empty($mostRecognized))
        <x-filament::section>
            <x-slot name="heading">Karyawan Paling Banyak Mendapat Pengakuan</x-slot>
            <div class="flex flex-wrap gap-4">
                @foreach($mostRecognized as $rec)
                    <div class="flex items-center gap-3 p-4 rounded-xl border border-gray-200 dark:border-gray-700 min-w-[200px]">
                        <x-filament::avatar
                            :src="$rec['avatar'] ? asset('storage/' . $rec['avatar']) : ''"
                            size="w-10 h-10"
                        />
                        <div>
                            <div class="font-semibold text-sm">{{ $rec['name'] }}</div>
                            <div class="text-xs text-rose-500 font-bold">{{ $rec['count'] }}x pengakuan</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-filament::section>
        @endif
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Level Distribution Doughnut
            const levelCtx = document.getElementById('levelDistChart')?.getContext('2d');
            if (levelCtx) {
                new Chart(levelCtx, {
                    type: 'doughnut',
                    data: {
                        labels: {!! json_encode(array_keys($levelDistribution)) !!},
                        datasets: [{
                            data: {!! json_encode(array_values($levelDistribution)) !!},
                            backgroundColor: [
                                '#cd7f32', '#c0c0c0', '#ffd700', '#e5e4e2', '#b9f2ff', '#e8a0bf'
                            ],
                            borderWidth: 0,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom', labels: { padding: 12, usePointStyle: true, pointStyleWidth: 10 } }
                        }
                    }
                });
            }

            // Points Distribution Bar
            const ptsCtx = document.getElementById('pointsDistChart')?.getContext('2d');
            if (ptsCtx) {
                new Chart(ptsCtx, {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode(array_column($pointsDistribution, 'action')) !!},
                        datasets: [{
                            label: 'Total Poin',
                            data: {!! json_encode(array_column($pointsDistribution, 'points')) !!},
                            backgroundColor: 'rgba(99, 102, 241, 0.7)',
                            borderColor: 'rgba(99, 102, 241, 1)',
                            borderWidth: 1,
                            borderRadius: 6,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        indexAxis: 'y',
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                            y: { grid: { display: false } }
                        }
                    }
                });
            }
        });
    </script>
    @endpush
</x-filament-panels::page>
