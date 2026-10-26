<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies the visitor's chosen locale (stored in the session by LocaleController)
 * so server-rendered text, Livewire re-renders and validation messages all match.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale');

        if ($locale && array_key_exists($locale, config('app.supported_locales'))) {
            App::setLocale($locale);
        }

        return $next($request);
    }
}
