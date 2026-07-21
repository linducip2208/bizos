<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    protected $fillable = [
        'employee_id',
        'date',
        'visit_type',
        'location',
        'purpose',
        'start_time',
        'end_time',
        'check_in_lat',
        'check_in_lng',
        'check_out_lat',
        'check_out_lng',
        'status',
        'report',
    ];

    protected $casts = [
        'date' => 'date',
        'visit_type' => 'string',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'check_in_lat' => 'decimal:7',
        'check_in_lng' => 'decimal:7',
        'check_out_lat' => 'decimal:7',
        'check_out_lng' => 'decimal:7',
        'status' => 'string',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
