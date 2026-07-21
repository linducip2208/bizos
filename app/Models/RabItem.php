<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RabItem extends Model
{
    protected $fillable = [
        'company_id',
        'project_id',
        'parent_id',
        'item_code',
        'description',
        'unit',
        'quantity',
        'unit_price',
        'total_amount',
        'category',
        'weight_percent',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'weight_percent' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function parent()
    {
        return $this->belongsTo(RabItem::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(RabItem::class, 'parent_id');
    }
}
