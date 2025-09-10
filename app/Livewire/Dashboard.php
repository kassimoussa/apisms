<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Client;
use App\Models\SmsMessage;
use App\Services\KannelService;
use Carbon\Carbon;

class Dashboard extends Component
{
    public $stats = [];
    public $realtimeStats = [];
    public $kannelStatus = [];

    protected KannelService $kannelService;

    public function boot()
    {
        $this->kannelService = app(KannelService::class);
    }

    public function mount()
    {
        $this->loadStats();
    }

    public function loadStats()
    {
        $this->stats = $this->calculateStats();
        $this->kannelStatus = $this->kannelService->checkConnectivity();
    }

    public function refreshStats()
    {
        $this->loadStats();
        $this->dispatch('stats-updated');
    }

    private function calculateStats(): array
    {
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();

        return [
            'clients' => [
                'total' => Client::count(),
                'active' => Client::active()->count(),
            ],
            'messages' => [
                'total' => SmsMessage::count(),
                'today' => SmsMessage::whereDate('created_at', $today)->count(),
                'this_week' => SmsMessage::where('created_at', '>=', $thisWeek)->count(),
                'this_month' => SmsMessage::where('created_at', '>=', $thisMonth)->count(),
            ],
            'status' => [
                'sent' => SmsMessage::whereIn('status', ['sent', 'delivered'])->count(),
                'failed' => SmsMessage::where('status', 'failed')->count(),
                'pending' => SmsMessage::where('status', 'pending')->count(),
            ],
            'recent_messages' => SmsMessage::with('client')
                ->latest()
                ->limit(5)
                ->get()
                ->map(function ($message) {
                    return [
                        'id' => $message->id,
                        'client' => $message->client->name,
                        'to' => $message->formatted_to,
                        'status' => $message->status,
                        'created_at' => $message->created_at->diffForHumans(),
                    ];
                }),
        ];
    }

    public function render()
    {
        return view('livewire.dashboard')
            ->layout('layouts.app', ['title' => 'Admin Dashboard']);
    }
}
