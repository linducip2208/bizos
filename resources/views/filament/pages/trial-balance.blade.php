<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Filter Bar --}}
        <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-800 dark:ring-white/10">
            <form method="get" class="flex flex-wrap items-end gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Dari</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sampai</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cabang</label>
                    <select name="branch_id"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm">
                        <option value="">Semua Cabang</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch['id'] }}" @selected($branchId == $branch['id'])>
                                {{ $branch['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <button type="submit"
                        class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        Filter
                    </button>
                </div>
                <div class="flex gap-2 border-l border-gray-300 pl-4 dark:border-gray-600">
                    <a href="{{ $this->getExportPdfUrl() }}" target="_blank"
                        class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        PDF
                    </a>
                    <a href="{{ $this->getExportCsvUrl() }}" target="_blank"
                        class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        CSV
                    </a>
                </div>
            </form>
        </div>

        @php
            $tb = $trialBalance;
            $totals = $tb['totals'] ?? [];
            $fmt = fn($v) => 'Rp ' . number_format($v, 0, ',', '.');
        @endphp

        {{-- Trial Balance Table --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-800 dark:ring-white/10 overflow-hidden">
            <div class="border-b border-gray-200 p-6 dark:border-gray-700">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">{{ $this->getCompanyName() }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Neraca Saldo</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Periode {{ \Carbon\Carbon::parse($dateFrom)->translatedFormat('d M Y') }} — {{ \Carbon\Carbon::parse($dateTo)->translatedFormat('d M Y') }}
                    @if($this->getBranchName()) — Cabang {{ $this->getBranchName() }} @endif
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead>
                        <tr class="border-b bg-gray-50 dark:bg-gray-900/40">
                            <th class="px-4 py-3 font-semibold text-gray-700 dark:text-gray-300">Kode</th>
                            <th class="px-4 py-3 font-semibold text-gray-700 dark:text-gray-300">Nama Akun</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">Saldo Awal Debit</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">Saldo Awal Kredit</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">Debit</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">Kredit</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">Saldo Akhir Debit</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">Saldo Akhir Kredit</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($tb['groups'] as $code => $group)
                            <tr class="bg-gray-100/70 dark:bg-gray-900/60">
                                <td colspan="8" class="px-4 py-2 text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                    {{ $group['label'] }}
                                </td>
                            </tr>
                            @foreach($group['accounts'] as $row)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                    <td class="px-4 py-2 font-mono text-xs text-gray-600 dark:text-gray-400">{{ $row['code'] }}</td>
                                    <td class="px-4 py-2 text-gray-800 dark:text-gray-200">{{ $row['name'] }}</td>
                                    <td class="px-4 py-2 text-right font-mono text-gray-700 dark:text-gray-300">{{ number_format($row['opening_debit'], 0, ',', '.') }}</td>
                                    <td class="px-4 py-2 text-right font-mono text-gray-700 dark:text-gray-300">{{ number_format($row['opening_credit'], 0, ',', '.') }}</td>
                                    <td class="px-4 py-2 text-right font-mono text-gray-700 dark:text-gray-300">{{ number_format($row['movement_debit'], 0, ',', '.') }}</td>
                                    <td class="px-4 py-2 text-right font-mono text-gray-700 dark:text-gray-300">{{ number_format($row['movement_credit'], 0, ',', '.') }}</td>
                                    <td class="px-4 py-2 text-right font-mono font-semibold text-gray-800 dark:text-gray-200">{{ number_format($row['closing_debit'], 0, ',', '.') }}</td>
                                    <td class="px-4 py-2 text-right font-mono font-semibold text-gray-800 dark:text-gray-200">{{ number_format($row['closing_credit'], 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-gray-400 bg-gray-50 dark:border-gray-500 dark:bg-gray-900/40">
                            <td colspan="2" class="px-4 py-3 font-bold text-gray-900 dark:text-white">TOTAL</td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-gray-900 dark:text-white">{{ number_format($totals['opening_debit'] ?? 0, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-gray-900 dark:text-white">{{ number_format($totals['opening_credit'] ?? 0, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-gray-900 dark:text-white">{{ number_format($totals['movement_debit'] ?? 0, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-gray-900 dark:text-white">{{ number_format($totals['movement_credit'] ?? 0, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-gray-900 dark:text-white">{{ number_format($totals['closing_debit'] ?? 0, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-gray-900 dark:text-white">{{ number_format($totals['closing_credit'] ?? 0, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="border-t border-gray-200 p-4 dark:border-gray-700">
                @if(!empty($tb['balanced']))
                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">Neraca Seimbang</span>
                @else
                    <span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">Neraca Belum Seimbang</span>
                @endif
            </div>
        </div>
    </div>
</x-filament-panels::page>
