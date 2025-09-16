<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\SmsMessage;
use App\Models\Client;

class SmsResponses extends Component
{
    use WithPagination;

    public $selectedClient = 'all';
    public $search = '';
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';
    public $refreshInterval = 30; // seconds

    protected $queryString = [
        'search' => ['except' => ''],
        'selectedClient' => ['except' => 'all'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingSelectedClient()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function refreshData()
    {
        // Force refresh by updating a reactive property
        $this->dispatch('refreshed');
    }

    public function markAsRead($smsId)
    {
        $sms = SmsMessage::find($smsId);
        if ($sms && $sms->isInbound()) {
            $metadata = $sms->metadata ?? [];
            $metadata['read_at'] = now()->toISOString();
            $metadata['read'] = true;
            $sms->update(['metadata' => $metadata]);
            
            $this->dispatch('sms-marked-read', $smsId);
        }
    }

    public function render()
    {
        $query = SmsMessage::query()
            ->inbound()
            ->with('client')
            ->orderBy($this->sortBy, $this->sortDirection);

        // Filter by client
        if ($this->selectedClient !== 'all') {
            $query->where('client_id', $this->selectedClient);
        }

        // Search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('content', 'like', '%' . $this->search . '%')
                  ->orWhere('from', 'like', '%' . $this->search . '%')
                  ->orWhere('to', 'like', '%' . $this->search . '%');
            });
        }

        $inboundMessages = $query->paginate(20);

        // Get client list for filter
        $clients = Client::active()
            ->withCount(['smsMessages as inbound_count' => function ($q) {
                $q->where('direction', 'inbound');
            }])
            ->having('inbound_count', '>', 0)
            ->get();

        // Statistics
        $stats = [
            'total_inbound' => SmsMessage::inbound()->count(),
            'today_inbound' => SmsMessage::inbound()->whereDate('created_at', today())->count(),
            'unread_count' => SmsMessage::inbound()
                ->where(function ($q) {
                    $q->whereJsonDoesntContain('metadata->read', true)
                      ->orWhereNull('metadata->read');
                })->count(),
            'last_received' => SmsMessage::inbound()->latest('created_at')->first()?->created_at,
        ];

        return view('livewire.sms-responses', [
            'inboundMessages' => $inboundMessages,
            'clients' => $clients,
            'stats' => $stats,
        ])->layout('components.layouts.app', ['title' => 'SMS Responses']);
    }
}
