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
        .card-indigo { background: #6366f1; }
        .card-emerald { background: #10b981; }
        .card-amber { background: #f59e0b; }
        .card-purple { background: #8b5cf6; }
        h2 { font-size: 16px; font-weight: 700; color: #0f172a; border-bottom: 2px solid #e2e8f0; padding-bottom: 6px; margin: 25px 0 12px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 11px; }
        th { background: #f1f5f9; font-weight: 600; text-align: left; padding: 8px 10px; border-bottom: 2px solid #cbd5e1; }
        th.right { text-align: right; }
        th.center { text-align: center; }
        td { padding: 6px 10px; border-bottom: 1px solid #e2e8f0; }
        td.right { text-align: right; }
        td.center { text-align: center; }
        .badge { display: inline-block; border-radius: 6px; padding: 2px 8px; font-size: 10px; font-weight: 600; }
        .badge-invoice { background: #dbeafe; color: #1d4ed8; }
        .badge-pos { background: #d1fae5; color: #047857; }
        .badge-paid { background: #d1fae5; color: #047857; border-radius: 12px; }
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
        <div class="summary-card card-indigo">
            <div class="label">Total Revenue</div>
            <div class="value">Rp {{ number_format($summaryCards['total_revenue'] ?? 0, 0, ',', '.') }}</div>
        </div>
        <div class="summary-card card-emerald">
            <div class="label">Total Transaksi</div>
            <div class="value">{{ number_format($summaryCards['total_transaksi'] ?? 0, 0, ',', '.') }}</div>
        </div>
        <div class="summary-card card-amber">
            <div class="label">Avg per Transaksi</div>
            <div class="value">Rp {{ number_format($summaryCards['avg_per_transaksi'] ?? 0, 0, ',', '.') }}</div>
        </div>
        <div class="summary-card card-purple">
            <div class="label">Total Pelanggan</div>
            <div class="value">{{ number_format($summaryCards['total_pelanggan'] ?? 0, 0, ',', '.') }}</div>
        </div>
    </div>

    <h2>Detail Transaksi</h2>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Tipe</th>
                <th>Referensi</th>
                <th class="right">Total</th>
                <th class="center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($detailTable as $row)
                <tr>
                    <td>{{ $row['date'] ?? '-' }}</td>
                    <td>
                        <span class="badge {{ ($row['source_type'] ?? '') === 'Invoice' ? 'badge-invoice' : 'badge-pos' }}">
                            {{ $row['source_type'] ?? '-' }}
                        </span>
                    </td>
                    <td>{{ $row['reference'] ?? '-' }}</td>
                    <td class="right">Rp {{ number_format($row['total'] ?? 0, 0, ',', '.') }}</td>
                    <td class="center"><span class="badge-paid">{{ $row['item_status'] ?? $row['status'] ?? 'Paid' }}</span></td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align:center;padding:20px;color:#94a3b8;">Tidak ada data transaksi</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Generated oleh BizOS &mdash; {{ now()->translatedFormat('d M Y H:i') }}
    </div>
</body>
</html>
