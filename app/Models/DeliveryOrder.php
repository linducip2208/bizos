<?php

namespace App\Models;

use App\Concerns\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;

class DeliveryOrder extends Model
{
    use HasCompanyScope;

    protected $fillable = [
        'company_id',
        'do_number',
        'invoice_id',
        'pos_transaction_id',
        'customer_name',
        'delivery_address',
        'delivery_date',
        'driver_id',
        'vehicle_id',
        'status',
        'estimated_arrival',
        'actual_arrival',
        'receiver_name',
        'receiver_signature_path',
        'pod_photo_path',
        'gps_lat',
        'gps_lng',
        'notes',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'estimated_arrival' => 'datetime',
        'actual_arrival' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function posTransaction()
    {
        return $this->belongsTo(PosTransaction::class, 'pos_transaction_id');
    }

    public function driver()
    {
        return $this->belongsTo(Employee::class, 'driver_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function items()
    {
        return $this->hasMany(DeliveryItem::class);
    }

    public function stops()
    {
        return $this->hasMany(DeliveryStop::class)->orderBy('stop_sequence');
    }

    public function coldChainLogs()
    {
        return $this->hasMany(ColdChainLog::class);
    }

    public function latestColdChainLog()
    {
        return $this->hasOne(ColdChainLog::class)->latestOfMany('recorded_at');
    }

    protected static function booted(): void
    {
        static::creating(function (DeliveryOrder $order) {
            if (!$order->do_number) {
                $prefix = 'DO-' . now()->format('ym');
                $last = static::where('do_number', 'like', $prefix . '%')
                    ->orderByDesc('do_number')
                    ->first();
                $num = $last ? (int) substr($last->do_number, 8) + 1 : 1;
                $order->do_number = $prefix . str_pad($num, 5, '0', STR_PAD_LEFT);
            }
        });
    }
}
