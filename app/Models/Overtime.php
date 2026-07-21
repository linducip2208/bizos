<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Concerns\HasBranchScope;

class Overtime extends Model
{
    use HasBranchScope;

    protected $fillable = [
        'employee_id',
        'date',
        'start_time',
        'end_time',
        'duration_minutes',
        'rate_multiplier',
        'reason',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'duration_minutes' => 'integer',
        'rate_multiplier' => 'decimal:2',
        'status' => 'string',
        'approved_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }
}
