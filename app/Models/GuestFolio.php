<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuestFolio extends Model
{
    protected $fillable = [
        'booking_id',
        'folio_number',
        'total_room_charges',
        'total_service_charges',
        'total_tax',
        'grand_total',
        'deposit_paid',
        'balance_due',
        'payment_status',
        'settled_at',
        'invoice_id',
        'payment_id',
    ];

    protected $casts = [
        'total_room_charges' => 'decimal:2',
        'total_service_charges' => 'decimal:2',
        'total_tax' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'deposit_paid' => 'decimal:2',
        'balance_due' => 'decimal:2',
        'settled_at' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(RoomBooking::class, 'booking_id');
    }

    public function folioItems()
    {
        return $this->hasMany(FolioItem::class, 'folio_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
