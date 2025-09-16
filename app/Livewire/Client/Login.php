<?php

namespace App\Livewire\Client;

use Livewire\Component;
use Illuminate\Support\Facades\Session;
use App\Models\Client;

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

        $client = Client::findByUsername($this->username);

        if (!$client || !$client->checkPassword($this->password)) {
            $this->addError('username', 'Invalid username or password.');
            return;
        }

        if (!$client->isActive()) {
            $this->addError('username', 'Your account is inactive. Please contact support.');
            return;
        }

        // Update last login
        $client->updateLastLogin();

        // Store client in session
        Session::put('client_id', $client->id);
        Session::put('client_name', $client->name);

        // Redirect to dashboard
        return redirect()->route('client.dashboard');
    }

    public function render()
    {
        return view('livewire.client.login')
            ->layout('components.layouts.client');
    }
}