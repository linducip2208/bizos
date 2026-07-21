<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BomItem extends Model
{
    protected $table = 'bom_items';

    protected $fillable = [
        'bom_id',
        'product_id',
        'quantity_per_unit',
        'unit',
        'scrap_percent',
        'is_critical',
        'sort_order',
        'notes',
    ];

    protected $casts = [
        'quantity_per_unit' => 'decimal:4',
        'scrap_percent' => 'decimal:2',
        'is_critical' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function bom()
    {
        return $this->belongsTo(BillOfMaterial::class, 'bom_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function productionOrderMaterials()
    {
        return $this->hasMany(ProductionOrderMaterial::class, 'bom_item_id');
    }
}
