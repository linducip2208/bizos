<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoutingOperation extends Model
{
    protected $table = 'routing_operations';

    protected $fillable = [
        'product_id',
        'bom_id',
        'work_center_id',
        'operation_name',
        'sequence',
        'setup_time_minutes',
        'run_time_minutes_per_unit',
        'notes',
    ];

    protected $casts = [
        'sequence' => 'integer',
        'setup_time_minutes' => 'decimal:2',
        'run_time_minutes_per_unit' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function bom()
    {
        return $this->belongsTo(BillOfMaterial::class, 'bom_id');
    }

    public function workCenter()
    {
        return $this->belongsTo(WorkCenter::class);
    }

    public function productionOrderOperations()
    {
        return $this->hasMany(ProductionOrderOperation::class);
    }
}
