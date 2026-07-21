<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    protected $fillable = [
        'company_id',
        'product_id',
        'batch_number',
        'manufacturing_date',
        'expiry_date',
        'quantity',
        'unit_cost',
        'warehouse_id',
    ];

    protected $casts = [
        'manufacturing_date' => 'date',
        'expiry_date' => 'date',
        'quantity' => 'decimal:4',
        'unit_cost' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function serialNumbers()
    {
        return $this->hasMany(SerialNumber::class, 'batch_id');
    }
}
