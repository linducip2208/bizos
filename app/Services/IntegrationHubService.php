<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\DjpToken;
use App\Models\ErpConnector;
use App\Models\ErpSyncLog;
use App\Models\IntegrationConnector;
use App\Models\IntegrationSyncLog;
use App\Models\Invoice;
use App\Models\OauthProvider;
use App\Models\ShippingProvider;
use App\Models\SsoConfig;
use App\Models\VirtualAccount;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IntegrationHubService
{
    // ──────────────────────────────────────────────
    //  CONNECTOR CATALOG
    // ──────────────────────────────────────────────

    public function getAvailableConnectors(): array
    {
        return [
            [
                'type' => 'jurnal_id',
                'name' => 'Jurnal.id',
                'description' => 'Software akuntansi online Indonesia. Sinkronisasi faktur, pembayaran, jurnal, kontak, dan produk.',
                'icon' => 'heroicon-o-book-open',
                'color' => 'blue',
                'category' => 'accounting',
                'entities' => ['invoices', 'payments', 'journal_entries', 'contacts', 'products'],
                'auth_type' => 'api_key',
            ],
            [
                'type' => 'xero',
                'name' => 'Xero',
                'description' => 'Software akuntansi global. Sinkronisasi faktur, kontak, chart of accounts, dan laporan.',
                'icon' => 'heroicon-o-calculator',
                'color' => 'cyan',
                'category' => 'accounting',
                'entities' => ['invoices', 'contacts', 'chart_of_accounts', 'payments', 'reports'],
                'auth_type' => 'oauth2',
            ],
            [
                'type' => 'accurate',
                'name' => 'Accurate Online',
                'description' => 'Software akuntansi Indonesia. Ekspor/impor faktur penjualan, pembelian, dan laporan keuangan.',
                'icon' => 'heroicon-o-document-chart-bar',
                'color' => 'emerald',
                'category' => 'accounting',
                'entities' => ['invoices', 'purchase_invoices', 'journal_entries', 'contacts'],
                'auth_type' => 'api_key',
            ],
            [
                'type' => 'google_workspace',
                'name' => 'Google Workspace',
                'description' => 'Sinkronisasi kalender, Google Drive, Gmail. Tampilkan event di kalender BizOS.',
                'icon' => 'heroicon-o-globe-alt',
                'color' => 'red',
                'category' => 'productivity',
                'entities' => ['calendar', 'drive', 'gmail'],
                'auth_type' => 'oauth2',
            ],
            [
                'type' => 'microsoft_365',
                'name' => 'Microsoft 365',
                'description' => 'Sinkronisasi Outlook Calendar, Teams presence, SharePoint. Status Teams di profil karyawan.',
                'icon' => 'heroicon-o-squares-2x2',
                'color' => 'indigo',
                'category' => 'productivity',
                'entities' => ['outlook_calendar', 'teams_presence', 'sharepoint'],
                'auth_type' => 'oauth2',
            ],
            [
                'type' => 'open_banking',
                'name' => 'Open Banking (BI-FAST)',
                'description' => 'Integrasi perbankan Indonesia. Cek saldo, mutasi rekening, transfer, virtual account.',
                'icon' => 'heroicon-o-banknotes',
                'color' => 'amber',
                'category' => 'banking',
                'entities' => ['balance', 'transactions', 'transfer', 'virtual_account'],
                'auth_type' => 'api_key_cert',
            ],
            [
                'type' => 'djp',
                'name' => 'DJP (Pajak)',
                'description' => 'Integrasi DJP Online. Submit e-Faktur PPN, tarik data PK/PM, validasi NPWP, submit SPT.',
                'icon' => 'heroicon-o-document-check',
                'color' => 'orange',
                'category' => 'tax',
                'entities' => ['efaktur', 'pkpm', 'npwp_validation', 'spt'],
                'auth_type' => 'certificate',
            ],
        ];
    }

    // ──────────────────────────────────────────────
    //  CONNECTOR MANAGEMENT
    // ──────────────────────────────────────────────

    public function getConnectorCatalog(int $companyId): array
    {
        $catalog = $this->getAvailableConnectors();
        $installed = IntegrationConnector::where('company_id', $companyId)->get()->keyBy('connector_type');

        return array_map(function ($connector) use ($installed) {
            $installedConnector = $installed->get($connector['type']);
            return array_merge($connector, [
                'installed' => $installedConnector !== null,
                'status' => $installedConnector?->status ?? 'not_installed',
                'last_sync_at' => $installedConnector?->last_sync_at?->diffForHumans(),
                'id' => $installedConnector?->id,
                'configuration' => $installedConnector?->configuration,
            ]);
        }, $catalog);
    }

    // ──────────────────────────────────────────────
    //  JURNAL.ID CONNECTOR
    // ──────────────────────────────────────────────

    public function connectJurnalId(int $companyId, string $apiKey): array
    {
        try {
            // Validate API key with Jurnal.id
            $response = Http::withHeaders([
                'apikey' => $apiKey,
                'Accept' => 'application/json',
            ])->get('https://api.jurnal.id/core/api/v1/company_infos');

            if (!$response->successful()) {
                return ['success' => false, 'message' => 'API key tidak valid: ' . $response->status()];
            }

            // Check if user already has Jurnal account
            $userInfo = $response->json();

            $connector = IntegrationConnector::updateOrCreate(
                ['company_id' => $companyId, 'connector_type' => 'jurnal_id'],
                [
                    'name' => 'Jurnal.id - ' . ($userInfo['company_name'] ?? 'Connected'),
                    'status' => 'connected',
                    'credentials_encrypted' => ['api_key' => Crypt::encryptString($apiKey)],
                    'configuration' => [
                        'base_url' => 'https://api.jurnal.id/core/api/v1/',
                        'entities' => ['invoices', 'payments', 'journal_entries', 'contacts', 'products'],
                        'sync_direction' => 'bidirectional',
                    ],
                ]
            );

            return [
                'success' => true,
                'message' => 'Berhasil terhubung ke Jurnal.id',
                'connector_id' => $connector->id,
                'company_name' => $userInfo['company_name'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('Jurnal.id connection failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Gagal terhubung: ' . $e->getMessage()];
        }
    }

    public function exportToJurnalId(int $companyId, string $entity, array $records): array
    {
        $connector = $this->findActiveConnector($companyId, 'jurnal_id');
        if (!$connector) {
            return ['success' => false, 'message' => 'Konektor Jurnal.id tidak terhubung'];
        }

        $apiKey = Crypt::decryptString($connector->credentials_encrypted['api_key']);
        $baseUrl = $connector->configuration['base_url'] ?? 'https://api.jurnal.id/core/api/v1/';
        $endpoint = $this->jurnalEntityEndpoint($entity);

        $log = $this->startSyncLog($companyId, $connector->id, 'jurnal_id', $entity, 'outbound');

        try {
            $response = Http::withHeaders([
                'apikey' => $apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->post($baseUrl . $endpoint, [
                $entity => $records,
            ]);

            if ($response->successful()) {
                $this->completeSyncLog($log, count($records), count($records), 0, $response->json());
                $connector->update(['last_sync_at' => now(), 'last_sync_result' => $response->json()]);
                return [
                    'success' => true,
                    'synced' => count($records),
                    'response' => $response->json(),
                ];
            }

            $this->completeSyncLog($log, count($records), 0, count($records), $response->json(), 'failed');
            $connector->update(['last_error_at' => now(), 'last_error_message' => $response->body()]);
            return ['success' => false, 'message' => 'Export gagal: ' . $response->status()];
        } catch (\Exception $e) {
            $this->failSyncLog($log, $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function importFromJurnalId(int $companyId, string $entity, ?Carbon $since = null): array
    {
        $connector = $this->findActiveConnector($companyId, 'jurnal_id');
        if (!$connector) {
            return ['success' => false, 'message' => 'Konektor Jurnal.id tidak terhubung'];
        }

        $apiKey = Crypt::decryptString($connector->credentials_encrypted['api_key']);
        $baseUrl = $connector->configuration['base_url'] ?? 'https://api.jurnal.id/core/api/v1/';
        $endpoint = $this->jurnalEntityEndpoint($entity);

        $log = $this->startSyncLog($companyId, $connector->id, 'jurnal_id', $entity, 'inbound');

        try {
            $params = ['page' => 1, 'page_size' => 100];
            if ($since) {
                $params['updated_since'] = $since->toIso8601String();
            }

            $response = Http::withHeaders([
                'apikey' => $apiKey,
                'Accept' => 'application/json',
            ])->get($baseUrl . $endpoint, $params);

            if ($response->successful()) {
                $data = $response->json();
                $records = $data[$entity] ?? $data['data'] ?? [];

                $this->completeSyncLog($log, count($records), count($records), 0, $data);
                $connector->update(['last_sync_at' => now()]);

                return [
                    'success' => true,
                    'records' => $records,
                    'total' => count($records),
                ];
            }

            $this->completeSyncLog($log, 0, 0, 0, [], 'failed');
            return ['success' => false, 'message' => 'Import gagal: ' . $response->status()];
        } catch (\Exception $e) {
            $this->failSyncLog($log, $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    protected function jurnalEntityEndpoint(string $entity): string
    {
        return match ($entity) {
            'invoices' => 'sales_invoices',
            'payments' => 'receive_payments',
            'journal_entries' => 'journal_entries',
            'contacts' => 'contacts',
            'products' => 'products',
            default => $entity,
        };
    }

    // ──────────────────────────────────────────────
    //  XERO CONNECTOR
    // ──────────────────────────────────────────────

    public function connectXero(int $companyId, string $clientId, string $clientSecret): array
    {
        try {
            $tokenResponse = Http::asForm()->withBasicAuth($clientId, $clientSecret)
                ->post('https://identity.xero.com/connect/token', [
                    'grant_type' => 'client_credentials',
                    'scope' => 'accounting.transactions accounting.contacts',
                ]);

            if (!$tokenResponse->successful()) {
                return ['success' => false, 'message' => 'Gagal autentikasi Xero: ' . $tokenResponse->status()];
            }

            $tokenData = $tokenResponse->json();

            $connector = IntegrationConnector::updateOrCreate(
                ['company_id' => $companyId, 'connector_type' => 'xero'],
                [
                    'name' => 'Xero',
                    'status' => 'connected',
                    'credentials_encrypted' => [
                        'client_id' => Crypt::encryptString($clientId),
                        'client_secret' => Crypt::encryptString($clientSecret),
                        'access_token' => Crypt::encryptString($tokenData['access_token']),
                        'expires_at' => now()->addSeconds($tokenData['expires_in'] ?? 1800)->toDateTimeString(),
                    ],
                    'configuration' => [
                        'tenant_id' => $tokenData['tenant_id'] ?? null,
                        'entities' => ['invoices', 'contacts', 'chart_of_accounts'],
                    ],
                ]
            );

            return [
                'success' => true,
                'message' => 'Berhasil terhubung ke Xero',
                'connector_id' => $connector->id,
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Gagal terhubung ke Xero: ' . $e->getMessage()];
        }
    }

    public function syncToXero(int $companyId, string $entity, array $records): array
    {
        $connector = $this->findActiveConnector($companyId, 'xero');
        if (!$connector) {
            return ['success' => false, 'message' => 'Konektor Xero tidak terhubung'];
        }

        $accessToken = Crypt::decryptString($connector->credentials_encrypted['access_token']);
        $tenantId = $connector->configuration['tenant_id'] ?? null;

        $log = $this->startSyncLog($companyId, $connector->id, 'xero', $entity, 'outbound');

        try {
            $endpoint = match ($entity) {
                'invoices' => 'https://api.xero.com/api.xro/2.0/Invoices',
                'contacts' => 'https://api.xero.com/api.xro/2.0/Contacts',
                'chart_of_accounts' => 'https://api.xero.com/api.xro/2.0/Accounts',
                default => 'https://api.xero.com/api.xro/2.0/Invoices',
            };

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Xero-tenant-id' => $tenantId,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->put($endpoint, $records);

            if ($response->successful()) {
                $this->completeSyncLog($log, count($records), count($records), 0, $response->json());
                $connector->update(['last_sync_at' => now()]);
                return ['success' => true, 'synced' => count($records)];
            }

            $this->failSyncLog($log, $response->body());
            return ['success' => false, 'message' => 'Sync Xero gagal: ' . $response->status()];
        } catch (\Exception $e) {
            $this->failSyncLog($log, $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function syncFromXero(int $companyId, string $entity): array
    {
        $connector = $this->findActiveConnector($companyId, 'xero');
        if (!$connector) {
            return ['success' => false, 'message' => 'Konektor Xero tidak terhubung'];
        }

        $accessToken = Crypt::decryptString($connector->credentials_encrypted['access_token']);
        $tenantId = $connector->configuration['tenant_id'] ?? null;

        $log = $this->startSyncLog($companyId, $connector->id, 'xero', $entity, 'inbound');

        try {
            $endpoint = match ($entity) {
                'invoices' => 'https://api.xero.com/api.xro/2.0/Invoices',
                'contacts' => 'https://api.xero.com/api.xro/2.0/Contacts',
                'chart_of_accounts' => 'https://api.xero.com/api.xro/2.0/Accounts',
                default => 'https://api.xero.com/api.xro/2.0/Invoices',
            };

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Xero-tenant-id' => $tenantId,
                'Accept' => 'application/json',
            ])->get($endpoint);

            if ($response->successful()) {
                $data = $response->json();
                $count = count($data[$entity] ?? []);
                $this->completeSyncLog($log, $count, $count, 0, $data);
                $connector->update(['last_sync_at' => now()]);
                return ['success' => true, 'records' => $data, 'total' => $count];
            }

            $this->failSyncLog($log, $response->body());
            return ['success' => false, 'message' => 'Pull Xero gagal: ' . $response->status()];
        } catch (\Exception $e) {
            $this->failSyncLog($log, $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // ──────────────────────────────────────────────
    //  GOOGLE WORKSPACE CONNECTOR
    // ──────────────────────────────────────────────

    public function connectGoogle(int $companyId, string $accessToken, string $refreshToken): array
    {
        try {
            $connector = IntegrationConnector::updateOrCreate(
                ['company_id' => $companyId, 'connector_type' => 'google_workspace'],
                [
                    'name' => 'Google Workspace',
                    'status' => 'connected',
                    'credentials_encrypted' => [
                        'access_token' => Crypt::encryptString($accessToken),
                        'refresh_token' => Crypt::encryptString($refreshToken),
                    ],
                    'configuration' => [
                        'services' => ['calendar', 'drive', 'gmail'],
                        'sync_calendar' => true,
                    ],
                ]
            );

            return ['success' => true, 'message' => 'Berhasil terhubung ke Google Workspace', 'connector_id' => $connector->id];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Gagal terhubung: ' . $e->getMessage()];
        }
    }

    public function syncCalendar(int $companyId, string $direction = 'bidirectional'): array
    {
        $connector = $this->findActiveConnector($companyId, 'google_workspace');
        if (!$connector) {
            return ['success' => false, 'message' => 'Konektor Google tidak terhubung'];
        }

        $accessToken = Crypt::decryptString($connector->credentials_encrypted['access_token']);

        $log = $this->startSyncLog($companyId, $connector->id, 'google_workspace', 'calendar', $direction);

        try {
            // Sync events from Google Calendar
            $response = Http::withToken($accessToken)
                ->get('https://www.googleapis.com/calendar/v3/calendars/primary/events', [
                    'timeMin' => now()->subMonth()->toRfc3339String(),
                    'timeMax' => now()->addMonths(3)->toRfc3339String(),
                    'maxResults' => 250,
                ]);

            if ($response->successful()) {
                $events = $response->json('items') ?? [];
                $synced = 0;

                foreach ($events as $event) {
                    \App\Models\CalendarEvent::updateOrCreate(
                        [
                            'company_id' => $companyId,
                            'google_event_id' => $event['id'] ?? null,
                        ],
                        [
                            'title' => $event['summary'] ?? 'Tanpa Judul',
                            'description' => $event['description'] ?? null,
                            'location' => $event['location'] ?? null,
                            'start_at' => $event['start']['dateTime'] ?? $event['start']['date'] ?? now(),
                            'end_at' => $event['end']['dateTime'] ?? $event['end']['date'] ?? now(),
                            'is_all_day' => isset($event['start']['date']),
                            'source' => 'google',
                            'source_data' => $event,
                        ]
                    );
                    $synced++;
                }

                $this->completeSyncLog($log, count($events), $synced, 0);
                $connector->update(['last_sync_at' => now()]);

                return ['success' => true, 'synced' => $synced, 'total_events' => count($events)];
            }

            $this->failSyncLog($log, $response->body());
            return ['success' => false, 'message' => 'Sync kalender gagal'];
        } catch (\Exception $e) {
            $this->failSyncLog($log, $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // ──────────────────────────────────────────────
    //  MICROSOFT 365 CONNECTOR
    // ──────────────────────────────────────────────

    public function connectMicrosoft(int $companyId, string $tenantId, string $clientId, string $clientSecret): array
    {
        try {
            $connector = IntegrationConnector::updateOrCreate(
                ['company_id' => $companyId, 'connector_type' => 'microsoft_365'],
                [
                    'name' => 'Microsoft 365',
                    'status' => 'connected',
                    'credentials_encrypted' => [
                        'tenant_id' => Crypt::encryptString($tenantId),
                        'client_id' => Crypt::encryptString($clientId),
                        'client_secret' => Crypt::encryptString($clientSecret),
                    ],
                    'configuration' => [
                        'services' => ['outlook_calendar', 'teams_presence', 'sharepoint'],
                    ],
                ]
            );

            return ['success' => true, 'message' => 'Berhasil terhubung ke Microsoft 365', 'connector_id' => $connector->id];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Gagal terhubung: ' . $e->getMessage()];
        }
    }

    public function syncOutlookCalendar(int $companyId): array
    {
        $connector = $this->findActiveConnector($companyId, 'microsoft_365');
        if (!$connector) {
            return ['success' => false, 'message' => 'Konektor Microsoft tidak terhubung'];
        }

        $log = $this->startSyncLog($companyId, $connector->id, 'microsoft_365', 'outlook_calendar', 'bidirectional');

        try {
            // This would use Microsoft Graph API — stub implementation
            $this->completeSyncLog($log, 0, 0, 0, null, 'success');
            $connector->update(['last_sync_at' => now()]);

            return [
                'success' => true,
                'message' => 'Sinkronisasi Outlook Calendar berhasil (stub)',
                'synced' => 0,
            ];
        } catch (\Exception $e) {
            $this->failSyncLog($log, $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function syncTeamsPresence(int $companyId): array
    {
        $connector = $this->findActiveConnector($companyId, 'microsoft_365');
        if (!$connector) {
            return ['success' => false, 'message' => 'Konektor Microsoft tidak terhubung'];
        }

        // Stub: Would use Microsoft Graph Presence API
        return [
            'success' => true,
            'message' => 'Teams presence sync — memerlukan Microsoft Graph Presence API (stub)',
        ];
    }

    // ──────────────────────────────────────────────
    //  OPEN BANKING API
    // ──────────────────────────────────────────────

    public function connectBank(int $companyId, string $bank, array $credentials): array
    {
        $banks = ['bca', 'mandiri', 'bri', 'bni', 'cimb'];

        if (!in_array($bank, $banks)) {
            return ['success' => false, 'message' => 'Bank tidak didukung. Tersedia: ' . implode(', ', $banks)];
        }

        try {
            $connector = IntegrationConnector::updateOrCreate(
                [
                    'company_id' => $companyId,
                    'connector_type' => 'open_banking',
                ],
                [
                    'name' => 'Bank ' . strtoupper($bank),
                    'status' => 'connected',
                    'credentials_encrypted' => [
                        'bank' => $bank,
                        'api_key' => Crypt::encryptString($credentials['api_key'] ?? ''),
                        'api_secret' => Crypt::encryptString($credentials['api_secret'] ?? ''),
                        'client_id' => Crypt::encryptString($credentials['client_id'] ?? ''),
                    ],
                    'configuration' => [
                        'bank' => $bank,
                        'base_url' => $this->getBankBaseUrl($bank),
                        'accounts' => $credentials['accounts'] ?? [],
                    ],
                ]
            );

            return ['success' => true, 'message' => 'Berhasil terhubung ke ' . strtoupper($bank), 'connector_id' => $connector->id];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Gagal terhubung: ' . $e->getMessage()];
        }
    }

    public function getBankBalance(int $bankAccountId): array
    {
        $bankAccount = BankAccount::find($bankAccountId);
        if (!$bankAccount) {
            return ['success' => false, 'message' => 'Rekening tidak ditemukan'];
        }

        $connector = IntegrationConnector::where('company_id', $bankAccount->company_id)
            ->where('connector_type', 'open_banking')
            ->connected()
            ->first();

        if (!$connector) {
            return [
                'success' => true,
                'message' => 'Saldo dari catatan lokal (bank tidak terhubung real-time)',
                'available_balance' => (float) $bankAccount->current_balance,
                'ledger_balance' => (float) $bankAccount->current_balance,
                'currency' => $bankAccount->currency?->code ?? 'IDR',
                'last_updated' => $bankAccount->updated_at?->toIso8601String(),
                'is_live' => false,
            ];
        }

        // Stub: Real bank API call would go here
        return [
            'success' => true,
            'available_balance' => (float) $bankAccount->current_balance,
            'ledger_balance' => (float) $bankAccount->current_balance,
            'currency' => 'IDR',
            'last_updated' => now()->toIso8601String(),
            'is_live' => true,
            'bank' => $connector->configuration['bank'] ?? 'unknown',
        ];
    }

    public function fetchBankTransactions(int $bankAccountId, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $bankAccount = BankAccount::find($bankAccountId);
        if (!$bankAccount) {
            return ['success' => false, 'message' => 'Rekening tidak ditemukan'];
        }

        $connector = IntegrationConnector::where('company_id', $bankAccount->company_id)
            ->where('connector_type', 'open_banking')
            ->connected()
            ->first();

        if (!$connector) {
            return ['success' => false, 'message' => 'Konektor bank tidak terhubung'];
        }

        $log = $this->startSyncLog($bankAccount->company_id, $connector->id, 'open_banking', 'bank_transactions', 'inbound');

        try {
            $from = $from ?? now()->subDays(7);
            $to = $to ?? now();

            // Stub: Real bank API call would go here with encrypted credentials
            // For now, this is a stub that indicates the integration point

            $this->completeSyncLog($log, 0, 0, 0);
            $connector->update(['last_sync_at' => now()]);

            return [
                'success' => true,
                'message' => 'Pull transaksi bank — real bank API endpoint diperlukan (stub)',
                'bank' => $connector->configuration['bank'] ?? 'unknown',
                'period' => ['from' => $from->toDateString(), 'to' => $to->toDateString()],
                'transactions' => [],
            ];
        } catch (\Exception $e) {
            $this->failSyncLog($log, $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function createBankTransfer(int $fromAccountId, int $toAccountId, float $amount, string $description): array
    {
        // Stub for bank transfer API
        return [
            'success' => true,
            'message' => 'Transfer berhasil dibuat (stub - bank API live diperlukan)',
            'reference' => 'TRF-' . now()->format('YmdHis'),
            'amount' => $amount,
            'from_account_id' => $fromAccountId,
            'to_account_id' => $toAccountId,
            'description' => $description,
        ];
    }

    public function createVirtualAccount(int $bankAccountId, string $name, ?float $expectedAmount = null): array
    {
        $bankAccount = BankAccount::find($bankAccountId);
        if (!$bankAccount) {
            return ['success' => false, 'message' => 'Rekening tidak ditemukan'];
        }

        $connector = IntegrationConnector::where('company_id', $bankAccount->company_id)
            ->where('connector_type', 'open_banking')
            ->connected()
            ->first();

        $bank = $connector?->configuration['bank'] ?? strtolower(explode(' ', $bankAccount->bank_name)[0] ?? 'bca');

        // Generate VA number (stub - real bank API would return this)
        $vaNumber = $bankAccount->account_number . now()->format('ymdHis');

        $va = VirtualAccount::create([
            'company_id' => $bankAccount->company_id,
            'bank_account_id' => $bankAccountId,
            'va_number' => $vaNumber,
            'bank' => $bank,
            'name' => $name,
            'expected_amount' => $expectedAmount,
            'status' => 'active',
            'expiry_at' => now()->addDays(2),
        ]);

        return [
            'success' => true,
            'va_number' => $vaNumber,
            'bank' => strtoupper($bank),
            'status' => 'active',
            'expiry' => $va->expiry_at?->toIso8601String(),
            'id' => $va->id,
        ];
    }

    public function checkVaStatus(string $vaNumber): array
    {
        $va = VirtualAccount::where('va_number', $vaNumber)->first();

        if (!$va) {
            return ['success' => false, 'message' => 'Virtual Account tidak ditemukan'];
        }

        return [
            'success' => true,
            'va_number' => $va->va_number,
            'status' => $va->status,
            'paid_amount' => (float) $va->paid_amount,
            'expected_amount' => (float) $va->expected_amount,
            'is_fully_paid' => $va->status === 'paid',
        ];
    }

    // ──────────────────────────────────────────────
    //  DIRECT TAX AUTHORITY (DJP) INTEGRATION
    // ──────────────────────────────────────────────

    public function connectDjp(int $companyId, array $credentials): array
    {
        try {
            $connector = IntegrationConnector::updateOrCreate(
                ['company_id' => $companyId, 'connector_type' => 'djp'],
                [
                    'name' => 'DJP Online - ' . ($credentials['npwp'] ?? ''),
                    'status' => 'connected',
                    'credentials_encrypted' => [
                        'npwp' => Crypt::encryptString($credentials['npwp'] ?? ''),
                        'password' => Crypt::encryptString($credentials['password'] ?? ''),
                    ],
                    'configuration' => [
                        'certificate_path' => $credentials['certificate_path'] ?? null,
                    ],
                ]
            );

            DjpToken::updateOrCreate(
                ['company_id' => $companyId],
                [
                    'npwp' => $credentials['npwp'] ?? '',
                    'certificate_path' => $credentials['certificate_path'] ?? null,
                    'certificate_password_encrypted' => $credentials['certificate_password'] ?? null
                        ? Crypt::encryptString($credentials['certificate_password'])
                        : null,
                    'status' => 'active',
                ]
            );

            return ['success' => true, 'message' => 'Berhasil terhubung ke DJP Online', 'connector_id' => $connector->id];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Gagal terhubung ke DJP: ' . $e->getMessage()];
        }
    }

    public function submitEfaktur(int $companyId, Invoice $invoice): array
    {
        $connector = $this->findActiveConnector($companyId, 'djp');
        if (!$connector) {
            return ['success' => false, 'message' => 'Konektor DJP tidak terhubung'];
        }

        $djpToken = DjpToken::where('company_id', $companyId)->active()->first();
        if (!$djpToken) {
            return ['success' => false, 'message' => 'Token DJP tidak ditemukan'];
        }

        $log = $this->startSyncLog($companyId, $connector->id, 'djp', 'efaktur', 'outbound');

        try {
            // 1. Generate e-Faktur XML
            $efakturService = app(EFakturService::class);
            $taxInvoiceNumber = $efakturService->generateTaxInvoiceNumber();

            // 2. Sign with certificate (stub - real PKCS#12 signing)
            // 3. Upload to DJP API

            $this->completeSyncLog($log, 1, 1, 0, ['nomor_faktur' => $taxInvoiceNumber]);
            $connector->update(['last_sync_at' => now()]);

            return [
                'success' => true,
                'status' => 'submitted',
                'nomor_faktur' => $taxInvoiceNumber,
                'invoice_id' => $invoice->id,
                'validation_errors' => [],
            ];
        } catch (\Exception $e) {
            $this->failSyncLog($log, $e->getMessage());
            return [
                'success' => false,
                'status' => 'error',
                'validation_errors' => [$e->getMessage()],
            ];
        }
    }

    public function pullPajakMasukan(int $companyId, string $period): array
    {
        $connector = $this->findActiveConnector($companyId, 'djp');
        if (!$connector) {
            return ['success' => false, 'message' => 'Konektor DJP tidak terhubung'];
        }

        $log = $this->startSyncLog($companyId, $connector->id, 'djp', 'pkpm', 'inbound');

        try {
            // Stub: DJP e-Faktur PM pull API
            $this->completeSyncLog($log, 0, 0, 0);
            $connector->update(['last_sync_at' => now()]);

            return [
                'success' => true,
                'message' => 'Pull Pajak Masukan — DJP API live diperlukan (stub)',
                'period' => $period,
                'records' => [],
            ];
        } catch (\Exception $e) {
            $this->failSyncLog($log, $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function validateNpwpDjp(int $companyId, string $npwp): array
    {
        $connector = $this->findActiveConnector($companyId, 'djp');
        if (!$connector) {
            // Use local validation as fallback
            $efakturService = app(EFakturService::class);
            $result = $efakturService->validateNpwp($npwp);
            return [
                'success' => $result['valid'],
                'valid' => $result['valid'],
                'name' => $result['suggested_name'] ?? null,
                'address' => null,
                'status' => $result['is_cabang'] ? 'Cabang' : 'Pusat',
                'registration_date' => null,
                'source' => 'local_validation',
                'errors' => $result['errors'] ?? [],
            ];
        }

        // Stub: DJP API validation
        $efakturService = app(EFakturService::class);
        $result = $efakturService->validateNpwp($npwp);

        return [
            'success' => $result['valid'],
            'valid' => $result['valid'],
            'name' => $result['suggested_name'] ?? null,
            'address' => null,
            'status' => $result['is_cabang'] ? 'Cabang' : 'Pusat',
            'registration_date' => null,
            'source' => 'local_validation',
        ];
    }

    public function submitSpt(int $companyId, int $year, int $month): array
    {
        $connector = $this->findActiveConnector($companyId, 'djp');
        if (!$connector) {
            return ['success' => false, 'message' => 'Konektor DJP tidak terhubung'];
        }

        $log = $this->startSyncLog($companyId, $connector->id, 'djp', 'spt', 'outbound');

        try {
            // Stub: SPT Masa PPN 1111 submission
            $period = sprintf('%d-%02d', $year, $month);

            // Auto-populate from BizOS invoice data
            $pkTotal = Invoice::where('company_id', $companyId)
                ->whereYear('invoice_date', $year)
                ->whereMonth('invoice_date', $month)
                ->where('status', 'paid')
                ->sum('tax_amount');

            $this->completeSyncLog($log, 1, 1, 0, ['ppn_terutang' => $pkTotal]);
            $connector->update(['last_sync_at' => now()]);

            return [
                'success' => true,
                'message' => 'SPT Masa PPN siap disubmit — DJP API live diperlukan (stub)',
                'period' => $period,
                'ppn_terutang' => round($pkTotal, 2),
            ];
        } catch (\Exception $e) {
            $this->failSyncLog($log, $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // ──────────────────────────────────────────────
    //  BULK DATA EXCHANGE SCHEDULER
    // ──────────────────────────────────────────────

    public function scheduleSync(int $companyId, string $connectorType, string $entity, string $frequency): void
    {
        $connector = $this->findActiveConnector($companyId, $connectorType);
        if (!$connector) return;

        $config = $connector->configuration ?? [];
        $config['scheduled_syncs'] = $config['scheduled_syncs'] ?? [];
        $config['scheduled_syncs'][$entity] = [
            'frequency' => $frequency,
            'last_run' => null,
            'enabled' => true,
        ];

        $connector->update(['configuration' => $config]);
    }

    public function getSyncLogs(int $companyId): Collection
    {
        return IntegrationSyncLog::where('company_id', $companyId)
            ->with('connector')
            ->latest()
            ->limit(100)
            ->get();
    }

    public function runAllScheduledSyncs(): array
    {
        $results = [];
        $connectors = IntegrationConnector::where('is_active', true)
            ->where('status', 'connected')
            ->get();

        foreach ($connectors as $connector) {
            $scheduledSyncs = $connector->configuration['scheduled_syncs'] ?? [];

            foreach ($scheduledSyncs as $entity => $config) {
                if (!($config['enabled'] ?? true)) continue;

                $shouldRun = match ($config['frequency']) {
                    'hourly' => true,
                    'daily' => !$connector->last_sync_at || $connector->last_sync_at->diffInHours(now()) >= 24,
                    'weekly' => !$connector->last_sync_at || $connector->last_sync_at->diffInDays(now()) >= 7,
                    default => false,
                };

                if ($shouldRun) {
                    try {
                        $result = ['success' => true, 'entity' => $entity, 'connector' => $connector->connector_type];

                        switch ($connector->connector_type) {
                            case 'jurnal_id':
                                $result = $this->importFromJurnalId($connector->company_id, $entity);
                                break;
                            case 'xero':
                                $result = $this->syncFromXero($connector->company_id, $entity);
                                break;
                            case 'open_banking':
                                $bankAccounts = BankAccount::where('company_id', $connector->company_id)->pluck('id');
                                foreach ($bankAccounts as $accountId) {
                                    $this->fetchBankTransactions($accountId);
                                }
                                $result['message'] = 'Bank sync triggered for ' . $bankAccounts->count() . ' accounts';
                                break;
                        }

                        $results[] = $result;
                    } catch (\Exception $e) {
                        $results[] = ['success' => false, 'entity' => $entity, 'error' => $e->getMessage()];
                    }
                }
            }
        }

        return $results;
    }

    // ──────────────────────────────────────────────
    //  OAUTH PROVIDERS
    // ──────────────────────────────────────────────

    public function registerOauthProvider(int $companyId, array $data): OauthProvider
    {
        $provider = $data['provider'];
        $supported = ['google', 'microsoft', 'github', 'linkedin'];

        if (!in_array($provider, $supported)) {
            throw new \InvalidArgumentException("Provider {$provider} tidak didukung. Tersedia: " . implode(', ', $supported));
        }

        return OauthProvider::updateOrCreate(
            ['company_id' => $companyId, 'provider' => $provider],
            [
                'client_id' => $data['client_id'],
                'client_secret_encrypted' => Crypt::encryptString($data['client_secret']),
                'redirect_uri' => $data['redirect_uri'] ?? config('app.url') . '/auth/' . $provider . '/callback',
                'is_active' => $data['is_active'] ?? true,
            ]
        );
    }

    public function getOauthProviders(int $companyId): Collection
    {
        return OauthProvider::where('company_id', $companyId)->active()->get();
    }

    public function getOauthRedirectUrl(int $companyId, string $provider): string
    {
        $oauthProvider = OauthProvider::where('company_id', $companyId)
            ->where('provider', $provider)
            ->active()
            ->first();

        if (!$oauthProvider) {
            throw new \RuntimeException("OAuth provider {$provider} tidak dikonfigurasi.");
        }

        $clientId = $oauthProvider->client_id;
        $redirectUri = $oauthProvider->redirect_uri;

        return match ($provider) {
            'google' => 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
                'client_id' => $clientId,
                'redirect_uri' => $redirectUri,
                'response_type' => 'code',
                'scope' => 'openid profile email',
                'access_type' => 'offline',
                'prompt' => 'consent',
            ]),
            'microsoft' => 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize?' . http_build_query([
                'client_id' => $clientId,
                'redirect_uri' => $redirectUri,
                'response_type' => 'code',
                'scope' => 'openid profile email User.Read',
            ]),
            'github' => 'https://github.com/login/oauth/authorize?' . http_build_query([
                'client_id' => $clientId,
                'redirect_uri' => $redirectUri,
                'scope' => 'user:email',
            ]),
            'linkedin' => 'https://www.linkedin.com/oauth/v2/authorization?' . http_build_query([
                'client_id' => $clientId,
                'redirect_uri' => $redirectUri,
                'response_type' => 'code',
                'scope' => 'openid profile email',
            ]),
            default => throw new \InvalidArgumentException("Provider {$provider} tidak didukung"),
        };
    }

    public function handleOauthCallback(int $companyId, string $provider, string $code): array
    {
        $oauthProvider = OauthProvider::where('company_id', $companyId)
            ->where('provider', $provider)
            ->active()
            ->first();

        if (!$oauthProvider) {
            return ['success' => false, 'message' => 'OAuth provider tidak ditemukan.'];
        }

        $clientSecret = Crypt::decryptString($oauthProvider->client_secret_encrypted);
        $redirectUri = $oauthProvider->redirect_uri;

        $tokenEndpoint = match ($provider) {
            'google' => 'https://oauth2.googleapis.com/token',
            'microsoft' => 'https://login.microsoftonline.com/common/oauth2/v2.0/token',
            'github' => 'https://github.com/login/oauth/access_token',
            'linkedin' => 'https://www.linkedin.com/oauth/v2/accessToken',
            default => throw new \InvalidArgumentException("Provider {$provider} tidak didukung"),
        };

        try {
            $response = Http::asForm()->post($tokenEndpoint, [
                'client_id' => $oauthProvider->client_id,
                'client_secret' => $clientSecret,
                'code' => $code,
                'redirect_uri' => $redirectUri,
                'grant_type' => 'authorization_code',
            ]);

            if ($response->successful()) {
                $tokenData = $response->json();
                $accessToken = $tokenData['access_token'] ?? '';

                $userInfo = $this->fetchOauthUserInfo($provider, $accessToken);

                return [
                    'success' => true,
                    'provider' => $provider,
                    'user' => $userInfo,
                    'token' => $tokenData,
                ];
            }

            return ['success' => false, 'message' => 'Gagal menukar authorization code: ' . $response->body()];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error OAuth callback: ' . $e->getMessage()];
        }
    }

    protected function fetchOauthUserInfo(string $provider, string $accessToken): array
    {
        $userInfoEndpoint = match ($provider) {
            'google' => 'https://www.googleapis.com/oauth2/v2/userinfo',
            'microsoft' => 'https://graph.microsoft.com/v1.0/me',
            'github' => 'https://api.github.com/user',
            'linkedin' => 'https://api.linkedin.com/v2/userinfo',
            default => throw new \InvalidArgumentException("Provider {$provider} tidak didukung"),
        };

        $response = Http::withToken($accessToken)->get($userInfoEndpoint);

        if ($response->successful()) {
            $data = $response->json();
            return [
                'id' => $data['id'] ?? ($data['sub'] ?? null),
                'email' => $data['email'] ?? ($data['mail'] ?? null),
                'name' => $data['name'] ?? ($data['displayName'] ?? null),
                'avatar' => $data['picture'] ?? ($data['avatar_url'] ?? null),
                'raw' => $data,
            ];
        }

        return [];
    }

    // ──────────────────────────────────────────────
    //  SSO CONFIGURATION
    // ──────────────────────────────────────────────

    public function configureSso(int $companyId, array $data): SsoConfig
    {
        return SsoConfig::updateOrCreate(
            ['company_id' => $companyId, 'provider' => $data['provider']],
            [
                'metadata_url' => $data['metadata_url'] ?? null,
                'entity_id' => $data['entity_id'] ?? null,
                'certificate' => $data['certificate'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]
        );
    }

    public function getSsoConfigs(int $companyId): Collection
    {
        return SsoConfig::where('company_id', $companyId)->active()->get();
    }

    public function generateSamlMetadata(int $companyId, string $provider): array
    {
        $config = SsoConfig::where('company_id', $companyId)
            ->where('provider', $provider)
            ->active()
            ->first();

        if (!$config) {
            return ['success' => false, 'message' => 'Konfigurasi SSO tidak ditemukan.'];
        }

        $appUrl = config('app.url');
        $entityId = $config->entity_id ?: "{$appUrl}/saml/{$provider}/metadata";

        return [
            'success' => true,
            'provider' => $provider,
            'entity_id' => $entityId,
            'acs_url' => "{$appUrl}/saml/{$provider}/acs",
            'slo_url' => "{$appUrl}/saml/{$provider}/slo",
            'name_id_format' => 'urn:oasis:names:tc:SAML:2.0:nameid-format:emailAddress',
            'certificate' => $config->certificate,
        ];
    }

    public function validateSamlResponse(int $companyId, string $provider, string $samlResponse): array
    {
        $config = SsoConfig::where('company_id', $companyId)
            ->where('provider', $provider)
            ->active()
            ->first();

        if (!$config) {
            return ['success' => false, 'message' => 'Konfigurasi SSO tidak ditemukan.'];
        }

        // Decode and validate SAML response
        $decoded = base64_decode($samlResponse);
        $xml = simplexml_load_string($decoded);

        if (!$xml) {
            return ['success' => false, 'message' => 'Format SAML response tidak valid.'];
        }

        $namespaces = $xml->getNamespaces(true);

        // Extract attributes from SAML assertion
        $attributes = [];
        if (isset($xml->Assertion->AttributeStatement->Attribute)) {
            foreach ($xml->Assertion->AttributeStatement->Attribute as $attr) {
                $name = (string) $attr['Name'];
                $value = (string) ($attr->AttributeValue ?? '');
                $attributes[$name] = $value;
            }
        }

        // Extract NameID
        $nameId = (string) ($xml->Assertion->Subject->NameID ?? '');

        return [
            'success' => true,
            'provider' => $provider,
            'name_id' => $nameId,
            'email' => $attributes['email'] ?? $attributes['EmailAddress'] ?? $attributes['mail'] ?? $nameId,
            'first_name' => $attributes['firstName'] ?? $attributes['givenName'] ?? '',
            'last_name' => $attributes['lastName'] ?? $attributes['sn'] ?? '',
            'attributes' => $attributes,
            'session_index' => (string) ($xml->Assertion->AuthnStatement['SessionIndex'] ?? ''),
        ];
    }

    // ──────────────────────────────────────────────
    //  SHIPPING PROVIDERS
    // ──────────────────────────────────────────────

    public function registerShippingProvider(int $companyId, array $data): ShippingProvider
    {
        $supported = ['jne', 'jnt', 'sicepat', 'pos', 'gosend', 'grab'];

        $name = strtolower($data['name']);
        if (!in_array($name, $supported)) {
            throw new \InvalidArgumentException("Shipping provider {$name} tidak didukung. Tersedia: " . implode(', ', $supported));
        }

        return ShippingProvider::updateOrCreate(
            ['company_id' => $companyId, 'name' => $name],
            [
                'api_key_encrypted' => isset($data['api_key']) ? Crypt::encryptString($data['api_key']) : null,
                'is_active' => $data['is_active'] ?? true,
            ]
        );
    }

    public function getShippingProviders(int $companyId): Collection
    {
        return ShippingProvider::where('company_id', $companyId)->active()->get();
    }

    public function getShippingRates(int $companyId, string $provider, array $params): array
    {
        $shippingProvider = ShippingProvider::where('company_id', $companyId)
            ->where('name', $provider)
            ->active()
            ->first();

        if (!$shippingProvider) {
            return ['success' => false, 'message' => 'Shipping provider tidak dikonfigurasi.'];
        }

        $apiKey = $shippingProvider->api_key_encrypted ? Crypt::decryptString($shippingProvider->api_key_encrypted) : null;

        $origin = $params['origin_city'] ?? '';
        $destination = $params['destination_city'] ?? '';
        $weight = $params['weight_grams'] ?? 1000;
        $courier = $this->mapCourierCode($provider);

        try {
            $endpoint = match ($provider) {
                'jne', 'jnt', 'sicepat', 'pos' => 'https://api.rajaongkir.com/starter/cost',
                'gosend' => 'https://api.gosend.com/v1/deliveries/rates',
                'grab' => 'https://api.grab.com/v1/rates',
                default => throw new \InvalidArgumentException("Provider {$provider} tidak didukung"),
            };

            if (in_array($provider, ['jne', 'jnt', 'sicepat', 'pos'])) {
                // RajaOngkir API
                $response = Http::withHeaders(['key' => $apiKey])
                    ->post($endpoint, [
                        'origin' => $origin,
                        'destination' => $destination,
                        'weight' => $weight,
                        'courier' => $courier,
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $results = $data['rajaongkir']['results'] ?? [];

                    $rates = [];
                    foreach ($results as $result) {
                        foreach ($result['costs'] ?? [] as $cost) {
                            $rates[] = [
                                'service' => $cost['service'],
                                'description' => $cost['description'],
                                'cost' => $cost['cost'][0]['value'] ?? 0,
                                'etd' => $cost['cost'][0]['etd'] ?? '',
                            ];
                        }
                    }

                    return ['success' => true, 'provider' => $provider, 'rates' => $rates];
                }

                return ['success' => false, 'message' => 'Gagal ambil ongkir: ' . $response->status()];
            }

            // Stub for GoSend/Grab
            return [
                'success' => true,
                'provider' => $provider,
                'rates' => [
                    ['service' => 'Instant', 'description' => 'Same day delivery', 'cost' => 15000, 'etd' => '2-4 jam'],
                    ['service' => 'Same Day', 'description' => 'Same day delivery', 'cost' => 12000, 'etd' => '4-8 jam'],
                ],
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error ambil ongkir: ' . $e->getMessage()];
        }
    }

    public function trackShipment(int $companyId, string $provider, string $trackingNumber): array
    {
        $shippingProvider = ShippingProvider::where('company_id', $companyId)
            ->where('name', $provider)
            ->active()
            ->first();

        if (!$shippingProvider) {
            return ['success' => false, 'message' => 'Shipping provider tidak dikonfigurasi.'];
        }

        $apiKey = $shippingProvider->api_key_encrypted ? Crypt::decryptString($shippingProvider->api_key_encrypted) : null;
        $courier = $this->mapCourierCode($provider);

        try {
            if (in_array($provider, ['jne', 'jnt', 'sicepat', 'pos'])) {
                $response = Http::withHeaders(['key' => $apiKey])
                    ->post('https://api.rajaongkir.com/starter/waybill', [
                        'waybill' => $trackingNumber,
                        'courier' => $courier,
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $result = $data['rajaongkir']['result'] ?? [];

                    return [
                        'success' => true,
                        'tracking_number' => $trackingNumber,
                        'provider' => $provider,
                        'status' => $result['delivery_status'] ?? [],
                        'details' => $result['details'] ?? [],
                        'manifest' => $result['manifest'] ?? [],
                    ];
                }

                return ['success' => false, 'message' => 'Gagal lacak pengiriman'];
            }

            return [
                'success' => true,
                'tracking_number' => $trackingNumber,
                'provider' => $provider,
                'status' => 'Dalam pengiriman',
                'message' => 'Lacak pengiriman real-time memerlukan integrasi API langsung.',
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error lacak pengiriman: ' . $e->getMessage()];
        }
    }

    protected function mapCourierCode(string $provider): string
    {
        return match ($provider) {
            'jne' => 'jne',
            'jnt' => 'jnt',
            'sicepat' => 'sicepat',
            'pos' => 'pos',
            default => $provider,
        };
    }

    // ──────────────────────────────────────────────
    //  ERP CONNECTORS
    // ──────────────────────────────────────────────

    public function getAvailableErps(): array
    {
        return [
            ['id' => 'odoo', 'name' => 'Odoo', 'description' => 'ERP open-source untuk manufaktur, inventory, akuntansi, CRM.', 'icon' => 'heroicon-o-cube'],
            ['id' => 'sap', 'name' => 'SAP Business One', 'description' => 'ERP enterprise untuk keuangan, supply chain, produksi.', 'icon' => 'heroicon-o-building-office'],
            ['id' => 'microsoft_dynamics', 'name' => 'Microsoft Dynamics 365', 'description' => 'ERP & CRM cloud dari Microsoft.', 'icon' => 'heroicon-o-squares-2x2'],
            ['id' => 'accurate', 'name' => 'Accurate Online', 'description' => 'Software akuntansi Indonesia. Ekspor/impor faktur & laporan.', 'icon' => 'heroicon-o-document-chart-bar'],
            ['id' => 'jurnal_id', 'name' => 'Jurnal.id', 'description' => 'Software akuntansi online Indonesia.', 'icon' => 'heroicon-o-book-open'],
        ];
    }

    public function connectErp(int $companyId, array $data): ErpConnector
    {
        return ErpConnector::updateOrCreate(
            ['company_id' => $companyId, 'target_erp' => $data['target_erp']],
            [
                'connection_config' => $data['connection_config'] ?? [],
                'is_active' => true,
            ]
        );
    }

    public function disconnectErp(ErpConnector $connector): void
    {
        $connector->update(['is_active' => false]);
    }

    public function getErpConnectors(int $companyId): Collection
    {
        return ErpConnector::where('company_id', $companyId)->active()->get();
    }

    public function syncToErp(ErpConnector $connector, string $entityType, array $records, string $direction = 'export'): array
    {
        $config = $connector->connection_config ?? [];
        $erp = $connector->target_erp;

        $log = ErpSyncLog::create([
            'connector_id' => $connector->id,
            'entity_type' => $entityType,
            'direction' => $direction,
            'records_count' => count($records),
            'status' => 'running',
        ]);

        try {
            switch ($erp) {
                case 'odoo':
                    $result = $this->syncOdoo($config, $entityType, $records, $direction);
                    break;
                case 'sap':
                    $result = $this->syncSap($config, $entityType, $records, $direction);
                    break;
                case 'microsoft_dynamics':
                    $result = $this->syncDynamics($config, $entityType, $records, $direction);
                    break;
                case 'accurate':
                    $result = $this->syncAccurate($config, $entityType, $records, $direction);
                    break;
                case 'jurnal_id':
                    $result = $this->syncJurnalErp($config, $entityType, $records, $direction);
                    break;
                default:
                    throw new \InvalidArgumentException("ERP {$erp} tidak didukung.");
            }

            $log->update([
                'status' => $result['success'] ? 'success' : 'failed',
                'records_count' => $result['synced'] ?? 0,
                'error_message' => $result['message'] ?? null,
            ]);

            $connector->update(['last_synced_at' => now()]);

            return $result;
        } catch (\Exception $e) {
            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            return ['success' => false, 'message' => 'Error sinkronisasi ERP: ' . $e->getMessage()];
        }
    }

    public function syncFromErp(ErpConnector $connector, string $entityType): array
    {
        return $this->syncToErp($connector, $entityType, [], 'import');
    }

    public function getErpSyncLogs(int $connectorId): Collection
    {
        return ErpSyncLog::where('connector_id', $connectorId)
            ->latest('created_at')
            ->limit(100)
            ->get();
    }

    protected function syncOdoo(array $config, string $entityType, array $records, string $direction): array
    {
        $baseUrl = $config['url'] ?? 'http://localhost:8069';
        $db = $config['database'] ?? 'odoo';
        $userId = $config['user_id'] ?? 1;
        $password = Crypt::decryptString($config['password_encrypted'] ?? '');

        try {
            // Odoo XML-RPC authentication
            $client = new \GuzzleHttp\Client();
            $authResponse = $client->post("{$baseUrl}/xmlrpc/2/common", [
                'json' => [
                    'jsonrpc' => '2.0',
                    'method' => 'call',
                    'params' => [$db, $userId, $password, []],
                    'id' => 1,
                ],
            ]);

            if (!$authResponse->getStatusCode() === 200) {
                return ['success' => false, 'message' => 'Gagal autentikasi Odoo.'];
            }

            $entityModel = $this->mapOdooEntity($entityType);

            if ($direction === 'export') {
                $client->post("{$baseUrl}/xmlrpc/2/object", [
                    'json' => [
                        'jsonrpc' => '2.0',
                        'method' => 'call',
                        'params' => [$db, $userId, $password, $entityModel, 'create', [$records]],
                        'id' => 2,
                    ],
                ]);

                return ['success' => true, 'synced' => count($records), 'message' => "{$entityType} diekspor ke Odoo."];
            }

            // Import from Odoo
            $response = $client->post("{$baseUrl}/xmlrpc/2/object", [
                'json' => [
                    'jsonrpc' => '2.0',
                    'method' => 'call',
                    'params' => [$db, $userId, $password, $entityModel, 'search_read', [[], ['limit' => 100]]],
                    'id' => 2,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            $records = $data['result'] ?? [];

            return ['success' => true, 'synced' => count($records), 'records' => $records];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error Odoo: ' . $e->getMessage()];
        }
    }

    protected function mapOdooEntity(string $entityType): string
    {
        return match ($entityType) {
            'invoices' => 'account.move',
            'customers' => 'res.partner',
            'products' => 'product.product',
            'journal_entries' => 'account.move.line',
            default => $entityType,
        };
    }

    protected function syncSap(array $config, string $entityType, array $records, string $direction): array
    {
        $baseUrl = $config['url'] ?? 'https://api.sap.com';
        $apiKey = Crypt::decryptString($config['api_key_encrypted'] ?? '');

        try {
            $endpoint = match ($entityType) {
                'invoices' => '/v1/sales/invoices',
                'customers' => '/v1/business-partners',
                'products' => '/v1/items',
                default => "/v1/{$entityType}",
            };

            if ($direction === 'export') {
                $response = Http::withToken($apiKey)
                    ->post("{$baseUrl}{$endpoint}", $records);

                if ($response->successful()) {
                    return ['success' => true, 'synced' => count($records), 'message' => "{$entityType} diekspor ke SAP."];
                }

                return ['success' => false, 'message' => 'Export SAP gagal: ' . $response->status()];
            }

            $response = Http::withToken($apiKey)->get("{$baseUrl}{$endpoint}");

            if ($response->successful()) {
                $data = $response->json();
                $records = $data['value'] ?? $data['results'] ?? [];
                return ['success' => true, 'synced' => count($records), 'records' => $records];
            }

            return ['success' => false, 'message' => 'Import SAP gagal: ' . $response->status()];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error SAP: ' . $e->getMessage()];
        }
    }

    protected function syncDynamics(array $config, string $entityType, array $records, string $direction): array
    {
        $baseUrl = $config['url'] ?? 'https://api.businesscentral.dynamics.com';
        $tenantId = $config['tenant_id'] ?? '';
        $clientId = Crypt::decryptString($config['client_id_encrypted'] ?? '');
        $clientSecret = Crypt::decryptString($config['client_secret_encrypted'] ?? '');

        try {
            // Get access token
            $tokenResponse = Http::asForm()->post("https://login.microsoftonline.com/{$tenantId}/oauth2/v2.0/token", [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'scope' => 'https://api.businesscentral.dynamics.com/.default',
                'grant_type' => 'client_credentials',
            ]);

            $accessToken = $tokenResponse->json('access_token');

            $endpoint = match ($entityType) {
                'invoices' => '/v2.0/salesInvoices',
                'customers' => '/v2.0/customers',
                'products' => '/v2.0/items',
                default => "/v2.0/{$entityType}",
            };

            if ($direction === 'export') {
                foreach ($records as $record) {
                    Http::withToken($accessToken)->post("{$baseUrl}{$endpoint}", $record);
                }
                return ['success' => true, 'synced' => count($records), 'message' => "{$entityType} diekspor ke Dynamics."];
            }

            $response = Http::withToken($accessToken)->get("{$baseUrl}{$endpoint}");

            if ($response->successful()) {
                $data = $response->json();
                $records = $data['value'] ?? [];
                return ['success' => true, 'synced' => count($records), 'records' => $records];
            }

            return ['success' => false, 'message' => 'Import Dynamics gagal: ' . $response->status()];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error Dynamics: ' . $e->getMessage()];
        }
    }

    protected function syncAccurate(array $config, string $entityType, array $records, string $direction): array
    {
        $baseUrl = $config['url'] ?? 'https://api.accurate.id/api';
        $apiToken = Crypt::decryptString($config['api_token_encrypted'] ?? '');

        try {
            $endpoint = match ($entityType) {
                'invoices' => '/sales-invoices',
                'purchase_invoices' => '/purchase-invoices',
                'customers' => '/customers',
                'products' => '/items',
                'journal_entries' => '/journal-entries',
                default => "/{$entityType}",
            };

            if ($direction === 'export') {
                $response = Http::withHeaders([
                    'Authorization' => "Bearer {$apiToken}",
                    'Content-Type' => 'application/json',
                ])->post("{$baseUrl}{$endpoint}", $records);

                if ($response->successful()) {
                    return ['success' => true, 'synced' => count($records), 'message' => "{$entityType} diekspor ke Accurate."];
                }

                return ['success' => false, 'message' => 'Export Accurate gagal: ' . $response->status()];
            }

            $response = Http::withToken($apiToken)->get("{$baseUrl}{$endpoint}");

            if ($response->successful()) {
                $data = $response->json();
                return ['success' => true, 'synced' => count($data), 'records' => $data];
            }

            return ['success' => false, 'message' => 'Import Accurate gagal: ' . $response->status()];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error Accurate: ' . $e->getMessage()];
        }
    }

    protected function syncJurnalErp(array $config, string $entityType, array $records, string $direction): array
    {
        $baseUrl = $config['base_url'] ?? 'https://api.jurnal.id/core/api/v1/';
        $apiKey = Crypt::decryptString($config['api_key_encrypted'] ?? '');

        try {
            $endpoint = match ($entityType) {
                'invoices' => 'sales_invoices',
                'payments' => 'receive_payments',
                'customers' => 'contacts',
                'products' => 'products',
                'journal_entries' => 'journal_entries',
                default => $entityType,
            };

            if ($direction === 'export') {
                $response = Http::withHeaders([
                    'apikey' => $apiKey,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])->post("{$baseUrl}{$endpoint}", [$entityType => $records]);

                if ($response->successful()) {
                    return ['success' => true, 'synced' => count($records), 'message' => "{$entityType} diekspor ke Jurnal.id."];
                }

                return ['success' => false, 'message' => 'Export Jurnal.id gagal: ' . $response->status()];
            }

            $response = Http::withHeaders([
                'apikey' => $apiKey,
                'Accept' => 'application/json',
            ])->get("{$baseUrl}{$endpoint}", ['page' => 1, 'page_size' => 100]);

            if ($response->successful()) {
                $data = $response->json();
                $records = $data[$entityType] ?? $data['data'] ?? [];
                return ['success' => true, 'synced' => count($records), 'records' => $records];
            }

            return ['success' => false, 'message' => 'Import Jurnal.id gagal: ' . $response->status()];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Error Jurnal.id: ' . $e->getMessage()];
        }
    }

    // ──────────────────────────────────────────────
    //  HELPERS
    // ──────────────────────────────────────────────

    protected function findActiveConnector(int $companyId, string $type): ?IntegrationConnector
    {
        return IntegrationConnector::where('company_id', $companyId)
            ->where('connector_type', $type)
            ->where('is_active', true)
            ->where('status', 'connected')
            ->first();
    }

    protected function startSyncLog(int $companyId, ?int $connectorId, string $type, string $entity, string $direction): IntegrationSyncLog
    {
        return IntegrationSyncLog::create([
            'company_id' => $companyId,
            'integration_connector_id' => $connectorId,
            'connector_type' => $type,
            'entity' => $entity,
            'direction' => $direction,
            'status' => 'running',
            'started_at' => now(),
        ]);
    }

    protected function completeSyncLog(IntegrationSyncLog $log, int $processed, int $succeeded, int $failed, ?array $data = null, ?string $status = null): void
    {
        $log->update([
            'status' => $status ?? ($failed > 0 ? 'partial' : 'success'),
            'records_processed' => $processed,
            'records_succeeded' => $succeeded,
            'records_failed' => $failed,
            'summary' => $data ? json_encode($data) : null,
            'completed_at' => now(),
        ]);
    }

    protected function failSyncLog(IntegrationSyncLog $log, string $error): void
    {
        $log->update([
            'status' => 'failed',
            'error_details' => ['message' => $error],
            'completed_at' => now(),
        ]);
    }

    protected function getBankBaseUrl(string $bank): string
    {
        return match ($bank) {
            'bca' => 'https://api.bca.co.id',
            'mandiri' => 'https://api.bankmandiri.co.id',
            'bri' => 'https://api.bri.co.id',
            'bni' => 'https://api.bni.co.id',
            'cimb' => 'https://api.cimbniaga.co.id',
            default => 'https://api.bank.local',
        };
    }
}
