<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SmsController;
use App\Http\Controllers\Api\StatsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->group(function () {
    
    // SMS API endpoints - requires authentication
    Route::middleware(['auth.api.client'])->group(function () {
        
        // SMS endpoints
        Route::post('sms/send', [SmsController::class, 'send'])->name('api.sms.send');
        Route::get('sms', [SmsController::class, 'index'])->name('api.sms.index');
        Route::get('sms/{id}/status', [SmsController::class, 'status'])->name('api.sms.status');
        
        // Statistics endpoints
        Route::get('stats', [StatsController::class, 'index'])->name('api.stats');
        Route::get('stats/realtime', [StatsController::class, 'realtime'])->name('api.stats.realtime');
        
    });
    
});

// Health check endpoint - no authentication required
Route::get('health', function () {
    return response()->json([
        'status' => 'ok',
        'service' => 'ApiSMS Gateway',
        'version' => '1.0.0',
        'timestamp' => now()->toISOString(),
    ]);
})->name('api.health');