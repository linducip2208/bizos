<?php

namespace App\Services;

use App\Models\SmsGateway;
use App\Models\SmsLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    public function send(string $to, string $message, ?string $gatewayName = null): array
    {
        $gateway = $gatewayName
            ? SmsGateway::where('name', $gatewayName)->where('is_active', true)->first()
            : SmsGateway::where('is_active', true)->first();

        if (!$gateway) {
            return ['success' => false, 'error' => 'Tidak ada SMS gateway aktif.'];
        }

        try {
            $result = match ($gateway->provider) {
                'twilio' => $this->sendViaTwilio($gateway, $to, $message),
                'vonage' => $this->sendViaVonage($gateway, $to, $message),
                'zenziva' => $this->sendViaZenziva($gateway, $to, $message),
                'gammu' => $this->sendViaGammu($gateway, $to, $message),
                default => ['success' => false, 'error' => 'Provider tidak dikenal: ' . $gateway->provider],
            };

            $this->logSms($gateway, $to, $message, $result);

            return $result;
        } catch (\Throwable $e) {
            Log::error('SMS send failed', ['error' => $e->getMessage()]);
            $this->logSms($gateway, $to, $message, ['success' => false, 'error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function sendBulk(array $recipients, string $message, ?string $gatewayName = null): array
    {
        $results = [];
        foreach ($recipients as $to) {
            $results[$to] = $this->send($to, $message, $gatewayName);
        }
        return $results;
    }

    public function getStatus(string $messageId): string
    {
        $log = SmsLog::where('message_id', $messageId)->first();
        return $log?->status ?? 'unknown';
    }

    public function getBalance(?string $gatewayName = null): float
    {
        $gateway = $gatewayName
            ? SmsGateway::where('name', $gatewayName)->where('is_active', true)->first()
            : SmsGateway::where('is_active', true)->first();

        if (!$gateway) {
            return 0;
        }

        try {
            return match ($gateway->provider) {
                'twilio' => $this->getTwilioBalance($gateway),
                'zenziva' => $this->getZenzivaBalance($gateway),
                default => 0,
            };
        } catch (\Throwable $e) {
            return 0;
        }
    }

    protected function sendViaTwilio(SmsGateway $gateway, string $to, string $message): array
    {
        $accountSid = $gateway->api_key_encrypted;
        $authToken = $gateway->api_secret_encrypted;
        $from = $gateway->sender_id;
        $baseUrl = $gateway->base_url ?: "https://api.twilio.com/2010-04-01";

        $response = Http::withBasicAuth($accountSid, $authToken)
            ->asForm()
            ->post("{$baseUrl}/Accounts/{$accountSid}/Messages.json", [
                'From' => $from,
                'To' => $to,
                'Body' => $message,
            ]);

        if ($response->successful()) {
            return [
                'success' => true,
                'message_id' => $response->json('sid'),
                'status' => 'sent',
            ];
        }

        return ['success' => false, 'error' => $response->body()];
    }

    protected function sendViaVonage(SmsGateway $gateway, string $to, string $message): array
    {
        $apiKey = $gateway->api_key_encrypted;
        $apiSecret = $gateway->api_secret_encrypted;
        $from = $gateway->sender_id;

        $response = Http::post('https://rest.nexmo.com/sms/json', [
            'api_key' => $apiKey,
            'api_secret' => $apiSecret,
            'from' => $from,
            'to' => $to,
            'text' => $message,
        ]);

        $body = $response->json();
        if ($response->successful() && isset($body['messages'][0]['status']) && $body['messages'][0]['status'] === '0') {
            return [
                'success' => true,
                'message_id' => $body['messages'][0]['message-id'] ?? null,
                'status' => 'sent',
            ];
        }

        return ['success' => false, 'error' => $body['messages'][0]['error-text'] ?? $response->body()];
    }

    protected function sendViaZenziva(SmsGateway $gateway, string $to, string $message): array
    {
        $userkey = $gateway->api_key_encrypted;
        $passkey = $gateway->api_secret_encrypted;
        $baseUrl = $gateway->base_url ?: 'https://reguler.zenziva.net/apps/smsapi.php';

        $response = Http::asForm()->post($baseUrl, [
            'userkey' => $userkey,
            'passkey' => $passkey,
            'nohp' => $to,
            'pesan' => $message,
        ]);

        if ($response->successful() && str_contains($response->body(), 'SUCCESS')) {
            preg_match('/messageId:(\d+)/', $response->body(), $matches);
            return [
                'success' => true,
                'message_id' => $matches[1] ?? null,
                'status' => 'sent',
            ];
        }

        return ['success' => false, 'error' => $response->body()];
    }

    protected function sendViaGammu(SmsGateway $gateway, string $to, string $message): array
    {
        $baseUrl = $gateway->base_url ?: 'http://localhost:8080';
        $key = $gateway->api_key_encrypted;

        $response = Http::withHeaders(['X-API-Key' => $key])
            ->post("{$baseUrl}/send", [
                'to' => $to,
                'message' => $message,
            ]);

        if ($response->successful()) {
            return [
                'success' => true,
                'message_id' => $response->json('id') ?? null,
                'status' => 'sent',
            ];
        }

        return ['success' => false, 'error' => $response->body()];
    }

    protected function getTwilioBalance(SmsGateway $gateway): float
    {
        $accountSid = $gateway->api_key_encrypted;
        $authToken = $gateway->api_secret_encrypted;
        $baseUrl = $gateway->base_url ?: "https://api.twilio.com/2010-04-01";

        $response = Http::withBasicAuth($accountSid, $authToken)
            ->get("{$baseUrl}/Accounts/{$accountSid}/Balance.json");

        return (float) ($response->json('balance') ?? 0);
    }

    protected function getZenzivaBalance(SmsGateway $gateway): float
    {
        $userkey = $gateway->api_key_encrypted;
        $passkey = $gateway->api_secret_encrypted;
        $baseUrl = str_replace('smsapi.php', 'ceksaldo.php', $gateway->base_url ?: 'https://reguler.zenziva.net/apps/smsapi.php');

        $response = Http::asForm()->post($baseUrl, [
            'userkey' => $userkey,
            'passkey' => $passkey,
        ]);

        preg_match('/Sisa Saldo:(\d+)/', $response->body(), $matches);
        return (float) ($matches[1] ?? 0);
    }

    protected function logSms(SmsGateway $gateway, string $to, string $message, array $result): void
    {
        SmsLog::create([
            'company_id' => $gateway->company_id,
            'gateway_id' => $gateway->id,
            'recipient' => $to,
            'message' => $message,
            'status' => $result['success'] ? ($result['status'] ?? 'sent') : 'failed',
            'message_id' => $result['message_id'] ?? null,
            'cost' => 0,
            'error_message' => $result['error'] ?? null,
        ]);
    }
}
