<x-filament-panels::page>
    <div class="space-y-6">
        {{ $this->form }}

        @if($selectedCycle)
            <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10">
                <h2 class="text-lg font-semibold mb-4">Distribusi Kurva Lonceng — {{ $selectedCycle->name }}</h2>
                @if($bellCurve && ($bellCurve['total_reviews'] ?? 0) > 0)
                    <div class="flex items-end gap-4 h-48">
                        @foreach($bellCurve['distribution'] ?? [] as $item)
                            @php
                                $maxCount = max(array_column($bellCurve['distribution'] ?? [], 'count')) ?: 1;
                                $height = ($item['count'] / $maxCount) * 100;
                            @endphp
                            <div class="flex-1 flex flex-col items-center gap-1">
                                <span class="text-xs font-bold">{{ $item['count'] }}</span>
                                <div class="w-full rounded-t-lg transition-all duration-500" style="height: {{ max($height, 4) }}%; background-color: {{ $item['color'] }}"></div>
                                <span class="text-xs font-medium">{{ $item['label'] }}</span>
                                <span class="text-xs text-gray-500">{{ $item['percent'] }}%</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4 text-sm text-gray-500 text-center">
                        Total Review: <strong>{{ $bellCurve['total_reviews'] }}</strong> &middot; Rata-rata Skor: <strong>{{ $bellCurve['average_score'] }}</strong>
                    </div>
                @else
                    <p class="text-gray-500 text-sm">Belum ada data review yang selesai.</p>
                @endif
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10">
                    <h3 class="text-base font-semibold mb-3">Top 10 Performa Terbaik</h3>
                    @if(count($topPerformers) > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b text-left text-gray-500">
                                        <th class="pb-2">#</th>
                                        <th class="pb-2">Nama</th>
                                        <th class="pb-2">Departemen</th>
                                        <th class="pb-2 text-right">Skor</th>
                                        <th class="pb-2 text-right">Rating</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($topPerformers as $i => $emp)
                                        <tr class="border-b last:border-0">
                                            <td class="py-2">{{ $i + 1 }}</td>
                                            <td class="py-2 font-medium">{{ $emp['employee_name'] }}</td>
                                            <td class="py-2 text-gray-500">{{ $emp['department'] ?? '-' }}</td>
                                            <td class="py-2 text-right">{{ number_format($emp['score'], 2) }}</td>
                                            <td class="py-2 text-right">
                                                <span class="px-2 py-0.5 rounded text-xs font-bold text-white" style="background-color: {{ $emp['rating'] === 'A' ? '#10b981' : ($emp['rating'] === 'B' ? '#6366f1' : '#f59e0b') }}">
                                                    {{ $emp['rating'] }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500 text-sm">Belum ada data.</p>
                    @endif
                </div>

                <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10">
                    <h3 class="text-base font-semibold mb-3">Perlu Perhatian (Skor Terendah)</h3>
                    @if(count($bottomPerformers) > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b text-left text-gray-500">
                                        <th class="pb-2">#</th>
                                        <th class="pb-2">Nama</th>
                                        <th class="pb-2">Departemen</th>
                                        <th class="pb-2 text-right">Skor</th>
                                        <th class="pb-2 text-right">Rating</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bottomPerformers as $i => $emp)
                                        <tr class="border-b last:border-0">
                                            <td class="py-2">{{ $i + 1 }}</td>
                                            <td class="py-2 font-medium">{{ $emp['employee_name'] }}</td>
                                            <td class="py-2 text-gray-500">{{ $emp['department'] ?? '-' }}</td>
                                            <td class="py-2 text-right">{{ number_format($emp['score'], 2) }}</td>
                                            <td class="py-2 text-right">
                                                <span class="px-2 py-0.5 rounded text-xs font-bold text-white" style="background-color: {{ $emp['rating'] === 'E' ? '#ef4444' : '#f97316' }}">
                                                    {{ $emp['rating'] }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500 text-sm">Belum ada data.</p>
                    @endif
                </div>
            </div>

            <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10">
                <h3 class="text-base font-semibold mb-3">Peringkat Performa per Departemen</h3>
                @if(count($departmentRanking) > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($departmentRanking as $rank => $dept)
                            <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-white/5">
                                <div>
                                    <span class="text-xs text-gray-500">#{{ $rank + 1 }}</span>
                                    <div class="font-semibold">{{ $dept['department'] }}</div>
                                    <div class="text-xs text-gray-500">{{ $dept['total_employees'] }} karyawan</div>
                                </div>
                                <div class="text-2xl font-bold {{ $dept['avg_score'] >= 75 ? 'text-green-600' : ($dept['avg_score'] >= 60 ? 'text-amber' : 'text-red-600') }}">
                                    {{ number_format($dept['avg_score'], 1) }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-sm">Belum ada data.</p>
                @endif
            </div>

            @if($recommendations)
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10">
                        <h3 class="text-base font-semibold mb-3 text-green-600">Kandidat Promosi</h3>
                        @php $promoCount = count($recommendations['promotion_candidates'] ?? []); @endphp
                        @if($promoCount > 0)
                            <p class="text-sm text-gray-500 mb-2">{{ $promoCount }} kandidat</p>
                            <div class="space-y-2 max-h-64 overflow-y-auto">
                                @foreach($recommendations['promotion_candidates'] as $c)
                                    <div class="p-2 rounded bg-green-50 dark:bg-green-900/20 text-sm">
                                        <div class="font-medium">{{ $c['employee_name'] }}</div>
                                        <div class="text-xs text-gray-500">{{ $c['department'] ?? '-' }} &middot; {{ $c['position'] ?? '-' }}</div>
                                        <div class="text-xs font-bold text-green-700">{{ $c['recommendation'] }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 text-sm">Tidak ada kandidat.</p>
                        @endif
                    </div>

                    <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10">
                        <h3 class="text-base font-semibold mb-3 text-indigo-600">Eligible Bonus</h3>
                        @php $bonusCount = count($recommendations['bonus_eligible'] ?? []); @endphp
                        @if($bonusCount > 0)
                            <p class="text-sm text-gray-500 mb-2">{{ $bonusCount }} eligible</p>
                            <div class="space-y-2 max-h-64 overflow-y-auto">
                                @foreach($recommendations['bonus_eligible'] as $b)
                                    <div class="p-2 rounded bg-indigo-50 dark:bg-indigo-900/20 text-sm">
                                        <div class="font-medium">{{ $b['employee_name'] }}</div>
                                        <div class="text-xs text-gray-500">Rating {{ $b['rating'] }}</div>
                                        <div class="text-xs font-bold text-indigo-700">Bonus: Rp {{ number_format($b['bonus'], 0, ',', '.') }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 text-sm">Tidak ada yang eligible.</p>
                        @endif
                    </div>

                    <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10">
                        <h3 class="text-base font-semibold mb-3 text-red-600">Butuh Improvement</h3>
                        @php $improvCount = count($recommendations['improvement_needed'] ?? []); @endphp
                        @if($improvCount > 0)
                            <p class="text-sm text-gray-500 mb-2">{{ $improvCount }} karyawan</p>
                            <div class="space-y-2 max-h-64 overflow-y-auto">
                                @foreach($recommendations['improvement_needed'] as $imp)
                                    <div class="p-2 rounded bg-red-50 dark:bg-red-900/20 text-sm">
                                        <div class="font-medium">{{ $imp['employee_name'] }}</div>
                                        <div class="text-xs text-gray-500">{{ $imp['department'] ?? '-' }}</div>
                                        <div class="text-xs font-bold text-red-700">{{ $imp['recommendation'] }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 text-sm">Tidak ada yang butuh improvement.</p>
                        @endif
                    </div>
                </div>
            @endif
        @else
            <div class="fi-section rounded-xl bg-white p-12 shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10 text-center">
                <p class="text-gray-500">Pilih siklus performa untuk melihat dashboard.</p>
            </div>
        @endif
    </div>
</x-filament-panels::page>
