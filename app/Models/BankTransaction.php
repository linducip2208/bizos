<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankTransaction extends Model
{
    protected $fillable = [
        'bank_account_id',
        'transaction_date',
        'transaction_type',
        'description',
        'reference_number',
        'amount',
        'is_reconciled',
        'reconciliation_id',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
        'is_reconciled' => 'boolean',
    ];

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function reconciliation()
    {
        return $this->belongsTo(BankReconciliation::class);
    }

    public function reconciliationItems()
    {
        return $this->hasMany(ReconciliationItem::class);
    }
}
