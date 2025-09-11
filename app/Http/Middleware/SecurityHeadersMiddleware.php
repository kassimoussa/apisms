<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Force HTTPS in production
        if (config('app.env') === 'production' && config('security.force_https', true)) {
            if (!$request->secure() && !$request->header('X-Forwarded-Proto') === 'https') {
                return redirect()->secure($request->getRequestUri(), 301);
            }
        }

        // Security headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
        
        // Strict Transport Security (HSTS)
        if ($request->secure() || config('security.force_https')) {
            $response->headers->set(
                'Strict-Transport-Security', 
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        // Content Security Policy
        $csp = $this->getContentSecurityPolicy();
        if ($csp) {
            $response->headers->set('Content-Security-Policy', $csp);
        }

        // Remove server information
        $response->headers->remove('Server');
        $response->headers->remove('X-Powered-By');

        return $response;
    }

    /**
     * Get Content Security Policy header value
     */
    private function getContentSecurityPolicy(): string
    {
        $policies = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' cdn.jsdelivr.net",
            "style-src 'self' 'unsafe-inline' fonts.googleapis.com cdn.jsdelivr.net",
            "font-src 'self' fonts.gstatic.com",
            "img-src 'self' data: blob:",
            "connect-src 'self'",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "form-action 'self'",
        ];

        return implode('; ', $policies);
    }
}