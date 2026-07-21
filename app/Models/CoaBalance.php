<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoaBalance extends Model
{
    protected $fillable = [
        'coa_id',
        'year',
        'month',
        'opening_balance',
        'debit_total',
        'credit_total',
        'closing_balance',
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'opening_balance' => 'decimal:2',
        'debit_total' => 'decimal:2',
        'credit_total' => 'decimal:2',
        'closing_balance' => 'decimal:2',
    ];

    public function coa()
    {
        return $this->belongsTo(Coa::class);
    }
}
