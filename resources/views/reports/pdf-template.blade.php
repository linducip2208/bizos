<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $template->name }} - {{ $generatedAt }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #333; margin: 0; padding: 20px; }
        h1 { font-size: 18px; margin-bottom: 4px; color: #1e293b; }
        .subtitle { font-size: 10px; color: #64748b; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th { background: #f1f5f9; text-align: left; padding: 8px 10px; border-bottom: 2px solid #e2e8f0; font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; color: #475569; }
        td { padding: 6px 10px; border-bottom: 1px solid #f1f5f9; font-size: 10px; }
        tr:nth-child(even) td { background: #f8fafc; }
        .summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 10px; margin-bottom: 16px; }
        .summary-card { border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 14px; }
        .summary-card .label { font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; margin-bottom: 2px; }
        .summary-card .value { font-size: 16px; font-weight: 700; color: #1e293b; }
        .footer { font-size: 9px; color: #94a3b8; text-align: center; margin-top: 24px; padding-top: 12px; border-top: 1px solid #e2e8f0; }
        .chart-placeholder { border: 1px dashed #cbd5e1; border-radius: 8px; padding: 20px; text-align: center; margin-bottom: 16px; color: #94a3b8; font-size: 10px; }
    </style>
</head>
<body>
    <h1>{{ $template->name }}</h1>
    <p class="subtitle">Dibuat: {{ $generatedAt }} | Kategori: {{ ucfirst($template->category) }}</p>

    @if($data->isNotEmpty())
        @php $first = (array) $data->first(); @endphp

        @if(isset($chartData) && !empty($chartData['labels']))
            <div class="chart-placeholder">
                Grafik {{ $chartData['type'] ?? 'bar' }} — {{ count($chartData['labels'] ?? []) }} titik data
            </div>

            @if(!empty($chartData['datasets']))
                <div class="summary-grid">
                    @foreach($chartData['datasets'] as $dataset)
                        <div class="summary-card">
                            <div class="label">{{ $dataset['label'] ?? 'Data' }}</div>
                            <div class="value">{{ number_format(array_sum($dataset['data'] ?? []), 0) }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        @endif

        <table>
            <thead>
                <tr>
                    @foreach(array_keys($first) as $header)
                        <th>{{ ucwords(str_replace('_', ' ', $header)) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($data as $row)
                    <tr>
                        @foreach((array) $row as $value)
                            <td>
                                @if(is_numeric($value) && floor($value) != $value)
                                    {{ number_format($value, 2) }}
                                @elseif(is_numeric($value))
                                    {{ number_format($value) }}
                                @else
                                    {{ $value }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="color: #94a3b8; text-align: center; padding: 40px;">Tidak ada data.</p>
    @endif

    <div class="footer">
        Laporan dibuat otomatis oleh {{ config('app.name', 'BizOS') }} · {{ $generatedAt }}
    </div>
</body>
</html>
