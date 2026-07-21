<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pph21Config extends Model
{
    protected $fillable = [
        'company_id',
        'ptkp_category',
        'ptkp_amount',
        'threshold_low',
        'rate_low',
        'threshold_mid',
        'rate_mid',
        'threshold_high',
        'rate_high',
        'rate_top',
        'effective_year',
        'is_active',
    ];

    protected $casts = [
        'ptkp_amount' => 'decimal:2',
        'threshold_low' => 'decimal:2',
        'rate_low' => 'decimal:4',
        'threshold_mid' => 'decimal:2',
        'rate_mid' => 'decimal:4',
        'threshold_high' => 'decimal:2',
        'rate_high' => 'decimal:4',
        'rate_top' => 'decimal:4',
        'effective_year' => 'integer',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
