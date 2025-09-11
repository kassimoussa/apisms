<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AuditLogMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        
        // Log request
        $this->logRequest($request, $startTime);
        
        $response = $next($request);
        
        // Log response
        $this->logResponse($request, $response, $startTime);
        
        return $response;
    }

    /**
     * Log incoming request
     */
    private function logRequest(Request $request, float $startTime): void
    {
        $logData = [
            'type' => 'api_request',
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'path' => $request->path(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referer' => $request->header('Referer'),
            'timestamp' => now()->toISOString(),
            'request_id' => $request->header('X-Request-ID') ?: uniqid(),
            'client_info' => $this->getClientInfo($request),
        ];

        // Add authenticated client info if available
        if ($request->attributes->has('client')) {
            $client = $request->attributes->get('client');
            $logData['client_id'] = $client->id;
            $logData['client_name'] = $client->name;
        }

        // Log sensitive endpoints with extra detail
        if ($this->isSensitiveEndpoint($request)) {
            $logData['sensitive_endpoint'] = true;
            $logData['headers'] = $this->filterSensitiveHeaders($request->headers->all());
        }

        // Don't log request body for large requests or file uploads
        if ($request->getContentLength() < 10000 && !$request->hasFile()) {
            $logData['request_data'] = $this->filterSensitiveData($request->all());
        }

        Log::channel('audit')->info('API Request', $logData);
    }

    /**
     * Log response
     */
    private function logResponse(Request $request, Response $response, float $startTime): void
    {
        $duration = round((microtime(true) - $startTime) * 1000, 2);
        
        $logData = [
            'type' => 'api_response',
            'status' => $response->getStatusCode(),
            'duration_ms' => $duration,
            'content_length' => $response->headers->get('Content-Length'),
            'timestamp' => now()->toISOString(),
            'request_id' => $request->header('X-Request-ID') ?: uniqid(),
        ];

        // Add client info if available
        if ($request->attributes->has('client')) {
            $client = $request->attributes->get('client');
            $logData['client_id'] = $client->id;
        }

        // Log errors with additional detail
        if ($response->getStatusCode() >= 400) {
            $logData['error'] = true;
            
            // Try to decode JSON error response
            $content = $response->getContent();
            if ($content && $this->isJson($content)) {
                $decoded = json_decode($content, true);
                if ($decoded && isset($decoded['error'])) {
                    $logData['error_message'] = $decoded['error'];
                    $logData['error_code'] = $decoded['code'] ?? null;
                }
            }
        }

        // Log slow requests
        if ($duration > 1000) {
            $logData['slow_request'] = true;
            Log::channel('audit')->warning('Slow API Response', $logData);
        } else {
            Log::channel('audit')->info('API Response', $logData);
        }
    }

    /**
     * Get client information
     */
    private function getClientInfo(Request $request): array
    {
        return [
            'ip' => $request->ip(),
            'forwarded_ip' => $request->header('X-Forwarded-For'),
            'real_ip' => $request->header('X-Real-IP'),
            'user_agent' => $request->userAgent(),
            'accept_language' => $request->header('Accept-Language'),
            'country' => $request->header('CF-IPCountry'), // Cloudflare
            'is_mobile' => $this->isMobileRequest($request),
        ];
    }

    /**
     * Check if endpoint is sensitive
     */
    private function isSensitiveEndpoint(Request $request): bool
    {
        $sensitivePatterns = [
            '/api/v1/sms/send',
            '/webhooks/',
            '/admin/',
        ];

        $path = $request->path();
        
        foreach ($sensitivePatterns as $pattern) {
            if (strpos($path, trim($pattern, '/')) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Filter sensitive headers
     */
    private function filterSensitiveHeaders(array $headers): array
    {
        $sensitiveHeaders = [
            'authorization',
            'x-api-key',
            'cookie',
            'x-csrf-token',
        ];

        $filtered = [];
        foreach ($headers as $key => $value) {
            $lowerKey = strtolower($key);
            if (in_array($lowerKey, $sensitiveHeaders)) {
                $filtered[$key] = ['[REDACTED]'];
            } else {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }

    /**
     * Filter sensitive data from request
     */
    private function filterSensitiveData(array $data): array
    {
        $sensitiveFields = [
            'password',
            'password_confirmation',
            'token',
            'api_key',
            'secret',
            'key',
        ];

        $filtered = [];
        foreach ($data as $key => $value) {
            $lowerKey = strtolower($key);
            if (in_array($lowerKey, $sensitiveFields)) {
                $filtered[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $filtered[$key] = $this->filterSensitiveData($value);
            } else {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }

    /**
     * Check if request is from mobile device
     */
    private function isMobileRequest(Request $request): bool
    {
        $userAgent = $request->userAgent();
        $mobileKeywords = [
            'Mobile', 'Android', 'iPhone', 'iPad', 'iPod', 
            'BlackBerry', 'IEMobile', 'Kindle', 'NetFront', 
            'Silk-Accelerated', 'hpwOS', 'webOS', 'Fennec', 
            'Minimo', 'Opera Mobi', 'Opera Mini'
        ];

        foreach ($mobileKeywords as $keyword) {
            if (strpos($userAgent, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if content is JSON
     */
    private function isJson(string $content): bool
    {
        json_decode($content);
        return json_last_error() === JSON_ERROR_NONE;
    }
}