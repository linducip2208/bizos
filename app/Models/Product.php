<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'category_id',
        'brand_id',
        'unit_id',
        'code',
        'name',
        'product_type',
        'description',
        'unit',
        'purchase_price',
        'selling_price',
        'stock',
        'min_stock',
        'max_stock',
        'photo',
        'is_taxable',
        'tax_rate',
        'is_active',
        'has_batch',
        'has_serial',
        'is_medicine',
        'active_ingredient',
        'dosage_form',
        'strength',
        'registration_number',
        'requires_prescription',
        'drug_category',
        'storage_requirement',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'stock' => 'decimal:4',
        'min_stock' => 'decimal:4',
        'max_stock' => 'decimal:4',
        'is_taxable' => 'boolean',
        'tax_rate' => 'decimal:2',
        'is_active' => 'boolean',
        'has_batch' => 'boolean',
        'has_serial' => 'boolean',
        'is_medicine' => 'boolean',
        'requires_prescription' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function transactionItems()
    {
        return $this->hasMany(PosTransactionItem::class);
    }

    public function prescriptionItems()
    {
        return $this->hasMany(PrescriptionItem::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function productUnit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function barcodes()
    {
        return $this->hasMany(ProductBarcode::class, 'product_id');
    }

    public function batches()
    {
        return $this->hasMany(Batch::class);
    }

    public function serialNumbers()
    {
        return $this->hasMany(SerialNumber::class);
    }

    public function bom()
    {
        return $this->hasOne(BillOfMaterial::class, 'product_id')->where('is_active', true);
    }

    public function productionOrders()
    {
        return $this->hasMany(ProductionOrder::class);
    }
}
