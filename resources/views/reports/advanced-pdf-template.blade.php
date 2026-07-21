<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $report->name ?? 'Advanced Report' }}</title>
    <style>
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; font-size: 12px; color: #1e293b; }
        h1 { font-size: 20px; color: #4f46e5; margin-bottom: 4px; }
        .subtitle { color: #64748b; font-size: 11px; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th { background: #f1f5f9; padding: 8px 10px; text-align: left; font-weight: 600; font-size: 11px; border: 1px solid #e2e8f0; }
        td { padding: 6px 10px; border: 1px solid #e2e8f0; font-size: 11px; }
        .total-row { background: #eef2ff; font-weight: 600; }
        .grand-total { background: #4f46e5; color: white; font-weight: 700; }
        .footer { margin-top: 24px; font-size: 9px; color: #94a3b8; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 8px; }
    </style>
</head>
<body>
    <h1>{{ $report->name ?? 'Laporan Advanced BI' }}</h1>
    <p class="subtitle">Dibuat: {{ $generatedAt ?? now()->format('d M Y H:i') }}</p>

    @if (!empty($data))
        @if (isset($data['matrix']))
            <table>
                <thead>
                    <tr>
                        <th>{{ $data['row_field'] ?? 'Baris' }}</th>
                        @foreach ($data['col_labels'] ?? [] as $colLabel)
                            <th>{{ $colLabel }}</th>
                        @endforeach
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data['row_labels'] ?? [] as $rowLabel)
                        <tr>
                            <td><strong>{{ $rowLabel }}</strong></td>
                            @foreach ($data['col_labels'] ?? [] as $colLabel)
                                <td>{{ number_format($data['matrix'][$rowLabel][$colLabel] ?? 0, 2) }}</td>
                            @endforeach
                            <td class="total-row">{{ number_format($data['row_totals'][$rowLabel] ?? 0, 2) }}</td>
                        </tr>
                    @endforeach
                    @if (!empty($data['col_totals']))
                        <tr>
                            <td class="total-row"><strong>Total</strong></td>
                            @foreach ($data['col_labels'] ?? [] as $colLabel)
                                <td class="total-row">{{ number_format($data['col_totals'][$colLabel] ?? 0, 2) }}</td>
                            @endforeach
                            <td class="grand-total">{{ number_format($data['grand_total'] ?? 0, 2) }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        @elseif (!empty($data['raw']))
            @php $raw = collect($data['raw']); @endphp
            @if ($raw->isNotEmpty())
                <table>
                    <thead>
                        <tr>
                            @foreach (array_keys((array) $raw->first()) as $header)
                                <th>{{ ucwords(str_replace('_', ' ', $header)) }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($raw as $row)
                            <tr>
                                @foreach ((array) $row as $cell)
                                    <td>{{ $cell }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        @endif
    @endif

    <div class="footer">
        Dokumen ini dibuat otomatis oleh {{ config('app.name', 'BizOS') }} &mdash; Advanced BI Module
    </div>
</body>
</html>
