<?php

namespace App\Http\Middleware;

use App\Services\TranslationService;
use Closure;
use Illuminate\Http\Request;

class LocalizationMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $locale = session('locale', config('app.locale', 'id'));

        if ($user = auth()->user()) {
            $locale = $user->preferred_locale ?? $locale;
        }

        app()->setLocale($locale);
        return $next($request);
    }
}
