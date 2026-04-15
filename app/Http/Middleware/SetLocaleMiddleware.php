<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocaleMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $locale = $request->header('Accept-Language', config('app.locale'));
        
        $locale = explode(',', $locale)[0];
        $locale = explode('-', $locale)[0];

        if (in_array($locale, config('app.available_locales', ['en', 'ar']))) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
