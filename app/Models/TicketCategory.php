<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketCategory extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'slug',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function slaPolicies()
    {
        return $this->hasMany(SlaPolicy::class, 'category_id');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'category_id');
    }
}
