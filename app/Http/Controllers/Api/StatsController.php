<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SmsMessage;
use App\Services\KannelService;
use Carbon\Carbon;

class StatsController extends Controller
{
    protected KannelService $kannelService;

    public function __construct(KannelService $kannelService)
    {
        $this->kannelService = $kannelService;
    }

    /**
     * Get client statistics
     */
    public function index(Request $request)
    {
        $client = $request->attributes->get('client');
        $period = $request->input('period', 'month');
        
        $startDate = $this->getStartDate($period);
        
        // Base query for the period
        $baseQuery = SmsMessage::where('client_id', $client->id)
            ->where('created_at', '>=', $startDate);

        // Overall statistics
        $stats = [
            'client' => [
                'id' => $client->id,
                'name' => $client->name,
                'rate_limit' => $client->rate_limit,
                'active' => $client->active,
            ],
            'period' => [
                'name' => $period,
                'start_date' => $startDate->toISOString(),
                'end_date' => now()->toISOString(),
            ],
            'totals' => [
                'sent' => $baseQuery->clone()->where('status', 'sent')->count(),
                'delivered' => $baseQuery->clone()->where('status', 'delivered')->count(),
                'failed' => $baseQuery->clone()->where('status', 'failed')->count(),
                'pending' => $baseQuery->clone()->where('status', 'pending')->count(),
                'total' => $baseQuery->clone()->count(),
            ],
            'directions' => [
                'outbound' => $baseQuery->clone()->where('direction', 'outbound')->count(),
                'inbound' => $baseQuery->clone()->where('direction', 'inbound')->count(),
            ],
        ];

        // Calculate success rate
        $totalSent = $stats['totals']['sent'] + $stats['totals']['delivered'];
        $totalFailed = $stats['totals']['failed'];
        $stats['success_rate'] = $totalSent + $totalFailed > 0 
            ? round(($totalSent / ($totalSent + $totalFailed)) * 100, 2)
            : 0;

        // Daily breakdown for charts
        $stats['daily'] = $this->getDailyStats($client->id, $startDate);

        // Recent messages
        $stats['recent_messages'] = SmsMessage::where('client_id', $client->id)
            ->with(['deliveryReports' => function ($query) {
                $query->latest()->limit(1);
            }])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'direction' => $message->direction,
                    'to' => $message->formatted_to,
                    'status' => $message->status,
                    'created_at' => $message->created_at->toISOString(),
                ];
            });

        // Kannel connectivity status
        $stats['kannel'] = $this->kannelService->checkConnectivity();

        return response()->json($stats);
    }

    /**
     * Get real-time statistics
     */
    public function realtime(Request $request)
    {
        $client = $request->attributes->get('client');
        
        // Last 24 hours statistics
        $last24Hours = now()->subDay();
        
        $stats = [
            'last_24_hours' => [
                'sent' => SmsMessage::where('client_id', $client->id)
                    ->where('created_at', '>=', $last24Hours)
                    ->whereIn('status', ['sent', 'delivered'])
                    ->count(),
                'failed' => SmsMessage::where('client_id', $client->id)
                    ->where('created_at', '>=', $last24Hours)
                    ->where('status', 'failed')
                    ->count(),
                'pending' => SmsMessage::where('client_id', $client->id)
                    ->where('status', 'pending')
                    ->count(),
            ],
            'kannel_status' => $this->kannelService->checkConnectivity(),
            'last_activity' => SmsMessage::where('client_id', $client->id)
                ->latest()
                ->first()?->created_at?->toISOString(),
            'current_time' => now()->toISOString(),
        ];

        return response()->json($stats);
    }

    private function getStartDate(string $period): Carbon
    {
        return match ($period) {
            'today' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };
    }

    private function getDailyStats(int $clientId, Carbon $startDate): array
    {
        $daily = [];
        $current = $startDate->copy();
        $end = now()->endOfDay();

        while ($current <= $end) {
            $dayStart = $current->copy()->startOfDay();
            $dayEnd = $current->copy()->endOfDay();
            
            $dayStats = [
                'date' => $current->toDateString(),
                'sent' => SmsMessage::where('client_id', $clientId)
                    ->whereBetween('created_at', [$dayStart, $dayEnd])
                    ->whereIn('status', ['sent', 'delivered'])
                    ->count(),
                'failed' => SmsMessage::where('client_id', $clientId)
                    ->whereBetween('created_at', [$dayStart, $dayEnd])
                    ->where('status', 'failed')
                    ->count(),
            ];
            
            $daily[] = $dayStats;
            $current->addDay();
        }

        return $daily;
    }
}
