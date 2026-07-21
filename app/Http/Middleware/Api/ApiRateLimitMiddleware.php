<?php

namespace App\Http\Middleware\Api;

use App\Services\ApiHubService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiRateLimitMiddleware
{
    public function __construct(protected ApiHubService $apiHub) {}

    public function handle(Request $request, Closure $next): Response
    {
        $header = $request->header('Authorization');

        if (! $header || ! str_starts_with($header, 'Bearer ')) {
            return $next($request);
        }

        $token = substr($header, 7);

        if (! $this->apiHub->checkRateLimit($token)) {
            $apiKey = $this->apiHub->findKeyByToken($token);

            return response()->json([
                'success' => false,
                'message' => 'Rate limit exceeded. Maksimal ' . ($apiKey->rate_limit ?? 60) . ' request per menit.',
                'retry_after' => 60,
            ], 429)->withHeaders([
                'X-RateLimit-Limit' => $apiKey->rate_limit ?? 60,
                'X-RateLimit-Remaining' => 0,
                'Retry-After' => 60,
            ]);
        }

        $response = $next($request);

        $apiKey = $this->apiHub->findKeyByToken($token);
        if ($apiKey && $response instanceof Response) {
            $cacheKey = "api_rate_limit:{$apiKey->id}";
            $current = cache()->get($cacheKey, 0);
            $response->headers->set('X-RateLimit-Limit', $apiKey->rate_limit);
            $response->headers->set('X-RateLimit-Remaining', max(0, $apiKey->rate_limit - $current - 1));
        }

        return $response;
    }
}
