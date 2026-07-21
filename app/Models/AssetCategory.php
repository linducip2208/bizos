<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetCategory extends Model
{
    protected $fillable = [
        'company_id',
        'code',
        'name',
        'depreciation_method',
        'useful_life_years',
        'salvage_value_percent',
    ];

    protected $casts = [
        'useful_life_years' => 'integer',
        'salvage_value_percent' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function assets()
    {
        return $this->hasMany(Asset::class, 'category_id');
    }
}
