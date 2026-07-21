<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coa extends Model
{
    protected $table = 'coa';

    protected $fillable = [
        'company_id',
        'category_id',
        'parent_id',
        'code',
        'name',
        'description',
        'is_header',
        'opening_balance',
        'balance_type',
        'is_active',
        'cost_center_id',
        'profit_center_id',
    ];

    protected $casts = [
        'is_header' => 'boolean',
        'opening_balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function category()
    {
        return $this->belongsTo(CoaCategory::class, 'category_id');
    }

    public function parent()
    {
        return $this->belongsTo(Coa::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Coa::class, 'parent_id');
    }

    public function balances()
    {
        return $this->hasMany(CoaBalance::class);
    }

    public function journalEntries()
    {
        return $this->hasMany(JournalEntry::class);
    }

    public function budgetItems()
    {
        return $this->hasMany(BudgetItem::class);
    }

    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class);
    }

    public function profitCenter()
    {
        return $this->belongsTo(ProfitCenter::class);
    }

    public function openingBalances()
    {
        return $this->hasMany(OpeningBalance::class);
    }

    public function trialBalances()
    {
        return $this->hasMany(TrialBalance::class);
    }
}
