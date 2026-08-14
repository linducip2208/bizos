<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShiftDenomination extends Model
{
    protected $fillable = [
        'cashier_shift_id',
        'denomination_id',
        'count',
        'subtotal',
    ];

    protected $casts = [
        'count' => 'integer',
    ];

    public function shift()
    {
        return $this->belongsTo(CashierShift::class, 'cashier_shift_id');
    }

    public function denomination()
    {
        return $this->belongsTo(CashDenomination::class, 'denomination_id');
    }

    public function getSubtotalAttribute(): float
    {
        return round((int) ($this->count ?? 0) * (float) ($this->denomination?->value ?? 0), 2);
    }
}
