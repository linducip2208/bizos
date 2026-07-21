<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrialBalance extends Model
{
    protected $fillable = [
        'company_id',
        'period_year',
        'period_month',
        'coa_id',
        'opening_debit',
        'opening_credit',
        'movement_debit',
        'movement_credit',
        'closing_debit',
        'closing_credit',
    ];

    protected $casts = [
        'opening_debit' => 'decimal:2',
        'opening_credit' => 'decimal:2',
        'movement_debit' => 'decimal:2',
        'movement_credit' => 'decimal:2',
        'closing_debit' => 'decimal:2',
        'closing_credit' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function coa()
    {
        return $this->belongsTo(Coa::class);
    }
}
