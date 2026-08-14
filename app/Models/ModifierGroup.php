<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModifierGroup extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'description',
        'selection_type',
        'min_selections',
        'max_selections',
        'is_required',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'min_selections' => 'integer',
        'max_selections' => 'integer',
        'is_required' => 'boolean',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function modifiers()
    {
        return $this->hasMany(Modifier::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_modifiers');
    }
}
