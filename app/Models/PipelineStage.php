<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PipelineStage extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'probability_percent',
        'color',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'probability_percent' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function deals()
    {
        return $this->hasMany(Deal::class, 'stage_id');
    }
}
