<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WhatsappBusinessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    public function verify(Request $request, WhatsappBusinessService $waService)
    {
        $mode = $request->query('hub_mode', '');
        $token = $request->query('hub_verify_token', '');
        $challenge = $request->query('hub_challenge', '');

        $result = $waService->verifyWebhook($mode, $token, $challenge);

        if ($result) {
            return response($result, 200)->header('Content-Type', 'text/plain');
        }

        return response('Verification failed', 403);
    }

    public function receive(Request $request, WhatsappBusinessService $waService)
    {
        $payload = $request->all();

        Log::info('WA webhook received', ['payload' => json_encode($payload)]);

        try {
            $waService->handleWebhook($payload);
        } catch (\Exception $e) {
            Log::error('WA webhook processing error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return response('OK', 200);
    }
}
