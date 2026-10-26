<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Browser hardening headers for every web response. */
class SecurityHeaders
{
    /**
     * Report-only for now: Livewire/Alpine need inline scripts and eval, so this is watched in the
     * browser console before being enforced.
     */
    private const CSP = "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; "
        ."style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; "
        ."img-src 'self' data: blob:; media-src 'self' blob:; connect-src 'self'; "
        ."frame-ancestors 'self'; form-action 'self'; base-uri 'self'; object-src 'none'";

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $headers = [
            'X-Frame-Options' => 'SAMEORIGIN',
            'X-Content-Type-Options' => 'nosniff',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            // Microphone only for this site (voice notes); nothing else.
            'Permissions-Policy' => 'microphone=(self), camera=(), geolocation=(), payment=(), usb=()',
            'Content-Security-Policy-Report-Only' => self::CSP,
        ];

        if ($request->isSecure()) {
            $headers['Strict-Transport-Security'] = 'max-age=31536000';
        }

        foreach ($headers as $name => $value) {
            if (! $response->headers->has($name)) {
                $response->headers->set($name, $value);
            }
        }

        return $response;
    }
}
