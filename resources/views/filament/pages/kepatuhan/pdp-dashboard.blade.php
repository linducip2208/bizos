<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold tracking-tight">Dashboard Kepatuhan PDP</h1>
            <span class="text-sm text-gray-500">UU No. 27/2022 — Perlindungan Data Pribadi</span>
        </div>

        {{-- Compliance Score --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <div class="text-sm text-gray-500 dark:text-gray-400">Skor Kepatuhan</div>
                <div class="mt-2 flex items-end gap-2">
                    <span class="text-4xl font-extrabold @if($complianceReport['overall_score'] >= 80) text-green-600 @elseif($complianceReport['overall_score'] >= 50) text-amber-600 @else text-red-600 @endif">
                        {{ $complianceReport['overall_score'] }}%
                    </span>
                </div>
                <div class="mt-2 w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    <div class="h-2 rounded-full @if($complianceReport['overall_score'] >= 80) bg-green-500 @elseif($complianceReport['overall_score'] >= 50) bg-amber-500 @else bg-red-500 @endif" style="width: {{ $complianceReport['overall_score'] }}%"></div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <div class="text-sm text-gray-500 dark:text-gray-400">Persetujuan Aktif</div>
                <div class="mt-2">
                    <span class="text-4xl font-extrabold text-primary-600">{{ $consentStats['active'] }}</span>
                    <span class="text-sm text-gray-400"> / {{ $consentStats['total'] }} total</span>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <div class="text-sm text-gray-500 dark:text-gray-400">Pelanggaran Data</div>
                <div class="mt-2">
                    <span class="text-4xl font-extrabold text-red-600">{{ $breachStats['open'] }}</span>
                    <span class="text-sm text-gray-400"> terbuka</span>
                </div>
                @if($lateNotifications > 0)
                <div class="mt-1 text-xs text-red-500">⚠ {{ $lateNotifications }} terlambat notifikasi (72 jam)</div>
                @endif
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <div class="text-sm text-gray-500 dark:text-gray-400">DPIA Pending</div>
                <div class="mt-2">
                    <span class="text-4xl font-extrabold text-amber-600">{{ $dpiaStats['draft'] + $dpiaStats['in_review'] }}</span>
                    <span class="text-sm text-gray-400"> / {{ $dpiaStats['total'] }}</span>
                </div>
            </div>
        </div>

        {{-- Detail Cards --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Consent Coverage --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold mb-4">Cakupan Persetujuan</h2>
                <div class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Persetujuan Aktif</span>
                        <span class="font-semibold text-green-600">{{ $consentStats['active'] }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Ditarik</span>
                        <span class="font-semibold text-red-600">{{ $consentStats['withdrawn'] }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Kedaluwarsa</span>
                        <span class="font-semibold text-amber-600">{{ $consentStats['expired'] }}</span>
                    </div>
                    <div class="border-t pt-3 mt-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Cakupan</span>
                            <span class="font-semibold">{{ $complianceReport['consent_coverage']['coverage_percentage'] }}%</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Breach Status --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold mb-4">Status Pelanggaran</h2>
                <div class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Terbuka</span>
                        <span class="font-semibold text-red-600">{{ $breachStats['open'] }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Investigasi</span>
                        <span class="font-semibold text-amber-600">{{ $breachStats['investigating'] }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Terselesaikan</span>
                        <span class="font-semibold text-green-600">{{ $breachStats['resolved'] }}</span>
                    </div>
                    <div class="border-t pt-3 mt-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Total Insiden</span>
                            <span class="font-semibold">{{ $breachStats['total'] }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- DPIA Overview --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold mb-4">Status DPIA</h2>
                <div class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Draft</span>
                        <span class="font-semibold">{{ $dpiaStats['draft'] }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Dalam Review</span>
                        <span class="font-semibold text-amber-600">{{ $dpiaStats['in_review'] }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Disetujui</span>
                        <span class="font-semibold text-green-600">{{ $dpiaStats['approved'] }}</span>
                    </div>
                </div>
            </div>

            {{-- Erasure Requests --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="text-lg font-semibold mb-4">Permintaan Penghapusan Data</h2>
                <div class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Pending</span>
                        <span class="font-semibold text-amber-600">{{ $erasureStats['pending'] }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Selesai</span>
                        <span class="font-semibold text-green-600">{{ $erasureStats['completed'] }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Total</span>
                        <span class="font-semibold">{{ $erasureStats['total'] }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent Breaches Table --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold mb-4">Pelanggaran Terbaru</h2>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b text-left text-gray-500 text-xs uppercase tracking-wider">
                        <th class="pb-3">Tipe</th>
                        <th class="pb-3">Deskripsi</th>
                        <th class="pb-3">Keparahan</th>
                        <th class="pb-3">Status</th>
                        <th class="pb-3">Ditemukan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentBreaches as $breach)
                    <tr class="border-b border-gray-100 dark:border-gray-700">
                        <td class="py-3">{{ $breach['breach_type'] }}</td>
                        <td class="py-3">{{ Str::limit($breach['description'], 50) }}</td>
                        <td class="py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                                @if($breach['severity'] === 'critical') bg-red-100 text-red-800
                                @elseif($breach['severity'] === 'high') bg-amber-100 text-amber-800
                                @elseif($breach['severity'] === 'medium') bg-blue-100 text-blue-800
                                @else bg-green-100 text-green-800 @endif">
                                {{ $breach['severity'] }}
                            </span>
                        </td>
                        <td class="py-3">{{ $breach['status'] }}</td>
                        <td class="py-3 text-gray-500">{{ \Carbon\Carbon::parse($breach['discovered_at'])->format('d M Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-gray-400">Tidak ada pelanggaran data tercatat</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
