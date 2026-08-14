<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ForecastItem extends Model
{
    protected $fillable = [
        'forecast_id',
        'coa_id',
        'description',
        'period_date',
        'planned_amount',
        'best_case_amount',
        'worst_case_amount',
        'probability_percent',
        'assumptions',
    ];

    protected $casts = [
        'period_date' => 'date',
        'planned_amount' => 'decimal:2',
        'best_case_amount' => 'decimal:2',
        'worst_case_amount' => 'decimal:2',
        'probability_percent' => 'decimal:2',
        'assumptions' => 'array',
    ];

    public function forecast()
    {
        return $this->belongsTo(Forecast::class);
    }

    public function coa()
    {
        return $this->belongsTo(Coa::class);
    }
}
