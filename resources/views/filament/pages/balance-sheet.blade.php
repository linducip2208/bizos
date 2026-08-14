<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Filter Bar --}}
        <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-800 dark:ring-white/10">
            <form method="get" class="flex flex-wrap items-end gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Per Tanggal</label>
                    <input type="date" name="as_of_date" value="{{ $asOfDate }}"
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
                </div>
            </form>
        </div>

        @php
            $bs = $balanceSheet;
            $fmt = fn($v) => 'Rp ' . number_format($v, 0, ',', '.');
        @endphp

        {{-- Balance Sheet Statement --}}
        <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-800 dark:ring-white/10">
            <div class="mb-6 text-center">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">{{ $this->getCompanyName() }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Laporan Posisi Keuangan (Neraca)</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Per {{ \Carbon\Carbon::parse($asOfDate)->translatedFormat('d F Y') }}
                    @if($this->getBranchName()) — Cabang {{ $this->getBranchName() }} @endif
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                {{-- Left: Assets --}}
                <div>
                    <h3 class="mb-3 text-base font-bold uppercase tracking-wide text-indigo-700 dark:text-indigo-400">Aset</h3>

                    <div class="mb-3">
                        <p class="mb-1 text-sm font-semibold text-gray-700 dark:text-gray-300">Aset Lancar</p>
                        @foreach($bs['assets']['current'] as $item)
                            <div class="flex items-baseline justify-between border-b border-dashed border-gray-200 py-1 text-sm dark:border-gray-700">
                                <span class="text-gray-600 dark:text-gray-400">{{ $item['name'] }}</span>
                                <span class="font-mono text-gray-800 dark:text-gray-200">{{ $fmt($item['balance']) }}</span>
                            </div>
                        @endforeach
                        <div class="flex items-baseline justify-between py-1.5 text-sm font-semibold">
                            <span class="text-gray-800 dark:text-white">Total Aset Lancar</span>
                            <span class="font-mono text-gray-800 dark:text-white">{{ $fmt($bs['total_assets_current']) }}</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <p class="mb-1 text-sm font-semibold text-gray-700 dark:text-gray-300">Aset Tetap</p>
                        @foreach($bs['assets']['non_current'] as $item)
                            <div class="flex items-baseline justify-between border-b border-dashed border-gray-200 py-1 text-sm dark:border-gray-700">
                                <span class="text-gray-600 dark:text-gray-400">{{ $item['name'] }}</span>
                                <span class="font-mono text-gray-800 dark:text-gray-200">{{ $fmt($item['balance']) }}</span>
                            </div>
                        @endforeach
                        <div class="flex items-baseline justify-between py-1.5 text-sm font-semibold">
                            <span class="text-gray-800 dark:text-white">Total Aset Tetap</span>
                            <span class="font-mono text-gray-800 dark:text-white">{{ $fmt($bs['total_assets_non_current']) }}</span>
                        </div>
                    </div>

                    <div class="mt-4 flex items-baseline justify-between rounded-lg bg-indigo-50 px-3 py-2 dark:bg-indigo-950/40">
                        <span class="text-sm font-bold text-indigo-800 dark:text-indigo-300">TOTAL ASET</span>
                        <span class="font-mono text-base font-bold text-indigo-800 dark:text-indigo-300">{{ $fmt($bs['total_assets']) }}</span>
                    </div>
                </div>

                {{-- Right: Liabilities + Equity --}}
                <div>
                    <h3 class="mb-3 text-base font-bold uppercase tracking-wide text-rose-700 dark:text-rose-400">Liabilitas &amp; Ekuitas</h3>

                    <div class="mb-3">
                        <p class="mb-1 text-sm font-semibold text-gray-700 dark:text-gray-300">Liabilitas Lancar</p>
                        @foreach($bs['liabilities']['current'] as $item)
                            <div class="flex items-baseline justify-between border-b border-dashed border-gray-200 py-1 text-sm dark:border-gray-700">
                                <span class="text-gray-600 dark:text-gray-400">{{ $item['name'] }}</span>
                                <span class="font-mono text-gray-800 dark:text-gray-200">{{ $fmt($item['balance']) }}</span>
                            </div>
                        @endforeach
                        <div class="flex items-baseline justify-between py-1.5 text-sm font-semibold">
                            <span class="text-gray-800 dark:text-white">Total Liabilitas Lancar</span>
                            <span class="font-mono text-gray-800 dark:text-white">{{ $fmt($bs['total_liabilities_current']) }}</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <p class="mb-1 text-sm font-semibold text-gray-700 dark:text-gray-300">Liabilitas Jangka Panjang</p>
                        @foreach($bs['liabilities']['non_current'] as $item)
                            <div class="flex items-baseline justify-between border-b border-dashed border-gray-200 py-1 text-sm dark:border-gray-700">
                                <span class="text-gray-600 dark:text-gray-400">{{ $item['name'] }}</span>
                                <span class="font-mono text-gray-800 dark:text-gray-200">{{ $fmt($item['balance']) }}</span>
                            </div>
                        @endforeach
                        <div class="flex items-baseline justify-between py-1.5 text-sm font-semibold">
                            <span class="text-gray-800 dark:text-white">Total Liabilitas Jangka Panjang</span>
                            <span class="font-mono text-gray-800 dark:text-white">{{ $fmt($bs['total_liabilities_non_current']) }}</span>
                        </div>
                    </div>

                    <div class="flex items-baseline justify-between py-1.5 text-sm font-semibold">
                        <span class="text-gray-800 dark:text-white">Total Liabilitas</span>
                        <span class="font-mono text-gray-800 dark:text-white">{{ $fmt($bs['total_liabilities']) }}</span>
                    </div>

                    <div class="mt-3 mb-3 border-t border-gray-300 pt-3 dark:border-gray-600">
                        <p class="mb-1 text-sm font-semibold text-gray-700 dark:text-gray-300">Ekuitas</p>
                        @foreach($bs['equity'] as $item)
                            <div class="flex items-baseline justify-between border-b border-dashed border-gray-200 py-1 text-sm dark:border-gray-700">
                                <span class="text-gray-600 dark:text-gray-400 {{ !empty($item['is_computed']) ? 'font-medium italic' : '' }}">{{ $item['name'] }}</span>
                                <span class="font-mono text-gray-800 dark:text-gray-200">{{ $fmt($item['balance']) }}</span>
                            </div>
                        @endforeach
                        <div class="flex items-baseline justify-between py-1.5 text-sm font-semibold">
                            <span class="text-gray-800 dark:text-white">Total Ekuitas</span>
                            <span class="font-mono text-gray-800 dark:text-white">{{ $fmt($bs['total_equity']) }}</span>
                        </div>
                    </div>

                    <div class="mt-4 flex items-baseline justify-between rounded-lg bg-rose-50 px-3 py-2 dark:bg-rose-950/40">
                        <span class="text-sm font-bold text-rose-800 dark:text-rose-300">TOTAL LIABILITAS &amp; EKUITAS</span>
                        <span class="font-mono text-base font-bold text-rose-800 dark:text-rose-300">{{ $fmt($bs['total_liabilities_and_equity']) }}</span>
                    </div>
                </div>
            </div>

            @if(abs($bs['total_assets'] - $bs['total_liabilities_and_equity']) > 0.01)
                <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800 dark:border-amber-800 dark:bg-amber-950/40 dark:text-amber-300">
                    Selisih: {{ $fmt($bs['total_assets'] - $bs['total_liabilities_and_equity']) }} — neraca belum seimbang.
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
