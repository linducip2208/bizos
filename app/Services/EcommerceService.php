<?php

namespace App\Services;

use App\Models\EcommerceChannel;
use App\Models\EcommerceInventoryLog;
use App\Models\EcommerceOrder;
use App\Models\EcommerceOrderItem;
use App\Models\PosTransaction;
use App\Models\PosTransactionItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EcommerceService
{
    public function connectChannel(string $channel, string $apiKey, string $apiSecret): EcommerceChannel
    {
        return EcommerceChannel::create([
            'channel_name' => $channel,
            'api_key' => $apiKey,
            'api_secret' => $apiSecret,
            'is_active' => true,
        ]);
    }

    public function testConnection(EcommerceChannel $channel): bool
    {
        try {
            if (empty($channel->api_key) || empty($channel->api_secret)) {
                return false;
            }

            $channel->update(['last_sync_at' => now()]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function pullOrders(EcommerceChannel $channel, ?Carbon $since = null): array
    {
        $since = $since ?? now()->subDay();

        $newCount = 0;
        $syncedCount = 0;
        $failedCount = 0;

        $simulatedOrders = $this->simulateChannelOrders($channel, $since);

        foreach ($simulatedOrders as $orderData) {
            try {
                $existing = EcommerceOrder::where('channel_id', $channel->id)
                    ->where('channel_order_id', $orderData['channel_order_id'])
                    ->first();

                if ($existing) {
                    $existing->update([
                        'channel_status' => $orderData['channel_status'],
                        'total_amount' => $orderData['total_amount'],
                    ]);
                    $syncedCount++;
                    continue;
                }

                $order = EcommerceOrder::create([
                    'company_id' => $channel->company_id,
                    'channel_id' => $channel->id,
                    'channel_order_id' => $orderData['channel_order_id'],
                    'order_date' => $orderData['order_date'],
                    'customer_name' => $orderData['customer_name'],
                    'customer_phone' => $orderData['customer_phone'],
                    'customer_address' => $orderData['customer_address'],
                    'shipping_method' => $orderData['shipping_method'],
                    'shipping_cost' => $orderData['shipping_cost'],
                    'total_amount' => $orderData['total_amount'],
                    'channel_status' => $orderData['channel_status'],
                    'sync_status' => 'pending',
                ]);

                foreach ($orderData['items'] as $itemData) {
                    $product = $this->matchBySku($itemData['channel_sku']);

                    EcommerceOrderItem::create([
                        'ecommerce_order_id' => $order->id,
                        'channel_sku' => $itemData['channel_sku'],
                        'product_id' => $product?->id,
                        'product_name' => $itemData['product_name'],
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $itemData['unit_price'],
                        'subtotal' => $itemData['subtotal'],
                    ]);
                }

                $newCount++;
            } catch (\Exception $e) {
                $failedCount++;
            }
        }

        $channel->update(['last_sync_at' => now()]);

        return [
            'new_orders' => $newCount,
            'synced_count' => $syncedCount,
            'failed_count' => $failedCount,
            'since' => $since->format('Y-m-d H:i:s'),
        ];
    }

    private function simulateChannelOrders(EcommerceChannel $channel, Carbon $since): array
    {
        $products = Product::where('is_active', true)->inRandomOrder()->take(3)->get();

        if ($products->isEmpty()) {
            return [];
        }

        $orders = [];
        $numOrders = rand(1, 3);

        for ($i = 0; $i < $numOrders; $i++) {
            $items = [];
            $totalAmount = 0;
            $numItems = rand(1, 2);

            for ($j = 0; $j < $numItems; $j++) {
                $product = $products->random();
                $qty = rand(1, 3);
                $price = (float) $product->selling_price;
                $subtotal = $qty * $price;
                $totalAmount += $subtotal;

                $items[] = [
                    'channel_sku' => $product->code ?? 'SKU-' . $product->id,
                    'product_name' => $product->name,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'subtotal' => $subtotal,
                ];
            }

            $shippingCost = rand(10000, 50000);
            $totalAmount += $shippingCost;

            $statuses = ['unpaid', 'paid', 'shipped', 'delivered', 'completed'];

            $orders[] = [
                'channel_order_id' => strtoupper(substr($channel->channel_name, 0, 3)) . '-' . now()->format('ymd') . '-' . rand(1000, 9999),
                'order_date' => Carbon::now()->subMinutes(rand(5, 1440)),
                'customer_name' => 'Pelanggan ' . $channel->channel_name . ' ' . rand(1, 100),
                'customer_phone' => '08' . rand(100000000, 999999999),
                'customer_address' => 'Alamat pengiriman via ' . $channel->channel_name,
                'shipping_method' => collect(['JNE', 'J&T', 'SiCepat', 'GoSend', 'GrabExpress'])->random(),
                'shipping_cost' => $shippingCost,
                'total_amount' => $totalAmount,
                'channel_status' => $statuses[array_rand($statuses)],
                'items' => $items,
            ];
        }

        return $orders;
    }

    public function syncOrderToPos(EcommerceOrder $order): PosTransaction
    {
        return DB::transaction(function () use ($order) {
            $subtotal = $order->items()->sum('subtotal');

            $posTransaction = PosTransaction::create([
                'company_id' => $order->company_id,
                'receipt_number' => 'ECO-' . $order->channel_order_id,
                'transaction_date' => now(),
                'subtotal' => $subtotal,
                'discount_total' => 0,
                'tax_total' => 0,
                'grand_total' => $order->total_amount,
                'payment_status' => in_array($order->channel_status, ['paid', 'completed']) ? 'paid' : 'unpaid',
                'notes' => 'Dari ' . $order->channel->channel_name . ' #' . $order->channel_order_id,
            ]);

            foreach ($order->items as $item) {
                PosTransactionItem::create([
                    'transaction_id' => $posTransaction->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'subtotal' => $item->subtotal,
                ]);

                if ($item->product_id) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->decrement('stock', $item->quantity);
                    }
                }
            }

            $order->update([
                'sync_status' => 'synced',
                'pos_transaction_id' => $posTransaction->id,
            ]);

            return $posTransaction;
        });
    }

    public function pushInventory(EcommerceChannel $channel): void
    {
        $products = Product::where('is_active', true)->get();

        foreach ($products as $product) {
            $this->syncInventoryForProduct($product, $channel);
        }
    }

    public function syncInventoryForProduct(Product $product, ?EcommerceChannel $channel = null): void
    {
        $channels = $channel ? [$channel] : EcommerceChannel::where('is_active', true)->get();

        foreach ($channels as $ch) {
            $lastLog = EcommerceInventoryLog::where('channel_id', $ch->id)
                ->where('product_id', $product->id)
                ->latest('synced_at')
                ->first();

            $oldStock = $lastLog?->new_stock ?? $product->stock;
            $newStock = $product->stock;
            $channelStock = $product->stock;

            if ($oldStock != $newStock) {
                EcommerceInventoryLog::create([
                    'channel_id' => $ch->id,
                    'product_id' => $product->id,
                    'old_stock' => $oldStock,
                    'new_stock' => $newStock,
                    'channel_stock' => $channelStock,
                    'sync_status' => 'synced',
                    'synced_at' => now(),
                ]);
            }
        }
    }

    public function matchBySku(string $channelSku): ?Product
    {
        return Product::where('code', $channelSku)
            ->orWhere('name', 'like', "%{$channelSku}%")
            ->first();
    }

    public function suggestMatches(): array
    {
        $unmatched = EcommerceOrderItem::whereNull('product_id')
            ->distinct('channel_sku')
            ->get(['channel_sku']);

        $suggestions = [];

        foreach ($unmatched as $item) {
            $suggestions[] = [
                'channel_sku' => $item->channel_sku,
                'suggested_product' => $this->matchBySku($item->channel_sku),
            ];
        }

        return $suggestions;
    }

    public function getChannelPerformance(string $period): array
    {
        $date = match ($period) {
            'today' => now()->startOfDay(),
            'yesterday' => now()->subDay()->startOfDay(),
            'this_week' => now()->startOfWeek(),
            'this_month' => now()->startOfMonth(),
            'last_month' => now()->subMonth()->startOfMonth(),
            default => now()->startOfMonth(),
        };

        $orders = EcommerceOrder::where('order_date', '>=', $date)->get();

        $perChannel = $orders->groupBy('channel_id')
            ->map(function ($group, $channelId) {
                $channel = EcommerceChannel::find($channelId);
                return [
                    'channel_name' => $channel?->channel_name ?? 'Unknown',
                    'total_orders' => $group->count(),
                    'total_revenue' => $group->sum('total_amount'),
                    'synced' => $group->where('sync_status', 'synced')->count(),
                    'pending_sync' => $group->where('sync_status', 'pending')->count(),
                ];
            })
            ->values()
            ->toArray();

        $topProducts = EcommerceOrderItem::selectRaw('product_id, product_name, SUM(quantity) as total_qty, SUM(subtotal) as total_revenue')
            ->whereHas('ecommerceOrder', function ($q) use ($date) {
                $q->where('order_date', '>=', $date);
            })
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get()
            ->toArray();

        return [
            'total_orders' => $orders->count(),
            'total_revenue' => $orders->sum('total_amount'),
            'per_channel' => $perChannel,
            'top_products' => $topProducts,
            'sync_status' => [
                'synced' => $orders->where('sync_status', 'synced')->count(),
                'pending' => $orders->where('sync_status', 'pending')->count(),
                'failed' => $orders->where('sync_status', 'failed')->count(),
            ],
        ];
    }

    public function reconcileSettlement(EcommerceChannel $channel, string $period): array
    {
        $date = match ($period) {
            'this_month' => now()->startOfMonth(),
            'last_month' => now()->subMonth()->startOfMonth(),
            default => now()->startOfMonth(),
        };

        $orders = EcommerceOrder::where('channel_id', $channel->id)
            ->where('order_date', '>=', $date)
            ->get();

        $totalChannelAmount = $orders->sum('total_amount');
        $syncedOrders = $orders->where('sync_status', 'synced');

        $totalPosAmount = 0;
        foreach ($syncedOrders as $order) {
            if ($order->posTransaction) {
                $totalPosAmount += (float) $order->posTransaction->grand_total;
            }
        }

        $unmatched = $orders->filter(function ($order) use ($totalPosAmount, $syncedOrders) {
            return $order->sync_status !== 'synced';
        });

        return [
            'channel' => $channel->channel_name,
            'period' => $period,
            'total_channel_amount' => $totalChannelAmount,
            'total_pos_amount' => $totalPosAmount,
            'difference' => $totalChannelAmount - $totalPosAmount,
            'synced_orders' => $syncedOrders->count(),
            'unmatched_orders' => $unmatched->count(),
            'is_reconciled' => abs($totalChannelAmount - $totalPosAmount) < 1,
        ];
    }
}
