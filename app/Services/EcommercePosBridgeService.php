<?php

namespace App\Services;

use App\Models\EcommerceChannel;
use App\Models\EcommerceOrder;
use App\Models\EcommerceOrderItem;
use App\Models\PosMember;
use App\Models\PosPayment;
use App\Models\PosRefund;
use App\Models\PosTransaction;
use App\Models\PosTransactionItem;
use App\Models\Product;
use App\Models\StockBalance;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EcommercePosBridgeService
{
    /**
     * Sinkronkan e-commerce order ke POS transaction.
     * Mapping: customer → PosMember (auto-create jika baru)
     * Mapping: order items → PosTransactionItems (cocokkan by SKU)
     * Mapping: payment → PosPayment
     */
    public function syncOrderToPos(EcommerceOrder $order): PosTransaction
    {
        return DB::transaction(function () use ($order) {
            if ($order->pos_transaction_id) {
                throw new \InvalidArgumentException('Pesanan ini sudah disinkronkan ke POS #' . $order->posTransaction->receipt_number);
            }

            $member = $this->findOrCreateMember($order);

            $subtotal = $order->items()->sum('subtotal');
            $receiptNumber = $this->generateReceiptNumber($order->company_id, $order->channel->channel_name ?? 'ECO');

            $posTransaction = PosTransaction::create([
                'company_id' => $order->company_id,
                'member_id' => $member->id,
                'receipt_number' => $receiptNumber,
                'transaction_date' => $order->order_date ?? now(),
                'subtotal' => $subtotal,
                'discount_total' => 0,
                'tax_total' => $this->calculateTax($order),
                'grand_total' => $order->total_amount,
                'payment_status' => in_array($order->channel_status, ['paid', 'completed'])
                    ? 'paid'
                    : 'unpaid',
                'notes' => 'Dari ' . ($order->channel->channel_name ?? 'E-Commerce') . ' #' . $order->channel_order_id,
            ]);

            foreach ($order->items as $item) {
                $productId = $this->autoMatchSku($item->channel_sku)?->id ?? $item->product_id;

                PosTransactionItem::create([
                    'transaction_id' => $posTransaction->id,
                    'product_id' => $productId,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'discount_amount' => 0,
                    'tax_amount' => 0,
                    'subtotal' => $item->subtotal,
                ]);
            }

            if (in_array($order->channel_status, ['paid', 'completed'])) {
                PosPayment::create([
                    'transaction_id' => $posTransaction->id,
                    'payment_method' => $this->mapPaymentMethod($order),
                    'amount' => $order->total_amount,
                    'reference_number' => $order->channel_order_id,
                    'paid_at' => now(),
                ]);
            }

            $order->update([
                'sync_status' => 'synced',
                'pos_transaction_id' => $posTransaction->id,
            ]);

            return $posTransaction;
        });
    }

    /**
     * Sinkronkan inventory setelah e-commerce order (kurangi stok).
     */
    public function syncInventoryAfterOrder(EcommerceOrder $order): void
    {
        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $product = Product::find($item->product_id);
                if (!$product) continue;

                $qty = (float) $item->quantity;

                $product->decrement('stock', $qty);

                $balance = StockBalance::where('company_id', $order->company_id)
                    ->where('product_id', $item->product_id)
                    ->first();

                if ($balance) {
                    $balance->update([
                        'quantity' => max(0, (float) $balance->quantity - $qty),
                    ]);
                }

                StockMovement::create([
                    'company_id' => $order->company_id,
                    'product_id' => $item->product_id,
                    'movement_type' => 'out',
                    'reference_type' => 'ecommerce_order',
                    'reference_id' => $order->id,
                    'quantity_in' => 0,
                    'quantity_out' => $qty,
                    'unit_cost' => $product->purchase_price ?? 0,
                    'running_quantity' => $qty,
                    'running_cost' => round($qty * (float) ($product->purchase_price ?? 0), 2),
                    'notes' => 'Penjualan e-commerce #' . $order->channel_order_id,
                    'created_by' => auth()->id(),
                    'movement_date' => now(),
                ]);

                if ((float) $product->stock <= (float) $product->min_stock) {
                    $this->notifyLowStock($product, $order);
                }
            }
        });
    }

    /**
     * Auto-match SKU channel dengan produk yang ada.
     */
    public function autoMatchSku(string $channelSku): ?Product
    {
        return Product::where('code', $channelSku)
            ->orWhere('code', 'like', '%' . $channelSku . '%')
            ->orWhere('name', 'like', '%' . $channelSku . '%')
            ->where('is_active', true)
            ->first();
    }

    /**
     * Tangani return/refund dari e-commerce ke POS Refund.
     */
    public function syncRefund(EcommerceOrder $order): PosRefund
    {
        return DB::transaction(function () use ($order) {
            if ($order->pos_refund_id) {
                throw new \InvalidArgumentException('Refund sudah dibuat untuk pesanan ini.');
            }

            $posTransaction = $order->posTransaction;
            if (!$posTransaction) {
                throw new \InvalidArgumentException('Pesanan belum disinkronkan ke POS. Sinkronkan dulu sebelum refund.');
            }

            $refundNumber = 'REF-ECO-' . date('Ymd') . '-' . str_pad($order->id, 4, '0', STR_PAD_LEFT);

            $posRefund = PosRefund::create([
                'transaction_id' => $posTransaction->id,
                'refund_number' => $refundNumber,
                'amount' => $order->total_amount,
                'reason' => 'Retur dari ' . ($order->channel->channel_name ?? 'E-Commerce') . ' #' . $order->channel_order_id,
                'refund_date' => now(),
                'refunded_by' => auth()->id(),
            ]);

            foreach ($order->items as $item) {
                if ($item->product_id) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->increment('stock', (float) $item->quantity);

                        $balance = StockBalance::where('company_id', $order->company_id)
                            ->where('product_id', $item->product_id)
                            ->first();

                        if ($balance) {
                            $balance->update([
                                'quantity' => (float) $balance->quantity + (float) $item->quantity,
                            ]);
                        }

                        StockMovement::create([
                            'company_id' => $order->company_id,
                            'product_id' => $item->product_id,
                            'movement_type' => 'in',
                            'reference_type' => 'ecommerce_refund',
                            'reference_id' => $posRefund->id,
                            'quantity_in' => $item->quantity,
                            'quantity_out' => 0,
                            'unit_cost' => $item->unit_price,
                            'running_quantity' => $item->quantity,
                            'running_cost' => round((float) $item->quantity * (float) $item->unit_price, 2),
                            'notes' => 'Retur e-commerce #' . $order->channel_order_id,
                            'created_by' => auth()->id(),
                            'movement_date' => now(),
                        ]);
                    }
                }
            }

            $order->update([
                'sync_status' => 'synced',
                'pos_refund_id' => $posRefund->id,
                'channel_status' => 'cancelled',
            ]);

            if ($posTransaction) {
                $posTransaction->update(['payment_status' => 'refunded']);
            }

            return $posRefund;
        });
    }

    /**
     * Rekonsiliasi harian: cocokkan orders dari channel dengan POS transactions.
     * Returns: {matched: N, unmatched: N, discrepancies: [...]}
     */
    public function reconcileDaily(string $channel, string $date): array
    {
        $channels = EcommerceChannel::where('channel_name', $channel)
            ->where('is_active', true)
            ->get();

        if ($channels->isEmpty()) {
            throw new \InvalidArgumentException('Channel "' . $channel . '" tidak ditemukan atau tidak aktif.');
        }

        $reconDate = Carbon::parse($date)->startOfDay();
        $nextDay = $reconDate->copy()->addDay();

        $channelIds = $channels->pluck('id');

        $syncedCount = EcommerceOrder::whereIn('channel_id', $channelIds)
            ->whereBetween('order_date', [$reconDate, $nextDay])
            ->where('sync_status', 'synced')
            ->count();

        $unmatchedCount = EcommerceOrder::whereIn('channel_id', $channelIds)
            ->whereBetween('order_date', [$reconDate, $nextDay])
            ->where('sync_status', '!=', 'synced')
            ->count();

        $discrepancies = EcommerceOrder::whereIn('channel_id', $channelIds)
            ->whereBetween('order_date', [$reconDate, $nextDay])
            ->where('sync_status', 'synced')
            ->get()
            ->filter(function ($order) {
                if (!$order->posTransaction) return false;
                $diff = abs((float) $order->total_amount - (float) $order->posTransaction->grand_total);
                return $diff > 1;
            })
            ->map(function ($order) {
                return [
                    'channel_order_id' => $order->channel_order_id,
                    'ecommerce_total' => (float) $order->total_amount,
                    'pos_total' => (float) ($order->posTransaction->grand_total ?? 0),
                    'difference' => round((float) $order->total_amount - (float) ($order->posTransaction->grand_total ?? 0), 2),
                ];
            })
            ->values()
            ->toArray();

        return [
            'channel' => $channel,
            'date' => $date,
            'total_orders' => $syncedCount + $unmatchedCount,
            'matched' => $syncedCount,
            'unmatched' => $unmatchedCount,
            'discrepancies' => $discrepancies,
            'discrepancy_count' => count($discrepancies),
        ];
    }

    /**
     * Bulk sync: sync semua order pending menjadi POS transactions.
     */
    public function bulkSyncPendingOrders(?EcommerceChannel $channel = null): array
    {
        $query = EcommerceOrder::where('sync_status', 'pending')
            ->whereNotNull('pos_transaction_id');

        if ($channel) {
            $query->where('channel_id', $channel->id);
        }

        $orders = $query->get();

        $synced = 0;
        $failed = 0;
        $errors = [];

        foreach ($orders as $order) {
            try {
                $this->syncOrderToPos($order);
                $synced++;
            } catch (\Exception $e) {
                $failed++;
                $errors[] = [
                    'order_id' => $order->id,
                    'channel_order_id' => $order->channel_order_id,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'total' => $orders->count(),
            'synced' => $synced,
            'failed' => $failed,
            'errors' => $errors,
        ];
    }

    protected function findOrCreateMember(EcommerceOrder $order): PosMember
    {
        $phone = $order->customer_phone;
        if ($phone) {
            $member = PosMember::where('company_id', $order->company_id)
                ->where('phone', $phone)
                ->first();

            if ($member) return $member;
        }

        $memberCode = 'MEM-ECO-' . now()->format('Ymd') . '-' . str_pad($order->id, 4, '0', STR_PAD_LEFT);

        return PosMember::create([
            'company_id' => $order->company_id,
            'member_code' => $memberCode,
            'name' => $order->customer_name,
            'phone' => $order->customer_phone,
            'email' => $order->customer_phone . '@ecommerce.local',
            'points' => 0,
            'total_spent' => (float) $order->total_amount,
            'join_date' => now(),
            'is_active' => true,
            'points_balance' => 0,
            'tier' => 'regular',
            'total_points_earned' => 0,
        ]);
    }

    protected function generateReceiptNumber(int $companyId, string $channelPrefix): string
    {
        $prefix = strtoupper(substr($channelPrefix, 0, 3)) . '-' . date('Ymd');
        $last = PosTransaction::where('company_id', $companyId)
            ->where('receipt_number', 'like', $prefix . '%')
            ->orderBy('receipt_number', 'desc')
            ->first();

        if ($last) {
            $lastNum = (int) substr($last->receipt_number, -4);
            return $prefix . '-' . str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        }

        return $prefix . '-0001';
    }

    protected function calculateTax(EcommerceOrder $order): float
    {
        $taxableTotal = 0;
        foreach ($order->items as $item) {
            if ($item->product_id) {
                $product = Product::find($item->product_id);
                if ($product && $product->is_taxable) {
                    $taxableTotal += (float) $item->subtotal;
                }
            }
        }

        $taxRate = 0.11;
        return round($taxableTotal * $taxRate, 2);
    }

    protected function mapPaymentMethod(EcommerceOrder $order): string
    {
        $channel = strtolower($order->channel->channel_name ?? '');

        return match (true) {
            str_contains($channel, 'cod') => 'cash',
            str_contains($channel, 'va') || str_contains($channel, 'transfer') => 'bank_transfer',
            default => 'e_wallet',
        };
    }

    protected function notifyLowStock(Product $product, EcommerceOrder $order): void
    {
        \Illuminate\Support\Facades\Log::warning('Stok menipis setelah penjualan e-commerce', [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'current_stock' => $product->stock,
            'min_stock' => $product->min_stock,
            'triggered_by' => 'EcommerceOrder #' . $order->channel_order_id,
        ]);
    }
}
