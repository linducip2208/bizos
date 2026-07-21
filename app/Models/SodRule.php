<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SodRule extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'sensitive_function',
        'conflicting_function',
        'risk_level',
        'description',
        'compensating_controls',
        'is_active',
        'is_system_default',
        'category',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_system_default' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function conflicts()
    {
        return $this->hasMany(SodConflict::class);
    }
}
