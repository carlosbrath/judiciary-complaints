<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * The admin panel is English only, whatever language the visitor picked on the public site.
 * Registered as Livewire persistent middleware so component updates stay in English too.
 */
class AdminLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        App::setLocale('en');

        return $next($request);
    }
}
