<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentGatewayWebhookController extends Controller
{
    public function handle(Request $request, string $gateway, PaymentGatewayService $service)
    {
        $payload = $request->all();

        Log::info('Payment gateway webhook received', [
            'gateway' => $gateway,
            'payload_keys' => array_keys($payload),
        ]);

        try {
            $result = $service->handleCallback($gateway, $payload);

            return response()->json([
                'status' => ($result['success'] ?? false) ? 'ok' : 'error',
                'message' => $result['message'] ?? null,
                'data' => $result,
            ], ($result['success'] ?? false) ? 200 : 422);
        } catch (\Throwable $e) {
            Log::error('Payment gateway webhook error: ' . $e->getMessage(), [
                'gateway' => $gateway,
                'exception' => $e,
            ]);

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
