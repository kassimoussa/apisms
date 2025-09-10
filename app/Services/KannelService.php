<?php

namespace App\Services;

use App\Models\SmsMessage;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class KannelService
{
    private Client $client;
    private string $baseUrl;
    private string $username;
    private string $password;
    private string $from;
    private int $timeout;

    public function __construct()
    {
        $this->client = new Client();
        $this->baseUrl = config('services.kannel.url') ?: 'http://localhost:13013/cgi-bin/sendsms';
        $this->username = config('services.kannel.username') ?: 'default_user';
        $this->password = config('services.kannel.password') ?: 'default_pass';
        $this->from = config('services.kannel.from') ?: '+253XXXXXXXX';
        $this->timeout = config('services.kannel.timeout', 30);
    }

    /**
     * Send SMS via Kannel
     */
    public function sendSms(string $to, string $text, ?string $from = null): array
    {
        $from = $from ?? $this->from;
        
        // Format phone number for Djibouti
        $to = $this->formatPhoneNumber($to);
        
        $params = [
            'username' => $this->username,
            'password' => $this->password,
            'from' => $from,
            'to' => $to,
            'text' => $text,
            'dlr-mask' => '31', // Request delivery reports
            'dlr-url' => route('webhooks.kannel.dlr', ['id' => '%i', 'status' => '%d']),
        ];

        try {
            Log::info('Sending SMS via Kannel', [
                'to' => $to,
                'from' => $from,
                'length' => strlen($text),
                'url' => $this->baseUrl,
            ]);

            $response = $this->client->get($this->baseUrl, [
                'query' => $params,
                'timeout' => $this->timeout,
                'verify' => false, // Disable SSL verification for local development
            ]);

            $body = $response->getBody()->getContents();
            $statusCode = $response->getStatusCode();

            Log::info('Kannel response received', [
                'status_code' => $statusCode,
                'body' => $body,
                'to' => $to,
            ]);

            return $this->parseKannelResponse($body, $statusCode);

        } catch (GuzzleException $e) {
            Log::error('Kannel SMS sending failed', [
                'error' => $e->getMessage(),
                'to' => $to,
                'from' => $from,
            ]);

            return [
                'success' => false,
                'error_code' => 'NETWORK_ERROR',
                'error_message' => 'Failed to connect to Kannel: ' . $e->getMessage(),
                'kannel_id' => null,
            ];
        }
    }

    /**
     * Check Kannel connectivity
     */
    public function checkConnectivity(): array
    {
        $cacheKey = 'kannel_connectivity_check';
        
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $response = $this->client->get($this->baseUrl, [
                'query' => [
                    'username' => $this->username,
                    'password' => $this->password,
                    'from' => $this->from,
                    'to' => '+25300000000', // Test number
                    'text' => 'Test connectivity',
                ],
                'timeout' => 10,
                'verify' => false,
            ]);

            $result = [
                'success' => true,
                'status_code' => $response->getStatusCode(),
                'message' => 'Kannel is accessible',
            ];

        } catch (GuzzleException $e) {
            $result = [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Cannot connect to Kannel',
            ];
        }

        // Cache result for 5 minutes
        Cache::put($cacheKey, $result, now()->addMinutes(5));

        return $result;
    }

    /**
     * Parse Kannel response
     */
    private function parseKannelResponse(string $body, int $statusCode): array
    {
        // Kannel typically returns:
        // - "0: Accepted for delivery" for success
        // - "3: System Error" or other codes for errors

        if ($statusCode !== 200) {
            return [
                'success' => false,
                'error_code' => 'HTTP_' . $statusCode,
                'error_message' => 'HTTP error: ' . $statusCode,
                'kannel_id' => null,
            ];
        }

        // Parse response body
        if (preg_match('/^(\d+):\s*(.+)$/m', trim($body), $matches)) {
            $code = (int) $matches[1];
            $message = trim($matches[2]);

            if ($code === 0) {
                // Success - extract message ID if available
                $kannelId = $this->extractMessageId($body);
                
                return [
                    'success' => true,
                    'kannel_code' => $code,
                    'message' => $message,
                    'kannel_id' => $kannelId,
                ];
            }

            // Error codes
            return [
                'success' => false,
                'error_code' => 'KANNEL_' . $code,
                'error_message' => $message,
                'kannel_id' => null,
            ];
        }

        // Unrecognized response format
        return [
            'success' => false,
            'error_code' => 'PARSE_ERROR',
            'error_message' => 'Could not parse Kannel response: ' . $body,
            'kannel_id' => null,
        ];
    }

    /**
     * Extract message ID from Kannel response
     */
    private function extractMessageId(string $response): ?string
    {
        // Look for patterns like "Message ID: 123" or similar
        if (preg_match('/(?:Message ID|ID):\s*([a-zA-Z0-9\-_]+)/i', $response, $matches)) {
            return $matches[1];
        }

        // Generate a UUID if no ID found
        return (string) \Illuminate\Support\Str::uuid();
    }

    /**
     * Format phone number for Djibouti (+253)
     */
    private function formatPhoneNumber(string $number): string
    {
        // Remove any spaces, dashes, or parentheses
        $number = preg_replace('/[^\d+]/', '', $number);

        // If already starts with +253, return as is
        if (str_starts_with($number, '+253')) {
            return $number;
        }

        // If starts with +, assume it's already international
        if (str_starts_with($number, '+')) {
            return $number;
        }

        // If starts with 253, add +
        if (str_starts_with($number, '253')) {
            return '+' . $number;
        }

        // If starts with 0, replace with +253
        if (str_starts_with($number, '0')) {
            return '+253' . substr($number, 1);
        }

        // Otherwise, prepend +253
        return '+253' . $number;
    }

    /**
     * Validate phone number format
     */
    public function isValidPhoneNumber(string $number): bool
    {
        $formatted = $this->formatPhoneNumber($number);
        
        // Djibouti numbers: +253 followed by 8 digits
        return preg_match('/^\+253\d{8}$/', $formatted) === 1;
    }

    /**
     * Get formatted statistics
     */
    public function getStats(): array
    {
        return [
            'connectivity' => $this->checkConnectivity(),
            'service_info' => [
                'url' => $this->baseUrl,
                'username' => $this->username,
                'from' => $this->from,
                'timeout' => $this->timeout,
            ],
        ];
    }
}