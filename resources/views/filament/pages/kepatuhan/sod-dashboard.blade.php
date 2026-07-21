<x-filament-panels::page>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold tracking-tight">Dashboard Segregation of Duties (SoD)</h1>
            <span class="text-sm text-gray-500">ISO 27001 A.5.3 — Pemisahan Tugas</span>
        </div>

        {{-- Top Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <div class="text-sm text-gray-500">Total Aturan SoD</div>
                <div class="mt-2"><span class="text-4xl font-extrabold">{{ $ruleStats['total'] }}</span></div>
                <div class="mt-1 text-xs text-gray-500">{{ $ruleStats['active'] }} aktif</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <div class="text-sm text-gray-500">Konflik Terdeteksi</div>
                <div class="mt-2"><span class="text-4xl font-extrabold text-red-600">{{ $conflictStats['detected'] }}</span></div>
                <div class="mt-1 text-xs text-gray-500">dari {{ $usersScanned }} user dipindai</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <div class="text-sm text-gray-500">Konflik Terselesaikan</div>
                <div class="mt-2"><span class="text-4xl font-extrabold text-green-600">{{ $conflictStats['resolved'] }}</span></div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <div class="text-sm text-gray-500">Mitigasi</div>
                <div class="mt-2"><span class="text-4xl font-extrabold text-amber-600">{{ $conflictStats['mitigated'] }}</span></div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Risk Distribution --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold mb-4">Distribusi Risiko Aturan</h2>
                <div class="space-y-3">
                    @foreach(['critical' => ['Kritis', 'red'], 'high' => ['Tinggi', 'amber'], 'medium' => ['Sedang', 'blue'], 'low' => ['Rendah', 'green']] as $level => $info)
                    @php $count = $ruleStats['by_risk'][$level] ?? 0; @endphp
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">{{ $info[0] }}</span>
                        <div class="flex items-center gap-2">
                            <div class="w-32 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="h-2 rounded-full bg-{{ $info[1] }}-500" style="width: {{ $ruleStats['total'] > 0 ? ($count / $ruleStats['total'] * 100) : 0 }}%"></div>
                            </div>
                            <span class="font-semibold">{{ $count }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Conflict Status --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold mb-4">Status Konflik</h2>
                <div class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Terdeteksi</span>
                        <span class="font-semibold text-red-600">{{ $conflictStats['detected'] }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Dimediasi</span>
                        <span class="font-semibold text-amber-600">{{ $conflictStats['mitigated'] }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Terselesaikan</span>
                        <span class="font-semibold text-green-600">{{ $conflictStats['resolved'] }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Active Conflicts Table --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold mb-4">Konflik Aktif ({{ count($activeConflicts) }})</h2>
            @if(count($activeConflicts) > 0)
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b text-left text-gray-500 text-xs uppercase tracking-wider">
                        <th class="pb-3">User</th>
                        <th class="pb-3">Aturan</th>
                        <th class="pb-3">Fungsi Sensitif</th>
                        <th class="pb-3">Fungsi Konflik</th>
                        <th class="pb-3">Risiko</th>
                        <th class="pb-3">Terdeteksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($activeConflicts as $conflict)
                    <tr class="border-b border-gray-100 dark:border-gray-700">
                        <td class="py-3 font-medium">{{ $conflict['user']['name'] ?? 'N/A' }}</td>
                        <td class="py-3">{{ $conflict['rule']['name'] ?? 'N/A' }}</td>
                        <td class="py-3">{{ $conflict['sensitive_permission'] }}</td>
                        <td class="py-3 text-red-600">{{ $conflict['conflicting_permission'] }}</td>
                        <td class="py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                                @if($conflict['risk_level'] === 'critical') bg-red-100 text-red-800
                                @elseif($conflict['risk_level'] === 'high') bg-amber-100 text-amber-800
                                @elseif($conflict['risk_level'] === 'medium') bg-blue-100 text-blue-800
                                @else bg-green-100 text-green-800 @endif">
                                {{ $conflict['risk_level'] }}
                            </span>
                        </td>
                        <td class="py-3 text-gray-500">{{ \Carbon\Carbon::parse($conflict['detected_at'])->format('d M Y H:i') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="py-8 text-center text-green-600">
                <p class="text-lg font-semibold">Tidak ada konflik SoD terdeteksi</p>
                <p class="text-sm text-gray-400 mt-1">Semua pengguna memiliki pemisahan tugas yang benar</p>
            </div>
            @endif
        </div>

        {{-- Conflict Matrix --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 overflow-x-auto">
            <h2 class="text-lg font-semibold mb-4">Matriks Konflik SoD</h2>
            @if(!empty($conflictMatrix['functions']))
            <table class="w-full text-xs">
                <thead>
                    <tr>
                        <th class="p-2 border bg-gray-50 dark:bg-gray-700"></th>
                        @foreach($conflictMatrix['functions'] as $func)
                        <th class="p-2 border bg-gray-50 dark:bg-gray-700 font-medium rotate-45" style="writing-mode: vertical-rl;">
                            {{ Str::limit($func, 15) }}
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($conflictMatrix['functions'] as $row)
                    <tr>
                        <td class="p-2 border bg-gray-50 dark:bg-gray-700 font-medium whitespace-nowrap">{{ Str::limit($row, 15) }}</td>
                        @foreach($conflictMatrix['functions'] as $col)
                        @php $val = $conflictMatrix['matrix'][$row][$col] ?? '-'; @endphp
                        <td class="p-2 border text-center
                            @if($val === 'critical') bg-red-100 text-red-800 font-bold
                            @elseif($val === 'high') bg-amber-100 text-amber-800 font-semibold
                            @elseif($val === 'medium') bg-blue-50 text-blue-700
                            @elseif($val === 'low') bg-green-50 text-green-700
                            @elseif($val === '-') bg-gray-50 dark:bg-gray-700 text-gray-300
                            @else text-gray-400 text-xs @endif">
                            {{ $val === 'safe' ? '✔' : ($val === '-' ? '' : Str::limit($val, 1)) }}
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <p class="text-gray-400 text-sm text-center py-8">Belum ada data matriks</p>
            @endif
        </div>
    </div>
</x-filament-panels::page>
