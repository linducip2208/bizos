<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CanteenOrder extends Model
{
    protected $fillable = [
        'employee_id',
        'order_date',
        'status',
        'total_amount',
        'notes',
    ];

    protected $casts = [
        'order_date' => 'date',
        'status' => 'string',
        'total_amount' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function canteenOrderItems()
    {
        return $this->hasMany(CanteenOrderItem::class, 'order_id');
    }
}
