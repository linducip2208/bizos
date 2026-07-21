<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionInvoice extends Model
{
    protected $fillable = [
        'company_id',
        'subscription_id',
        'invoice_number',
        'amount',
        'tax_amount',
        'total',
        'period_start',
        'period_end',
        'status',
        'payment_method',
        'paid_at',
        'due_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'period_start' => 'date',
        'period_end' => 'date',
        'paid_at' => 'datetime',
        'due_date' => 'date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function payments()
    {
        return $this->hasMany(SubscriptionPayment::class, 'invoice_id');
    }

    public function totalPaid(): float
    {
        return (float) $this->payments()
            ->where('status', 'confirmed')
            ->sum('amount');
    }

    public function remainingAmount(): float
    {
        return max(0, (float) $this->total - $this->totalPaid());
    }

    public function isFullyPaid(): bool
    {
        return $this->remainingAmount() <= 0;
    }

    public function isOverdue(): bool
    {
        return $this->status !== 'paid' && $this->due_date->isPast();
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu Pembayaran',
            'paid' => 'Lunas',
            'overdue' => 'Jatuh Tempo',
            'cancelled' => 'Dibatalkan',
            default => 'Tidak Diketahui',
        };
    }
}
