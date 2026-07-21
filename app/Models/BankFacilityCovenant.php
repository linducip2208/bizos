<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankFacilityCovenant extends Model
{
    protected $fillable = [
        'company_id',
        'bank_facility_id',
        'name',
        'description',
        'metric',
        'requirement',
        'actual_value',
        'is_compliant',
        'last_tested_at',
        'next_test_date',
        'frequency',
        'status',
        'notes',
    ];

    protected $casts = [
        'is_compliant' => 'boolean',
        'last_tested_at' => 'date',
        'next_test_date' => 'date',
    ];

    public function facility()
    {
        return $this->belongsTo(BankFacility::class, 'bank_facility_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function scopeCompliant($query)
    {
        return $query->where('is_compliant', true);
    }

    public function scopeBreach($query)
    {
        return $query->where('status', 'breach');
    }
}
