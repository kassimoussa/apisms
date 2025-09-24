<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Client;
use Carbon\Carbon;

class ClientProfile extends Component
{
    public Client $client;
    public $showEditModal = false;
    public $showResetPasswordModal = false;
    public $activeTab = 'overview';
    
    // Edit form properties
    public $editData = [];
    
    // Password reset properties
    public $newPassword = '';
    public $confirmPassword = '';
    public $forceLogout = true;

    public function mount(Client $client)
    {
        $this->client = $client->load('smsMessages');
        $this->initializeEditData();
    }

    public function initializeEditData()
    {
        $this->editData = [
            'name' => $this->client->name,
            'email' => $this->client->email,
            'phone' => $this->client->phone,
            'company' => $this->client->company,
            'address' => $this->client->address,
            'industry' => $this->client->industry,
            'website' => $this->client->website,
            'description' => $this->client->description,
            'daily_sms_limit' => $this->client->daily_sms_limit,
            'monthly_sms_limit' => $this->client->monthly_sms_limit,
            'rate_limit' => $this->client->rate_limit,
            'allowed_ips' => is_array($this->client->allowed_ips) ? implode(', ', $this->client->allowed_ips) : '',
        ];
    }

    public function switchTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function toggleEditModal()
    {
        $this->showEditModal = !$this->showEditModal;
        if (!$this->showEditModal) {
            $this->initializeEditData();
        }
    }

    public function updateClient()
    {
        $this->validate([
            'editData.name' => 'required|string|max:255',
            'editData.email' => 'nullable|email|max:255',
            'editData.phone' => 'nullable|string|max:20',
            'editData.company' => 'nullable|string|max:255',
            'editData.address' => 'nullable|string|max:500',
            'editData.industry' => 'nullable|string|max:255',
            'editData.website' => 'nullable|string|max:255|regex:/^[a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?(\.[a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?)*$/',
            'editData.description' => 'nullable|string',
            'editData.daily_sms_limit' => 'required|integer|min:1|max:100000',
            'editData.monthly_sms_limit' => 'required|integer|min:1|max:1000000',
            'editData.rate_limit' => 'required|integer|min:1|max:1000',
            'editData.allowed_ips' => 'nullable|string',
        ]);

        $allowedIps = null;
        if (!empty($this->editData['allowed_ips'])) {
            $allowedIps = array_map('trim', explode(',', $this->editData['allowed_ips']));
        }

        $this->client->update([
            'name' => $this->editData['name'],
            'email' => $this->editData['email'],
            'phone' => $this->editData['phone'],
            'company' => $this->editData['company'],
            'address' => $this->editData['address'],
            'industry' => $this->editData['industry'],
            'website' => $this->editData['website'],
            'description' => $this->editData['description'],
            'daily_sms_limit' => $this->editData['daily_sms_limit'],
            'monthly_sms_limit' => $this->editData['monthly_sms_limit'],
            'rate_limit' => $this->editData['rate_limit'],
            'allowed_ips' => $allowedIps,
        ]);

        $this->client->refresh();
        $this->showEditModal = false;
        session()->flash('message', 'Client updated successfully!');
    }

    public function toggleClientStatus()
    {
        $this->client->update(['active' => !$this->client->active]);
        $this->client->refresh();
    }

    public function suspendClient()
    {
        $this->client->suspend('Suspended by administrator');
        $this->client->refresh();
        session()->flash('message', 'Client suspended successfully!');
    }

    public function unsuspendClient()
    {
        $this->client->unsuspend();
        $this->client->refresh();
        session()->flash('message', 'Client unsuspended successfully!');
    }

    public function regenerateApiKey()
    {
        $newKey = $this->client->regenerateApiKey();
        session()->flash('newApiKey', $newKey);
        session()->flash('message', "API key regenerated for {$this->client->name}");
    }

    public function openResetPasswordModal()
    {
        $this->showResetPasswordModal = true;
        $this->newPassword = '';
        $this->confirmPassword = '';
        $this->forceLogout = true;
    }

    public function closeResetPasswordModal()
    {
        $this->showResetPasswordModal = false;
        $this->newPassword = '';
        $this->confirmPassword = '';
        $this->forceLogout = true;
    }

    public function resetPassword()
    {
        $this->validate([
            'newPassword' => 'required|string|min:6|max:255',
            'confirmPassword' => 'required|same:newPassword',
        ], [
            'newPassword.required' => 'Le nouveau mot de passe est requis.',
            'newPassword.min' => 'Le mot de passe doit contenir au moins 6 caractères.',
            'confirmPassword.required' => 'La confirmation est requise.',
            'confirmPassword.same' => 'Les mots de passe ne correspondent pas.',
        ]);

        // Update the client password
        $this->client->setPassword($this->newPassword);
        
        // Optional: Force logout by updating a session token or similar
        if ($this->forceLogout) {
            // Implementation depends on your session management
            // Could update last_password_change or similar field
        }

        $this->closeResetPasswordModal();
        session()->flash('message', "Mot de passe réinitialisé avec succès pour {$this->client->name}");
    }

    public function getUsageStatsProperty()
    {
        $now = Carbon::now();
        
        return [
            'daily_usage' => $this->client->getDailySmsUsage($now),
            'monthly_usage' => $this->client->getMonthlySmsUsage($now),
            'total_messages' => $this->client->smsMessages()->count(),
            'successful_messages' => $this->client->smsMessages()->where('status', 'delivered')->count(),
            'failed_messages' => $this->client->smsMessages()->where('status', 'failed')->count(),
            'pending_messages' => $this->client->smsMessages()->where('status', 'pending')->count(),
        ];
    }

    public function render()
    {
        return view('livewire.client-profile', [
            'usageStats' => $this->usageStats
        ])->layout('components.layouts.app', ['title' => "Client Profile - {$this->client->name}"]);
    }
}
