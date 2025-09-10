<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Client extends Model
{
    protected $fillable = [
        'name',
        'api_key',
        'rate_limit',
        'active',
        'allowed_ips',
        'description',
    ];

    protected $casts = [
        'allowed_ips' => 'array',
        'active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($client) {
            if (empty($client->api_key)) {
                $client->api_key = 'sk_' . Str::random(32);
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
        $this->api_key = 'sk_' . Str::random(32);
        $this->save();
        
        return $this->api_key;
    }
}
