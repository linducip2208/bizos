<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionUsage extends Model
{
    protected $table = 'subscription_usage';

    protected $fillable = [
        'company_id',
        'subscription_id',
        'metric',
        'usage_count',
        'recorded_at',
    ];

    protected $casts = [
        'usage_count' => 'decimal:4',
        'recorded_at' => 'date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
}
