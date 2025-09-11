<?php

namespace App\Jobs;

use App\Models\SmsMessage;
use App\Models\DeliveryReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProcessDeliveryReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    public function __construct(
        private array $deliveryData
    ) {
        $this->onQueue('dlr');
    }

    public function handle(): void
    {
        $requestId = uniqid('dlr_job_', true);
        
        Log::info('DLR processing job started', [
            'request_id' => $requestId,
            'delivery_data' => $this->deliveryData,
        ]);

        try {
            // Extract data from delivery report
            $smsId = $this->deliveryData['id'] ?? null;
            $status = $this->deliveryData['status'] ?? null;
            $statusText = $this->deliveryData['status_text'] ?? '';
            $timestamp = $this->deliveryData['timestamp'] ?? now();
            $errorCode = $this->deliveryData['error_code'] ?? null;
            
            if (!$smsId) {
                Log::warning('DLR job missing SMS ID', [
                    'request_id' => $requestId,
                    'delivery_data' => $this->deliveryData,
                ]);
                return;
            }

            // Find SMS message by Kannel ID or SMS ID
            $smsMessage = SmsMessage::where('kannel_id', $smsId)
                ->orWhere('id', $smsId)
                ->first();

            if (!$smsMessage) {
                Log::warning('DLR job - SMS message not found', [
                    'request_id' => $requestId,
                    'sms_id' => $smsId,
                ]);
                return;
            }

            // Create delivery report record
            $deliveryReport = DeliveryReport::create([
                'sms_message_id' => $smsMessage->id,
                'status' => $this->mapDeliveryStatus($status),
                'status_text' => $statusText,
                'error_code' => $errorCode,
                'received_at' => $timestamp instanceof Carbon ? $timestamp : Carbon::parse($timestamp),
                'raw_data' => $this->deliveryData,
            ]);

            Log::info('DLR record created', [
                'request_id' => $requestId,
                'dlr_id' => $deliveryReport->id,
                'sms_id' => $smsMessage->id,
                'status' => $deliveryReport->status,
            ]);

            // Update SMS message status based on delivery report
            $this->updateSmsMessageStatus($smsMessage, $deliveryReport, $requestId);

        } catch (\Exception $e) {
            Log::error('DLR job exception', [
                'request_id' => $requestId,
                'error' => $e->getMessage(),
                'delivery_data' => $this->deliveryData,
            ]);
            throw $e;
        }
    }

    public function failed(?\Exception $exception): void
    {
        Log::error('DLR job failed permanently', [
            'delivery_data' => $this->deliveryData,
            'error' => $exception?->getMessage(),
        ]);
    }

    /**
     * Update SMS message status based on delivery report
     */
    private function updateSmsMessageStatus(SmsMessage $smsMessage, DeliveryReport $deliveryReport, string $requestId): void
    {
        switch ($deliveryReport->status) {
            case 'delivered':
                if ($smsMessage->status !== 'delivered') {
                    $smsMessage->markAsDelivered($deliveryReport->received_at);
                    Log::info('SMS marked as delivered', [
                        'request_id' => $requestId,
                        'sms_id' => $smsMessage->id,
                    ]);
                }
                break;

            case 'failed':
            case 'expired':
            case 'rejected':
                if (!in_array($smsMessage->status, ['failed', 'delivered'])) {
                    $smsMessage->markAsFailed(
                        $deliveryReport->error_code,
                        $deliveryReport->status_text
                    );
                    Log::info('SMS marked as failed from DLR', [
                        'request_id' => $requestId,
                        'sms_id' => $smsMessage->id,
                        'dlr_status' => $deliveryReport->status,
                    ]);
                }
                break;

            case 'pending':
            case 'buffered':
            case 'sent':
                // Keep current status if already delivered or failed
                if (!in_array($smsMessage->status, ['delivered', 'failed'])) {
                    Log::debug('SMS status unchanged from DLR', [
                        'request_id' => $requestId,
                        'sms_id' => $smsMessage->id,
                        'current_status' => $smsMessage->status,
                        'dlr_status' => $deliveryReport->status,
                    ]);
                }
                break;
        }
    }

    /**
     * Map Kannel delivery status codes to our internal status
     */
    private function mapDeliveryStatus(?string $status): string
    {
        return match ($status) {
            '1', 'delivered', 'DELIVRD' => 'delivered',
            '2', 'failed', 'FAILED' => 'failed',
            '4', 'buffered', 'BUFFERED' => 'buffered',
            '8', 'sent', 'SENT' => 'sent',
            '16', 'expired', 'EXPIRED' => 'expired',
            '32', 'rejected', 'REJECTD' => 'rejected',
            default => 'pending',
        };
    }
}