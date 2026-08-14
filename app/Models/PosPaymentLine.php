<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosPaymentLine extends Model
{
    protected $fillable = [
        'pos_payment_id',
        'payment_method_id',
        'payment_method_name',
        'amount',
        'reference_number',
        'approval_code',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function posPayment()
    {
        return $this->belongsTo(PosPayment::class, 'pos_payment_id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }
}
