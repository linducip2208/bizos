<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'attendance_id',
        'employee_id',
        'action',
        'latitude',
        'longitude',
        'photo',
        'wifi_bssid',
        'device_info',
        'ip_address',
        'notes',
        'created_at',
    ];

    protected $casts = [
        'action' => 'string',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'created_at' => 'datetime',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
