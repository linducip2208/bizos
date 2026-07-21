<?php

namespace App\Services;

use App\Models\Webhook;
use App\Models\WebhookDelivery;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class WebhookService
{
    protected array $retryDelays = [60, 300, 900, 3600, 21600];

    public function register(string $event, string $url, ?string $secret = null, ?int $companyId = null, ?int $createdBy = null, string $name = ''): Webhook
    {
        return Webhook::create([
            'company_id' => $companyId,
            'name' => $name ?: "Webhook: {$event}",
            'event' => $event,
            'url' => $url,
            'secret' => $secret,
            'is_active' => true,
            'created_by' => $createdBy,
        ]);
    }

    public function fire(string $event, array $payload): void
    {
        $webhooks = Webhook::where('event', $event)
            ->where('is_active', true)
            ->get();

        foreach ($webhooks as $webhook) {
            $this->dispatch($webhook, $payload, 1);
        }
    }

    public function dispatch(Webhook $webhook, array $payload, int $attempt = 1): void
    {
        $start = microtime(true);

        $delivery = WebhookDelivery::create([
            'webhook_id' => $webhook->id,
            'request_payload' => $payload,
            'status' => 'pending',
            'attempt' => $attempt,
        ]);

        try {
            $headers = $webhook->headers ?? [];
            $headers['Content-Type'] = 'application/json';
            $headers['User-Agent'] = 'BizOS-Webhook/1.0';

            if ($webhook->secret) {
                $headers['X-Webhook-Signature'] = $this->generateSignature(
                    json_encode($payload),
                    $webhook->secret
                );
            }

            $response = Http::timeout(30)
                ->withHeaders($headers)
                ->post($webhook->url, $payload);

            $durationMs = (int) ((microtime(true) - $start) * 1000);

            $delivery->update([
                'response_code' => $response->status(),
                'response_body' => mb_substr($response->body(), 0, 5000),
                'duration_ms' => $durationMs,
                'status' => $response->successful() ? 'success' : 'failed',
                'error_message' => $response->successful() ? null : "HTTP {$response->status()}",
            ]);
        } catch (\Throwable $e) {
            $durationMs = (int) ((microtime(true) - $start) * 1000);

            $delivery->update([
                'response_code' => 0,
                'duration_ms' => $durationMs,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }

        if ($delivery->status === 'failed' && $attempt < $webhook->max_retries) {
            $delay = $this->retryDelays[min($attempt - 1, count($this->retryDelays) - 1)];
            $delivery->update([
                'next_retry_at' => now()->addSeconds($delay),
            ]);
        }
    }

    public function retryFailed(): void
    {
        $deliveries = WebhookDelivery::where('status', 'failed')
            ->where('next_retry_at', '<=', now())
            ->with('webhook')
            ->get();

        foreach ($deliveries as $delivery) {
            if (! $delivery->webhook || ! $delivery->webhook->is_active) {
                continue;
            }

            $this->dispatch($delivery->webhook, $delivery->request_payload, $delivery->attempt + 1);
        }
    }

    public function validateSignature(string $payload, string $signature, string $secret): bool
    {
        $expected = $this->generateSignature($payload, $secret);
        return hash_equals($expected, $signature);
    }

    public function generateSignature(string $payload, string $secret): string
    {
        return 'sha256=' . hash_hmac('sha256', $payload, $secret);
    }

    public function getDeliveryLogs(Webhook $webhook): Collection
    {
        return WebhookDelivery::where('webhook_id', $webhook->id)
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();
    }

    public function testWebhook(Webhook $webhook): WebhookDelivery
    {
        $payload = [
            'event' => $webhook->event,
            'test' => true,
            'timestamp' => now()->toIso8601String(),
            'message' => 'Ini adalah payload test dari BizOS Webhook Builder.',
        ];

        $this->dispatch($webhook, $payload);

        return $webhook->deliveries()->latest()->first();
    }
}
