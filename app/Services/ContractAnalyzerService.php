<?php

namespace App\Services;

use App\Models\AiProvider;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ContractAnalyzerService
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
            throw new \RuntimeException('Tidak ada AI Provider aktif dengan format openai_compatible.');
        }

        return $this->provider;
    }

    public function setProvider(AiProvider $provider): self
    {
        $this->provider = $provider;
        return $this;
    }

    public function analyzePdf(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \RuntimeException("File tidak ditemukan: {$filePath}");
        }

        $text = $this->extractTextFromPdf($filePath);

        return $this->extractContractData($text);
    }

    public function extractContractData(string $fullText): array
    {
        $provider = $this->getProvider();

        $systemPrompt = $this->contractExtractionPrompt();

        $fullText = Str::limit($fullText, 15000);

        $response = $this->callLlm($provider, $systemPrompt, "Ekstrak semua informasi kontrak dari teks berikut:\n\n{$fullText}");

        return $this->parseContractResponse($response);
    }

    public function extractClauses(string $fullText): array
    {
        $provider = $this->getProvider();

        $systemPrompt = "Anda adalah analis kontrak profesional. Ekstrak klausa-klausa penting dari kontrak berikut. Kelompokkan berdasarkan kategori: pihak_terlibat, nilai_kontrak, jangka_waktu, ketentuan_pembayaran, denda_penalti, pengakhiran, hukum_yang_berlaku, force_majeure, kerahasiaan, penyelesaian_sengketa, lain_lain.\n\nFormat output HARUS JSON valid:\n{\n  \"klausa\": [\n    {\"kategori\": \"...\", \"judul\": \"...\", \"isi\": \"...\", \"pasal\": \"...\"}\n  ]\n}\n\nHANYA keluarkan JSON, tanpa teks lain.";

        $fullText = Str::limit($fullText, 15000);

        $response = $this->callLlm($provider, $systemPrompt, $fullText);

        $data = json_decode($response, true);
        return $data['klausa'] ?? [];
    }

    public function compareContracts(array $contractIds): array
    {
        $provider = $this->getProvider();

        $contracts = \App\Models\AiKnowledgeBase::whereIn('id', $contractIds)
            ->where('is_active', true)
            ->get();

        if ($contracts->count() < 2) {
            throw new \RuntimeException('Minimal 2 kontrak diperlukan untuk perbandingan.');
        }

        $contractTexts = [];
        foreach ($contracts as $contract) {
            $contractTexts[] = "KONTRAK: {$contract->title}\n" . Str::limit($contract->content, 5000);
        }

        $combined = implode("\n\n---\n\n", $contractTexts);

        $systemPrompt = "Anda adalah analis perbandingan kontrak. Bandingkan kontrak-kontrak di bawah ini dan identifikasi persamaan dan perbedaan kunci. Format output HARUS JSON valid:\n{\n  \"perbandingan\": [\n    {\"aspek\": \"nama aspek\", \"nilai_per_kontrak\": {\"Kontrak 1\": \"...\", \"Kontrak 2\": \"...\"}, \"catatan\": \"...\"}\n  ],\n  \"risiko\": \"ringkasan risiko utama\",\n  \"rekomendasi\": \"rekomendasi\"\n}\n\nHANYA keluarkan JSON.";

        $response = $this->callLlm($provider, $systemPrompt, $combined);

        $data = json_decode($response, true);
        return $data ?: [];
    }

    public function searchAcrossContracts(string $query, array $contractIds = []): array
    {
        $query = AiKnowledgeBase::where('is_active', true)
            ->where('source_type', 'document');

        if (!empty($contractIds)) {
            $query->whereIn('id', $contractIds);
        }

        $contracts = $query->get();

        if ($contracts->isEmpty()) {
            return [];
        }

        $ragService = app(RagEnterpriseService::class);
        if ($this->provider) {
            $ragService->setProvider($this->provider);
        }

        $results = [];
        foreach ($contracts as $contract) {
            $relevant = $ragService->searchRelevant($contract, $query, 3);
            foreach ($relevant as $r) {
                if ($r['score'] > 0.5) {
                    $results[] = [
                        'contract_id' => $contract->id,
                        'contract_title' => $contract->title,
                        'excerpt' => Str::limit($r['text'], 300),
                        'score' => round($r['score'], 4),
                    ];
                }
            }
        }

        usort($results, fn($a, $b) => $b['score'] <=> $a['score']);

        return $results;
    }

    protected function contractExtractionPrompt(): string
    {
        return "Anda adalah analis kontrak profesional. Ekstrak informasi berikut dari teks kontrak dalam Bahasa Indonesia. Jika informasi tidak ditemukan, gunakan nilai null.\n\nFormat output HARUS JSON valid:\n{\n  \"para_pihak\": [\n    {\"nama\": \"...\", \"peran\": \"pihak_pertama / pihak_kedua / ...\", \"alamat\": \"...\"}\n  ],\n  \"nilai_kontrak\": {\"jumlah\": 12345678, \"mata_uang\": \"IDR\", \"terbilang\": \"...\"},\n  \"tanggal_mulai\": \"YYYY-MM-DD\",\n  \"tanggal_berakhir\": \"YYYY-MM-DD\",\n  \"jangka_waktu\": \"...\",\n  \"ketentuan_pembayaran\": [\n    {\"tahap\": \"DP / Termin 1 / Pelunasan\", \"persentase\": 30, \"jumlah\": 123456, \"deskripsi\": \"...\"}\n  ],\n  \"denda_penalti\": [\n    {\"jenis\": \"keterlambatan / wanprestasi / ...\", \"besaran\": \"...\", \"deskripsi\": \"...\"}\n  ],\n  \"ketentuan_pengakhiran\": [\n    {\"alasan\": \"...\", \"prosedur\": \"...\", \"konsekuensi\": \"...\"}\n  ],\n  \"hukum_yang_berlaku\": \"...\",\n  \"force_majeure\": \"...\",\n  \"kerahasiaan\": \"...\",\n  \"penyelesaian_sengketa\": \"...\",\n  \"jangka_waktu_garansi\": \"...\",\n  \"ringkasan\": \"ringkasan kontrak dalam 2-3 kalimat bahasa Indonesia\"\n}\n\nHANYA keluarkan JSON, tanpa teks lain, tanpa markdown code block.";
    }

    protected function parseContractResponse(string $jsonResponse): array
    {
        $jsonResponse = trim($jsonResponse);
        $jsonResponse = preg_replace('/^```(?:json)?\s*/i', '', $jsonResponse);
        $jsonResponse = preg_replace('/\s*```$/i', '', $jsonResponse);

        $data = json_decode($jsonResponse, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Contract analysis JSON parse error', [
                'error' => json_last_error_msg(),
                'response' => $jsonResponse,
            ]);
            return [
                'error' => 'Gagal memparse hasil analisis kontrak.',
                'raw_response' => $jsonResponse,
            ];
        }

        return $data ?: [];
    }

    protected function callLlm(AiProvider $provider, string $systemPrompt, string $userMessage): string
    {
        $baseUrl = rtrim($provider->base_url, '/');
        $apiKey = decrypt($provider->api_key_encrypted);
        $model = $provider->default_model ?: 'gpt-4o-mini';

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ])
                ->timeout(90)
                ->post("{$baseUrl}/v1/chat/completions", [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userMessage],
                    ],
                    'temperature' => 0.1,
                    'max_tokens' => 4000,
                ]);

            if ($response->successful()) {
                return $response->json('choices.0.message.content') ?? '';
            }

            Log::error('Contract Analyzer LLM error', ['status' => $response->status()]);
            return '';
        } catch (ConnectionException $e) {
            Log::error('Contract Analyzer connection error: ' . $e->getMessage());
            return '';
        }
    }

    protected function extractTextFromPdf(string $filePath): string
    {
        if (class_exists(\Smalot\PdfParser\Parser::class)) {
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($filePath);
            return $pdf->getText();
        }

        $content = file_get_contents($filePath);
        $text = '';
        $content = mb_convert_encoding($content, 'UTF-8', 'ISO-8859-1');

        preg_match_all('/\/Text\s*<\s*([^>]+)/i', $content, $matches);
        foreach ($matches[1] as $hex) {
            $text .= hex2bin(str_replace([' ', "\n", "\r"], '', $hex)) . ' ';
        }

        return trim($text) ?: 'Tidak dapat mengekstrak teks dari PDF.';
    }
}
