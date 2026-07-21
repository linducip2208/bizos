<x-filament-panels::page>
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    @endpush

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Dashboard Treasury</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Posisi kas, eksposur forex, portfolio investasi, dan utilisasi fasilitas bank
                </p>
            </div>
            <x-filament::button wire:click="loadData" color="gray" size="sm" outlined>
                Refresh Data
            </x-filament::button>
        </div>

        {{-- Top Stats Row --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl border p-4">
                <p class="text-xs text-gray-500">Total Kas</p>
                <p class="text-xl font-bold text-green-700">
                    {{ $cashPosition['total_formatted'] ?? 'Rp 0' }}
                </p>
                <p class="text-xs text-gray-400 mt-1">{{ $cashPosition['account_count'] ?? 0 }} rekening</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border p-4">
                <p class="text-xs text-gray-500">Total Investasi</p>
                <p class="text-xl font-bold text-indigo-700">
                    {{ $investmentPortfolio['total_invested_formatted'] ?? 'Rp 0' }}
                </p>
                <p class="text-xs text-gray-400 mt-1">{{ $investmentPortfolio['active_count'] ?? 0 }} aktif</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border p-4">
                <p class="text-xs text-gray-500">YTD Return</p>
                <p class="text-xl font-bold text-green-700">
                    {{ $investmentPortfolio['ytd_return_formatted'] ?? 'Rp 0' }}
                </p>
                <p class="text-xs text-gray-400 mt-1">avg {{ $investmentPortfolio['weighted_return'] ?? 0 }}%</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border p-4">
                <p class="text-xs text-gray-500">Current Ratio</p>
                <p class="text-xl font-bold {{ ($liquidityRatios['current_ratio'] ?? 0) >= 1.5 ? 'text-green-700' : 'text-red-700' }}">
                    {{ $liquidityRatios['current_ratio_formatted'] ?? 'N/A' }}
                </p>
                <p class="text-xs text-gray-400 mt-1">{{ $liquidityRatios['current_ratio_interpretation'] ?? '' }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border p-4">
                <p class="text-xs text-gray-500">Quick Ratio</p>
                <p class="text-xl font-bold {{ ($liquidityRatios['quick_ratio'] ?? 0) >= 1 ? 'text-green-700' : 'text-red-700' }}">
                    {{ $liquidityRatios['quick_ratio_formatted'] ?? 'N/A' }}
                </p>
                <p class="text-xs text-gray-400 mt-1">{{ $liquidityRatios['quick_ratio_interpretation'] ?? '' }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl border p-4">
                <p class="text-xs text-gray-500">Modal Kerja</p>
                <p class="text-xl font-bold text-blue-700">
                    {{ $liquidityRatios['working_capital_formatted'] ?? 'Rp 0' }}
                </p>
                <p class="text-xs text-gray-400 mt-1">D/E {{ $liquidityRatios['debt_to_equity_formatted'] ?? 'N/A' }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Cash Position Details --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border">
                <div class="p-4 border-b">
                    <h2 class="text-lg font-semibold">Posisi Kas per Rekening</h2>
                </div>
                <div class="p-4">
                    @foreach($cashPosition['accounts'] ?? [] as $account)
                        <div class="flex items-center justify-between py-2 border-b last:border-0">
                            <div>
                                <p class="text-sm font-medium">{{ $account['bank_name'] }}</p>
                                <p class="text-xs text-gray-500">{{ $account['account_number'] }} &middot; {{ $account['currency'] }}</p>
                            </div>
                            <p class="text-sm font-bold font-mono">{{ number_format($account['balance'], 2, ',', '.') }}</p>
                        </div>
                    @endforeach
                    @if(!empty($cashPosition['by_currency']))
                        <div class="mt-3 pt-3 border-t">
                            <p class="text-xs font-semibold text-gray-500 mb-1">Total per Mata Uang</p>
                            @foreach($cashPosition['by_currency'] as $byCurr)
                                <div class="flex justify-between text-xs">
                                    <span>{{ $byCurr['currency'] }}</span>
                                    <span class="font-mono">{{ number_format($byCurr['total'], 2, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Forex Exposure --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border">
                <div class="p-4 border-b">
                    <h2 class="text-lg font-semibold">Eksposur Forex</h2>
                </div>
                <div class="p-4">
                    @if(!empty($forexExposure['exposures']))
                        @foreach($forexExposure['exposures'] as $exp)
                            <div class="flex items-center justify-between py-2 border-b last:border-0">
                                <div>
                                    <p class="text-sm font-medium">{{ $exp['currency'] }} ({{ $exp['currency_name'] }})</p>
                                    <p class="text-xs text-gray-500">Rate: {{ number_format($exp['current_rate'], 2, ',', '.') }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-bold font-mono
                                        {{ $exp['exposure_direction'] === 'long' ? 'text-green-600' : ($exp['exposure_direction'] === 'short' ? 'text-red-600' : '') }}">
                                        {{ $exp['net_exposure_formatted'] }}
                                    </p>
                                    <p class="text-xs {{ $exp['unrealized_gain_loss'] >= 0 ? 'text-green-500' : 'text-red-500' }}">
                                        UGL: {{ number_format($exp['unrealized_gain_loss'], 2, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                        <div class="mt-3 pt-3 border-t">
                            <p class="text-xs font-semibold text-gray-500">Total Eksposur Net (IDR)</p>
                            <p class="text-sm font-bold">{{ $forexExposure['total_net_exposure_formatted'] ?? 'Rp 0' }}</p>
                        </div>
                    @else
                        <p class="text-sm text-gray-500 text-center py-8">Tidak ada eksposur forex</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Investment Portfolio --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border">
                <div class="p-4 border-b flex items-center justify-between">
                    <h2 class="text-lg font-semibold">Portfolio Investasi</h2>
                    <span class="text-sm text-gray-500">{{ $investmentPortfolio['total_count'] ?? 0 }} investasi</span>
                </div>
                <div class="p-4">
                    @if(!empty($investmentPortfolio['allocation_by_type']))
                        @php $totalVal = $investmentPortfolio['total_current_value'] ?? 1; @endphp
                        @foreach($investmentPortfolio['allocation_by_type'] as $alloc)
                            <div class="mb-3">
                                <div class="flex justify-between text-sm mb-1">
                                    <span>{{ $alloc['type'] }}</span>
                                    <span class="font-mono">{{ $alloc['percent'] }}% ({{ $alloc['count'] }})</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                                    <div class="bg-indigo-600 h-2.5 rounded-full" style="width: {{ $alloc['percent'] }}%"></div>
                                </div>
                                <p class="text-xs text-gray-500 mt-0.5">{{ $alloc['total_formatted'] }}</p>
                            </div>
                        @endforeach
                    @endif

                    {{-- Maturity Schedule --}}
                    <div class="mt-4 pt-4 border-t">
                        <h3 class="text-sm font-semibold mb-2">Jadwal Jatuh Tempo</h3>
                        <div class="grid grid-cols-3 gap-2">
                            <div class="text-center p-2 bg-blue-50 dark:bg-blue-900/20 rounded">
                                <p class="text-xs text-gray-500">30 Hari</p>
                                <p class="text-sm font-bold">{{ count($maturitySchedule['next_30_days'] ?? []) }}</p>
                                <p class="text-xs font-mono text-blue-600">{{ number_format($maturitySchedule['total_maturing_30'] ?? 0, 0, ',', '.') }}</p>
                            </div>
                            <div class="text-center p-2 bg-yellow-50 dark:bg-yellow-900/20 rounded">
                                <p class="text-xs text-gray-500">60 Hari</p>
                                <p class="text-sm font-bold">{{ count($maturitySchedule['next_60_days'] ?? []) }}</p>
                            </div>
                            <div class="text-center p-2 bg-green-50 dark:bg-green-900/20 rounded">
                                <p class="text-xs text-gray-500">90 Hari</p>
                                <p class="text-sm font-bold">{{ count($maturitySchedule['next_90_days'] ?? []) }}</p>
                                <p class="text-xs font-mono text-green-600">{{ number_format($maturitySchedule['total_maturing_90'] ?? 0, 0, ',', '.') }}</p>
                            </div>
                        </div>
                        @if(!empty($maturitySchedule['past_due']))
                            <div class="mt-2 p-2 bg-red-50 rounded text-xs text-red-600">
                                {{ count($maturitySchedule['past_due']) }} investasi sudah jatuh tempo
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Bank Facility Utilization --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border">
                <div class="p-4 border-b flex items-center justify-between">
                    <h2 class="text-lg font-semibold">Utilisasi Fasilitas Bank</h2>
                    <span class="text-sm text-gray-500">{{ count($facilitySummary) }} fasilitas</span>
                </div>
                <div class="p-4">
                    @foreach($facilitySummary as $fac)
                        <div class="mb-4 pb-3 border-b last:border-0 last:pb-0">
                            <div class="flex justify-between items-start mb-1">
                                <div>
                                    <p class="text-sm font-semibold">{{ $fac['bank_name'] }}</p>
                                    <p class="text-xs text-gray-500">{{ $fac['name'] }} ({{ $fac['facility_type'] }})</p>
                                </div>
                                <div class="text-right">
                                    <span class="px-2 py-0.5 rounded text-xs font-semibold
                                        {{ $fac['covenants_compliant'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $fac['covenants_compliant'] ? 'Covenant OK' : $fac['breach_count'] . ' Breach' }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex justify-between text-xs text-gray-600 mb-1">
                                <span>Limit: {{ $fac['limit'] }}</span>
                                <span>Terpakai: {{ $fac['utilized'] }}</span>
                                <span>Tersedia: {{ $fac['available'] }}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                                <div class="h-2 rounded-full
                                    {{ $fac['utilization_percent'] > 80 ? 'bg-red-500' : ($fac['utilization_percent'] > 60 ? 'bg-yellow-500' : 'bg-green-500') }}"
                                    style="width: {{ $fac['utilization_percent'] }}%"></div>
                            </div>
                            <p class="text-xs text-gray-400 mt-1">
                                Berakhir: {{ $fac['expiry_date'] }} ({{ $fac['days_to_expiry'] }} hari)
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Cash Pooling Suggestions --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border">
                <div class="p-4 border-b">
                    <h2 class="text-lg font-semibold">Saran Cash Pooling</h2>
                </div>
                <div class="p-4">
                    @if($cashPooling['pooling_possible'] ?? false)
                        <p class="text-sm text-gray-600 mb-3">
                            Total saldo: Rp {{ number_format($cashPooling['total_balance'] ?? 0, 2, ',', '.') }}
                            &middot; Target per rekening: Rp {{ number_format($cashPooling['target_per_account'] ?? 0, 2, ',', '.') }}
                        </p>
                        @if(count($cashPooling['transfer_instructions'] ?? []) > 0)
                            @foreach($cashPooling['transfer_instructions'] as $transfer)
                                <div class="flex items-center gap-2 py-2 border-b last:border-0 text-sm">
                                    <span class="text-xs font-mono bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded">{{ $transfer['amount_formatted'] }}</span>
                                    <span>{{ $transfer['from_account_name'] }}</span>
                                    <x-heroicon-o-arrow-right class="w-4 h-4 text-gray-400" />
                                    <span>{{ $transfer['to_account_name'] }}</span>
                                </div>
                            @endforeach
                        @else
                            <p class="text-sm text-gray-500 text-center py-4">Saldo sudah seimbang, tidak perlu pooling</p>
                        @endif
                    @else
                        <p class="text-sm text-gray-500 text-center py-4">{{ $cashPooling['message'] ?? 'Tidak dapat melakukan pooling' }}</p>
                    @endif
                </div>
            </div>

            {{-- Hedging Suggestions --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border">
                <div class="p-4 border-b">
                    <h2 class="text-lg font-semibold">Saran Hedging Forex</h2>
                </div>
                <div class="p-4">
                    @if(count($hedgingSuggestions['suggestions'] ?? []) > 0)
                        @foreach($hedgingSuggestions['suggestions'] as $sg)
                            <div class="p-3 bg-yellow-50 dark:bg-yellow-900/10 rounded-lg border border-yellow-200 mb-2">
                                <p class="text-sm font-semibold">{{ $sg['currency'] }}</p>
                                <p class="text-xs text-gray-600">{{ $sg['suggested_action'] }}</p>
                                <div class="flex justify-between text-xs mt-1">
                                    <span>Eksposur: Rp {{ number_format($sg['net_exposure_idr'], 2, ',', '.') }}</span>
                                    <span>Hedge {{ $sg['hedge_percent_suggested'] }}%: Rp {{ number_format($sg['hedge_amount_suggested'], 2, ',', '.') }}</span>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-sm text-gray-500 text-center py-8">{{ $hedgingSuggestions['message'] ?? 'Eksposur forex dalam batas aman' }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
