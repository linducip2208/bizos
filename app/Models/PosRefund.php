<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosRefund extends Model
{
    protected $fillable = [
        'transaction_id',
        'refund_number',
        'amount',
        'reason',
        'refund_date',
        'refunded_by',
        'approved_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'refund_date' => 'datetime',
    ];

    public function transaction()
    {
        return $this->belongsTo(PosTransaction::class, 'transaction_id');
    }

    public function refundedBy()
    {
        return $this->belongsTo(Employee::class, 'refunded_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }
}
