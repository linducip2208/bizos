<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComboProduct extends Model
{
    protected $fillable = [
        'product_id',
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function items()
    {
        return $this->hasMany(ComboItem::class, 'combo_id');
    }
}
