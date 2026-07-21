<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sertifikat Elektronik — Tanda Tangan Digital</title>
    <style>
        @page { margin: 2cm; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11pt; color: #1a1a1a; line-height: 1.6; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 3px solid #1a1a1a; padding-bottom: 20px; }
        .header h1 { font-size: 18pt; margin: 0; letter-spacing: 2px; }
        .header .subtitle { font-size: 10pt; color: #666; margin-top: 5px; }
        .section { margin-bottom: 20px; }
        .section h2 { font-size: 12pt; border-bottom: 1px solid #ccc; padding-bottom: 5px; margin-bottom: 10px; }
        .grid { display: flex; flex-wrap: wrap; gap: 0; }
        .grid-item { width: 50%; margin-bottom: 8px; }
        .grid-item .label { font-size: 9pt; color: #666; }
        .grid-item .value { font-size: 10pt; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 9pt; }
        th { background: #f0f0f0; text-align: left; padding: 6px 8px; border: 1px solid #ddd; font-size: 8pt; text-transform: uppercase; }
        td { padding: 6px 8px; border: 1px solid #ddd; }
        .legal { background: #f8f8f8; border-left: 4px solid #1a1a1a; padding: 12px 16px; margin: 20px 0; font-size: 9pt; }
        .legal strong { display: block; margin-bottom: 5px; }
        .footer { margin-top: 40px; text-align: center; font-size: 8pt; color: #999; border-top: 1px solid #ccc; padding-top: 10px; }
        .stamp { border: 2px dashed #ccc; padding: 15px; text-align: center; margin-top: 20px; }
        .stamp .stamp-title { font-size: 14pt; font-weight: bold; color: #666; letter-spacing: 3px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>SERTIFIKAT ELEKTRONIK</h1>
        <div class="subtitle">Tanda Tangan Digital — Penyelenggara Sertifikasi Elektronik (PSrE) Indonesia</div>
    </div>

    <div class="section">
        <h2>Informasi Sertifikat</h2>
        <div class="grid">
            <div class="grid-item">
                <div class="label">Nomor Sertifikat</div>
                <div class="value">{{ $data['certificate_number'] }}</div>
            </div>
            <div class="grid-item">
                <div class="label">Tanggal Pembuatan</div>
                <div class="value">{{ $data['generated_at'] }}</div>
            </div>
            <div class="grid-item">
                <div class="label">ID Dokumen</div>
                <div class="value">{{ $data['document_id'] }}</div>
            </div>
            <div class="grid-item">
                <div class="label">Provider PSrE</div>
                <div class="value">{{ $data['provider'] }}</div>
            </div>
            <div class="grid-item">
                <div class="label">Provider Terdaftar di Kominfo</div>
                <div class="value">{{ $data['provider_registered'] ? 'Ya' : 'Tidak' }}</div>
            </div>
        </div>
    </div>

    <div class="legal">
        <strong>DASAR HUKUM</strong>
        <ol>
            @foreach($data['legal_basis'] as $law)
            <li>{{ $law }}</li>
            @endforeach
        </ol>
        <p><strong>Pasal 11 UU ITE:</strong> Tanda tangan elektronik memiliki kekuatan hukum dan akibat hukum yang sah selama memenuhi persyaratan sebagaimana dimaksud dalam Pasal 12.</p>
        <p><strong>PP No. 71/2019 Pasal 59:</strong> Tanda Tangan Elektronik yang dibuat oleh Penyelenggara Sertifikasi Elektronik (PSrE) Indonesia yang telah terdaftar di Kementerian Komunikasi dan Informatika memiliki kekuatan hukum yang sah dan mengikat.</p>
    </div>

    <div class="section">
        <h2>Daftar PSrE Terdaftar di Indonesia</h2>
        <table>
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Perusahaan</th>
                    <th>Status</th>
                    <th>Registrasi Kominfo</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['providers_list'] as $p)
                <tr>
                    <td>{{ $p['name'] }}</td>
                    <td>{{ $p['company'] }}</td>
                    <td>{{ $p['status'] === 'active' ? 'Aktif' : $p['status'] }}</td>
                    <td>{{ $p['kominfo_registration'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if(!empty($data['timeline']))
    <div class="section">
        <h2>Jejak Audit (Audit Trail)</h2>
        <table>
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Event</th>
                    <th>IP</th>
                    <th>User</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['timeline'] as $event)
                <tr>
                    <td>{{ $event['timestamp'] ?? '-' }}</td>
                    <td>{{ $event['event'] ?? '-' }}</td>
                    <td>{{ $event['ip'] ?? '-' }}</td>
                    <td>{{ $event['user'] ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="stamp">
        <div class="stamp-title">DISAHKAN SECARA ELEKTRONIK</div>
        <div style="font-size: 9pt; margin-top: 5px;">Dokumen ini ditandatangani secara elektronik dan memiliki kekuatan hukum yang sah berdasarkan UU No. 11/2008 tentang Informasi dan Transaksi Elektronik jo. PP No. 71/2019 tentang PSTE.</div>
    </div>

    <div class="footer">
        <p>Sertifikat ini diterbitkan secara otomatis oleh {{ config('app.name', 'BizOS') }} — {{ date('Y') }}</p>
        <p>Dokumen ini sah tanpa tanda tangan basah</p>
    </div>
</body>
</html>
