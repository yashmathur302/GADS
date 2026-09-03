<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Apply baseline security headers to every response.
     *
     * These defend against clickjacking, MIME sniffing, and referrer/feature
     * leakage. They are deliberately conservative — reflecting only what this
     * admin panel actually needs — rather than a maximal policy that risks
     * breaking legitimate functionality as features are added later.
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set(
            'Permissions-Policy',
            'camera=(), microphone=(), geolocation=(), payment=(), usb=()'
        );
        $response->headers->set(
            'Content-Security-Policy',
            "default-src 'self'; ".
            // 'unsafe-eval' is required because Alpine.js (bundled, self-hosted
            // only — no third-party script origins are allowed) evaluates its
            // x-data/x-bind/x-on directive expressions via Function(). Without
            // it every Alpine directive in the admin UI silently breaks.
            "script-src 'self' 'unsafe-eval'; ".
            "style-src 'self' 'unsafe-inline' https://fonts.bunny.net; ".
            "font-src 'self' https://fonts.bunny.net; ".
            "img-src 'self' data:; ".
            "connect-src 'self'; ".
            "frame-ancestors 'none'; ".
            "base-uri 'self'; ".
            "form-action 'self'"
        );

        if ($request->secure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains'
            );
        }

        return $response;
    }
}
