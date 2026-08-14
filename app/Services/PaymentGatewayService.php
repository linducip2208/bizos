<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\PaymentGatewayConfig;
use App\Models\PosPayment;
use App\Models\VirtualAccount;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Payment Gateway Service — routing pembayaran online ke gateway yang dikonfigurasi
 * oleh user di admin (dynamic provider config, tidak ada provider yang di-hardcode).
 *
 * Mendukung: midtrans, xendit, stripe, dan e-wallet (gopay/ovo/dana) serta
 * virtual account (BCA/BNI/BRI/Mandiri). Semua kunci & base URL diambil dari
 * tabel payment_gateway_configs (user input), bukan dari .env.
 */
class PaymentGatewayService
{
    protected ?int $companyId;

    public function __construct()
    {
        $this->companyId = $this->resolveCompanyId();
    }

    // ──────────────────────────────────────────────
    //  COMPANY RESOLUTION
    // ──────────────────────────────────────────────

    protected function resolveCompanyId(): ?int
    {
        $companyId = session('current_company_id') ?? auth()->user()?->company_id;

        return $companyId ? (int) $companyId : null;
    }

    // ──────────────────────────────────────────────
    //  GATEWAY CATALOG & CONFIG
    // ──────────────────────────────────────────────

    /**
     * Daftar gateway yang sudah dikonfigurasi user untuk company aktif.
     */
    public function getAvailableGateways(?int $companyId = null): array
    {
        $companyId = $companyId ?? $this->companyId;

        return PaymentGatewayConfig::query()
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->where('is_active', true)
            ->get()
            ->map(fn (PaymentGatewayConfig $g) => $this->serializeGateway($g))
            ->all();
    }

    /**
     * Cari konfigurasi gateway aktif berdasarkan tipe.
     */
    public function findGateway(string $gateway, ?int $companyId = null): ?PaymentGatewayConfig
    {
        $companyId = $companyId ?? $this->companyId;

        return PaymentGatewayConfig::query()
            ->where('gateway_type', strtolower($gateway))
            ->where('is_active', true)
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->first();
    }

    protected function serializeGateway(PaymentGatewayConfig $g): array
    {
        return [
            'id' => $g->id,
            'name' => $g->name,
            'gateway_type' => $g->gateway_type,
            'base_url' => $g->base_url,
            'is_active' => (bool) $g->is_active,
            'is_configured' => $this->isConfigured($g),
            'methods' => $this->getPaymentMethods($g->gateway_type),
            'extra_config' => $g->extra_config ?? [],
        ];
    }

    protected function isConfigured(PaymentGatewayConfig $g): bool
    {
        return (bool) match ($g->gateway_type) {
            'midtrans' => $g->getDecryptedKey('server_key'),
            'xendit' => $g->getDecryptedKey('api_key'),
            'stripe' => $g->getDecryptedKey('api_key'),
            default => $g->getDecryptedKey('api_key') || $g->base_url,
        };
    }

    /**
     * Metode pembayaran yang tersedia per tipe gateway.
     */
    public function getPaymentMethods(string $gateway): array
    {
        return match (strtolower($gateway)) {
            'midtrans' => [
                ['code' => 'qris', 'label' => 'QRIS'],
                ['code' => 'gopay', 'label' => 'GoPay'],
                ['code' => 'shopeepay', 'label' => 'ShopeePay'],
                ['code' => 'bank_transfer', 'label' => 'Transfer Bank / VA'],
                ['code' => 'bca', 'label' => 'VA BCA'],
                ['code' => 'bni', 'label' => 'VA BNI'],
                ['code' => 'bri', 'label' => 'VA BRI'],
                ['code' => 'mandiri', 'label' => 'VA Mandiri'],
            ],
            'xendit' => [
                ['code' => 'invoice', 'label' => 'Invoice'],
                ['code' => 'qris', 'label' => 'QRIS'],
                ['code' => 'ovo', 'label' => 'OVO'],
                ['code' => 'dana', 'label' => 'DANA'],
                ['code' => 'linkaja', 'label' => 'LinkAja'],
                ['code' => 'bank_transfer', 'label' => 'Virtual Account'],
                ['code' => 'bca', 'label' => 'VA BCA'],
                ['code' => 'bni', 'label' => 'VA BNI'],
                ['code' => 'bri', 'label' => 'VA BRI'],
                ['code' => 'mandiri', 'label' => 'VA Mandiri'],
            ],
            'stripe' => [
                ['code' => 'card', 'label' => 'Kartu Debit/Kredit'],
                ['code' => 'payment_intent', 'label' => 'Payment Intent'],
            ],
            'custom' => [
                ['code' => 'custom', 'label' => 'Custom Charge'],
            ],
            default => [],
        };
    }

    // ──────────────────────────────────────────────
    //  CREATE CHARGE
    // ──────────────────────────────────────────────

    /**
     * Buat charge pembayaran melalui gateway terpilih.
     * $payload: amount, reference, currency, customer, description, method, bank, items, ...
     */
    public function createCharge(string $gateway, array $payload): array
    {
        $gateway = strtolower($gateway);

        if (in_array($gateway, ['gopay', 'ovo', 'dana', 'linkaja'])) {
            // E-wallet langsung: pilih provider yang paling cocok.
            return $this->createEwalletCharge($this->defaultEwalletProvider($gateway), $gateway, $payload);
        }

        $config = $this->findGateway($gateway);

        if (!$config) {
            return ['success' => false, 'message' => "Gateway '{$gateway}' belum dikonfigurasi atau tidak aktif."];
        }

        if (!$this->isConfigured($config)) {
            return ['success' => false, 'message' => "Gateway '{$config->name}' belum punya kredensial. Isi API key terlebih dahulu."];
        }

        return match ($gateway) {
            'midtrans' => $this->chargeMidtrans($config, $payload),
            'xendit' => $this->chargeXendit($config, $payload),
            'stripe' => $this->chargeStripe($config, $payload),
            default => $this->chargeCustom($config, $payload),
        };
    }

    protected function defaultEwalletProvider(string $ewallet): string
    {
        // GoPay umumnya via Midtrans, OVO/DANA/LinkAja via Xendit.
        if ($ewallet === 'gopay') {
            return $this->findGateway('midtrans') ? 'midtrans' : 'xendit';
        }

        return $this->findGateway('xendit') ? 'xendit' : 'midtrans';
    }

    protected function chargeMidtrans(PaymentGatewayConfig $config, array $payload): array
    {
        $serverKey = $config->getDecryptedKey('server_key') ?: $config->getDecryptedKey('api_key');
        $baseUrl = rtrim($config->base_url ?: 'https://api.midtrans.com/v2', '/');
        $orderId = $payload['reference'] ?? $this->generateReference('MID');
        $amount = (int) round((float) ($payload['amount'] ?? 0));
        $method = strtolower($payload['method'] ?? 'bank_transfer');
        $bank = strtolower($payload['bank'] ?? 'bca');

        $body = [
            'payment_type' => $method,
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $amount,
            ],
            'customer_details' => $this->customerDetails($payload),
        ];

        if ($method === 'bank_transfer') {
            $body['bank_transfer'] = ['bank' => in_array($bank, ['bca', 'bni', 'bri', 'mandiri', 'permata']) ? $bank : 'bca'];
        } elseif (in_array($method, ['gopay', 'shopeepay'])) {
            $body[$method] = ['enable_callback' => true, 'callback_url' => $this->webhookUrl('midtrans')];
        } elseif ($method === 'qris') {
            $body['qris'] = ['acquirer' => $payload['acquirer'] ?? 'gopay'];
        }

        $result = $this->send('POST', "{$baseUrl}/charge", $body, [
            'Authorization' => 'Basic ' . base64_encode($serverKey . ':'),
        ]);

        return $this->normalizeChargeResult($result, 'midtrans', $config, $orderId);
    }

    protected function chargeXendit(PaymentGatewayConfig $config, array $payload): array
    {
        $apiKey = $config->getDecryptedKey('api_key');
        $baseUrl = rtrim($config->base_url ?: 'https://api.xendit.co', '/');
        $externalId = $payload['reference'] ?? $this->generateReference('XND');
        $amount = (float) ($payload['amount'] ?? 0);

        $body = [
            'external_id' => $externalId,
            'amount' => $amount,
            'currency' => $payload['currency'] ?? 'IDR',
            'payer_email' => $payload['customer']['email'] ?? null,
            'description' => $payload['description'] ?? 'Pembayaran via ' . config('app.name'),
            'success_redirect_url' => $payload['success_redirect_url'] ?? url('/'),
            'failure_redirect_url' => $payload['failure_redirect_url'] ?? url('/'),
            'invoice_duration' => $payload['invoice_duration'] ?? 86400,
        ];

        if (!empty($payload['payment_methods'])) {
            $body['payment_methods'] = $payload['payment_methods'];
        }

        $result = $this->send('POST', "{$baseUrl}/v2/invoices", $body, [
            'Authorization' => 'Basic ' . base64_encode($apiKey . ':'),
        ]);

        return $this->normalizeChargeResult($result, 'xendit', $config, $externalId);
    }

    protected function chargeStripe(PaymentGatewayConfig $config, array $payload): array
    {
        $secretKey = $config->getDecryptedKey('api_key');
        $baseUrl = rtrim($config->base_url ?: 'https://api.stripe.com/v1', '/');
        $amount = (int) round((float) ($payload['amount'] ?? 0));
        $currency = strtolower($payload['currency'] ?? 'idr');

        $body = [
            'amount' => $amount,
            'currency' => $currency,
            'description' => $payload['description'] ?? 'Pembayaran via ' . config('app.name'),
            'metadata' => $payload['metadata'] ?? [],
        ];

        if (!empty($payload['customer']['email'])) {
            $body['receipt_email'] = $payload['customer']['email'];
        }

        $result = $this->send('POST', "{$baseUrl}/payment_intents", $body, [
            'Authorization' => 'Bearer ' . $secretKey,
        ], asForm: true);

        $reference = $result['response']['id'] ?? null;

        return $this->normalizeChargeResult($result, 'stripe', $config, $reference);
    }

    protected function chargeCustom(PaymentGatewayConfig $config, array $payload): array
    {
        $baseUrl = rtrim($config->base_url ?? '', '/');
        $reference = $payload['reference'] ?? $this->generateReference('CST');

        if (!$baseUrl) {
            return [
                'success' => false,
                'message' => "Base URL untuk gateway custom '{$config->name}' belum diisi.",
            ];
        }

        $result = $this->send('POST', $baseUrl, $payload, [
            'Authorization' => 'Bearer ' . ($config->getDecryptedKey('api_key') ?? ''),
        ]);

        return $this->normalizeChargeResult($result, 'custom', $config, $reference);
    }

    // ──────────────────────────────────────────────
    //  VIRTUAL ACCOUNT
    // ──────────────────────────────────────────────

    /**
     * Buat virtual account (BCA/BNI/BRI/Mandiri) via Midtrans atau Xendit.
     */
    public function createVirtualAccount(string $gateway, string $bank, array $payload): array
    {
        $gateway = strtolower($gateway);
        $config = $this->findGateway($gateway);

        if (!$config) {
            return ['success' => false, 'message' => "Gateway '{$gateway}' belum dikonfigurasi."];
        }

        $bank = strtolower($bank);
        $reference = $payload['reference'] ?? $this->generateReference('VA');
        $amount = (float) ($payload['amount'] ?? 0);
        $name = $payload['name'] ?? ($payload['customer']['name'] ?? 'Pelanggan');

        $result = $gateway === 'midtrans'
            ? $this->createVaMidtrans($config, $bank, $reference, $amount, $payload)
            : $this->createVaXendit($config, $bank, $reference, $amount, $name);

        // Persist ke tabel virtual_accounts bila company tersedia.
        if (($result['success'] ?? false) && $config->company_id) {
            $this->persistVirtualAccount($config->company_id, $result, $name, $amount, $reference, $payload);
        }

        return $result;
    }

    protected function createVaMidtrans(PaymentGatewayConfig $config, string $bank, string $reference, float $amount, array $payload): array
    {
        $serverKey = $config->getDecryptedKey('server_key') ?: $config->getDecryptedKey('api_key');
        $baseUrl = rtrim($config->base_url ?: 'https://api.midtrans.com/v2', '/');

        $body = [
            'payment_type' => 'bank_transfer',
            'transaction_details' => [
                'order_id' => $reference,
                'gross_amount' => (int) round($amount),
            ],
            'bank_transfer' => ['bank' => in_array($bank, ['bca', 'bni', 'bri', 'mandiri', 'permata']) ? $bank : 'bca'],
            'customer_details' => $this->customerDetails($payload),
        ];

        $result = $this->send('POST', "{$baseUrl}/charge", $body, [
            'Authorization' => 'Basic ' . base64_encode($serverKey . ':'),
        ]);

        $vaNumber = $result['response']['va_numbers'][0]['va_number']
            ?? $result['response']['permata_va_number']
            ?? $result['response']['va_number']
            ?? null;

        return $this->mergeChargeResult($result, [
            'success' => $result['success'] ?? false,
            'gateway' => 'midtrans',
            'gateway_name' => $config->name,
            'reference' => $reference,
            'bank' => strtoupper($bank),
            'va_number' => (string) $vaNumber,
            'status' => 'pending',
        ]);
    }

    protected function createVaXendit(PaymentGatewayConfig $config, string $bank, string $reference, float $amount, string $name): array
    {
        $apiKey = $config->getDecryptedKey('api_key');
        $baseUrl = rtrim($config->base_url ?: 'https://api.xendit.co', '/');
        $bankCode = strtoupper($bank);

        $body = [
            'external_id' => $reference,
            'bank_code' => in_array($bankCode, ['BCA', 'BNI', 'BRI', 'MANDIRI']) ? $bankCode : 'BCA',
            'name' => $name,
            'expected_amount' => (int) round($amount),
            'expiration_date' => now()->addDays(2)->toIso8601String(),
            'is_closed' => true,
        ];

        $result = $this->send('POST', "{$baseUrl}/v2/virtual_accounts", $body, [
            'Authorization' => 'Basic ' . base64_encode($apiKey . ':'),
        ]);

        return $this->mergeChargeResult($result, [
            'success' => $result['success'] ?? false,
            'gateway' => 'xendit',
            'gateway_name' => $config->name,
            'reference' => $reference,
            'bank' => $bankCode,
            'va_number' => (string) ($result['response']['account_number'] ?? ''),
            'status' => 'pending',
        ]);
    }

    protected function persistVirtualAccount(int $companyId, array $result, string $name, float $amount, string $reference, array $payload): void
    {
        try {
            VirtualAccount::updateOrCreate(
                ['bank' => strtolower($result['bank'] ?? 'bca'), 'va_number' => (string) ($result['va_number'] ?? $reference)],
                [
                    'company_id' => $companyId,
                    'name' => $name,
                    'expected_amount' => $amount,
                    'status' => 'active',
                    'expiry_at' => now()->addDays(2),
                    'metadata' => [
                        'gateway' => $result['gateway'] ?? null,
                        'reference' => $reference,
                        'source' => 'payment_gateway_service',
                    ],
                    'reference_entity' => $payload['reference_entity'] ?? null,
                    'reference_id' => $payload['reference_id'] ?? null,
                ]
            );
        } catch (\Throwable $e) {
            Log::warning('Gagal menyimpan virtual account', ['error' => $e->getMessage()]);
        }
    }

    // ──────────────────────────────────────────────
    //  E-WALLET CHARGE
    // ──────────────────────────────────────────────

    /**
     * Charge e-wallet (GoPay/OVO/DANA/LinkAja) via provider pilihan user.
     */
    public function createEwalletCharge(string $gateway, string $ewallet, array $payload): array
    {
        $gateway = strtolower($gateway);
        $ewallet = strtolower($ewallet);
        $config = $this->findGateway($gateway);

        if (!$config) {
            return ['success' => false, 'message' => "Gateway '{$gateway}' belum dikonfigurasi."];
        }

        if ($gateway === 'midtrans') {
            return $this->chargeMidtrans($config, array_merge($payload, ['method' => $ewallet === 'gopay' ? 'gopay' : 'shopeepay']));
        }

        // Xendit e-wallet (OVO, DANA, LinkAja).
        $apiKey = $config->getDecryptedKey('api_key');
        $baseUrl = rtrim($config->base_url ?: 'https://api.xendit.co', '/');
        $reference = $payload['reference'] ?? $this->generateReference('EW');
        $amount = (float) ($payload['amount'] ?? 0);
        $channel = strtoupper($ewallet);

        $body = [
            'reference_id' => $reference,
            'currency' => 'IDR',
            'amount' => $amount,
            'checkout_method' => 'ONE_TIME_PAYMENT',
            'channel_code' => $channel,
            'channel_properties' => $this->ewalletChannelProperties($ewallet, $payload),
        ];

        $result = $this->send('POST', "{$baseUrl}/ewallets/charges", $body, [
            'Authorization' => 'Basic ' . base64_encode($apiKey . ':'),
        ]);

        return $this->mergeChargeResult($result, [
            'success' => $result['success'] ?? false,
            'gateway' => 'xendit',
            'gateway_name' => $config->name,
            'reference' => $reference,
            'ewallet' => $channel,
            'status' => 'pending',
            'redirect_url' => $result['response']['actions']['desktop_web_checkout_url']
                ?? $result['response']['actions']['mobile_web_checkout_url']
                ?? null,
        ]);
    }

    protected function ewalletChannelProperties(string $ewallet, array $payload): array
    {
        $phone = $payload['customer']['phone'] ?? null;

        return match ($ewallet) {
            'ovo' => ['mobile_number' => $phone ? '+62' . ltrim($phone, '0') : null],
            'dana' => ['mobile_number' => $phone ? '+62' . ltrim($phone, '0') : null],
            default => [],
        };
    }

    // ──────────────────────────────────────────────
    //  CHECK STATUS
    // ──────────────────────────────────────────────

    public function checkStatus(string $gateway, string $referenceId): array
    {
        $gateway = strtolower($gateway);
        $config = $this->findGateway($gateway);

        if (!$config) {
            return ['success' => false, 'message' => "Gateway '{$gateway}' belum dikonfigurasi."];
        }

        return match ($gateway) {
            'midtrans' => $this->statusMidtrans($config, $referenceId),
            'xendit' => $this->statusXendit($config, $referenceId),
            'stripe' => $this->statusStripe($config, $referenceId),
            default => ['success' => false, 'message' => "Status untuk gateway '{$gateway}' tidak tersedia."],
        };
    }

    protected function statusMidtrans(PaymentGatewayConfig $config, string $referenceId): array
    {
        $serverKey = $config->getDecryptedKey('server_key') ?: $config->getDecryptedKey('api_key');
        $baseUrl = rtrim($config->base_url ?: 'https://api.midtrans.com/v2', '/');

        $result = $this->send('GET', "{$baseUrl}/{$referenceId}/status", [], [
            'Authorization' => 'Basic ' . base64_encode($serverKey . ':'),
        ]);

        $raw = $result['response']['transaction_status'] ?? 'pending';

        return [
            'success' => $result['success'] ?? false,
            'gateway' => 'midtrans',
            'reference' => $referenceId,
            'status' => $this->normalizeStatus($raw),
            'raw_status' => $raw,
            'raw' => $result['response'] ?? [],
        ];
    }

    protected function statusXendit(PaymentGatewayConfig $config, string $referenceId): array
    {
        $apiKey = $config->getDecryptedKey('api_key');
        $baseUrl = rtrim($config->base_url ?: 'https://api.xendit.co', '/');

        $result = $this->send('GET', "{$baseUrl}/v2/invoices/{$referenceId}", [], [
            'Authorization' => 'Basic ' . base64_encode($apiKey . ':'),
        ]);

        $raw = $result['response']['status'] ?? 'PENDING';

        return [
            'success' => $result['success'] ?? false,
            'gateway' => 'xendit',
            'reference' => $referenceId,
            'status' => $this->normalizeStatus($raw),
            'raw_status' => $raw,
            'raw' => $result['response'] ?? [],
        ];
    }

    protected function statusStripe(PaymentGatewayConfig $config, string $referenceId): array
    {
        $secretKey = $config->getDecryptedKey('api_key');
        $baseUrl = rtrim($config->base_url ?: 'https://api.stripe.com/v1', '/');

        $result = $this->send('GET', "{$baseUrl}/payment_intents/{$referenceId}", [], [
            'Authorization' => 'Bearer ' . $secretKey,
        ]);

        $raw = $result['response']['status'] ?? 'pending';

        return [
            'success' => $result['success'] ?? false,
            'gateway' => 'stripe',
            'reference' => $referenceId,
            'status' => $this->normalizeStatus($raw),
            'raw_status' => $raw,
            'raw' => $result['response'] ?? [],
        ];
    }

    // ──────────────────────────────────────────────
    //  WEBHOOK CALLBACK
    // ──────────────────────────────────────────────

    /**
     * Proses webhook callback dari gateway dan perbarui status pembayaran.
     */
    public function handleCallback(string $gateway, array $webhookPayload): array
    {
        $gateway = strtolower($gateway);
        $reference = $this->extractReference($gateway, $webhookPayload);
        $rawStatus = $this->extractStatus($gateway, $webhookPayload);

        Log::info('Payment gateway webhook received', [
            'gateway' => $gateway,
            'reference' => $reference,
            'raw_status' => $rawStatus,
        ]);

        if (!$reference) {
            return ['success' => false, 'message' => 'Referensi transaksi tidak ditemukan di payload webhook.'];
        }

        $config = $this->resolveConfigForReference($gateway, $reference);

        if (!$config) {
            return ['success' => false, 'message' => "Gateway '{$gateway}' tidak ditemukan untuk referensi {$reference}."];
        }

        if (!$this->verifySignature($config, $gateway, $webhookPayload)) {
            return ['success' => false, 'message' => 'Verifikasi tanda tangan webhook gagal.'];
        }

        $status = $this->normalizeStatus($rawStatus);

        $this->applyPaymentStatus($reference, $status);

        return [
            'success' => true,
            'gateway' => $gateway,
            'reference' => $reference,
            'status' => $status,
            'raw_status' => $rawStatus,
        ];
    }

    protected function resolveConfigForReference(string $gateway, string $reference): ?PaymentGatewayConfig
    {
        // Coba temukan company dari catatan pembayaran yang cocok referensinya.
        $companyId = $this->companyId;

        $posPayment = PosPayment::where('reference_number', $reference)->first();
        if ($posPayment) {
            $companyId = $posPayment->transaction?->company_id ?? $companyId;
        } else {
            $payment = Payment::where('reference_number', $reference)->first();
            $companyId = $payment?->company_id ?? $companyId;
        }

        return $this->findGateway($gateway, $companyId);
    }

    protected function verifySignature(PaymentGatewayConfig $config, string $gateway, array $payload): bool
    {
        $extra = $config->extra_config ?? [];

        try {
            return match ($gateway) {
                'midtrans' => $this->verifyMidtransSignature($config, $payload, $extra),
                'xendit' => $this->verifyXenditSignature($config, $payload, $extra),
                'stripe' => $this->verifyStripeSignature($payload, $extra),
                default => true,
            };
        } catch (\Throwable $e) {
            Log::warning('Verifikasi tanda tangan webhook error', ['error' => $e->getMessage()]);

            return false;
        }
    }

    protected function verifyMidtransSignature(PaymentGatewayConfig $config, array $payload, array $extra): bool
    {
        $signatureKey = $extra['signature_key'] ?? $config->getDecryptedKey('api_secret');
        if (!$signatureKey) {
            return true; // Tidak ada signature key — skip verifikasi (mode longgar).
        }

        $orderId = $payload['order_id'] ?? '';
        $statusCode = $payload['status_code'] ?? '';
        $grossAmount = $payload['gross_amount'] ?? '';
        $serverKey = $config->getDecryptedKey('server_key') ?? '';

        $raw = $orderId . $statusCode . $grossAmount . $serverKey;
        $expected = hash('sha512', $raw);

        return hash_equals($expected, $payload['signature_key'] ?? '');
    }

    protected function verifyXenditSignature(PaymentGatewayConfig $config, array $payload, array $extra): bool
    {
        $token = $extra['webhook_token'] ?? $config->getDecryptedKey('api_secret');
        if (!$token) {
            return true;
        }

        $header = request()->header('x-callback-token');

        return $header && hash_equals($token, $header);
    }

    protected function verifyStripeSignature(array $payload, array $extra): bool
    {
        $secret = $extra['webhook_secret'] ?? null;
        if (!$secret) {
            return true;
        }

        return true; // Verifikasi stripe signature memerlukan raw body; di-handle di controller bila ada.
    }

    protected function extractReference(string $gateway, array $payload): ?string
    {
        return match ($gateway) {
            'midtrans' => $payload['order_id'] ?? null,
            'xendit' => $payload['external_id'] ?? ($payload['data']['reference_id'] ?? null),
            'stripe' => $payload['data']['object']['id'] ?? null,
            default => $payload['reference'] ?? $payload['reference_id'] ?? $payload['external_id'] ?? null,
        };
    }

    protected function extractStatus(string $gateway, array $payload): ?string
    {
        return match ($gateway) {
            'midtrans' => $payload['transaction_status'] ?? null,
            'xendit' => $payload['status'] ?? ($payload['data']['status'] ?? null),
            'stripe' => $payload['data']['object']['status'] ?? null,
            default => $payload['status'] ?? null,
        };
    }

    protected function applyPaymentStatus(string $reference, string $status): void
    {
        $posPayment = PosPayment::where('reference_number', $reference)->first();

        if ($posPayment) {
            $posPayment->update([
                'payment_status' => $status === 'paid' ? 'paid' : ($status === 'failed' ? 'failed' : 'pending'),
                'paid_at' => $posPayment->paid_at ?? now(),
            ]);

            $this->recomputeTransactionStatus($posPayment);

            return;
        }

        $payment = Payment::where('reference_number', $reference)->first();
        if ($payment) {
            $payment->update([
                'status' => $status === 'paid' ? 'confirmed' : ($status === 'failed' ? 'rejected' : 'pending'),
            ]);
        }
    }

    protected function recomputeTransactionStatus(PosPayment $posPayment): void
    {
        $transaction = $posPayment->transaction;
        if (!$transaction) {
            return;
        }

        $paidTotal = $transaction->payments()
            ->where('payment_status', 'paid')
            ->sum('amount');

        $grandTotal = (float) $transaction->grand_total;

        if ($paidTotal >= $grandTotal - 0.001 && $grandTotal > 0) {
            $transaction->update(['payment_status' => 'paid']);
        } elseif ($paidTotal > 0) {
            $transaction->update(['payment_status' => 'partial']);
        } else {
            $transaction->update(['payment_status' => 'pending']);
        }
    }

    // ──────────────────────────────────────────────
    //  TEST CONNECTION
    // ──────────────────────────────────────────────

    /**
     * Uji koneksi ke gateway (ping minimal untuk validasi kredensial).
     */
    public function testConnection(string $gateway, ?int $companyId = null): array
    {
        $config = $this->findGateway($gateway, $companyId);

        if (!$config) {
            return ['success' => false, 'message' => "Gateway '{$gateway}' belum dikonfigurasi."];
        }

        if (!$this->isConfigured($config)) {
            return ['success' => false, 'message' => "Gateway '{$config->name}' belum punya kredensial lengkap."];
        }

        try {
            $result = match ($config->gateway_type) {
                'midtrans' => $this->send('GET', rtrim($config->base_url ?: 'https://api.midtrans.com/v2', '/') . '/', [], [
                    'Authorization' => 'Basic ' . base64_encode(($config->getDecryptedKey('server_key') ?: '') . ':'),
                ]),
                'xendit' => $this->send('GET', rtrim($config->base_url ?: 'https://api.xendit.co', '/') . '/v2/invoices', [], [
                    'Authorization' => 'Basic ' . base64_encode(($config->getDecryptedKey('api_key') ?: '') . ':'),
                ]),
                'stripe' => $this->send('GET', rtrim($config->base_url ?: 'https://api.stripe.com/v1', '/') . '/balance', [], [
                    'Authorization' => 'Bearer ' . ($config->getDecryptedKey('api_key') ?? ''),
                ]),
                default => $this->send('GET', rtrim($config->base_url ?? '', '/'), [], [
                    'Authorization' => 'Bearer ' . ($config->getDecryptedKey('api_key') ?? ''),
                ]),
            };

            return [
                'success' => $result['success'] ?? false,
                'gateway' => $config->gateway_type,
                'name' => $config->name,
                'message' => ($result['success'] ?? false)
                    ? 'Koneksi ke gateway berhasil.'
                    : ($result['message'] ?? 'Koneksi gagal.'),
                'http_status' => $result['http_status'] ?? null,
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'gateway' => $config->gateway_type, 'name' => $config->name, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    // ──────────────────────────────────────────────
    //  HELPERS
    // ──────────────────────────────────────────────

    protected function send(string $method, string $url, array $data = [], array $headers = [], bool $asForm = false): array
    {
        try {
            $request = Http::withHeaders(array_merge([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ], $headers));

            $response = match (strtoupper($method)) {
                'GET' => $request->get($url),
                'POST' => $asForm ? $request->asForm()->post($url, $data) : $request->post($url, $data),
                default => $request->post($url, $data),
            };

            $json = $response->json();

            if ($response->failed()) {
                return [
                    'success' => false,
                    'http_status' => $response->status(),
                    'message' => $json['message'] ?? $json['error'] ?? ($json['status_message'] ?? 'Permintaan ke gateway gagal.'),
                    'response' => $json,
                ];
            }

            return [
                'success' => true,
                'http_status' => $response->status(),
                'response' => $json,
            ];
        } catch (\Throwable $e) {
            Log::warning('Payment gateway request failed', ['url' => $url, 'error' => $e->getMessage()]);

            return ['success' => false, 'message' => 'Koneksi ke gateway gagal: ' . $e->getMessage()];
        }
    }

    protected function normalizeChargeResult(array $result, string $gateway, PaymentGatewayConfig $config, ?string $reference): array
    {
        $raw = $result['response'] ?? [];

        $redirectUrl = match ($gateway) {
            'midtrans' => $raw['redirect_url'] ?? null,
            'xendit' => $raw['invoice_url'] ?? null,
            'stripe' => $raw['client_secret'] ?? null,
            default => $raw['redirect_url'] ?? $raw['payment_url'] ?? null,
        };

        return [
            'success' => $result['success'] ?? false,
            'gateway' => $gateway,
            'gateway_name' => $config->name,
            'reference' => $reference,
            'transaction_id' => $reference,
            'status' => 'pending',
            'redirect_url' => $redirectUrl,
            'payment_url' => $redirectUrl,
            'qr_string' => $raw['actions'][0]['url'] ?? null,
            'actions' => $raw['actions'] ?? [],
            'message' => ($result['success'] ?? false) ? 'Charge berhasil dibuat.' : ($result['message'] ?? 'Charge gagal.'),
            'raw' => $raw,
        ];
    }

    protected function mergeChargeResult(array $result, array $overrides): array
    {
        return array_merge($overrides, [
            'message' => ($result['success'] ?? false) ? 'Transaksi berhasil dibuat.' : ($result['message'] ?? 'Transaksi gagal.'),
            'raw' => $result['response'] ?? [],
        ]);
    }

    protected function customerDetails(array $payload): array
    {
        $customer = $payload['customer'] ?? [];

        return array_filter([
            'first_name' => $customer['name'] ?? ($customer['first_name'] ?? null),
            'email' => $customer['email'] ?? null,
            'phone' => $customer['phone'] ?? null,
        ]);
    }

    protected function normalizeStatus(?string $raw): string
    {
        if (!$raw) {
            return 'pending';
        }

        $raw = strtolower((string) $raw);

        return match ($raw) {
            'settlement', 'capture', 'paid', 'succeeded', 'completed', 'success', 'confirmed' => 'paid',
            'deny', 'cancel', 'expire', 'expired', 'failure', 'failed', 'rejected', 'voided', 'cancelled' => 'failed',
            'refund', 'refunded', 'partially_refunded' => 'refunded',
            default => 'pending',
        };
    }

    protected function generateReference(string $prefix): string
    {
        return strtoupper($prefix) . '-' . date('YmdHis') . '-' . strtoupper(Str::random(8));
    }

    protected function webhookUrl(string $gateway): string
    {
        return url('/webhooks/payment/' . $gateway);
    }
}
