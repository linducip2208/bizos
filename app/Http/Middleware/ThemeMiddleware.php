<?php

namespace App\Http\Middleware;

use App\Models\CompanyTheme;
use App\Services\ThemeBuilderService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ThemeMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return $next($request);
        }

        $companyId = auth()->user()->company_id;

        if (!$companyId) {
            return $next($request);
        }

        $theme = Cache::remember("company_theme_{$companyId}", 3600, function () use ($companyId) {
            return CompanyTheme::where('company_id', $companyId)
                ->where('is_active', true)
                ->first();
        });

        if ($theme) {
            $service = app(ThemeBuilderService::class);
            $css = $service->getCssForCompany($companyId);

            if ($css) {
                view()->share('companyThemeCss', $css);
                view()->share('companyTheme', $theme->toArray());
            }
        }

        return $next($request);
    }
}
