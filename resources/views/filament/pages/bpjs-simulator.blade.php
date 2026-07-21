<x-filament-panels::page>
    <style>
        .sim-result { background:#f8fafc;border-radius:14px;padding:20px;border:1px solid #e2e8f0;margin-bottom:16px; }
        .sim-title { font-size:15px;font-weight:700;color:#1e293b;margin-bottom:12px;padding-bottom:8px;border-bottom:2px solid #e2e8f0; }
        .sim-row { display:flex;justify-content:space-between;padding:6px 0; }
        .sim-label { color:#64748b;font-size:13px; }
        .sim-val { font-weight:600;color:#1e293b;font-size:13px; }
        .sim-val.bold { font-weight:800;color:#4f46e5;font-size:14px; }
        .tier-badge { display:inline-block;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:700; }
        .tier-I { background:#dbeafe;color:#1d4ed8; }
        .tier-II { background:#d1fae5;color:#047857; }
        .tier-III { background:#fef3c7;color:#b45309; }
    </style>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
        <div>
            <h2 style="font-size:18px;font-weight:700;margin-bottom:16px;">Masukkan Data</h2>
            {{ $this->form }}

            <div style="margin-top:16px;">
                <x-filament::button wire:click="calculate" icon="heroicon-o-calculator" style="width:100%;">
                    Hitung Iuran BPJS
                </x-filament::button>
            </div>
        </div>

        <div>
            <h2 style="font-size:18px;font-weight:700;margin-bottom:16px;">Hasil Simulasi</h2>

            @if ($result)
                <div class="sim-result">
                    <div class="sim-row">
                        <span class="sim-label">Gaji Bulanan</span>
                        <span class="sim-val">Rp {{ number_format($result['salary']) }}</span>
                    </div>
                    <div class="sim-row">
                        <span class="sim-label">Tingkat Risiko</span>
                        <span class="sim-val">{{ $result['risk_grade'] }}</span>
                    </div>
                    <div class="sim-row">
                        <span class="sim-label">Total Pemberi Kerja</span>
                        <span class="sim-val bold">Rp {{ number_format($result['total_employer']) }}</span>
                    </div>
                    <div class="sim-row">
                        <span class="sim-label">Total Pekerja</span>
                        <span class="sim-val bold">Rp {{ number_format($result['total_employee']) }}</span>
                    </div>
                    <div class="sim-row">
                        <span class="sim-label">Grand Total Iuran</span>
                        <span class="sim-val bold">Rp {{ number_format($result['grand_total']) }}</span>
                    </div>
                </div>

                {{-- BPJS Kesehatan --}}
                <div class="sim-result">
                    <div class="sim-title">BPJS Kesehatan <span class="tier-badge tier-{{ $result['bpjs_kesehatan']['tier'] }}">Kelas {{ $result['bpjs_kesehatan']['tier'] }}</span></div>
                    <div class="sim-row"><span class="sim-label">Basis Gaji (ceiling Rp 12jt)</span><span class="sim-val">Rp {{ number_format($result['bpjs_kesehatan']['base_salary']) }}</span></div>
                    <div class="sim-row"><span class="sim-label">Pemberi Kerja (4%)</span><span class="sim-val">Rp {{ number_format($result['bpjs_kesehatan']['employer_amount']) }}</span></div>
                    <div class="sim-row"><span class="sim-label">Pekerja (1%)</span><span class="sim-val">Rp {{ number_format($result['bpjs_kesehatan']['employee_amount']) }}</span></div>
                    <div class="sim-row"><span class="sim-label">Total</span><span class="sim-val bold">Rp {{ number_format($result['bpjs_kesehatan']['total']) }}</span></div>
                </div>

                {{-- BPJS TK --}}
                <div class="sim-result">
                    <div class="sim-title">BPJS Ketenagakerjaan</div>

                    <div style="margin-bottom:10px;">
                        <div style="font-size:13px;font-weight:600;color:#475569;margin-bottom:4px;">JKK — Kecelakaan Kerja</div>
                        <div class="sim-row"><span class="sim-label">Pemberi Kerja ({{ round($result['bpjs_tk']['jkk']['employer_rate'] * 100, 2) }}%)</span><span class="sim-val">Rp {{ number_format($result['bpjs_tk']['jkk']['employer_amount']) }}</span></div>
                    </div>

                    <div style="margin-bottom:10px;">
                        <div style="font-size:13px;font-weight:600;color:#475569;margin-bottom:4px;">JKM — Kematian</div>
                        <div class="sim-row"><span class="sim-label">Pemberi Kerja (0,3%)</span><span class="sim-val">Rp {{ number_format($result['bpjs_tk']['jkm']['employer_amount']) }}</span></div>
                    </div>

                    <div style="margin-bottom:10px;">
                        <div style="font-size:13px;font-weight:600;color:#475569;margin-bottom:4px;">JHT — Hari Tua</div>
                        <div class="sim-row"><span class="sim-label">Pemberi Kerja (3,7%)</span><span class="sim-val">Rp {{ number_format($result['bpjs_tk']['jht']['employer_amount']) }}</span></div>
                        <div class="sim-row"><span class="sim-label">Pekerja (2%)</span><span class="sim-val">Rp {{ number_format($result['bpjs_tk']['jht']['employee_amount']) }}</span></div>
                    </div>

                    <div>
                        <div style="font-size:13px;font-weight:600;color:#475569;margin-bottom:4px;">JP — Pensiun</div>
                        <div class="sim-row"><span class="sim-label">Basis (ceiling Rp 12jt)</span><span class="sim-val">Rp {{ number_format($result['bpjs_tk']['jp']['base_salary']) }}</span></div>
                        <div class="sim-row"><span class="sim-label">Pemberi Kerja (2%)</span><span class="sim-val">Rp {{ number_format($result['bpjs_tk']['jp']['employer_amount']) }}</span></div>
                        <div class="sim-row"><span class="sim-label">Pekerja (1%)</span><span class="sim-val">Rp {{ number_format($result['bpjs_tk']['jp']['employee_amount']) }}</span></div>
                    </div>

                    <div style="margin-top:10px;padding-top:10px;border-top:2px solid #e2e8f0;">
                        <div class="sim-row"><span class="sim-label">Total Pemberi Kerja</span><span class="sim-val bold">Rp {{ number_format($result['bpjs_tk']['total_employer']) }}</span></div>
                        <div class="sim-row"><span class="sim-label">Total Pekerja</span><span class="sim-val bold">Rp {{ number_format($result['bpjs_tk']['total_employee']) }}</span></div>
                    </div>
                </div>
            @else
                <div style="padding:40px;text-align:center;color:#94a3b8;">
                    Klik <strong>"Hitung Iuran BPJS"</strong> untuk melihat hasil
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
