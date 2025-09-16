<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Admin;

class Login extends Component
{
    public $username = '';
    public $password = '';
    public $remember = false;

    protected $rules = [
        'username' => 'required|string',
        'password' => 'required|string',
    ];

    public function login()
    {
        $this->validate();

        // Find admin by username or email
        $admin = Admin::findByUsername($this->username) 
                ?? Admin::findByEmail($this->username);

        if (!$admin || !$admin->checkPassword($this->password)) {
            $this->addError('username', 'Identifiants incorrects.');
            return;
        }

        if (!$admin->isActive()) {
            $this->addError('username', 'Votre compte est désactivé.');
            return;
        }

        // Login successful
        $admin->updateLastLogin();
        
        session([
            'admin_id' => $admin->id,
            'admin_name' => $admin->name,
            'admin_role' => $admin->role,
        ]);

        session()->flash('success', 'Connexion réussie ! Bienvenue ' . $admin->name);
        
        return redirect()->route('admin.dashboard');
    }

    public function render()
    {
        return view('livewire.admin.login')
            ->layout('components.layouts.auth');
    }
}
