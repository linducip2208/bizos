<?php

namespace App\Services;

use App\Models\Calendar;
use App\Models\CalendarEvent;
use App\Models\Company;
use App\Models\IntegrationConnector;
use App\Models\IntegrationSyncLog;
use App\Models\OauthProvider;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CalendarSyncService
{
    public const PROVIDERS = [
        'google' => [
            'label' => 'Google Calendar',
            'connector_type' => 'google_calendar',
            'oauth_provider' => 'google_calendar',
            'token_url' => 'https://oauth2.googleapis.com/token',
            'auth_url' => 'https://accounts.google.com/o/oauth2/v2/auth',
            'scopes' => 'https://www.googleapis.com/auth/calendar',
            'icon' => 'heroicon-o-globe-alt',
            'color' => 'red',
            'description' => 'Sinkronisasi dua arah dengan Google Calendar.',
        ],
        'outlook' => [
            'label' => 'Outlook / Microsoft 365',
            'connector_type' => 'outlook_calendar',
            'oauth_provider' => 'outlook_calendar',
            'token_url' => 'https://login.microsoftonline.com/common/oauth2/v2.0/token',
            'auth_url' => 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize',
            'scopes' => 'offline_access https://graph.microsoft.com/Calendars.ReadWrite',
            'icon' => 'heroicon-o-squares-2x2',
            'color' => 'indigo',
            'description' => 'Sinkronisasi dua arah dengan Outlook Calendar (Microsoft 365).',
        ],
    ];

    // ──────────────────────────────────────────────
    //  PROVIDER STATUS
    // ──────────────────────────────────────────────

    public function getSyncProviders(): array
    {
        $companyId = $this->companyId();
        $result = [];

        foreach (self::PROVIDERS as $key => $meta) {
            $oauth = $companyId ? $this->getOauthConfig($companyId, $key) : null;
            $connector = $companyId ? $this->findConnector($companyId, $key) : null;

            $result[] = [
                'key' => $key,
                'label' => $meta['label'],
                'description' => $meta['description'],
                'icon' => $meta['icon'],
                'color' => $meta['color'],
                'auth_url' => $this->buildAuthUrl($key),
                'configured' => $oauth !== null && !empty($oauth['client_id']) && !empty($oauth['client_secret']),
                'connected' => $connector?->status === 'connected',
                'status' => $connector?->status ?? 'not_configured',
                'last_sync_at' => $connector?->last_sync_at?->diffForHumans(),
                'last_error_message' => $connector?->last_error_message,
            ];
        }

        return $result;
    }

    public function getSyncStatus(): array
    {
        $companyId = $this->companyId();
        $status = [];

        foreach (self::PROVIDERS as $key => $meta) {
            $connector = $companyId ? $this->findConnector($companyId, $key) : null;
            $status[$key] = [
                'label' => $meta['label'],
                'connected' => $connector?->status === 'connected',
                'status' => $connector?->status ?? 'not_configured',
                'last_sync_at' => $connector?->last_sync_at?->diffForHumans(),
                'last_error_message' => $connector?->last_error_message,
            ];
        }

        return $status;
    }

    public function getSyncLogs(int $companyId): array
    {
        return IntegrationSyncLog::query()
            ->where('company_id', $companyId)
            ->whereIn('connector_type', array_column(self::PROVIDERS, 'connector_type'))
            ->latest('started_at')
            ->limit(20)
            ->get()
            ->toArray();
    }

    // ──────────────────────────────────────────────
    //  OAUTH CONFIG
    // ──────────────────────────────────────────────

    public function getOauthConfig(int $companyId, string $provider): ?array
    {
        $meta = self::PROVIDERS[$provider] ?? null;
        if (!$meta) {
            return null;
        }

        $row = OauthProvider::where('company_id', $companyId)
            ->where('provider', $meta['oauth_provider'])
            ->first();

        if (!$row) {
            return null;
        }

        return [
            'client_id' => $row->client_id,
            'client_secret' => $row->client_secret_encrypted ? Crypt::decryptString($row->client_secret_encrypted) : '',
            'redirect_uri' => $row->redirect_uri,
        ];
    }

    public function saveOauthConfig(int $companyId, string $provider, string $clientId, string $clientSecret, ?string $redirectUri = null): void
    {
        $meta = self::PROVIDERS[$provider] ?? null;
        if (!$meta) {
            throw new \RuntimeException('Provider tidak dikenal: ' . $provider);
        }

        OauthProvider::updateOrCreate(
            ['company_id' => $companyId, 'provider' => $meta['oauth_provider']],
            [
                'client_id' => $clientId,
                'client_secret_encrypted' => $clientSecret ? Crypt::encryptString($clientSecret) : '',
                'redirect_uri' => $redirectUri ?: $this->defaultRedirectUri(),
                'is_active' => true,
            ]
        );
    }

    public function buildAuthUrl(string $provider): ?string
    {
        $meta = self::PROVIDERS[$provider] ?? null;
        if (!$meta) {
            return null;
        }

        $companyId = $this->companyId();
        $oauth = $companyId ? $this->getOauthConfig($companyId, $provider) : null;
        if (!$oauth || empty($oauth['client_id'])) {
            return null;
        }

        $params = http_build_query([
            'client_id' => $oauth['client_id'],
            'redirect_uri' => $oauth['redirect_uri'] ?? $this->defaultRedirectUri(),
            'response_type' => 'code',
            'scope' => $meta['scopes'],
            'access_type' => 'offline',
            'prompt' => 'consent',
        ]);

        return $meta['auth_url'] . '?' . $params;
    }

    // ──────────────────────────────────────────────
    //  CONNECT / DISCONNECT
    // ──────────────────────────────────────────────

    public function connectProvider(string $provider, string $authCode): void
    {
        $companyId = $this->companyId();
        if (!$companyId) {
            throw new \RuntimeException('Tidak ada perusahaan aktif.');
        }

        $meta = self::PROVIDERS[$provider] ?? null;
        if (!$meta) {
            throw new \RuntimeException('Provider tidak dikenal: ' . $provider);
        }

        $oauth = $this->getOauthConfig($companyId, $provider);

        $credentials = [];
        if ($oauth && !empty($oauth['client_id']) && !empty($oauth['client_secret'])) {
            $credentials['client_id'] = Crypt::encryptString($oauth['client_id']);
            $credentials['client_secret'] = Crypt::encryptString($oauth['client_secret']);
        }

        $refreshToken = null;
        if (!empty($authCode) && $oauth && !empty($oauth['client_id'])) {
            $refreshToken = $this->exchangeAuthCode($provider, $authCode, $oauth);
        }

        if ($refreshToken) {
            $credentials['refresh_token'] = Crypt::encryptString($refreshToken);
        }

        $connector = IntegrationConnector::updateOrCreate(
            ['company_id' => $companyId, 'connector_type' => $meta['connector_type']],
            [
                'name' => $meta['label'],
                'status' => 'connected',
                'credentials_encrypted' => $credentials,
                'configuration' => [
                    'provider' => $provider,
                    'scopes' => $meta['scopes'],
                    'auto_sync' => $this->isAutoSyncEnabled($companyId),
                ],
                'is_active' => true,
                'last_error_at' => null,
                'last_error_message' => null,
            ]
        );

        $this->writeSyncLog(
            $companyId,
            $connector->id,
            $meta['connector_type'],
            'calendar',
            'bidirectional',
            'success',
            0,
            0,
            0,
            $meta['label'] . ' terhubung' . ($refreshToken ? '' : ' (mode stub, tanpa token)')
        );
    }

    public function disconnectProvider(string $provider): void
    {
        $companyId = $this->companyId();
        if (!$companyId) {
            return;
        }

        $meta = self::PROVIDERS[$provider] ?? null;
        if (!$meta) {
            throw new \RuntimeException('Provider tidak dikenal: ' . $provider);
        }

        $connector = $this->findConnector($companyId, $provider);
        if ($connector) {
            $connector->update(['status' => 'disconnected', 'is_active' => false]);
        }

        $this->writeSyncLog(
            $companyId,
            $connector?->id,
            $meta['connector_type'],
            'calendar',
            'bidirectional',
            'success',
            0,
            0,
            0,
            $meta['label'] . ' diputus'
        );
    }

    // ──────────────────────────────────────────────
    //  EVENT SYNC (EXPORT / UPDATE / DELETE / IMPORT)
    // ──────────────────────────────────────────────

    public function exportEvent(CalendarEvent $event, string $provider): string
    {
        $meta = self::PROVIDERS[$provider] ?? null;
        if (!$meta) {
            throw new \RuntimeException('Provider tidak dikenal: ' . $provider);
        }

        $externalId = $this->pushToExternal($event, $provider);

        $event->update([
            'external_id' => $externalId,
            'external_provider' => $provider,
            'last_synced_at' => now(),
        ]);

        $companyId = $this->companyId();
        if ($companyId) {
            $connector = $this->findConnector($companyId, $provider);
            $connector?->update(['last_sync_at' => now()]);
            $this->writeSyncLog(
                $companyId,
                $connector?->id,
                $meta['connector_type'],
                'calendar_event',
                'outbound',
                'success',
                1,
                1,
                0,
                'Event "' . $event->title . '" diekspor'
            );
        }

        return $externalId;
    }

    public function updateEvent(CalendarEvent $event): void
    {
        if (!$event->external_id || !$event->external_provider) {
            return;
        }

        // Stub: push perubahan ke kalender eksternal.
        $event->update(['last_synced_at' => now()]);
    }

    public function deleteEvent(CalendarEvent $event): void
    {
        if (!$event->external_id) {
            return;
        }

        // Stub: hapus dari kalender eksternal, lalu bersihkan referensi lokal.
        $event->update([
            'external_id' => null,
            'external_provider' => null,
            'last_synced_at' => now(),
        ]);
    }

    public function importEvents(string $provider, Carbon $from, Carbon $to): array
    {
        $meta = self::PROVIDERS[$provider] ?? null;
        if (!$meta) {
            throw new \RuntimeException('Provider tidak dikenal: ' . $provider);
        }

        $companyId = $this->companyId();

        // Stub: pada implementasi nyata ini menarik event dari API provider.
        // Di sini kita tandai event lokal dalam rentang sebagai sudah tersinkron.
        $events = CalendarEvent::query()
            ->whereHas('calendar', fn ($q) => $q->where('company_id', $companyId))
            ->whereBetween('start_time', [$from, $to])
            ->get();

        foreach ($events as $event) {
            if (!$event->external_id) {
                $event->update([
                    'external_id' => $provider . '::' . $event->id,
                    'external_provider' => $provider,
                    'last_synced_at' => now(),
                ]);
            }
        }

        if ($companyId) {
            $connector = $this->findConnector($companyId, $provider);
            $connector?->update(['last_sync_at' => now()]);
            $this->writeSyncLog(
                $companyId,
                $connector?->id,
                $meta['connector_type'],
                'calendar_event',
                'inbound',
                'success',
                $events->count(),
                $events->count(),
                0,
                'Import event selesai'
            );
        }

        return ['success' => true, 'imported' => $events->count()];
    }

    public function syncNow(?string $provider = null): array
    {
        $companyId = $this->companyId();
        $providers = $provider ? [$provider] : array_keys(self::PROVIDERS);
        $summary = [];

        foreach ($providers as $p) {
            if (!isset(self::PROVIDERS[$p])) {
                continue;
            }

            $connector = $companyId ? $this->findConnector($companyId, $p) : null;
            if (!$connector || $connector->status !== 'connected') {
                $summary[$p] = ['success' => false, 'message' => 'Tidak terhubung'];
                continue;
            }

            $events = CalendarEvent::query()
                ->whereHas('calendar', fn ($q) => $q->where('company_id', $companyId))
                ->whereNull('external_id')
                ->get();

            $exported = 0;
            foreach ($events as $event) {
                try {
                    $this->exportEvent($event, $p);
                    $exported++;
                } catch (\Exception $e) {
                    Log::warning('CalendarSyncService: export event gagal', [
                        'event' => $event->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $import = $this->importEvents($p, now()->subDays(30), now()->addDays(90));

            $summary[$p] = [
                'success' => true,
                'exported' => $exported,
                'imported' => $import['imported'] ?? 0,
            ];
        }

        return $summary;
    }

    // ──────────────────────────────────────────────
    //  ICAL FEED
    // ──────────────────────────────────────────────

    public function generateIcalFeed(int $companyId): string
    {
        $token = $this->ensureIcalToken($companyId);

        return route('calendar.ical', ['token' => $token]);
    }

    public function regenerateIcalToken(int $companyId): void
    {
        SystemSetting::updateOrCreate(
            ['company_id' => $companyId, 'key' => 'calendar.ical_token'],
            [
                'value' => Str::random(40),
                'type' => 'string',
                'group' => 'calendar',
                'description' => 'Token feed iCal publik',
            ]
        );
    }

    public function resolveCompanyByToken(string $token): ?int
    {
        $companyId = SystemSetting::where('key', 'calendar.ical_token')
            ->where('value', $token)
            ->value('company_id');

        return $companyId ? (int) $companyId : null;
    }

    public function buildIcalFeed(int $companyId): string
    {
        $company = Company::find($companyId);
        $calendarIds = Calendar::where('company_id', $companyId)->pluck('id');
        $events = CalendarEvent::whereIn('calendar_id', $calendarIds)->orderBy('start_time')->get();

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//BizOS//Calendar Sync//ID',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'X-WR-CALNAME:' . $this->escapeIcal($company?->name ?? 'BizOS Calendar'),
            'X-WR-TIMEZONE:Asia/Jakarta',
        ];

        foreach ($events as $event) {
            $lines[] = 'BEGIN:VEVENT';
            $lines[] = 'UID:' . $event->id . '-bizos@' . ($company?->slug ?? 'bizos');
            $lines[] = 'DTSTAMP:' . $event->created_at->setTimezone('UTC')->format('Ymd\THis\Z');
            $lines[] = 'SUMMARY:' . $this->escapeIcal($event->title);

            if ($event->description) {
                $lines[] = 'DESCRIPTION:' . $this->escapeIcal($event->description);
            }
            if ($event->location) {
                $lines[] = 'LOCATION:' . $this->escapeIcal($event->location);
            }

            if ($event->is_all_day) {
                $lines[] = 'DTSTART;VALUE=DATE:' . $event->start_time->format('Ymd');
                $lines[] = 'DTEND;VALUE=DATE:' . $event->end_time->copy()->addDay()->format('Ymd');
            } else {
                $lines[] = 'DTSTART:' . $event->start_time->setTimezone('UTC')->format('Ymd\THis\Z');
                $lines[] = 'DTEND:' . $event->end_time->setTimezone('UTC')->format('Ymd\THis\Z');
            }

            $lines[] = 'END:VEVENT';
        }

        $lines[] = 'END:VCALENDAR';

        return implode("\r\n", $lines) . "\r\n";
    }

    // ──────────────────────────────────────────────
    //  AUTO-SYNC SETTING
    // ──────────────────────────────────────────────

    public function isAutoSyncEnabled(int $companyId): bool
    {
        $value = SystemSetting::where('company_id', $companyId)
            ->where('key', 'calendar.auto_sync')
            ->value('value');

        return $value === 'true' || $value === '1';
    }

    public function setAutoSync(int $companyId, bool $enabled): void
    {
        SystemSetting::updateOrCreate(
            ['company_id' => $companyId, 'key' => 'calendar.auto_sync'],
            [
                'value' => $enabled ? 'true' : 'false',
                'type' => 'boolean',
                'group' => 'calendar',
                'description' => 'Aktifkan sinkronisasi otomatis kalender',
            ]
        );
    }

    // ──────────────────────────────────────────────
    //  HELPERS
    // ──────────────────────────────────────────────

    protected function companyId(): ?int
    {
        return auth()->user()?->company_id;
    }

    protected function findConnector(int $companyId, string $provider): ?IntegrationConnector
    {
        $meta = self::PROVIDERS[$provider] ?? null;
        if (!$meta) {
            return null;
        }

        return IntegrationConnector::where('company_id', $companyId)
            ->where('connector_type', $meta['connector_type'])
            ->first();
    }

    protected function defaultRedirectUri(): string
    {
        return rtrim((string) config('app.url', url('/')), '/') . '/admin/calendar-sync';
    }

    protected function exchangeAuthCode(string $provider, string $code, array $oauth): ?string
    {
        $meta = self::PROVIDERS[$provider] ?? null;
        if (!$meta) {
            return null;
        }

        try {
            $response = Http::asForm()->post($meta['token_url'], [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'client_id' => $oauth['client_id'],
                'client_secret' => $oauth['client_secret'],
                'redirect_uri' => $oauth['redirect_uri'] ?? $this->defaultRedirectUri(),
            ]);

            if ($response->successful()) {
                $json = $response->json();

                return $json['refresh_token'] ?? null;
            }

            Log::warning('CalendarSyncService: token exchange gagal', [
                'provider' => $provider,
                'status' => $response->status(),
            ]);
        } catch (\Exception $e) {
            Log::warning('CalendarSyncService: token exchange error', [
                'provider' => $provider,
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    protected function pushToExternal(CalendarEvent $event, string $provider): string
    {
        // Stub: pada implementasi nyata, event dikirim ke Google Calendar API
        // atau Microsoft Graph API. Di sini kita hasilkan external id stabil.
        return $provider . '::' . $event->id . '::' . Str::lower(Str::random(8));
    }

    protected function writeSyncLog(
        int $companyId,
        ?int $connectorId,
        string $connectorType,
        string $entity,
        string $direction,
        string $status,
        int $processed,
        int $succeeded,
        int $failed,
        ?string $summary
    ): void {
        IntegrationSyncLog::create([
            'company_id' => $companyId,
            'integration_connector_id' => $connectorId,
            'connector_type' => $connectorType,
            'entity' => $entity,
            'direction' => $direction,
            'status' => $status,
            'records_processed' => $processed,
            'records_succeeded' => $succeeded,
            'records_failed' => $failed,
            'summary' => $summary,
            'started_at' => now(),
            'completed_at' => now(),
        ]);
    }

    protected function ensureIcalToken(int $companyId): string
    {
        $token = SystemSetting::where('company_id', $companyId)
            ->where('key', 'calendar.ical_token')
            ->value('value');

        if ($token) {
            return $token;
        }

        $token = Str::random(40);
        SystemSetting::updateOrCreate(
            ['company_id' => $companyId, 'key' => 'calendar.ical_token'],
            [
                'value' => $token,
                'type' => 'string',
                'group' => 'calendar',
                'description' => 'Token feed iCal publik',
            ]
        );

        return $token;
    }

    protected function escapeIcal(string $text): string
    {
        $text = str_replace('\\', '\\\\', $text);
        $text = str_replace(["\r\n", "\n", "\r"], '\\n', $text);
        $text = str_replace([';', ','], ['\\;', '\\,'], $text);

        return $text;
    }
}
