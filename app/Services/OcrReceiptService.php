<?php

namespace App\Services;

use App\Models\AiProvider;
use App\Models\Budget;
use App\Models\BudgetItem;
use App\Models\Reimbursement;
use App\Models\ReimbursementCategory;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class OcrReceiptService
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
        $provider = $this->getProvider();
        $baseUrl = rtrim($provider->base_url, '/');
        $apiKey = decrypt($provider->api_key_encrypted);
        $model = $provider->default_model ?: 'gpt-4o';

        if (!Storage::disk('public')->exists($imagePath)) {
            $fullPath = $imagePath;
        } else {
            $fullPath = Storage::disk('public')->path($imagePath);
        }

        if (!file_exists($fullPath)) {
            throw new \RuntimeException('File gambar struk tidak ditemukan: ' . $imagePath);
        }

        $mimeType = mime_content_type($fullPath) ?: 'image/jpeg';
        $base64Image = base64_encode(file_get_contents($fullPath));
        $dataUri = "data:{$mimeType};base64,{$base64Image}";

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
                                ['type' => 'image_url', 'image_url' => ['url' => $dataUri]],
                            ],
                        ],
                    ],
                    'temperature' => 0.1,
                    'max_tokens' => 2000,
                ]);

            if ($response->successful()) {
                $content = $response->json('choices.0.message.content') ?? '{}';
                return $this->parseOcrResponse($content);
            }

            Log::error('OCR LLM error', ['status' => $response->status(), 'body' => $response->body()]);
            return $this->getEmptyOcrResult();
        } catch (ConnectionException $e) {
            Log::error('OCR connection error: ' . $e->getMessage());
            return $this->getEmptyOcrResult();
        }
    }

    public function createReimbursementDraft(array $ocrResult, int $employeeId): Reimbursement
    {
        $category = $this->categorize($ocrResult);

        return Reimbursement::create([
            'employee_id' => $employeeId,
            'category_id' => $category?->id,
            'date' => $ocrResult['transaction_date'] ?? now()->toDateString(),
            'amount' => $ocrResult['total_amount'] ?? 0,
            'description' => $this->buildDescription($ocrResult),
            'status' => 'draft',
        ]);
    }

    public function detectDuplicate(array $ocrResult, int $employeeId): bool
    {
        $amount = $ocrResult['total_amount'] ?? 0;
        $vendor = $ocrResult['vendor_name'] ?? '';
        $date = $ocrResult['transaction_date'] ?? '';

        if (!$amount && !$vendor && !$date) {
            return false;
        }

        return Reimbursement::where('employee_id', $employeeId)
            ->where('amount', $amount)
            ->where('description', 'like', "%{$vendor}%")
            ->whereDate('date', $date)
            ->exists();
    }

    public function categorize(array $ocrResult): ?ReimbursementCategory
    {
        $vendor = $ocrResult['vendor_name'] ?? '';
        $items = $ocrResult['line_items'] ?? [];
        $descriptions = collect($items)->pluck('description')->implode(' ');

        $keywordMap = [
            'transport' => ['taxi', 'gojek', 'grab', 'bensin', 'solar', 'parkir', 'tol', 'transportasi', 'travel', 'tiket', 'kereta', 'pesawat', 'bus', 'ojek', 'bbm', 'spbu', 'pertamina'],
            'makan' => ['restoran', 'makan', 'minum', 'kopi', 'cafe', 'resto', 'food', 'catering', 'sarapan', 'makan siang', 'makan malam', 'snack', 'gofood', 'grabfood'],
            'akomodasi' => ['hotel', 'penginapan', 'kamar', 'guest house', 'airbnb', 'losmen'],
            'alat_tulis' => ['atk', 'kertas', 'pulpen', 'alat tulis', 'stationery', 'printer', 'toner', 'tinta'],
            'komunikasi' => ['pulsa', 'paket data', 'internet', 'telepon', 'wifi', 'telkom', 'indihome'],
            'kesehatan' => ['obat', 'dokter', 'rumah sakit', 'apotek', 'klinik', 'medical', 'rs', 'puskesmas'],
            'entertainment' => ['entertainment', 'hiburan', 'bioskop', 'karaoke'],
        ];

        $searchText = strtolower($vendor . ' ' . $descriptions);

        foreach ($keywordMap as $categoryType => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($searchText, $keyword)) {
                    return ReimbursementCategory::where('name', 'like', "%{$this->getCategoryNameFromType($categoryType)}%")
                        ->where('is_active', true)
                        ->first();
                }
            }
        }

        return ReimbursementCategory::where('is_active', true)->first();
    }

    public function checkBudget(int $categoryId, float $amount, int $departmentId): array
    {
        $currentYear = now()->year;
        $currentMonth = now()->month;

        $budget = Budget::where('department_id', $departmentId)
            ->where('fiscal_year', $currentYear)
            ->whereIn('status', ['approved', 'active'])
            ->first();

        if (!$budget) {
            return [
                'within_budget' => true,
                'remaining' => null,
                'budget_total' => null,
                'note' => 'Tidak ada budget departemen yang ditemukan untuk tahun ini.',
            ];
        }

        $totalPlanned = $budget->budgetItems()->sum('planned_amount');
        $totalActual = $budget->budgetItems()->sum('actual_amount');

        $remaining = $totalPlanned - $totalActual;

        return [
            'within_budget' => ($totalActual + $amount) <= $totalPlanned,
            'remaining' => $remaining,
            'budget_total' => $totalPlanned,
            'spent' => $totalActual,
            'budget_name' => $budget->name,
        ];
    }

    protected function buildReceiptPrompt(): string
    {
        return <<<PROMPT
Anda adalah sistem OCR untuk struk dan kwitansi. Ekstrak data berikut dari gambar yang diberikan. Kembalikan HANYA JSON valid tanpa markdown, tanpa komentar, tanpa text lain.

Format JSON yang diharapkan:
{
    "vendor_name": "Nama toko/restoran/vendor",
    "transaction_date": "YYYY-MM-DD",
    "total_amount": 123456,
    "tax_amount": 12345,
    "line_items": [
        {"description": "Nama item", "amount": 12345}
    ],
    "payment_method": "cash/card/transfer/qris",
    "receipt_number": "Nomor struk jika ada"
}

Aturan:
- total_amount adalah total keseluruhan dalam Rupiah (angka saja, tanpa titik/koma)
- tax_amount adalah jumlah PPN/Pajak jika tercantum, jika tidak ada isi 0
- transaction_date format YYYY-MM-DD, jika tidak jelas gunakan null
- Jika ada beberapa item, pisahkan sebagai line_items array
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
            return array_merge($this->getEmptyOcrResult(), $decoded);
        }

        Log::warning('OCR response JSON parse failed', ['content' => mb_substr($content, 0, 500)]);
        return $this->fallbackParse($content);
    }

    protected function fallbackParse(string $content): array
    {
        $result = $this->getEmptyOcrResult();

        if (preg_match('/vendor_name["' . "\'" . ']?\s*[:=]\s*["' . "\'" . ']?([^"' . "\'" . "\n" . ',}]+)/i', $content, $m)) {
            $result['vendor_name'] = trim($m[1]);
        }

        if (preg_match('/total_amount["' . "\'" . ']?\s*[:=]\s*(\d+)/i', $content, $m)) {
            $result['total_amount'] = (float) $m[1];
        }

        if (preg_match('/tax_amount["' . "\'" . ']?\s*[:=]\s*(\d+)/i', $content, $m)) {
            $result['tax_amount'] = (float) $m[1];
        }

        if (preg_match('/transaction_date["' . "\'" . ']?\s*[:=]\s*["' . "\'" . ']?(\d{4}-\d{2}-\d{2})/i', $content, $m)) {
            $result['transaction_date'] = $m[1];
        }

        return $result;
    }

    protected function getEmptyOcrResult(): array
    {
        return [
            'vendor_name' => '',
            'transaction_date' => '',
            'total_amount' => 0,
            'tax_amount' => 0,
            'line_items' => [],
            'payment_method' => '',
            'receipt_number' => '',
        ];
    }

    protected function buildDescription(array $ocrResult): string
    {
        $vendor = $ocrResult['vendor_name'] ?? '';
        $receipt = $ocrResult['receipt_number'] ?? '';
        $items = $ocrResult['line_items'] ?? [];
        $itemDesc = collect($items)->pluck('description')->take(3)->implode(', ');

        $parts = array_filter([$vendor, $receipt, $itemDesc]);
        return implode(' - ', $parts) ?: 'Reimbursement dari struk';
    }

    protected function getCategoryNameFromType(string $type): string
    {
        $map = [
            'transport' => 'Transportasi',
            'makan' => 'Makan',
            'akomodasi' => 'Akomodasi',
            'alat_tulis' => 'ATK',
            'komunikasi' => 'Komunikasi',
            'kesehatan' => 'Kesehatan',
            'entertainment' => 'Hiburan',
        ];
        return $map[$type] ?? $type;
    }
}
