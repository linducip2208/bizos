<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeLoanInstallment extends Model
{
    protected $fillable = [
        'employee_loan_id',
        'payroll_id',
        'installment_number',
        'amount',
        'status',
        'due_date',
        'paid_date',
    ];

    protected $casts = [
        'installment_number' => 'integer',
        'amount' => 'decimal:2',
        'status' => 'string',
        'due_date' => 'date',
        'paid_date' => 'date',
    ];

    public function employeeLoan()
    {
        return $this->belongsTo(EmployeeLoan::class);
    }

    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }
}
