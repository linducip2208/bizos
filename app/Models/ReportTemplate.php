<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportTemplate extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'slug',
        'description',
        'category',
        'query_type',
        'query_config',
        'chart_config',
        'is_system',
        'is_public',
        'created_by',
    ];

    protected $casts = [
        'query_config' => 'array',
        'chart_config' => 'array',
        'is_system' => 'boolean',
        'is_public' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function schedules()
    {
        return $this->hasMany(ReportSchedule::class);
    }

    public function snapshots()
    {
        return $this->hasMany(ReportSnapshot::class);
    }
}
