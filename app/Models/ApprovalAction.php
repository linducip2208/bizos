<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalAction extends Model
{
    protected $fillable = [
        'approval_request_id',
        'level_id',
        'approver_id',
        'action',
        'delegated_to',
        'comment',
        'action_at',
    ];

    protected $casts = [
        'delegated_to' => 'integer',
        'action_at' => 'datetime',
    ];

    public function request()
    {
        return $this->belongsTo(ApprovalRequest::class, 'approval_request_id');
    }

    public function level()
    {
        return $this->belongsTo(ApprovalLevel::class, 'level_id');
    }

    public function approver()
    {
        return $this->belongsTo(Employee::class, 'approver_id');
    }

    public function delegatedTo()
    {
        return $this->belongsTo(Employee::class, 'delegated_to');
    }
}
