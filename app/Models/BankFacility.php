<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankFacility extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'currency_id',
        'bank_account_id',
        'bank_name',
        'facility_type',
        'facility_number',
        'name',
        'limit_amount',
        'utilized_amount',
        'available_amount',
        'interest_rate_percent',
        'commitment_fee_percent',
        'start_date',
        'expiry_date',
        'review_date',
        'status',
        'is_secured',
        'collateral_description',
        'collateral_value',
        'metadata',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'limit_amount' => 'decimal:2',
        'utilized_amount' => 'decimal:2',
        'available_amount' => 'decimal:2',
        'interest_rate_percent' => 'decimal:4',
        'commitment_fee_percent' => 'decimal:4',
        'start_date' => 'date',
        'expiry_date' => 'date',
        'review_date' => 'date',
        'is_secured' => 'boolean',
        'collateral_value' => 'decimal:2',
        'metadata' => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function drawdowns()
    {
        return $this->hasMany(BankFacilityDrawdown::class);
    }

    public function covenants()
    {
        return $this->hasMany(BankFacilityCovenant::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function getUtilizationPercent(): float
    {
        if ($this->limit_amount <= 0) return 0;
        return round(($this->utilized_amount / $this->limit_amount) * 100, 2);
    }

    public function getDaysToExpiry(): int
    {
        return (int) now()->startOfDay()->diffInDays($this->expiry_date, false);
    }
}
