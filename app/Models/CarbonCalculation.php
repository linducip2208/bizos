<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarbonCalculation extends Model
{
    protected $fillable = [
        'company_id', 'period', 'scope', 'emissions_tco2e',
        'breakdown', 'emission_factors_used', 'source_data', 'notes',
    ];

    protected $casts = [
        'emissions_tco2e' => 'decimal:4',
        'breakdown' => 'array',
        'emission_factors_used' => 'array',
        'source_data' => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
