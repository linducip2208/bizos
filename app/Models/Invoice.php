<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Concerns\HasBranchScope;

class Invoice extends Model
{
    use HasBranchScope;

    protected $fillable = [
        'company_id',
        'invoice_number',
        'invoice_type',
        'invoice_date',
        'due_date',
        'reference_entity',
        'reference_id',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total',
        'paid_amount',
        'remaining_amount',
        'status',
        'notes',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
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

    public function assets()
    {
        return $this->hasMany(Asset::class, 'purchase_invoice_id');
    }
}
