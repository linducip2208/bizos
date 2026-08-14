<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = Session::get('locale')
            ?? $request->user()?->locale
            ?? config('app.locale', 'id');

        if (in_array($locale, config('app.available_locales', ['id', 'en']))) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
