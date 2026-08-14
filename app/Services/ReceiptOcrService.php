<?php

namespace App\Services;

use App\Models\AiProvider;
use App\Models\Reimbursement;
use App\Models\ReimbursementCategory;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ReceiptOcrService
{
    protected ?AiProvider $provider = null;

    public function getProvider(): AiProvider
    {
        if ($this->provider) {
            return $this->provider;
        }

        $this->provider = AiProvider::where('is_active', true)
            ->where('api_format', 'openai_compatible')
            ->first();

        if (!$this->provider) {
            $this->provider = AiProvider::where('is_active', true)->first();
        }

        if (!$this->provider) {
            throw new \RuntimeException('Tidak ada AI Provider aktif. Silakan konfigurasi AI Provider terlebih dahulu.');
        }

        return $this->provider;
    }

    public function setProvider(AiProvider $provider): self
    {
        $this->provider = $provider;
        return $this;
    }

    public function processReceipt(string $imagePath): array
    {
        $fullPath = $imagePath;

        if (Storage::disk('public')->exists($imagePath)) {
            $fullPath = Storage::disk('public')->path($imagePath);
        }

        if (!file_exists($fullPath)) {
            throw new \RuntimeException('File gambar struk tidak ditemukan: ' . $imagePath);
        }

        $mimeType = mime_content_type($fullPath) ?: 'image/jpeg';
        $base64Image = base64_encode(file_get_contents($fullPath));
        $dataUri = "data:{$mimeType};base64,{$base64Image}";

        return $this->extractFromImage($dataUri);
    }

    public function extractFromImage(string $imageBase64): array
    {
        $provider = $this->getProvider();
        $baseUrl = rtrim($provider->base_url, '/');
        $apiKey = decrypt($provider->api_key_encrypted);
        $model = $provider->default_model ?: 'gpt-4o';

        $systemPrompt = $this->buildReceiptPrompt();
        $userMessage = 'Ekstrak data dari struk/kwitansi ini dan kembalikan dalam format JSON.';

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ])
                ->timeout(120)
                ->post("{$baseUrl}/v1/chat/completions", [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        [
                            'role' => 'user',
                            'content' => [
                                ['type' => 'text', 'text' => $userMessage],
                                ['type' => 'image_url', 'image_url' => ['url' => $imageBase64]],
                            ],
                        ],
                    ],
                    'temperature' => 0.1,
                    'max_tokens' => 2000,
                ]);

            if ($response->successful()) {
                $content = $response->json('choices.0.message.content') ?? '{}';
                $ocrData = $this->parseOcrResponse($content);

                $validated = $this->validateOcrResult($ocrData);
                $validated['category_id'] = $this->matchToReimbursementCategory($validated);

                return $validated;
            }

            Log::error('ReceiptOCR LLM error', ['status' => $response->status(), 'body' => $response->body()]);
            return $this->getEmptyResult('LLM API error: HTTP ' . $response->status());
        } catch (ConnectionException $e) {
            Log::error('ReceiptOCR connection error: ' . $e->getMessage());
            return $this->getEmptyResult('Connection error: ' . $e->getMessage());
        }
    }

    public function validateOcrResult(array $ocrData): array
    {
        $validated = $ocrData;

        if (!empty($ocrData['transaction_date']) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $ocrData['transaction_date'])) {
            try {
                $d = new \DateTime($ocrData['transaction_date']);
                $validated['transaction_date'] = $d->format('Y-m-d');
            } catch (\Throwable) {
                $validated['transaction_date'] = '';
            }
        }

        if (isset($ocrData['total_amount']) && !is_numeric($ocrData['total_amount'])) {
            $cleaned = preg_replace('/[^0-9.]/', '', (string) $ocrData['total_amount']);
            $validated['total_amount'] = is_numeric($cleaned) ? (float) $cleaned : 0;
        } else {
            $validated['total_amount'] = (float) ($ocrData['total_amount'] ?? 0);
        }

        if (isset($ocrData['tax_amount']) && !is_numeric($ocrData['tax_amount'])) {
            $cleaned = preg_replace('/[^0-9.]/', '', (string) $ocrData['tax_amount']);
            $validated['tax_amount'] = is_numeric($cleaned) ? (float) $cleaned : 0;
        } else {
            $validated['tax_amount'] = (float) ($ocrData['tax_amount'] ?? 0);
        }

        if (!isset($ocrData['line_items']) || !is_array($ocrData['line_items'])) {
            $validated['line_items'] = [];
        }

        $validated['merchant_name'] = $ocrData['merchant_name'] ?? $ocrData['vendor_name'] ?? '';
        $validated['payment_method'] = $ocrData['payment_method'] ?? '';
        $validated['currency'] = $ocrData['currency'] ?? 'IDR';
        $validated['receipt_number'] = $ocrData['receipt_number'] ?? '';
        $validated['confidence'] = $ocrData['confidence'] ?? null;

        return $validated;
    }

    public function matchToReimbursementCategory(array $ocrData): ?int
    {
        $provider = $this->getProvider();
        $baseUrl = rtrim($provider->base_url, '/');
        $apiKey = decrypt($provider->api_key_encrypted);
        $model = $provider->default_model ?: 'gpt-4o';

        $categories = ReimbursementCategory::where('is_active', true)->get();

        if ($categories->isEmpty()) {
            return null;
        }

        if ($categories->count() === 1) {
            return $categories->first()->id;
        }

        $merchant = $ocrData['merchant_name'] ?? $ocrData['vendor_name'] ?? '';
        $items = collect($ocrData['line_items'] ?? [])->pluck('description')->implode(', ');
        $totalAmount = $ocrData['total_amount'] ?? 0;

        $categoryList = $categories->map(fn ($c) => "ID {$c->id}: {$c->name}")->implode("\n");

        $prompt = <<<PROMPT
Klasifikasikan transaksi berikut ke dalam kategori reimbursement yang paling sesuai.

Detail Transaksi:
- Merchant: {$merchant}
- Items: {$items}
- Total: Rp {$totalAmount}

Kategori yang tersedia:
{$categoryList}

HANYA kembalikan nomor ID kategori yang paling sesuai. Jangan tambahkan teks lain.
Jika tidak ada yang cocok, kembalikan 0.
PROMPT;

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ])
                ->timeout(30)
                ->post("{$baseUrl}/v1/chat/completions", [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0,
                    'max_tokens' => 10,
                ]);

            if ($response->successful()) {
                $content = trim($response->json('choices.0.message.content') ?? '0');
                $categoryId = (int) $content;

                if ($categoryId > 0 && $categories->pluck('id')->contains($categoryId)) {
                    return $categoryId;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('ReceiptOCR category matching failed: ' . $e->getMessage());
        }

        return $this->keywordBasedCategory($merchant . ' ' . $items);
    }

    public function processAndAttach(Reimbursement $reimbursement): void
    {
        $reimbursement->update(['ocr_status' => 'processing']);

        try {
            $result = $this->processReceipt($reimbursement->receipt_image_path);

            $confidence = $result['confidence'] ?? null;
            $ocrData = [
                'merchant_name' => $result['merchant_name'] ?? '',
                'transaction_date' => $result['transaction_date'] ?? '',
                'total_amount' => $result['total_amount'] ?? 0,
                'line_items' => $result['line_items'] ?? [],
                'tax_amount' => $result['tax_amount'] ?? 0,
                'payment_method' => $result['payment_method'] ?? '',
                'currency' => $result['currency'] ?? 'IDR',
                'receipt_number' => $result['receipt_number'] ?? '',
            ];

            $reimbursement->update([
                'ocr_data' => $ocrData,
                'ocr_confidence' => $confidence,
                'ocr_status' => 'completed',
            ]);

            if (!empty($result['category_id'])) {
                $reimbursement->update(['category_id' => $result['category_id']]);
            }
        } catch (\Throwable $e) {
            Log::error('ReceiptOCR processing failed: ' . $e->getMessage());

            $reimbursement->update([
                'ocr_status' => 'failed',
                'ocr_data' => ['error' => $e->getMessage()],
            ]);
        }
    }

    protected function buildReceiptPrompt(): string
    {
        return <<<PROMPT
Anda adalah sistem OCR untuk struk dan kwitansi. Ekstrak data berikut dari gambar yang diberikan. Kembalikan HANYA JSON valid tanpa markdown, tanpa komentar, tanpa text lain.

Format JSON yang diharapkan:
{
    "merchant_name": "Nama toko/restoran/vendor",
    "transaction_date": "YYYY-MM-DD",
    "total_amount": 123456,
    "tax_amount": 12345,
    "line_items": [
        {"description": "Nama item", "amount": 12345}
    ],
    "payment_method": "cash/card/transfer/qris",
    "currency": "IDR",
    "receipt_number": "Nomor struk jika ada",
    "confidence": 0.95
}

Aturan:
- total_amount adalah total keseluruhan, hanya angka (tanpa titik/koma pemisah ribuan)
- tax_amount adalah jumlah PPN/Pajak jika tercantum, jika tidak ada isi 0
- transaction_date format YYYY-MM-DD, jika tidak jelas gunakan null
- currency selalu "IDR" kecuali ada indikasi mata uang lain
- confidence adalah skor keyakinan 0.0 - 1.0 tentang kualitas ekstraksi
- Jika ada beberapa item, pisahkan sebagai line_items array
- payment_method diisi "cash", "card", "transfer", atau "qris"
- Jika data tidak ditemukan, isi dengan string kosong atau 0
- JANGAN tambahkan teks apapun selain JSON
PROMPT;
    }

    protected function parseOcrResponse(string $content): array
    {
        $content = trim($content);

        if (str_starts_with($content, '```json')) {
            $content = substr($content, 7);
        } elseif (str_starts_with($content, '```')) {
            $content = substr($content, 3);
        }

        if (str_ends_with($content, '```')) {
            $content = substr($content, 0, -3);
        }

        $content = trim($content);

        $decoded = json_decode($content, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        Log::warning('ReceiptOCR JSON parse failed', ['content' => mb_substr($content, 0, 500)]);
        return $this->fallbackParse($content);
    }

    protected function fallbackParse(string $content): array
    {
        $result = [
            'merchant_name' => '',
            'transaction_date' => '',
            'total_amount' => 0,
            'tax_amount' => 0,
            'line_items' => [],
            'payment_method' => '',
            'currency' => 'IDR',
            'receipt_number' => '',
            'confidence' => null,
        ];

        if (preg_match("~merchant_name[\"']*\s*[:=]\s*[\"']?([^\"'\n,}]+)~i", $content, $m)) {
            $result['merchant_name'] = trim($m[1]);
        } elseif (preg_match("~vendor_name[\"']*\s*[:=]\s*[\"']?([^\"'\n,}]+)~i", $content, $m)) {
            $result['merchant_name'] = trim($m[1]);
        }

        if (preg_match("~total_amount[\"']*\s*[:=]\s*(\d+)~i", $content, $m)) {
            $result['total_amount'] = (float) $m[1];
        }

        if (preg_match("~tax_amount[\"']*\s*[:=]\s*(\d+)~i", $content, $m)) {
            $result['tax_amount'] = (float) $m[1];
        }

        if (preg_match("~transaction_date[\"']*\s*[:=]\s*[\"']?(\d{4}-\d{2}-\d{2})~i", $content, $m)) {
            $result['transaction_date'] = $m[1];
        }

        if (preg_match("~payment_method[\"']*\s*[:=]\s*[\"']?(\w+)~i", $content, $m)) {
            $result['payment_method'] = trim($m[1]);
        }

        return $result;
    }

    protected function keywordBasedCategory(string $searchText): ?int
    {
        $searchText = strtolower($searchText);

        $keywordMap = [
            'transport' => ['taxi', 'gojek', 'grab', 'bensin', 'solar', 'parkir', 'tol', 'transportasi', 'travel', 'tiket', 'kereta', 'pesawat', 'bus', 'ojek', 'bbm', 'spbu', 'pertamina'],
            'makan' => ['restoran', 'makan', 'minum', 'kopi', 'cafe', 'resto', 'food', 'catering', 'sarapan', 'makan siang', 'makan malam', 'snack', 'gofood', 'grabfood'],
            'akomodasi' => ['hotel', 'penginapan', 'kamar', 'guest house', 'airbnb', 'losmen'],
            'alat_tulis' => ['atk', 'kertas', 'pulpen', 'alat tulis', 'stationery', 'printer', 'toner', 'tinta'],
            'komunikasi' => ['pulsa', 'paket data', 'internet', 'telepon', 'wifi', 'telkom', 'indihome'],
            'kesehatan' => ['obat', 'dokter', 'rumah sakit', 'apotek', 'klinik', 'medical', 'rs', 'puskesmas'],
            'entertainment' => ['entertainment', 'hiburan', 'bioskop', 'karaoke'],
        ];

        $categoryNameMap = [
            'transport' => 'Transportasi',
            'makan' => 'Makan',
            'akomodasi' => 'Akomodasi',
            'alat_tulis' => 'ATK',
            'komunikasi' => 'Komunikasi',
            'kesehatan' => 'Kesehatan',
            'entertainment' => 'Hiburan',
        ];

        foreach ($keywordMap as $type => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($searchText, $keyword)) {
                    $categoryName = $categoryNameMap[$type] ?? $type;
                    $category = ReimbursementCategory::where('name', 'like', "%{$categoryName}%")
                        ->where('is_active', true)
                        ->first();
                    if ($category) {
                        return $category->id;
                    }
                }
            }
        }

        return ReimbursementCategory::where('is_active', true)->value('id');
    }

    protected function getEmptyResult(string $error = ''): array
    {
        return [
            'merchant_name' => '',
            'transaction_date' => '',
            'total_amount' => 0,
            'tax_amount' => 0,
            'line_items' => [],
            'payment_method' => '',
            'currency' => 'IDR',
            'receipt_number' => '',
            'confidence' => null,
            'category_id' => null,
            'error' => $error,
        ];
    }
}
