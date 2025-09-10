<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class AuthenticateApiClient
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get API key from header
        $apiKey = $request->header('X-API-Key') ?? $request->bearerToken();

        if (!$apiKey) {
            return response()->json([
                'error' => 'API key required',
                'message' => 'Please provide an API key in X-API-Key header or Authorization Bearer token.',
            ], 401);
        }

        // Find the client
        $client = Client::where('api_key', $apiKey)->active()->first();

        if (!$client) {
            Log::warning('Invalid API key attempted', [
                'api_key' => substr($apiKey, 0, 8) . '...',
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'error' => 'Invalid API key',
                'message' => 'The provided API key is invalid or inactive.',
            ], 401);
        }

        // Check IP restriction
        if (!$client->isIpAllowed($request->ip())) {
            Log::warning('IP not allowed for client', [
                'client_id' => $client->id,
                'client_name' => $client->name,
                'ip' => $request->ip(),
                'allowed_ips' => $client->allowed_ips,
            ]);

            return response()->json([
                'error' => 'IP not allowed',
                'message' => 'Your IP address is not authorized to use this API key.',
            ], 403);
        }

        // Apply rate limiting
        $rateLimitKey = 'api_client:' . $client->id;
        $maxAttempts = $client->rate_limit;
        $decayMinutes = 1; // Per minute

        if (RateLimiter::tooManyAttempts($rateLimitKey, $maxAttempts)) {
            $retryAfter = RateLimiter::availableIn($rateLimitKey);
            
            Log::info('Rate limit exceeded', [
                'client_id' => $client->id,
                'client_name' => $client->name,
                'ip' => $request->ip(),
                'rate_limit' => $maxAttempts,
            ]);

            return response()->json([
                'error' => 'Rate limit exceeded',
                'message' => "Too many requests. Try again in {$retryAfter} seconds.",
                'retry_after' => $retryAfter,
                'rate_limit' => $maxAttempts,
            ], 429);
        }

        // Increment rate limit counter
        RateLimiter::hit($rateLimitKey, $decayMinutes * 60);

        // Add client to request for use in controllers
        $request->attributes->set('client', $client);

        // Log successful request
        Log::info('API request authenticated', [
            'client_id' => $client->id,
            'client_name' => $client->name,
            'ip' => $request->ip(),
            'endpoint' => $request->path(),
            'method' => $request->method(),
        ]);

        return $next($request);
    }
}
