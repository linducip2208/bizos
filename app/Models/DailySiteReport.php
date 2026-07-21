<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailySiteReport extends Model
{
    protected $fillable = [
        'company_id',
        'project_id',
        'report_date',
        'weather',
        'temperature',
        'worker_count',
        'heavy_equipment_used',
        'materials_used',
        'work_description',
        'progress_photo_path',
        'issues',
        'approved_by',
    ];

    protected $casts = [
        'report_date' => 'date',
        'temperature' => 'decimal:1',
        'heavy_equipment_used' => 'array',
        'materials_used' => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
