<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubcontractOrder extends Model
{
    protected $table = 'subcontract_orders';

    protected $fillable = [
        'company_id',
        'supplier_id',
        'product_id',
        'quantity_sent',
        'quantity_received',
        'quantity_rejected',
        'sent_date',
        'expected_return',
        'actual_return',
        'status',
        'cost',
        'notes',
    ];

    protected $casts = [
        'quantity_sent' => 'decimal:4',
        'quantity_received' => 'decimal:4',
        'quantity_rejected' => 'decimal:4',
        'sent_date' => 'date',
        'expected_return' => 'date',
        'actual_return' => 'date',
        'cost' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
