<x-filament-panels::page>
    <style>
        .ot-result { background:#f8fafc;border-radius:14px;padding:20px;border:1px solid #e2e8f0;margin-bottom:16px; }
        .ot-row { display:flex;justify-content:space-between;padding:6px 0; }
        .ot-label { color:#64748b;font-size:13px; }
        .ot-val { font-weight:600;color:#1e293b;font-size:13px; }
        .ot-val.highlight { font-weight:800;color:#4f46e5;font-size:15px; }
        table.ot-table { width:100%;border-collapse:collapse;font-size:12px; }
        table.ot-table th { background:#f1f5f9;padding:8px;text-align:left;border:1px solid #e2e8f0;font-weight:700; }
        table.ot-table td { padding:6px 8px;border:1px solid #e2e8f0; }
    </style>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">
        <div>
            <h2 style="font-size:18px;font-weight:700;margin-bottom:16px;">Data Lembur</h2>
            {{ $this->form }}

            <div style="margin-top:16px;">
                <x-filament::button wire:click="calculate" icon="heroicon-o-clock" style="width:100%;">
                    Hitung Lembur
                </x-filament::button>
            </div>

            <div style="margin-top:24px;padding:16px;background:#fef3c7;border:1px solid #fde68a;border-radius:12px;font-size:12px;color:#92400e;">
                <strong>Aturan Lembur (Kepmenakertrans 102/2004):</strong>
                <ul style="margin-top:8px;padding-left:16px;">
                    <li><strong>Hari Kerja:</strong> Jam I = 1,5×, Jam II+ = 2×</li>
                    <li><strong>Istirahat 5 Hari:</strong> 8 jam pertama = 2×, Jam 9 = 3×, Jam 10-11 = 4×</li>
                    <li><strong>Istirahat 6 Hari:</strong> 7 jam pertama = 2×, Jam 8 = 3×, Jam 9-10 = 4×</li>
                    <li><strong>Libur Nasional:</strong> 5 jam pertama = 2×, Jam 6 = 3×, Jam 7-8 = 4×</li>
                    <li><strong>Upah Sejam:</strong> 1/173 × gaji bulanan</li>
                    <li><strong>Istirahat:</strong> Jika ≥ 4 jam kerja, potong 1 jam istirahat</li>
                </ul>
            </div>
        </div>

        <div>
            <h2 style="font-size:18px;font-weight:700;margin-bottom:16px;">Hasil Simulasi</h2>

            @if ($result)
                <div class="ot-result">
                    <div class="ot-row"><span class="ot-label">Gaji Bulanan</span><span class="ot-val">Rp {{ number_format($result['base_wage']) }}</span></div>
                    <div class="ot-row"><span class="ot-label">Upah Sejam (1/173)</span><span class="ot-val">Rp {{ number_format($result['hourly_rate']) }}</span></div>
                    <div class="ot-row"><span class="ot-label">Tipe Hari</span><span class="ot-val">{{ $result['day_type_label'] }}</span></div>
                    <div class="ot-row"><span class="ot-label">Total Jam Bekerja</span><span class="ot-val">{{ $result['hours_worked'] }} jam</span></div>
                    <div class="ot-row"><span class="ot-label">Jam Efektif (setelah potongan)</span><span class="ot-val">{{ $result['effective_hours'] }} jam</span></div>
                    <div class="ot-row">
                        <span class="ot-label">Potongan Istirahat</span>
                        <span class="ot-val">{{ $result['break_deducted'] }} jam</span>
                    </div>
                    <div class="ot-row" style="margin-top:8px;padding-top:8px;border-top:2px solid #e2e8f0;">
                        <span class="ot-label">Total Upah Lembur</span>
                        <span class="ot-val highlight">Rp {{ number_format($result['total_overtime_pay']) }}</span>
                    </div>
                </div>

                @if (!empty($result['overtime_breakdown']))
                    <h3 style="font-size:15px;font-weight:700;margin-bottom:8px;">Rincian Per Jam</h3>
                    <table class="ot-table">
                        <thead>
                            <tr>
                                <th>Jam</th>
                                <th>Pengali</th>
                                <th>Rate/Jam</th>
                                <th>Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($result['overtime_breakdown'] as $bd)
                                <tr>
                                    <td>{{ $bd['hour_label'] }}</td>
                                    <td style="text-align:center;">{{ $bd['multiplier'] }}×</td>
                                    <td style="text-align:right;">Rp {{ number_format($bd['rate']) }}</td>
                                    <td style="text-align:right;font-weight:700;">Rp {{ number_format($bd['amount']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            @else
                <div style="padding:40px;text-align:center;color:#94a3b8;">
                    Klik <strong>"Hitung Lembur"</strong> untuk melihat hasil
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
