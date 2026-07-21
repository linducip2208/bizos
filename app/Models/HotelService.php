<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HotelService extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'category',
        'unit_price',
        'is_active',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function folioItems()
    {
        return $this->hasMany(FolioItem::class, 'service_id');
    }
}
