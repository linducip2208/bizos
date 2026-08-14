<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    protected $fillable = [
        'journal_id',
        'coa_id',
        'branch_id',
        'description',
        'debit',
        'credit',
        'currency_id',
        'exchange_rate',
    ];

    protected $casts = [
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
    ];

    public function journal()
    {
        return $this->belongsTo(Journal::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function coa()
    {
        return $this->belongsTo(Coa::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
