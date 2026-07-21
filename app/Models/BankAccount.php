<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankAccount extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'currency_id',
        'bank_name',
        'account_number',
        'account_name',
        'branch',
        'opening_balance',
        'current_balance',
        'is_active',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function bankTransactions()
    {
        return $this->hasMany(BankTransaction::class);
    }

    public function bankReconciliations()
    {
        return $this->hasMany(BankReconciliation::class);
    }

    public function outboundTransfers()
    {
        return $this->hasMany(BankTransfer::class, 'from_account_id');
    }

    public function inboundTransfers()
    {
        return $this->hasMany(BankTransfer::class, 'to_account_id');
    }
}
