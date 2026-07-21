<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankFacilityDrawdown extends Model
{
    protected $fillable = [
        'company_id',
        'bank_facility_id',
        'drawdown_date',
        'repayment_date',
        'amount',
        'interest_rate_percent',
        'outstanding_amount',
        'reference_number',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'drawdown_date' => 'date',
        'repayment_date' => 'date',
        'amount' => 'decimal:2',
        'interest_rate_percent' => 'decimal:4',
        'outstanding_amount' => 'decimal:2',
    ];

    public function facility()
    {
        return $this->belongsTo(BankFacility::class, 'bank_facility_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeOutstanding($query)
    {
        return $query->where('status', 'outstanding');
    }
}
