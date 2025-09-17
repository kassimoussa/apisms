<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Client;
use Livewire\WithPagination;

class ClientManager extends Component
{
    use WithPagination;

    // Basic Information
    public $name = '';
    public $username = '';
    public $password = '';
    public $description = '';
    
    // Contact Information
    public $email = '';
    public $phone = '';
    public $company = '';
    public $address = '';
    
    // Business Information
    public $client_type = 'individual';
    public $industry = '';
    public $website = '';
    
    // Billing & Limits
    public $balance = 0;
    public $credit_limit = 0;
    public $currency = 'EUR';
    public $daily_sms_limit = 1000;
    public $monthly_sms_limit = 30000;
    public $rate_limit = 60;
    
    // Auto-recharge
    public $auto_recharge = false;
    public $auto_recharge_amount = 0;
    public $auto_recharge_threshold = 0;
    
    // Trial
    public $trial_ends_at = '';
    
    // Technical
    public $allowed_ips = '';
    
    // UI State
    public $showCreateForm = false;
    public $showEditForm = false;
    public $selectedClient = null;
    public $revealedKeys = [];
    public $currentStep = 1;
    public $showAdvancedFields = false;
    
    // Edit client properties
    public $editingClientId = null;

    public function toggleCreateForm()
    {
        $this->showCreateForm = !$this->showCreateForm;
        $this->currentStep = 1;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset([
            'name', 'username', 'password', 'description', 'email', 'phone', 'company', 'address',
            'client_type', 'industry', 'website', 'balance', 'credit_limit', 'currency',
            'daily_sms_limit', 'monthly_sms_limit', 'rate_limit', 'auto_recharge',
            'auto_recharge_amount', 'auto_recharge_threshold', 'trial_ends_at', 'allowed_ips'
        ]);
        
        // Reset to defaults
        $this->client_type = 'individual';
        $this->currency = 'EUR';
        $this->daily_sms_limit = 1000;
        $this->monthly_sms_limit = 30000;
        $this->rate_limit = 60;
    }

    public function nextStep()
    {
        if ($this->currentStep < 4) {
            $this->currentStep++;
        }
    }

    public function prevStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function toggleAdvancedFields()
    {
        $this->showAdvancedFields = !$this->showAdvancedFields;
    }

    public function createClient()
    {
        $this->validate([
            // Basic Information
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:clients,username',
            'password' => 'required|string|min:6',
            'description' => 'nullable|string',
            
            // Contact Information
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            
            // Business Information
            'client_type' => 'required|in:individual,business,enterprise',
            'industry' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            
            // Billing & Limits
            'balance' => 'nullable|numeric|min:0',
            'credit_limit' => 'nullable|numeric|min:0',
            'currency' => 'required|string|size:3',
            'daily_sms_limit' => 'required|integer|min:1|max:100000',
            'monthly_sms_limit' => 'required|integer|min:1|max:1000000',
            'rate_limit' => 'required|integer|min:1|max:1000',
            
            // Auto-recharge
            'auto_recharge_amount' => 'nullable|numeric|min:0',
            'auto_recharge_threshold' => 'nullable|numeric|min:0',
            
            // Trial
            'trial_ends_at' => 'nullable|date|after:now',
            
            // Technical
            'allowed_ips' => 'nullable|string',
        ]);

        $allowedIps = null;
        if ($this->allowed_ips) {
            $allowedIps = array_map('trim', explode(',', $this->allowed_ips));
        }

        $trialEndsAt = null;
        if ($this->trial_ends_at) {
            $trialEndsAt = \Carbon\Carbon::parse($this->trial_ends_at);
        }

        $client = Client::create([
            // Basic Information
            'name' => $this->name,
            'username' => $this->username,
            'password' => $this->password, // Will be hashed by the model
            'description' => $this->description,
            
            // Contact Information
            'email' => $this->email,
            'phone' => $this->phone,
            'company' => $this->company,
            'address' => $this->address,
            
            // Business Information
            'client_type' => $this->client_type,
            'industry' => $this->industry,
            'website' => $this->website,
            
            // Billing & Limits
            'balance' => $this->balance ?? 0,
            'credit_limit' => $this->credit_limit ?? 0,
            'currency' => $this->currency,
            'daily_sms_limit' => $this->daily_sms_limit,
            'monthly_sms_limit' => $this->monthly_sms_limit,
            'rate_limit' => $this->rate_limit,
            
            // Auto-recharge
            'auto_recharge' => $this->auto_recharge,
            'auto_recharge_amount' => $this->auto_recharge ? $this->auto_recharge_amount : null,
            'auto_recharge_threshold' => $this->auto_recharge ? $this->auto_recharge_threshold : null,
            
            // Trial
            'trial_ends_at' => $trialEndsAt,
            
            // Technical
            'allowed_ips' => $allowedIps,
            'active' => true,
            'status' => 'active',
        ]);

        // Set password using the model method to ensure proper hashing
        $client->setPassword($this->password);

        $this->resetForm();
        $this->showCreateForm = false;
        session()->flash('message', "Client '{$client->name}' created successfully with username '{$client->username}'!");
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

    public function editClient($clientId)
    {
        $client = Client::find($clientId);
        if ($client) {
            $this->editingClientId = $clientId;
            
            // Populate form fields with client data
            $this->name = $client->name;
            $this->username = $client->username;
            $this->description = $client->description;
            $this->email = $client->email;
            $this->phone = $client->phone;
            $this->company = $client->company;
            $this->address = $client->address;
            $this->client_type = $client->client_type;
            $this->industry = $client->industry;
            $this->website = $client->website;
            $this->balance = $client->balance;
            $this->credit_limit = $client->credit_limit;
            $this->currency = $client->currency;
            $this->daily_sms_limit = $client->daily_sms_limit;
            $this->monthly_sms_limit = $client->monthly_sms_limit;
            $this->rate_limit = $client->rate_limit;
            $this->auto_recharge = $client->auto_recharge;
            $this->auto_recharge_amount = $client->auto_recharge_amount;
            $this->auto_recharge_threshold = $client->auto_recharge_threshold;
            $this->trial_ends_at = $client->trial_ends_at ? $client->trial_ends_at->format('Y-m-d') : '';
            $this->allowed_ips = is_array($client->allowed_ips) ? implode(', ', $client->allowed_ips) : '';
            
            $this->showEditForm = true;
            $this->currentStep = 1;
        }
    }

    public function updateClient()
    {
        $this->validate([
            // Basic Information
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:clients,username,' . $this->editingClientId,
            'description' => 'nullable|string',
            
            // Contact Information
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            
            // Business Information
            'client_type' => 'required|in:individual,business,enterprise',
            'industry' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            
            // Billing & Limits
            'balance' => 'nullable|numeric|min:0',
            'credit_limit' => 'nullable|numeric|min:0',
            'currency' => 'required|string|size:3',
            'daily_sms_limit' => 'required|integer|min:1|max:100000',
            'monthly_sms_limit' => 'required|integer|min:1|max:1000000',
            'rate_limit' => 'required|integer|min:1|max:1000',
            
            // Auto-recharge
            'auto_recharge_amount' => 'nullable|numeric|min:0',
            'auto_recharge_threshold' => 'nullable|numeric|min:0',
            
            // Trial
            'trial_ends_at' => 'nullable|date|after:now',
            
            // Technical
            'allowed_ips' => 'nullable|string',
        ]);

        $allowedIps = null;
        if ($this->allowed_ips) {
            $allowedIps = array_map('trim', explode(',', $this->allowed_ips));
        }

        $trialEndsAt = null;
        if ($this->trial_ends_at) {
            $trialEndsAt = \Carbon\Carbon::parse($this->trial_ends_at);
        }

        $client = Client::find($this->editingClientId);
        if ($client) {
            $client->update([
                // Basic Information
                'name' => $this->name,
                'username' => $this->username,
                'description' => $this->description,
                
                // Contact Information
                'email' => $this->email,
                'phone' => $this->phone,
                'company' => $this->company,
                'address' => $this->address,
                
                // Business Information
                'client_type' => $this->client_type,
                'industry' => $this->industry,
                'website' => $this->website,
                
                // Billing & Limits
                'balance' => $this->balance ?? 0,
                'credit_limit' => $this->credit_limit ?? 0,
                'currency' => $this->currency,
                'daily_sms_limit' => $this->daily_sms_limit,
                'monthly_sms_limit' => $this->monthly_sms_limit,
                'rate_limit' => $this->rate_limit,
                
                // Auto-recharge
                'auto_recharge' => $this->auto_recharge,
                'auto_recharge_amount' => $this->auto_recharge ? $this->auto_recharge_amount : null,
                'auto_recharge_threshold' => $this->auto_recharge ? $this->auto_recharge_threshold : null,
                
                // Trial
                'trial_ends_at' => $trialEndsAt,
                
                // Technical
                'allowed_ips' => $allowedIps,
            ]);

            $this->resetForm();
            $this->showEditForm = false;
            $this->editingClientId = null;
            session()->flash('message', "Client '{$client->name}' updated successfully!");
        }
    }

    public function cancelEdit()
    {
        $this->resetForm();
        $this->showEditForm = false;
        $this->editingClientId = null;
    }

    public function deleteClient($clientId)
    {
        $client = Client::find($clientId);
        if ($client) {
            // Check if client has SMS messages
            $messageCount = $client->smsMessages()->count();
            
            if ($messageCount > 0) {
                session()->flash('error', "Cannot delete client '{$client->name}' - they have {$messageCount} SMS messages. Please archive instead.");
                return;
            }
            
            $clientName = $client->name;
            $client->delete();
            session()->flash('message', "Client '{$clientName}' deleted successfully!");
        }
    }

    public function render()
    {
        return view('livewire.client-manager', [
            'clients' => Client::withCount('smsMessages')->orderBy('created_at', 'desc')->paginate(10)
        ])->layout('components.layouts.app', ['title' => 'Client Management']);
    }
}
