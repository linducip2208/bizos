<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan ESG — {{ $company['name'] ?? 'Company' }}</title>
    <style>
        body { font-family: 'Inter', sans-serif; font-size: 12px; line-height: 1.6; color: #1e293b; padding: 40px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 3px solid #4f46e5; padding-bottom: 20px; }
        .header h1 { font-size: 24px; font-weight: 800; color: #4f46e5; margin: 0 0 5px; }
        .header .period { font-size: 14px; color: #64748b; }
        .header .framework { font-size: 11px; color: #94a3b8; margin-top: 4px; }
        .score-card { display: flex; justify-content: space-around; margin: 20px 0; padding: 20px; background: #f8fafc; border-radius: 12px; }
        .score-item { text-align: center; }
        .score-value { font-size: 32px; font-weight: 800; color: #4f46e5; }
        .score-label { font-size: 10px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
        .grade { font-size: 48px; font-weight: 900; color: #059669; }
        h2 { font-size: 18px; font-weight: 700; color: #0f172a; border-bottom: 2px solid #e2e8f0; padding-bottom: 6px; margin: 25px 0 12px; }
        h3 { font-size: 14px; font-weight: 600; color: #334155; margin: 14px 0 6px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 11px; }
        th { background: #f1f5f9; font-weight: 600; text-align: left; padding: 8px 10px; border-bottom: 2px solid #cbd5e1; }
        td { padding: 6px 10px; border-bottom: 1px solid #e2e8f0; }
        .metric-row { display: flex; justify-content: space-between; padding: 4px 0; border-bottom: 1px solid #f1f5f9; }
        .metric-value { font-weight: 600; }
        .footer { margin-top: 40px; padding-top: 15px; border-top: 1px solid #e2e8f0; font-size: 10px; color: #94a3b8; text-align: center; }
        .disclaimer { background: #fef3c7; border: 1px solid #fcd34d; border-radius: 8px; padding: 10px 14px; margin: 20px 0; font-size: 10px; color: #92400e; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan ESG</h1>
        <div class="period">{{ $company['name'] ?? 'Company' }} — {{ $period_label ?? $period }}</div>
        <div class="framework">Kerangka: {{ $framework_label ?? $framework }} | Generated: {{ $generated_at }}</div>
    </div>

    <div class="disclaimer">
        Laporan ini digenerate otomatis oleh BizOS ESG Reporting Engine. Data diambil dari modul: Vehicle (BBM), Energy (listrik), Waste (limbah), Water (air), Employee (karyawan), Compliance (kepatuhan). Verifikasi manual disarankan sebelum digunakan untuk pelaporan resmi.
    </div>

    {{-- ESG Score Summary --}}
    <div class="score-card">
        <div class="score-item">
            <div class="score-value">{{ $esg_score['total_score'] ?? '--' }}</div>
            <div class="score-label">Skor Total ESG</div>
        </div>
        <div class="score-item">
            <div class="grade">{{ $esg_score['grade'] ?? '--' }}</div>
            <div class="score-label">Grade</div>
        </div>
        <div class="score-item">
            <div class="score-value">{{ $esg_score['environmental_score'] ?? '--' }}</div>
            <div class="score-label">Lingkungan</div>
        </div>
        <div class="score-item">
            <div class="score-value">{{ $esg_score['social_score'] ?? '--' }}</div>
            <div class="score-label">Sosial</div>
        </div>
        <div class="score-item">
            <div class="score-value">{{ $esg_score['governance_score'] ?? '--' }}</div>
            <div class="score-label">Tata Kelola</div>
        </div>
    </div>

    {{-- Executive Summary --}}
    <h2>Ringkasan Eksekutif</h2>
    <p style="font-size: 12px; color: #475569;">{{ $executive_summary ?? 'Tidak tersedia.' }}</p>

    {{-- Carbon Footprint --}}
    <h2>1. Jejak Karbon (Carbon Footprint)</h2>
    <table>
        <tr>
            <th>Kategori</th>
            <th>Emisi (tCO2e)</th>
            <th>Kontribusi</th>
        </tr>
        <tr>
            <td>Cakupan 1 — Emisi Langsung</td>
            <td><strong>{{ round($carbon['scope1_tco2e'] ?? 0, 4) }}</strong></td>
            <td>{{ ($carbon['total_tco2e'] ?? 1) > 0 ? round(($carbon['scope1_tco2e'] ?? 0) / ($carbon['total_tco2e'] ?: 1) * 100, 1) : 0 }}%</td>
        </tr>
        <tr>
            <td>Cakupan 2 — Listrik</td>
            <td><strong>{{ round($carbon['scope2_tco2e'] ?? 0, 4) }}</strong></td>
            <td>{{ ($carbon['total_tco2e'] ?? 1) > 0 ? round(($carbon['scope2_tco2e'] ?? 0) / ($carbon['total_tco2e'] ?: 1) * 100, 1) : 0 }}%</td>
        </tr>
        <tr>
            <td>Cakupan 3 — Rantai Nilai</td>
            <td><strong>{{ round($carbon['scope3_tco2e'] ?? 0, 4) }}</strong></td>
            <td>{{ ($carbon['total_tco2e'] ?? 1) > 0 ? round(($carbon['scope3_tco2e'] ?? 0) / ($carbon['total_tco2e'] ?: 1) * 100, 1) : 0 }}%</td>
        </tr>
        <tr style="background: #f1f5f9; font-weight: 700;">
            <td>TOTAL</td>
            <td><strong>{{ round($carbon['total_tco2e'] ?? 0, 4) }}</strong></td>
            <td>100%</td>
        </tr>
    </table>

    <div class="metric-row">
        <span>Trend vs Periode Sebelumnya</span>
        <span class="metric-value" style="color: {{ ($carbon['trend_direction'] ?? 'stable') === 'down' ? '#059669' : '#dc2626' }}">
            {{ $carbon['trend_vs_last_period_percent'] ?? 0 }}%
            ({{ ($carbon['trend_direction'] ?? 'stable') === 'down' ? 'Menurun' : (($carbon['trend_direction'] ?? '') === 'up' ? 'Meningkat' : 'Stabil') }})
        </span>
    </div>
    <div class="metric-row">
        <span>Intensitas per Karyawan</span>
        <span class="metric-value">{{ round($carbon['intensity_per_employee']['tco2e_per_employee'] ?? 0, 4) }} tCO2e/karyawan</span>
    </div>

    {{-- Waste Management --}}
    <h2>2. Pengelolaan Limbah</h2>
    <table>
        <tr><th>Metrik</th><th>Nilai</th></tr>
        <tr><td>Total Limbah</td><td><strong>{{ round($waste['total_waste_kg'] ?? 0, 2) }} kg</strong></td></tr>
        <tr><td>Daur Ulang</td><td>{{ round($waste['recycled_kg'] ?? 0, 2) }} kg ({{ $waste['recycled_percent'] ?? 0 }}%)</td></tr>
        <tr><td>Ke TPA</td><td>{{ round($waste['landfilled_kg'] ?? 0, 2) }} kg ({{ $waste['landfilled_percent'] ?? 0 }}%)</td></tr>
        <tr><td>Limbah B3</td><td>{{ round($waste['hazardous_kg'] ?? 0, 2) }} kg</td></tr>
        <tr><td>B3 Terolah</td><td>{{ $waste['hazardous_treated_percent'] ?? 0 }}%</td></tr>
    </table>

    {{-- Water Management --}}
    <h2>3. Pengelolaan Air</h2>
    <table>
        <tr><th>Metrik</th><th>Nilai</th></tr>
        <tr><td>Total Air</td><td><strong>{{ round($water['total_water_m3'] ?? 0, 2) }} m3</strong></td></tr>
        <tr><td>Air Daur Ulang</td><td>{{ round($water['recycled_m3'] ?? 0, 2) }} m3 ({{ $water['recycled_percent'] ?? 0 }}%)</td></tr>
        <tr><td>PDAM</td><td>{{ round($water['municipal_m3'] ?? 0, 2) }} m3</td></tr>
        <tr><td>Sumur</td><td>{{ round($water['well_m3'] ?? 0, 2) }} m3</td></tr>
        <tr><td>Air Hujan</td><td>{{ round($water['rainwater_m3'] ?? 0, 2) }} m3</td></tr>
    </table>

    {{-- Social --}}
    <h2>4. Metrik Sosial</h2>
    <h3>Diversitas & Inklusi</h3>
    <table>
        <tr><th>Metrik</th><th>Nilai</th></tr>
        <tr><td>Total Karyawan</td><td>{{ $social['total_employees'] ?? 0 }}</td></tr>
        <tr><td>Pria / Wanita</td><td>{{ $social['diversity']['gender']['male_percent'] ?? 0 }}% / {{ $social['diversity']['gender']['female_percent'] ?? 0 }}%</td></tr>
        <tr><td>Turnover (Annualized)</td><td>{{ $social['turnover']['annualized_rate_percent'] ?? 0 }}%</td></tr>
        <tr><td>Gap Gaji Gender</td><td>{{ $social['compensation']['gender_pay_gap_percent'] ?? 0 }}%</td></tr>
    </table>

    {{-- Governance --}}
    <h2>5. Tata Kelola</h2>
    <table>
        <tr><th>Metrik</th><th>Nilai</th></tr>
        <tr><td>Pelanggaran Data (YTD)</td><td>{{ $governance['data_privacy']['breaches_ytd'] ?? 0 }}</td></tr>
        <tr><td>DPIA Selesai</td><td>{{ $governance['data_privacy']['dpia_completed'] ?? 0 }}</td></tr>
        <tr><td>Risiko Teridentifikasi</td><td>{{ $governance['risk_management']['risks_identified'] ?? 0 }}</td></tr>
    </table>

    {{-- Targets --}}
    @if(count($targets))
    <h2>6. Target ESG</h2>
    <table>
        <tr><th>Target</th><th>Kategori</th><th>Target</th><th>Saat Ini</th><th>Deadline</th><th>Status</th></tr>
        @foreach($targets as $t)
        <tr>
            <td>{{ $t['metric_label'] }}</td>
            <td>{{ $t['category'] }}</td>
            <td>{{ $t['target_value'] }} {{ $t['unit'] }}</td>
            <td>{{ $t['current_value'] }} {{ $t['unit'] }}</td>
            <td>{{ $t['deadline'] }}</td>
            <td>{{ $t['status'] }}</td>
        </tr>
        @endforeach
    </table>
    @endif

    {{-- Recommendations --}}
    <h2>7. Rekomendasi</h2>
    @foreach($suggestions as $s)
    <div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 12px; margin-bottom: 6px;">
        <strong>{{ $s['title'] }}</strong><br>
        <span style="font-size: 10px; color: #64748b;">Potensi reduksi {{ $s['reduction_percent'] }}% | Tingkat Kesulitan: {{ $s['difficulty'] }} | ROI: {{ $s['roi_estimate'] }}</span>
        <p style="font-size: 10px; margin: 4px 0 0; color: #475569;">{{ $s['description'] }}</p>
    </div>
    @endforeach

    <div class="footer">
        Laporan ini digenerate oleh BizOS ESG Reporting Engine pada {{ $generated_at }}. Data disajikan apa adanya dari modul operasional.
    </div>
</body>
</html>
