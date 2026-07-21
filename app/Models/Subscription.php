<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'company_id',
        'plan_id',
        'started_at',
        'ends_at',
        'trial_ends_at',
        'status',
        'auto_renew',
        'cancelled_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ends_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'auto_renew' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function invoices()
    {
        return $this->hasMany(SubscriptionInvoice::class);
    }

    public function usage()
    {
        return $this->hasMany(SubscriptionUsage::class);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['trial', 'active', 'grace']);
    }

    public function isTrial(): bool
    {
        return $this->status === 'trial';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isGrace(): bool
    {
        return $this->status === 'grace';
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isCurrentlyActive(): bool
    {
        return in_array($this->status, ['trial', 'active', 'grace']);
    }

    public function daysRemaining(): int
    {
        if (!$this->ends_at) {
            return 0;
        }

        return max(0, (int) now()->diffInDays($this->ends_at, false));
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'trial' => 'Trial',
            'active' => 'Aktif',
            'grace' => 'Grace Period',
            'expired' => 'Kedaluwarsa',
            'cancelled' => 'Dibatalkan',
            default => 'Tidak Diketahui',
        };
    }
}
