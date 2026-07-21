<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxTransaction extends Model
{
    protected $fillable = [
        'company_id',
        'tax_config_id',
        'reference_type',
        'reference_id',
        'base_amount',
        'tax_amount',
        'tax_date',
        'payment_status',
        'paid_date',
    ];

    protected $casts = [
        'base_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'tax_date' => 'date',
        'paid_date' => 'date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function taxConfig()
    {
        return $this->belongsTo(TaxConfig::class);
    }

    public function reference()
    {
        return $this->morphTo(__FUNCTION__, 'reference_type', 'reference_id');
    }
}
