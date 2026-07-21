<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CanteenMenu extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'description',
        'category',
        'price',
        'photo',
        'stock',
        'is_available',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
        'is_available' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function canteenOrderItems()
    {
        return $this->hasMany(CanteenOrderItem::class, 'menu_id');
    }
}
