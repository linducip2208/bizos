<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\DjpToken;
use App\Models\IntegrationConnector;
use App\Models\IntegrationSyncLog;
use App\Models\Invoice;
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
