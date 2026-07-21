<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Concerns\HasBranchScope;

class PosTransaction extends Model
{
    use HasBranchScope;

    protected $fillable = [
        'company_id',
        'shift_id',
        'receipt_number',
        'member_id',
        'cashier_id',
        'transaction_date',
        'subtotal',
        'discount_total',
        'tax_total',
        'grand_total',
        'payment_status',
        'notes',
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'subtotal' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function shift()
    {
        return $this->belongsTo(CashierShift::class, 'shift_id');
    }

    public function member()
    {
        return $this->belongsTo(PosMember::class, 'member_id');
    }

    public function cashier()
    {
        return $this->belongsTo(Employee::class, 'cashier_id');
    }

    public function items()
    {
        return $this->hasMany(PosTransactionItem::class, 'transaction_id');
    }

    public function payments()
    {
        return $this->hasMany(PosPayment::class, 'transaction_id');
    }

    public function refunds()
    {
        return $this->hasMany(PosRefund::class, 'transaction_id');
    }
}
