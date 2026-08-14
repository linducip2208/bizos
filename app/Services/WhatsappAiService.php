<?php

namespace App\Services;

use App\Models\AiProvider;
use App\Models\Client;
use App\Models\ClientContact;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\SalesOrder;
use App\Models\Ticket;
use App\Models\WaAutoReply;
use App\Models\WaConversation;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappAiService
{
    protected const INTENT_CLASSIFY_PROMPT = <<<'PROMPT'
Anda adalah classifier intent untuk pesan WhatsApp bisnis.
Klasifikasikan pesan pengguna ke dalam salah satu intent berikut:
- sales_inquiry: pertanyaan tentang produk, harga, atau pembelian
- order_status: menanyakan status pesanan, tracking, pengiriman
- invoice_query: pertanyaan tentang invoice, tagihan, pembayaran
- support_request: keluhan, masalah teknis, butuh bantuan
- general: pertanyaan umum tentang perusahaan, jam operasional, alamat
- greeting: salam pembuka, halo, perkenalan

HANYA keluarkan JSON dengan format: {"intent":"...", "confidence":0.0-1.0, "is_human_escalation":false}
Jangan tambahkan teks lain.
PROMPT;

    protected ?AiProvider $provider = null;
    protected ?WaAutoReply $autoReplyConfig = null;
    protected ?string $currentSenderPhone = null;

    public function handleIncomingMessage(array $payload): void
    {
        $entry = $payload['entry'][0] ?? null;
        if (!$entry) return;

        $changes = $entry['changes'][0] ?? null;
        if (!$changes || ($changes['field'] ?? '') !== 'messages') return;

        $value = $changes['value'] ?? [];
        $contacts = $value['contacts'] ?? [];
        $messages = $value['messages'] ?? [];

        foreach ($messages as $message) {
            $from = $message['from'] ?? null;
            if (!$from) continue;

            $messageText = $this->extractMessageText($message);
            if (empty($messageText)) continue;

            $contact = collect($contacts)->firstWhere('wa_id', $from);
            $conversation = $this->findOrCreateConversation($from, $contact);
            if (!$conversation) continue;

            $this->currentSenderPhone = $from;

            $conversation->update([
                'last_message' => $messageText,
                'last_message_at' => now(),
                'unread_count' => $conversation->unread_count + 1,
                'status' => 'aktif',
            ]);

            $conversation->addToFlowHistory('customer', $messageText);

            $this->autoReplyConfig = WaAutoReply::where('company_id', $conversation->company_id)
                ->where('is_active', true)
                ->where('is_ai_powered', true)
                ->first();

            if (!$this->autoReplyConfig) continue;

            $context = $this->buildContext($from);
            $intentResult = $this->classifyIntent($messageText);

            $intent = $intentResult['intent'] ?? 'general';
            $confidence = $intentResult['confidence'] ?? 0.5;

            $conversation->update([
                'chatbot_intent' => $intent,
                'chatbot_confidence' => $confidence,
            ]);

            if ($this->shouldEscalateToHuman($messageText, $intentResult)) {
                $reply = $this->autoReplyConfig->fallback_message
                    ?: 'Maaf, pertanyaan Anda memerlukan bantuan tim kami. Mohon tunggu, Anda akan segera terhubung dengan agent.';

                $this->sendWhatsappReply($from, $reply);
                $this->createTicketFromConversation($conversation->id, $messageText);
                $conversation->update(['last_bot_message_at' => now()]);
                $conversation->addToFlowHistory('bot', $reply);
                continue;
            }

            try {
                $reply = $this->handleIntent($intent, $messageText, $context);
                if ($reply === null || trim($reply) === '') {
                    $reply = $this->getAiResponse($messageText, $from, $context);
                }
            } catch (\Exception $e) {
                Log::error('WhatsappAiService error', ['error' => $e->getMessage()]);
                $reply = $this->autoReplyConfig->fallback_message
                    ?: 'Maaf, terjadi kesalahan. Silakan coba lagi atau hubungi tim kami.';
            }

            $this->sendWhatsappReply($from, $reply);
            $conversation->update([
                'last_message' => $reply,
                'last_message_at' => now(),
                'last_bot_message_at' => now(),
            ]);
            $conversation->addToFlowHistory('bot', $reply);
        }
    }

    public function isAiAutoReplyEnabled(): bool
    {
        return WaAutoReply::where('is_active', true)
            ->where('is_ai_powered', true)
            ->exists();
    }

    public function getAiResponse(string $message, string $senderPhone, array $context): string
    {
        $provider = $this->resolveProvider();

        if (!$provider) {
            return 'Maaf, layanan AI belum dikonfigurasi.';
        }

        $baseUrl = rtrim($provider->base_url, '/');
        $apiKey = Crypt::decrypt($provider->api_key_encrypted);
        $model = $provider->default_model ?: 'gpt-4o-mini';

        $systemPrompt = $this->buildSystemPrompt($context);
        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $message],
        ];

        $payload = [
            'model' => $model,
            'messages' => $messages,
            'temperature' => 0.7,
            'max_tokens' => 2000,
        ];

        $startTime = microtime(true);

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ])
                ->timeout(60)
                ->post("{$baseUrl}/v1/chat/completions", $payload);

            $elapsed = round((microtime(true) - $startTime) * 1000);

            if ($response->successful()) {
                $content = $response->json('choices.0.message.content');
                Log::info('WhatsappAiService response', [
                    'elapsed_ms' => $elapsed,
                    'model' => $model,
                    'phone' => $senderPhone,
                ]);
                return $content ?: 'Maaf, tidak ada respons dari AI.';
            }

            Log::error('WhatsappAiService LLM error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return 'Maaf, terjadi kesalahan saat memproses pesan Anda.';
        } catch (ConnectionException $e) {
            Log::error('WhatsappAiService connection error: ' . $e->getMessage());
            return 'Maaf, tidak dapat terhubung ke AI provider.';
        }
    }

    public function buildContext(string $senderPhone): array
    {
        $context = [];
        $phone = $this->normalizePhoneForQuery($senderPhone);

        $contact = ClientContact::where('phone', 'like', "%{$phone}%")->first();
        $client = $contact ? $contact->client : null;

        if (!$client) {
            $client = Client::where('phone', 'like', "%{$phone}%")->first();
        }

        if (!$client) {
            $lead = Lead::where('phone', 'like', "%{$phone}%")
                ->orderByDesc('created_at')
                ->first();

            if ($lead) {
                $context['lead'] = [
                    'name' => trim($lead->first_name . ' ' . $lead->last_name),
                    'company' => $lead->company_name,
                    'status' => $lead->status,
                    'industry' => $lead->industry,
                ];
            }

            return $context;
        }

        $context['client'] = [
            'name' => $client->name,
            'type' => $client->client_type,
            'industry' => $client->industry,
            'city' => $client->city,
            'phone' => $client->phone,
            'email' => $client->email,
        ];

        $recentOrders = SalesOrder::where('client_id', $client->id)
            ->orderByDesc('order_date')
            ->limit(5)
            ->get();

        if ($recentOrders->isNotEmpty()) {
            $context['recent_orders'] = $recentOrders->map(fn($so) => [
                'so_number' => $so->so_number,
                'order_date' => $so->order_date?->format('Y-m-d'),
                'total' => (float) $so->total,
                'status' => $so->status,
                'expected_delivery' => $so->expected_delivery?->format('Y-m-d'),
            ])->toArray();
        }

        $recentInvoices = Invoice::whereHas('salesOrder', fn($q) => $q->where('client_id', $client->id))
            ->orderByDesc('invoice_date')
            ->limit(5)
            ->get();

        if ($recentInvoices->isNotEmpty()) {
            $context['recent_invoices'] = $recentInvoices->map(fn($inv) => [
                'invoice_number' => $inv->invoice_number,
                'invoice_date' => $inv->invoice_date?->format('Y-m-d'),
                'due_date' => $inv->due_date?->format('Y-m-d'),
                'total' => (float) $inv->total,
                'paid_amount' => (float) $inv->paid_amount,
                'remaining_amount' => (float) $inv->remaining_amount,
                'status' => $inv->status,
            ])->toArray();
        }

        $recentPayments = Payment::whereHas('invoices.salesOrder', fn($q) => $q->where('client_id', $client->id))
            ->orderByDesc('payment_date')
            ->limit(5)
            ->get();

        if ($recentPayments->isNotEmpty()) {
            $context['recent_payments'] = $recentPayments->map(fn($p) => [
                'payment_number' => $p->payment_number,
                'payment_date' => $p->payment_date?->format('Y-m-d'),
                'amount' => (float) $p->amount,
                'status' => $p->status,
            ])->toArray();
        }

        $recentTickets = Ticket::where('client_id', $client->id)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        if ($recentTickets->isNotEmpty()) {
            $context['recent_tickets'] = $recentTickets->map(fn($t) => [
                'ticket_number' => $t->ticket_number,
                'subject' => $t->subject,
                'status' => $t->status,
                'priority' => $t->priority,
            ])->toArray();
        }

        return $context;
    }

    public function classifyIntent(string $message): array
    {
        $provider = $this->resolveProvider();

        if (!$provider) {
            return $this->fallbackIntentClassification($message);
        }

        $baseUrl = rtrim($provider->base_url, '/');
        $apiKey = Crypt::decrypt($provider->api_key_encrypted);
        $model = $provider->default_model ?: 'gpt-4o-mini';

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ])
                ->timeout(30)
                ->post("{$baseUrl}/v1/chat/completions", [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => self::INTENT_CLASSIFY_PROMPT],
                        ['role' => 'user', 'content' => $message],
                    ],
                    'temperature' => 0.1,
                    'max_tokens' => 150,
                ]);

            if ($response->successful()) {
                $content = $response->json('choices.0.message.content');
                $json = json_decode(trim($content), true);

                if (is_array($json) && isset($json['intent'])) {
                    return $json;
                }

                preg_match('/\{.*\}/s', $content, $matches);
                if (!empty($matches[0])) {
                    $parsed = json_decode($matches[0], true);
                    if (is_array($parsed) && isset($parsed['intent'])) {
                        return $parsed;
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('WhatsappAiService classifyIntent error: ' . $e->getMessage());
        }

        return $this->fallbackIntentClassification($message);
    }

    public function handleIntent(string $intent, string $message, array $context): ?string
    {
        $intentConfig = $this->autoReplyConfig?->intent_config ?? [];
        $intentSettings = $intentConfig[$intent] ?? null;

        if ($intentSettings && empty($intentSettings['enabled'])) {
            return null;
        }

        $intentPrompts = [
            'sales_inquiry' => 'Jawab pertanyaan sales/produk ini dengan ramah dan profesional. Tawarkan bantuan untuk menghubungkan ke tim sales.',
            'order_status' => 'Jawab pertanyaan status pesanan. Gunakan data konteks pesanan yang tersedia. Berikan status terkini dan estimasi.',
            'invoice_query' => 'Jawab pertanyaan invoice/tagihan. Gunakan data invoice yang tersedia. Jelaskan status pembayaran dengan jelas.',
            'support_request' => 'Tangani keluhan dengan empati. Tawarkan solusi atau eskalasi ke tim support. Jangan defensif.',
            'general' => 'Jawab pertanyaan umum dengan singkat, jelas, dan informatif. Bahasa profesional namun ramah.',
            'greeting' => 'Sambut dengan ramah. Perkenalkan diri sebagai asisten virtual. Tawarkan bantuan.',
        ];

        $customPrompt = $intentSettings['prompt'] ?? null;
        $promptOverride = $customPrompt ?: ($intentPrompts[$intent] ?? $intentPrompts['general']);

        $provider = $this->resolveProvider();
        if (!$provider) return null;

        $baseUrl = rtrim($provider->base_url, '/');
        $apiKey = Crypt::decrypt($provider->api_key_encrypted);
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
                        ['role' => 'system', 'content' => $this->buildSystemPrompt($context, $promptOverride)],
                        ['role' => 'user', 'content' => $message],
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 2000,
                ]);

            if ($response->successful()) {
                return $response->json('choices.0.message.content');
            }
        } catch (\Exception $e) {
            Log::error('WhatsappAiService handleIntent error: ' . $e->getMessage());
        }

        return null;
    }

    public function queryErpData(string $naturalLanguageQuery): array
    {
        $results = [];

        $statusKeywords = ['status', 'pesanan', 'order', 'tracking', 'lacak', 'pengiriman', 'kirim'];
        $invoiceKeywords = ['invoice', 'tagihan', 'bayar', 'pembayaran', 'faktur', 'bill'];
        $productKeywords = ['produk', 'barang', 'harga', 'katalog', 'stok', 'beli'];

        $lower = strtolower($naturalLanguageQuery);

        if ($this->matchesAny($lower, $statusKeywords)) {
            $phone = $this->currentSenderPhone;
            if ($phone) {
                $contact = ClientContact::where('phone', 'like', "%{$phone}%")->first();
                if ($contact) {
                    $results['type'] = 'order_status';
                    $results['data'] = SalesOrder::where('client_id', $contact->client_id)
                        ->orderByDesc('order_date')
                        ->limit(5)
                        ->get()
                        ->map(fn($so) => [
                            'number' => $so->so_number,
                            'date' => $so->order_date?->format('Y-m-d'),
                            'total' => (float) $so->total,
                            'status' => $so->status,
                        ])
                        ->toArray();
                }
            }
        }

        if ($this->matchesAny($lower, $invoiceKeywords)) {
            $phone = $this->currentSenderPhone;
            if ($phone) {
                $contact = ClientContact::where('phone', 'like', "%{$phone}%")->first();
                if ($contact) {
                    $results['type'] = $results['type'] ?? 'invoice_query';
                    $results['data'] = Invoice::whereHas('salesOrder', fn($q) => $q->where('client_id', $contact->client_id))
                        ->orderByDesc('invoice_date')
                        ->limit(5)
                        ->get()
                        ->map(fn($inv) => [
                            'number' => $inv->invoice_number,
                            'date' => $inv->invoice_date?->format('Y-m-d'),
                            'due_date' => $inv->due_date?->format('Y-m-d'),
                            'total' => (float) $inv->total,
                            'paid' => (float) $inv->paid_amount,
                            'remaining' => (float) $inv->remaining_amount,
                            'status' => $inv->status,
                        ])
                        ->toArray();
                }
            }
        }

        return $results;
    }

    public function sendWhatsappReply(string $to, string $message): void
    {
        try {
            $waService = app(WhatsappBusinessService::class);
            $result = $waService->sendTextMessage($to, $message);

            if (!$result['success']) {
                Log::error('WhatsappAiService send error', [
                    'to' => $to,
                    'error' => $result['message'] ?? 'Unknown',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('WhatsappAiService send exception: ' . $e->getMessage());
        }
    }

    public function shouldEscalateToHuman(string $message, array $aiResponse): bool
    {
        if (!empty($aiResponse['is_human_escalation'])) {
            return true;
        }

        $confidence = $aiResponse['confidence'] ?? 1.0;
        $escalationRules = $this->autoReplyConfig?->escalation_rules ?? [];

        $threshold = $escalationRules['confidence_threshold'] ?? 0.3;
        if ($confidence < $threshold) {
            return true;
        }

        $intent = $aiResponse['intent'] ?? '';
        $escalateIntents = $escalationRules['escalate_intents'] ?? [];
        if (in_array($intent, $escalateIntents)) {
            return true;
        }

        $keywords = $escalationRules['escalate_keywords'] ?? [];
        foreach ($keywords as $kw) {
            if (stripos($message, $kw) !== false) {
                return true;
            }
        }

        return false;
    }

    public function createTicketFromConversation(int $conversationId, string $summary): void
    {
        $conversation = WaConversation::find($conversationId);
        if (!$conversation) return;

        try {
            $contact = ClientContact::where('phone', $conversation->contact_phone)->first();
            $client = $contact ? $contact->client : null;

            Ticket::create([
                'company_id' => $conversation->company_id,
                'ticket_number' => $this->generateTicketNumber(),
                'subject' => 'Eskalasi AI Chatbot - ' . ($conversation->contact_name ?? $conversation->contact_phone),
                'description' => "Pesan: {$summary}\n\nDari: {$conversation->contact_name} ({$conversation->contact_phone})\nIntent: {$conversation->chatbot_intent}",
                'status' => 'open',
                'priority' => 'medium',
                'source' => 'whatsapp_ai',
                'client_id' => $client?->id,
                'contact_id' => $contact?->id,
            ]);

            Log::info('WhatsappAiService ticket created', [
                'conversation_id' => $conversationId,
                'phone' => $conversation->contact_phone,
            ]);
        } catch (\Exception $e) {
            Log::error('WhatsappAiService createTicket error: ' . $e->getMessage());
        }
    }

    public function setConfigForTest(array $config): void
    {
        $this->autoReplyConfig = new WaAutoReply([
            'ai_provider_id' => $config['ai_provider_id'] ?? null,
            'ai_prompt_template' => $config['ai_prompt_template'] ?? null,
            'fallback_message' => $config['fallback_message'] ?? null,
            'intent_config' => $config['intent_config'] ?? [],
            'escalation_rules' => $config['escalation_rules'] ?? [],
            'is_ai_powered' => true,
            'is_active' => true,
        ]);
    }

    public function testConnection(int $providerId): array
    {
        $provider = AiProvider::find($providerId);

        if (!$provider || !$provider->is_active) {
            return ['success' => false, 'message' => 'AI Provider tidak ditemukan atau tidak aktif.'];
        }

        $baseUrl = rtrim($provider->base_url, '/');
        $apiKey = Crypt::decrypt($provider->api_key_encrypted);
        $model = $provider->default_model ?: 'gpt-4o-mini';

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ])
                ->timeout(15)
                ->post("{$baseUrl}/v1/chat/completions", [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'user', 'content' => 'Halo'],
                    ],
                    'max_tokens' => 10,
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Koneksi berhasil! Model: ' . ($response->json('model') ?? $model),
                ];
            }

            return [
                'success' => false,
                'message' => 'Gagal: HTTP ' . $response->status() . ' - ' . ($response->json('error.message') ?? 'Unknown error'),
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Gagal terhubung: ' . $e->getMessage()];
        }
    }

    public function testChat(string $message, int $providerId, ?array $context = null, ?string $promptTemplate = null): array
    {
        $provider = AiProvider::find($providerId);

        if (!$provider) {
            return ['success' => false, 'reply' => 'Provider tidak ditemukan.'];
        }

        $baseUrl = rtrim($provider->base_url, '/');
        $apiKey = Crypt::decrypt($provider->api_key_encrypted);
        $model = $provider->default_model ?: 'gpt-4o-mini';

        $systemPrompt = $promptTemplate ?: $this->buildSystemPrompt($context ?? []);

        try {
            $startTime = microtime(true);

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ])
                ->timeout(60)
                ->post("{$baseUrl}/v1/chat/completions", [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $message],
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 2000,
                ]);

            $elapsed = round((microtime(true) - $startTime) * 1000);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'reply' => $response->json('choices.0.message.content') ?? '',
                    'model' => $response->json('model') ?? $model,
                    'elapsed_ms' => $elapsed,
                    'usage' => $response->json('usage'),
                ];
            }

            return [
                'success' => false,
                'reply' => 'Error: ' . $response->status(),
                'elapsed_ms' => $elapsed,
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'reply' => 'Error: ' . $e->getMessage()];
        }
    }

    // ─── Protected Helpers ────────────────────────────────────────────

    protected function resolveProvider(): ?AiProvider
    {
        if ($this->provider) return $this->provider;

        if ($this->autoReplyConfig?->ai_provider_id) {
            $this->provider = AiProvider::find($this->autoReplyConfig->ai_provider_id);
            if ($this->provider && $this->provider->is_active) {
                return $this->provider;
            }
        }

        $this->provider = AiProvider::where('is_active', true)
            ->where('api_format', 'openai_compatible')
            ->first();

        return $this->provider;
    }

    protected function buildSystemPrompt(array $context, ?string $intentOverride = null): string
    {
        $template = $this->autoReplyConfig?->ai_prompt_template;

        if ($template) {
            $prompt = $this->interpolateVariables($template, $context);
            if ($intentOverride) {
                $prompt .= "\n\nInstruksi khusus: {$intentOverride}";
            }
            return $prompt;
        }

        $defaultPrompt = "Anda adalah asisten virtual WhatsApp untuk aplikasi bisnis BizOS. "
            . "Nama Anda adalah BizBot. Jawab pertanyaan pelanggan dengan ramah, profesional, dan informatif dalam bahasa Indonesia. "
            . "Gunakan data konteks yang disediakan untuk memberikan jawaban yang akurat dan personal. "
            . "Jangan menyebutkan bahwa Anda adalah AI. "
            . "Jika tidak tahu jawabannya, akui dengan jujur dan tawarkan untuk menghubungkan ke tim support.";

        if ($context) {
            $defaultPrompt .= "\n\nKONTEKS PELANGGAN:\n" . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }

        if ($intentOverride) {
            $defaultPrompt .= "\n\nInstruksi khusus: {$intentOverride}";
        }

        return $defaultPrompt;
    }

    protected function interpolateVariables(string $template, array $context): string
    {
        $replacements = [];

        if (isset($context['client'])) {
            $c = $context['client'];
            $replacements['{name}'] = $c['name'] ?? '';
            $replacements['{company}'] = $c['name'] ?? '';
            $replacements['{city}'] = $c['city'] ?? '';
        }

        if (isset($context['lead'])) {
            $l = $context['lead'];
            $replacements['{name}'] = $replacements['{name}'] ?? ($l['name'] ?? '');
            $replacements['{company}'] = $replacements['{company}'] ?? ($l['company'] ?? '');
        }

        if (isset($context['recent_orders'][0])) {
            $o = $context['recent_orders'][0];
            $replacements['{order_status}'] = $o['status'] ?? '';
            $replacements['{order_number}'] = $o['so_number'] ?? '';
        }

        if (isset($context['recent_invoices'][0])) {
            $inv = $context['recent_invoices'][0];
            $replacements['{invoice_status}'] = $inv['status'] ?? '';
            $replacements['{invoice_number}'] = $inv['invoice_number'] ?? '';
            $replacements['{invoice_total}'] = number_format((float) ($inv['total'] ?? 0), 0, ',', '.');
            $replacements['{invoice_remaining}'] = number_format((float) ($inv['remaining_amount'] ?? 0), 0, ',', '.');
        }

        $replacements['{now}'] = now()->format('d M Y H:i');
        $replacements['{app_name}'] = config('app.name', 'BizOS');

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    protected function fallbackIntentClassification(string $message): array
    {
        $lower = strtolower($message);

        $patterns = [
            'greeting' => '/\b(halo|hai|hallo|assalam|selamat\s?(pagi|siang|sore|malam)|hi|bro|gan|pagi|siang|sore|malam)\b/i',
            'sales_inquiry' => '/\b(harga|biaya|berapa|beli|pesan|order|produk|barang|katalog|stok|diskon|promo)\b/i',
            'order_status' => '/\b(status|tracking|lacak|dimana|sampai|kirim|pengiriman|resi|pesanan\s?saya)\b/i',
            'invoice_query' => '/\b(invoice|tagihan|bayar|pembayaran|faktur|bill|angsuran|cicil|tempo|jatuh\s?tempo)\b/i',
            'support_request' => '/\b(komplain|keluhan|rusak|jelek|kecewa|buruk|masalah|error|gangguan|tolong|bantuan|help)\b/i',
            'general' => '/\b(alamat|lokasi|jam|buka|tutup|operasional|info|informasi|tanya)\b/i',
        ];

        foreach ($patterns as $intent => $pattern) {
            if (preg_match($pattern, $lower)) {
                return ['intent' => $intent, 'confidence' => 0.80];
            }
        }

        return ['intent' => 'general', 'confidence' => 0.50];
    }

    protected function extractMessageText(array $message): string
    {
        $messageType = $message['type'] ?? 'unknown';

        if ($messageType === 'text') {
            return $message['text']['body'] ?? '';
        }

        if ($messageType === 'button') {
            return $message['button']['text'] ?? '';
        }

        if ($messageType === 'interactive') {
            $interactive = $message['interactive'] ?? [];
            return $interactive['button_reply']['title'] ?? $interactive['list_reply']['title'] ?? '';
        }

        if (in_array($messageType, ['image', 'video', 'audio', 'document', 'sticker'])) {
            return $message[$messageType]['caption'] ?? "[{$messageType}]";
        }

        if ($messageType === 'location') {
            $loc = $message['location'] ?? [];
            return "[Lokasi: {$loc['name']}]";
        }

        return '';
    }

    protected function normalizePhoneForQuery(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($phone, '62')) {
            return '0' . substr($phone, 2);
        }

        if (str_starts_with($phone, '0')) {
            return $phone;
        }

        return '0' . $phone;
    }

    protected function findOrCreateConversation(string $phone, ?array $contact): ?WaConversation
    {
        $conversation = WaConversation::where('contact_phone', $phone)->first();

        if ($conversation) {
            return $conversation;
        }

        $companyId = $this->autoReplyConfig?->company_id
            ?? WaAutoReply::where('is_ai_powered', true)->value('company_id')
            ?? \App\Models\Company::first()?->id;

        if (!$companyId) {
            return null;
        }

        return WaConversation::create([
            'company_id' => $companyId,
            'contact_phone' => $phone,
            'contact_name' => $contact['profile']['name'] ?? $phone,
            'last_message' => '',
            'unread_count' => 0,
            'status' => 'aktif',
        ]);
    }

    protected function matchesAny(string $text, array $keywords): bool
    {
        foreach ($keywords as $kw) {
            if (stripos($text, $kw) !== false) {
                return true;
            }
        }
        return false;
    }

    protected function generateTicketNumber(): string
    {
        $prefix = 'TKT-' . now()->format('ym');
        $last = Ticket::where('ticket_number', 'like', $prefix . '%')
            ->orderByDesc('ticket_number')
            ->first();
        $num = $last ? (int) substr($last->ticket_number, 7) + 1 : 1;
        return $prefix . str_pad($num, 5, '0', STR_PAD_LEFT);
    }
}
