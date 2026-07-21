<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiDefinition extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'description',
        'category',
        'calculation_formula',
        'target_value',
        'unit',
        'data_source',
        'update_frequency',
        'is_active',
    ];

    protected $casts = [
        'target_value' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function values()
    {
        return $this->hasMany(KpiValue::class, 'kpi_definition_id')->orderBy('period', 'desc');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function latestValue(): ?KpiValue
    {
        return $this->values()->first();
    }
}
