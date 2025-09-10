<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookController;

Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});

// Admin routes
Route::get('/admin/dashboard', \App\Livewire\Dashboard::class)->name('admin.dashboard');
Route::get('/admin/clients', \App\Livewire\ClientManager::class)->name('admin.clients');
Route::get('/admin/test', \App\Livewire\SmsTest::class)->name('admin.test');

// Webhook endpoints for Kannel callbacks
Route::prefix('webhooks/kannel')->group(function () {
    Route::any('dlr', [WebhookController::class, 'handleDeliveryReport'])->name('webhooks.kannel.dlr');
    Route::any('mo', [WebhookController::class, 'handleIncomingSms'])->name('webhooks.kannel.mo');
});
