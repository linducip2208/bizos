<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FolioItem extends Model
{
    protected $fillable = [
        'folio_id',
        'service_id',
        'pos_transaction_id',
        'description',
        'amount',
        'charge_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'charge_date' => 'date',
    ];

    public function folio()
    {
        return $this->belongsTo(GuestFolio::class, 'folio_id');
    }

    public function service()
    {
        return $this->belongsTo(HotelService::class, 'service_id');
    }

    public function posTransaction()
    {
        return $this->belongsTo(PosTransaction::class, 'pos_transaction_id');
    }
}
