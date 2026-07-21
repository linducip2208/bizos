<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiTemplate extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'description',
        'position_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function indicators()
    {
        return $this->hasMany(KpiIndicator::class, 'template_id')->orderBy('sort_order');
    }

    public function performanceReviews()
    {
        return $this->hasMany(PerformanceReview::class, 'kpi_template_id');
    }
}
