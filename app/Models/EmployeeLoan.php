<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeLoan extends Model
{
    protected $fillable = [
        'company_id',
        'employee_id',
        'loan_type',
        'amount',
        'approved_amount',
        'interest_rate',
        'installment_count',
        'installment_amount',
        'remaining_balance',
        'start_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'loan_type' => 'string',
        'amount' => 'decimal:2',
        'approved_amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'installment_count' => 'integer',
        'installment_amount' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
        'start_date' => 'date',
        'status' => 'string',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function installments()
    {
        return $this->hasMany(EmployeeLoanInstallment::class);
    }
}
