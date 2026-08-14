<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RfqItem extends Model
{
    protected $fillable = [
        'rfq_id',
        'product_id',
        'description',
        'quantity',
        'unit_id',
        'specifications',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'specifications' => 'json',
        ];
    }

    public function rfq()
    {
        return $this->belongsTo(Rfq::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function bidItems()
    {
        return $this->hasMany(BidItem::class);
    }
}
