<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\SendSmsRequest;
use App\Http\Resources\SmsMessageResource;
use App\Models\SmsMessage;
use App\Services\KannelService;
use App\Jobs\SendSmsJob;
use Illuminate\Support\Facades\Log;

class SmsController extends Controller
{
    protected KannelService $kannelService;

    public function __construct(KannelService $kannelService)
    {
        $this->kannelService = $kannelService;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/sms/send",
     *     tags={"SMS"},
     *     summary="Send SMS Message",
     *     description="Send an SMS message through the Kannel gateway. Supports both synchronous and asynchronous processing.",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"to", "message"},
     *             @OA\Property(property="to", type="string", example="77166677", description="Recipient phone number (Djibouti format: 77XXXXXX or +25377XXXXXX)"),
     *             @OA\Property(property="message", type="string", maxLength=160, example="Test SMS from ApiSMS Gateway", description="SMS message content (max 160 characters)"),
     *             @OA\Property(property="from", type="string", example="11123", description="Sender ID (can be text up to 11 chars or phone number)"),
     *             @OA\Property(property="async", type="boolean", example=false, description="Send asynchronously via queue (default: false)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="SMS sent successfully (synchronous)",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=123),
     *                 @OA\Property(property="direction", type="string", example="outbound"),
     *                 @OA\Property(property="from", type="string", example="+25311123"),
     *                 @OA\Property(property="to", type="string", example="+25377166677"),
     *                 @OA\Property(property="message", type="string", example="Test SMS from ApiSMS Gateway"),
     *                 @OA\Property(property="status", type="string", example="sent"),
     *                 @OA\Property(property="sent_at", type="string", format="datetime", example="2025-09-11T10:00:00.000000Z"),
     *                 @OA\Property(property="created_at", type="string", format="datetime", example="2025-09-11T10:00:00.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", format="datetime", example="2025-09-11T10:00:00.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=202,
     *         description="SMS queued for delivery (asynchronous)",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="SMS queued for delivery"),
     *             @OA\Property(property="async", type="boolean", example=true),
     *             @OA\Property(property="sms", type="object", description="SMS record with status 'pending'")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or SMS sending failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="SMS sending failed"),
     *             @OA\Property(property="code", type="string", example="KANNEL_5"),
     *             @OA\Property(property="message", type="string", example="Invalid destination number"),
     *             @OA\Property(property="sms", type="object", description="SMS record with failure details")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Authentication required",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=429,
     *         description="Rate limit exceeded",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Rate limit exceeded")
     *         )
     *     )
     * )
     */
    public function send(SendSmsRequest $request)
    {
        $client = $request->attributes->get('client');
        $async = $request->boolean('async', false);
        
        // Check daily SMS quota
        $dailyUsage = $client->getDailySmsUsage();
        if ($dailyUsage >= $client->daily_sms_limit) {
            return response()->json([
                'error' => 'Daily SMS quota exceeded',
                'message' => "Daily limit of {$client->daily_sms_limit} SMS reached. Used: {$dailyUsage}",
                'quota' => [
                    'daily_limit' => $client->daily_sms_limit,
                    'daily_used' => $dailyUsage,
                    'remaining' => max(0, $client->daily_sms_limit - $dailyUsage)
                ]
            ], 429); // 429 Too Many Requests
        }
        
        // Check monthly SMS quota
        $monthlyUsage = $client->getMonthlySmsUsage();
        if ($monthlyUsage >= $client->monthly_sms_limit) {
            return response()->json([
                'error' => 'Monthly SMS quota exceeded',
                'message' => "Monthly limit of {$client->monthly_sms_limit} SMS reached. Used: {$monthlyUsage}",
                'quota' => [
                    'monthly_limit' => $client->monthly_sms_limit,
                    'monthly_used' => $monthlyUsage,
                    'remaining' => max(0, $client->monthly_sms_limit - $monthlyUsage)
                ]
            ], 429); // 429 Too Many Requests
        }
        
        // Create SMS message record
        $smsMessage = SmsMessage::create([
            'client_id' => $client->id,
            'direction' => 'outbound',
            'from' => $client->sender_id ?: config('services.kannel.from'),
            'to' => $request->input('to'),
            'content' => $request->input('message'),
            'status' => 'pending',
            'metadata' => [
                'api_request' => true,
                'async_mode' => $async,
                'created_at' => now()->toISOString(),
            ],
        ]);

        Log::info('SMS API request received', [
            'sms_id' => $smsMessage->id,
            'client_id' => $client->id,
            'to' => $smsMessage->to,
            'async_mode' => $async,
        ]);

        if ($async) {
            // Async processing via queue
            SendSmsJob::dispatch($smsMessage);
            
            Log::info('SMS job dispatched', [
                'sms_id' => $smsMessage->id,
                'client_id' => $client->id,
            ]);

            return response()->json([
                'message' => 'SMS queued for delivery',
                'sms' => new SmsMessageResource($smsMessage),
                'async' => true,
            ], 202); // 202 Accepted for async processing
        }

        // Synchronous processing
        try {
            $result = $this->kannelService->sendSms(
                $smsMessage->to,
                $smsMessage->content,
                $smsMessage->from
            );

            if ($result['success']) {
                $smsMessage->markAsSent($result['kannel_id'] ?? null);
                
                Log::info('SMS sent successfully (sync)', [
                    'sms_id' => $smsMessage->id,
                    'client_id' => $client->id,
                    'kannel_id' => $result['kannel_id'],
                ]);

                return new SmsMessageResource($smsMessage->fresh());
            } else {
                $smsMessage->markAsFailed(
                    $result['error_code'] ?? 'UNKNOWN',
                    $result['error_message'] ?? 'Unknown error'
                );

                Log::error('SMS sending failed (sync)', [
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
            
            Log::error('SMS sending exception (sync)', [
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
     * @OA\Get(
     *     path="/api/v1/sms/{id}/status",
     *     tags={"SMS"},
     *     summary="Get SMS Status",
     *     description="Retrieve the status of a specific SMS message including delivery information.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="SMS message ID",
     *         @OA\Schema(type="integer", example=123)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="SMS status retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=123),
     *                 @OA\Property(property="direction", type="string", example="outbound"),
     *                 @OA\Property(property="from", type="string", example="+25311123"),
     *                 @OA\Property(property="to", type="string", example="+25377166677"),
     *                 @OA\Property(property="message", type="string", example="Test SMS from ApiSMS Gateway"),
     *                 @OA\Property(property="status", type="string", enum={"pending", "sent", "delivered", "failed"}, example="delivered"),
     *                 @OA\Property(property="sent_at", type="string", format="datetime", nullable=true),
     *                 @OA\Property(property="delivered_at", type="string", format="datetime", nullable=true),
     *                 @OA\Property(property="error_code", type="string", nullable=true, example="KANNEL_5"),
     *                 @OA\Property(property="error_message", type="string", nullable=true, example="Invalid destination number"),
     *                 @OA\Property(property="created_at", type="string", format="datetime"),
     *                 @OA\Property(property="updated_at", type="string", format="datetime")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="SMS message not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="SMS not found"),
     *             @OA\Property(property="message", type="string", example="The requested SMS message was not found or does not belong to your client.")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Authentication required")
     * )
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
     * @OA\Get(
     *     path="/api/v1/sms",
     *     tags={"SMS"},
     *     summary="List SMS Messages",
     *     description="Retrieve a paginated list of SMS messages for the authenticated client with optional filters.",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number for pagination",
     *         @OA\Schema(type="integer", minimum=1, example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of messages per page (max 100)",
     *         @OA\Schema(type="integer", minimum=1, maximum=100, example=20)
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by SMS status",
     *         @OA\Schema(type="string", enum={"pending", "sent", "delivered", "failed"}, example="delivered")
     *     ),
     *     @OA\Parameter(
     *         name="direction",
     *         in="query",
     *         description="Filter by SMS direction",
     *         @OA\Schema(type="string", enum={"outbound", "inbound"}, example="outbound")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="SMS messages retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer", example=123),
     *                 @OA\Property(property="direction", type="string", example="outbound"),
     *                 @OA\Property(property="from", type="string", example="+25311123"),
     *                 @OA\Property(property="to", type="string", example="+25377166677"),
     *                 @OA\Property(property="message", type="string", example="Test SMS from ApiSMS Gateway"),
     *                 @OA\Property(property="status", type="string", example="delivered"),
     *                 @OA\Property(property="created_at", type="string", format="datetime")
     *             )),
     *             @OA\Property(property="links", type="object",
     *                 @OA\Property(property="first", type="string", example="http://apisms.test/api/v1/sms?page=1"),
     *                 @OA\Property(property="last", type="string", example="http://apisms.test/api/v1/sms?page=5"),
     *                 @OA\Property(property="prev", type="string", nullable=true),
     *                 @OA\Property(property="next", type="string", example="http://apisms.test/api/v1/sms?page=2")
     *             ),
     *             @OA\Property(property="meta", type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="from", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=5),
     *                 @OA\Property(property="per_page", type="integer", example=20),
     *                 @OA\Property(property="to", type="integer", example=20),
     *                 @OA\Property(property="total", type="integer", example=95)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Authentication required")
     * )
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
