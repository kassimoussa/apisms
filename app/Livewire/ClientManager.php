<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Client;
use Livewire\WithPagination;

class ClientManager extends Component
{
    use WithPagination;

    public $name = '';
    public $description = '';
    public $rate_limit = 60;
    public $allowed_ips = '';
    public $showCreateForm = false;
    public $selectedClient = null;

    public function toggleCreateForm()
    {
        $this->showCreateForm = !$this->showCreateForm;
        $this->reset(['name', 'description', 'rate_limit', 'allowed_ips']);
    }

    public function createClient()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'rate_limit' => 'required|integer|min:1|max:1000',
            'allowed_ips' => 'nullable|string',
        ]);

        $allowedIps = null;
        if ($this->allowed_ips) {
            $allowedIps = array_map('trim', explode(',', $this->allowed_ips));
        }

        Client::create([
            'name' => $this->name,
            'description' => $this->description,
            'rate_limit' => $this->rate_limit,
            'allowed_ips' => $allowedIps,
            'active' => true,
        ]);

        $this->reset(['name', 'description', 'rate_limit', 'allowed_ips', 'showCreateForm']);
        session()->flash('message', 'Client created successfully!');
    }

    public function toggleClient($clientId)
    {
        $client = Client::find($clientId);
        if ($client) {
            $client->update(['active' => !$client->active]);
        }
    }

    public function regenerateApiKey($clientId)
    {
        $client = Client::find($clientId);
        if ($client) {
            $client->regenerateApiKey();
            session()->flash('message', "API key regenerated for {$client->name}");
        }
    }

    public function render()
    {
        return view('livewire.client-manager', [
            'clients' => Client::withCount('smsMessages')->paginate(10)
        ])->layout('components.layouts.app', ['title' => 'Client Management']);
    }
}
