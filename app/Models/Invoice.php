<?php

namespace App\Models;

use App\Concerns\HasBranchScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Invoice extends Model
{
    use HasBranchScope;

    protected $fillable = [
        'company_id',
        'branch_id',
        'invoice_number',
        'invoice_type',
        'invoice_date',
        'due_date',
        'reference_entity',
        'reference_id',
        'sales_order_id',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total',
        'paid_amount',
        'remaining_amount',
        'status',
        'notes',
        'currency_id',
        'exchange_rate',
        'payment_token',
        'payment_link_sent_at',
        'payment_link_expires_at',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'payment_link_sent_at' => 'datetime',
        'payment_link_expires_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
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

    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function invoicePayments()
    {
        return $this->hasMany(InvoicePayment::class);
    }

    public function payments()
    {
        return $this->belongsToMany(Payment::class, 'invoice_payments')
            ->withPivot('amount')
            ->withTimestamps();
    }

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class);
    }

    public function assets()
    {
        return $this->hasMany(Asset::class, 'purchase_invoice_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function generatePaymentToken(): string
    {
        $this->payment_token = Str::random(40);
        $this->payment_link_sent_at = now();
        $this->payment_link_expires_at = now()->addDays(7);
        $this->save();

        return $this->payment_token;
    }

    public function getPaymentLinkUrl(): string
    {
        return url('/pay/'.$this->payment_token);
    }

    public function isPaymentLinkExpired(): bool
    {
        return $this->payment_link_expires_at && $this->payment_link_expires_at->isPast();
    }
}
