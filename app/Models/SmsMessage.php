<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class SmsMessage extends Model
{
    protected $fillable = [
        'client_id',
        'direction',
        'from',
        'to',
        'content',
        'status',
        'kannel_id',
        'error_code',
        'error_message',
        'sent_at',
        'delivered_at',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function deliveryReports(): HasMany
    {
        return $this->hasMany(DeliveryReport::class);
    }

    public function latestDeliveryReport(): HasMany
    {
        return $this->hasMany(DeliveryReport::class)->latest();
    }

    public function scopeOutbound($query)
    {
        return $query->where('direction', 'outbound');
    }

    public function scopeInbound($query)
    {
        return $query->where('direction', 'inbound');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function markAsSent(string $kannelId = null): void
    {
        $this->update([
            'status' => 'sent',
            'kannel_id' => $kannelId,
            'sent_at' => now(),
        ]);
    }

    public function markAsDelivered(?Carbon $deliveredAt = null): void
    {
        $this->update([
            'status' => 'delivered',
            'delivered_at' => $deliveredAt ?? now(),
        ]);
    }

    public function markAsFailed(string $errorCode = null, string $errorMessage = null): void
    {
        $this->update([
            'status' => 'failed',
            'error_code' => $errorCode,
            'error_message' => $errorMessage,
        ]);
    }

    public function isOutbound(): bool
    {
        return $this->direction === 'outbound';
    }

    public function isInbound(): bool
    {
        return $this->direction === 'inbound';
    }

    public function getFormattedFromAttribute(): string
    {
        return $this->formatPhoneNumber($this->from);
    }

    public function getFormattedToAttribute(): string
    {
        return $this->formatPhoneNumber($this->to);
    }

    private function formatPhoneNumber(string $number): string
    {
        // Format for Djibouti numbers
        if (!str_starts_with($number, '+')) {
            if (str_starts_with($number, '253')) {
                return '+' . $number;
            }
            return '+253' . $number;
        }
        
        return $number;
    }
}
