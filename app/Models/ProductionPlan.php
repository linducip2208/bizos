<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionPlan extends Model
{
    protected $fillable = [
        'company_id',
        'product_id',
        'planned_quantity',
        'start_date',
        'end_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'planned_quantity' => 'decimal:4',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productionOrders()
    {
        return $this->hasMany(ProductionOrder::class, 'production_plan_id');
    }
}
