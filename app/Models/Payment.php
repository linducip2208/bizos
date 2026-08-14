<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'company_id',
        'branch_id',
        'payment_number',
        'payment_date',
        'payment_method_id',
        'amount',
        'reference_number',
        'proof_path',
        'notes',
        'status',
        'confirmed_by',
        'confirmed_at',
        'currency_id',
        'exchange_rate',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'confirmed_at' => 'datetime',
        'exchange_rate' => 'decimal:6',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function invoicePayments()
    {
        return $this->hasMany(InvoicePayment::class);
    }

    public function invoices()
    {
        return $this->belongsToMany(Invoice::class, 'invoice_payments')
            ->withPivot('amount')
            ->withTimestamps();
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
