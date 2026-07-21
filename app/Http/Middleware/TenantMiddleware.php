<?php

namespace App\Http\Middleware;

use App\Services\TenantService;
use App\Services\TenantUsageService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();

        // Set company context
        app(TenantService::class)->applyCompanyScope();
        app(TenantService::class)->applyBranchScope($user);

        // Block suspended tenants
        if ($user->company && $user->company->is_suspended) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Akun perusahaan Anda sedang dinonaktifkan.',
                    'reason' => $user->company->suspended_reason,
                ], 403);
            }

            auth()->logout();
            return redirect()->route('login')->with('error', 'Akun perusahaan Anda sedang dinonaktifkan. Silakan hubungi administrator.');
        }

        // Log API usage for rate limiting (non-GET requests only)
        if (!$request->isMethod('GET') && !$request->is('admin/livewire/*')) {
            app(TenantUsageService::class)->recordIncrement('api_calls');
        }

        // Set tenant-specific configs
        if ($user->company_id) {
            app()->setLocale('id');
            config(['app.timezone' => 'Asia/Jakarta']);
        }

        return $next($request);
    }
}
