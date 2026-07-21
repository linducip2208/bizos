<?php

namespace App\Services;

use App\Models\PosPayment;
use App\Models\PosTransaction;
use App\Models\PosTransactionItem;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Product;
use App\Models\StockBalance;
use App\Models\StockMovement;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PharmacyBridgeService
{
    /**
     * Dispense resep → auto-reduce stok farmasi + buat POS transaction.
     */
    public function dispensePrescription(Prescription $prescription): void
    {
        DB::transaction(function () use ($prescription) {
            $prescription->load('items.product', 'patient', 'doctor');

            if ($prescription->status === 'dispensed') {
                throw new \InvalidArgumentException('Resep sudah didispensing.');
            }

            if ($prescription->pos_transaction_id) {
                throw new \InvalidArgumentException('Resep sudah memiliki transaksi POS #' . $prescription->pos_transaction_id);
            }

            $items = $prescription->items;
            $subtotal = 0;

            $receiptNumber = 'RX-' . date('Ymd') . '-' . str_pad($prescription->id, 4, '0', STR_PAD_LEFT);

            $posTransaction = PosTransaction::create([
                'company_id' => $prescription->doctor->company_id ?? null,
                'receipt_number' => $receiptNumber,
                'transaction_date' => now(),
                'subtotal' => 0,
                'discount_total' => 0,
                'tax_total' => 0,
                'grand_total' => 0,
                'payment_status' => 'paid',
                'notes' => 'Penjualan farmasi dari resep #' . $prescription->id
                    . ' — Pasien: ' . $prescription->patient->full_name
                    . ', Dokter: ' . $prescription->doctor->first_name . ' ' . $prescription->doctor->last_name,
            ]);

            foreach ($items as $item) {
                $product = $item->product;
                if (!$product) continue;

                $price = (float) ($product->selling_price ?? 0);
                $qty = (float) $item->quantity;
                $itemSubtotal = round($qty * $price, 2);
                $subtotal += $itemSubtotal;

                PosTransactionItem::create([
                    'transaction_id' => $posTransaction->id,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'discount_amount' => 0,
                    'tax_amount' => 0,
                    'subtotal' => $itemSubtotal,
                ]);

                $product->decrement('stock', $qty);

                $balance = StockBalance::where('product_id', $product->id)
                    ->where('company_id', $prescription->doctor->company_id ?? null)
                    ->first();

                if ($balance) {
                    $balance->update([
                        'quantity' => max(0, (float) $balance->quantity - $qty),
                    ]);
                }

                StockMovement::create([
                    'company_id' => $prescription->doctor->company_id ?? null,
                    'product_id' => $product->id,
                    'movement_type' => 'out',
                    'reference_type' => 'prescription_dispense',
                    'reference_id' => $prescription->id,
                    'quantity_in' => 0,
                    'quantity_out' => $qty,
                    'unit_cost' => $product->purchase_price ?? 0,
                    'running_quantity' => $qty,
                    'running_cost' => round($qty * (float) ($product->purchase_price ?? 0), 2),
                    'notes' => 'Dispensing resep #' . $prescription->id . ' — ' . $product->name,
                    'created_by' => auth()->id(),
                    'movement_date' => now(),
                ]);

                $this->checkLowStockAlert($product);
            }

            $taxTotal = round($subtotal * 0.11, 2);
            $grandTotal = round($subtotal + $taxTotal, 2);

            $posTransaction->update([
                'subtotal' => $subtotal,
                'tax_total' => $taxTotal,
                'grand_total' => $grandTotal,
            ]);

            PosPayment::create([
                'transaction_id' => $posTransaction->id,
                'payment_method' => 'cash',
                'amount' => $grandTotal,
                'reference_number' => $receiptNumber,
                'paid_at' => now(),
            ]);

            $prescription->update([
                'status' => 'dispensed',
                'pos_transaction_id' => $posTransaction->id,
            ]);
        });
    }

    /**
     * Cek interaksi obat antar item resep.
     * Returns: [{severity, drugs, description}]
     */
    public function checkInteraction(Prescription $prescription): array
    {
        $prescription->load('items.product');

        $drugs = $prescription->items->map(function ($item) {
            $product = $item->product;
            return [
                'product_id' => $product->id ?? null,
                'drug_name' => $product->name ?? 'Unknown',
                'active_ingredient' => $product->active_ingredient ?? null,
                'drug_category' => $product->drug_category ?? null,
                'dosage' => $item->dosage,
            ];
        })->filter(fn($d) => !empty($d['active_ingredient']))->values()->toArray();

        $interactions = [];
        $count = count($drugs);

        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                $d1 = $drugs[$i];
                $d2 = $drugs[$j];

                $interaction = $this->getKnownInteraction($d1['active_ingredient'], $d2['active_ingredient']);
                if ($interaction) {
                    $interactions[] = [
                        'severity' => $interaction['severity'],
                        'drugs' => [$d1['drug_name'], $d2['drug_name']],
                        'ingredients' => [$d1['active_ingredient'], $d2['active_ingredient']],
                        'description' => $interaction['description'],
                    ];
                }
            }
        }

        return [
            'prescription_id' => $prescription->id,
            'drugs_count' => count($drugs),
            'interactions' => $interactions,
            'has_interaction' => count($interactions) > 0,
            'has_severe' => count(array_filter($interactions, fn($i) => $i['severity'] === 'severe')) > 0,
        ];
    }

    /**
     * Auto-reorder jika stok farmasi rendah.
     */
    public function autoReorder(int $productId): ?array
    {
        $product = Product::find($productId);
        if (!$product || !$product->is_medicine) return null;

        $currentStock = (float) $product->stock;
        $minStock = (float) $product->min_stock;

        if ($currentStock > $minStock) return null;

        $reorderQty = (float) $product->max_stock - $currentStock;
        if ($reorderQty <= 0) $reorderQty = $minStock * 3;

        return [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_code' => $product->code,
            'current_stock' => $currentStock,
            'min_stock' => $minStock,
            'max_stock' => (float) $product->max_stock,
            'suggested_reorder_qty' => round($reorderQty, 2),
            'estimated_cost' => round($reorderQty * (float) ($product->purchase_price ?? 0), 2),
            'urgent' => $currentStock <= 0,
        ];
    }

    /**
     * Batch cek semua produk farmasi untuk reorder.
     */
    public function batchAutoReorder(int $companyId): array
    {
        $products = Product::where('is_medicine', true)
            ->where('is_active', true)
            ->where('company_id', $companyId)
            ->get();

        $reorderList = [];

        foreach ($products as $product) {
            $result = $this->autoReorder($product->id);
            if ($result) {
                $reorderList[] = $result;
            }
        }

        usort($reorderList, function ($a, $b) {
            return ($b['urgent'] <=> $a['urgent']) ?: ($a['current_stock'] <=> $b['current_stock']);
        });

        return [
            'company_id' => $companyId,
            'total_medicine_products' => $products->count(),
            'needs_reorder' => count($reorderList),
            'urgent_count' => count(array_filter($reorderList, fn($r) => $r['urgent'])),
            'items' => $reorderList,
        ];
    }

    /**
     * Dapatkan riwayat penjualan farmasi untuk produk tertentu.
     */
    public function getPharmacySalesHistory(int $productId, string $period = 'monthly'): array
    {
        $dates = match ($period) {
            'daily' => [now()->startOfDay(), now()->endOfDay()],
            'weekly' => [now()->startOfWeek(), now()->endOfWeek()],
            'monthly' => [now()->startOfMonth(), now()->endOfMonth()],
            'yearly' => [now()->startOfYear(), now()->endOfYear()],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };

        [$from, $to] = $dates;

        $items = PosTransactionItem::where('product_id', $productId)
            ->whereHas('transaction', function ($q) use ($from, $to) {
                $q->whereBetween('transaction_date', [$from, $to])
                    ->where('notes', 'like', '%resep%');
            })
            ->with('transaction')
            ->get();

        $totalQty = round($items->sum('quantity'), 2);
        $totalRevenue = round($items->sum('subtotal'), 2);

        $dailyBreakdown = $items->groupBy(function ($item) {
            return $item->transaction->transaction_date?->format('Y-m-d') ?? 'unknown';
        })->map(function ($group) {
            return [
                'quantity' => round($group->sum('quantity'), 2),
                'revenue' => round($group->sum('subtotal'), 2),
                'transactions' => $group->unique('transaction_id')->count(),
            ];
        })->toArray();

        return [
            'product_id' => $productId,
            'period' => $period,
            'from' => $from->format('Y-m-d'),
            'to' => $to->format('Y-m-d'),
            'total_quantity' => $totalQty,
            'total_revenue' => $totalRevenue,
            'transaction_count' => $items->unique('transaction_id')->count(),
            'daily_breakdown' => $dailyBreakdown,
        ];
    }

    protected function checkLowStockAlert(Product $product): void
    {
        if ((float) $product->stock <= (float) $product->min_stock) {
            \Illuminate\Support\Facades\Log::warning('Stok obat menipis setelah dispensing resep', [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'current_stock' => $product->stock,
                'min_stock' => $product->min_stock,
                'action_required' => 'Segera lakukan pemesanan ulang.',
            ]);
        }
    }

    protected function getKnownInteraction(string $ingredient1, string $ingredient2): ?array
    {
        $ingredient1 = strtolower(trim($ingredient1));
        $ingredient2 = strtolower(trim($ingredient2));

        $knownInteractions = [
            'warfarin' => [
                'aspirin' => ['severity' => 'severe', 'description' => 'Risiko perdarahan meningkat signifikan. Pantau INR ketat.'],
                'ibuprofen' => ['severity' => 'moderate', 'description' => 'Peningkatan risiko perdarahan gastrointestinal.'],
            ],
            'simvastatin' => [
                'ketoconazole' => ['severity' => 'severe', 'description' => 'Risiko rhabdomyolysis meningkat. Hindari kombinasi.'],
                'erythromycin' => ['severity' => 'moderate', 'description' => 'Peningkatan kadar simvastatin dalam darah.'],
            ],
            'metformin' => [
                'furosemide' => ['severity' => 'moderate', 'description' => 'Risiko asidosis laktat meningkat. Monitor fungsi ginjal.'],
            ],
            'digoxin' => [
                'furosemide' => ['severity' => 'moderate', 'description' => 'Risiko toksisitas digoxin akibat hipokalemia. Monitor kalium.'],
                'amiodarone' => ['severity' => 'severe', 'description' => 'Peningkatan kadar digoxin signifikan. Kurangi dosis digoxin 50%.'],
            ],
        ];

        $check1 = $knownInteractions[$ingredient1][$ingredient2] ?? null;
        $check2 = $knownInteractions[$ingredient2][$ingredient1] ?? null;

        return $check1 ?? $check2;
    }
}
