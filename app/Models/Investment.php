<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Investment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'currency_id',
        'bank_account_id',
        'type',
        'name',
        'investment_number',
        'institution',
        'principal_amount',
        'current_value',
        'interest_rate_percent',
        'interest_type',
        'interest_payment_frequency',
        'start_date',
        'maturity_date',
        'next_interest_date',
        'total_accrued_interest',
        'total_interest_earned',
        'status',
        'notes',
        'metadata',
        'created_by',
    ];

    protected $casts = [
        'principal_amount' => 'decimal:2',
        'current_value' => 'decimal:2',
        'interest_rate_percent' => 'decimal:4',
        'start_date' => 'date',
        'maturity_date' => 'date',
        'next_interest_date' => 'date',
        'total_accrued_interest' => 'decimal:2',
        'total_interest_earned' => 'decimal:2',
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

    public function transactions()
    {
        return $this->hasMany(InvestmentTransaction::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeMaturingBetween($query, $from, $to)
    {
        return $query->whereBetween('maturity_date', [$from, $to]);
    }

    public function getDaysToMaturity(): ?int
    {
        if (!$this->maturity_date) return null;
        return (int) now()->startOfDay()->diffInDays($this->maturity_date, false);
    }
}
