<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Dashboard RAB</h1>
        <select wire:model.live="selectedProjectId" wire:change="selectProject($event.target.value)"
                class="px-4 py-2 border rounded-lg">
            <option value="">Pilih Proyek</option>
            @foreach($projects as $project)
                <option value="{{ $project['id'] }}" {{ $selectedProjectId == $project['id'] ? 'selected' : '' }}>
                    {{ $project['name'] }}
                </option>
            @endforeach
        </select>
    </div>

    @if($selectedProjectId && !empty($rabVsActual))
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl border p-4">
                <p class="text-sm text-gray-500">Total RAB</p>
                <p class="text-2xl font-bold text-indigo-600">Rp {{ number_format($projectTotals['rab_total'] ?? 0, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-xl border p-4">
                <p class="text-sm text-gray-500">Total Ditagihkan</p>
                <p class="text-2xl font-bold text-green-600">Rp {{ number_format($projectTotals['billings_total'] ?? 0, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-xl border p-4">
                <p class="text-sm text-gray-500">Sisa Kontrak</p>
                <p class="text-2xl font-bold text-amber-600">Rp {{ number_format(max($projectTotals['balance'] ?? 0, 0), 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-xl border p-4">
                <p class="text-sm text-gray-500">Varians (Budget vs Actual)</p>
                <p class="text-2xl font-bold {{ ($rabVsActual['variance'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    Rp {{ number_format($rabVsActual['variance'] ?? 0, 0, ',', '.') }}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
            <div class="bg-blue-50 rounded-xl border border-blue-200 p-4">
                <p class="text-sm text-blue-600 font-semibold">Material</p>
                <p class="text-xl font-bold">Rp {{ number_format($rabVsActual['material']['budget'] ?? 0, 0, ',', '.') }}</p>
            </div>
            <div class="bg-green-50 rounded-xl border border-green-200 p-4">
                <p class="text-sm text-green-600 font-semibold">Tenaga Kerja</p>
                <p class="text-xl font-bold">Rp {{ number_format($rabVsActual['labor']['budget'] ?? 0, 0, ',', '.') }}</p>
            </div>
            <div class="bg-amber-50 rounded-xl border border-amber-200 p-4">
                <p class="text-sm text-amber-600 font-semibold">Alat Berat</p>
                <p class="text-xl font-bold">Rp {{ number_format($rabVsActual['equipment']['budget'] ?? 0, 0, ',', '.') }}</p>
            </div>
            <div class="bg-red-50 rounded-xl border border-red-200 p-4">
                <p class="text-sm text-red-600 font-semibold">Subkontraktor</p>
                <p class="text-xl font-bold">Rp {{ number_format($rabVsActual['subcontract']['budget'] ?? 0, 0, ',', '.') }}</p>
            </div>
            <div class="bg-gray-50 rounded-xl border border-gray-200 p-4">
                <p class="text-sm text-gray-600 font-semibold">Overhead</p>
                <p class="text-xl font-bold">Rp {{ number_format($rabVsActual['overhead']['budget'] ?? 0, 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl border p-6">
            <h2 class="text-lg font-semibold mb-4">Riwayat Tagihan Progres</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b bg-gray-50">
                            <th class="text-left p-3">Nomor</th>
                            <th class="text-left p-3">Periode</th>
                            <th class="text-right p-3">Progres (%)</th>
                            <th class="text-right p-3">Kotor</th>
                            <th class="text-right p-3">Retensi</th>
                            <th class="text-right p-3">Bersih</th>
                            <th class="text-center p-3">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($progressBillings as $billing)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="p-3 font-medium">{{ $billing['billing_number'] }}</td>
                                <td class="p-3">{{ \Carbon\Carbon::parse($billing['billing_period_start'])->format('d M Y') }} - {{ \Carbon\Carbon::parse($billing['billing_period_end'])->format('d M Y') }}</td>
                                <td class="p-3 text-right">{{ $billing['physical_progress_percent'] }}%</td>
                                <td class="p-3 text-right">Rp {{ number_format($billing['gross_amount'], 0, ',', '.') }}</td>
                                <td class="p-3 text-right">Rp {{ number_format($billing['retention_amount'], 0, ',', '.') }}</td>
                                <td class="p-3 text-right font-semibold">Rp {{ number_format($billing['net_amount'], 0, ',', '.') }}</td>
                                <td class="p-3 text-center">
                                    @php
                                        $statusColors = [
                                            'draft' => 'bg-gray-100 text-gray-700',
                                            'submitted' => 'bg-yellow-100 text-yellow-700',
                                            'approved' => 'bg-green-100 text-green-700',
                                            'rejected' => 'bg-red-100 text-red-700',
                                            'paid' => 'bg-blue-100 text-blue-700',
                                        ];
                                        $statusLabels = [
                                            'draft' => 'Draft',
                                            'submitted' => 'Diajukan',
                                            'approved' => 'Disetujui',
                                            'rejected' => 'Ditolak',
                                            'paid' => 'Dibayar',
                                        ];
                                    @endphp
                                    <span class="px-2 py-1 rounded text-xs font-semibold {{ $statusColors[$billing['status']] ?? 'bg-gray-100' }}">
                                        {{ $statusLabels[$billing['status']] ?? $billing['status'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="p-6 text-center text-gray-400">Belum ada tagihan progres</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @elseif($selectedProjectId)
        <div class="bg-white rounded-xl border p-12 text-center text-gray-400">
            Tidak ada data RAB untuk proyek ini
        </div>
    @else
        <div class="bg-white rounded-xl border p-12 text-center text-gray-400">
            Pilih proyek untuk melihat dashboard
        </div>
    @endif
</div>
