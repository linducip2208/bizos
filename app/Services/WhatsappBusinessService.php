<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Integration;
use App\Models\WaConversation;
use App\Models\WaTemplate;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WhatsappBusinessService
{
    protected ?string $baseUrl;
    protected ?string $apiKey;
    protected ?string $phoneNumberId;
    protected ?string $businessAccountId;
    protected ?Integration $integration;

    public function __construct(?int $companyId = null)
    {
        $this->resolveConfig($companyId);
    }

    protected function resolveConfig(?int $companyId = null): void
    {
        $query = Integration::where('integration_type', 'whatsapp_business');

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        $this->integration = $query->where('is_active', true)->first();

        if ($this->integration) {
            $this->apiKey = $this->integration->api_key_encrypted;
            $this->baseUrl = $this->integration->base_url ?: 'https://graph.facebook.com/v20.0';
            $config = $this->integration->extra_config ?? [];
            $this->phoneNumberId = $config['phone_number_id'] ?? null;
            $this->businessAccountId = $config['business_account_id'] ?? null;
        } else {
            $this->apiKey = config('services.wa.api_key') ?: env('WA_API_KEY');
            $this->baseUrl = config('services.wa.api_url') ?: env('WA_API_URL', 'https://graph.facebook.com/v20.0');
            $this->phoneNumberId = config('services.wa.phone_number_id') ?: env('WA_PHONE_NUMBER_ID');
            $this->businessAccountId = config('services.wa.business_account_id') ?: env('WA_BUSINESS_ACCOUNT_ID');
        }

        if ($this->baseUrl && !str_ends_with($this->baseUrl, '/')) {
            $this->baseUrl .= '/';
        }
    }

    protected function request(string $method, string $endpoint, array $data = []): Response
    {
        $url = Str::startsWith($endpoint, 'http')
            ? $endpoint
            : $this->baseUrl . ltrim($endpoint, '/');

        $headers = [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ];

        return Http::withHeaders($headers)->$method($url, $data);
    }

    protected function apiEndpoint(string $path): string
    {
        return "{$this->phoneNumberId}/{$path}";
    }

    // ─── Template Management ──────────────────────────────────────────

    public function syncTemplates(): array
    {
        if (!$this->businessAccountId) {
            return ['success' => false, 'message' => 'Business Account ID tidak dikonfigurasi', 'synced' => 0];
        }

        $response = $this->request('get', "{$this->businessAccountId}/message_templates", [
            'limit' => 100,
            'fields' => 'name,status,category,language,components,quality_score,rejected_reason',
        ]);

        if (!$response->successful()) {
            Log::error('WA syncTemplates gagal', ['response' => $response->json()]);
            return ['success' => false, 'message' => 'Gagal sync template dari Meta', 'synced' => 0];
        }

        $data = $response->json();
        $templates = $data['data'] ?? [];
        $synced = 0;

        foreach ($templates as $metaTemplate) {
            $existing = WaTemplate::where('name', $metaTemplate['name'])->first();

            $templateData = [
                'meta_template_id' => $metaTemplate['id'],
                'meta_template_status' => $metaTemplate['status'],
                'category' => $metaTemplate['category'],
                'language' => $metaTemplate['language'],
                'components' => $metaTemplate['components'] ?? null,
                'quality_score' => $metaTemplate['quality_score'] ?? null,
                'meta_rejection_reason' => $metaTemplate['rejected_reason'] ?? null,
                'meta_synced_at' => now(),
            ];

            if ($metaTemplate['status'] === 'rejected') {
                $templateData['rejected_at'] = now();
            }

            if ($existing) {
                $existing->update($templateData);
            } else {
                $templateData['name'] = $metaTemplate['name'];
                $templateData['content'] = $this->extractTextFromComponents($metaTemplate['components'] ?? []);
                $templateData['status'] = $metaTemplate['status'];
                $templateData['company_id'] ??= Company::first()?->id;
                WaTemplate::create($templateData);
            }
            $synced++;
        }

        Log::info("WA templates synced: {$synced} templates");
        return ['success' => true, 'message' => "Berhasil sync {$synced} template", 'synced' => $synced];
    }

    protected function extractTextFromComponents(array $components): string
    {
        $texts = [];
        foreach ($components as $component) {
            if ($component['type'] === 'BODY' && isset($component['text'])) {
                $texts[] = $component['text'];
            }
            if ($component['type'] === 'HEADER' && isset($component['text'])) {
                $texts[] = $component['text'];
            }
        }
        return implode("\n\n", $texts);
    }

    public function submitTemplate(string $name, string $language, string $category, array $components): array
    {
        if (!$this->businessAccountId) {
            return ['success' => false, 'message' => 'Business Account ID tidak dikonfigurasi'];
        }

        $payload = [
            'name' => $this->sanitizeTemplateName($name),
            'language' => $language,
            'category' => strtoupper($category),
            'components' => $components,
        ];

        $response = $this->request('post', "{$this->businessAccountId}/message_templates", $payload);

        $body = $response->json();

        if ($response->successful()) {
            return ['success' => true, 'meta_template_id' => $body['id'] ?? null, 'message' => 'Template berhasil diajukan ke Meta'];
        }

        Log::error('WA submitTemplate gagal', ['response' => $body, 'payload' => $payload]);
        return ['success' => false, 'message' => $body['error']['message'] ?? 'Gagal mengajukan template'];
    }

    protected function sanitizeTemplateName(string $name): string
    {
        return strtolower(Str::slug($name, '_'));
    }

    public function getTemplateStatus(string $templateId): string
    {
        $response = $this->request('get', $templateId, [
            'fields' => 'status,rejected_reason',
        ]);

        if ($response->successful()) {
            $data = $response->json();
            return $data['status'] ?? 'unknown';
        }

        return 'unknown';
    }

    public function checkAndUpdateTemplateStatus(WaTemplate $template): void
    {
        if (!$template->meta_template_id) {
            return;
        }

        $status = $this->getTemplateStatus($template->meta_template_id);

        $updates = ['meta_template_status' => $status, 'meta_synced_at' => now()];

        if ($status === 'rejected') {
            $response = $this->request('get', $template->meta_template_id, ['fields' => 'rejected_reason']);
            if ($response->successful()) {
                $data = $response->json();
                $updates['meta_rejection_reason'] = $data['rejected_reason'] ?? null;
                $updates['rejected_at'] = now();
            }
        }

        $template->update($updates);
    }

    // ─── Message Sending ──────────────────────────────────────────────

    public function sendTemplateMessage(string $to, string $templateName, string $language, array $parameters = []): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $this->normalizePhone($to),
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => ['code' => $language],
            ],
        ];

        if (!empty($parameters)) {
            $bodyParams = [];
            foreach ($parameters as $i => $value) {
                $bodyParams[] = [
                    'type' => 'text',
                    'text' => $value,
                ];
            }
            $payload['template']['components'] = [
                [
                    'type' => 'body',
                    'parameters' => $bodyParams,
                ],
            ];
        }

        return $this->send($payload);
    }

    public function sendTextMessage(string $to, string $text, bool $previewUrl = false): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $this->normalizePhone($to),
            'type' => 'text',
            'text' => [
                'preview_url' => $previewUrl,
                'body' => $text,
            ],
        ];

        return $this->send($payload);
    }

    public function sendMediaMessage(string $to, string $type, string $url, ?string $caption = null, ?string $filename = null): array
    {
        $validTypes = ['audio', 'document', 'image', 'sticker', 'video'];
        if (!in_array($type, $validTypes)) {
            return ['success' => false, 'message' => "Tipe media tidak valid: {$type}. Gunakan: " . implode(', ', $validTypes)];
        }

        $media = [$type => ['link' => $url]];
        if ($filename) {
            $media[$type]['filename'] = $filename;
        }
        if ($caption && $type !== 'sticker') {
            $media[$type]['caption'] = $caption;
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $this->normalizePhone($to),
            'type' => $type,
        ] + $media;

        return $this->send($payload);
    }

    public function sendInteractiveMessage(string $to, array $interactive): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $this->normalizePhone($to),
            'type' => 'interactive',
            'interactive' => $interactive,
        ];

        return $this->send($payload);
    }

    public function sendLocationMessage(string $to, float $latitude, float $longitude, string $name, string $address): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $this->normalizePhone($to),
            'type' => 'location',
            'location' => [
                'latitude' => (string) $latitude,
                'longitude' => (string) $longitude,
                'name' => $name,
                'address' => $address,
            ],
        ];

        return $this->send($payload);
    }

    public function sendContactMessage(string $to, array $contacts): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $this->normalizePhone($to),
            'type' => 'contacts',
            'contacts' => $contacts,
        ];

        return $this->send($payload);
    }

    protected function send(array $payload): array
    {
        $response = $this->request('post', $this->apiEndpoint('messages'), $payload);
        $body = $response->json();

        if ($response->successful()) {
            return [
                'success' => true,
                'message_id' => $body['messages'][0]['id'] ?? null,
                'wa_id' => $body['contacts'][0]['wa_id'] ?? null,
            ];
        }

        Log::error('WA send message gagal', ['payload' => $payload, 'response' => $body]);
        return [
            'success' => false,
            'message' => $body['error']['message'] ?? 'Gagal mengirim pesan',
            'code' => $body['error']['code'] ?? null,
        ];
    }

    // ─── Webhook Handling ─────────────────────────────────────────────

    public function verifyWebhook(string $mode, string $token, string $challenge): string
    {
        $verifyToken = config('services.wa.webhook_verify_token') ?: env('WA_WEBHOOK_VERIFY_TOKEN', '');

        if ($mode === 'subscribe' && $token === $verifyToken) {
            return $challenge;
        }

        return '';
    }

    public function handleWebhook(array $payload): void
    {
        $entry = $payload['entry'][0] ?? null;
        if (!$entry) {
            return;
        }

        $changes = $entry['changes'][0] ?? null;
        if (!$changes) {
            return;
        }

        $value = $changes['value'] ?? [];
        $field = $changes['field'] ?? '';

        if ($field === 'messages') {
            $this->processIncomingMessages($value);
        } elseif ($field === 'message_template_status_update') {
            $this->processTemplateStatusUpdate($value);
        }
    }

    protected function processIncomingMessages(array $value): void
    {
        $contacts = $value['contacts'] ?? [];
        $messages = $value['messages'] ?? [];
        $metadata = $value['metadata'] ?? [];

        foreach ($messages as $message) {
            $from = $message['from'] ?? null;
            if (!$from) continue;

            $contact = collect($contacts)->firstWhere('wa_id', $from);

            $messageType = $message['type'] ?? 'unknown';
            $messageText = '';

            if ($messageType === 'text') {
                $messageText = $message['text']['body'] ?? '';
            } elseif ($messageType === 'button') {
                $messageText = $message['button']['text'] ?? '[Button]';
            } elseif ($messageType === 'interactive') {
                $interactive = $message['interactive'] ?? [];
                $messageText = $interactive['button_reply']['title'] ?? $interactive['list_reply']['title'] ?? '[Interactive]';
            } elseif (in_array($messageType, ['image', 'video', 'audio', 'document', 'sticker'])) {
                $caption = $message[$messageType]['caption'] ?? '';
                $messageText = $caption ?: "[{$messageType}]";
            } elseif ($messageType === 'location') {
                $loc = $message['location'] ?? [];
                $messageText = "[Lokasi: {$loc['name']}]";
            }

            $conversation = $this->findOrCreateConversation($from, $contact);

            $conversation->update([
                'last_message' => $messageText,
                'last_message_at' => now(),
                'unread_count' => $conversation->unread_count + 1,
                'status' => 'aktif',
            ]);

            $conversation->addToFlowHistory('customer', $messageText);

            if ($conversation->is_bot_active && $conversation->flow) {
                try {
                    app(ChatbotFlowService::class)->processMessage($conversation, $messageText);
                } catch (\Exception $e) {
                    Log::error('ChatbotFlow error', ['error' => $e->getMessage(), 'conversation_id' => $conversation->id]);
                }
            }
        }
    }

    protected function processTemplateStatusUpdate(array $value): void
    {
        $templateId = $value['message_template_id'] ?? null;
        $status = $value['event'] ?? null;
        $reason = $value['reason'] ?? null;

        if (!$templateId) return;

        $template = WaTemplate::where('meta_template_id', $templateId)->first();
        if (!$template) return;

        $updates = [
            'meta_template_status' => $status,
            'meta_synced_at' => now(),
        ];

        if ($status === 'rejected') {
            $updates['meta_rejection_reason'] = $reason;
            $updates['rejected_at'] = now();
        }

        $template->update($updates);

        Log::info("WA template status update: {$templateId} → {$status}");
    }

    protected function findOrCreateConversation(string $phone, ?array $contact): WaConversation
    {
        $conversation = WaConversation::where('contact_phone', $phone)->first();

        if (!$conversation) {
            $conversation = WaConversation::create([
                'company_id' => Company::first()?->id,
                'contact_phone' => $phone,
                'contact_name' => $contact['profile']['name'] ?? $phone,
                'last_message' => '',
                'unread_count' => 0,
                'status' => 'aktif',
            ]);
        }

        return $conversation;
    }

    // ─── Conversation Management ──────────────────────────────────────

    public function getConversations(string $phoneNumber, int $limit = 20): Collection
    {
        return WaConversation::where('contact_phone', 'like', "%{$phoneNumber}%")
            ->orWhere('contact_name', 'like', "%{$phoneNumber}%")
            ->orderBy('last_message_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function markAsRead(int $conversationId): void
    {
        WaConversation::where('id', $conversationId)->update(['unread_count' => 0]);
    }

    // ─── Analytics ────────────────────────────────────────────────────

    public function getMessageStats(string $dateFrom, string $dateTo): array
    {
        $conversations = WaConversation::whereBetween('last_message_at', [$dateFrom, $dateTo])->get();

        $byStatus = $conversations->groupBy('status')->map->count()->toArray();
        $activeCount = $conversations->where('status', 'aktif')->count();
        $resolvedCount = $conversations->where('status', 'selesai')->count();

        return [
            'total_conversations' => $conversations->count(),
            'active' => $activeCount,
            'resolved' => $resolvedCount,
            'by_status' => $byStatus,
            'total_unread' => $conversations->sum('unread_count'),
            'bot_handled' => $conversations->where('is_bot_active', true)->count(),
        ];
    }

    public function getTemplatePerformance(string $templateName): array
    {
        $template = WaTemplate::where('name', $templateName)->first();
        if (!$template) {
            return ['error' => 'Template tidak ditemukan'];
        }

        $campaigns = $template->waBlastCampaigns()->get();
        $totalLogs = 0;
        $totalSent = 0;
        $totalFailed = 0;
        $totalDelivered = 0;
        $totalRead = 0;

        foreach ($campaigns as $campaign) {
            $totalSent += $campaign->total_sent ?? 0;
            $totalFailed += $campaign->total_failed ?? 0;
            $logs = $campaign->waBlastLogs()->get();
            $totalLogs += $logs->count();
            $totalDelivered += $logs->whereNotNull('delivered_at')->count();
            $totalRead += $logs->whereNotNull('read_at')->count();
        }

        return [
            'template_name' => $templateName,
            'status' => $template->meta_template_status ?? $template->status,
            'quality_score' => $template->quality_score,
            'campaigns' => $campaigns->count(),
            'total_sent' => $totalSent,
            'total_failed' => $totalFailed,
            'total_delivered' => $totalDelivered,
            'total_read' => $totalRead,
            'delivery_rate' => $totalSent > 0 ? round(($totalDelivered / $totalSent) * 100, 2) : 0,
            'read_rate' => $totalDelivered > 0 ? round(($totalRead / $totalDelivered) * 100, 2) : 0,
        ];
    }

    public function getConversationDetails(WaConversation $conversation): array
    {
        return [
            'id' => $conversation->id,
            'contact_phone' => $conversation->contact_phone,
            'contact_name' => $conversation->contact_name,
            'last_message' => $conversation->last_message,
            'last_message_at' => $conversation->last_message_at?->diffForHumans(),
            'unread_count' => $conversation->unread_count,
            'status' => $conversation->status,
            'is_bot_active' => $conversation->is_bot_active,
            'chatbot_intent' => $conversation->chatbot_intent,
            'flow_id' => $conversation->flow_id,
            'flow_history' => $conversation->flow_state['history'] ?? [],
            'assigned_to_name' => $conversation->assignedTo?->first_name,
        ];
    }

    // ─── Helpers ──────────────────────────────────────────────────────

    protected function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }
        if (!str_starts_with($phone, '62')) {
            $phone = '62' . $phone;
        }
        return $phone;
    }

    public static function sendQuickReply(string $to, string $headerText, string $bodyText, string $footerText, array $buttons): array
    {
        $service = new static();

        $buttonComponents = [];
        foreach ($buttons as $i => $btn) {
            $buttonComponents[] = [
                'type' => 'reply',
                'reply' => [
                    'id' => "btn_{$i}",
                    'title' => $btn,
                ],
            ];
        }

        $interactive = [
            'type' => 'button',
            'header' => [
                'type' => 'text',
                'text' => $headerText,
            ],
            'body' => [
                'text' => $bodyText,
            ],
            'footer' => [
                'text' => $footerText,
            ],
            'action' => [
                'buttons' => $buttonComponents,
            ],
        ];

        return $service->sendInteractiveMessage($to, $interactive);
    }

    public static function sendListPicker(string $to, string $headerText, string $bodyText, string $buttonText, array $sections): array
    {
        $service = new static();

        $interactive = [
            'type' => 'list',
            'header' => [
                'type' => 'text',
                'text' => $headerText,
            ],
            'body' => [
                'text' => $bodyText,
            ],
            'action' => [
                'button' => $buttonText,
                'sections' => $sections,
            ],
        ];

        return $service->sendInteractiveMessage($to, $interactive);
    }
}
