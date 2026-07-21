<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReconciliationItem extends Model
{
    protected $fillable = [
        'reconciliation_id',
        'journal_entry_id',
        'bank_transaction_id',
        'matched_amount',
        'type',
        'notes',
    ];

    protected $casts = [
        'matched_amount' => 'decimal:2',
    ];

    public function reconciliation()
    {
        return $this->belongsTo(BankReconciliation::class);
    }

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function bankTransaction()
    {
        return $this->belongsTo(BankTransaction::class);
    }
}
