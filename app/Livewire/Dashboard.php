<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\SmsMessage;
use App\Models\Client;
use App\Services\KannelService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class Dashboard extends Component
{
    public $realTimeStats = [];
    public $chartData = [];
    public $recentMessages = [];
    public $systemHealth = [];
    public $refreshInterval = 30; // seconds
    public $autoRefresh = true;

    protected KannelService $kannelService;

    public function boot()
    {
        $this->kannelService = app(KannelService::class);
    }

    public function mount()
    {
        $this->loadRealTimeStats();
        $this->loadChartData();
        $this->loadRecentMessages();
        $this->checkSystemHealth();
    }

    public function loadRealTimeStats()
    {
        $cacheKey = 'dashboard_stats_' . now()->format('Y-m-d-H-i');
        
        $this->realTimeStats = Cache::remember($cacheKey, 60, function () {
            return [
                'total_sms' => SmsMessage::count(),
                'today_sms' => SmsMessage::whereDate('created_at', today())->count(),
                'sent_today' => SmsMessage::sent()->whereDate('created_at', today())->count(),
                'delivered_today' => SmsMessage::delivered()->whereDate('created_at', today())->count(),
                'failed_today' => SmsMessage::failed()->whereDate('created_at', today())->count(),
                'pending_sms' => SmsMessage::pending()->count(),
                'active_clients' => Client::active()->count(),
                'queue_jobs' => $this->getQueueStats(),
                'success_rate' => $this->calculateSuccessRate(),
                'avg_delivery_time' => $this->calculateAvgDeliveryTime(),
            ];
        });
    }

    public function loadChartData()
    {
        // Last 7 days SMS activity
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            
            $sent = SmsMessage::whereDate('created_at', $date->toDateString())
                ->whereIn('status', ['sent', 'delivered'])->count();
            $delivered = SmsMessage::whereDate('created_at', $date->toDateString())
                ->where('status', 'delivered')->count();
            $failed = SmsMessage::whereDate('created_at', $date->toDateString())
                ->where('status', 'failed')->count();
            
            $chartData[] = [
                'date' => $date->format('M d'),
                'sent' => $sent,
                'delivered' => $delivered,
                'failed' => $failed,
            ];
        }
        
        \Log::info('Chart data loaded', ['chartData' => $chartData]);
        $this->chartData = $chartData;
    }

    public function loadRecentMessages()
    {
        $this->recentMessages = SmsMessage::with(['client', 'deliveryReports'])
            ->orderBy('created_at', 'desc')
            ->take(15)
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'client_name' => $message->client?->name ?? 'Unknown',
                    'to' => $message->getFormattedToAttribute(),
                    'content_preview' => str($message->content)->limit(50),
                    'status' => $message->status,
                    'created_at' => $message->created_at->diffForHumans(),
                    'status_class' => $this->getStatusClass($message->status),
                ];
            });
    }

    public function checkSystemHealth()
    {
        $this->systemHealth = [
            'kannel' => $this->kannelService->checkConnectivity(),
            'database' => $this->checkDatabaseHealth(),
            'queue' => $this->checkQueueHealth(),
            'disk_space' => $this->checkDiskSpace(),
        ];
    }

    public function toggleAutoRefresh()
    {
        $this->autoRefresh = !$this->autoRefresh;
        
        if ($this->autoRefresh) {
            $this->dispatch('startAutoRefresh', interval: $this->refreshInterval * 1000);
        } else {
            $this->dispatch('stopAutoRefresh');
        }
    }

    public function refreshData()
    {
        $this->loadRealTimeStats();
        $this->loadChartData();
        $this->loadRecentMessages();
        $this->checkSystemHealth();
        
        $this->dispatch('dataRefreshed', timestamp: now()->format('H:i:s'));
    }

    public function exportData()
    {
        $data = [
            'exported_at' => now()->toISOString(),
            'stats' => $this->realTimeStats,
            'chart_data' => $this->chartData,
            'system_health' => $this->systemHealth,
        ];

        return response()->streamDownload(function () use ($data) {
            echo json_encode($data, JSON_PRETTY_PRINT);
        }, 'dashboard-data-' . now()->format('Y-m-d-H-i-s') . '.json');
    }

    private function getQueueStats(): array
    {
        try {
            $failed = DB::table('failed_jobs')->count();
            $pending = DB::table('jobs')->count();
            
            return [
                'pending' => $pending,
                'failed' => $failed,
                'total' => $pending + $failed,
            ];
        } catch (\Exception $e) {
            return ['pending' => 0, 'failed' => 0, 'total' => 0];
        }
    }

    private function calculateSuccessRate(): float
    {
        $total = SmsMessage::whereDate('created_at', today())->count();
        if ($total === 0) return 0;
        
        $successful = SmsMessage::whereIn('status', ['sent', 'delivered'])
            ->whereDate('created_at', today())->count();
        
        return round(($successful / $total) * 100, 1);
    }

    private function calculateAvgDeliveryTime(): ?string
    {
        $avgSeconds = SmsMessage::whereNotNull('sent_at')
            ->whereNotNull('delivered_at')
            ->whereDate('created_at', today())
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, sent_at, delivered_at)) as avg_time')
            ->value('avg_time');

        if (!$avgSeconds) return null;

        if ($avgSeconds < 60) {
            return round($avgSeconds) . 's';
        } elseif ($avgSeconds < 3600) {
            return round($avgSeconds / 60, 1) . 'm';
        } else {
            return round($avgSeconds / 3600, 1) . 'h';
        }
    }

    private function checkDatabaseHealth(): array
    {
        try {
            $start = microtime(true);
            DB::select('SELECT 1');
            $responseTime = round((microtime(true) - $start) * 1000, 2);
            
            return [
                'success' => true,
                'response_time_ms' => $responseTime,
                'message' => 'Database accessible',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Database connection failed',
            ];
        }
    }

    private function checkQueueHealth(): array
    {
        try {
            $failedCount = DB::table('failed_jobs')->count();
            $pendingCount = DB::table('jobs')->count();
            
            return [
                'success' => true,
                'pending_jobs' => $pendingCount,
                'failed_jobs' => $failedCount,
                'message' => 'Queue system operational',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Queue system check failed',
            ];
        }
    }

    private function checkDiskSpace(): array
    {
        try {
            $bytes = disk_free_space(storage_path());
            $gb = round($bytes / 1024 / 1024 / 1024, 2);
            
            return [
                'success' => $gb > 1, // Warn if less than 1GB free
                'free_space_gb' => $gb,
                'message' => $gb > 1 ? 'Sufficient disk space' : 'Low disk space warning',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Disk space check failed',
            ];
        }
    }

    private function getStatusClass(string $status): string
    {
        return match ($status) {
            'delivered' => 'text-green-600 bg-green-100',
            'sent' => 'text-blue-600 bg-blue-100',
            'failed' => 'text-red-600 bg-red-100',
            'pending' => 'text-yellow-600 bg-yellow-100',
            default => 'text-gray-600 bg-gray-100',
        };
    }

    public function render()
    {
        return view('livewire.dashboard')
            ->layout('components.layouts.app', ['title' => 'Dashboard']);
    }
}