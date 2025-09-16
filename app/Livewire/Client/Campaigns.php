<?php

namespace App\Livewire\Client;

use Livewire\Component;
use App\Models\BulkSmsJob;
use Livewire\WithPagination;

class Campaigns extends Component
{
    use WithPagination;

    public $client;
    public $statusFilter = '';

    public function mount()
    {
        $this->client = request()->attributes->get('client');
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = BulkSmsJob::where('client_id', $this->client->id)
            ->orderBy('created_at', 'desc');

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        $campaigns = $query->paginate(10);

        return view('livewire.client.campaigns', compact('campaigns'))
            ->layout('components.layouts.client');
    }
}