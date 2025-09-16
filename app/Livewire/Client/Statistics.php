<?php

namespace App\Livewire\Client;

use Livewire\Component;
use App\Models\SmsMessage;
use App\Models\BulkSmsJob;
use Carbon\Carbon;

class Statistics extends Component
{
    public $client;

    public function mount()
    {
        $this->client = request()->attributes->get('client');
    }

    public function render()
    {
        // Get statistics for the past 30 days
        $thirtyDaysAgo = Carbon::now()->subDays(30);
        
        $stats = [
            'total_sms' => SmsMessage::where('client_id', $this->client->id)->count(),
            'sent_sms' => SmsMessage::where('client_id', $this->client->id)->where('status', 'sent')->count(),
            'failed_sms' => SmsMessage::where('client_id', $this->client->id)->where('status', 'failed')->count(),
            'total_campaigns' => BulkSmsJob::where('client_id', $this->client->id)->count(),
            'completed_campaigns' => BulkSmsJob::where('client_id', $this->client->id)->where('status', 'completed')->count(),
        ];

        // Monthly breakdown
        $monthlyStats = collect();
        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i)->startOfMonth();
            $nextMonth = $month->copy()->addMonth();
            
            $monthlyStats->push([
                'month' => $month->format('M Y'),
                'sent' => SmsMessage::where('client_id', $this->client->id)
                    ->where('status', 'sent')
                    ->whereBetween('created_at', [$month, $nextMonth])
                    ->count(),
                'failed' => SmsMessage::where('client_id', $this->client->id)
                    ->where('status', 'failed')
                    ->whereBetween('created_at', [$month, $nextMonth])
                    ->count(),
                'campaigns' => BulkSmsJob::where('client_id', $this->client->id)
                    ->whereBetween('created_at', [$month, $nextMonth])
                    ->count(),
            ]);
        }

        return view('livewire.client.statistics', compact('stats', 'monthlyStats'))
            ->layout('components.layouts.client');
    }
}