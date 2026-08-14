<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BidItem extends Model
{
    protected $fillable = [
        'bid_id',
        'rfq_item_id',
        'description',
        'quantity',
        'unit_price',
        'total_price',
        'delivery_days',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'unit_price' => 'decimal:2',
            'total_price' => 'decimal:2',
        ];
    }

    public function bid()
    {
        return $this->belongsTo(Bid::class);
    }

    public function rfqItem()
    {
        return $this->belongsTo(RfqItem::class);
    }
}
