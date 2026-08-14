<x-filament-panels::page>
    <div class="space-y-6">
        {{ $this->form }}

        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4">
            <div class="fi-section rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10 text-center">
                <div class="text-3xl font-bold text-primary-600">{{ $completionStats['total'] ?? 0 }}</div>
                <div class="text-xs text-gray-500 mt-1">Total Objective</div>
            </div>
            <div class="fi-section rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10 text-center">
                <div class="text-3xl font-bold text-green-600">{{ $completionStats['completed'] ?? 0 }}</div>
                <div class="text-xs text-gray-500 mt-1">Selesai</div>
            </div>
            <div class="fi-section rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10 text-center">
                <div class="text-3xl font-bold text-blue-600">{{ $completionStats['on_track'] ?? 0 }}</div>
                <div class="text-xs text-gray-500 mt-1">On Track</div>
            </div>
            <div class="fi-section rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10 text-center">
                <div class="text-3xl font-bold text-amber-600">{{ $completionStats['at_risk'] ?? 0 }}</div>
                <div class="text-xs text-gray-500 mt-1">Berisiko</div>
            </div>
            <div class="fi-section rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10 text-center">
                <div class="text-3xl font-bold text-red-600">{{ $completionStats['behind'] ?? 0 }}</div>
                <div class="text-xs text-gray-500 mt-1">Tertinggal</div>
            </div>
            <div class="fi-section rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10 text-center">
                <div class="text-3xl font-bold text-indigo-600">{{ $completionStats['avg_progress'] ?? 0 }}%</div>
                <div class="text-xs text-gray-500 mt-1">Rata-rata Progress</div>
            </div>
            <div class="fi-section rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10 text-center">
                <div class="text-3xl font-bold text-emerald-600">{{ $completionStats['completion_rate'] ?? 0 }}%</div>
                <div class="text-xs text-gray-500 mt-1">Tingkat Penyelesaian</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10">
                <h3 class="text-base font-semibold mb-4">Objective Perusahaan</h3>
                @if(count($companyObjectives) > 0)
                    <div class="space-y-3">
                        @foreach($companyObjectives as $obj)
                            @php
                                $progress = $obj['progress_percent'] ?? 0;
                                $color = $progress >= 70 ? '#10b981' : ($progress >= 40 ? '#f59e0b' : '#ef4444');
                                $statusLabel = \App\Models\Objective::statusOptions()[$obj['status']] ?? $obj['status'];
                                $statusHex = \App\Models\Objective::statusColorHex()[$obj['status']] ?? '#6b7280';
                            @endphp
                            <div class="p-4 rounded-lg bg-gray-50 dark:bg-white/5 hover:shadow transition-shadow">
                                <div class="flex items-start justify-between mb-2">
                                    <div>
                                        <a href="{{ url('/admin/objectives/' . $obj['id'] . '/edit') }}" class="font-semibold text-gray-900 dark:text-white hover:text-primary-600">
                                            {{ $obj['title'] }}
                                        </a>
                                        @if(!empty($obj['description']))
                                            <p class="text-sm text-gray-500 mt-1 line-clamp-1">{{ $obj['description'] }}</p>
                                        @endif
                                    </div>
                                    <span class="px-2 py-0.5 rounded text-xs font-semibold text-white" style="background-color: {{ $statusHex }}">
                                        {{ $statusLabel }}
                                    </span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-3 dark:bg-gray-700">
                                    <div class="h-3 rounded-full transition-all duration-500" style="width: {{ max($progress, 3) }}%; background-color: {{ $color }}"></div>
                                </div>
                                <div class="flex justify-between mt-1 text-xs text-gray-500">
                                    <span>{{ $progress }}%</span>
                                    <span>{{ $obj['key_results_count'] ?? 0 }} Key Results</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-sm text-center py-8">Belum ada objective perusahaan.</p>
                @endif
            </div>

            <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10">
                <h3 class="text-base font-semibold mb-4">Butuh Perhatian</h3>
                @if(count($atRiskItems) > 0)
                    <div class="space-y-2">
                        @foreach($atRiskItems as $obj)
                            @php
                                $progress = $obj['progress_percent'] ?? 0;
                                $color = $obj['status'] === 'behind' ? '#ef4444' : '#f59e0b';
                                $statusLabel = \App\Models\Objective::statusOptions()[$obj['status']] ?? $obj['status'];
                            @endphp
                            <div class="p-3 rounded-lg border-l-4 @if($obj['status'] === 'behind') border-red-500 bg-red-50 dark:bg-red-900/10 @else border-amber-500 bg-amber-50 dark:bg-amber-900/10 @endif">
                                <a href="{{ url('/admin/objectives/' . $obj['id'] . '/edit') }}" class="text-sm font-semibold hover:text-primary-600">
                                    {{ $obj['title'] }}
                                </a>
                                <div class="flex items-center gap-2 mt-1">
                                    <div class="flex-1 bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                                        <div class="h-2 rounded-full" style="width: {{ max($progress, 3) }}%; background-color: {{ $color }}"></div>
                                    </div>
                                    <span class="text-xs font-bold" style="color: {{ $color }}">{{ $progress }}%</span>
                                </div>
                                <span class="text-xs text-gray-500">{{ $statusLabel }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 text-sm text-center py-8">Semua objective berjalan lancar.</p>
                @endif
            </div>
        </div>

        @if(count($departmentObjectives) > 0)
            <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10">
                <h3 class="text-base font-semibold mb-4">Objective Departemen & Tim</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b text-left text-gray-500">
                                <th class="pb-2">Objective</th>
                                <th class="pb-2">Pemilik</th>
                                <th class="pb-2 text-center">Progress</th>
                                <th class="pb-2 text-center">Status</th>
                                <th class="pb-2 text-center">Key Results</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($departmentObjectives as $obj)
                                @php
                                    $progress = $obj['progress_percent'] ?? 0;
                                    $color = $progress >= 70 ? '#10b981' : ($progress >= 40 ? '#f59e0b' : '#ef4444');
                                @endphp
                                <tr class="border-b last:border-0 hover:bg-gray-50 dark:hover:bg-white/5">
                                    <td class="py-2.5">
                                        <a href="{{ url('/admin/objectives/' . $obj['id'] . '/edit') }}" class="font-medium hover:text-primary-600">{{ $obj['title'] }}</a>
                                    </td>
                                    <td class="py-2.5 text-gray-500">{{ $obj['owner']['name'] ?? $obj['owner']['first_name'] ?? '-' }}</td>
                                    <td class="py-2.5">
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1 bg-gray-200 rounded-full h-2 max-w-[100px]">
                                                <div class="h-2 rounded-full" style="width: {{ max($progress, 3) }}%; background-color: {{ $color }}"></div>
                                            </div>
                                            <span class="text-xs font-bold">{{ $progress }}%</span>
                                        </div>
                                    </td>
                                    <td class="py-2.5 text-center">
                                        @php $deptHex = \App\Models\Objective::statusColorHex()[$obj['status']] ?? '#6b7280'; @endphp
                                        <span class="px-2 py-0.5 rounded text-xs font-semibold text-white" style="background-color: {{ $deptHex }}">
                                            {{ \App\Models\Objective::statusOptions()[$obj['status']] ?? $obj['status'] }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 text-center text-gray-500">{{ $obj['key_results_count'] ?? 0 }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10">
            <h3 class="text-base font-semibold mb-4">Aktivitas Check-in Terbaru</h3>
            @if(count($recentCheckIns) > 0)
                <div class="space-y-2">
                    @foreach($recentCheckIns as $checkIn)
                        <div class="flex items-center gap-3 p-3 rounded-lg bg-gray-50 dark:bg-white/5">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold @if($checkIn['on_track']) bg-green-100 text-green-700 @else bg-red-100 text-red-700 @endif">
                                {{ $checkIn['on_track'] ? 'OK' : '!!' }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium truncate">{{ $checkIn['key_result_title'] }}</div>
                                <div class="text-xs text-gray-500 truncate">{{ $checkIn['objective_title'] }}</div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-bold">{{ number_format($checkIn['value'], 2) }}{{ $checkIn['unit'] ? ' ' . $checkIn['unit'] : '' }}</div>
                                @if($checkIn['confidence'])
                                    <div class="text-xs text-gray-400">Confidence: {{ $checkIn['confidence'] }}/5</div>
                                @endif
                            </div>
                            <div class="text-xs text-gray-400 text-right w-20">
                                <div>{{ $checkIn['checked_by'] }}</div>
                                <div>{{ $checkIn['created_at'] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-sm text-center py-8">Belum ada aktivitas check-in.</p>
            @endif
        </div>
    </div>
</x-filament-panels::page>
