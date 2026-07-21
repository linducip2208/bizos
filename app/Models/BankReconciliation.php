<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankReconciliation extends Model
{
    protected $fillable = [
        'company_id',
        'bank_account_id',
        'period_start',
        'period_end',
        'opening_balance',
        'closing_balance',
        'statement_balance',
        'difference',
        'status',
        'completed_at',
        'completed_by',
        'notes',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'opening_balance' => 'decimal:2',
        'closing_balance' => 'decimal:2',
        'statement_balance' => 'decimal:2',
        'difference' => 'decimal:2',
        'completed_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function completedBy()
    {
        return $this->belongsTo(Employee::class, 'completed_by');
    }

    public function items()
    {
        return $this->hasMany(ReconciliationItem::class);
    }

    public function bankTransactions()
    {
        return $this->hasMany(BankTransaction::class);
    }

    public function calculateDifference(): void
    {
        $this->difference = $this->statement_balance - $this->closing_balance;
    }

    public function userCompleted(?Employee $employee = null): void
    {
        $this->status = 'completed';
        $this->completed_at = now();
        $this->completed_by = $employee?->id ?? auth()->user()?->employee?->id;
        $this->save();
    }
}
