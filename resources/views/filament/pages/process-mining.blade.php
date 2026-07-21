<x-filament-panels::page>
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Process Mining Dashboard</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Analisis bottleneck, conformance, dan improvement suggestions</p>
            </div>
            <div class="flex gap-3">
                <select wire:model.live="selectedProcessId" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                    <option value="">-- Pilih Proses --</option>
                    @foreach($processList as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
                <select wire:model.live="period" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
                    <option value="7 days">7 Hari</option>
                    <option value="30 days">30 Hari</option>
                    <option value="90 days">90 Hari</option>
                    <option value="1 year">1 Tahun</option>
                </select>
            </div>
        </div>

        @if(empty($selectedProcessId))
            <div class="p-12 text-center text-gray-400 dark:text-gray-500">
                <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <p class="text-lg font-medium">Pilih proses untuk melihat analisis</p>
            </div>
        @else
            {{-- Summary Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                    <div class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{ $miningResults['total_instances'] ?? 0 }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Total Instances</div>
                </div>
                <div class="p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                    <div class="text-3xl font-bold text-emerald-600 dark:text-emerald-400">{{ $miningResults['completed_instances'] ?? 0 }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Selesai</div>
                </div>
                <div class="p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                    <div class="text-3xl font-bold text-amber-600 dark:text-amber-400">{{ $miningResults['running_instances'] ?? 0 }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Berjalan</div>
                </div>
                <div class="p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                    <div class="text-3xl font-bold {{ ($conformance['conformance_percent'] ?? 100) >= 90 ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">
                        {{ $conformance['conformance_percent'] ?? '-' }}%
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Conformance</div>
                </div>
            </div>

            {{-- Avg Duration --}}
            <div class="p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-3">Rata-rata Durasi Total</h3>
                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ round($miningResults['avg_total_duration_hours'] ?? 0, 1) }} jam</div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Dari {{ $miningResults['completed_instances'] ?? 0 }} instance yang selesai</p>
            </div>

            {{-- Bottlenecks --}}
            <div class="p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">
                    Bottleneck Terdeteksi
                    @if(!empty($bottlenecks))
                        <span class="ml-2 px-2 py-0.5 text-xs rounded-full bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">{{ count($bottlenecks) }}</span>
                    @endif
                </h3>
                @if(empty($bottlenecks))
                    <p class="text-sm text-emerald-600 dark:text-emerald-400">Tidak ada bottleneck terdeteksi. Proses berjalan efisien!</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-gray-500 dark:text-gray-400 border-b dark:border-gray-700">
                                    <th class="pb-2 font-medium">Task</th>
                                    <th class="pb-2 font-medium">Avg Duration</th>
                                    <th class="pb-2 font-medium">Median</th>
                                    <th class="pb-2 font-medium">Instances</th>
                                    <th class="pb-2 font-medium">Severity</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y dark:divide-gray-700">
                                @foreach($bottlenecks as $bn)
                                    <tr>
                                        <td class="py-2 text-gray-900 dark:text-white">{{ $bn['task_name'] }}</td>
                                        <td class="py-2 text-gray-700 dark:text-gray-300">{{ $bn['avg_duration_hours'] }} jam</td>
                                        <td class="py-2 text-gray-700 dark:text-gray-300">{{ $bn['median_duration_hours'] }} jam</td>
                                        <td class="py-2 text-gray-700 dark:text-gray-300">{{ $bn['instances'] }}</td>
                                        <td class="py-2">
                                            <span class="px-2 py-0.5 text-xs rounded-full {{ $bn['severity'] === 'high' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' }}">
                                                {{ $bn['severity'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- Actual Flow (Process Map) --}}
            <div class="p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Alur Aktual (Task Statistics)</h3>
                @if(empty($miningResults['actual_flow'] ?? []))
                    <p class="text-sm text-gray-400">Belum ada data eksekusi.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-left text-gray-500 dark:text-gray-400 border-b dark:border-gray-700">
                                    <th class="pb-2 font-medium">Task</th>
                                    <th class="pb-2 font-medium">Avg (menit)</th>
                                    <th class="pb-2 font-medium">Count</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y dark:divide-gray-700">
                                @foreach($miningResults['actual_flow'] as $task)
                                    <tr>
                                        <td class="py-2 text-gray-900 dark:text-white">{{ $task['task_name'] }}</td>
                                        <td class="py-2 text-gray-700 dark:text-gray-300">{{ $task['avg_minutes'] }} menit</td>
                                        <td class="py-2 text-gray-700 dark:text-gray-300">{{ $task['count'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- Conformance --}}
            <div class="p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Conformance Check</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <div class="text-gray-500 dark:text-gray-400">Designed Tasks</div>
                        <div class="text-lg font-bold text-gray-900 dark:text-white">{{ $conformance['designed_tasks_count'] ?? 0 }}</div>
                    </div>
                    <div>
                        <div class="text-gray-500 dark:text-gray-400">Executed Tasks</div>
                        <div class="text-lg font-bold text-gray-900 dark:text-white">{{ $conformance['executed_tasks_count'] ?? 0 }}</div>
                    </div>
                    <div>
                        <div class="text-gray-500 dark:text-gray-400">Matching</div>
                        <div class="text-lg font-bold text-emerald-600 dark:text-emerald-400">{{ $conformance['matching_tasks'] ?? 0 }}</div>
                    </div>
                    <div>
                        <div class="text-gray-500 dark:text-gray-400">Conformance</div>
                        <div class="text-lg font-bold {{ ($conformance['conformance_percent'] ?? 100) >= 90 ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">
                            {{ $conformance['conformance_percent'] ?? 0 }}%
                        </div>
                    </div>
                </div>
            </div>

            {{-- Improvement Suggestions --}}
            <div class="p-5 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Saran Peningkatan</h3>
                @if(empty($miningResults['improvement_suggestions'] ?? []))
                    <p class="text-sm text-gray-400">Tidak ada saran.</p>
                @else
                    <ul class="space-y-2">
                        @foreach($miningResults['improvement_suggestions'] as $suggestion)
                            <li class="flex items-start gap-3 p-3 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg">
                                <svg class="w-5 h-5 text-indigo-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span class="text-sm text-indigo-700 dark:text-indigo-300">{{ $suggestion }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endif
    </div>
</x-filament-panels::page>
