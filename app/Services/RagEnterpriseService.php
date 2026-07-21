<?php

namespace App\Services;

use App\Models\AiKnowledgeBase;
use App\Models\AiProvider;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RagEnterpriseService
{
    protected ?AiProvider $provider = null;
    protected string $embeddingModel = 'text-embedding-3-small';
    protected int $chunkSize = 1000;
    protected int $chunkOverlap = 200;

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

    public function ingestDocument(AiKnowledgeBase $kb, string $filePath): int
    {
        $content = $this->extractDocumentContent($filePath);
        $title = pathinfo($filePath, PATHINFO_FILENAME);

        $chunks = $this->chunkDocument($content, $this->chunkSize);

        $chunksWithEmbeddings = [];
        foreach ($chunks as $chunk) {
            $chunksWithEmbeddings[] = [
                'text' => $chunk,
                'embedding' => $this->getEmbedding($chunk),
            ];
        }

        $kb->update([
            'title' => $kb->title ?: $title,
            'content' => $content,
            'source_type' => 'document',
            'source_path' => $filePath,
            'chunks_json' => $chunksWithEmbeddings,
            'embedding_vector' => $this->getEmbedding($content),
        ]);

        return count($chunksWithEmbeddings);
    }

    public function ingestText(AiKnowledgeBase $kb, string $title, string $content): void
    {
        $chunks = $this->chunkDocument($content, $this->chunkSize);

        $chunksWithEmbeddings = [];
        foreach ($chunks as $chunk) {
            $chunksWithEmbeddings[] = [
                'text' => $chunk,
                'embedding' => $this->getEmbedding($chunk),
            ];
        }

        $kb->update([
            'title' => $title,
            'content' => $content,
            'source_type' => 'text',
            'chunks_json' => $chunksWithEmbeddings,
            'embedding_vector' => $this->getEmbedding($content),
        ]);
    }

    public function searchRelevant(AiKnowledgeBase $kb, string $query, int $topK = 5): array
    {
        $queryEmbedding = $this->getEmbedding($query);
        $chunks = $kb->chunks_json ?? [];

        if (empty($chunks)) {
            $chunks = $this->chunkDocument($kb->content, $this->chunkSize);
            $chunks = array_map(fn($text) => ['text' => $text, 'embedding' => null], $chunks);
        }

        $scored = [];
        foreach ($chunks as $idx => $chunk) {
            $chunkEmbedding = $chunk['embedding'] ?? null;
            if (!$chunkEmbedding) {
                continue;
            }
            $score = $this->cosineSimilarity($queryEmbedding, $chunkEmbedding);
            $scored[] = [
                'chunk_index' => $idx,
                'text' => $chunk['text'],
                'score' => $score,
            ];
        }

        usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($scored, 0, $topK);
    }

    public function ask(string $question, ?int $restrictToKbId = null): array
    {
        $provider = $this->getProvider();
        $companyId = auth()->user()?->company_id;

        if ($restrictToKbId) {
            $knowledgeBases = AiKnowledgeBase::where('id', $restrictToKbId)
                ->where('is_active', true)
                ->get();
        } else {
            $knowledgeBases = $this->getRelevantKbsForUser(auth()->user());
        }

        $allCitations = [];
        $contextChunks = [];

        foreach ($knowledgeBases as $kb) {
            $relevant = $this->searchRelevant($kb, $question, 3);
            foreach ($relevant as $r) {
                $contextChunks[] = "[Sumber: {$kb->title}] {$r['text']}";
                $allCitations[] = [
                    'kb_id' => $kb->id,
                    'title' => $kb->title,
                    'excerpt' => Str::limit($r['text'], 300),
                    'score' => round($r['score'], 4),
                ];
            }
        }

        if (empty($contextChunks)) {
            return [
                'answer' => 'Maaf, tidak ditemukan dokumen yang relevan untuk pertanyaan Anda. Silakan coba kata kunci lain atau tambahkan dokumen ke knowledge base.',
                'citations' => [],
                'confidence' => 0,
            ];
        }

        $context = implode("\n\n---\n\n", $contextChunks);

        $systemPrompt = "Anda adalah asisten enterprise untuk BizOS. Gunakan HANYA konteks di bawah ini untuk menjawab pertanyaan. Jika konteks tidak cukup, katakan 'Informasi tidak tersedia di dokumen yang ada.' Jangan membuat jawaban di luar konteks yang diberikan.\n\nKONTEKS:\n{$context}";

        $answer = $this->callLlm($provider, $systemPrompt, $question);

        $topScores = array_column($allCitations, 'score');
        $avgScore = !empty($topScores) ? array_sum($topScores) / count($topScores) : 0;
        $confidence = min(1.0, max(0, $avgScore * 1.5));

        return [
            'answer' => $answer,
            'citations' => $allCitations,
            'confidence' => round($confidence, 2),
        ];
    }

    public function getRelevantKbsForUser(User $user): Collection
    {
        return AiKnowledgeBase::where('company_id', $user->company_id)
            ->where('is_active', true)
            ->get();
    }

    protected function getEmbedding(string $text): array
    {
        $provider = $this->getProvider();
        $baseUrl = rtrim($provider->base_url, '/');
        $apiKey = decrypt($provider->api_key_encrypted);
        $text = Str::limit($text, 8000);

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ])
                ->timeout(30)
                ->post("{$baseUrl}/v1/embeddings", [
                    'model' => $this->embeddingModel,
                    'input' => $text,
                ]);

            if ($response->successful()) {
                return $response->json('data.0.embedding') ?? [];
            }

            Log::error('Embedding API error', ['status' => $response->status(), 'body' => $response->body()]);
            throw new \RuntimeException('Gagal generate embedding: ' . $response->status());
        } catch (ConnectionException $e) {
            Log::error('Embedding connection error: ' . $e->getMessage());
            throw new \RuntimeException('Tidak dapat terhubung ke AI provider untuk embedding.');
        }
    }

    protected function chunkDocument(string $content, int $chunkSize = 1000): array
    {
        $content = strip_tags($content);
        $content = preg_replace('/\s+/', ' ', $content);
        $content = trim($content);

        if (mb_strlen($content) <= $chunkSize) {
            return [$content];
        }

        $paragraphs = preg_split('/\n\s*\n/', $content);
        $chunks = [];
        $currentChunk = '';

        foreach ($paragraphs as $paragraph) {
            $paragraph = trim($paragraph);
            if (empty($paragraph)) {
                continue;
            }

            if (mb_strlen($currentChunk . ' ' . $paragraph) > $chunkSize && !empty($currentChunk)) {
                $chunks[] = trim($currentChunk);
                $currentChunk = $paragraph;
            } else {
                $currentChunk = $currentChunk ? $currentChunk . ' ' . $paragraph : $paragraph;
            }
        }

        if (!empty($currentChunk)) {
            $chunks[] = trim($currentChunk);
        }

        if (empty($chunks)) {
            $chunks = mb_str_split($content, $chunkSize);
        }

        return $chunks;
    }

    protected function cosineSimilarity(array $queryEmbedding, array $chunkEmbedding): float
    {
        if (empty($queryEmbedding) || empty($chunkEmbedding)) {
            return 0.0;
        }

        $dotProduct = 0.0;
        $magnitudeA = 0.0;
        $magnitudeB = 0.0;

        $count = min(count($queryEmbedding), count($chunkEmbedding));
        for ($i = 0; $i < $count; $i++) {
            $dotProduct += $queryEmbedding[$i] * $chunkEmbedding[$i];
            $magnitudeA += $queryEmbedding[$i] * $queryEmbedding[$i];
            $magnitudeB += $chunkEmbedding[$i] * $chunkEmbedding[$i];
        }

        $magnitudeA = sqrt($magnitudeA);
        $magnitudeB = sqrt($magnitudeB);

        if ($magnitudeA == 0.0 || $magnitudeB == 0.0) {
            return 0.0;
        }

        return $dotProduct / ($magnitudeA * $magnitudeB);
    }

    protected function extractDocumentContent(string $filePath): string
    {
        if (!file_exists($filePath)) {
            throw new \RuntimeException("File tidak ditemukan: {$filePath}");
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        return match ($extension) {
            'txt' => file_get_contents($filePath),
            'pdf' => $this->extractPdfContent($filePath),
            'docx', 'doc' => $this->extractDocxContent($filePath),
            default => throw new \RuntimeException("Format file '{$extension}' tidak didukung. Gunakan PDF, DOCX, atau TXT."),
        };
    }

    protected function extractPdfContent(string $filePath): string
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

        $text = trim($text);
        if (strlen($text) < 50) {
            $text = strip_tags($content);
            $text = preg_replace('/[^\P{C}\n]/u', '', $text);
        }

        return $text ?: 'Tidak dapat mengekstrak teks dari PDF.';
    }

    protected function extractDocxContent(string $filePath): string
    {
        $zip = new \ZipArchive();
        if ($zip->open($filePath) === true) {
            $content = $zip->getFromName('word/document.xml');
            $zip->close();

            if ($content) {
                $content = strip_tags($content);
                return trim($content);
            }
        }

        return file_get_contents($filePath);
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
                    'temperature' => 0.3,
                    'max_tokens' => 2000,
                ]);

            if ($response->successful()) {
                return $response->json('choices.0.message.content') ?? 'Tidak ada respons dari AI.';
            }

            Log::error('LLM API error', ['status' => $response->status(), 'body' => $response->body()]);
            throw new \RuntimeException('Gagal memanggil AI: ' . $response->status());
        } catch (ConnectionException $e) {
            Log::error('LLM connection error: ' . $e->getMessage());
            throw new \RuntimeException('Tidak dapat terhubung ke AI provider.');
        }
    }
}
