<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashierShift extends Model
{
    protected $fillable = [
        'employee_id',
        'branch_id',
        'shift_date',
        'opening_time',
        'opening_balance',
        'closing_time',
        'closing_balance',
        'expected_cash',
        'actual_cash',
        'difference',
        'total_transactions',
        'total_sales',
        'status',
        'notes',
    ];

    protected $casts = [
        'shift_date' => 'date',
        'opening_time' => 'datetime',
        'opening_balance' => 'decimal:2',
        'closing_time' => 'datetime',
        'closing_balance' => 'decimal:2',
        'expected_cash' => 'decimal:2',
        'actual_cash' => 'decimal:2',
        'difference' => 'decimal:2',
        'total_transactions' => 'integer',
        'total_sales' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function posTransactions()
    {
        return $this->hasMany(PosTransaction::class, 'shift_id');
    }
}
