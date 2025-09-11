<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SmsMessage;
use App\Models\DeliveryReport;
use App\Jobs\ProcessDeliveryReportJob;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Handle Kannel delivery reports (DLR) - Queue-based processing
     * 
     * URL: /webhooks/kannel/dlr?id={kannel_id}&status={status}
     */
    public function handleDeliveryReport(Request $request)
    {
        $requestId = uniqid('webhook_dlr_', true);
        
        try {
            $kannelId = $request->query('id');
            $status = $request->query('status');
            
            Log::info('DLR webhook received', [
                'request_id' => $requestId,
                'kannel_id' => $kannelId,
                'status' => $status,
                'query_params' => $request->query(),
                'ip' => $request->ip(),
            ]);

            if (!$kannelId) {
                Log::warning('DLR webhook missing kannel_id', [
                    'request_id' => $requestId,
                ]);
                return response('Missing kannel_id', 400);
            }

            // Prepare delivery report data for async processing
            $deliveryData = [
                'id' => $kannelId,
                'status' => $status,
                'status_text' => $request->query('status_text', ''),
                'error_code' => $request->query('error_code'),
                'timestamp' => now(),
                'raw_webhook_data' => $request->query(),
            ];

            // Dispatch job to process delivery report asynchronously
            ProcessDeliveryReportJob::dispatch($deliveryData);

            Log::info('DLR job dispatched', [
                'request_id' => $requestId,
                'kannel_id' => $kannelId,
            ]);

            return response('OK', 200);

        } catch (\Exception $e) {
            Log::error('DLR webhook processing failed', [
                'request_id' => $requestId,
                'error' => $e->getMessage(),
                'kannel_id' => $request->query('id'),
                'query_params' => $request->query(),
            ]);

            return response('Internal Server Error', 500);
        }
    }

    /**
     * Handle incoming SMS messages (MO)
     * 
     * URL: /webhooks/kannel/mo
     */
    public function handleIncomingSms(Request $request)
    {
        try {
            $from = $request->query('from');
            $to = $request->query('to');
            $text = $request->query('text');
            $timestamp = $request->query('timestamp');
            
            Log::info('MO SMS webhook received', [
                'from' => $from,
                'to' => $to,
                'text' => $text,
                'timestamp' => $timestamp,
                'query_params' => $request->query(),
                'ip' => $request->ip(),
            ]);

            if (!$from || !$to || !$text) {
                Log::warning('MO webhook missing required parameters');
                return response('Missing required parameters', 400);
            }

            // Find client by the destination number (to)
            // For now, we'll use the first active client
            // In production, you might want to map numbers to clients
            $client = \App\Models\Client::active()->first();

            if (!$client) {
                Log::warning('No active client found for incoming SMS');
                return response('No client configured', 500);
            }

            // Create SMS message record for incoming message
            $smsMessage = SmsMessage::create([
                'client_id' => $client->id,
                'direction' => 'inbound',
                'from' => $from,
                'to' => $to,
                'content' => $text,
                'status' => 'delivered', // Incoming SMS is already delivered
                'delivered_at' => $timestamp ? now()->parse($timestamp) : now(),
                'metadata' => [
                    'webhook_data' => $request->query(),
                    'received_at' => now()->toISOString(),
                ],
            ]);

            Log::info('Incoming SMS stored successfully', [
                'sms_id' => $smsMessage->id,
                'client_id' => $client->id,
                'from' => $from,
                'to' => $to,
            ]);

            return response('OK', 200);

        } catch (\Exception $e) {
            Log::error('MO SMS webhook processing failed', [
                'error' => $e->getMessage(),
                'query_params' => $request->query(),
            ]);

            return response('Internal Server Error', 500);
        }
    }

    /**
     * Parse Kannel delivery status codes
     */
    private function parseDeliveryStatus(?string $status): string
    {
        return match ($status) {
            '1' => 'delivered',
            '2' => 'failed',
            '4' => 'buffered',
            '8' => 'smsc_reject',
            '16' => 'smsc_unknown',
            default => 'failed',
        };
    }
}
