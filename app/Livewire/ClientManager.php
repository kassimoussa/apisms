<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Client;
use Livewire\WithPagination;

class ClientManager extends Component
{
    use WithPagination;

    // UI State
    public $revealedKeys = [];


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
            $newKey = $client->regenerateApiKey();
            // Temporarily store the new key to show it to the user
            session()->flash('newApiKey', $newKey);
            session()->flash('message', "API key regenerated for {$client->name}");
        }
    }

    public function toggleKeyVisibility($clientId)
    {
        if (in_array($clientId, $this->revealedKeys)) {
            $this->revealedKeys = array_diff($this->revealedKeys, [$clientId]);
        } else {
            $this->revealedKeys[] = $clientId;
        }
    }

    public function copyApiKey($clientId)
    {
        $client = Client::find($clientId);
        if ($client) {
            $plainKey = $client->getDecryptedApiKey();
            if ($plainKey) {
                // Emit event to frontend to copy to clipboard
                $this->dispatch('copyToClipboard', $plainKey);
            }
        }
    }


    public function deleteClient($clientId)
    {
        $client = Client::find($clientId);
        if ($client) {
            $clientName = $client->name;
            $messageCount = $client->smsMessages()->count();
            
            // Delete all related SMS messages first
            $client->smsMessages()->delete();
            
            // Then delete the client
            $client->delete();
            
            if ($messageCount > 0) {
                session()->flash('message', "Client '{$clientName}' et ses {$messageCount} messages SMS supprimés avec succès !");
            } else {
                session()->flash('message', "Client '{$clientName}' supprimé avec succès !");
            }
        }
    }

    public function render()
    {
        return view('livewire.client-manager', [
            'clients' => Client::withCount('smsMessages')->orderBy('created_at', 'desc')->paginate(10)
        ])->layout('components.layouts.app', ['title' => 'Client Management']);
    }
}
