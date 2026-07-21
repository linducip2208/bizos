<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 5mm; }
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
        .labels { display: grid; grid-template-columns: repeat(3, 1fr); gap: 8mm; }
        .label {
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 4mm;
            text-align: center;
            page-break-inside: avoid;
        }
        .label img { max-width: 100%; height: auto; }
        .label .name { font-size: 12px; font-weight: bold; margin-top: 3px; }
        .label .code { font-size: 10px; color: #555; }
        .label .location { font-size: 9px; color: #888; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    @foreach ($assets->chunk(12) as $chunk)
        <div class="labels">
            @foreach ($chunk as $asset)
                <div class="label">
                    @if ($asset->qr_code)
                        <img src="{{ storage_path('app/public/qrcodes/' . $asset->qr_code) }}" style="width: 100px; height: 100px;">
                    @endif
                    <div class="name">{{ $asset->name }}</div>
                    <div class="code">{{ $asset->asset_code }}</div>
                    <div class="location">{{ $asset->location ?? '-' }}</div>
                    @if ($asset->barcode)
                        <img src="{{ storage_path('app/public/barcodes/' . $asset->barcode) }}" style="width: 180px; height: 40px; margin-top: 3px;">
                    @endif
                </div>
            @endforeach
        </div>
        @if (!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach
</body>
</html>
