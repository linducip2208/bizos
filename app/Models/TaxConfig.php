<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxConfig extends Model
{
    protected $fillable = [
        'company_id',
        'tax_type',
        'name',
        'rate',
        'effective_year',
        'is_active',
    ];

    protected $casts = [
        'rate' => 'decimal:4',
        'effective_year' => 'integer',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function taxTransactions()
    {
        return $this->hasMany(TaxTransaction::class);
    }
}
