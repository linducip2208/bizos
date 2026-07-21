<?php

namespace App\Services;

use App\Models\PosPayment;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class QrisService
{
    protected $merchantId;
    protected $terminalId;
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->merchantId = config('services.qris.merchant_id', '');
        $this->terminalId = config('services.qris.terminal_id', '');
        $this->apiKey = config('services.qris.api_key', '');
        $this->baseUrl = config('services.qris.base_url', 'https://api.midtrans.com/v2');
    }

    public function generateStaticQris(float $amount, ?string $merchantId = null, ?string $terminalId = null): array
    {
        $merchantId = $merchantId ?: $this->merchantId;
        $terminalId = $terminalId ?: $this->terminalId;
        $transactionId = 'QRIS-' . date('Ymd') . '-' . strtoupper(Str::random(8));
        $amountFormatted = number_format($amount, 0, '', '');

        $qrString = $this->buildQrisString(
            merchantId: $merchantId,
            terminalId: $terminalId,
            amount: $amountFormatted,
            transactionId: $transactionId
        );

        return [
            'qr_string' => $qrString,
            'transaction_id' => $transactionId,
            'amount' => $amount,
            'merchant_id' => $merchantId,
            'terminal_id' => $terminalId,
            'generated_at' => now()->toIso8601String(),
        ];
    }

    public function generateDynamicQris(float $amount, string $reference): array
    {
        $transactionId = 'DYN-' . date('Ymd') . '-' . strtoupper(Str::random(10));

        $payload = [
            'payment_type' => 'qris',
            'transaction_details' => [
                'order_id' => $transactionId,
                'gross_amount' => (int) $amount,
            ],
            'qris' => [
                'acquirer' => 'gopay',
            ],
        ];

        try {
            $response = $this->request('POST', '/charge', $payload);
            $responseData = $response['actions'] ?? [];

            return [
                'qr_string' => $responseData[0]['url'] ?? '',
                'transaction_id' => $transactionId,
                'amount' => $amount,
                'reference' => $reference,
                'actions' => $responseData,
                'expiry_time' => $response['expiry_time'] ?? null,
                'generated_at' => now()->toIso8601String(),
            ];
        } catch (\Exception $e) {
            Log::error('QRIS Dynamic generation failed', [
                'error' => $e->getMessage(),
                'transaction_id' => $transactionId,
            ]);

            $qrString = $this->buildQrisString(
                merchantId: $this->merchantId,
                terminalId: $this->terminalId,
                amount: number_format($amount, 0, '', ''),
                transactionId: $transactionId,
                dynamic: true
            );

            return [
                'qr_string' => $qrString,
                'transaction_id' => $transactionId,
                'amount' => $amount,
                'reference' => $reference,
                'generated_at' => now()->toIso8601String(),
            ];
        }
    }

    public function checkPaymentStatus(string $transactionId): string
    {
        try {
            $response = $this->request('GET', "/{$transactionId}/status");
            $statusMap = [
                'settlement' => 'success',
                'capture' => 'success',
                'pending' => 'pending',
                'deny' => 'failed',
                'cancel' => 'failed',
                'expire' => 'expired',
                'failure' => 'failed',
                'refund' => 'refunded',
            ];

            $midtransStatus = $response['transaction_status'] ?? 'pending';
            return $statusMap[$midtransStatus] ?? 'pending';
        } catch (\Exception $e) {
            Log::warning('QRIS status check failed', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage(),
            ]);
            return 'pending';
        }
    }

    public function handleWebhook(array $payload): void
    {
        $transactionId = $payload['order_id'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? null;
        $fraudStatus = $payload['fraud_status'] ?? 'accept';
        $grossAmount = $payload['gross_amount'] ?? 0;

        if (!$transactionId || !$transactionStatus) {
            Log::warning('QRIS webhook: Invalid payload', ['payload' => $payload]);
            return;
        }

        Log::info('QRIS webhook received', [
            'transaction_id' => $transactionId,
            'status' => $transactionStatus,
            'fraud' => $fraudStatus,
        ]);

        $payment = PosPayment::where('reference_number', $transactionId)->first();
        if (!$payment) return;

        $newStatus = match ($transactionStatus) {
            'settlement', 'capture' => 'paid',
            'pending' => 'pending',
            'deny', 'cancel', 'expire', 'failure' => 'failed',
            default => 'pending',
        };

        if ($fraudStatus === 'deny') {
            $newStatus = 'failed';
        }

        $payment->update(['payment_status' => $newStatus]);

        $transaction = $payment->transaction;
        if ($transaction && $newStatus === 'paid') {
            $paidTotal = $transaction->payments()
                ->where('payment_status', 'paid')
                ->sum('amount');

            $grandTotal = (float) $transaction->grand_total;
            if ($paidTotal >= $grandTotal) {
                $transaction->update(['payment_status' => 'paid']);
            } elseif ($paidTotal > 0) {
                $transaction->update(['payment_status' => 'partial']);
            }
        }
    }

    protected function buildQrisString(
        string $merchantId,
        string $terminalId,
        string $amount,
        string $transactionId,
        bool $dynamic = false
    ): string {
        $merchantAccountInfo = "ID2020{$merchantId}{$terminalId}";
        $merchantAccountInfoLen = str_pad(strlen($merchantAccountInfo), 2, '0', STR_PAD_LEFT);

        $merchantName = 'BizOS Merchant';
        $merchantCity = 'Jakarta';
        $postalCode = '12345';

        $payload = "00" . "01"
            . "01" . "12"
            . "26" . $merchantAccountInfoLen . $merchantAccountInfo
            . "52" . "5812"
            . "53" . "360"
            . "54" . $amount
            . "58" . "ID"
            . "59" . $merchantName
            . "60" . $merchantCity
            . "61" . "12345"
            . "62" . "01" . $transactionId
            . "63" . "0211";

        $crc = sprintf('%04X', crc32($payload) & 0xFFFF);
        $payload .= "63" . $crc;

        return $payload;
    }

    protected function request(string $method, string $endpoint, ?array $data = null): array
    {
        $auth = base64_encode($this->apiKey . ':');

        $ch = curl_init($this->baseUrl . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Basic ' . $auth,
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $decoded = json_decode($response, true);

        if ($httpCode >= 400) {
            throw new \RuntimeException('QRIS API error: ' . ($decoded['status_message'] ?? 'Unknown error'));
        }

        return $decoded ?? [];
    }
}
