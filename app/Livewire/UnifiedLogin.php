<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Admin;
use App\Models\Client;

class UnifiedLogin extends Component
{
    public $username = '';
    public $password = '';
    public $remember = false;
    public $loginType = 'auto'; // auto, client, admin

    protected $rules = [
        'username' => 'required|string',
        'password' => 'required|string',
    ];

    public function login()
    {
        $this->validate();

        // Try to authenticate as admin first
        if ($this->loginType === 'auto' || $this->loginType === 'admin') {
            $admin = Admin::findByUsername($this->username) 
                    ?? Admin::findByEmail($this->username);
            
            if ($admin && $admin->checkPassword($this->password)) {
                if (!$admin->isActive()) {
                    $this->addError('username', 'Votre compte admin est désactivé.');
                    return;
                }

                // Admin login successful
                $admin->updateLastLogin();
                
                session([
                    'admin_id' => $admin->id,
                    'admin_name' => $admin->name,
                    'admin_role' => $admin->role,
                ]);

                session()->flash('success', 'Connexion admin réussie ! Bienvenue ' . $admin->name);
                return redirect()->route('admin.dashboard');
            }
        }

        // Try to authenticate as client
        if ($this->loginType === 'auto' || $this->loginType === 'client') {
            $client = Client::findByUsername($this->username);
            
            if ($client && $client->checkPassword($this->password)) {
                if (!$client->isActive()) {
                    $this->addError('username', 'Votre compte client est désactivé.');
                    return;
                }

                // Client login successful
                $client->updateLastLogin();
                
                session([
                    'client_id' => $client->id,
                    'client_name' => $client->name,
                ]);

                session()->flash('success', 'Connexion client réussie ! Bienvenue ' . $client->name);
                return redirect()->route('client.dashboard');
            }
        }

        // No matching credentials found
        if ($this->loginType === 'admin') {
            $this->addError('username', 'Identifiants admin incorrects.');
        } elseif ($this->loginType === 'client') {
            $this->addError('username', 'Identifiants client incorrects.');
        } else {
            $this->addError('username', 'Identifiants incorrects. Vérifiez votre nom d\'utilisateur et mot de passe.');
        }
    }

    public function setLoginType($type)
    {
        $this->loginType = $type;
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.unified-login')
            ->layout('components.layouts.auth');
    }
}
