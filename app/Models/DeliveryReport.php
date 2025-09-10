<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryReport extends Model
{
    protected $fillable = [
        'sms_message_id',
        'kannel_id',
        'status',
        'error_code',
        'error_message',
        'delivered_at',
        'raw_data',
    ];

    protected $casts = [
        'raw_data' => 'array',
        'delivered_at' => 'datetime',
    ];

    public function smsMessage(): BelongsTo
    {
        return $this->belongsTo(SmsMessage::class);
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function isDelivered(): bool
    {
        return $this->status === 'delivered';
    }

    public function isFailed(): bool
    {
        return in_array($this->status, ['failed', 'smsc_reject', 'smsc_unknown']);
    }

    public function isBuffered(): bool
    {
        return $this->status === 'buffered';
    }
}
