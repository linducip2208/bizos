<x-filament-panels::page>
    <style>
        .tax-result { background:#f8fafc;border-radius:14px;padding:20px;border:1px solid #e2e8f0;margin-bottom:16px; }
        .tax-row { display:flex;justify-content:space-between;padding:6px 0; }
        .tax-label { color:#64748b;font-size:13px; }
        .tax-val { font-weight:600;color:#1e293b;font-size:13px; }
        .tax-val.highlight { font-weight:800;color:#4f46e5;font-size:15px; }
        .tax-val.success { color:#059669; }
        .tax-val.danger { color:#ef4444; }
        .npwp-valid { background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px; }
        .npwp-invalid { background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:12px; }
    </style>

    {{-- Tab --}}
    <div style="display:flex;gap:8px;margin-bottom:24px;border-bottom:2px solid #e2e8f0;padding-bottom:0;">
        @foreach (['generate' => 'Generate Faktur', 'npwp' => 'Validasi NPWP', 'pph' => 'Kalkulator PPh'] as $tab => $label)
            <button wire:click="setTab('{{ $tab }}')"
                style="padding:10px 20px;border:none;background:none;border-bottom:3px solid {{ $activeTab === $tab ? '#4f46e5' : 'transparent' }};color:{{ $activeTab === $tab ? '#4f46e5' : '#64748b' }};font-weight:{{ $activeTab === $tab ? '700' : '500' }};font-size:14px;cursor:pointer;">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- Tab: Generate Faktur --}}
    @if ($activeTab === 'generate')
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
            <div>
                <h2 style="font-size:18px;font-weight:700;margin-bottom:16px;">Generate Nomor Faktur Pajak</h2>
                {{ $this->generateForm }}

                <x-filament::button wire:click="generateInvoiceNumber" icon="heroicon-o-document-plus" style="width:100%;margin-top:16px;">
                    Generate Nomor Faktur
                </x-filament::button>
            </div>

            <div>
                <h2 style="font-size:18px;font-weight:700;margin-bottom:16px;">Hasil</h2>

                @if ($generatedInvoice)
                    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:14px;padding:24px;text-align:center;">
                        <div style="font-size:12px;color:#059669;font-weight:600;margin-bottom:8px;">NOMOR FAKTUR PAJAK</div>
                        <div style="font-size:28px;font-weight:800;color:#047857;word-break:break-all;">{{ $generatedInvoice }}</div>
                        <div style="margin-top:12px;font-size:12px;color:#6b7280;">Format: KodeTransaksi.KodeStatus.Prefix-Tahun.NoSeri</div>
                    </div>
                @else
                    <div style="padding:40px;text-align:center;color:#94a3b8;">
                        Klik <strong>"Generate Nomor Faktur"</strong>
                    </div>
                @endif
            </div>
        </div>

    {{-- Tab: Validasi NPWP --}}
    @elseif ($activeTab === 'npwp')
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
            <div>
                <h2 style="font-size:18px;font-weight:700;margin-bottom:16px;">Validasi NPWP</h2>
                <p style="font-size:13px;color:#64748b;margin-bottom:16px;">Masukkan 15 digit NPWP. Format: XX.XXX.XXX.X-XXX.XXX</p>
                {{ $this->npwpForm }}

                <x-filament::button wire:click="validateNpwp" icon="heroicon-o-check-circle" style="width:100%;margin-top:16px;">
                    Validasi NPWP
                </x-filament::button>
            </div>

            <div>
                <h2 style="font-size:18px;font-weight:700;margin-bottom:16px;">Hasil Validasi</h2>

                @if ($npwpValidationResult)
                    <div class="{{ $npwpValidationResult['valid'] ? 'npwp-valid' : 'npwp-invalid' }}">
                        <div style="font-weight:700;font-size:15px;margin-bottom:8px;">
                            {{ $npwpValidationResult['valid'] ? 'NPWP Valid' : 'NPWP Tidak Valid' }}
                        </div>
                        <div class="tax-row"><span class="tax-label">NPWP</span><span class="tax-val">{{ $npwpValidationResult['npwp_formatted'] }}</span></div>
                        <div class="tax-row"><span class="tax-label">Tipe WP</span><span class="tax-val">{{ $npwpValidationResult['type_description'] }}</span></div>
                        <div class="tax-row"><span class="tax-label">Kode KPP</span><span class="tax-val">{{ $npwpValidationResult['kpp_code'] }}</span></div>
                        <div class="tax-row"><span class="tax-label">Kode Cabang</span><span class="tax-val">{{ $npwpValidationResult['branch_code'] }}</span></div>
                        <div class="tax-row"><span class="tax-label">Pusat/Cabang</span><span class="tax-val">{{ $npwpValidationResult['is_head_office'] ? 'Kantor Pusat' : 'Kantor Cabang' }}</span></div>
                        <div style="margin-top:8px;font-size:12px;color:{{ $npwpValidationResult['valid'] ? '#059669' : '#dc2626' }};">
                            {{ $npwpValidationResult['message'] }}
                        </div>
                    </div>
                @else
                    <div style="padding:40px;text-align:center;color:#94a3b8;">Klik <strong>"Validasi NPWP"</strong></div>
                @endif
            </div>
        </div>

    {{-- Tab: Kalkulator PPh --}}
    @elseif ($activeTab === 'pph')
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
            <div>
                <h2 style="font-size:18px;font-weight:700;margin-bottom:16px;">Data Pajak</h2>
                {{ $this->pphForm }}

                <x-filament::button wire:click="calculatePph" icon="heroicon-o-calculator" style="width:100%;margin-top:16px;">
                    Hitung PPh
                </x-filament::button>
            </div>

            <div>
                <h2 style="font-size:18px;font-weight:700;margin-bottom:16px;">Hasil Perhitungan</h2>

                @if ($pph23Result)
                    <div class="tax-result">
                        <div class="tax-row"><span class="tax-label">Jenis PPh</span><span class="tax-val">{{ $pph23Result['pph_article'] }} — {{ $pph23Result['label'] }}</span></div>
                        <div class="tax-row"><span class="tax-label">DPP</span><span class="tax-val">Rp {{ number_format($pph23Result['dpp_value']) }}</span></div>
                        <div class="tax-row"><span class="tax-label">Tarif</span><span class="tax-val">{{ $pph23Result['rate_percent'] }}</span></div>
                        @if ($pph23Result['has_npwp_penalty'])
                            <div style="padding:8px;background:#fef2f2;border-radius:6px;margin-top:8px;font-size:12px;color:#dc2626;">
                                {{ $pph23Result['npwp_penalty_note'] }}
                            </div>
                        @endif
                        <div class="tax-row" style="margin-top:12px;padding-top:8px;border-top:2px solid #e2e8f0;">
                            <span class="tax-label">PPh Terutang</span>
                            <span class="tax-val highlight">Rp {{ number_format($pph23Result['pph_amount']) }}</span>
                        </div>
                    </div>
                @endif

                @if ($pph4Result)
                    <div class="tax-result">
                        <div class="tax-row"><span class="tax-label">Jenis PPh</span><span class="tax-val">{{ $pph4Result['pph_article'] }}</span></div>
                        <div class="tax-row"><span class="tax-label">Transaksi</span><span class="tax-val">{{ $pph4Result['label'] }}</span></div>
                        <div class="tax-row"><span class="tax-label">DPP</span><span class="tax-val">Rp {{ number_format($pph4Result['dpp_value']) }}</span></div>
                        <div class="tax-row"><span class="tax-label">Tarif</span><span class="tax-val">{{ $pph4Result['rate_percent'] }}</span></div>
                        <div class="tax-row" style="margin-top:12px;padding-top:8px;border-top:2px solid #e2e8f0;">
                            <span class="tax-label">PPh Terutang (Final)</span>
                            <span class="tax-val highlight">Rp {{ number_format($pph4Result['pph_amount']) }}</span>
                        </div>
                    </div>
                @endif

                @if ($pph26Result)
                    <div class="tax-result">
                        <div class="tax-row"><span class="tax-label">Jenis PPh</span><span class="tax-val">{{ $pph26Result['pph_article'] }}</span></div>
                        <div class="tax-row"><span class="tax-label">DPP</span><span class="tax-val">Rp {{ number_format($pph26Result['dpp_value']) }}</span></div>
                        <div class="tax-row"><span class="tax-label">Tarif Default</span><span class="tax-val">{{ $pph26Result['default_rate_percent'] }}</span></div>
                        @if ($pph26Result['tax_treaty_country'])
                            <div class="tax-row"><span class="tax-label">Tax Treaty Rate</span><span class="tax-val {{ $pph26Result['has_treaty_benefit'] ? 'success' : 'danger' }}">{{ $pph26Result['effective_rate_percent'] }}</span></div>
                        @endif
                        <div style="padding:8px;background:#eff6ff;border-radius:6px;margin-top:8px;font-size:12px;color:#1d4ed8;">
                            {{ $pph26Result['note'] }}
                        </div>
                        <div class="tax-row" style="margin-top:12px;padding-top:8px;border-top:2px solid #e2e8f0;">
                            <span class="tax-label">PPh Terutang</span>
                            <span class="tax-val highlight">Rp {{ number_format($pph26Result['pph_amount']) }}</span>
                        </div>
                    </div>
                @endif

                @if (!$pph23Result && !$pph4Result && !$pph26Result)
                    <div style="padding:40px;text-align:center;color:#94a3b8;">Klik <strong>"Hitung PPh"</strong></div>
                @endif
            </div>
        </div>
    @endif
</x-filament-panels::page>
