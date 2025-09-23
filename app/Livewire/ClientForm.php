<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Client;

class ClientForm extends Component
{
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
    
    // Limits
    public $daily_sms_limit = 1000;
    public $monthly_sms_limit = 30000;
    public $rate_limit = 60;
    
    // Trial
    public $trial_ends_at = '';
    
    // Technical
    public $allowed_ips = '';
    
    // UI State
    public $isEditing = false;
    public $clientId = null;
    
    public function mount($clientId = null)
    {
        if ($clientId) {
            $this->isEditing = true;
            $this->clientId = $clientId;
            $this->loadClient($clientId);
        }
    }
    
    public function loadClient($clientId)
    {
        $client = Client::find($clientId);
        if ($client) {
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
            $this->daily_sms_limit = $client->daily_sms_limit;
            $this->monthly_sms_limit = $client->monthly_sms_limit;
            $this->rate_limit = $client->rate_limit;
            $this->trial_ends_at = $client->trial_ends_at ? $client->trial_ends_at->format('Y-m-d') : '';
            $this->allowed_ips = is_array($client->allowed_ips) ? implode(', ', $client->allowed_ips) : '';
        }
    }


    public function save()
    {
        $rules = [
            // Basic Information
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:clients,username' . ($this->isEditing ? ',' . $this->clientId : ''),
            'description' => 'nullable|string',
            
            // Contact Information
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            
            // Business Information
            'client_type' => 'required|in:individual,business,enterprise',
            'industry' => 'nullable|string|max:255',
            'website' => 'nullable|string|max:255|regex:/^[a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?(\.[a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?)*$/',
            
            // Limits
            'daily_sms_limit' => 'required|integer|min:1|max:100000',
            'monthly_sms_limit' => 'required|integer|min:1|max:1000000',
            'rate_limit' => 'required|integer|min:1|max:1000',
            
            // Trial
            'trial_ends_at' => 'nullable|date|after:now',
            
            // Technical
            'allowed_ips' => 'nullable|string',
        ];

        if (!$this->isEditing) {
            $rules['password'] = 'required|string|min:6';
        }

        $this->validate($rules);

        $allowedIps = null;
        if ($this->allowed_ips) {
            $allowedIps = array_map('trim', explode(',', $this->allowed_ips));
        }

        $trialEndsAt = null;
        if ($this->trial_ends_at) {
            $trialEndsAt = \Carbon\Carbon::parse($this->trial_ends_at);
        }

        $data = [
            'name' => $this->name,
            'username' => $this->username,
            'description' => $this->description,
            'email' => $this->email,
            'phone' => $this->phone,
            'company' => $this->company,
            'address' => $this->address,
            'client_type' => $this->client_type,
            'industry' => $this->industry,
            'website' => $this->website,
            'daily_sms_limit' => $this->daily_sms_limit,
            'monthly_sms_limit' => $this->monthly_sms_limit,
            'rate_limit' => $this->rate_limit,
            'trial_ends_at' => $trialEndsAt,
            'allowed_ips' => $allowedIps,
        ];

        if ($this->isEditing) {
            $client = Client::find($this->clientId);
            $client->update($data);
            session()->flash('message', "Client '{$client->name}' mis à jour avec succès!");
        } else {
            $data['password'] = $this->password;
            $data['active'] = true;
            $data['status'] = 'active';
            $data['balance'] = 0;
            $data['credit_limit'] = 0;
            $data['currency'] = 'EUR';
            $data['auto_recharge'] = false;
            $data['auto_recharge_amount'] = null;
            $data['auto_recharge_threshold'] = null;
            
            $client = Client::create($data);
            $client->setPassword($this->password);
            
            session()->flash('message', "Client '{$client->name}' créé avec succès!");
        }

        return redirect()->route('admin.clients');
    }

    public function cancel()
    {
        return redirect()->route('admin.clients');
    }

    public function render()
    {
        $title = $this->isEditing ? 'Modifier le Client' : 'Créer un Client';
        return view('livewire.client-form')
            ->layout('components.layouts.app', ['title' => $title]);
    }
}