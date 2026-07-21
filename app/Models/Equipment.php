<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    protected $table = 'equipment';

    protected $fillable = [
        'company_id',
        'project_id',
        'name',
        'type',
        'status',
        'hourly_cost',
        'notes',
    ];

    protected $casts = [
        'hourly_cost' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function usages()
    {
        return $this->hasMany(EquipmentUsage::class);
    }
}
