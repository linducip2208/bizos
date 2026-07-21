<?php

namespace App\Services;

use App\Models\AiProvider;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiWriteService
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

    public function generate(array $params): string
    {
        $prompt = $params['prompt'] ?? '';
        $context = $params['context'] ?? '';
        $tone = $params['tone'] ?? 'formal';
        $language = $params['language'] ?? 'id';

        $systemPrompt = $this->buildSystemPrompt($tone, $language);

        $userMessage = $prompt;
        if ($context) {
            $userMessage = "KONTEKS: {$context}\n\nTUGAS: {$prompt}";
        }

        return $this->callLlm($systemPrompt, $userMessage);
    }

    public function summarize(string $text, int $maxBullets = 3): string
    {
        $systemPrompt = "Anda adalah asisten ringkasan profesional. Ringkas teks berikut dalam {$maxBullets} poin kunci dalam bahasa Indonesia. Format sebagai bullet points pendek. Tidak perlu pembukaan atau penutup.";

        return $this->callLlm($systemPrompt, $text);
    }

    public function rewrite(string $text, string $tone): string
    {
        $toneMap = [
            'formal' => 'formal dan profesional',
            'casual' => 'santai dan ramah',
            'persuasive' => 'persuasif dan meyakinkan',
            'empathetic' => 'empatik dan pengertian',
        ];

        $toneDesc = $toneMap[$tone] ?? $tone;

        $systemPrompt = "Anda adalah asisten penulisan ulang teks. Tulis ulang teks berikut dengan gaya {$toneDesc} dalam bahasa Indonesia. Pertahankan makna dan informasi yang sama. Hanya keluarkan teks hasil rewrite, tanpa pembukaan atau penutup.";

        return $this->callLlm($systemPrompt, $text);
    }

    public function translate(string $text, string $toLang = 'id'): string
    {
        $langMap = [
            'id' => 'Bahasa Indonesia',
            'en' => 'Bahasa Inggris',
        ];

        $targetLang = $langMap[$toLang] ?? $toLang;

        $systemPrompt = "Anda adalah penerjemah profesional. Terjemahkan teks berikut ke {$targetLang}. Pertahankan makna, nada, dan gaya asli. Hanya keluarkan teks hasil terjemahan, tanpa pembukaan atau penutup.";

        return $this->callLlm($systemPrompt, $text);
    }

    public function expand(string $text, string $direction): string
    {
        $directionMap = [
            'longer' => 'lebih panjang dan detail',
            'examples' => 'dengan contoh konkret',
            'explain' => 'dengan penjelasan mendalam',
        ];

        $directionDesc = $directionMap[$direction] ?? $direction;

        $systemPrompt = "Anda adalah asisten penulisan konten. Kembangkan teks berikut {$directionDesc} dalam bahasa Indonesia. Pertahankan poin utama. Hanya keluarkan teks hasil pengembangan, tanpa pembukaan atau penutup.";

        return $this->callLlm($systemPrompt, $text);
    }

    public function fixGrammar(string $text): string
    {
        $systemPrompt = "Anda adalah editor bahasa Indonesia profesional. Perbaiki tata bahasa, ejaan, tanda baca, dan struktur kalimat pada teks berikut. Jangan ubah makna. Hanya keluarkan teks yang sudah diperbaiki, tanpa pembukaan atau penutup.";

        return $this->callLlm($systemPrompt, $text);
    }

    protected function buildSystemPrompt(string $tone, string $language): string
    {
        $toneMap = [
            'formal' => 'formal dan profesional',
            'casual' => 'santai dan ramah',
            'persuasive' => 'persuasif dan meyakinkan',
            'empathetic' => 'empatik dan pengertian',
        ];

        $toneDesc = $toneMap[$tone] ?? $tone;
        $lang = $language === 'en' ? 'Bahasa Inggris' : 'Bahasa Indonesia';

        return "Anda adalah asisten penulisan AI untuk aplikasi bisnis BizOS. Tulis dalam {$lang} dengan gaya {$toneDesc}. Jawab langsung tanpa pembukaan atau penutup yang tidak perlu. Hasil harus siap pakai dan langsung bisa digunakan.";
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
                ->timeout(60)
                ->post("{$baseUrl}/v1/chat/completions", [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userMessage],
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 2000,
                ]);

            if ($response->successful()) {
                return $response->json('choices.0.message.content') ?? 'Tidak ada respons dari AI.';
            }

            Log::error('AI Write LLM error', ['status' => $response->status(), 'body' => $response->body()]);
            return 'Maaf, terjadi kesalahan saat memanggil AI. Silakan coba lagi.';
        } catch (ConnectionException $e) {
            Log::error('AI Write connection error: ' . $e->getMessage());
            return 'Maaf, tidak dapat terhubung ke AI provider. Periksa koneksi dan coba lagi.';
        }
    }
}
