<?php

namespace App\Services;

use App\Models\KitchenOrder;
use App\Models\KitchenOrderItem;
use App\Models\PosTransaction;
use App\Models\ResTable;
use Illuminate\Support\Collection;

class KitchenDisplayService
{
    /**
     * Resolve company id aktif (dari session atau user).
     */
    public function resolveCompanyId(): int
    {
        $companyId = session('current_company_id') ?? auth()->user()?->company_id;

        if (!$companyId) {
            throw new \RuntimeException('Perusahaan tidak ditemukan untuk user aktif.');
        }

        return (int) $companyId;
    }

    public function resolveBranchId(): ?int
    {
        return auth()->user()?->employee?->branch_id;
    }

    /**
     * Generate nomor order dapur per perusahaan: KD-YYYYMMDD-XXXX
     */
    public function generateOrderNumber(?int $companyId = null): string
    {
        $companyId = $companyId ?: $this->resolveCompanyId();
        $prefix = 'KD-' . date('Ymd') . '-';

        $last = KitchenOrder::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('order_number', 'like', $prefix . '%')
            ->orderBy('order_number', 'desc')
            ->first();

        $lastNum = 0;
        if ($last) {
            $lastNum = (int) substr($last->order_number, -4);
        }

        return $prefix . str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Buat kitchen order dari transaksi POS (khusus dine-in / takeaway).
     */
    public function createOrderFromPos(PosTransaction $tx, ?int $tableId = null): KitchenOrder
    {
        $serviceType = $tx->serviceType;

        if (!$serviceType || !in_array($serviceType->type, ['dine_in', 'takeaway'], true)) {
            throw new \InvalidArgumentException('Kitchen order hanya untuk tipe layanan dine-in atau takeaway.');
        }

        $order = KitchenOrder::create([
            'company_id' => $tx->company_id,
            'pos_transaction_id' => $tx->id,
            'table_id' => $tableId,
            'order_number' => $this->generateOrderNumber($tx->company_id),
            'status' => KitchenOrder::STATUS_PENDING,
            'priority' => KitchenOrder::PRIORITY_NORMAL,
            'note' => $tx->notes,
            'ordered_at' => now(),
            'created_by' => auth()->id(),
        ]);

        foreach ($tx->items as $item) {
            KitchenOrderItem::create([
                'kitchen_order_id' => $order->id,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'notes' => null,
                'modifiers' => null,
                'status' => KitchenOrder::STATUS_PENDING,
            ]);
        }

        if ($tableId) {
            ResTable::where('id', $tableId)->update(['status' => 'occupied']);
        }

        return $order->fresh(['items.product', 'table']);
    }

    /**
     * Ambil semua order dapur yang masih aktif (belum served / cancelled).
     */
    public function getActiveOrders(): Collection
    {
        return KitchenOrder::query()
            ->where('company_id', $this->resolveCompanyId())
            ->whereNotIn('status', [KitchenOrder::STATUS_SERVED, KitchenOrder::STATUS_CANCELLED])
            ->with(['items.product', 'table', 'posTransaction.serviceType'])
            ->orderBy('priority')
            ->orderBy('ordered_at')
            ->get();
    }

    /**
     * Update status order dapur (manual, mis. served / cancelled).
     */
    public function updateOrderStatus(int $orderId, string $status): void
    {
        $order = KitchenOrder::where('company_id', $this->resolveCompanyId())->findOrFail($orderId);

        $data = ['status' => $status];

        if ($status === KitchenOrder::STATUS_PREPARING && !$order->started_at) {
            $data['started_at'] = now();
        }
        if ($status === KitchenOrder::STATUS_READY && !$order->ready_at) {
            $data['ready_at'] = now();
        }
        if ($status === KitchenOrder::STATUS_SERVED && !$order->served_at) {
            $data['served_at'] = now();
        }

        $order->update($data);

        if ($status === KitchenOrder::STATUS_SERVED && $order->table_id) {
            ResTable::where('id', $order->table_id)->update(['status' => 'available']);
        }
    }

    /**
     * Update status item dapur, lalu sinkronkan status order dari item-itemnya.
     */
    public function updateItemStatus(int $itemId, string $status): void
    {
        $item = KitchenOrderItem::findOrFail($itemId);
        $item->update(['status' => $status]);

        $this->syncOrderStatusFromItems($item->kitchen_order_id);
    }

    protected function syncOrderStatusFromItems(int $orderId): void
    {
        $order = KitchenOrder::with('items')->find($orderId);

        if (!$order || $order->items->isEmpty()) {
            return;
        }

        $active = $order->items->reject(fn ($item) => $item->isCancelled());

        if ($active->isEmpty()) {
            return;
        }

        if ($active->contains(fn ($item) => $item->isPreparing())) {
            $status = KitchenOrder::STATUS_PREPARING;
        } elseif ($active->every(fn ($item) => $item->isServed())) {
            $status = KitchenOrder::STATUS_SERVED;
        } elseif ($active->every(fn ($item) => $item->isReady() || $item->isServed())) {
            $status = KitchenOrder::STATUS_READY;
        } else {
            $status = KitchenOrder::STATUS_PENDING;
        }

        $this->updateOrderStatus($orderId, $status);
    }

    /**
     * Data board untuk kitchen display, dikelompokkan per status.
     */
    public function getKdsDashboard(): array
    {
        $orders = $this->getActiveOrders();

        return [
            'pending' => $orders->where('status', KitchenOrder::STATUS_PENDING)->values(),
            'preparing' => $orders->where('status', KitchenOrder::STATUS_PREPARING)->values(),
            'ready' => $orders->where('status', KitchenOrder::STATUS_READY)->values(),
            'now' => now(),
        ];
    }
}
