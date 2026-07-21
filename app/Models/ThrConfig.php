<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThrConfig extends Model
{
    protected $fillable = [
        'company_id',
        'religious_holiday',
        'min_months_service',
        'formula',
        'custom_formula',
        'payment_deadline_days',
        'is_active',
    ];

    protected $casts = [
        'min_months_service' => 'integer',
        'payment_deadline_days' => 'integer',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
