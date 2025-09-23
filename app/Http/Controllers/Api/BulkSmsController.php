<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BulkSmsJob;
use App\Jobs\ProcessBulkSmsJob;
use App\Services\QueueWorkerManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Tag(
 *     name="Bulk SMS",
 *     description="Bulk SMS sending and management operations"
 * )
 */
class BulkSmsController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/sms/bulk",
     *     summary="Send bulk SMS",
     *     tags={"Bulk SMS"},
     *     security={{"ApiKeyAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"recipients", "content"},
     *             @OA\Property(property="name", type="string", example="Marketing Campaign Q4"),
     *             @OA\Property(property="recipients", type="array", @OA\Items(type="string"), example={"77123456", "77987654", "77555333"}),
     *             @OA\Property(property="content", type="string", example="Special offer! Get 50% off today only!"),
     *             @OA\Property(property="from", type="string", example="PROMO"),
     *             @OA\Property(property="scheduled_at", type="string", format="datetime", example="2025-09-15T15:00:00Z"),
     *             @OA\Property(property="settings", type="object", 
     *                 @OA\Property(property="rate_limit", type="integer", example=30),
     *                 @OA\Property(property="batch_size", type="integer", example=100)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Bulk SMS job created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Bulk SMS job created successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="job_id", type="integer", example=123),
     *                 @OA\Property(property="name", type="string", example="Marketing Campaign Q4"),
     *                 @OA\Property(property="status", type="string", example="pending"),
     *                 @OA\Property(property="total_count", type="integer", example=1000),
     *                 @OA\Property(property="scheduled_at", type="string", example="2025-09-15T15:00:00Z")
     *             )
     *         )
     *     )
     * )
     */
    public function create(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'recipients' => 'required|array|min:1|max:10000',
            'recipients.*' => 'string|regex:/^[0-9+\-\s()]+$/',
            'content' => 'required|string|max:1600',
            'from' => 'nullable|string|max:20',
            'scheduled_at' => 'nullable|date|after:now',
            'settings.rate_limit' => 'nullable|integer|min:1|max:1000',
            'settings.batch_size' => 'nullable|integer|min:10|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $client = $request->attributes->get('client');
            $recipients = array_unique($request->input('recipients'));
            $totalCount = count($recipients);

            // Validate recipient phone numbers
            $validRecipients = [];
            $invalidRecipients = [];

            foreach ($recipients as $recipient) {
                // Basic phone number validation and formatting
                $cleanNumber = preg_replace('/[^\d+]/', '', $recipient);
                if (strlen($cleanNumber) >= 8 && strlen($cleanNumber) <= 15) {
                    $validRecipients[] = $cleanNumber;
                } else {
                    $invalidRecipients[] = $recipient;
                }
            }

            if (!empty($invalidRecipients)) {
                Log::warning('Invalid recipients in bulk SMS', [
                    'client_id' => $client->id,
                    'invalid_count' => count($invalidRecipients),
                    'invalid_recipients' => array_slice($invalidRecipients, 0, 10) // Log first 10
                ]);
            }

            if (empty($validRecipients)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid recipients found',
                    'invalid_recipients' => $invalidRecipients
                ], 422);
            }

            // Check daily SMS quota (includes all SMS types)
            $dailyUsage = $client->getDailySmsUsage();
            if (($dailyUsage + count($validRecipients)) > $client->daily_sms_limit) {
                return response()->json([
                    'success' => false,
                    'message' => 'Daily SMS quota exceeded',
                    'limit' => $client->daily_sms_limit,
                    'used_today' => $dailyUsage,
                    'requested' => count($validRecipients),
                    'remaining' => $client->getRemainingDailyQuota()
                ], 429);
            }

            // Check monthly SMS quota (includes all SMS types)
            $monthlyUsage = $client->getMonthlySmsUsage();
            if (($monthlyUsage + count($validRecipients)) > $client->monthly_sms_limit) {
                return response()->json([
                    'success' => false,
                    'message' => 'Monthly SMS quota exceeded',
                    'limit' => $client->monthly_sms_limit,
                    'used_this_month' => $monthlyUsage,
                    'requested' => count($validRecipients),
                    'remaining' => $client->getRemainingMonthlyQuota()
                ], 429);
            }

            $bulkJob = BulkSmsJob::create([
                'client_id' => $client->id,
                'name' => $request->input('name', 'Bulk SMS Campaign ' . now()->format('Y-m-d H:i')),
                'content' => $request->input('content'),
                'from' => $request->input('from'),
                'recipients' => $validRecipients,
                'total_count' => count($validRecipients),
                'pending_count' => count($validRecipients),
                'scheduled_at' => $request->input('scheduled_at') ? now()->parse($request->input('scheduled_at')) : null,
                'settings' => $request->input('settings', [])
            ]);

            // Dispatch job if not scheduled
            if (!$bulkJob->scheduled_at || $bulkJob->scheduled_at->isPast()) {
                $batchSize = $request->input('settings.batch_size', 50);
                ProcessBulkSmsJob::dispatch($bulkJob->id, $batchSize);
                
                // Auto-start queue worker to process the job
                QueueWorkerManager::ensureWorkerRunning();
            }

            Log::info('Bulk SMS job created', [
                'client_id' => $client->id,
                'bulk_job_id' => $bulkJob->id,
                'total_recipients' => count($validRecipients),
                'invalid_recipients' => count($invalidRecipients),
                'scheduled_at' => $bulkJob->scheduled_at
            ]);

            $response = [
                'job_id' => $bulkJob->id,
                'name' => $bulkJob->name,
                'status' => $bulkJob->status,
                'total_count' => $bulkJob->total_count,
                'valid_recipients' => count($validRecipients),
                'scheduled_at' => $bulkJob->scheduled_at?->toISOString(),
            ];

            if (!empty($invalidRecipients)) {
                $response['invalid_recipients'] = array_slice($invalidRecipients, 0, 50); // Show first 50
                $response['invalid_count'] = count($invalidRecipients);
            }

            return response()->json([
                'success' => true,
                'message' => 'Bulk SMS job created successfully',
                'data' => $response
            ], 201);

        } catch (\Exception $e) {
            $client = $request->attributes->get('client');
            Log::error('Failed to create bulk SMS job', [
                'client_id' => $client?->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create bulk SMS job',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/sms/bulk/{jobId}",
     *     summary="Get bulk SMS job status",
     *     tags={"Bulk SMS"},
     *     security={{"ApiKeyAuth":{}}},
     *     @OA\Parameter(
     *         name="jobId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Bulk SMS job status",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="job_id", type="integer", example=123),
     *                 @OA\Property(property="name", type="string", example="Marketing Campaign Q4"),
     *                 @OA\Property(property="status", type="string", example="processing"),
     *                 @OA\Property(property="progress_percentage", type="number", example=65.5),
     *                 @OA\Property(property="total_count", type="integer", example=1000),
     *                 @OA\Property(property="sent_count", type="integer", example=655),
     *                 @OA\Property(property="failed_count", type="integer", example=12),
     *                 @OA\Property(property="pending_count", type="integer", example=333),
     *                 @OA\Property(property="success_rate", type="number", example=98.2),
     *                 @OA\Property(property="estimated_duration", type="integer", example=300)
     *             )
     *         )
     *     )
     * )
     */
    public function status(Request $request, int $jobId): JsonResponse
    {
        $client = $request->attributes->get('client');
        $bulkJob = BulkSmsJob::where('id', $jobId)
                            ->where('client_id', $client->id)
                            ->first();

        if (!$bulkJob) {
            return response()->json([
                'success' => false,
                'message' => 'Bulk SMS job not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'job_id' => $bulkJob->id,
                'name' => $bulkJob->name,
                'status' => $bulkJob->status,
                'progress_percentage' => $bulkJob->progress_percentage,
                'total_count' => $bulkJob->total_count,
                'sent_count' => $bulkJob->sent_count,
                'failed_count' => $bulkJob->failed_count,
                'pending_count' => $bulkJob->pending_count,
                'success_rate' => $bulkJob->success_rate,
                'estimated_duration' => $bulkJob->estimated_duration,
                'scheduled_at' => $bulkJob->scheduled_at?->toISOString(),
                'started_at' => $bulkJob->started_at?->toISOString(),
                'completed_at' => $bulkJob->completed_at?->toISOString(),
                'failure_reason' => $bulkJob->failure_reason,
                'created_at' => $bulkJob->created_at->toISOString(),
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/sms/bulk",
     *     summary="List bulk SMS jobs",
     *     tags={"Bulk SMS"},
     *     security={{"ApiKeyAuth":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         @OA\Schema(type="string", enum={"pending", "processing", "completed", "failed", "paused"})
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         @OA\Schema(type="integer", maximum=100)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of bulk SMS jobs"
     *     )
     * )
     */
    public function list(Request $request): JsonResponse
    {
        $client = $request->attributes->get('client');
        $perPage = min($request->input('per_page', 20), 100);
        
        $query = BulkSmsJob::where('client_id', $client->id)
                          ->orderBy('created_at', 'desc');

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        $jobs = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $jobs->items(),
            'pagination' => [
                'current_page' => $jobs->currentPage(),
                'per_page' => $jobs->perPage(),
                'total' => $jobs->total(),
                'last_page' => $jobs->lastPage(),
            ]
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/sms/bulk/{jobId}/pause",
     *     summary="Pause bulk SMS job",
     *     tags={"Bulk SMS"},
     *     security={{"ApiKeyAuth":{}}},
     *     @OA\Parameter(
     *         name="jobId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Job paused successfully"
     *     )
     * )
     */
    public function pause(Request $request, int $jobId): JsonResponse
    {
        return $this->controlJob($request, $jobId, 'pause');
    }

    /**
     * @OA\Post(
     *     path="/api/v1/sms/bulk/{jobId}/resume",
     *     summary="Resume bulk SMS job",
     *     tags={"Bulk SMS"},
     *     security={{"ApiKeyAuth":{}}},
     *     @OA\Parameter(
     *         name="jobId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Job resumed successfully"
     *     )
     * )
     */
    public function resume(Request $request, int $jobId): JsonResponse
    {
        return $this->controlJob($request, $jobId, 'resume');
    }

    private function controlJob(Request $request, int $jobId, string $action): JsonResponse
    {
        $client = $request->attributes->get('client');
        $bulkJob = BulkSmsJob::where('id', $jobId)
                            ->where('client_id', $client->id)
                            ->first();

        if (!$bulkJob) {
            return response()->json([
                'success' => false,
                'message' => 'Bulk SMS job not found'
            ], 404);
        }

        $canPerform = match($action) {
            'pause' => $bulkJob->canPause(),
            'resume' => $bulkJob->canResume(),
            default => false
        };

        if (!$canPerform) {
            return response()->json([
                'success' => false,
                'message' => "Cannot {$action} job in current status: {$bulkJob->status}"
            ], 400);
        }

        $bulkJob->$action();

        // Dispatch new job if resuming
        if ($action === 'resume') {
            $batchSize = $bulkJob->settings['batch_size'] ?? 50;
            ProcessBulkSmsJob::dispatch($bulkJob->id, $batchSize);
            
            // Auto-start queue worker to process the resumed job
            QueueWorkerManager::ensureWorkerRunning();
        }

        Log::info("Bulk SMS job {$action}d", [
            'client_id' => $client->id,
            'bulk_job_id' => $bulkJob->id,
            'action' => $action
        ]);

        return response()->json([
            'success' => true,
            'message' => "Job {$action}d successfully",
            'data' => [
                'job_id' => $bulkJob->id,
                'status' => $bulkJob->status
            ]
        ]);
    }
}
