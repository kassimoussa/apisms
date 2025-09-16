<?php

namespace App\Livewire\Client;

use Livewire\Component;

class ApiKeys extends Component
{
    public $client;
    public $showNewToken = false;
    public $generatedToken = '';
    public $confirmRegenerate = false;
    public $showCurrentKey = false;

    public function mount()
    {
        $this->client = request()->attributes->get('client');
    }

    public function regenerateToken()
    {
        // Utilise la méthode existante du modèle Client
        $newToken = $this->client->regenerateApiKey();
        
        $this->generatedToken = $newToken;
        $this->showNewToken = true;
        $this->confirmRegenerate = false;
        
        session()->flash('success', 'Clé API régénérée avec succès!');
    }

    public function toggleShowCurrentKey()
    {
        $this->showCurrentKey = !$this->showCurrentKey;
    }

    public function cancelNewToken()
    {
        $this->showNewToken = false;
        $this->generatedToken = '';
        $this->confirmRegenerate = false;
    }

    public function copyToClipboard()
    {
        // Méthode appelée via JavaScript
        session()->flash('info', 'Clé API copiée dans le presse-papier!');
    }

    public function render()
    {
        return view('livewire.client.api-keys')
            ->layout('components.layouts.client');
    }
}
