<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\BulkSmsJob;
use App\Models\SmsMessage;
use App\Services\KannelService;
use Illuminate\Support\Facades\Log;
use Exception;

class ProcessBulkSmsJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public $timeout = 600; // 10 minutes timeout
    public $tries = 3;

    public function __construct(
        private int $bulkJobId,
        private int $batchSize = 50
    ) {
        $this->onQueue('bulk-sms');
    }

    /**
     * Execute the bulk SMS job.
     */
    public function handle(): void
    {
        $bulkJob = BulkSmsJob::find($this->bulkJobId);

        if (!$bulkJob) {
            Log::error('Bulk SMS job not found', ['bulk_job_id' => $this->bulkJobId]);
            return;
        }

        if (!$bulkJob->canStart()) {
            Log::info('Bulk SMS job cannot start', [
                'bulk_job_id' => $this->bulkJobId,
                'status' => $bulkJob->status
            ]);
            return;
        }

        try {
            $this->processBulkJob($bulkJob);
        } catch (Exception $e) {
            Log::error('Bulk SMS job failed', [
                'bulk_job_id' => $this->bulkJobId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $bulkJob->fail($e->getMessage());
            throw $e;
        }
    }

    private function processBulkJob(BulkSmsJob $bulkJob): void
    {
        Log::info('Starting bulk SMS job processing', [
            'bulk_job_id' => $bulkJob->id,
            'total_recipients' => $bulkJob->total_count
        ]);

        $bulkJob->start();

        $kannelService = app(KannelService::class);
        $recipients = $bulkJob->recipients;
        $settings = $bulkJob->settings ?? [];
        
        // Rate limiting from settings or default
        $rateLimitPerMinute = $settings['rate_limit'] ?? $bulkJob->client->rate_limit ?? 60;
        $delayBetweenMessages = 60 / $rateLimitPerMinute; // seconds between messages

        $processed = 0;
        $sent = 0;
        $failed = 0;

        foreach (array_chunk($recipients, $this->batchSize) as $batch) {
            foreach ($batch as $recipient) {
                try {
                    // Create SMS message record
                    $smsMessage = SmsMessage::create([
                        'client_id' => $bulkJob->client_id,
                        'bulk_job_id' => $bulkJob->id,
                        'direction' => 'outbound',
                        'to' => $recipient,
                        'from' => $bulkJob->from ?: 'SMS',
                        'content' => $bulkJob->content,
                        'status' => 'pending',
                    ]);
                    
                    // Fix bulk_job_id if not saved correctly
                    if (!$smsMessage->bulk_job_id) {
                        $smsMessage->bulk_job_id = $bulkJob->id;
                        $smsMessage->save();
                    }

                    // Send via Kannel
                    $result = $kannelService->sendSms(
                        $recipient,
                        $bulkJob->content,
                        $bulkJob->from ?: 'SMS'
                    );

                    if ($result['success']) {
                        $smsMessage->markAsSent($result['kannel_id']);
                        $sent++;
                        
                        Log::debug('Bulk SMS sent', [
                            'bulk_job_id' => $bulkJob->id,
                            'sms_id' => $smsMessage->id,
                            'to' => $recipient
                        ]);
                    } else {
                        $smsMessage->markAsFailed(null, $result['error'] ?? 'Kannel send failed');
                        $failed++;
                        
                        Log::warning('Bulk SMS failed', [
                            'bulk_job_id' => $bulkJob->id,
                            'sms_id' => $smsMessage->id,
                            'to' => $recipient,
                            'error' => $result['error'] ?? 'Unknown error'
                        ]);
                    }

                    $processed++;

                    // Update progress every 10 messages
                    if ($processed % 10 === 0) {
                        $bulkJob->update([
                            'sent_count' => $sent,
                            'failed_count' => $failed,
                        ]);
                        $bulkJob->updateProgress();
                    }

                    // Rate limiting
                    if ($delayBetweenMessages > 0) {
                        usleep($delayBetweenMessages * 1000000);
                    }

                    // Check if job was paused
                    $bulkJob->refresh();
                    if ($bulkJob->status === 'paused') {
                        Log::info('Bulk SMS job paused', ['bulk_job_id' => $bulkJob->id]);
                        return;
                    }

                } catch (Exception $e) {
                    $failed++;
                    Log::error('Error processing bulk SMS recipient', [
                        'bulk_job_id' => $bulkJob->id,
                        'recipient' => $recipient,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Small break between batches
            sleep(1);
        }

        // Final update
        $bulkJob->update([
            'sent_count' => $sent,
            'failed_count' => $failed,
        ]);
        $bulkJob->updateProgress();

        Log::info('Bulk SMS job completed', [
            'bulk_job_id' => $bulkJob->id,
            'total_processed' => $processed,
            'sent' => $sent,
            'failed' => $failed,
            'success_rate' => $bulkJob->success_rate
        ]);
    }

    /**
     * Handle job failure.
     */
    public function failed(?Throwable $exception): void
    {
        Log::error('Bulk SMS job failed permanently', [
            'bulk_job_id' => $this->bulkJobId,
            'exception' => $exception?->getMessage(),
            'attempts' => $this->attempts()
        ]);

        $bulkJob = BulkSmsJob::find($this->bulkJobId);
        if ($bulkJob) {
            $bulkJob->fail($exception?->getMessage() ?? 'Job failed after maximum attempts');
        }
    }
}
