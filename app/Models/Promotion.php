<?php

namespace App\Models;

use App\Concerns\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    use HasCompanyScope;

    protected $fillable = [
        'company_id',
        'name',
        'type',
        'config',
        'start_date',
        'end_date',
        'is_active',
        'discount_type',
        'discount_value',
        'min_purchase',
        'applies_to',
        'applies_to_ids',
        'auto_apply',
        'stacking_allowed',
    ];

    protected $casts = [
        'config' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'discount_value' => 'decimal:2',
        'min_purchase' => 'decimal:2',
        'applies_to_ids' => 'array',
        'auto_apply' => 'boolean',
        'stacking_allowed' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function coupons()
    {
        return $this->hasMany(Coupon::class);
    }

    public function isApplicable(float $subtotal, array $cartItems): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now()->startOfDay();

        if ($this->start_date && $now->lt($this->start_date)) {
            return false;
        }

        if ($this->end_date && $now->gt($this->end_date)) {
            return false;
        }

        if ((float) $this->min_purchase > 0 && $subtotal < (float) $this->min_purchase) {
            return false;
        }

        $appliesTo = $this->applies_to ?? 'all';

        if ($appliesTo === 'all') {
            return true;
        }

        $ids = $this->applies_to_ids ?? [];

        if (empty($ids)) {
            return false;
        }

        $productIds = array_values(array_filter(array_map(
            fn ($item) => $item['product_id'] ?? null,
            $cartItems,
        )));

        if (empty($productIds)) {
            return false;
        }

        if ($appliesTo === 'products') {
            return count(array_intersect($productIds, $ids)) > 0;
        }

        if ($appliesTo === 'category') {
            return Product::whereIn('id', $productIds)
                ->whereIn('category_id', $ids)
                ->exists();
        }

        return false;
    }
}
