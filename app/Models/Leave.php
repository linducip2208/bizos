<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Concerns\HasBranchScope;

class Leave extends Model
{
    use HasBranchScope;

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'total_days',
        'reason',
        'attachment',
        'status',
        'rejection_reason',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_days' => 'integer',
        'status' => 'string',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function leaveApprovals()
    {
        return $this->hasMany(LeaveApproval::class);
    }
}
