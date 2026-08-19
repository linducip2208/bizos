<x-filament-panels::page>
    <div class="command-center space-y-5">
        <section class="cc-control-rail" aria-label="Filter dashboard">
            <div class="cc-control-heading">
                <div>
                    <p class="cc-eyebrow">Business control plane</p>
                    <h2>{{ auth()->user()->company?->name ?? 'Command Center' }}</h2>
                </div>
                <div class="cc-update">
                    <span class="cc-live-dot"></span>
                    <span>{{ $lastUpdated ? 'Diperbarui ' . $lastUpdated : 'Memuat data' }}</span>
                </div>
            </div>

            <div class="cc-filter-grid">
                <label><span>Perusahaan</span><select wire:model="filters.company_id"><option value="">Pilih perusahaan</option>@foreach($this->filterOptions['companies'] as $id => $name)<option value="{{ $id }}">{{ $name }}</option>@endforeach</select></label>
                <label><span>Cabang</span><select wire:model="filters.branch_id"><option value="">Semua cabang</option>@foreach($this->filterOptions['branches'] as $id => $name)<option value="{{ $id }}">{{ $name }}</option>@endforeach</select></label>
                <label><span>Unit bisnis</span><select wire:model="filters.business_unit_id"><option value="">Semua unit</option>@foreach($this->filterOptions['business_units'] as $id => $name)<option value="{{ $id }}">{{ $name }}</option>@endforeach</select></label>
                <label><span>Departemen</span><select wire:model="filters.department_id"><option value="">Semua departemen</option>@foreach($this->filterOptions['departments'] as $id => $name)<option value="{{ $id }}">{{ $name }}</option>@endforeach</select></label>
                <label><span>Proyek</span><select wire:model="filters.project_id"><option value="">Semua proyek</option>@foreach($this->filterOptions['projects'] as $id => $name)<option value="{{ $id }}">{{ $name }}</option>@endforeach</select></label>
                <label><span>Dari</span><input type="date" wire:model="filters.date_from"></label>
                <label><span>Sampai</span><input type="date" wire:model="filters.date_to"></label>
                <label><span>Perbandingan</span><select wire:model="filters.comparison_period"><option value="previous_period">Periode sebelumnya</option><option value="previous_year">Tahun sebelumnya</option></select></label>
                <label><span>Mata uang</span><select wire:model="filters.currency">@foreach($this->filterOptions['currencies'] as $code)<option value="{{ $code }}">{{ $code }}</option>@endforeach</select></label>
                <button type="button" wire:click="applyFilters" class="cc-apply">Terapkan filter</button>
                <button type="button" wire:click="resetFilters" class="cc-button" title="Kembali ke cakupan default">Reset filter</button>
            </div>
        </section>

        <nav class="cc-tabs" aria-label="Area dashboard">
            @foreach($availableTabs as $key => $tab)
                <button type="button" wire:click="selectTab('{{ $key }}')" @class(['cc-tab', 'is-active' => $activeTab === $key]) aria-current="{{ $activeTab === $key ? 'page' : 'false' }}">
                    <x-dynamic-component :component="$tab['icon']" class="w-4 h-4" />
                    <span>{{ $tab['label'] }}</span>
                </button>
            @endforeach
        </nav>

        <div class="cc-actions">
            <div>
                <p class="cc-eyebrow">{{ $availableTabs[$activeTab]['label'] ?? 'Dashboard' }}</p>
                <p class="cc-period">{{ \Carbon\Carbon::parse($filters['date_from'])->translatedFormat('d M Y') }}—{{ \Carbon\Carbon::parse($filters['date_to'])->translatedFormat('d M Y') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if(\App\Filament\Pages\DashboardBuilder::canAccess())
                    <a href="{{ \App\Filament\Pages\DashboardBuilder::getUrl() }}" class="cc-button"><x-heroicon-o-squares-plus class="w-4 h-4" /> Personalisasi</a>
                @endif
                <button type="button" wire:click="refreshDashboard" class="cc-button"><x-heroicon-o-arrow-path class="w-4 h-4" /> Refresh</button>
                <button type="button" wire:click="exportPdf" class="cc-button"><x-heroicon-o-document-arrow-down class="w-4 h-4" /> PDF</button>
                <button type="button" wire:click="exportExcel" class="cc-button"><x-heroicon-o-table-cells class="w-4 h-4" /> Excel</button>
            </div>
        </div>

        <div wire:loading.flex wire:target="selectTab,applyFilters,refreshDashboard" class="cc-skeleton-grid" aria-label="Memuat data dashboard">
            @for($i = 0; $i < 6; $i++)<div class="cc-skeleton"></div>@endfor
        </div>

        <div wire:loading.remove wire:target="selectTab,applyFilters,refreshDashboard">
            @if($loadError)
                <div class="cc-error" role="alert"><x-heroicon-o-exclamation-triangle class="w-5 h-5" /><div><strong>Dashboard belum tersedia</strong><p>{{ $loadError }}</p></div></div>
            @elseif(empty($data))
                <div class="cc-empty"><x-heroicon-o-chart-bar-square class="w-9 h-9" /><strong>Belum ada data pada periode ini</strong><p>Ubah rentang tanggal atau filter organisasi untuk melihat data lain.</p></div>
            @else
                @php $cards = $data['kpis'] ?? $data['cards'] ?? []; @endphp
                <section class="cc-kpi-grid" aria-label="Ringkasan KPI">
                    @foreach($cards as $card)
                        <a href="{{ $card['url'] ?? '#' }}" class="cc-kpi-card">
                            <span class="cc-kpi-label">{{ $card['label'] }}</span>
                            <strong>{{ $this->formatValue($card['value'] ?? 0, $card['format'] ?? 'number') }}</strong>
                            <span @class(['cc-delta', 'is-up' => ($card['delta'] ?? 0) > 0, 'is-down' => ($card['delta'] ?? 0) < 0])>
                                @if(isset($card['delta'])){{ $card['delta'] > 0 ? '↑' : ($card['delta'] < 0 ? '↓' : '→') }} {{ abs($card['delta']) }}% vs periode lalu @else Data aktual @endif
                            </span>
                        </a>
                    @endforeach
                </section>

                @if(!empty($data['charts']))
                    <section class="cc-analytics-grid">
                        <article class="cc-panel cc-panel-wide">
                            <div class="cc-panel-title"><div><span>Arus kinerja</span><strong>Revenue vs Expense</strong></div><x-heroicon-o-chart-bar class="w-5 h-5" /></div>
                            @php
                                $rev = collect($data['charts']['revenue_expense']['revenue'] ?? []);
                                $exp = collect($data['charts']['revenue_expense']['expense'] ?? []);
                                $maxChart = max(1, (float) max($rev->max('revenue') ?? 0, $exp->max('expense') ?? 0));
                            @endphp
                            <div class="cc-chart" role="img" aria-label="Grafik revenue dan expense">
                                @forelse($rev as $point)
                                    <div class="cc-chart-column" title="{{ $point['period'] }}: {{ $this->formatValue($point['revenue'], 'currency') }}">
                                        <span class="cc-bar revenue" style="height: {{ max(5, ($point['revenue'] / $maxChart) * 100) }}%"></span><small>{{ \Carbon\Carbon::parse($point['period'])->format('d') }}</small>
                                    </div>
                                @empty <p class="cc-muted">Belum ada transaksi revenue pada periode ini.</p> @endforelse
                            </div>
                        </article>
                        <article class="cc-panel">
                            <div class="cc-panel-title"><div><span>Kontrol biaya</span><strong>Budget vs Actual</strong></div><x-heroicon-o-scale class="w-5 h-5" /></div>
                            @php $budget = (float)($data['charts']['budget_actual']['budget'] ?? 0); $actual = (float)($data['charts']['budget_actual']['actual'] ?? 0); $usage = $budget > 0 ? min(100, ($actual / $budget) * 100) : 0; @endphp
                            <div class="cc-budget"><div class="cc-budget-ring" style="--usage: {{ $usage }}"><span>{{ round($usage) }}%</span></div><div><p>Budget<br><strong>{{ $this->formatValue($budget, 'currency') }}</strong></p><p>Aktual<br><strong>{{ $this->formatValue($actual, 'currency') }}</strong></p></div></div>
                        </article>
                    </section>
                @endif

                @if(!empty($data['signals']))
                    <section class="cc-signal-grid">
                        @foreach($data['signals'] as $signal)<article class="cc-signal"><span>{{ $signal['label'] }}</span><strong>{{ $this->formatValue($signal['value'], $signal['format']) }}</strong></article>@endforeach
                    </section>
                @endif
            @endif
        </div>
    </div>
</x-filament-panels::page>
