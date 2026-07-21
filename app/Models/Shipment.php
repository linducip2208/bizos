<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    protected $fillable = [
        'company_id',
        'shipment_number',
        'carrier',
        'tracking_number',
        'shipment_date',
        'estimated_delivery',
        'actual_delivery',
        'status',
        'cost',
        'notes',
    ];

    protected $casts = [
        'shipment_date' => 'date',
        'estimated_delivery' => 'date',
        'actual_delivery' => 'date',
        'cost' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function items()
    {
        return $this->hasMany(ShipmentItem::class);
    }

    protected static function booted(): void
    {
        static::creating(function (Shipment $shipment) {
            if (!$shipment->shipment_number) {
                $prefix = 'SHP-' . now()->format('ym');
                $last = static::where('shipment_number', 'like', $prefix . '%')
                    ->orderByDesc('shipment_number')
                    ->first();
                $num = $last ? (int) substr($last->shipment_number, 8) + 1 : 1;
                $shipment->shipment_number = $prefix . str_pad($num, 5, '0', STR_PAD_LEFT);
            }
        });
    }
}
