<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WasteLog extends Model
{
    protected $table = 'waste_logs';

    protected $fillable = [
        'production_order_id',
        'product_id',
        'quantity',
        'unit',
        'waste_type',
        'reason',
        'cost_impact',
        'reported_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'cost_impact' => 'decimal:2',
    ];

    public $timestamps = ['created_at'];
    const UPDATED_AT = null;

    public function productionOrder()
    {
        return $this->belongsTo(ProductionOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function reporter()
    {
        return $this->belongsTo(Employee::class, 'reported_by');
    }
}
