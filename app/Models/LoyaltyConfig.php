<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoyaltyConfig extends Model
{
    protected $fillable = [
        'company_id',
        'earn_rate',
        'redeem_rate',
        'points_expiry_months',
        'silver_threshold',
        'gold_threshold',
        'platinum_threshold',
        'is_active',
    ];

    protected $casts = [
        'earn_rate' => 'decimal:2',
        'redeem_rate' => 'decimal:2',
        'points_expiry_months' => 'integer',
        'silver_threshold' => 'integer',
        'gold_threshold' => 'integer',
        'platinum_threshold' => 'integer',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
