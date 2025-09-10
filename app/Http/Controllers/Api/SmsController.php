<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\SendSmsRequest;
use App\Http\Resources\SmsMessageResource;
use App\Models\SmsMessage;
use App\Services\KannelService;
use Illuminate\Support\Facades\Log;

class SmsController extends Controller
{
    protected KannelService $kannelService;

    public function __construct(KannelService $kannelService)
    {
        $this->kannelService = $kannelService;
    }

    /**
     * Send SMS
     * 
     * @group SMS API
     * @bodyParam to string required The recipient phone number (+253XXXXXXXX). Example: +25377123456
     * @bodyParam message string required The SMS message content (max 160 chars). Example: Hello from ApiSMS!
     * @bodyParam from string optional Custom sender number. Example: +25377000000
     */
    public function send(SendSmsRequest $request)
    {
        $client = $request->attributes->get('client');
        
        // Create SMS message record
        $smsMessage = SmsMessage::create([
            'client_id' => $client->id,
            'direction' => 'outbound',
            'from' => $request->input('from') ?: config('services.kannel.from'),
            'to' => $request->input('to'),
            'content' => $request->input('message'),
            'status' => 'pending',
        ]);

        try {
            // Send via Kannel
            $result = $this->kannelService->sendSms(
                $smsMessage->to,
                $smsMessage->content,
                $smsMessage->from
            );

            if ($result['success']) {
                // Update message status
                $smsMessage->markAsSent($result['kannel_id'] ?? null);
                
                Log::info('SMS sent successfully', [
                    'sms_id' => $smsMessage->id,
                    'client_id' => $client->id,
                    'to' => $smsMessage->to,
                    'kannel_id' => $result['kannel_id'],
                ]);

                return new SmsMessageResource($smsMessage->fresh());

            } else {
                // Mark as failed
                $smsMessage->markAsFailed(
                    $result['error_code'] ?? 'UNKNOWN',
                    $result['error_message'] ?? 'Unknown error'
                );

                Log::error('SMS sending failed', [
                    'sms_id' => $smsMessage->id,
                    'client_id' => $client->id,
                    'error_code' => $result['error_code'],
                    'error_message' => $result['error_message'],
                ]);

                return response()->json([
                    'error' => 'SMS sending failed',
                    'code' => $result['error_code'],
                    'message' => $result['error_message'],
                    'sms' => new SmsMessageResource($smsMessage->fresh()),
                ], 422);
            }

        } catch (\Exception $e) {
            $smsMessage->markAsFailed('EXCEPTION', $e->getMessage());
            
            Log::error('SMS sending exception', [
                'sms_id' => $smsMessage->id,
                'client_id' => $client->id,
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Internal server error',
                'message' => 'Failed to send SMS due to server error',
                'sms' => new SmsMessageResource($smsMessage->fresh()),
            ], 500);
        }
    }

    /**
     * Get SMS status
     * 
     * @group SMS API
     * @urlParam id integer required The SMS message ID. Example: 123
     */
    public function status(Request $request, int $id)
    {
        $client = $request->attributes->get('client');
        
        $smsMessage = SmsMessage::where('id', $id)
            ->where('client_id', $client->id)
            ->first();

        if (!$smsMessage) {
            return response()->json([
                'error' => 'SMS not found',
                'message' => 'The requested SMS message was not found or does not belong to your client.',
            ], 404);
        }

        return new SmsMessageResource($smsMessage);
    }

    /**
     * List SMS messages
     * 
     * @group SMS API
     * @queryParam page integer Page number for pagination. Example: 1
     * @queryParam per_page integer Number of messages per page (max 100). Example: 20
     * @queryParam status string Filter by status (pending,sent,delivered,failed). Example: sent
     * @queryParam direction string Filter by direction (outbound,inbound). Example: outbound
     */
    public function index(Request $request)
    {
        $client = $request->attributes->get('client');
        
        $query = SmsMessage::where('client_id', $client->id)
            ->with('deliveryReports')
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('direction')) {
            $query->where('direction', $request->input('direction'));
        }

        $perPage = min($request->input('per_page', 20), 100);
        $messages = $query->paginate($perPage);

        return SmsMessageResource::collection($messages);
    }
}
