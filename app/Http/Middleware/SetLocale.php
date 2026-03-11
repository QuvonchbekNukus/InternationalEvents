<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supportedLocales = array_keys(config('app.supported_locales', []));
        $defaultLocale = config('app.locale', 'uz');
        $locale = (string) ($request->session()->get('locale') ?: $request->cookie('locale') ?: $defaultLocale);

        if (! in_array($locale, $supportedLocales, true)) {
            $locale = $defaultLocale;
        }

        if ($request->session()->get('locale') !== $locale) {
            $request->session()->put('locale', $locale);
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
