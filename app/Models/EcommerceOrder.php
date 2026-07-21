<?php

namespace App\Models;

use App\Concerns\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;

class EcommerceOrder extends Model
{
    use HasCompanyScope;

    protected $fillable = [
        'company_id',
        'channel_id',
        'channel_order_id',
        'order_date',
        'customer_name',
        'customer_phone',
        'customer_address',
        'shipping_method',
        'shipping_cost',
        'total_amount',
        'channel_status',
        'sync_status',
        'pos_transaction_id',
        'pos_refund_id',
    ];

    protected $casts = [
        'order_date' => 'datetime',
        'shipping_cost' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function channel()
    {
        return $this->belongsTo(EcommerceChannel::class, 'channel_id');
    }

    public function items()
    {
        return $this->hasMany(EcommerceOrderItem::class);
    }

    public function posTransaction()
    {
        return $this->belongsTo(PosTransaction::class, 'pos_transaction_id');
    }

    public function posRefund()
    {
        return $this->belongsTo(PosRefund::class, 'pos_refund_id');
    }
}
