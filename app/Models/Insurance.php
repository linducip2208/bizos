<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Insurance extends Model
{
    protected $fillable = [
        'company_id',
        'patient_id',
        'insurance_provider',
        'policy_number',
        'coverage_type',
        'coverage_limit',
        'expiry_date',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'coverage_limit' => 'decimal:2',
        'expiry_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
