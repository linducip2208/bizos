<?php

namespace App\Filament\Pages;

use BackedEnum;
use App\Models\BiometricRegistration;
use App\Models\OfflineAction;
use App\Models\SystemSetting;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\View;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Cache;

class MobileSettings extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-device-phone-mobile';
    protected static ?string $navigationLabel = 'Pengaturan Mobile';
    protected static ?int $navigationSort = 99;
    protected static ?string $title = 'Pengaturan Mobile & PWA';

    public static function getNavigationGroup(): ?string
    {
        return 'Sistem';
    }

    protected static string $view = 'filament.pages.mobile-settings';

    public ?array $data = [];
    public array $offlineConfig = [];
    public array $biometricConfig = [];
    public array $pwaConfig = [];
    public array $notificationConfig = [];

    public function mount(): void
    {
        $this->form->fill();

        $this->offlineConfig = [
            'enabled' => $this->getSetting('mobile_offline_enabled', 'true') === 'true',
            'max_cache_size_mb' => (int) $this->getSetting('mobile_cache_max_mb', '50'),
            'data_types' => json_decode($this->getSetting('mobile_offline_data_types', '["employees","products","warehouses","tasks","shifts","settings"]'), true),
            'auto_sync_interval_minutes' => (int) $this->getSetting('mobile_sync_interval', '15'),
            'sync_only_on_wifi' => $this->getSetting('mobile_sync_wifi_only', 'false') === 'true',
        ];

        $this->biometricConfig = [
            'enabled' => $this->getSetting('mobile_biometric_enabled', 'true') === 'true',
            'require_for_clock_in' => $this->getSetting('mobile_biometric_clock_in', 'false') === 'true',
            'require_for_sensitive_actions' => $this->getSetting('mobile_biometric_sensitive', 'true') === 'true',
            'challenge_timeout_seconds' => (int) $this->getSetting('mobile_biometric_timeout', '300'),
            'max_devices_per_user' => (int) $this->getSetting('mobile_biometric_max_devices', '3'),
        ];

        $this->pwaConfig = [
            'app_name' => $this->getSetting('pwa_app_name', 'BizOS'),
            'short_name' => $this->getSetting('pwa_short_name', 'BizOS'),
            'theme_color' => $this->getSetting('pwa_theme_color', '#4f46e5'),
            'background_color' => $this->getSetting('pwa_bg_color', '#1e1b4b'),
            'display_mode' => $this->getSetting('pwa_display_mode', 'standalone'),
        ];

        $this->notificationConfig = [
            'fcm_enabled' => $this->getSetting('mobile_fcm_enabled', 'false') === 'true',
            'fcm_server_key' => $this->getSetting('mobile_fcm_key', ''),
            'fcm_sender_id' => $this->getSetting('mobile_fcm_sender_id', ''),
            'vapid_public_key' => $this->getSetting('mobile_vapid_public', ''),
            'vapid_private_key' => $this->getSetting('mobile_vapid_private', ''),
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi PWA')
                    ->description('Status Progressive Web App saat ini')
                    ->schema([
                        Placeholder::make('pwa_status')
                            ->label('Status PWA')
                            ->content(function () {
                                $manifestExists = file_exists(public_path('manifest.json'));
                                $swExists = file_exists(public_path('sw.js'));
                                $registerExists = file_exists(public_path('pwa-register.js'));
                                $allReady = $manifestExists && $swExists && $registerExists;

                                $manifestCheck = $manifestExists ? '✅' : '❌';
                                $swCheck = $swExists ? '✅' : '❌';
                                $registerCheck = $registerExists ? '✅' : '❌';
                                $statusColor = $allReady ? 'text-success-500' : 'text-danger-500';
                                $statusText = $allReady ? 'PWA Siap' : 'PWA Belum Siap';

                                return view('filament.components.pwa-status', [
                                    'statusText' => $statusText,
                                    'statusColor' => $statusColor,
                                    'manifestCheck' => $manifestCheck,
                                    'swCheck' => $swCheck,
                                    'registerCheck' => $registerCheck,
                                    'allReady' => $allReady,
                                    'manifestSize' => $manifestExists ? round(filesize(public_path('manifest.json')) / 1024, 1) . ' KB' : '-',
                                    'swSize' => $swExists ? round(filesize(public_path('sw.js')) / 1024, 1) . ' KB' : '-',
                                ]);
                            }),

                        Placeholder::make('mobile_stats')
                            ->label('Statistik Mobile')
                            ->content(function () {
                                $offlinePending = OfflineAction::where('status', 'pending')->count();
                                $offlineFailed = OfflineAction::where('status', 'failed')->count();
                                $biometricUsers = BiometricRegistration::where('is_active', true)->distinct('user_id')->count();
                                $biometricDevices = BiometricRegistration::where('is_active', true)->count();

                                return view('filament.components.mobile-stats', [
                                    'offlinePending' => $offlinePending,
                                    'offlineFailed' => $offlineFailed,
                                    'biometricUsers' => $biometricUsers,
                                    'biometricDevices' => $biometricDevices,
                                ]);
                            }),
                    ]),
            ])
            ->statePath('data');
    }

    public function saveOfflineConfig(): void
    {
        $this->setSetting('mobile_offline_enabled', $this->offlineConfig['enabled'] ? 'true' : 'false');
        $this->setSetting('mobile_cache_max_mb', (string) ($this->offlineConfig['max_cache_size_mb'] ?? 50));
        $this->setSetting('mobile_offline_data_types', json_encode($this->offlineConfig['data_types'] ?? []));
        $this->setSetting('mobile_sync_interval', (string) ($this->offlineConfig['auto_sync_interval_minutes'] ?? 15));
        $this->setSetting('mobile_sync_wifi_only', ($this->offlineConfig['sync_only_on_wifi'] ?? false) ? 'true' : 'false');

        Cache::forget('mobile_settings_cache');

        Notification::make()
            ->title('Konfigurasi offline tersimpan')
            ->success()
            ->send();
    }

    public function saveBiometricConfig(): void
    {
        $this->setSetting('mobile_biometric_enabled', $this->biometricConfig['enabled'] ? 'true' : 'false');
        $this->setSetting('mobile_biometric_clock_in', ($this->biometricConfig['require_for_clock_in'] ?? false) ? 'true' : 'false');
        $this->setSetting('mobile_biometric_sensitive', ($this->biometricConfig['require_for_sensitive_actions'] ?? true) ? 'true' : 'false');
        $this->setSetting('mobile_biometric_timeout', (string) ($this->biometricConfig['challenge_timeout_seconds'] ?? 300));
        $this->setSetting('mobile_biometric_max_devices', (string) ($this->biometricConfig['max_devices_per_user'] ?? 3));

        Cache::forget('mobile_settings_cache');

        Notification::make()
            ->title('Konfigurasi biometric tersimpan')
            ->success()
            ->send();
    }

    public function savePwaConfig(): void
    {
        $this->setSetting('pwa_app_name', $this->pwaConfig['app_name'] ?? 'BizOS');
        $this->setSetting('pwa_short_name', $this->pwaConfig['short_name'] ?? 'BizOS');
        $this->setSetting('pwa_theme_color', $this->pwaConfig['theme_color'] ?? '#4f46e5');
        $this->setSetting('pwa_bg_color', $this->pwaConfig['background_color'] ?? '#1e1b4b');
        $this->setSetting('pwa_display_mode', $this->pwaConfig['display_mode'] ?? 'standalone');

        // Regenerate manifest.json
        $this->regenerateManifest();

        Cache::forget('mobile_settings_cache');

        Notification::make()
            ->title('Konfigurasi PWA tersimpan & manifest.json diperbarui')
            ->success()
            ->send();
    }

    public function saveNotificationConfig(): void
    {
        $this->setSetting('mobile_fcm_enabled', ($this->notificationConfig['fcm_enabled'] ?? false) ? 'true' : 'false');
        $this->setSetting('mobile_fcm_key', $this->notificationConfig['fcm_server_key'] ?? '');
        $this->setSetting('mobile_fcm_sender_id', $this->notificationConfig['fcm_sender_id'] ?? '');
        $this->setSetting('mobile_vapid_public', $this->notificationConfig['vapid_public_key'] ?? '');
        $this->setSetting('mobile_vapid_private', $this->notificationConfig['vapid_private_key'] ?? '');

        Cache::forget('mobile_settings_cache');

        Notification::make()
            ->title('Konfigurasi notifikasi push tersimpan')
            ->success()
            ->send();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(BiometricRegistration::query()->where('is_active', true))
            ->columns([
                TextColumn::make('user.name')
                    ->label('Pengguna')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('device_name')
                    ->label('Perangkat')
                    ->searchable(),
                TextColumn::make('platform')
                    ->label('Platform')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'ios' => 'gray',
                        'android' => 'success',
                        default => 'warning',
                    }),
                TextColumn::make('registered_at')
                    ->label('Terdaftar')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('last_used_at')
                    ->label('Terakhir Digunakan')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                Action::make('revoke')
                    ->label('Cabut')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (BiometricRegistration $record) {
                        $record->update(['is_active' => false]);
                        Notification::make()
                            ->title('Biometric dicabut')
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('registered_at', 'desc')
            ->paginated([10, 25, 50]);
    }

    protected function getSetting(string $key, string $default = ''): string
    {
        $companyId = auth()->user()?->company_id;
        if (!$companyId) return $default;

        return SystemSetting::where('company_id', $companyId)
            ->where('key', $key)
            ->value('value') ?? $default;
    }

    protected function setSetting(string $key, string $value): void
    {
        $companyId = auth()->user()?->company_id;
        if (!$companyId) return;

        SystemSetting::updateOrCreate(
            ['company_id' => $companyId, 'key' => $key],
            [
                'value' => $value,
                'type' => 'string',
                'group' => 'mobile',
                'description' => 'Mobile app configuration',
            ]
        );
    }

    protected function regenerateManifest(): void
    {
        $manifest = [
            'name' => $this->pwaConfig['app_name'] ?? 'BizOS',
            'short_name' => $this->pwaConfig['short_name'] ?? 'BizOS',
            'description' => 'Aplikasi all-in-one untuk manajemen bisnis',
            'start_url' => '/admin',
            'display' => $this->pwaConfig['display_mode'] ?? 'standalone',
            'display_override' => ['standalone', 'minimal-ui'],
            'orientation' => 'portrait-primary',
            'background_color' => $this->pwaConfig['background_color'] ?? '#1e1b4b',
            'theme_color' => $this->pwaConfig['theme_color'] ?? '#4f46e5',
            'categories' => ['business', 'productivity'],
            'lang' => 'id-ID',
            'dir' => 'ltr',
            'scope' => '/',
            'icons' => [
                ['src' => '/favicon.ico', 'sizes' => '48x48', 'type' => 'image/x-icon'],
                ['src' => '/marketing/icon-192x192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any maskable'],
                ['src' => '/marketing/icon-512x512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any maskable'],
            ],
            'shortcuts' => [
                ['name' => 'Dashboard', 'short_name' => 'Dashboard', 'description' => 'Lihat dashboard bisnis', 'url' => '/admin', 'icons' => [['src' => '/favicon.ico', 'sizes' => '48x48']]],
                ['name' => 'Clock In', 'short_name' => 'Clock In', 'description' => 'Absen masuk kerja', 'url' => '/admin/attendances', 'icons' => [['src' => '/favicon.ico', 'sizes' => '48x48']]],
                ['name' => 'Scan Barcode', 'short_name' => 'Scan', 'description' => 'Scan barcode produk', 'url' => '/admin/scan', 'icons' => [['src' => '/favicon.ico', 'sizes' => '48x48']]],
            ],
        ];

        file_put_contents(
            public_path('manifest.json'),
            json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        );
    }
}
