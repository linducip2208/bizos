<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\PosMember;
use App\Models\Product;
use App\Models\ProductDiscount;
use App\Models\Promotion;

class DiscountEngineService
{
    public function evaluatePromotions(array $cartItems, ?int $customerId = null, float $subtotal = 0): array
    {
        $results = [];
        $member = $customerId ? PosMember::find($customerId) : null;
        $categoryMap = $this->categoryMap($cartItems);

        $promotions = Promotion::query()
            ->where('is_active', true)
            ->where('auto_apply', true)
            ->get();

        foreach ($promotions as $promotion) {
            if (!$this->matchesMember($promotion, $member)) {
                continue;
            }

            $applicableSubtotal = $this->applicableSubtotal($promotion, $cartItems, $subtotal, $categoryMap);

            if (!$promotion->isApplicable($applicableSubtotal, $cartItems)) {
                continue;
            }

            $amount = $this->promotionDiscountAmount($promotion, $applicableSubtotal, $cartItems);

            if ($amount <= 0) {
                continue;
            }

            $results[] = [
                'type' => 'promotion',
                'id' => $promotion->id,
                'name' => $promotion->name,
                'amount' => round($amount, 2),
                'description' => $this->describePromotion($promotion),
                'stacking_allowed' => (bool) $promotion->stacking_allowed,
            ];
        }

        usort($results, fn ($a, $b) => $b['amount'] <=> $a['amount']);

        return $results;
    }

    public function evaluateCoupons(array $cartItems, float $subtotal, ?int $customerId = null): array
    {
        $results = [];
        $companyId = $this->resolveCompanyId();

        $coupons = Coupon::query()
            ->where('is_active', true)
            ->where('auto_apply', true)
            ->when($companyId, function ($query) use ($companyId) {
                $query->where(function ($sub) use ($companyId) {
                    $sub->whereNull('promotion_id')
                        ->orWhereHas('promotion', fn ($p) => $p->where('company_id', $companyId));
                });
            })
            ->get();

        foreach ($coupons as $coupon) {
            if (!$coupon->isApplicable($subtotal)) {
                continue;
            }

            $amount = $coupon->discountAmount($subtotal);

            if ($amount <= 0) {
                continue;
            }

            $results[] = [
                'type' => 'coupon',
                'id' => $coupon->id,
                'name' => 'Kupon ' . $coupon->code,
                'amount' => round($amount, 2),
                'description' => 'Kupon otomatis diterapkan',
                'stacking_allowed' => false,
            ];
        }

        usort($results, fn ($a, $b) => $b['amount'] <=> $a['amount']);

        return $results;
    }

    public function evaluateProductDiscounts(array $cartItems): array
    {
        $subtotal = $this->cartSubtotal($cartItems);
        $results = [];
        $now = now()->startOfDay();

        $discounts = ProductDiscount::query()
            ->where('is_active', true)
            ->get();

        foreach ($discounts as $discount) {
            if ($discount->start_date && $now->lt($discount->start_date)) {
                continue;
            }

            if ($discount->end_date && $now->gt($discount->end_date)) {
                continue;
            }

            if ((float) $discount->min_purchase > 0 && $subtotal < (float) $discount->min_purchase) {
                continue;
            }

            $amount = $discount->type === 'percentage'
                ? round($subtotal * ((float) $discount->value / 100), 2)
                : min((float) $discount->value, $subtotal);

            if ($amount <= 0) {
                continue;
            }

            $results[] = [
                'type' => 'product_discount',
                'id' => $discount->id,
                'name' => $discount->name,
                'amount' => round($amount, 2),
                'description' => $discount->type === 'percentage'
                    ? 'Diskon produk ' . (float) $discount->value . '%'
                    : 'Diskon produk Rp ' . number_format((float) $discount->value, 0, ',', '.'),
                'stacking_allowed' => true,
            ];
        }

        usort($results, fn ($a, $b) => $b['amount'] <=> $a['amount']);

        return $results;
    }

    public function evaluateMemberDiscount(?int $customerId, float $subtotal): array
    {
        if (!$customerId) {
            return [];
        }

        $member = PosMember::find($customerId);

        if (!$member) {
            return [];
        }

        $percent = match ($member->tier ?? 'regular') {
            'gold' => 3.0,
            'platinum' => 5.0,
            default => 0.0,
        };

        if ($percent <= 0) {
            return [];
        }

        $amount = round($subtotal * $percent / 100, 2);

        return [[
            'type' => 'member',
            'id' => $member->id,
            'name' => 'Diskon Member ' . ucfirst($member->tier),
            'amount' => $amount,
            'description' => 'Diskon otomatis member tier ' . $member->tier . ' (' . $percent . '%)',
            'stacking_allowed' => true,
        ]];
    }

    public function calculateBestDiscount(array $cartItems, float $subtotal, ?int $customerId = null): array
    {
        $discounts = array_merge(
            $this->evaluateMemberDiscount($customerId, $subtotal),
            $this->evaluateProductDiscounts($cartItems),
            $this->evaluatePromotions($cartItems, $customerId, $subtotal),
            $this->evaluateCoupons($cartItems, $subtotal, $customerId),
        );

        $applied = $this->resolveStacking($discounts);

        $totalDiscount = 0.0;
        foreach ($applied as $discount) {
            $totalDiscount += (float) $discount['amount'];
        }

        $totalDiscount = min(round($totalDiscount, 2), $subtotal);

        return [
            'applied_discounts' => array_values($applied),
            'total_discount' => $totalDiscount,
            'final_total' => round(max(0, $subtotal - $totalDiscount), 2),
            'auto_applied' => $totalDiscount > 0,
        ];
    }

    public function resolveStacking(array $discounts): array
    {
        $members = [];
        $productDiscounts = [];
        $promotions = [];
        $coupons = [];

        foreach ($discounts as $discount) {
            switch ($discount['type'] ?? '') {
                case 'member':
                    $members[] = $discount;
                    break;
                case 'coupon':
                    $coupons[] = $discount;
                    break;
                case 'promotion':
                    $promotions[] = $discount;
                    break;
                default:
                    $productDiscounts[] = $discount;
            }
        }

        $bestPromotion = $this->best($promotions);
        $bestCoupon = $this->best($coupons);

        $promoAndCoupon = [];

        if ($bestPromotion && $bestCoupon) {
            $canStack = (bool) ($bestPromotion['stacking_allowed'] ?? false)
                || (bool) ($bestCoupon['stacking_allowed'] ?? false);

            $promoAndCoupon = $canStack
                ? [$bestPromotion, $bestCoupon]
                : [($bestPromotion['amount'] >= $bestCoupon['amount'] ? $bestPromotion : $bestCoupon)];
        } elseif ($bestPromotion) {
            $promoAndCoupon = [$bestPromotion];
        } elseif ($bestCoupon) {
            $promoAndCoupon = [$bestCoupon];
        }

        return array_merge($members, $productDiscounts, $promoAndCoupon);
    }

    protected function best(array $discounts): ?array
    {
        if (empty($discounts)) {
            return null;
        }

        $best = $discounts[0];

        foreach ($discounts as $discount) {
            if ((float) $discount['amount'] > (float) $best['amount']) {
                $best = $discount;
            }
        }

        return $best;
    }

    protected function resolveCompanyId(): ?int
    {
        $companyId = session('current_company_id');

        if (!$companyId && auth()->check()) {
            $companyId = auth()->user()->company_id;
        }

        return $companyId ? (int) $companyId : null;
    }

    protected function cartSubtotal(array $cartItems): float
    {
        $subtotal = 0.0;

        foreach ($cartItems as $item) {
            $subtotal += (float) ($item['quantity'] ?? 0) * (float) ($item['unit_price'] ?? 0);
        }

        return round($subtotal, 2);
    }

    protected function categoryMap(array $cartItems): array
    {
        $productIds = array_values(array_unique(array_filter(array_map(
            fn ($item) => $item['product_id'] ?? null,
            $cartItems,
        ))));

        if (empty($productIds)) {
            return [];
        }

        return Product::whereIn('id', $productIds)->pluck('category_id', 'id')->toArray();
    }

    protected function applicableSubtotal(Promotion $promotion, array $cartItems, float $subtotal, array $categoryMap): float
    {
        $appliesTo = $promotion->applies_to ?? 'all';
        $ids = $promotion->applies_to_ids ?? [];

        if ($appliesTo === 'all' || empty($ids)) {
            return $subtotal;
        }

        $total = 0.0;

        foreach ($cartItems as $item) {
            $productId = $item['product_id'] ?? null;

            if (!$productId) {
                continue;
            }

            $match = $appliesTo === 'products'
                ? in_array($productId, $ids)
                : in_array($categoryMap[$productId] ?? null, $ids);

            if ($match) {
                $total += (float) ($item['quantity'] ?? 0) * (float) ($item['unit_price'] ?? 0);
            }
        }

        return round($total, 2);
    }

    protected function matchesMember(Promotion $promotion, ?PosMember $member): bool
    {
        $config = $promotion->config ?? [];
        $tiers = $config['member_tiers'] ?? $config['member_tier'] ?? null;

        if (empty($tiers)) {
            return true;
        }

        if (!$member) {
            return false;
        }

        $tiers = (array) $tiers;
        $memberTier = $member->tier ?? 'regular';

        return in_array($memberTier, $tiers, true);
    }

    protected function promotionDiscountAmount(Promotion $promotion, float $subtotal, array $cartItems): float
    {
        $type = $promotion->type;
        $config = $promotion->config ?? [];

        if ($type === 'buy_x_get_y') {
            return round($this->buyXGetYAmount($config, $cartItems), 2);
        }

        if ($type === 'bundle') {
            return round($this->bundleAmount($config, $cartItems), 2);
        }

        $discountType = $promotion->discount_type ?: ($type === 'discount_percent' ? 'percentage' : 'fixed');

        $value = (float) ($promotion->discount_value ?? 0);
        if ($value <= 0) {
            $value = (float) ($config['percent'] ?? $config['amount'] ?? 0);
        }

        if ($discountType === 'percentage') {
            $amount = $subtotal * ($value / 100);
            $maxDiscount = (float) ($config['max_discount'] ?? 0);
            if ($maxDiscount > 0) {
                $amount = min($amount, $maxDiscount);
            }

            return round(max(0, min($amount, $subtotal)), 2);
        }

        return round(max(0, min($value, $subtotal)), 2);
    }

    protected function buyXGetYAmount(array $config, array $cartItems): float
    {
        $buyQty = (float) ($config['buy_qty'] ?? 0);
        $getQty = (float) ($config['get_qty'] ?? 0);
        $targetProductId = (int) ($config['free_product_id'] ?? $config['product_id'] ?? 0);

        if ($buyQty <= 0 || $getQty <= 0) {
            return 0;
        }

        foreach ($cartItems as $item) {
            $productId = (int) ($item['product_id'] ?? 0);

            if ($targetProductId && $productId !== $targetProductId) {
                continue;
            }

            $quantity = (float) ($item['quantity'] ?? 0);

            if ($quantity < $buyQty) {
                continue;
            }

            $unitPrice = $this->unitPriceOf($targetProductId ?: $productId, $cartItems);
            $times = floor($quantity / $buyQty);

            return $unitPrice * $getQty * $times;
        }

        return 0;
    }

    protected function bundleAmount(array $config, array $cartItems): float
    {
        $requiredIds = array_values(array_filter(array_map('intval', (array) ($config['product_ids'] ?? []))));

        if (empty($requiredIds)) {
            return 0;
        }

        $cartIds = array_map('intval', array_filter(array_map(
            fn ($item) => $item['product_id'] ?? null,
            $cartItems,
        )));

        if (count(array_intersect($requiredIds, $cartIds)) !== count($requiredIds)) {
            return 0;
        }

        $bundleSubtotal = 0.0;
        foreach ($cartItems as $item) {
            if (in_array((int) ($item['product_id'] ?? 0), $requiredIds, true)) {
                $bundleSubtotal += (float) ($item['quantity'] ?? 0) * (float) ($item['unit_price'] ?? 0);
            }
        }

        $percent = (float) ($config['discount_percent'] ?? 0);
        if ($percent > 0) {
            return $bundleSubtotal * $percent / 100;
        }

        return min((float) ($config['discount_amount'] ?? 0), $bundleSubtotal);
    }

    protected function unitPriceOf(int $productId, array $cartItems): float
    {
        foreach ($cartItems as $item) {
            if ((int) ($item['product_id'] ?? 0) === $productId) {
                return (float) ($item['unit_price'] ?? 0);
            }
        }

        $product = Product::find($productId);

        return $product ? (float) $product->selling_price : 0.0;
    }

    protected function describePromotion(Promotion $promotion): string
    {
        return match ($promotion->type) {
            'discount_percent' => 'Diskon ' . (float) ($promotion->discount_value ?? 0) . '% otomatis',
            'discount_amount' => 'Diskon Rp ' . number_format((float) ($promotion->discount_value ?? 0), 0, ',', '.') . ' otomatis',
            'buy_x_get_y' => 'Beli X Gratis Y otomatis',
            'bundle' => 'Diskon bundle otomatis',
            'free_shipping' => 'Gratis ongkir',
            default => 'Promosi otomatis',
        };
    }
}
