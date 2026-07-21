<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VirtualAccount extends Model
{
    protected $fillable = [
        'company_id', 'bank_account_id', 'va_number', 'bank', 'name',
        'expected_amount', 'paid_amount', 'status', 'expiry_at',
        'paid_at', 'paid_by', 'metadata', 'reference_entity', 'reference_id',
    ];

    protected $casts = [
        'expected_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'expiry_at' => 'datetime',
        'paid_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function reference()
    {
        return $this->morphTo('reference', 'reference_entity', 'reference_id');
    }

    public function markAsPaid(float $amount, string $paidBy = null): void
    {
        $this->update([
            'paid_amount' => $amount,
            'status' => $amount >= ($this->expected_amount ?? 0) ? 'paid' : 'partial',
            'paid_at' => now(),
            'paid_by' => $paidBy,
        ]);
    }
}
