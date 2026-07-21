<?php

namespace App\Models;

use App\Concerns\HasBranchScope;
use Database\Factories\AttendanceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    /** @use HasFactory<AttendanceFactory> */
    use HasBranchScope, HasFactory;
    protected $fillable = [
        'employee_id',
        'shift_id',
        'payroll_period_id',
        'date',
        'clock_in',
        'clock_out',
        'clock_in_lat',
        'clock_in_lng',
        'clock_out_lat',
        'clock_out_lng',
        'clock_in_photo',
        'clock_out_photo',
        'clock_in_wifi_bssid',
        'clock_out_wifi_bssid',
        'status',
        'late_minutes',
        'early_departure_minutes',
        'overtime_minutes',
        'work_type',
        'notes',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'date' => 'date',
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
        'clock_in_lat' => 'decimal:8',
        'clock_in_lng' => 'decimal:8',
        'clock_out_lat' => 'decimal:8',
        'clock_out_lng' => 'decimal:8',
        'status' => 'string',
        'late_minutes' => 'integer',
        'early_departure_minutes' => 'integer',
        'overtime_minutes' => 'integer',
        'work_type' => 'string',
        'approved_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function attendanceLogs()
    {
        return $this->hasMany(AttendanceLog::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }

    public function payrollPeriod()
    {
        return $this->belongsTo(PayrollPeriod::class);
    }
}
