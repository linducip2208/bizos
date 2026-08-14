<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    protected $fillable = [
        'company_id',
        'branch_id',
        'journal_number',
        'journal_date',
        'journal_type',
        'description',
        'total_debit',
        'total_credit',
        'reference_type',
        'reference_id',
        'status',
        'posted_by',
        'posted_at',
        'currency_id',
        'exchange_rate',
    ];

    protected $casts = [
        'journal_date' => 'date',
        'total_debit' => 'decimal:2',
        'total_credit' => 'decimal:2',
        'posted_at' => 'datetime',
        'exchange_rate' => 'decimal:6',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function postedBy()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class);
    }

    public function reference()
    {
        return $this->morphTo(__FUNCTION__, 'reference_type', 'reference_id');
    }

    public function assetDepreciations()
    {
        return $this->hasMany(AssetDepreciation::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
