<x-filament-panels::page>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold tracking-tight">Dashboard ISO 27001</h1>
            <span class="text-sm text-gray-500">ISO/IEC 27001:2022 — Sistem Manajemen Keamanan Informasi</span>
        </div>

        {{-- Top Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <div class="text-sm text-gray-500">Total Risiko</div>
                <div class="mt-2"><span class="text-4xl font-extrabold">{{ $riskStats['total'] }}</span></div>
                <div class="mt-1 text-xs">
                    <span class="text-red-600 font-semibold">{{ $riskStats['critical'] }} Kritis</span>
                    <span class="text-amber-500 ml-2">{{ $riskStats['high'] }} Tinggi</span>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <div class="text-sm text-gray-500">Insiden Aktif</div>
                <div class="mt-2"><span class="text-4xl font-extrabold text-red-600">{{ $incidentStats['open'] + $incidentStats['investigating'] }}</span></div>
                <div class="mt-1 text-xs text-gray-500">dari {{ $incidentStats['total'] }} total</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <div class="text-sm text-gray-500">Audit</div>
                <div class="mt-2"><span class="text-4xl font-extrabold text-blue-600">{{ $auditStats['in_progress'] }}</span></div>
                <div class="mt-1 text-xs text-gray-500">berjalan · {{ $auditStats['planned'] }} direncanakan</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <div class="text-sm text-gray-500">Kebijakan Aktif</div>
                <div class="mt-2"><span class="text-4xl font-extrabold text-green-600">{{ $policyStats['active'] }}</span></div>
                <div class="mt-1 text-xs text-gray-500">dari {{ $policyStats['total'] }} total</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Risk Summary --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold mb-4">Ringkasan Risiko</h2>
                <div class="space-y-3">
                    @foreach(['critical' => 'Kritis', 'high' => 'Tinggi', 'medium' => 'Sedang', 'low' => 'Rendah'] as $level => $label)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">{{ $label }}</span>
                        <div class="flex items-center gap-2">
                            <div class="w-32 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="h-2 rounded-full
                                    @if($level === 'critical') bg-red-500
                                    @elseif($level === 'high') bg-amber-500
                                    @elseif($level === 'medium') bg-blue-500
                                    @else bg-green-500 @endif"
                                    style="width: {{ $riskStats['total'] > 0 ? ($riskStats[$level] / $riskStats['total'] * 100) : 0 }}%"></div>
                            </div>
                            <span class="font-semibold">{{ $riskStats[$level] }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="mt-4 pt-4 border-t text-sm">
                    <span class="text-gray-500">Terbuka: {{ $riskStats['open'] }}</span>
                    <span class="text-green-600 ml-4">Ditangani: {{ $riskStats['treated'] }}</span>
                </div>
            </div>

            {{-- Incident Summary --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold mb-4">Ringkasan Insiden</h2>
                <div class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Terbuka</span>
                        <span class="font-semibold text-red-600">{{ $incidentStats['open'] }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Investigasi</span>
                        <span class="font-semibold text-amber-600">{{ $incidentStats['investigating'] }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Selesai</span>
                        <span class="font-semibold text-green-600">{{ $incidentStats['resolved'] }}</span>
                    </div>
                    <div class="border-t pt-3 mt-3 flex justify-between text-sm">
                        <span class="text-gray-500">Kritis/Tinggi</span>
                        <span class="font-semibold text-red-600">{{ $incidentStats['critical_high'] }}</span>
                    </div>
                </div>
            </div>

            {{-- Audit Schedule --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold mb-4">Jadwal Audit</h2>
                @if(count($upcomingAudits) > 0)
                <div class="space-y-2">
                    @foreach($upcomingAudits as $audit)
                    <div class="flex justify-between items-center text-sm border-b border-gray-100 dark:border-gray-700 pb-2">
                        <span>{{ $audit['title'] }}</span>
                        <span class="text-gray-500">{{ \Carbon\Carbon::parse($audit['planned_date'])->format('d M Y') }}</span>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-gray-400 text-sm">Tidak ada audit mendatang</p>
                @endif
                <div class="mt-4 pt-4 border-t text-sm">
                    <span class="text-gray-500">Tingkat Kelulusan: </span>
                    <span class="font-semibold text-green-600">{{ $auditStats['pass_rate'] }}%</span>
                </div>
            </div>

            {{-- Policy Compliance --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold mb-4">Kepatuhan Kebijakan</h2>
                @if(count($policyStats['compliance']) > 0)
                <div class="space-y-2">
                    @foreach(array_slice($policyStats['compliance'], 0, 5) as $pc)
                    <div class="flex justify-between text-sm border-b border-gray-100 dark:border-gray-700 pb-2">
                        <span class="truncate max-w-[60%]">{{ $pc['policy'] }}</span>
                        <span class="font-semibold @if($pc['compliance_percentage'] >= 80) text-green-600 @elseif($pc['compliance_percentage'] >= 50) text-amber-600 @else text-red-600 @endif">
                            {{ $pc['compliance_percentage'] }}%
                        </span>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-gray-400 text-sm">Belum ada kebijakan</p>
                @endif
            </div>
        </div>

        {{-- SOA Summary --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold mb-4">Statement of Applicability (SOA)</h2>
            @php
                $totalControls = count($soaSummary);
                $implemented = count(array_filter($soaSummary, fn($c) => $c['implemented'] === 'yes'));
                $partial = count(array_filter($soaSummary, fn($c) => $c['implemented'] === 'partial'));
                $notImpl = $totalControls - $implemented - $partial;
            @endphp
            <div class="grid grid-cols-3 gap-4 text-center mb-4">
                <div>
                    <div class="text-2xl font-extrabold text-green-600">{{ $implemented }}</div>
                    <div class="text-xs text-gray-500">Terimplementasi</div>
                </div>
                <div>
                    <div class="text-2xl font-extrabold text-amber-600">{{ $partial }}</div>
                    <div class="text-xs text-gray-500">Parsial</div>
                </div>
                <div>
                    <div class="text-2xl font-extrabold text-gray-400">{{ $notImpl }}</div>
                    <div class="text-xs text-gray-500">Belum</div>
                </div>
            </div>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3 overflow-hidden">
                <div class="h-3 bg-green-500 float-left" style="width: {{ $totalControls > 0 ? ($implemented / $totalControls * 100) : 0 }}%"></div>
                <div class="h-3 bg-amber-500 float-left" style="width: {{ $totalControls > 0 ? ($partial / $totalControls * 100) : 0 }}%"></div>
            </div>
            <div class="mt-2 text-xs text-gray-500 text-right">Total {{ $totalControls }} kontrol Annex A</div>
        </div>
    </div>
</x-filament-panels::page>
