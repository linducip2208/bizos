<?php

namespace App\Filament\Pages;

use App\Models\AiProvider;
use App\Models\WaAutoReply;
use App\Models\WaConversation;
use App\Services\WhatsappAiService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class WaAiChatbot extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cpu-chip';

    protected static ?int $navigationSort = 155;

    protected static ?string $title = 'Chatbot AI WhatsApp';

    protected static ?string $navigationLabel = 'Chatbot AI WhatsApp';

    protected static ?string $slug = 'wa-ai-chatbot';

    protected string $view = 'filament.pages.wa-ai-chatbot';

    public static function getNavigationGroup(): ?string
    {
        return 'Marketing';
    }

    public array $providers = [];
    public int|string|null $aiProviderId = null;
    public bool $isAiEnabled = true;
    public bool $isActive = true;
    public string $promptTemplate = '';
    public string $fallbackMessage = '';
    public array $intentConfig = [];
    public array $escalationRules = [];
    public string $escalationKeywordsInput = '';

    public string $testMessage = '';
    public string $testPhone = '081234567890';
    public array $chatHistory = [];
    public ?string $connectionResult = null;
    public ?bool $connectionOk = null;
    public bool $isTesting = false;
    public bool $saved = false;

    public array $recentConversations = [];
    public array $variableHints = [];

    protected const AVAILABLE_INTENTS = [
        'sales_inquiry' => 'Penjualan / Produk',
        'order_status' => 'Status Pesanan',
        'invoice_query' => 'Invoice / Tagihan',
        'support_request' => 'Dukungan / Keluhan',
        'general' => 'Pertanyaan Umum',
        'greeting' => 'Sapaan',
    ];

    protected const ESCALATABLE_INTENTS = [
        'support_request' => 'Dukungan / Keluhan',
        'invoice_query' => 'Invoice / Tagihan',
    ];

    public function mount(): void
    {
        $this->providers = AiProvider::where('is_active', true)->pluck('name', 'id')->toArray();

        $this->variableHints = [
            '{name}' => 'Nama kontak',
            '{company}' => 'Nama perusahaan',
            '{city}' => 'Kota',
            '{order_status}' => 'Status pesanan terakhir',
            '{order_number}' => 'Nomor pesanan terakhir',
            '{invoice_status}' => 'Status invoice terakhir',
            '{invoice_number}' => 'Nomor invoice terakhir',
            '{invoice_total}' => 'Total invoice',
            '{invoice_remaining}' => 'Sisa tagihan',
            '{now}' => 'Waktu sekarang',
            '{app_name}' => 'Nama aplikasi',
        ];

        $this->intentConfig = collect(self::AVAILABLE_INTENTS)->mapWithKeys(fn($label, $intent) => [
            $intent => ['enabled' => true, 'prompt' => ''],
        ])->toArray();

        $this->escalationRules = [
            'confidence_threshold' => 0.3,
            'escalate_intents' => ['support_request'],
            'escalate_keywords' => [],
            'max_consecutive_fallback' => 3,
        ];

        $config = WaAutoReply::where('keyword', '*')->first();

        if ($config) {
            $this->loadConfig($config);
        }

        $this->loadRecentConversations();
    }

    protected function loadConfig(WaAutoReply $config): void
    {
        $this->aiProviderId = $config->ai_provider_id;
        $this->isAiEnabled = (bool) $config->is_ai_powered;
        $this->isActive = (bool) $config->is_active;
        $this->promptTemplate = $config->ai_prompt_template ?? '';
        $this->fallbackMessage = $config->fallback_message ?? '';

        $storedIntent = $config->intent_config ?? [];
        foreach (self::AVAILABLE_INTENTS as $intent => $label) {
            $this->intentConfig[$intent] = [
                'enabled' => $storedIntent[$intent]['enabled'] ?? true,
                'prompt' => $storedIntent[$intent]['prompt'] ?? '',
            ];
        }

        $storedRules = $config->escalation_rules ?? [];
        $this->escalationRules = array_merge($this->escalationRules, $storedRules);
        $this->escalationKeywordsInput = implode(', ', $this->escalationRules['escalate_keywords'] ?? []);
    }

    public function loadRecentConversations(): void
    {
        $this->recentConversations = WaConversation::whereNotNull('chatbot_intent')
            ->where('chatbot_intent', '!=', '')
            ->orderByDesc('last_message_at')
            ->limit(15)
            ->get()
            ->map(fn($c) => [
                'phone' => $c->contact_phone,
                'name' => $c->contact_name,
                'last_message' => $c->last_message,
                'intent' => $c->chatbot_intent,
                'confidence' => $c->chatbot_confidence,
                'at' => $c->last_message_at?->diffForHumans(),
            ])
            ->toArray();
    }

    public function saveConfig(): void
    {
        $escalationKeywords = array_values(array_filter(array_map('trim', explode(',', $this->escalationKeywordsInput))));
        $this->escalationRules['escalate_keywords'] = $escalationKeywords;

        $data = [
            'company_id' => auth()->user()?->company_id ?? 1,
            'keyword' => '*',
            'match_type' => 'contains',
            'reply_text' => 'Dijawab oleh AI Chatbot',
            'is_ai_powered' => $this->isAiEnabled,
            'ai_provider_id' => $this->aiProviderId ?: null,
            'ai_prompt_template' => $this->promptTemplate,
            'fallback_message' => $this->fallbackMessage,
            'intent_config' => $this->intentConfig,
            'escalation_rules' => $this->escalationRules,
            'is_active' => $this->isActive,
        ];

        $config = WaAutoReply::where('keyword', '*')->first();

        if ($config) {
            $config->update($data);
        } else {
            WaAutoReply::create($data);
        }

        $this->saved = true;

        Notification::make()
            ->title('Konfigurasi tersimpan')
            ->body('Pengaturan Chatbot AI WhatsApp berhasil disimpan.')
            ->success()
            ->send();
    }

    public function testConnection(): void
    {
        $this->reset('connectionResult', 'connectionOk');

        if (!$this->aiProviderId) {
            $this->connectionResult = 'Pilih AI Provider terlebih dahulu.';
            $this->connectionOk = false;
            return;
        }

        $result = app(WhatsappAiService::class)->testConnection((int) $this->aiProviderId);

        $this->connectionResult = $result['message'];
        $this->connectionOk = $result['success'];

        if ($result['success']) {
            Notification::make()->title('Koneksi Berhasil')->success()->send();
        } else {
            Notification::make()->title('Koneksi Gagal')->danger()->send();
        }
    }

    public function sendTestMessage(): void
    {
        $message = trim($this->testMessage);
        if (empty($message)) {
            return;
        }

        if (!$this->aiProviderId) {
            Notification::make()->title('Pilih AI Provider terlebih dahulu')->warning()->send();
            return;
        }

        $this->chatHistory[] = ['role' => 'user', 'content' => $message, 'at' => now()->format('H:i')];
        $this->testMessage = '';
        $this->isTesting = true;

        $service = app(WhatsappAiService::class);
        $service->setConfigForTest($this->buildConfigPayload());

        $context = $service->buildContext($this->testPhone);
        $result = $service->testChat($message, (int) $this->aiProviderId, $context, $this->promptTemplate);

        $this->isTesting = false;

        $reply = $result['reply'] ?? 'Tidak ada respons.';
        $meta = isset($result['elapsed_ms']) ? " · {$result['elapsed_ms']}ms · {$result['model']}" : '';

        $this->chatHistory[] = [
            'role' => 'ai',
            'content' => $reply,
            'meta' => trim($meta),
            'at' => now()->format('H:i'),
        ];
    }

    public function clearChat(): void
    {
        $this->chatHistory = [];
    }

    public function insertVariable(string $variable): void
    {
        $this->promptTemplate = $this->promptTemplate . $variable;
    }

    protected function buildConfigPayload(): array
    {
        return [
            'ai_provider_id' => $this->aiProviderId,
            'ai_prompt_template' => $this->promptTemplate,
            'fallback_message' => $this->fallbackMessage,
            'intent_config' => $this->intentConfig,
            'escalation_rules' => $this->escalationRules,
        ];
    }

    public function getIntentLabels(): array
    {
        return self::AVAILABLE_INTENTS;
    }

    public function getEscalatableIntentLabels(): array
    {
        return self::ESCALATABLE_INTENTS;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('testConnection')
                ->label('Tes Koneksi')
                ->icon('heroicon-o-signal')
                ->color('gray')
                ->action(fn() => $this->testConnection()),
            Action::make('save')
                ->label('Simpan Konfigurasi')
                ->icon('heroicon-o-check-circle')
                ->action(fn() => $this->saveConfig()),
        ];
    }
}
