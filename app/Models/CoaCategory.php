<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoaCategory extends Model
{
    protected $fillable = [
        'company_id',
        'code',
        'name',
        'normal_balance',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function coas()
    {
        return $this->hasMany(Coa::class, 'category_id');
    }
}
