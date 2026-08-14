<?php

namespace App\Models;

use App\Concerns\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;

class CustomerGroup extends Model
{
    use HasCompanyScope;

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'price_list_id',
        'discount_percent',
        'credit_limit',
        'payment_terms',
        'is_active',
    ];

    protected $casts = [
        'discount_percent' => 'decimal:2',
        'credit_limit' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function priceList()
    {
        return $this->belongsTo(PriceList::class);
    }

    public function clients()
    {
        return $this->hasMany(Client::class);
    }

    public function members()
    {
        return $this->hasMany(PosMember::class);
    }
}
