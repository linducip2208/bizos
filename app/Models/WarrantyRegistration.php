<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarrantyRegistration extends Model
{
    protected $fillable = [
        'company_id',
        'warranty_id',
        'product_id',
        'serial_number_id',
        'pos_transaction_id',
        'client_id',
        'start_date',
        'end_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function warranty()
    {
        return $this->belongsTo(Warranty::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function serialNumber()
    {
        return $this->belongsTo(SerialNumber::class, 'serial_number_id');
    }

    public function posTransaction()
    {
        return $this->belongsTo(PosTransaction::class, 'pos_transaction_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function claims()
    {
        return $this->hasMany(WarrantyClaim::class);
    }

    public function isActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        return $this->end_date && now()->startOfDay()->lte($this->end_date);
    }

    public function isExpired(): bool
    {
        if ($this->status === 'expired') {
            return true;
        }

        return $this->end_date && now()->startOfDay()->gt($this->end_date);
    }

    public function daysRemaining(): int
    {
        if (!$this->end_date) {
            return 0;
        }

        return max(0, (int) now()->startOfDay()->diffInDays($this->end_date, false));
    }

    public function effectiveStatus(): string
    {
        if ($this->status === 'void') {
            return 'void';
        }

        return $this->isExpired() ? 'expired' : 'active';
    }
}
