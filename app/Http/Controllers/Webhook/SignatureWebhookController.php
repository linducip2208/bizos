<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Services\DigitalSignatureService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SignatureWebhookController extends Controller
{
    public function handle(Request $request, DigitalSignatureService $service)
    {
        $provider = $request->route('provider') ?? $request->input('provider') ?? 'unknown';
        $payload = $request->all();

        Log::info('Signature webhook received', [
            'provider' => $provider,
            'headers' => $request->headers->all(),
            'payload_keys' => array_keys($payload),
        ]);

        try {
            $service->handleCallback($provider, $payload);
            return response()->json(['status' => 'ok']);
        } catch (\Exception $e) {
            Log::error('Signature webhook error: ' . $e->getMessage(), [
                'provider' => $provider,
                'exception' => $e,
            ]);
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
