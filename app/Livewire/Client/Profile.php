<?php

namespace App\Livewire\Client;

use Livewire\Component;
use App\Models\Client;
use Illuminate\Support\Facades\Hash;

class Profile extends Component
{
    // Personal Information
    public $name = '';
    public $email = '';
    public $phone = '';
    public $company = '';
    public $address = '';
    public $website = '';
    public $industry = '';
    
    // Password Change
    public $current_password = '';
    public $new_password = '';
    public $new_password_confirmation = '';
    
    // UI State
    public $showPasswordForm = false;
    
    public function mount()
    {
        $clientId = session('client_id');
        if (!$clientId) {
            return redirect()->route('login');
        }
        
        $client = Client::find($clientId);
        if (!$client) {
            return redirect()->route('login');
        }
        
        $this->name = $client->name;
        $this->email = $client->email;
        $this->phone = $client->phone;
        $this->company = $client->company;
        $this->address = $client->address;
        $this->website = $client->website;
        $this->industry = $client->industry;
    }
    
    public function updateProfile()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'website' => 'nullable|string|max:255|regex:/^[a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?(\.[a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?)*$/',
            'industry' => 'nullable|string|max:255',
        ]);
        
        $clientId = session('client_id');
        $client = Client::find($clientId);
        
        $client->update([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'company' => $this->company,
            'address' => $this->address,
            'website' => $this->website,
            'industry' => $this->industry,
        ]);
        
        // Update session name if changed
        if ($this->name !== session('client_name')) {
            session(['client_name' => $this->name]);
        }
        
        session()->flash('profile_success', 'Profil mis à jour avec succès!');
    }
    
    public function updatePassword()
    {
        $this->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);
        
        $clientId = session('client_id');
        $client = Client::find($clientId);
        
        if (!Hash::check($this->current_password, $client->password)) {
            $this->addError('current_password', 'Le mot de passe actuel est incorrect.');
            return;
        }
        
        $client->update([
            'password' => Hash::make($this->new_password)
        ]);
        
        // Reset form
        $this->current_password = '';
        $this->new_password = '';
        $this->new_password_confirmation = '';
        $this->showPasswordForm = false;
        
        session()->flash('password_success', 'Mot de passe modifié avec succès!');
    }
    
    public function togglePasswordForm()
    {
        $this->showPasswordForm = !$this->showPasswordForm;
        
        // Reset form when hiding
        if (!$this->showPasswordForm) {
            $this->current_password = '';
            $this->new_password = '';
            $this->new_password_confirmation = '';
            $this->resetErrorBag();
        }
    }

    public function render()
    {
        $clientId = session('client_id');
        $client = Client::find($clientId);
        
        return view('livewire.client.profile', [
            'client' => $client
        ])->layout('components.layouts.client', ['title' => 'Mon Profil']);
    }
}
