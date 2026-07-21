<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankTransfer extends Model
{
    protected $fillable = [
        'company_id',
        'from_account_id',
        'to_account_id',
        'transfer_date',
        'amount',
        'exchange_rate',
        'notes',
        'status',
        'created_by',
        'approved_by',
    ];

    protected $casts = [
        'transfer_date' => 'date',
        'amount' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function fromAccount()
    {
        return $this->belongsTo(BankAccount::class, 'from_account_id');
    }

    public function toAccount()
    {
        return $this->belongsTo(BankAccount::class, 'to_account_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
