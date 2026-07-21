<x-filament-panels::page>
    <style>
        .thr-result { background:#f8fafc;border-radius:14px;padding:20px;border:1px solid #e2e8f0;margin-bottom:16px; }
        .thr-row { display:flex;justify-content:space-between;padding:6px 0; }
        .thr-label { color:#64748b;font-size:13px; }
        .thr-val { font-weight:600;color:#1e293b;font-size:13px; }
        .thr-val.highlight { font-weight:800;color:#4f46e5;font-size:15px; }
        table.thr-table { width:100%;border-collapse:collapse;font-size:12px; }
        table.thr-table th { background:#f1f5f9;padding:8px;text-align:left;border:1px solid #e2e8f0;font-weight:700; }
        table.thr-table td { padding:6px 8px;border:1px solid #e2e8f0; }
    </style>

    {{-- Tab --}}
    <div style="display:flex;gap:8px;margin-bottom:24px;border-bottom:2px solid #e2e8f0;padding-bottom:0;">
        @foreach (['single' => 'Per Karyawan', 'batch' => 'Seluruh Karyawan'] as $tab => $label)
            <button wire:click="setTab('{{ $tab }}')"
                style="padding:10px 20px;border:none;background:none;border-bottom:3px solid {{ $activeTab === $tab ? '#4f46e5' : 'transparent' }};color:{{ $activeTab === $tab ? '#4f46e5' : '#64748b' }};font-weight:{{ $activeTab === $tab ? '700' : '500' }};font-size:14px;cursor:pointer;">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- Form Umum --}}
    <div style="margin-bottom:24px;">
        {{ $this->form }}

        <div style="display:flex;gap:12px;margin-top:16px;">
            @if ($activeTab === 'single')
                <x-filament::button wire:click="calculateSingle" icon="heroicon-o-calculator" style="flex:1;">
                    Hitung THR
                </x-filament::button>
            @else
                <x-filament::button wire:click="calculateBatch" icon="heroicon-o-users" style="flex:1;" color="warning">
                    Hitung THR Semua Karyawan
                </x-filament::button>
            @endif
        </div>
    </div>

    {{-- Hasil Single --}}
    @if ($activeTab === 'single' && $result)
        <h2 style="font-size:18px;font-weight:700;margin-bottom:16px;">Hasil Perhitungan THR</h2>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
            <div class="thr-result">
                <div class="thr-row"><span class="thr-label">Nama Karyawan</span><span class="thr-val highlight">{{ $result['employee_name'] }}</span></div>
                <div class="thr-row"><span class="thr-label">Kode</span><span class="thr-val">{{ $result['employee_code'] }}</span></div>
                <div class="thr-row"><span class="thr-label">Tipe</span><span class="thr-val">{{ $result['employee_type'] }}</span></div>
                <div class="thr-row"><span class="thr-label">Tanggal Masuk</span><span class="thr-val">{{ $result['join_date'] }}</span></div>
                <div class="thr-row"><span class="thr-label">Masa Kerja</span><span class="thr-val">{{ $result['months_worked'] }} bulan</span></div>
                <div class="thr-row"><span class="thr-label">Gaji Bulanan</span><span class="thr-val">Rp {{ number_format($result['monthly_salary']) }}</span></div>
            </div>

            <div class="thr-result">
                <div class="thr-row"><span class="thr-label">Jumlah THR</span><span class="thr-val highlight">Rp {{ number_format($result['thr_amount']) }}</span></div>
                <div class="thr-row"><span class="thr-label">Jatuh Tempo</span><span class="thr-val">{{ $result['due_date'] }}</span></div>
                <div style="margin-top:12px;padding:12px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;font-size:12px;color:#166534;">
                    <strong>Rincian:</strong> {{ $result['calculation_detail'] }}
                </div>
            </div>
        </div>
    @endif

    {{-- Hasil Batch --}}
    @if ($activeTab === 'batch' && $batchResult)
        <h2 style="font-size:18px;font-weight:700;margin-bottom:16px;">THR Seluruh Karyawan</h2>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:24px;">
            <div style="background:#eef2ff;border-radius:12px;padding:16px;text-align:center;">
                <div style="font-size:12px;color:#6366f1;font-weight:600;">Total Karyawan</div>
                <div style="font-size:28px;font-weight:800;color:#4f46e5;">{{ $batchResult['total_employees'] }}</div>
            </div>
            <div style="background:#f0fdf4;border-radius:12px;padding:16px;text-align:center;">
                <div style="font-size:12px;color:#059669;font-weight:600;">Total THR</div>
                <div style="font-size:28px;font-weight:800;color:#047857;">Rp {{ number_format($batchResult['total_thr']) }}</div>
            </div>
            <div style="background:#fef3c7;border-radius:12px;padding:16px;text-align:center;">
                <div style="font-size:12px;color:#d97706;font-weight:600;">Jatuh Tempo</div>
                <div style="font-size:24px;font-weight:700;color:#b45309;">{{ $batchResult['due_date'] }}</div>
            </div>
        </div>

        <div style="overflow-x:auto;">
            <table class="thr-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Karyawan</th>
                        <th>Kode</th>
                        <th>Tipe</th>
                        <th>Masuk</th>
                        <th>Masa Kerja</th>
                        <th>Gaji</th>
                        <th>THR</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($batchResult['details'] as $i => $d)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td style="font-weight:600;">{{ $d['employee_name'] }}</td>
                            <td>{{ $d['employee_code'] }}</td>
                            <td>{{ $d['employee_type'] }}</td>
                            <td>{{ $d['join_date'] }}</td>
                            <td>{{ $d['months_worked'] }} bln</td>
                            <td style="text-align:right;">{{ number_format($d['monthly_salary']) }}</td>
                            <td style="text-align:right;font-weight:700;">{{ number_format($d['thr_amount']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-filament-panels::page>
