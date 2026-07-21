<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionOrderOperation extends Model
{
    protected $table = 'production_order_operations';

    protected $fillable = [
        'production_order_id',
        'routing_operation_id',
        'work_center_id',
        'planned_start',
        'planned_end',
        'actual_start',
        'actual_end',
        'status',
        'notes',
    ];

    protected $casts = [
        'planned_start' => 'datetime',
        'planned_end' => 'datetime',
        'actual_start' => 'datetime',
        'actual_end' => 'datetime',
    ];

    public function productionOrder()
    {
        return $this->belongsTo(ProductionOrder::class);
    }

    public function routingOperation()
    {
        return $this->belongsTo(RoutingOperation::class, 'routing_operation_id');
    }

    public function workCenter()
    {
        return $this->belongsTo(WorkCenter::class);
    }
}
