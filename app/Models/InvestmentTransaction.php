<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvestmentTransaction extends Model
{
    protected $fillable = [
        'company_id',
        'investment_id',
        'type',
        'transaction_date',
        'amount',
        'currency_id',
        'exchange_rate',
        'reference_number',
        'notes',
        'journal_entry_id',
        'created_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
    ];

    public function investment()
    {
        return $this->belongsTo(Investment::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
