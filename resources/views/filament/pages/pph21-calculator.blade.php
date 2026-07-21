<x-filament-panels::page>
    <style>
        .calc-result { background: #f8fafc; border-radius: 14px; padding: 20px; border: 1px solid #e2e8f0; }
        .result-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e2e8f0; }
        .result-row:last-child { border-bottom: none; }
        .result-label { color: #64748b; font-size: 13px; }
        .result-value { font-weight: 700; color: #1e293b; font-size: 13px; }
        .result-value.highlight { color: #4f46e5; font-size: 15px; }
    </style>

    {{-- Tab Switcher --}}
    <div style="display:flex;gap:8px;margin-bottom:24px;border-bottom:2px solid #e2e8f0;padding-bottom:0;">
        @foreach ([
            'monthly' => 'PPh 21 TER Bulanan',
            'yearly' => 'Rekonsiliasi Tahunan',
            'grossup' => 'Gross-Up',
            'table' => 'Tabel TER',
        ] as $tab => $label)
            <button wire:click="setTab('{{ $tab }}')"
                style="padding:10px 20px;border:none;background:none;border-bottom:3px solid {{ $activeTab === $tab ? '#4f46e5' : 'transparent' }};color:{{ $activeTab === $tab ? '#4f46e5' : '#64748b' }};font-weight:{{ $activeTab === $tab ? '700' : '500' }};font-size:14px;cursor:pointer;">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- Tab: Bulanan --}}
    @if ($activeTab === 'monthly')
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
            <div>
                <h2 style="font-size:18px;font-weight:700;margin-bottom:16px;">Masukkan Data</h2>
                {{ $this->form }}

                <div style="margin-top:16px;">
                    <x-filament::button wire:click="calculate" icon="heroicon-o-calculator" style="width:100%;">
                        Hitung PPh 21 TER
                    </x-filament::button>
                </div>
            </div>

            <div>
                <h2 style="font-size:18px;font-weight:700;margin-bottom:16px;">Hasil Perhitungan</h2>

                @if ($result)
                    <div class="calc-result">
                        <div class="result-row">
                            <span class="result-label">Kategori TER</span>
                            <span class="result-value highlight">{{ $result['ter_category'] }}</span>
                        </div>
                        <div class="result-row">
                            <span class="result-label">Tarif TER</span>
                            <span class="result-value highlight">{{ $result['ter_rate_percent'] }}%</span>
                        </div>
                        <div class="result-row">
                            <span class="result-label">Gaji Bruto Bulanan</span>
                            <span class="result-value">Rp {{ number_format($result['gross_monthly']) }}</span>
                        </div>
                        <div class="result-row">
                            <span class="result-label">PTKP Setahun</span>
                            <span class="result-value">Rp {{ number_format($result['ptkp_amount']) }}</span>
                        </div>
                        <div class="result-row">
                            <span class="result-label">PKP Bulanan (estimasi)</span>
                            <span class="result-value">Rp {{ number_format($result['pkp_amount']) }}</span>
                        </div>
                        <div class="result-row">
                            <span class="result-label">PPh 21 TER Bulanan</span>
                            <span class="result-value highlight">Rp {{ number_format($result['pph21_amount']) }}</span>
                        </div>
                        <div class="result-row">
                            <span class="result-label">Take Home Pay</span>
                            <span class="result-value highlight">Rp {{ number_format($result['gross_monthly'] - $result['pph21_amount']) }}</span>
                        </div>
                    </div>

                    <div style="margin-top:12px;padding:12px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;font-size:12px;color:#166534;">
                        <strong>Keterangan:</strong> {{ $result['details']['ptkp_description'] }}<br>
                        <strong>Alasan Kategori:</strong> {{ $result['details']['ter_category_reason'] }}<br>
                        <strong>Rumus:</strong> {{ $result['details']['calculation'] }}
                    </div>
                @else
                    <div style="padding:40px;text-align:center;color:#94a3b8;">
                        <svg style="width:48px;height:48px;margin:0 auto 12px;opacity:.3;" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75V18m-7.5-6.75h.008v.008H8.25v-.008zm0 2.25h.008v.008H8.25V13.5zm0 2.25h.008v.008H8.25v-.008zm0 2.25h.008v.008H8.25V18zm2.498-6.75h.007v.008h-.007v-.008zm0 2.25h.007v.008h-.007V13.5zm0 2.25h.007v.008h-.007v-.008zm0 2.25h.007v.008h-.007V18zm2.504-6.75h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V13.5zm0 2.25h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V18zm2.498-6.75h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V13.5z"/></svg>
                        Klik <strong>"Hitung PPh 21 TER"</strong> untuk melihat hasil
                    </div>
                @endif
            </div>
        </div>

    {{-- Tab: Rekonsiliasi Tahunan --}}
    @elseif ($activeTab === 'yearly')
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
            <div>
                <h2 style="font-size:18px;font-weight:700;margin-bottom:16px;">Data Rekonsiliasi</h2>
                {{ $this->form }}

                <div style="margin-top:16px;">
                    <x-filament::button wire:click="calculateReconciliation" icon="heroicon-o-arrows-right-left" style="width:100%;">
                        Hitung Rekonsiliasi
                    </x-filament::button>
                </div>
            </div>

            <div>
                <h2 style="font-size:18px;font-weight:700;margin-bottom:16px;">Hasil Rekonsiliasi</h2>

                @if ($reconciliationResult)
                    <div class="calc-result">
                        <div class="result-row">
                            <span class="result-label">Penghasilan Bruto Setahun</span>
                            <span class="result-value">Rp {{ number_format($reconciliationResult['yearly_gross_salary']) }}</span>
                        </div>
                        <div class="result-row">
                            <span class="result-label">PTKP ({{ $reconciliationResult['ptkp_code'] }})</span>
                            <span class="result-value">Rp {{ number_format($reconciliationResult['ptkp_amount']) }}</span>
                        </div>
                        <div class="result-row">
                            <span class="result-label">PKP</span>
                            <span class="result-value">Rp {{ number_format($reconciliationResult['yearly_pkp']) }}</span>
                        </div>
                        <div class="result-row">
                            <span class="result-label">PPh 21 Terutang (Pasal 17)</span>
                            <span class="result-value">Rp {{ number_format($reconciliationResult['yearly_pph21_terutang']) }}</span>
                        </div>
                        <div class="result-row">
                            <span class="result-label">PPh 21 TER Sudah Dibayar</span>
                            <span class="result-value">Rp {{ number_format($reconciliationResult['total_pph21_ter_paid']) }}</span>
                        </div>
                        <div class="result-row">
                            <span class="result-label">Status</span>
                            <span class="result-value highlight" style="color:{{ $reconciliationResult['status'] === 'kurang_bayar' ? '#ef4444' : ($reconciliationResult['status'] === 'lebih_bayar' ? '#10b981' : '#6b7280') }};">
                                {{ $reconciliationResult['status_label'] }}
                            </span>
                        </div>
                        <div class="result-row">
                            <span class="result-label">Selisih</span>
                            <span class="result-value highlight">Rp {{ number_format($reconciliationResult['selisih']) }}</span>
                        </div>
                    </div>

                    @if (!empty($reconciliationResult['pasal17_breakdown']))
                        <h3 style="font-size:15px;font-weight:700;margin-top:16px;margin-bottom:8px;">Rincian Pasal 17</h3>
                        <div style="overflow-x:auto;">
                            <table style="width:100%;font-size:12px;border-collapse:collapse;">
                                <thead>
                                    <tr style="background:#f1f5f9;">
                                        <th style="padding:8px;text-align:left;border:1px solid #e2e8f0;">Lapisan</th>
                                        <th style="padding:8px;text-align:left;border:1px solid #e2e8f0;">Rentang</th>
                                        <th style="padding:8px;text-align:right;border:1px solid #e2e8f0;">Penghasilan Kena Pajak</th>
                                        <th style="padding:8px;text-align:center;border:1px solid #e2e8f0;">Tarif</th>
                                        <th style="padding:8px;text-align:right;border:1px solid #e2e8f0;">PPh</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($reconciliationResult['pasal17_breakdown'] as $layer)
                                        <tr>
                                            <td style="padding:6px 8px;border:1px solid #e2e8f0;">{{ $layer['layer'] }}</td>
                                            <td style="padding:6px 8px;border:1px solid #e2e8f0;">{{ $layer['range'] }}</td>
                                            <td style="padding:6px 8px;border:1px solid #e2e8f0;text-align:right;">Rp {{ number_format($layer['taxable']) }}</td>
                                            <td style="padding:6px 8px;border:1px solid #e2e8f0;text-align:center;">{{ $layer['rate_percent'] }}</td>
                                            <td style="padding:6px 8px;border:1px solid #e2e8f0;text-align:right;">Rp {{ number_format($layer['tax']) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                @else
                    <div style="padding:40px;text-align:center;color:#94a3b8;">
                        Klik <strong>"Hitung Rekonsiliasi"</strong> untuk melihat hasil
                    </div>
                @endif
            </div>
        </div>

    {{-- Tab: Gross-Up --}}
    @elseif ($activeTab === 'grossup')
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
            <div>
                <h2 style="font-size:18px;font-weight:700;margin-bottom:16px;">Target Take Home Pay</h2>
                {{ $this->form }}

                <div style="margin-top:16px;">
                    <x-filament::button wire:click="calculateGrossUp" icon="heroicon-o-arrow-trending-up" style="width:100%;">
                        Hitung Gross-Up
                    </x-filament::button>
                </div>
            </div>

            <div>
                <h2 style="font-size:18px;font-weight:700;margin-bottom:16px;">Hasil Gross-Up</h2>

                @if ($grossUpResult)
                    <div class="calc-result">
                        <div class="result-row">
                            <span class="result-label">Take Home Pay Diinginkan</span>
                            <span class="result-value">Rp {{ number_format($grossUpResult['desired_take_home']) }}</span>
                        </div>
                        <div class="result-row">
                            <span class="result-label">Gaji Bruto Diperlukan</span>
                            <span class="result-value highlight">Rp {{ number_format($grossUpResult['required_gross']) }}</span>
                        </div>
                        <div class="result-row">
                            <span class="result-label">Kategori TER</span>
                            <span class="result-value">{{ $grossUpResult['ter_category'] }}</span>
                        </div>
                        <div class="result-row">
                            <span class="result-label">Tarif TER</span>
                            <span class="result-value">{{ $grossUpResult['ter_rate_percent'] }}%</span>
                        </div>
                        <div class="result-row">
                            <span class="result-label">PPh 21 TER Bulanan</span>
                            <span class="result-value">Rp {{ number_format($grossUpResult['pph21_amount']) }}</span>
                        </div>
                        <div class="result-row">
                            <span class="result-label">Take Home Pay Aktual</span>
                            <span class="result-value highlight">Rp {{ number_format($grossUpResult['actual_take_home']) }}</span>
                        </div>
                    </div>
                @else
                    <div style="padding:40px;text-align:center;color:#94a3b8;">
                        Klik <strong>"Hitung Gross-Up"</strong> untuk melihat hasil
                    </div>
                @endif
            </div>
        </div>

    {{-- Tab: Tabel TER --}}
    @elseif ($activeTab === 'table')
        <div>
            @php $categories = ['A', 'B', 'C']; @endphp
            @foreach ($categories as $cat)
                <h3 style="font-size:16px;font-weight:700;margin-bottom:12px;margin-top:24px;">
                    TER {{ $cat }} — {{ $cat === 'A' ? 'TK/0, TK/1, K/0' : ($cat === 'B' ? 'TK/2, TK/3, K/1, K/2' : 'K/3') }}
                </h3>
                <div style="overflow-x:auto;">
                    <table style="width:100%;font-size:12px;border-collapse:collapse;">
                        <thead>
                            <tr style="background:#f1f5f9;">
                                <th style="padding:8px;text-align:center;border:1px solid #e2e8f0;">#</th>
                                <th style="padding:8px;text-align:left;border:1px solid #e2e8f0;">Dari (Rp)</th>
                                <th style="padding:8px;text-align:left;border:1px solid #e2e8f0;">Sampai (Rp)</th>
                                <th style="padding:8px;text-align:center;border:1px solid #e2e8f0;">Tarif</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($this->getTerBrackets($cat) as $i => $b)
                                @php
                                    $from = $b[0];
                                    $to = $b[1] >= 99999999999 ? '&infin;' : $b[1];
                                    $rate = $b[2] * 100;
                                @endphp
                                <tr style="{{ $i % 2 === 0 ? 'background:#f8fafc;' : '' }}{{ $rate === 0.0 ? 'color:#10b981;' : '' }}">
                                    <td style="padding:6px 8px;border:1px solid #e2e8f0;text-align:center;">{{ $i + 1 }}</td>
                                    <td style="padding:6px 8px;border:1px solid #e2e8f0;">{{ number_format($from) }}</td>
                                    <td style="padding:6px 8px;border:1px solid #e2e8f0;">{!! is_string($to) ? $to : number_format($to) !!}</td>
                                    <td style="padding:6px 8px;border:1px solid #e2e8f0;text-align:center;font-weight:700;">{!! rtrim(rtrim(number_format($rate, 2), '0'), '.') !!}%</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        </div>
    @endif
</x-filament-panels::page>
