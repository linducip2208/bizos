<?php

namespace App\Models;

use App\Concerns\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;

class PosHoldOrder extends Model
{
    use HasCompanyScope;

    protected $fillable = [
        'company_id',
        'branch_id',
        'cashier_id',
        'name',
        'items',
        'subtotal',
        'discount_total',
        'tax_total',
        'grand_total',
        'status',
        'held_at',
    ];

    protected $casts = [
        'items' => 'array',
        'subtotal' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'held_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function cashier()
    {
        return $this->belongsTo(Employee::class, 'cashier_id');
    }
}
