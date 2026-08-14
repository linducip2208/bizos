<?php

namespace App\Services;

use App\Models\PosMember;
use App\Models\PriceListItem;
use App\Models\Product;

class CustomerGroupService
{
    /**
     * Tetapkan grup pelanggan ke customer POS (member).
     */
    public function assignGroup(int $customerId, int $groupId): void
    {
        PosMember::where('id', $customerId)->update(['customer_group_id' => $groupId]);
    }

    /**
     * Ambil pricing efektif untuk customer: price list + diskon grup.
     */
    public function getCustomerPricing(int $customerId): array
    {
        $member = PosMember::with('customerGroup')->find($customerId);
        $group = $member?->customerGroup;

        return [
            'price_list_id' => $group?->price_list_id,
            'discount_percent' => (float) ($group?->discount_percent ?? 0),
        ];
    }

    /**
     * Harga produk berdasarkan price list grup customer.
     * Fallback ke harga jual produk jika tidak ada di price list.
     */
    public function getGroupPrice(int $customerId, int $productId): float
    {
        $pricing = $this->getCustomerPricing($customerId);

        if ($pricing['price_list_id']) {
            $unitPrice = PriceListItem::where('price_list_id', $pricing['price_list_id'])
                ->where('product_id', $productId)
                ->value('unit_price');

            if ($unitPrice !== null) {
                return (float) $unitPrice;
            }
        }

        return (float) (Product::find($productId)?->selling_price ?? 0);
    }
}
