<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookController;

// Main login page - UNIFIED for both admin and client
Route::get('/', \App\Livewire\UnifiedLogin::class)->name('login');
Route::get('/login', \App\Livewire\UnifiedLogin::class)->name('unified.login');

// Logout routes
Route::get('/logout', function() {
    session()->flush();
    return redirect()->route('login')->with('success', 'Déconnecté avec succès');
})->name('logout');

// Legacy routes (redirects for backward compatibility)
Route::get('/admin/login', function() {
    return redirect()->route('login');
})->name('admin.login');
Route::get('/client/login', function() {
    return redirect()->route('login');
})->name('client.login');
Route::get('/admin/logout', function() {
    return redirect()->route('logout');
})->name('admin.logout');
Route::get('/client/logout', function() {
    return redirect()->route('logout');  
})->name('client.logout');

// Admin routes - PROTECTED with admin authentication
Route::middleware(['auth.web.admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', \App\Livewire\Dashboard::class)->name('admin.dashboard');
    Route::get('/clients', \App\Livewire\ClientManager::class)->name('admin.clients');
    Route::get('/clients/create', \App\Livewire\ClientForm::class)->name('admin.clients.create');
    Route::get('/clients/{clientId}/edit', \App\Livewire\ClientForm::class)->name('admin.clients.edit');
    Route::get('/clients/{client}', \App\Livewire\ClientProfile::class)->name('admin.clients.profile');
    Route::get('/test', \App\Livewire\SmsTest::class)->name('admin.test');
    Route::get('/responses', \App\Livewire\SmsResponses::class)->name('admin.responses');
});

// Temporary success page
Route::get('/client/dashboard-temp', function() {
    return '<h1>✅ Login Success! Welcome ' . session('client_name') . '</h1><p>Client ID: ' . session('client_id') . '</p><a href="/logout">Logout</a>';
});

// Protected client routes
Route::middleware(['auth.web.client'])->prefix('client')->group(function () {
    Route::get('/dashboard', \App\Livewire\Client\Dashboard::class)->name('client.dashboard');
    Route::get('/profile', \App\Livewire\Client\Profile::class)->name('client.profile');
    Route::get('/bulk-sms', \App\Livewire\BulkSmsManager::class)->name('client.bulk-sms');
    Route::get('/campaigns', \App\Livewire\Client\Campaigns::class)->name('client.campaigns');
    Route::get('/campaigns/{campaignId}', \App\Livewire\Client\CampaignDetails::class)->name('client.campaigns.details');
    Route::get('/statistics', \App\Livewire\Client\Statistics::class)->name('client.statistics');
    Route::get('/api-keys', \App\Livewire\Client\ApiKeys::class)->name('client.api-keys');
});

// Webhook endpoints for Kannel callbacks
Route::prefix('webhooks/kannel')->group(function () {
    Route::any('dlr', [WebhookController::class, 'handleDeliveryReport'])->name('webhooks.kannel.dlr');
    Route::any('mo', [WebhookController::class, 'handleIncomingSms'])->name('webhooks.kannel.mo');
});
