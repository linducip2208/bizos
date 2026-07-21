<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiIndicator extends Model
{
    protected $fillable = [
        'template_id',
        'name',
        'description',
        'category',
        'weight_percent',
        'target_type',
        'target_value',
        'measurement_unit',
        'data_source',
        'sort_order',
    ];

    protected $casts = [
        'category' => 'string',
        'weight_percent' => 'decimal:2',
        'target_type' => 'string',
        'target_value' => 'decimal:4',
        'sort_order' => 'integer',
    ];

    public function template()
    {
        return $this->belongsTo(KpiTemplate::class, 'template_id');
    }

    public function performanceReviewScores()
    {
        return $this->hasMany(PerformanceReviewScore::class, 'indicator_id');
    }
}
