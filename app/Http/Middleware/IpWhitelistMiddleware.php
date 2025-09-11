<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class IpWhitelistMiddleware
{
    /**
     * Handle an incoming request for webhook endpoints
     */
    public function handle(Request $request, Closure $next): Response
    {
        $allowedIps = $this->getAllowedIps();
        $clientIp = $this->getClientIp($request);

        if (!$this->isIpAllowed($clientIp, $allowedIps)) {
            Log::warning('Webhook IP not whitelisted', [
                'ip' => $clientIp,
                'allowed_ips' => $allowedIps,
                'endpoint' => $request->path(),
                'user_agent' => $request->userAgent(),
                'headers' => $request->headers->all(),
            ]);

            return response()->json([
                'error' => 'Forbidden',
                'message' => 'IP address not authorized for this endpoint',
            ], 403);
        }

        Log::info('Webhook request from authorized IP', [
            'ip' => $clientIp,
            'endpoint' => $request->path(),
        ]);

        return $next($request);
    }

    /**
     * Get allowed IPs from configuration
     */
    private function getAllowedIps(): array
    {
        $ips = config('security.webhook_ip_whitelist', '127.0.0.1,::1');
        
        if (is_string($ips)) {
            $ips = explode(',', $ips);
        }
        
        return array_map('trim', $ips);
    }

    /**
     * Get the real client IP address
     */
    private function getClientIp(Request $request): string
    {
        // Check for various headers that might contain the real IP
        $headers = [
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'HTTP_CLIENT_IP',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED',
        ];

        foreach ($headers as $header) {
            $value = $request->server($header);
            if ($value) {
                // Handle comma-separated IPs (X-Forwarded-For can contain multiple IPs)
                $ips = explode(',', $value);
                $ip = trim($ips[0]);
                
                if ($this->isValidIp($ip)) {
                    return $ip;
                }
            }
        }

        return $request->ip() ?? '127.0.0.1';
    }

    /**
     * Check if IP is in the whitelist
     */
    private function isIpAllowed(string $clientIp, array $allowedIps): bool
    {
        foreach ($allowedIps as $allowedIp) {
            $allowedIp = trim($allowedIp);
            
            // Exact match
            if ($clientIp === $allowedIp) {
                return true;
            }
            
            // CIDR notation support
            if (strpos($allowedIp, '/') !== false) {
                if ($this->ipInRange($clientIp, $allowedIp)) {
                    return true;
                }
            }
            
            // Wildcard support (e.g., 192.168.1.*)
            if (strpos($allowedIp, '*') !== false) {
                $pattern = str_replace(['*', '.'], ['.*', '\.'], $allowedIp);
                if (preg_match("/^{$pattern}$/", $clientIp)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if IP is in CIDR range
     */
    private function ipInRange(string $ip, string $range): bool
    {
        if (!strpos($range, '/')) {
            return false;
        }

        list($subnet, $bits) = explode('/', $range);
        
        if (!$this->isValidIp($ip) || !$this->isValidIp($subnet)) {
            return false;
        }

        $ip = ip2long($ip);
        $subnet = ip2long($subnet);
        $mask = -1 << (32 - (int)$bits);
        $subnet &= $mask; # nb: in case the supplied subnet wasn't correctly aligned

        return ($ip & $mask) == $subnet;
    }

    /**
     * Validate IP address
     */
    private function isValidIp(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6) !== false;
    }
}