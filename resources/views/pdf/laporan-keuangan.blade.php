<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: 'Inter', sans-serif; font-size: 12px; line-height: 1.6; color: #1e293b; padding: 40px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 3px solid #6366f1; padding-bottom: 20px; }
        .header h1 { font-size: 24px; font-weight: 800; color: #4f46e5; margin: 0 0 5px; }
        .header .period { font-size: 13px; color: #64748b; }
        .header .group { font-size: 11px; color: #94a3b8; margin-top: 2px; text-transform: capitalize; }
        .summary-grid { display: flex; justify-content: space-between; margin: 20px 0; gap: 12px; }
        .summary-card { flex: 1; padding: 16px 12px; border-radius: 10px; color: #fff; text-align: center; }
        .summary-card .label { font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; opacity: 0.9; }
        .summary-card .value { font-size: 20px; font-weight: 800; }
        .card-emerald { background: #10b981; }
        .card-rose { background: #f43f5e; }
        .card-indigo { background: #6366f1; }
        .card-sky { background: #0ea5e9; }
        .card-red { background: #ef4444; }
        h2 { font-size: 16px; font-weight: 700; color: #0f172a; border-bottom: 2px solid #e2e8f0; padding-bottom: 6px; margin: 25px 0 12px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 11px; }
        th { background: #f1f5f9; font-weight: 600; text-align: left; padding: 8px 10px; border-bottom: 2px solid #cbd5e1; }
        td { padding: 6px 10px; border-bottom: 1px solid #e2e8f0; }
        td.right { text-align: right; }
        td.pl { padding-left: 24px; }
        tr.header-row td { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; padding-top: 14px; }
        tr.subtotal td { font-weight: 600; border-top: 1px solid #cbd5e1; }
        tr.total td { font-weight: 800; border-top: 2px solid #64748b; background: #f8fafc; }
        .profit { color: #047857; }
        .loss { color: #dc2626; }
        .footer { margin-top: 40px; padding-top: 15px; border-top: 1px solid #e2e8f0; font-size: 10px; color: #94a3b8; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <div class="period">Periode: {{ \Carbon\Carbon::parse($dateFrom)->translatedFormat('d M Y') }} — {{ \Carbon\Carbon::parse($dateTo)->translatedFormat('d M Y') }}</div>
        <div class="group">Group By: {{ $groupBy }}</div>
    </div>

    <div class="summary-grid">
        <div class="summary-card card-emerald">
            <div class="label">Total Pendapatan</div>
            <div class="value">Rp {{ number_format($summaryCards['total_pendapatan'] ?? 0, 0, ',', '.') }}</div>
        </div>
        <div class="summary-card card-rose">
            <div class="label">Total Beban</div>
            <div class="value">Rp {{ number_format($summaryCards['total_beban'] ?? 0, 0, ',', '.') }}</div>
        </div>
        <div class="summary-card {{ ($summaryCards['laba_rugi'] ?? 0) >= 0 ? 'card-indigo' : 'card-red' }}">
            <div class="label">{{ ($summaryCards['laba_rugi'] ?? 0) >= 0 ? 'Laba' : 'Rugi' }}</div>
            <div class="value">Rp {{ number_format(abs($summaryCards['laba_rugi'] ?? 0), 0, ',', '.') }}</div>
        </div>
        <div class="summary-card {{ ($summaryCards['margin'] ?? 0) >= 0 ? 'card-sky' : 'card-red' }}">
            <div class="label">Margin</div>
            <div class="value">{{ number_format($summaryCards['margin'] ?? 0, 1) }}%</div>
        </div>
    </div>

    <h2>Ringkasan Laba Rugi</h2>
    <table>
        @foreach ($pnlSummary as $row)
            @if ($row['type'] === 'header')
                <tr class="header-row"><td colspan="2">{{ $row['label'] }}</td></tr>
            @elseif ($row['type'] === 'subtotal')
                <tr class="subtotal">
                    <td class="pl">{{ $row['label'] }}</td>
                    <td class="right">Rp {{ number_format($row['amount'], 0, ',', '.') }}</td>
                </tr>
            @elseif ($row['type'] === 'total')
                <tr class="total">
                    <td class="pl {{ $row['amount'] >= 0 ? 'profit' : 'loss' }}">{{ $row['label'] }}</td>
                    <td class="right {{ $row['amount'] >= 0 ? 'profit' : 'loss' }}">Rp {{ number_format($row['amount'], 0, ',', '.') }}</td>
                </tr>
            @else
                <tr>
                    <td class="pl">{{ $row['label'] }}</td>
                    <td class="right">Rp {{ number_format($row['amount'], 0, ',', '.') }}</td>
                </tr>
            @endif
        @endforeach
    </table>

    <div class="footer">
        Generated oleh BizOS &mdash; {{ now()->translatedFormat('d M Y H:i') }}
    </div>
</body>
</html>
