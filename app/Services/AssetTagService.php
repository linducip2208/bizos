<?php

namespace App\Services;

use App\Models\Asset;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Collection;
use Barryvdh\DomPDF\Facade\Pdf;

class AssetTagService
{
    public function generateQrCode(Asset $asset): string
    {
        $data = json_encode([
            'type' => 'asset',
            'id' => $asset->id,
            'code' => $asset->asset_code,
            'name' => $asset->name,
            'company' => $asset->company?->name,
        ]);

        $result = Builder::create()
            ->writer(new PngWriter())
            ->data($data)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->size(300)
            ->margin(10)
            ->build();

        $fileName = 'qr_asset_' . $asset->id . '_' . time() . '.png';
        $path = storage_path('app/public/qrcodes/' . $fileName);

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $result->saveToFile($path);
        $asset->update(['qr_code' => $fileName]);

        return $fileName;
    }

    public function generateBarcode(Asset $asset): string
    {
        $barcodeData = $asset->asset_code ?? 'ASST' . str_pad($asset->id, 8, '0', STR_PAD_LEFT);

        $fileName = 'bc_asset_' . $asset->id . '_' . time() . '.png';
        $path = storage_path('app/public/barcodes/' . $fileName);

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $width = 400;
        $height = 120;
        $image = imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        imagefilledrectangle($image, 0, 0, $width, $height, $white);

        $this->renderCode128($image, $barcodeData, 20, 10, $width - 40, 80);

        $fontSize = 3;
        $textWidth = imagefontwidth($fontSize) * strlen($barcodeData);
        $textX = (int)(($width - $textWidth) / 2);
        imagestring($image, $fontSize, $textX, 92, $barcodeData, $black);

        imagepng($image, $path);
        imagedestroy($image);

        $asset->update(['barcode' => $fileName]);

        return $fileName;
    }

    private function renderCode128($image, string $data, int $x, int $y, int $maxWidth, int $barHeight): void
    {
        $bars = $this->code128Encode($data);
        $barCount = count($bars);
        $barWidth = max(1, (int)($maxWidth / $barCount));
        $black = imagecolorallocate($image, 0, 0, 0);
        $white = imagecolorallocate($image, 255, 255, 255);

        $pos = $x;
        foreach ($bars as $i => $bar) {
            $color = $bar ? $black : $white;
            imagefilledrectangle($image, $pos, $y, $pos + $barWidth - 1, $y + $barHeight, $color);
            $pos += $barWidth;
        }
    }

    private function code128Encode(string $data): array
    {
        $chars = [];
        $sum = 104;
        for ($i = 0; $i < strlen($data); $i++) {
            $val = ord($data[$i]) - 32;
            if ($val < 0 || $val > 94) $val = 0;
            $sum += $val * ($i + 1);
            $chars[] = $val;
        }
        $checksum = $sum % 103;
        $chars[] = $checksum;

        $patterns = [
            0 => [2,1,2,2,2,2], 1 => [2,2,2,1,2,2], 2 => [2,2,2,2,2,1],
            3 => [1,2,1,2,2,3], 4 => [1,2,1,3,2,2], 5 => [1,3,1,2,2,2],
            6 => [1,2,2,2,1,3], 7 => [1,2,2,3,1,2], 8 => [1,3,2,2,1,2],
            9 => [2,2,1,2,1,3], 20 => [2,2,3,2,1,1], 30 => [1,1,2,3,2,2],
            40 => [1,1,2,2,3,2], 50 => [2,2,1,3,1,2], 60 => [2,2,2,2,2,2],
            70 => [1,2,2,2,2,2], 80 => [2,2,1,2,2,2], 90 => [1,2,2,1,2,3],
            100 => [1,3,1,3,1,2], 103 => [2,3,1,1,1,2],
        ];

        $bars = [2,1,1]; // start code B
        foreach ($chars as $char) {
            $p = $patterns[$char] ?? $patterns[0];
            foreach ($p as $w) {
                $bars = array_merge($bars, array_fill(0, $w, count($bars) % 2 === 1));
            }
        }
        $bars[] = 2; $bars[] = 1; $bars[] = 1; // stop pattern
        return $bars;
    }

    public function printLabels(array $assetIds): string
    {
        $assets = Asset::whereIn('id', $assetIds)->get();

        foreach ($assets as $asset) {
            if (!$asset->qr_code) {
                $this->generateQrCode($asset);
            }
            if (!$asset->barcode) {
                $this->generateBarcode($asset);
            }
        }

        $pdf = Pdf::loadView('pdf.asset-labels', ['assets' => $assets]);
        $pdf->setPaper('a4', 'portrait');

        $fileName = 'asset_labels_' . now()->format('Ymd_His') . '.pdf';
        $path = storage_path('app/public/labels/' . $fileName);

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $pdf->save($path);
        return $fileName;
    }

    public function scanQrCode(string $data): ?Asset
    {
        $decoded = json_decode($data, true);
        if (!$decoded || !isset($decoded['type']) || $decoded['type'] !== 'asset') {
            return null;
        }
        return Asset::find($decoded['id'] ?? 0);
    }
}
