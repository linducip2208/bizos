<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Neraca</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; line-height: 1.5; color: #1e293b; padding: 30px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 3px solid #4f46e5; padding-bottom: 15px; }
        .header h1 { font-size: 18px; font-weight: 800; color: #312e81; margin: 0 0 3px; }
        .header .sub { font-size: 12px; color: #475569; }
        .header .period { font-size: 11px; color: #64748b; margin-top: 2px; }
        .two-col { display: flex; gap: 40px; }
        .col { flex: 1; }
        .section-title { font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; margin: 14px 0 4px; }
        .section-title.asset { color: #4f46e5; }
        .section-title.liab { color: #e11d48; }
        .group-label { font-size: 11px; font-weight: 700; color: #334155; margin-top: 8px; }
        .row { display: flex; justify-content: space-between; padding: 2px 0; border-bottom: 1px dashed #e2e8f0; }
        .row .name { color: #475569; }
        .row .amount { font-family: 'DejaVu Sans Mono', monospace; color: #1e293b; }
        .subtotal { display: flex; justify-content: space-between; padding: 4px 0; font-weight: 700; color: #0f172a; }
        .grand-total { display: flex; justify-content: space-between; padding: 6px 8px; margin-top: 8px; border-radius: 6px; font-weight: 800; }
        .grand-total.asset { background: #eef2ff; color: #3730a3; }
        .grand-total.liab { background: #fff1f2; color: #be123c; }
        .note { margin-top: 12px; padding: 8px 10px; border-radius: 6px; background: #fffbeb; color: #92400e; font-size: 10px; }
        .footer { margin-top: 30px; padding-top: 12px; border-top: 1px solid #e2e8f0; font-size: 9px; color: #94a3b8; text-align: center; }
        @page { margin: 60px; }
    </style>
</head>
<body>
    @php $fmt = fn($v) => number_format($v, 0, ',', '.'); @endphp
    <div class="header">
        <h1>{{ $companyName }}</h1>
        <div class="sub">Laporan Posisi Keuangan (Neraca)</div>
        <div class="period">
            Per {{ \Carbon\Carbon::parse($asOfDate)->translatedFormat('d F Y') }}
            @if($branchName) — Cabang {{ $branchName }} @endif
        </div>
    </div>

    <div class="two-col">
        <div class="col">
            <div class="section-title asset">Aset</div>

            <div class="group-label">Aset Lancar</div>
            @foreach($balanceSheet['assets']['current'] as $item)
                <div class="row">
                    <span class="name">{{ $item['name'] }}</span>
                    <span class="amount">Rp {{ $fmt($item['balance']) }}</span>
                </div>
            @endforeach
            <div class="subtotal">
                <span>Total Aset Lancar</span>
                <span>Rp {{ $fmt($balanceSheet['total_assets_current']) }}</span>
            </div>

            <div class="group-label">Aset Tetap</div>
            @foreach($balanceSheet['assets']['non_current'] as $item)
                <div class="row">
                    <span class="name">{{ $item['name'] }}</span>
                    <span class="amount">Rp {{ $fmt($item['balance']) }}</span>
                </div>
            @endforeach
            <div class="subtotal">
                <span>Total Aset Tetap</span>
                <span>Rp {{ $fmt($balanceSheet['total_assets_non_current']) }}</span>
            </div>

            <div class="grand-total asset">
                <span>TOTAL ASET</span>
                <span>Rp {{ $fmt($balanceSheet['total_assets']) }}</span>
            </div>
        </div>

        <div class="col">
            <div class="section-title liab">Liabilitas &amp; Ekuitas</div>

            <div class="group-label">Liabilitas Lancar</div>
            @foreach($balanceSheet['liabilities']['current'] as $item)
                <div class="row">
                    <span class="name">{{ $item['name'] }}</span>
                    <span class="amount">Rp {{ $fmt($item['balance']) }}</span>
                </div>
            @endforeach
            <div class="subtotal">
                <span>Total Liabilitas Lancar</span>
                <span>Rp {{ $fmt($balanceSheet['total_liabilities_current']) }}</span>
            </div>

            <div class="group-label">Liabilitas Jangka Panjang</div>
            @foreach($balanceSheet['liabilities']['non_current'] as $item)
                <div class="row">
                    <span class="name">{{ $item['name'] }}</span>
                    <span class="amount">Rp {{ $fmt($item['balance']) }}</span>
                </div>
            @endforeach
            <div class="subtotal">
                <span>Total Liabilitas Jangka Panjang</span>
                <span>Rp {{ $fmt($balanceSheet['total_liabilities_non_current']) }}</span>
            </div>

            <div class="subtotal">
                <span>Total Liabilitas</span>
                <span>Rp {{ $fmt($balanceSheet['total_liabilities']) }}</span>
            </div>

            <div class="group-label">Ekuitas</div>
            @foreach($balanceSheet['equity'] as $item)
                <div class="row">
                    <span class="name">{{ $item['name'] }}</span>
                    <span class="amount">Rp {{ $fmt($item['balance']) }}</span>
                </div>
            @endforeach
            <div class="subtotal">
                <span>Total Ekuitas</span>
                <span>Rp {{ $fmt($balanceSheet['total_equity']) }}</span>
            </div>

            <div class="grand-total liab">
                <span>TOTAL LIABILITAS &amp; EKUITAS</span>
                <span>Rp {{ $fmt($balanceSheet['total_liabilities_and_equity']) }}</span>
            </div>
        </div>
    </div>

    @if(abs($balanceSheet['total_assets'] - $balanceSheet['total_liabilities_and_equity']) > 0.01)
        <div class="note">
            Selisih: Rp {{ $fmt($balanceSheet['total_assets'] - $balanceSheet['total_liabilities_and_equity']) }} — neraca belum seimbang.
        </div>
    @endif

    <div class="footer">Generated oleh BizOS &mdash; {{ now()->translatedFormat('d M Y H:i') }}</div>
</body>
</html>
