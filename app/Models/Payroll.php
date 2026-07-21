<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    protected $fillable = [
        'period_id',
        'employee_id',
        'gross_salary',
        'total_income_components',
        'total_deduction_components',
        'pph21_amount',
        'bpjs_tk_jht',
        'bpjs_tk_jp',
        'bpjs_tk_jkk',
        'bpjs_tk_jkm',
        'bpjs_kes',
        'net_salary',
        'attendance_days',
        'leave_days',
        'overtime_hours',
        'overtime_pay',
        'status',
        'notes',
    ];

    protected $casts = [
        'gross_salary' => 'decimal:2',
        'total_income_components' => 'decimal:2',
        'total_deduction_components' => 'decimal:2',
        'pph21_amount' => 'decimal:2',
        'bpjs_tk_jht' => 'decimal:2',
        'bpjs_tk_jp' => 'decimal:2',
        'bpjs_tk_jkk' => 'decimal:2',
        'bpjs_tk_jkm' => 'decimal:2',
        'bpjs_kes' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'attendance_days' => 'integer',
        'leave_days' => 'integer',
        'overtime_hours' => 'decimal:2',
        'overtime_pay' => 'decimal:2',
    ];

    public function period()
    {
        return $this->belongsTo(PayrollPeriod::class, 'period_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function payrollItems()
    {
        return $this->hasMany(PayrollItem::class);
    }

    public function paySlip()
    {
        return $this->hasOne(PaySlip::class);
    }
}
