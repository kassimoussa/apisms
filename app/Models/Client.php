<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use App\Services\ApiKeyEncryptionService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class Client extends Model
{
    protected $fillable = [
        'name',
        'username',
        'password',
        'email',
        'phone',
        'company',
        'address',
        'balance',
        'credit_limit',
        'currency',
        'daily_sms_limit',
        'monthly_sms_limit',
        'client_type',
        'industry',
        'website',
        'notification_settings',
        'webhook_settings',
        'auto_recharge',
        'auto_recharge_amount',
        'auto_recharge_threshold',
        'trial_ends_at',
        'suspended_at',
        'suspension_reason',
        'last_login_at',
        'status',
        'api_key_hash',
        'api_key_encrypted',
        'api_key_expires_at',
        'rate_limit',
        'active',
        'allowed_ips',
        'description',
    ];

    protected $casts = [
        'allowed_ips' => 'array',
        'active' => 'boolean',
        'auto_recharge' => 'boolean',
        'balance' => 'decimal:2',
        'credit_limit' => 'decimal:2',
        'auto_recharge_amount' => 'decimal:2',
        'auto_recharge_threshold' => 'decimal:2',
        'notification_settings' => 'array',
        'webhook_settings' => 'array',
        'api_key_expires_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'suspended_at' => 'datetime',
    ];

    protected $hidden = [
        'api_key_hash',
        'api_key_encrypted',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($client) {
            if (empty($client->api_key_hash) && empty($client->api_key_encrypted)) {
                $encryptionService = app(ApiKeyEncryptionService::class);
                $keyData = $encryptionService->generateExpiringApiKey('sk', 365);
                
                $client->api_key_hash = $keyData['hashed_key'];
                $client->api_key_encrypted = $keyData['encrypted_key'];
                $client->api_key_expires_at = $keyData['expires_at'];
            }
        });
    }

    public function smsMessages(): HasMany
    {
        return $this->hasMany(SmsMessage::class);
    }

    public function outboundMessages(): HasMany
    {
        return $this->hasMany(SmsMessage::class)->where('direction', 'outbound');
    }

    public function inboundMessages(): HasMany
    {
        return $this->hasMany(SmsMessage::class)->where('direction', 'inbound');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function isIpAllowed(?string $ip): bool
    {
        if (empty($this->allowed_ips)) {
            return true;
        }

        return in_array($ip, $this->allowed_ips);
    }

    public function regenerateApiKey(): string
    {
        $encryptionService = app(ApiKeyEncryptionService::class);
        $keyData = $encryptionService->generateExpiringApiKey('sk', 365);
        
        $this->api_key_hash = $keyData['hashed_key'];
        $this->api_key_encrypted = $keyData['encrypted_key'];
        $this->api_key_expires_at = $keyData['expires_at'];
        $this->save();
        
        return $keyData['plain_key'];
    }

    public function verifyApiKey(string $plainKey): bool
    {
        if (empty($this->api_key_hash)) {
            return false;
        }

        $encryptionService = app(ApiKeyEncryptionService::class);
        return $encryptionService->verifyApiKey($plainKey, $this->api_key_hash);
    }

    public function getDecryptedApiKey(): ?string
    {
        if (empty($this->api_key_encrypted)) {
            return null;
        }

        try {
            $encryptionService = app(ApiKeyEncryptionService::class);
            return $encryptionService->decryptApiKey($this->api_key_encrypted);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getMaskedApiKey(): ?string
    {
        $plainKey = $this->getDecryptedApiKey();
        if (!$plainKey) {
            return null;
        }

        $encryptionService = app(ApiKeyEncryptionService::class);
        return $encryptionService->maskApiKey($plainKey);
    }

    public function isApiKeyExpired(): bool
    {
        if (!$this->api_key_expires_at) {
            return false;
        }

        return $this->api_key_expires_at->isPast();
    }

    public function scopeWithValidApiKey($query)
    {
        return $query->where('active', true)
                    ->where(function ($q) {
                        $q->whereNull('api_key_expires_at')
                          ->orWhere('api_key_expires_at', '>', now());
                    });
    }

    /**
     * Authentication methods for web interface
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && $this->active;
    }

    public function setPassword(string $password): void
    {
        $this->password = Hash::make($password);
        $this->save();
    }

    public function checkPassword(string $password): bool
    {
        return Hash::check($password, $this->password);
    }

    public function updateLastLogin(): void
    {
        $this->last_login_at = now();
        $this->save();
    }

    public static function findByUsername(string $username): ?Client
    {
        return static::where('username', $username)
                    ->where('status', 'active')
                    ->first();
    }

    /**
     * New utility methods for advanced client management
     */
    public function isSuspended(): bool
    {
        return !is_null($this->suspended_at);
    }

    public function isOnTrial(): bool
    {
        return !is_null($this->trial_ends_at) && $this->trial_ends_at->isFuture();
    }

    public function hasLowBalance(): bool
    {
        return $this->auto_recharge && 
               $this->auto_recharge_threshold && 
               $this->balance <= $this->auto_recharge_threshold;
    }

    public function getDailySmsUsage(?\DateTime $date = null): int
    {
        $date = $date ?? now();
        return $this->smsMessages()
            ->whereDate('created_at', $date)
            ->count();
    }

    public function getMonthlySmsUsage(?\DateTime $date = null): int
    {
        $date = $date ?? now();
        return $this->smsMessages()
            ->whereYear('created_at', $date->year)
            ->whereMonth('created_at', $date->month)
            ->count();
    }

    public function getRemainingDailyQuota(): int
    {
        return max(0, $this->daily_sms_limit - $this->getDailySmsUsage());
    }

    public function getRemainingMonthlyQuota(): int
    {
        return max(0, $this->monthly_sms_limit - $this->getMonthlySmsUsage());
    }

    public function getClientTypeLabel(): string
    {
        return match($this->client_type) {
            'individual' => 'Individual',
            'business' => 'Business',
            'enterprise' => 'Enterprise',
            default => 'Unknown'
        };
    }

    public function suspend(string $reason): void
    {
        $this->update([
            'suspended_at' => now(),
            'suspension_reason' => $reason,
            'active' => false
        ]);
    }

    public function unsuspend(): void
    {
        $this->update([
            'suspended_at' => null,
            'suspension_reason' => null,
            'active' => true
        ]);
    }
}
