<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 14px; color: #333; line-height: 1.5; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #4f46e5; color: white; padding: 20px; border-radius: 12px 12px 0 0; }
        .header h1 { margin: 0; font-size: 18px; }
        .header p { margin: 4px 0 0; font-size: 12px; opacity: 0.85; }
        .content { background: #f8fafc; padding: 20px; border: 1px solid #e2e8f0; border-top: none; }
        table { width: 100%; border-collapse: collapse; margin: 16px 0; font-size: 12px; }
        th { background: #e2e8f0; text-align: left; padding: 8px 10px; }
        td { padding: 6px 10px; border-bottom: 1px solid #e2e8f0; }
        .footer { padding: 16px 20px; text-align: center; color: #94a3b8; font-size: 11px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $schedule->reportTemplate->name }}</h1>
            <p>Laporan Terjadwal · {{ now()->format('d M Y H:i') }}</p>
        </div>
        <div class="content">
            <p>Berikut adalah laporan terjadwal <strong>{{ $schedule->name }}</strong> dari template <strong>{{ $schedule->reportTemplate->name }}</strong>.</p>

            @if($data->isNotEmpty())
                <p>Total data: {{ count($data) }} baris. Berikut 10 data pertama:</p>
                <table>
                    <thead>
                        <tr>
                            @foreach(array_keys((array) $data->first()) as $h)
                                <th>{{ ucwords(str_replace('_', ' ', $h)) }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $row)
                            <tr>
                                @foreach((array) $row as $v)
                                    <td>{{ $v }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p>Tidak ada data untuk ditampilkan pada periode ini.</p>
            @endif
        </div>
        <div class="footer">
            Laporan dikirim otomatis oleh {{ config('app.name', 'BizOS') }}
        </div>
    </div>
</body>
</html>
