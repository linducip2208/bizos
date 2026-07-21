<?php

namespace App\Models;

use Database\Factories\ShiftFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    /** @use HasFactory<ShiftFactory> */
    use HasFactory;
    protected $fillable = [
        'company_id',
        'name',
        'start_time',
        'end_time',
        'grace_period_minutes',
        'break_start',
        'break_end',
        'is_overnight',
        'is_active',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'break_start' => 'datetime',
        'break_end' => 'datetime',
        'is_overnight' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function shiftEmployees()
    {
        return $this->hasMany(ShiftEmployee::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}
