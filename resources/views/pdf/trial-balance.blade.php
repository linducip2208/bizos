<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Neraca Saldo</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; line-height: 1.4; color: #1e293b; padding: 30px; }
        .header { text-align: center; margin-bottom: 18px; border-bottom: 3px solid #4f46e5; padding-bottom: 12px; }
        .header h1 { font-size: 16px; font-weight: 800; color: #312e81; margin: 0 0 3px; }
        .header .sub { font-size: 12px; color: #475569; }
        .header .period { font-size: 10px; color: #64748b; margin-top: 2px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f1f5f9; font-weight: 700; text-align: right; padding: 6px 6px; border-bottom: 2px solid #cbd5e1; font-size: 9px; }
        th.left { text-align: left; }
        td { padding: 4px 6px; border-bottom: 1px solid #e2e8f0; text-align: right; font-family: 'DejaVu Sans Mono', monospace; }
        td.left { text-align: left; font-family: 'DejaVu Sans', sans-serif; }
        tr.group-row td { background: #f8fafc; font-weight: 700; text-transform: uppercase; font-size: 9px; color: #475569; letter-spacing: 0.4px; text-align: left; font-family: 'DejaVu Sans', sans-serif; padding-top: 8px; }
        tr.total-row td { font-weight: 800; border-top: 2px solid #64748b; background: #f8fafc; }
        .status { margin-top: 12px; font-size: 10px; }
        .balanced { color: #047857; font-weight: 700; }
        .unbalanced { color: #b45309; font-weight: 700; }
        .footer { margin-top: 24px; padding-top: 10px; border-top: 1px solid #e2e8f0; font-size: 9px; color: #94a3b8; text-align: center; }
        @page { margin: 40px; }
    </style>
</head>
<body>
    @php $totals = $trialBalance['totals'] ?? []; @endphp
    <div class="header">
        <h1>{{ $companyName }}</h1>
        <div class="sub">Neraca Saldo</div>
        <div class="period">
            Periode {{ \Carbon\Carbon::parse($dateFrom)->translatedFormat('d M Y') }} — {{ \Carbon\Carbon::parse($dateTo)->translatedFormat('d M Y') }}
            @if($branchName) — Cabang {{ $branchName }} @endif
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="left">Kode</th>
                <th class="left">Nama Akun</th>
                <th>Saldo Awal Debit</th>
                <th>Saldo Awal Kredit</th>
                <th>Debit</th>
                <th>Kredit</th>
                <th>Saldo Akhir Debit</th>
                <th>Saldo Akhir Kredit</th>
            </tr>
        </thead>
        <tbody>
            @foreach($trialBalance['groups'] as $code => $group)
                <tr class="group-row">
                    <td colspan="8">{{ $group['label'] }}</td>
                </tr>
                @foreach($group['accounts'] as $row)
                    <tr>
                        <td class="left">{{ $row['code'] }}</td>
                        <td class="left">{{ $row['name'] }}</td>
                        <td>{{ number_format($row['opening_debit'], 0, ',', '.') }}</td>
                        <td>{{ number_format($row['opening_credit'], 0, ',', '.') }}</td>
                        <td>{{ number_format($row['movement_debit'], 0, ',', '.') }}</td>
                        <td>{{ number_format($row['movement_credit'], 0, ',', '.') }}</td>
                        <td>{{ number_format($row['closing_debit'], 0, ',', '.') }}</td>
                        <td>{{ number_format($row['closing_credit'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            @endforeach
            <tr class="total-row">
                <td class="left" colspan="2">TOTAL</td>
                <td>{{ number_format($totals['opening_debit'] ?? 0, 0, ',', '.') }}</td>
                <td>{{ number_format($totals['opening_credit'] ?? 0, 0, ',', '.') }}</td>
                <td>{{ number_format($totals['movement_debit'] ?? 0, 0, ',', '.') }}</td>
                <td>{{ number_format($totals['movement_credit'] ?? 0, 0, ',', '.') }}</td>
                <td>{{ number_format($totals['closing_debit'] ?? 0, 0, ',', '.') }}</td>
                <td>{{ number_format($totals['closing_credit'] ?? 0, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="status">
        @if(!empty($trialBalance['balanced']))
            <span class="balanced">&#10004; Neraca Seimbang</span>
        @else
            <span class="unbalanced">&#9888; Neraca Belum Seimbang</span>
        @endif
    </div>

    <div class="footer">Generated oleh BizOS &mdash; {{ now()->translatedFormat('d M Y H:i') }}</div>
</body>
</html>
