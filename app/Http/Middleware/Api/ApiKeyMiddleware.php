<?php

namespace App\Http\Middleware\Api;

use App\Services\ApiHubService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyMiddleware
{
    public function __construct(protected ApiHubService $apiHub) {}

    public function handle(Request $request, Closure $next): Response
    {
        $header = $request->header('Authorization');

        if (! $header || ! str_starts_with($header, 'Bearer ')) {
            return response()->json([
                'success' => false,
                'message' => 'API key tidak ditemukan. Gunakan header Authorization: Bearer {API_KEY}',
            ], 401);
        }

        $token = substr($header, 7);

        if (empty($token)) {
            return response()->json([
                'success' => false,
                'message' => 'API key kosong.',
            ], 401);
        }

        $apiKey = $this->apiHub->findKeyByToken($token);

        if (! $apiKey || ! $apiKey->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'API key tidak valid atau sudah tidak aktif.',
            ], 401);
        }

        $apiKey->update(['last_used_at' => now()]);

        $request->attributes->set('api_key', $apiKey);
        $request->attributes->set('company_id', $apiKey->company_id);

        return $next($request);
    }
}
