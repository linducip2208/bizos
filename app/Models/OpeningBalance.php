<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpeningBalance extends Model
{
    protected $fillable = [
        'company_id',
        'coa_id',
        'period_year',
        'period_month',
        'debit_amount',
        'credit_amount',
    ];

    protected $casts = [
        'debit_amount' => 'decimal:2',
        'credit_amount' => 'decimal:2',
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
