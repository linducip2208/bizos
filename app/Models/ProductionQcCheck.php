<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionQcCheck extends Model
{
    protected $table = 'production_qc_checks';

    protected $fillable = [
        'production_order_id',
        'product_id',
        'check_type',
        'parameter',
        'specification',
        'result',
        'checked_by',
        'checked_at',
        'notes',
    ];

    protected $casts = [
        'checked_at' => 'datetime',
    ];

    public function productionOrder()
    {
        return $this->belongsTo(ProductionOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function checker()
    {
        return $this->belongsTo(Employee::class, 'checked_by');
    }
}
