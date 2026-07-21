<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPayment extends Model
{
    protected $fillable = [
        'company_id',
        'invoice_id',
        'amount',
        'payment_method',
        'transaction_id',
        'payment_date',
        'proof_path',
        'status',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function invoice()
    {
        return $this->belongsTo(SubscriptionInvoice::class, 'invoice_id');
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu Konfirmasi',
            'confirmed' => 'Dikonfirmasi',
            'rejected' => 'Ditolak',
            default => 'Tidak Diketahui',
        };
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function confirm(): void
    {
        $this->update(['status' => 'confirmed']);

        $invoice = $this->invoice;
        if ($invoice->isFullyPaid()) {
            $invoice->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);
        }
    }

    public function reject(): void
    {
        $this->update(['status' => 'rejected']);
    }
}
