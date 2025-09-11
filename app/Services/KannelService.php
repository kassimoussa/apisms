<?php

namespace App\Services;

use App\Models\SmsMessage;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
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
    private int $retryAttempts;
    private int $retryDelay;

    public function __construct()
    {
        $this->client = new Client([
            'verify' => false,
            'http_errors' => false,
        ]);
        $this->baseUrl = config('services.kannel.url') ?: 'http://localhost:13013/cgi-bin/sendsms';
        $this->username = config('services.kannel.username') ?: 'default_user';
        $this->password = config('services.kannel.password') ?: 'default_pass';
        $this->from = config('services.kannel.from') ?: '+253XXXXXXXX';
        $this->timeout = config('services.kannel.timeout', 30);
        $this->retryAttempts = config('services.kannel.retry_attempts', 3);
        $this->retryDelay = config('services.kannel.retry_delay', 2);
    }

    /**
     * Send SMS via Kannel with retry logic
     */
    public function sendSms(string $to, string $text, ?string $from = null): array
    {
        $from = $from ?? $this->from;
        $requestId = uniqid('sms_', true);
        
        // Format phone number for Djibouti
        $to = $this->formatPhoneNumber($to);
        
        // Format sender (only if it looks like a phone number)
        if ($from && $this->isValidPhoneNumber($from)) {
            $from = $this->formatPhoneNumber($from);
        }
        
        $params = [
            'username' => $this->username,
            'password' => $this->password,
            'from' => $from,
            'to' => $to,
            'text' => $text,
            'dlr-mask' => '31', // Request delivery reports
            'dlr-url' => route('webhooks.kannel.dlr', ['id' => '%i', 'status' => '%d']),
        ];

        Log::info('SMS send request initiated', [
            'request_id' => $requestId,
            'to' => $to,
            'from' => $from,
            'length' => strlen($text),
            'url' => $this->baseUrl,
            'retry_attempts' => $this->retryAttempts,
        ]);

        return $this->executeWithRetry(function() use ($params, $to, $from, $requestId) {
            return $this->makeKannelRequest($params, $to, $from, $requestId);
        }, $requestId);
    }

    /**
     * Execute request with retry logic
     */
    private function executeWithRetry(callable $callback, string $requestId): array
    {
        $lastException = null;
        
        for ($attempt = 1; $attempt <= $this->retryAttempts; $attempt++) {
            try {
                Log::debug('SMS send attempt', [
                    'request_id' => $requestId,
                    'attempt' => $attempt,
                    'max_attempts' => $this->retryAttempts,
                ]);

                $result = $callback();
                
                if ($result['success'] || $attempt === $this->retryAttempts) {
                    if ($result['success']) {
                        Log::info('SMS send successful', [
                            'request_id' => $requestId,
                            'attempt' => $attempt,
                            'kannel_id' => $result['kannel_id'] ?? null,
                        ]);
                    }
                    return $result;
                }
                
                // If not success and not last attempt, retry
                Log::warning('SMS send failed, retrying', [
                    'request_id' => $requestId,
                    'attempt' => $attempt,
                    'error_code' => $result['error_code'] ?? 'UNKNOWN',
                    'error_message' => $result['error_message'] ?? 'Unknown error',
                    'next_attempt_in' => $this->retryDelay . 's',
                ]);
                
                if ($attempt < $this->retryAttempts) {
                    sleep($this->retryDelay);
                }
                
            } catch (GuzzleException $e) {
                $lastException = $e;
                Log::warning('SMS send network error', [
                    'request_id' => $requestId,
                    'attempt' => $attempt,
                    'error' => $e->getMessage(),
                    'error_type' => get_class($e),
                ]);
                
                if ($attempt < $this->retryAttempts) {
                    sleep($this->retryDelay);
                } else {
                    Log::error('SMS send failed after all retry attempts', [
                        'request_id' => $requestId,
                        'total_attempts' => $attempt,
                        'final_error' => $e->getMessage(),
                    ]);
                }
            }
        }

        // All attempts failed
        return [
            'success' => false,
            'error_code' => 'MAX_RETRIES_EXCEEDED',
            'error_message' => 'Failed after ' . $this->retryAttempts . ' attempts. Last error: ' . 
                ($lastException ? $lastException->getMessage() : 'Unknown error'),
            'kannel_id' => null,
        ];
    }

    /**
     * Make actual HTTP request to Kannel
     */
    private function makeKannelRequest(array $params, string $to, string $from, string $requestId): array
    {
        $startTime = microtime(true);
        
        $response = $this->client->get($this->baseUrl, [
            'query' => $params,
            'timeout' => $this->timeout,
        ]);

        $endTime = microtime(true);
        $duration = round(($endTime - $startTime) * 1000, 2);
        
        $body = $response->getBody()->getContents();
        $statusCode = $response->getStatusCode();

        Log::info('Kannel HTTP response', [
            'request_id' => $requestId,
            'status_code' => $statusCode,
            'response_time_ms' => $duration,
            'response_body' => $body,
            'to' => $to,
            'from' => $from,
        ]);

        return $this->parseKannelResponse($body, $statusCode, $requestId);
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
     * Parse Kannel response with detailed error code mapping
     */
    private function parseKannelResponse(string $body, int $statusCode, string $requestId): array
    {
        // Kannel typically returns:
        // - "0: Accepted for delivery" for success
        // - "3: System Error" or other codes for errors

        // Accept both 200 (OK) and 202 (Accepted) as success
        if (!in_array($statusCode, [200, 202])) {
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

            Log::debug('Kannel response parsed', [
                'request_id' => $requestId,
                'kannel_code' => $code,
                'kannel_message' => $message,
            ]);

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

            // Map Kannel error codes to meaningful messages
            $errorMapping = $this->getKannelErrorMapping();
            $errorMessage = $errorMapping[$code] ?? $message;
            
            Log::warning('Kannel returned error code', [
                'request_id' => $requestId,
                'kannel_code' => $code,
                'original_message' => $message,
                'mapped_message' => $errorMessage,
            ]);

            return [
                'success' => false,
                'error_code' => 'KANNEL_' . $code,
                'error_message' => $errorMessage,
                'kannel_id' => null,
            ];
        }

        // Unrecognized response format
        Log::error('Could not parse Kannel response', [
            'request_id' => $requestId,
            'status_code' => $statusCode,
            'response_body' => $body,
        ]);
        
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

        // If it's a national format (6-8 digits), prepend +253
        if (preg_match('/^\d{6,8}$/', $number)) {
            return '+253' . $number;
        }

        // Otherwise, return as is (could be international)
        return $number;
    }

    /**
     * Validate phone number format (flexible)
     */
    public function isValidPhoneNumber(string $number): bool
    {
        // Remove spaces and special characters for validation
        $clean = preg_replace('/[^\d+]/', '', $number);
        
        // Accept various formats:
        // +253XXXXXXXX (international)
        // 253XXXXXXXX (without +)
        // 77XXXXXX, 70XXXXXX etc (national 8 digits)
        // 7XXXXXX, 6XXXXXX etc (national 6-7 digits)
        return preg_match('/^(\+?253\d{8}|\d{6,8})$/', $clean) === 1;
    }

    /**
     * Validate sender (number or text)
     */
    public function isValidSender(string $sender): bool
    {
        // Allow text sender (alphanumeric, max 11 chars)
        if (preg_match('/^[a-zA-Z0-9]{1,11}$/', $sender)) {
            return true;
        }
        
        // Or allow phone number format
        return $this->isValidPhoneNumber($sender);
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
                'retry_attempts' => $this->retryAttempts,
                'retry_delay' => $this->retryDelay,
            ],
        ];
    }

    /**
     * Get Kannel error code mapping
     */
    private function getKannelErrorMapping(): array
    {
        return [
            0 => 'Message accepted for delivery',
            1 => 'Message buffered for later delivery',
            2 => 'Message rejected',
            3 => 'System error',
            4 => 'System full (try later)',
            5 => 'Invalid destination number',
            6 => 'Invalid source number',
            7 => 'Invalid message length',
            8 => 'Invalid message content',
            9 => 'Invalid message type',
            10 => 'Connection failed',
            11 => 'Submit failed',
            12 => 'Throttling error',
            13 => 'Account suspended',
            14 => 'Insufficient credits',
            15 => 'Invalid credentials',
            16 => 'Message expired',
            17 => 'Message cancelled',
            18 => 'Temporary routing error',
            19 => 'Permanent routing error',
            20 => 'Subscriber absent',
            21 => 'Subscriber busy',
            22 => 'Equipment protocol error',
            23 => 'Equipment not provisioned',
            24 => 'Unknown subscriber',
            25 => 'Illegal subscriber',
            26 => 'Teleservice not provisioned',
            27 => 'Teleservice not available',
            28 => 'Message waiting',
            29 => 'Memory capacity exceeded',
            30 => 'Invalid message reference',
            31 => 'Message integrity error',
        ];
    }
}