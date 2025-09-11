<?php

namespace App\Jobs;

use App\Models\SmsMessage;
use App\Services\KannelService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

class SendSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $maxExceptions = 3;
    public int $timeout = 120;
    public int $backoff = 30;

    public function __construct(
        private SmsMessage $smsMessage
    ) {
        $this->onQueue('sms');
    }

    public function handle(KannelService $kannelService): void
    {
        $requestId = uniqid('job_sms_', true);
        
        Log::info('SMS job started', [
            'request_id' => $requestId,
            'sms_id' => $this->smsMessage->id,
            'to' => $this->smsMessage->to,
            'from' => $this->smsMessage->from,
            'attempt' => $this->attempts(),
        ]);

        try {
            // Check if message is still pending
            if ($this->smsMessage->status !== 'pending') {
                Log::info('SMS job skipped - message not pending', [
                    'request_id' => $requestId,
                    'sms_id' => $this->smsMessage->id,
                    'current_status' => $this->smsMessage->status,
                ]);
                return;
            }

            // Send SMS via Kannel
            $result = $kannelService->sendSms(
                $this->smsMessage->to,
                $this->smsMessage->content,
                $this->smsMessage->from
            );

            if ($result['success']) {
                // Mark as sent
                $this->smsMessage->markAsSent($result['kannel_id'] ?? null);
                
                Log::info('SMS job completed successfully', [
                    'request_id' => $requestId,
                    'sms_id' => $this->smsMessage->id,
                    'kannel_id' => $result['kannel_id'] ?? null,
                    'attempt' => $this->attempts(),
                ]);
            } else {
                // Check if we should retry based on error code
                if ($this->shouldRetry($result['error_code'] ?? '')) {
                    Log::warning('SMS job failed - will retry', [
                        'request_id' => $requestId,
                        'sms_id' => $this->smsMessage->id,
                        'error_code' => $result['error_code'],
                        'error_message' => $result['error_message'],
                        'attempt' => $this->attempts(),
                        'max_attempts' => $this->tries,
                    ]);
                    
                    throw new Exception(
                        'SMS sending failed: ' . ($result['error_message'] ?? 'Unknown error')
                    );
                } else {
                    // Permanent failure - don't retry
                    $this->smsMessage->markAsFailed(
                        $result['error_code'] ?? 'UNKNOWN',
                        $result['error_message'] ?? 'Unknown error'
                    );
                    
                    Log::error('SMS job failed permanently', [
                        'request_id' => $requestId,
                        'sms_id' => $this->smsMessage->id,
                        'error_code' => $result['error_code'],
                        'error_message' => $result['error_message'],
                    ]);
                }
            }
        } catch (Exception $e) {
            Log::error('SMS job exception', [
                'request_id' => $requestId,
                'sms_id' => $this->smsMessage->id,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);
            
            throw $e;
        }
    }

    public function failed(?Exception $exception): void
    {
        Log::error('SMS job failed permanently after all retries', [
            'sms_id' => $this->smsMessage->id,
            'error' => $exception?->getMessage(),
            'total_attempts' => $this->attempts(),
        ]);

        // Mark message as failed if not already processed
        if ($this->smsMessage->status === 'pending') {
            $this->smsMessage->markAsFailed(
                'JOB_FAILED',
                'Job failed after ' . $this->tries . ' attempts: ' . ($exception?->getMessage() ?? 'Unknown error')
            );
        }
    }

    /**
     * Determine if we should retry based on error code
     */
    private function shouldRetry(string $errorCode): bool
    {
        // Don't retry these permanent error codes
        $permanentErrors = [
            'KANNEL_5',  // Invalid destination number
            'KANNEL_6',  // Invalid source number
            'KANNEL_7',  // Invalid message length
            'KANNEL_8',  // Invalid message content
            'KANNEL_13', // Account suspended
            'KANNEL_15', // Invalid credentials
            'KANNEL_24', // Unknown subscriber
            'KANNEL_25', // Illegal subscriber
        ];

        return !in_array($errorCode, $permanentErrors);
    }

    /**
     * Calculate the number of seconds to wait before retrying the job
     */
    public function backoff(): array
    {
        return [30, 60, 180]; // 30s, 1min, 3min
    }
}