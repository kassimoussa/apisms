<?php

namespace App\Livewire\Client;

use Livewire\Component;
use App\Models\SmsMessage;
use App\Models\BulkSmsJob;
use Carbon\Carbon;

class Dashboard extends Component
{
    public $client;
    public $todayStats = [];
    public $weekStats = [];
    public $recentCampaigns = [];

    public function mount()
    {
        $this->client = request()->attributes->get('client');
        $this->loadStatistics();
    }

    public function loadStatistics()
    {
        // Today's statistics
        $today = Carbon::today();
        $this->todayStats = [
            'sent' => SmsMessage::where('client_id', $this->client->id)
                ->whereDate('created_at', $today)
                ->where('status', 'sent')
                ->count(),
            'failed' => SmsMessage::where('client_id', $this->client->id)
                ->whereDate('created_at', $today)
                ->where('status', 'failed')
                ->count(),
            'pending' => SmsMessage::where('client_id', $this->client->id)
                ->whereDate('created_at', $today)
                ->where('status', 'pending')
                ->count(),
            'campaigns' => BulkSmsJob::where('client_id', $this->client->id)
                ->whereDate('created_at', $today)
                ->count(),
        ];

        // This week's statistics
        $weekStart = Carbon::now()->startOfWeek();
        $this->weekStats = [
            'sent' => SmsMessage::where('client_id', $this->client->id)
                ->where('created_at', '>=', $weekStart)
                ->where('status', 'sent')
                ->count(),
            'failed' => SmsMessage::where('client_id', $this->client->id)
                ->where('created_at', '>=', $weekStart)
                ->where('status', 'failed')
                ->count(),
            'campaigns' => BulkSmsJob::where('client_id', $this->client->id)
                ->where('created_at', '>=', $weekStart)
                ->count(),
        ];

        // Recent campaigns
        $this->recentCampaigns = BulkSmsJob::where('client_id', $this->client->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($campaign) {
                return [
                    'id' => $campaign->id,
                    'name' => $campaign->name,
                    'status' => $campaign->status,
                    'progress' => $campaign->progress_percentage,
                    'total' => $campaign->total_count,
                    'sent' => $campaign->sent_count,
                    'failed' => $campaign->failed_count,
                    'created_at' => $campaign->created_at->diffForHumans(),
                ];
            });
    }

    public function render()
    {
        return view('livewire.client.dashboard')
            ->layout('components.layouts.client');
    }
}