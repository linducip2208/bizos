<?php

namespace App\Services;

use App\Models\AiKnowledgeBase;
use App\Models\AiProvider;
use App\Models\Employee;
use App\Models\Ticket;
use App\Models\TicketCategory;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TicketTriageService
{
    protected ?AiProvider $provider = null;
    protected ?RagEnterpriseService $ragService = null;

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

    public function categorize(Ticket $ticket): TicketCategory
    {
        $provider = $this->getProvider();
        $categories = TicketCategory::where('company_id', $ticket->company_id)
            ->where('is_active', true)
            ->get();

        if ($categories->isEmpty()) {
            throw new \RuntimeException('Tidak ada kategori tiket aktif.');
        }

        $categoryList = $categories->map(fn($c) => "- {$c->name} (ID: {$c->id})")->implode("\n");

        $systemPrompt = "Anda adalah sistem triase tiket helpdesk. Kategorikan tiket berikut ke salah satu kategori yang tersedia. HANYA keluarkan ID kategori (angka saja), tanpa teks lain.\n\nKategori tersedia:\n{$categoryList}";

        $userMessage = "Judul: {$ticket->subject}\nDeskripsi: {$ticket->description}";

        $response = $this->callLlm($provider, $systemPrompt, $userMessage);
        $categoryId = (int) trim($response);

        $category = $categories->firstWhere('id', $categoryId);

        if (!$category) {
            foreach ($categories as $cat) {
                if (stripos($response, $cat->name) !== false) {
                    $category = $cat;
                    break;
                }
            }
        }

        if (!$category) {
            $category = $categories->first();
        }

        if ($ticket->category_id !== $category->id) {
            $ticket->update(['category_id' => $category->id]);
        }

        return $category;
    }

    public function prioritize(Ticket $ticket): string
    {
        $provider = $this->getProvider();

        $systemPrompt = "Anda adalah sistem prioritas tiket helpdesk. Tentukan prioritas tiket berikut berdasarkan kata kunci dan sentimen. HANYA keluarkan salah satu: low, medium, high, urgent. Tanpa teks lain.\n\nPanduan:\n- urgent: sistem down, kebocoran data, kehilangan uang, pelanggan VIP komplain\n- high: fitur utama tidak berfungsi, deadline <24 jam, banyak pengguna terdampak\n- medium: bug non-kritis, permintaan fitur, pertanyaan teknis\n- low: pertanyaan umum, permintaan minor, enhancement";

        $userMessage = "Judul: {$ticket->subject}\nDeskripsi: {$ticket->description}";

        $response = strtolower(trim($this->callLlm($provider, $systemPrompt, $userMessage)));

        $validPriorities = ['low', 'medium', 'high', 'urgent'];
        if (!in_array($response, $validPriorities)) {
            $response = $this->keywordPriority($ticket);
        }

        if ($ticket->priority !== $response) {
            $ticket->update(['priority' => $response]);
        }

        return $response;
    }

    public function suggestAssignee(Ticket $ticket): ?Employee
    {
        $resolvedCounts = Ticket::where('company_id', $ticket->company_id)
            ->where('status', 'resolved')
            ->whereNotNull('assigned_to')
            ->when($ticket->category_id, fn($q) => $q->where('category_id', $ticket->category_id))
            ->select('assigned_to', \Illuminate\Support\Facades\DB::raw('COUNT(*) as resolved'))
            ->groupBy('assigned_to')
            ->orderByDesc('resolved')
            ->limit(3)
            ->pluck('assigned_to');

        if ($resolvedCounts->isEmpty()) {
            return Employee::where('company_id', $ticket->company_id)
                ->where('status', 'active')
                ->orderBy('id')
                ->first();
        }

        return Employee::whereIn('id', $resolvedCounts)
            ->where('status', 'active')
            ->first();
    }

    public function findSimilarResolved(Ticket $ticket, int $limit = 5): array
    {
        $provider = $this->getProvider();

        $resolvedTickets = Ticket::where('company_id', $ticket->company_id)
            ->where('status', 'resolved')
            ->where('id', '!=', $ticket->id)
            ->whereNotNull('description')
            ->latest('resolved_at')
            ->limit(50)
            ->get();

        if ($resolvedTickets->isEmpty()) {
            return [];
        }

        $resolvedList = $resolvedTickets->map(fn($t) => "ID: {$t->id} | Subjek: {$t->subject} | Solusi: " . Str::limit($t->replies()->where('is_internal', false)->latest()->value('message') ?? 'Tidak ada', 200))->implode("\n---\n");

        $systemPrompt = "Anda adalah sistem pencarian tiket serupa. Cari tiket resolved yang paling mirip dengan tiket baru. HANYA keluarkan JSON array berisi ID tiket yang mirip, format: [123, 456, 789]. Maksimal {$limit} ID. Tanpa teks lain.";

        $userMessage = "TIKET BARU:\nJudul: {$ticket->subject}\nDeskripsi: {$ticket->description}\n\nTIKET RESOLVED:\n{$resolvedList}";

        $response = $this->callLlm($provider, $systemPrompt, $userMessage);
        $ids = json_decode(trim($response), true);

        if (!is_array($ids)) {
            return [];
        }

        $limit = min($limit, count($ids));
        $ids = array_slice(array_map('intval', $ids), 0, $limit);

        return Ticket::whereIn('id', $ids)->get()->toArray();
    }

    public function suggestKbArticle(Ticket $ticket, int $limit = 3): array
    {
        $ragService = $this->getRagService();

        $knowledgeBases = AiKnowledgeBase::where('company_id', $ticket->company_id)
            ->where('is_active', true)
            ->get();

        if ($knowledgeBases->isEmpty()) {
            return [];
        }

        $allResults = [];
        foreach ($knowledgeBases as $kb) {
            $searchResults = $ragService->searchRelevant($kb, $ticket->subject . ' ' . $ticket->description, $limit);
            foreach ($searchResults as $result) {
                if ($result['score'] > 0.4) {
                    $allResults[] = [
                        'kb_id' => $kb->id,
                        'title' => $kb->title,
                        'excerpt' => Str::limit($result['text'], 250),
                        'score' => round($result['score'], 4),
                    ];
                }
            }
        }

        usort($allResults, fn($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($allResults, 0, $limit);
    }

    public function suggestReply(Ticket $ticket): string
    {
        $provider = $this->getProvider();

        $ticketHistory = $ticket->replies()
            ->where('is_internal', false)
            ->orderBy('created_at')
            ->get()
            ->map(fn($r) => ($r->employee_id ? 'Agent' : 'Customer') . ': ' . $r->message)
            ->implode("\n\n");

        $kbArticles = $this->suggestKbArticle($ticket, 2);
        $kbContext = '';
        foreach ($kbArticles as $article) {
            $kbContext .= "Artikel KB: {$article['title']}\n{$article['excerpt']}\n\n";
        }

        $systemPrompt = "Anda adalah agen helpdesk profesional untuk BizOS. Buat balasan yang sopan, membantu, dan profesional dalam Bahasa Indonesia. Gunakan informasi dari knowledge base jika relevan. Balasan harus langsung bisa dikirim ke pelanggan. JANGAN gunakan placeholder seperti [Nama].";

        $userMessage = "TIKET:\nJudul: {$ticket->subject}\nDeskripsi: {$ticket->description}\nPrioritas: {$ticket->priority}\n\nRIWAYAT PERCAKAPAN:\n{$ticketHistory}\n\nKNOWLEDGE BASE:\n{$kbContext}\n\nBuat balasan yang sesuai.";

        return $this->callLlm($provider, $systemPrompt, $userMessage);
    }

    public function triageNewTicket(Ticket $ticket): void
    {
        try {
            if (!$ticket->category_id) {
                $this->categorize($ticket);
            }

            $this->prioritize($ticket);

            if (!$ticket->assigned_to) {
                $assignee = $this->suggestAssignee($ticket);
                if ($assignee) {
                    $ticket->update(['assigned_to' => $assignee->id]);
                    $ticket->activities()->create([
                        'employee_id' => null,
                        'activity_type' => 'auto_assigned',
                        'new_value' => (string) $assignee->id,
                        'created_at' => now(),
                    ]);
                }
            }

            Log::info('Ticket triaged', [
                'ticket_id' => $ticket->id,
                'category_id' => $ticket->category_id,
                'priority' => $ticket->priority,
                'assigned_to' => $ticket->assigned_to,
            ]);
        } catch (\Exception $e) {
            Log::error('Ticket triage failed: ' . $e->getMessage(), [
                'ticket_id' => $ticket->id,
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    protected function keywordPriority(Ticket $ticket): string
    {
        $text = strtolower($ticket->subject . ' ' . $ticket->description);

        $urgent = ['down', 'tidak bisa akses', 'error sistem', 'data hilang', 'kebocoran', 'kehilangan uang', 'server down', 'urgent', 'darurat', 'segera'];
        $high = ['error', 'bug', 'tidak berfungsi', 'rusak', 'gagal', 'deadline', 'secepatnya'];
        $low = ['pertanyaan', 'info', 'tanya', 'enhancement', 'saran', 'request minor'];

        foreach ($urgent as $word) {
            if (str_contains($text, $word)) return 'urgent';
        }
        foreach ($high as $word) {
            if (str_contains($text, $word)) return 'high';
        }
        foreach ($low as $word) {
            if (str_contains($text, $word)) return 'low';
        }

        return 'medium';
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
                    'temperature' => 0.2,
                    'max_tokens' => 1000,
                ]);

            if ($response->successful()) {
                return $response->json('choices.0.message.content') ?? '';
            }

            Log::error('Ticket Triage LLM error', ['status' => $response->status()]);
            return '';
        } catch (ConnectionException $e) {
            Log::error('Ticket Triage connection error: ' . $e->getMessage());
            return '';
        }
    }

    protected function getRagService(): RagEnterpriseService
    {
        if (!$this->ragService) {
            $this->ragService = app(RagEnterpriseService::class);
            if ($this->provider) {
                $this->ragService->setProvider($this->provider);
            }
        }
        return $this->ragService;
    }
}
