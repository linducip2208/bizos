<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquipmentUsage extends Model
{
    protected $fillable = [
        'equipment_id',
        'project_id',
        'date',
        'hours_used',
        'cost',
    ];

    protected $casts = [
        'date' => 'date',
        'hours_used' => 'decimal:2',
        'cost' => 'decimal:2',
    ];

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
