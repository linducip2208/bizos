<?php

namespace App\Filament\Pages;

use App\Services\CalendarSyncService;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class CalendarSync extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?int $navigationSort = 713;

    protected static ?string $title = 'Calendar Sync';

    protected static ?string $navigationLabel = 'Calendar Sync';

    protected static ?string $slug = 'calendar-sync';

    protected string $view = 'filament.pages.calendar-sync';

    public ?array $data = [];

    public array $providers = [];

    public array $syncStatus = [];

    public array $syncLogs = [];

    public ?string $icalFeedUrl = null;

    public bool $autoSync = false;

    public static function getNavigationGroup(): ?string
    {
        return 'Collaboration';
    }

    public function mount(): void
    {
        $this->form->fill($this->defaultFormData());
        $this->refreshState();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('OAuth — Google Calendar')
                    ->description('Kredensial OAuth2 dari Google Cloud Console')
                    ->icon('heroicon-o-globe-alt')
                    ->collapsed(false)
                    ->schema([
                        TextInput::make('google_client_id')
                            ->label('Client ID')
                            ->maxLength(255),
                        TextInput::make('google_client_secret')
                            ->label('Client Secret')
                            ->password()
                            ->revealable(),
                        TextInput::make('google_auth_code')
                            ->label('Kode Otorisasi (opsional)')
                            ->helperText('Tempel kode dari URL redirect setelah menyetujui akses Google'),
                    ]),
                Section::make('OAuth — Outlook / Microsoft 365')
                    ->description('Kredensial OAuth2 dari Azure AD App Registration')
                    ->icon('heroicon-o-squares-2x2')
                    ->collapsed(false)
                    ->schema([
                        TextInput::make('outlook_client_id')
                            ->label('Client ID')
                            ->maxLength(255),
                        TextInput::make('outlook_client_secret')
                            ->label('Client Secret')
                            ->password()
                            ->revealable(),
                        TextInput::make('outlook_auth_code')
                            ->label('Kode Otorisasi (opsional)')
                            ->helperText('Tempel kode dari URL redirect setelah menyetujui akses Microsoft'),
                    ]),
            ])
            ->statePath('data');
    }

    public function refreshState(): void
    {
        $companyId = auth()->user()?->company_id;
        if (!$companyId) {
            $this->providers = [];
            $this->syncStatus = [];
            $this->syncLogs = [];
            $this->icalFeedUrl = null;
            $this->autoSync = false;

            return;
        }

        $service = $this->service();
        $this->providers = $service->getSyncProviders();
        $this->syncStatus = $service->getSyncStatus();
        $this->syncLogs = $service->getSyncLogs($companyId);
        $this->icalFeedUrl = $service->generateIcalFeed($companyId);
        $this->autoSync = $service->isAutoSyncEnabled($companyId);
    }

    public function saveOauth(): void
    {
        $companyId = auth()->user()?->company_id;
        if (!$companyId) {
            return;
        }

        $data = $this->form->getState();
        $service = $this->service();

        foreach (['google', 'outlook'] as $provider) {
            $service->saveOauthConfig(
                $companyId,
                $provider,
                (string) ($data[$provider . '_client_id'] ?? ''),
                (string) ($data[$provider . '_client_secret'] ?? ''),
            );
        }

        $this->refreshState();

        Notification::make()
            ->title('Konfigurasi OAuth tersimpan')
            ->success()
            ->send();
    }

    public function connect(string $provider): void
    {
        $companyId = auth()->user()?->company_id;
        if (!$companyId) {
            return;
        }

        $data = $this->form->getState();

        try {
            $this->service()->connectProvider($provider, (string) ($data[$provider . '_auth_code'] ?? ''));
            $this->refreshState();

            Notification::make()
                ->title('Terhubung')
                ->body(CalendarSyncService::PROVIDERS[$provider]['label'] . ' berhasil dihubungkan.')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal terhubung')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function disconnect(string $provider): void
    {
        try {
            $this->service()->disconnectProvider($provider);
            $this->refreshState();

            Notification::make()
                ->title('Terputus')
                ->body(CalendarSyncService::PROVIDERS[$provider]['label'] . ' berhasil diputus.')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal memutus')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function syncNow(): void
    {
        $summary = $this->service()->syncNow();
        $this->refreshState();

        Notification::make()
            ->title('Sinkronisasi selesai')
            ->body($this->summaryMessage($summary))
            ->success()
            ->send();
    }

    public function toggleAutoSync(): void
    {
        $companyId = auth()->user()?->company_id;
        if (!$companyId) {
            return;
        }

        $this->autoSync = !$this->autoSync;
        $this->service()->setAutoSync($companyId, $this->autoSync);

        Notification::make()
            ->title($this->autoSync ? 'Auto-sync aktif' : 'Auto-sync nonaktif')
            ->success()
            ->send();
    }

    public function regenerateFeed(): void
    {
        $companyId = auth()->user()?->company_id;
        if (!$companyId) {
            return;
        }

        $this->service()->regenerateIcalToken($companyId);
        $this->refreshState();

        Notification::make()
            ->title('Feed iCal dibuat ulang')
            ->body('URL lama tidak lagi valid.')
            ->success()
            ->send();
    }

    protected function service(): CalendarSyncService
    {
        return app(CalendarSyncService::class);
    }

    protected function defaultFormData(): array
    {
        $companyId = auth()->user()?->company_id;
        $data = [
            'google_client_id' => '',
            'google_client_secret' => '',
            'google_auth_code' => '',
            'outlook_client_id' => '',
            'outlook_client_secret' => '',
            'outlook_auth_code' => '',
        ];

        if (!$companyId) {
            return $data;
        }

        foreach (['google', 'outlook'] as $provider) {
            $config = $this->service()->getOauthConfig($companyId, $provider);
            if ($config) {
                $data[$provider . '_client_id'] = $config['client_id'] ?? '';
                $data[$provider . '_client_secret'] = $config['client_secret'] ?? '';
            }
        }

        return $data;
    }

    protected function summaryMessage(array $summary): string
    {
        $parts = [];
        foreach ($summary as $provider => $result) {
            $label = $provider === 'google' ? 'Google' : 'Outlook';
            if (!empty($result['success'])) {
                $parts[] = "{$label}: {$result['exported']} diekspor, {$result['imported']} diimpor";
            } else {
                $parts[] = "{$label}: " . ($result['message'] ?? 'gagal');
            }
        }

        return implode(' · ', $parts);
    }
}
