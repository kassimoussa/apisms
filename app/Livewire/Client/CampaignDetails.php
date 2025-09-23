<?php

namespace App\Livewire\Client;

use Livewire\Component;
use App\Models\BulkSmsJob;
use App\Models\SmsMessage;
use Livewire\WithPagination;

class CampaignDetails extends Component
{
    use WithPagination;

    public $client;
    public $campaign;
    public $campaignId;
    
    // Filters
    public $statusFilter = '';
    public $searchFilter = '';

    public function mount($campaignId)
    {
        $this->client = request()->attributes->get('client');
        $this->campaignId = $campaignId;
        
        $this->campaign = BulkSmsJob::where('id', $campaignId)
            ->where('client_id', $this->client->id)
            ->firstOrFail();
    }

    public function pauseCampaign()
    {
        if ($this->campaign->canPause()) {
            $this->campaign->pause();
            $this->campaign->refresh();
            session()->flash('success', 'Campagne mise en pause avec succès.');
        }
    }

    public function resumeCampaign()
    {
        if ($this->campaign->canResume()) {
            $this->campaign->resume();
            
            // Redispatch job
            $batchSize = $this->campaign->settings['batch_size'] ?? 50;
            \App\Jobs\ProcessBulkSmsJob::dispatch($this->campaign->id, $batchSize);
            
            // Auto-start queue worker
            \App\Services\QueueWorkerManager::ensureWorkerRunning();
            
            $this->campaign->refresh();
            session()->flash('success', 'Campagne reprise avec succès.');
        }
    }

    public function render()
    {
        $query = SmsMessage::where('bulk_job_id', $this->campaign->id)
            ->orderBy('created_at', 'desc');

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->searchFilter) {
            $query->where('to', 'like', '%' . $this->searchFilter . '%');
        }

        $messages = $query->paginate(20);

        return view('livewire.client.campaign-details', [
            'messages' => $messages
        ])->layout('components.layouts.client');
    }
}