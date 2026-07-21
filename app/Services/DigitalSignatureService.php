<?php

namespace App\Services;

use App\Models\DocumentGeneration;
use App\Models\SignatureProvider;
use App\Models\SignatureRequest;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DigitalSignatureService
{
    public function sendForSigning(DocumentGeneration $doc, array $signers, ?string $provider = null): array
    {
        $sigProvider = $this->resolveProvider($provider);

        if (!$sigProvider) {
            return $this->manualSigningFallback($doc, $signers);
        }

        $payload = [
            'document' => $this->getDocumentBase64($doc),
            'document_name' => $doc->template->name ?? 'document.pdf',
            'signers' => $signers,
            'callback_url' => route('webhook.signature', ['provider' => $sigProvider->name]),
        ];

        try {
            $baseUrl = rtrim($sigProvider->base_url, '/');
            $apiKey = decrypt($sigProvider->api_key_encrypted);

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ])
                ->timeout(60)
                ->post("{$baseUrl}/api/v1/documents/send", $payload);

            if ($response->successful()) {
                $body = $response->json();
                $externalId = $body['id'] ?? $body['document_id'] ?? (string) Str::uuid();

                SignatureRequest::create([
                    'document_generation_id' => $doc->id,
                    'provider' => $sigProvider->name,
                    'external_id' => $externalId,
                    'status' => 'sent',
                    'signers' => $signers,
                ]);

                $doc->update([
                    'status' => 'sent',
                    'signature_provider' => $sigProvider->name,
                ]);

                return [
                    'success' => true,
                    'external_id' => $externalId,
                    'provider' => $sigProvider->name,
                ];
            }

            Log::error('Digital signature send failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return $this->manualSigningFallback($doc, $signers);
        } catch (ConnectionException $e) {
            Log::error('Digital signature connection error: ' . $e->getMessage());
            return $this->manualSigningFallback($doc, $signers);
        }
    }

    public function checkStatus(string $externalDocId, ?string $provider = null): array
    {
        $sigProvider = $this->resolveProvider($provider);

        if (!$sigProvider) {
            $sigRequest = SignatureRequest::where('external_id', $externalDocId)->first();
            return [
                'status' => $sigRequest?->status ?? 'unknown',
                'completed_at' => $sigRequest?->completed_at?->toIso8601String(),
            ];
        }

        try {
            $baseUrl = rtrim($sigProvider->base_url, '/');
            $apiKey = decrypt($sigProvider->api_key_encrypted);

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
            ])
                ->timeout(30)
                ->get("{$baseUrl}/api/v1/documents/{$externalDocId}/status");

            if ($response->successful()) {
                $body = $response->json();
                $status = $body['status'] ?? 'unknown';

                $sigRequest = SignatureRequest::where('external_id', $externalDocId)->first();
                if ($sigRequest) {
                    $sigRequest->update(['status' => $status]);
                    if (in_array($status, ['completed', 'signed'])) {
                        $sigRequest->update(['completed_at' => now()]);
                        $sigRequest->documentGeneration->update([
                            'status' => 'signed',
                            'signed_at' => now(),
                        ]);
                    }
                }

                return [
                    'status' => $status,
                    'completed_at' => $sigRequest?->completed_at?->toIso8601String(),
                ];
            }

            return ['status' => 'unknown', 'error' => 'Failed to check status'];
        } catch (ConnectionException $e) {
            Log::error('Signature status check error: ' . $e->getMessage());
            return ['status' => 'unknown', 'error' => $e->getMessage()];
        }
    }

    public function handleCallback(string $provider, array $payload): void
    {
        Log::info('Signature webhook received', ['provider' => $provider, 'payload' => $payload]);

        $externalId = $payload['document_id'] ?? $payload['id'] ?? null;
        if (!$externalId) {
            Log::warning('Signature webhook missing document_id');
            return;
        }

        $sigRequest = SignatureRequest::where('external_id', $externalId)->first();
        if (!$sigRequest) {
            Log::warning('Signature request not found', ['external_id' => $externalId]);
            return;
        }

        $newStatus = $payload['status'] ?? $payload['event'] ?? 'unknown';

        $statusMap = [
            'document.signed' => 'signed',
            'document.completed' => 'completed',
            'document.declined' => 'declined',
            'document.expired' => 'expired',
            'document.viewed' => 'viewed',
            'signed' => 'signed',
            'completed' => 'completed',
            'declined' => 'declined',
            'expired' => 'expired',
        ];

        $mappedStatus = $statusMap[$newStatus] ?? $newStatus;

        $sigRequest->update(['status' => $mappedStatus]);

        if (in_array($mappedStatus, ['signed', 'completed'])) {
            $sigRequest->update(['completed_at' => now()]);
            $sigRequest->documentGeneration->update([
                'status' => 'signed',
                'signed_at' => now(),
            ]);
        }

        if (in_array($mappedStatus, ['declined', 'expired'])) {
            $sigRequest->documentGeneration->update([
                'status' => 'draft',
            ]);
        }
    }

    public function getCertificate(string $externalDocId): string
    {
        $sigRequest = SignatureRequest::where('external_id', $externalDocId)->first();
        if (!$sigRequest || !$sigRequest->documentGeneration->file_path) {
            throw new \RuntimeException('Dokumen tidak ditemukan');
        }

        return $sigRequest->documentGeneration->file_path;
    }

    public function verifyDocument(string $filePath): bool
    {
        if (!Storage::disk('public')->exists($filePath)) {
            return false;
        }

        $fullPath = Storage::disk('public')->path($filePath);
        $content = file_get_contents($fullPath);

        if (empty($content)) {
            return false;
        }

        return str_contains($content, '%PDF');
    }

    protected function resolveProvider(?string $provider = null): ?SignatureProvider
    {
        if ($provider) {
            return SignatureProvider::where('name', $provider)
                ->where('is_active', true)
                ->first();
        }

        return SignatureProvider::where('is_active', true)->first();
    }

    protected function getDocumentBase64(DocumentGeneration $doc): string
    {
        if (!$doc->file_path || !Storage::disk('public')->exists($doc->file_path)) {
            throw new \RuntimeException('File dokumen tidak ditemukan');
        }

        return base64_encode(Storage::disk('public')->get($doc->file_path));
    }

    protected function manualSigningFallback(DocumentGeneration $doc, array $signers): array
    {
        $doc->update(['status' => 'sent', 'signature_provider' => 'manual']);

        SignatureRequest::create([
            'document_generation_id' => $doc->id,
            'provider' => 'manual',
            'external_id' => 'MANUAL-' . strtoupper(Str::uuid()->toString()),
            'status' => 'sent',
            'signers' => $signers,
        ]);

        return [
            'success' => true,
            'external_id' => 'MANUAL-' . strtoupper(Str::uuid()->toString()),
            'provider' => 'manual',
            'note' => 'Dokumen dikirim untuk tanda tangan manual. Provider eksternal tidak tersedia.',
        ];
    }
}
