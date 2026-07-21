<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommissionSlab extends Model
{
    protected $fillable = ['company_id', 'min_amount', 'max_amount', 'rate_percent', 'sort_order', 'is_active'];
    protected $casts = ['min_amount' => 'decimal:2', 'max_amount' => 'decimal:2', 'rate_percent' => 'decimal:2', 'sort_order' => 'integer', 'is_active' => 'boolean'];

    public function company() { return $this->belongsTo(Company::class); }
}
