<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbcClassification extends Model
{
    protected $fillable = ['company_id', 'product_id', 'classification', 'annual_consumption_value', 'cumulative_percent', 'calculated_at'];
    protected $casts = ['annual_consumption_value' => 'decimal:2', 'cumulative_percent' => 'decimal:2', 'calculated_at' => 'datetime'];

    public function company() { return $this->belongsTo(Company::class); }
    public function product() { return $this->belongsTo(Product::class); }
}
