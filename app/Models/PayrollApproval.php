<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollApproval extends Model
{
    protected $fillable = [
        'payroll_period_id',
        'approver_id',
        'level',
        'status',
        'comment',
    ];

    protected $casts = [
        'level' => 'integer',
        'status' => 'string',
    ];

    public function payrollPeriod()
    {
        return $this->belongsTo(PayrollPeriod::class);
    }

    public function approver()
    {
        return $this->belongsTo(Employee::class, 'approver_id');
    }
}
