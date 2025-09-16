<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class BulkSmsJob extends Model
{
    protected $fillable = [
        'client_id',
        'name',
        'content',
        'from',
        'recipients',
        'status',
        'total_count',
        'sent_count',
        'failed_count',
        'pending_count',
        'scheduled_at',
        'started_at',
        'completed_at',
        'settings',
        'failure_reason',
        'progress_percentage',
    ];

    protected $casts = [
        'recipients' => 'array',
        'settings' => 'array',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'progress_percentage' => 'decimal:2',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function smsMessages(): HasMany
    {
        return $this->hasMany(SmsMessage::class, 'bulk_job_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeScheduled($query)
    {
        return $query->whereNotNull('scheduled_at')
                    ->where('scheduled_at', '<=', now())
                    ->where('status', 'pending');
    }

    public function start()
    {
        $this->update([
            'status' => 'processing',
            'started_at' => now(),
        ]);
    }

    public function complete()
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'progress_percentage' => 100,
        ]);
    }

    public function fail(string $reason = null)
    {
        $this->update([
            'status' => 'failed',
            'failure_reason' => $reason,
        ]);
    }

    public function pause()
    {
        $this->update([
            'status' => 'paused',
        ]);
    }

    public function resume()
    {
        $this->update([
            'status' => 'processing',
        ]);
    }

    public function updateProgress()
    {
        $total = $this->total_count;
        $processed = $this->sent_count + $this->failed_count;
        
        if ($total > 0) {
            $percentage = ($processed / $total) * 100;
            $this->update([
                'progress_percentage' => round($percentage, 2),
                'pending_count' => $total - $processed,
            ]);

            if ($processed >= $total) {
                $this->complete();
            }
        }
    }

    public function getEstimatedDurationAttribute()
    {
        if (!$this->started_at || $this->sent_count === 0) {
            return null;
        }

        $elapsed = $this->started_at->diffInSeconds(now());
        $rate = $this->sent_count / $elapsed; // messages per second
        
        if ($rate > 0) {
            $remaining = $this->pending_count;
            return round($remaining / $rate); // seconds remaining
        }

        return null;
    }

    public function getSuccessRateAttribute()
    {
        $total = $this->sent_count + $this->failed_count;
        if ($total === 0) return 0;
        
        return round(($this->sent_count / $total) * 100, 2);
    }

    public function isScheduled(): bool
    {
        return $this->scheduled_at && $this->scheduled_at->isFuture();
    }

    public function canStart(): bool
    {
        return in_array($this->status, ['pending']) && 
               (!$this->scheduled_at || $this->scheduled_at->isPast());
    }

    public function canPause(): bool
    {
        return $this->status === 'processing';
    }

    public function canResume(): bool
    {
        return $this->status === 'paused';
    }
}
