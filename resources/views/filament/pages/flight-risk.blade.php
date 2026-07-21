<x-filament-panels::page>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Analisis Risiko Turnover</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Prediksi risiko resign karyawan dengan 7 faktor analisis + rekomendasi retensi AI
                </p>
            </div>
        </div>

        {{ $this->form }}

        @if(!empty($departmentSummary))
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-xl border p-4">
                    <p class="text-xs text-gray-500">Total Karyawan</p>
                    <p class="text-2xl font-bold">{{ $departmentSummary['total_employees'] }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl border p-4">
                    <p class="text-xs text-red-500">Critical</p>
                    <p class="text-2xl font-bold text-red-600">{{ $departmentSummary['critical'] }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl border p-4">
                    <p class="text-xs text-orange-500">High</p>
                    <p class="text-2xl font-bold text-orange-600">{{ $departmentSummary['high'] }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl border p-4">
                    <p class="text-xs text-yellow-500">Medium</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $departmentSummary['medium'] }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl border p-4">
                    <p class="text-xs text-gray-500">Rata-rata Skor</p>
                    <p class="text-2xl font-bold">{{ $departmentSummary['avg_risk_score'] }}/100</p>
                </div>
            </div>
        @else
            @php $sum = $this->getSummaryStats(); @endphp
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-xl border p-4">
                    <p class="text-xs text-gray-500">Total Dianalisis</p>
                    <p class="text-2xl font-bold">{{ $sum['total'] }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl border p-4">
                    <p class="text-xs text-red-500">Critical</p>
                    <p class="text-2xl font-bold text-red-600">{{ $sum['critical'] }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl border p-4">
                    <p class="text-xs text-orange-500">High</p>
                    <p class="text-2xl font-bold text-orange-600">{{ $sum['high'] }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl border p-4">
                    <p class="text-xs text-yellow-500">Medium</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $sum['medium'] }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl border p-4">
                    <p class="text-xs text-green-500">Low</p>
                    <p class="text-2xl font-bold text-green-600">{{ $sum['low'] }}</p>
                </div>
            </div>
        @endif

        @if(!empty($topRisks))
        <div class="bg-white dark:bg-gray-800 rounded-xl border overflow-hidden">
            <div class="p-4 border-b">
                <h3 class="font-semibold">Top Karyawan Berisiko</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700/50 text-left text-gray-500 dark:text-gray-400">
                            <th class="p-3">#</th>
                            <th class="p-3">Nama</th>
                            <th class="p-3">Departemen</th>
                            <th class="p-3">Posisi</th>
                            <th class="p-3 text-center">Skor</th>
                            <th class="p-3 text-center">Level</th>
                            <th class="p-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topRisks as $i => $risk)
                        <tr class="border-t @if($risk['risk_level'] === 'critical') bg-red-50/30 dark:bg-red-900/10 @endif">
                            <td class="p-3">{{ $i + 1 }}</td>
                            <td class="p-3 font-medium">{{ $risk['employee_name'] }}</td>
                            <td class="p-3 text-gray-500">{{ $risk['department'] }}</td>
                            <td class="p-3 text-gray-500">{{ $risk['position'] }}</td>
                            <td class="p-3 text-center font-bold">
                                <span style="color: {{ $this->getRiskLevelColor($risk['risk_level']) }}">
                                    {{ $risk['risk_score'] }}
                                </span>
                            </td>
                            <td class="p-3 text-center">
                                <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $this->getRiskLevelBg($risk['risk_level']) }}">
                                    {{ ucfirst($risk['risk_level']) }}
                                </span>
                            </td>
                            <td class="p-3 text-center">
                                <x-filament::button wire:click="viewRetentionPlan({{ $loop->index }})" size="xs" color="gray" outlined>
                                    Retensi Plan
                                </x-filament::button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        @if(!empty($retentionPlan) && !empty($retentionPlan['risk']))
        <div class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-semibold text-indigo-900 dark:text-indigo-300">
                        Retention Plan: {{ $retentionPlan['risk']['employee_name'] }}
                    </h3>
                    <p class="text-sm text-indigo-600 dark:text-indigo-400">
                        Skor Risiko: {{ $retentionPlan['risk']['risk_score'] }}/100 ({{ $retentionPlan['risk']['risk_level'] }})
                    </p>
                </div>
                <x-filament::icon-button wire:click="$set('retentionPlan', [])" icon="heroicon-o-x-mark" color="gray" size="sm" />
            </div>

            <div class="mb-4">
                <h4 class="text-sm font-semibold mb-2">Faktor Risiko:</h4>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                    @foreach($retentionPlan['risk']['factors'] as $key => $factor)
                    <div class="bg-white/60 dark:bg-white/10 rounded-lg p-2 text-sm">
                        <span class="font-medium">{{ $factor['label'] }}</span>
                        <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-1.5 mt-1">
                            <div class="h-1.5 rounded-full @if($factor['risk']==='high') bg-red-500 @elseif($factor['risk']==='medium') bg-yellow-500 @else bg-green-500 @endif" style="width: {{ $factor['raw'] }}%"></div>
                        </div>
                        <span class="text-xs text-gray-500">{{ $factor['raw'] }}%</span>
                    </div>
                    @endforeach
                </div>
            </div>

            <div>
                <h4 class="text-sm font-semibold mb-2">Rekomendasi Retensi AI:</h4>
                <ul class="space-y-2">
                    @foreach($retentionPlan['ai_recommendations'] as $rec)
                    <li class="flex gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <span class="text-indigo-500 font-bold">&#9654;</span>
                        <span>{{ $rec }}</span>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif
    </div>
</x-filament-panels::page>
