<?php

namespace App\Services;

use App\Models\Product;

class ProductBarcodeService
{
    public function generateCode128(string $data): string
    {
        $width = 400;
        $barHeight = 80;
        $height = 120;
        $image = imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        imagefilledrectangle($image, 0, 0, $width, $height, $white);

        $this->renderCode128($image, $data, 20, 10, $width - 40, $barHeight);

        $fontSize = 3;
        $textWidth = imagefontwidth($fontSize) * strlen($data);
        $textX = (int) (($width - $textWidth) / 2);
        imagestring($image, $fontSize, $textX, 92, $data, $black);

        ob_start();
        imagepng($image);
        $png = ob_get_clean();
        imagedestroy($image);

        return 'data:image/png;base64,'.base64_encode($png);
    }

    public function generateEan13(string $data): string
    {
        $digits = preg_replace('/\D/', '', $data);

        if (strlen($digits) === 12) {
            $digits .= $this->ean13CheckDigit($digits);
        } elseif (strlen($digits) !== 13) {
            throw new \InvalidArgumentException('EAN-13 membutuhkan 12 atau 13 digit.');
        } elseif ($this->ean13CheckDigit(substr($digits, 0, 12)) !== $digits[12]) {
            $digits = substr($digits, 0, 12).$this->ean13CheckDigit(substr($digits, 0, 12));
        }

        $modules = $this->ean13Modules($digits);

        return $this->pngFromModules($modules, 2, $digits);
    }

    public function generateBarcodeImage(Product $product, string $format = 'code128'): string
    {
        $data = $this->barcodeData($product, $format);

        return $format === 'ean13'
            ? $this->generateEan13($data)
            : $this->generateCode128($data);
    }

    public function generateLabel(Product $product, string $format = 'code128'): array
    {
        $barcodeData = $this->barcodeData($product, $format);

        return [
            'product_id' => $product->id,
            'barcode_image' => $this->generateBarcodeImage($product, $format),
            'barcode_data' => $barcodeData,
            'name' => $product->name,
            'price' => (float) $product->selling_price,
            'sku' => $product->code,
            'format' => $format,
        ];
    }

    public function generateBatch(array $productIds, string $format = 'code128'): array
    {
        return Product::whereIn('id', $productIds)
            ->get()
            ->map(fn (Product $product) => $this->generateLabel($product, $format))
            ->all();
    }

    public function printLabels(array $productIds, string $format = 'code128', int $copies = 1): array
    {
        $copies = max(1, $copies);
        $labels = $this->generateBatch($productIds, $format);

        $job = [];
        foreach ($labels as $label) {
            for ($i = 0; $i < $copies; $i++) {
                $job[] = $label;
            }
        }

        return [
            'job_id' => uniqid('label_', true),
            'total_labels' => count($job),
            'copies' => $copies,
            'format' => $format,
            'labels' => $job,
            'generated_at' => now()->toIso8601String(),
        ];
    }

    protected function barcodeData(Product $product, string $format): string
    {
        $barcodes = $product->barcodes()->orderByDesc('is_primary')->get();

        if ($format === 'ean13') {
            foreach ($barcodes as $bc) {
                if (preg_match('/^\d{12,13}$/', $bc->barcode)) {
                    return $bc->barcode;
                }
            }

            return $this->internalEan13($product->id);
        }

        $primary = $barcodes->first();
        if ($primary && $primary->barcode) {
            return $primary->barcode;
        }

        return $product->code ?: 'P'.str_pad((string) $product->id, 8, '0', STR_PAD_LEFT);
    }

    protected function internalEan13(int $productId): string
    {
        return '20'.str_pad((string) $productId, 10, '0', STR_PAD_LEFT);
    }

    protected function ean13CheckDigit(string $twelve): string
    {
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $weight = ($i % 2 === 0) ? 1 : 3;
            $sum += (int) $twelve[$i] * $weight;
        }

        return (string) ((10 - ($sum % 10)) % 10);
    }

    protected function ean13Modules(string $digits): array
    {
        $L = [
            '0' => '0001101', '1' => '0011001', '2' => '0010011', '3' => '0111101', '4' => '0100011',
            '5' => '0110001', '6' => '0101111', '7' => '0111011', '8' => '0110111', '9' => '0001011',
        ];
        $G = [
            '0' => '0100111', '1' => '0110011', '2' => '0011011', '3' => '0100001', '4' => '0011101',
            '5' => '0111001', '6' => '0000101', '7' => '0010001', '8' => '0001001', '9' => '0010111',
        ];
        $R = [
            '0' => '1110010', '1' => '1100110', '2' => '1101100', '3' => '1000010', '4' => '1011100',
            '5' => '1001110', '6' => '1010000', '7' => '1000100', '8' => '1001000', '9' => '1110100',
        ];
        $parity = [
            '0' => 'LLLLLL', '1' => 'LLGLGG', '2' => 'LLGGLG', '3' => 'LLGGGL', '4' => 'LGLLGG',
            '5' => 'LGGLLG', '6' => 'LGGGLL', '7' => 'LGLGLG', '8' => 'LGLGGL', '9' => 'LGGLGL',
        ];

        $first = $digits[0];
        $bits = '101';
        for ($i = 0; $i < 6; $i++) {
            $d = $digits[$i + 1];
            $bits .= $parity[$first][$i] === 'L' ? $L[$d] : $G[$d];
        }
        $bits .= '01010';
        for ($i = 6; $i < 12; $i++) {
            $bits .= $R[$digits[$i + 1]];
        }
        $bits .= '101';

        return array_map(fn ($c) => $c === '1', str_split($bits));
    }

    protected function pngFromModules(array $modules, int $scale, string $text): string
    {
        $barHeight = 80;
        $textSpace = 24;
        $width = count($modules) * $scale;
        $height = $barHeight + $textSpace;

        $image = imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        imagefilledrectangle($image, 0, 0, $width, $height, $white);

        foreach ($modules as $i => $module) {
            if ($module) {
                imagefilledrectangle($image, $i * $scale, 0, ($i + 1) * $scale - 1, $barHeight - 1, $black);
            }
        }

        $fontSize = 3;
        $textWidth = imagefontwidth($fontSize) * strlen($text);
        $textX = (int) (($width - $textWidth) / 2);
        imagestring($image, $fontSize, $textX, $barHeight + 6, $text, $black);

        ob_start();
        imagepng($image);
        $png = ob_get_clean();
        imagedestroy($image);

        return 'data:image/png;base64,'.base64_encode($png);
    }

    protected function renderCode128($image, string $data, int $x, int $y, int $maxWidth, int $barHeight): void
    {
        $bars = $this->code128Encode($data);
        $barCount = count($bars);
        $barWidth = max(1, (int) ($maxWidth / $barCount));
        $black = imagecolorallocate($image, 0, 0, 0);
        $white = imagecolorallocate($image, 255, 255, 255);

        $pos = $x;
        foreach ($bars as $bar) {
            $color = $bar ? $black : $white;
            imagefilledrectangle($image, $pos, $y, $pos + $barWidth - 1, $y + $barHeight, $color);
            $pos += $barWidth;
        }
    }

    protected function code128Encode(string $data): array
    {
        $chars = [];
        $sum = 104;
        for ($i = 0; $i < strlen($data); $i++) {
            $val = ord($data[$i]) - 32;
            if ($val < 0 || $val > 94) {
                $val = 0;
            }
            $sum += $val * ($i + 1);
            $chars[] = $val;
        }
        $checksum = $sum % 103;
        $chars[] = $checksum;

        $patterns = [
            0 => [2, 1, 2, 2, 2, 2], 1 => [2, 2, 2, 1, 2, 2], 2 => [2, 2, 2, 2, 2, 1],
            3 => [1, 2, 1, 2, 2, 3], 4 => [1, 2, 1, 3, 2, 2], 5 => [1, 3, 1, 2, 2, 2],
            6 => [1, 2, 2, 2, 1, 3], 7 => [1, 2, 2, 3, 1, 2], 8 => [1, 3, 2, 2, 1, 2],
            9 => [2, 2, 1, 2, 1, 3], 20 => [2, 2, 3, 2, 1, 1], 30 => [1, 1, 2, 3, 2, 2],
            40 => [1, 1, 2, 2, 3, 2], 50 => [2, 2, 1, 3, 1, 2], 60 => [2, 2, 2, 2, 2, 2],
            70 => [1, 2, 2, 2, 2, 2], 80 => [2, 2, 1, 2, 2, 2], 90 => [1, 2, 2, 1, 2, 3],
            100 => [1, 3, 1, 3, 1, 2], 103 => [2, 3, 1, 1, 1, 2],
        ];

        $bars = [2, 1, 1];
        foreach ($chars as $char) {
            $p = $patterns[$char] ?? $patterns[0];
            foreach ($p as $w) {
                $bars = array_merge($bars, array_fill(0, $w, count($bars) % 2 === 1));
            }
        }
        $bars[] = 2;
        $bars[] = 1;
        $bars[] = 1;

        return $bars;
    }
}
