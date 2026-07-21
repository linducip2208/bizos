<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceConfig extends Model
{
    protected $fillable = [
        'company_id',
        'method',
        'gps_radius_meters',
        'gps_latitude',
        'gps_longitude',
        'require_selfie',
        'require_wfh_photo',
        'auto_clock_out',
        'auto_clock_out_time',
        'weekend_days',
    ];

    protected $casts = [
        'method' => 'array',
        'gps_radius_meters' => 'integer',
        'gps_latitude' => 'decimal:8',
        'gps_longitude' => 'decimal:8',
        'require_selfie' => 'boolean',
        'require_wfh_photo' => 'boolean',
        'auto_clock_out' => 'boolean',
        'auto_clock_out_time' => 'datetime',
        'weekend_days' => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
