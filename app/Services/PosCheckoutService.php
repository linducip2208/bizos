<?php

namespace App\Services;

use App\Models\CashierShift;
use App\Models\Modifier;
use App\Models\PaymentMethod;
use App\Models\PosHoldOrder;
use App\Models\PosMember;
use App\Models\PriceListItem;
use App\Models\PosPayment;
use App\Models\PosPaymentLine;
use App\Models\PosTransaction;
use App\Models\PosTransactionItem;
use App\Models\PosVoucher;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SerialNumber;
use App\Models\ServiceType;
use App\Models\StockBalance;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PosCheckoutService
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

    public function resolveCashierId(): int
    {
        $employeeId = auth()->user()?->employee_id;

        if (!$employeeId) {
            throw new \RuntimeException('Akun kamu belum terhubung ke data karyawan. Hubungi admin untuk menautkan akun kasir.');
        }

        return (int) $employeeId;
    }

    public function resolveBranchId(): ?int
    {
        return auth()->user()?->employee?->branch_id;
    }

    /**
     * Cari shift kasir yang masih terbuka hari ini, atau buka baru otomatis.
     */
    public function openShift(): CashierShift
    {
        $cashierId = $this->resolveCashierId();
        $branchId = $this->resolveBranchId();

        $shift = CashierShift::where('employee_id', $cashierId)
            ->where('status', 'open')
            ->whereDate('shift_date', now()->toDateString())
            ->latest('id')
            ->first();

        if ($shift) {
            return $shift;
        }

        return CashierShift::create([
            'employee_id' => $cashierId,
            'branch_id' => $branchId,
            'shift_date' => now()->toDateString(),
            'opening_time' => now(),
            'opening_balance' => 0,
            'status' => 'open',
        ]);
    }

    /**
     * Generate nomor struk per perusahaan: POS-YYYYMMDD-XXXX
     */
    public function generateReceiptNumber(?int $companyId = null): string
    {
        $companyId = $companyId ?: $this->resolveCompanyId();
        $prefix = 'POS-' . date('Ymd');

        $last = PosTransaction::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('receipt_number', 'like', $prefix . '%')
            ->orderBy('receipt_number', 'desc')
            ->first();

        $lastNum = 0;
        if ($last) {
            $lastNum = (int) substr($last->receipt_number, -4);
        }

        return $prefix . '-' . str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Tentukan harga satuan produk (variant + modifier + harga grup + diskon grup + diskon tier member).
     */
    public function resolveUnitPrice(Product $product, ?ProductVariant $variant = null, ?PosMember $member = null, array $modifierIds = []): float
    {
        $price = (float) $product->selling_price;

        $group = $member?->customerGroup;

        if ($group?->price_list_id) {
            $groupPrice = PriceListItem::where('price_list_id', $group->price_list_id)
                ->where('product_id', $product->id)
                ->value('unit_price');

            if ($groupPrice !== null) {
                $price = (float) $groupPrice;
            }
        }

        if ($variant) {
            $price = $price + (float) $variant->price_adjustment;
        }

        $price = $price + $this->resolveModifierPrice($modifierIds);

        $groupDiscountPercent = (float) ($group?->discount_percent ?? 0);

        if ($groupDiscountPercent > 0) {
            $price = $price * (1 - $groupDiscountPercent / 100);
        }

        $memberDiscountPercent = $this->memberDiscountPercent($member);

        if ($memberDiscountPercent > 0) {
            $price = $price * (1 - $memberDiscountPercent / 100);
        }

        return round($price, 2);
    }

    /**
     * Ambil detail modifier terpilih (id, nama, harga) yang masih aktif.
     */
    public function resolveModifiers(array $modifierIds): array
    {
        if (empty($modifierIds)) {
            return [];
        }

        return Modifier::query()
            ->whereIn('id', array_values(array_map('intval', $modifierIds)))
            ->where('is_active', true)
            ->get(['id', 'name', 'price'])
            ->map(fn (Modifier $modifier) => [
                'id' => $modifier->id,
                'name' => $modifier->name,
                'price' => (float) $modifier->price,
            ])
            ->all();
    }

    /**
     * Total harga tambahan dari modifier terpilih.
     */
    public function resolveModifierPrice(array $modifierIds): float
    {
        return round(array_sum(array_column($this->resolveModifiers($modifierIds), 'price')), 2);
    }

    public function memberDiscountPercent(?PosMember $member): float
    {
        if (!$member) {
            return 0;
        }

        return match ($member->tier ?? 'regular') {
            'gold' => 3.0,
            'platinum' => 5.0,
            default => 0.0,
        };
    }

    /**
     * Hitung total keranjang. Format item cart:
     * ['product_id', 'variant_id', 'name', 'unit_price', 'quantity',
     *  'discount_amount', 'tax_amount', 'subtotal', 'is_taxable', 'tax_rate']
     */
    public function calculateTotals(array $cart, float $extraDiscount = 0): array
    {
        $subtotal = 0.0;
        $lineDiscount = 0.0;
        $taxTotal = 0.0;
        $itemCount = 0;

        foreach ($cart as $item) {
            $qty = (float) ($item['quantity'] ?? 0);
            $unitPrice = (float) ($item['unit_price'] ?? 0);
            $discount = (float) ($item['discount_amount'] ?? 0);

            $gross = $qty * $unitPrice;
            $subtotal += $gross;
            $lineDiscount += $discount;
            $itemCount += 1;

            $net = $gross - $discount;

            if (!empty($item['is_taxable'])) {
                $rate = (float) ($item['tax_rate'] ?? 0);
                if ($rate <= 0) {
                    $rate = 0.11;
                } elseif ($rate > 1) {
                    $rate = $rate / 100;
                }
                $taxTotal += round($net * $rate, 2);
            }
        }

        $discountTotal = $lineDiscount + max(0, $extraDiscount);
        $grandTotal = max(0, $subtotal - $discountTotal + $taxTotal);

        return [
            'subtotal' => round($subtotal, 2),
            'line_discount' => round($lineDiscount, 2),
            'discount_total' => round($discountTotal, 2),
            'tax_total' => round($taxTotal, 2),
            'grand_total' => round($grandTotal, 2),
            'item_count' => $itemCount,
        ];
    }

    /**
     * Validasi & hitung voucher. Return ['voucher' => PosVoucher|null, 'discount_amount' => float]
     */
    public function applyVoucher(string $code, float $subtotal, ?int $companyId = null): array
    {
        $companyId = $companyId ?: $this->resolveCompanyId();

        $voucher = PosVoucher::where('company_id', $companyId)
            ->where('code', $code)
            ->where('is_active', true)
            ->first();

        if (!$voucher) {
            throw new \InvalidArgumentException('Voucher tidak ditemukan atau tidak aktif.');
        }

        $now = now();
        if ($voucher->start_date && $now->lt($voucher->start_date)) {
            throw new \InvalidArgumentException('Voucher belum berlaku.');
        }
        if ($voucher->end_date && $now->gt($voucher->end_date)) {
            throw new \InvalidArgumentException('Voucher sudah kedaluwarsa.');
        }
        if ($voucher->usage_limit && ($voucher->used_count ?? 0) >= $voucher->usage_limit) {
            throw new \InvalidArgumentException('Kuota voucher sudah habis.');
        }
        if ($subtotal < (float) $voucher->min_purchase) {
            throw new \InvalidArgumentException('Minimal belanja Rp ' . number_format((float) $voucher->min_purchase, 0, ',', '.') . ' untuk voucher ini.');
        }

        $amount = match ($voucher->type) {
            'percent' => round($subtotal * ((float) $voucher->value / 100), 2),
            default => (float) $voucher->value,
        };

        if ((float) $voucher->max_discount > 0) {
            $amount = min($amount, (float) $voucher->max_discount);
        }

        return [
            'voucher' => $voucher,
            'discount_amount' => round(min($amount, $subtotal), 2),
        ];
    }

    /**
     * Hitung diskon otomatis (promosi/kupon/diskon produk) dari keranjang.
     * Diskon member tier sudah diterapkan di harga satuan sehingga tidak ikut
     * dihitung ulang di sini.
     */
    public function calculateAutoDiscounts(array $cart, ?int $memberId = null): array
    {
        if (empty($cart)) {
            return [
                'applied_discounts' => [],
                'total_discount' => 0.0,
                'final_total' => 0.0,
                'auto_applied' => false,
                'subtotal' => 0.0,
            ];
        }

        $subtotal = (float) $this->calculateTotals($cart)['subtotal'];

        $engine = app(DiscountEngineService::class);
        $result = $engine->calculateBestDiscount($cart, $subtotal, $memberId);

        $applied = array_values(array_filter(
            $result['applied_discounts'],
            fn ($discount) => ($discount['type'] ?? '') !== 'member',
        ));

        $totalDiscount = 0.0;
        foreach ($applied as $discount) {
            $totalDiscount += (float) $discount['amount'];
        }

        $totalDiscount = min(round($totalDiscount, 2), $subtotal);

        return [
            'applied_discounts' => $applied,
            'total_discount' => $totalDiscount,
            'final_total' => round(max(0, $subtotal - $totalDiscount), 2),
            'auto_applied' => $totalDiscount > 0,
            'subtotal' => $subtotal,
        ];
    }

    /**
     * Selesaikan transaksi POS: transaksi + items + payments + stok + loyalty + voucher.
     */
    public function checkout(array $cart, array $options = []): PosTransaction
    {
        if (empty($cart)) {
            throw new \InvalidArgumentException('Keranjang kosong.');
        }

        $companyId = $this->resolveCompanyId();
        $cashierId = $this->resolveCashierId();
        $branchId = $this->resolveBranchId();
        $shift = $this->openShift();

        $memberId = $options['member_id'] ?? null;
        $voucher = $options['voucher'] ?? null;
        $voucherDiscount = (float) ($options['voucher_discount'] ?? 0);
        $extraDiscount = (float) ($options['extra_discount'] ?? 0);
        $autoDiscount = (float) ($options['auto_discount'] ?? 0);
        $autoDiscounts = (array) ($options['auto_discounts'] ?? []);
        $notes = (string) ($options['notes'] ?? '');
        $payments = (array) ($options['payments'] ?? []);
        $receiptNumber = $options['receipt_number'] ?? $this->generateReceiptNumber($companyId);

        if (!empty($autoDiscounts)) {
            $summary = collect($autoDiscounts)
                ->map(fn ($d) => ($d['name'] ?? 'Diskon') . ' -Rp' . number_format((float) ($d['amount'] ?? 0), 0, ',', '.'))
                ->implode('; ');
            $notes = trim($notes . ($notes ? "\n" : '') . 'Diskon otomatis: ' . $summary);
        }

        $member = $memberId ? PosMember::find($memberId) : null;

        $serviceTypeId = $options['service_type_id'] ?? null;
        $serviceStaffId = $options['service_staff_id'] ?? null;
        $deliveryAddress = $options['delivery_address'] ?? null;
        $deliveryFee = (float) ($options['delivery_fee'] ?? 0);
        $tableId = $options['table_id'] ?? null;
        $serviceType = $serviceTypeId ? ServiceType::find($serviceTypeId) : null;

        $totals = $this->calculateTotals($cart, $voucherDiscount + $extraDiscount + $autoDiscount);

        $packCharge = $serviceType
            ? $serviceType->getPackCharge((float) $totals['subtotal'])
            : 0.0;

        $totals['pack_charge'] = round($packCharge, 2);
        $totals['delivery_fee'] = round($deliveryFee, 2);
        $totals['grand_total'] = round((float) $totals['grand_total'] + $packCharge + $deliveryFee, 2);

        return DB::transaction(function () use (
            $companyId, $branchId, $cashierId, $shift, $member, $voucher,
            $voucherDiscount, $extraDiscount, $notes, $payments, $receiptNumber,
            $totals, $cart, $serviceTypeId, $serviceStaffId, $deliveryAddress, $deliveryFee,
            $serviceType, $tableId
        ) {
            $transaction = PosTransaction::create([
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'shift_id' => $shift->id,
                'receipt_number' => $receiptNumber,
                'member_id' => $member?->id,
                'cashier_id' => $cashierId,
                'service_type_id' => $serviceTypeId,
                'service_staff_id' => $serviceStaffId,
                'transaction_date' => now(),
                'subtotal' => $totals['subtotal'],
                'discount_total' => $totals['discount_total'],
                'tax_total' => $totals['tax_total'],
                'delivery_fee' => $deliveryFee > 0 ? $deliveryFee : null,
                'grand_total' => $totals['grand_total'],
                'payment_status' => 'pending',
                'delivery_address' => $deliveryAddress ?: null,
                'notes' => $notes ?: null,
            ]);

            $taxRatePerItem = [];
            foreach ($cart as $key => $item) {
                $product = Product::find($item['product_id']);
                $variant = !empty($item['variant_id']) ? ProductVariant::find($item['variant_id']) : null;

                if (!$product) {
                    throw new \InvalidArgumentException('Produk tidak ditemukan: ' . ($item['name'] ?? $key));
                }

                $qty = (float) $item['quantity'];
                $unitPrice = (float) $item['unit_price'];
                $lineDiscount = (float) ($item['discount_amount'] ?? 0);
                $gross = $qty * $unitPrice;
                $net = $gross - $lineDiscount;

                $isTaxable = $product->is_taxable;
                $rate = (float) $product->tax_rate;
                $taxAmount = 0;

                if ($isTaxable) {
                    $rate = $rate > 0 ? $rate : 0.11;
                    if ($rate > 1) {
                        $rate = $rate / 100;
                    }
                    $taxAmount = round($net * $rate, 2);
                }
                $taxRatePerItem[$key] = $rate;

                PosTransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $product->id,
                    'variant_id' => $variant?->id,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'discount_amount' => $lineDiscount,
                    'tax_amount' => $taxAmount,
                    'subtotal' => $gross,
                ]);

                $this->decrementStock($companyId, $product, $variant, $qty, $transaction);

                $this->registerWarrantyIfNeeded($companyId, $product, $item, $transaction);
            }

            $this->createPayments($transaction, $payments, $companyId);

            if ($voucher) {
                $voucher->increment('used_count');
            }

            if ($member) {
                $member->increment('total_spent', $totals['grand_total']);
                try {
                    app(LoyaltyService::class)->earnPoints($transaction, $member);
                } catch (\Throwable $e) {
                    Log::warning('Gagal menambah poin loyalty', ['error' => $e->getMessage()]);
                }
            }

            $shift->increment('total_transactions');
            $shift->increment('total_sales', $totals['grand_total']);

            if ($serviceType && in_array($serviceType->type, ['dine_in', 'takeaway'], true)) {
                try {
                    app(KitchenDisplayService::class)->createOrderFromPos($transaction, $tableId);
                } catch (\Throwable $e) {
                    Log::warning('Gagal membuat kitchen order', ['error' => $e->getMessage()]);
                }
            }

            return $transaction->fresh(['items', 'payments.paymentLines']);
        });
    }

    protected function decrementStock(int $companyId, Product $product, ?ProductVariant $variant, float $qty, PosTransaction $transaction): void
    {
        $cost = (float) ($product->purchase_price ?? 0);
        $warehouseId = $this->resolveDefaultWarehouseId($companyId);
        $cashierId = $this->resolveCashierId();

        if ($variant) {
            $variant->decrement('stock', $qty);
        } else {
            $product->decrement('stock', $qty);
        }

        $balance = StockBalance::where('company_id', $companyId)
            ->where('product_id', $product->id)
            ->when($variant, fn ($q) => $q->where('product_variant_id', $variant->id))
            ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
            ->first();

        if ($balance) {
            $balance->update([
                'quantity' => max(0, (float) $balance->quantity - $qty),
            ]);
        }

        StockMovement::create([
            'company_id' => $companyId,
            'product_id' => $product->id,
            'product_variant_id' => $variant?->id,
            'warehouse_id' => $warehouseId,
            'movement_type' => 'out',
            'reference_type' => 'pos_transaction',
            'reference_id' => $transaction->id,
            'quantity_in' => 0,
            'quantity_out' => $qty,
            'unit_cost' => $cost,
            'running_quantity' => $qty,
            'running_cost' => round($qty * $cost, 2),
            'notes' => 'Penjualan POS #' . $transaction->receipt_number,
            'created_by' => $cashierId,
            'movement_date' => now(),
        ]);
    }

    protected function resolveDefaultWarehouseId(int $companyId): ?int
    {
        return Warehouse::where('company_id', $companyId)->value('id');
    }

    /**
     * Daftarkan garansi otomatis saat produk bergaransi terjual.
     */
    protected function registerWarrantyIfNeeded(int $companyId, Product $product, array $item, PosTransaction $transaction): void
    {
        if (!$product->warranty_id) {
            return;
        }

        $serialNumberId = null;
        $serialNumberCode = $item['serial_number'] ?? null;

        if ($serialNumberCode) {
            $serial = SerialNumber::where('company_id', $companyId)
                ->where('serial_number', $serialNumberCode)
                ->first();

            if ($serial) {
                $serial->update(['status' => 'sold']);
                $serialNumberId = $serial->id;
            }
        }

        try {
            app(WarrantyService::class)->registerWarranty([
                'company_id' => $companyId,
                'warranty_id' => $product->warranty_id,
                'product_id' => $product->id,
                'serial_number_id' => $serialNumberId,
                'pos_transaction_id' => $transaction->id,
                'notes' => 'Terdaftar otomatis dari penjualan POS #' . $transaction->receipt_number,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Gagal mendaftarkan garansi otomatis', [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Buat record pembayaran (support split payment + QRIS offline + e-wallet gateway).
     * $payments = [ ['method' => 'cash', 'amount' => 10000, 'reference' => null], ... ]
     */
    protected function createPayments(PosTransaction $transaction, array $payments, int $companyId): void
    {
        if (empty($payments)) {
            return;
        }

        $totalPaid = 0.0;

        foreach ($payments as $line) {
            $amount = (float) ($line['amount'] ?? 0);
            if ($amount <= 0) {
                continue;
            }

            $method = (string) ($line['method'] ?? 'cash');
            $reference = (string) ($line['reference'] ?? '');
            $gatewayCharge = null;
            $isGatewayMethod = $this->isGatewayMethod($method);

            if ($isGatewayMethod && !$reference) {
                $gatewayCharge = app(PaymentGatewayService::class)->createCharge($method, [
                    'amount' => $amount,
                    'customer' => $line['customer'] ?? [],
                    'description' => 'Pembayaran ' . $transaction->receipt_number,
                ]);

                if (!empty($gatewayCharge['reference'])) {
                    $reference = (string) $gatewayCharge['reference'];
                }
            }

            if ($method === 'qris' && !$reference) {
                $qris = app(QrisService::class)->generateStaticQris($amount);
                $reference = $qris['transaction_id'];
            }

            $isPaid = !$isGatewayMethod;

            $posPayment = PosPayment::create([
                'transaction_id' => $transaction->id,
                'payment_method' => $method,
                'amount' => $amount,
                'reference_number' => $reference ?: null,
                'payment_status' => $isPaid ? 'paid' : 'pending',
                'paid_at' => now(),
            ]);

            $paymentMethod = PaymentMethod::where('company_id', $companyId)
                ->where('code', $method)
                ->first();

            PosPaymentLine::create([
                'pos_payment_id' => $posPayment->id,
                'payment_method_id' => $paymentMethod?->id,
                'payment_method_name' => $paymentMethod?->name ?? $this->methodLabel($method),
                'amount' => $amount,
                'reference_number' => $reference ?: null,
                'approval_code' => $gatewayCharge['redirect_url'] ?? $gatewayCharge['qr_string'] ?? null,
            ]);

            if ($isPaid) {
                $totalPaid += $amount;
            }
        }

        $grandTotal = (float) $transaction->grand_total;

        $status = 'pending';
        if ($totalPaid >= $grandTotal - 0.001 && $grandTotal > 0) {
            $status = 'paid';
        } elseif ($totalPaid > 0) {
            $status = 'partial';
        }

        $transaction->update(['payment_status' => $status]);
    }

    protected function isGatewayMethod(string $method): bool
    {
        return in_array($method, ['gopay', 'ovo', 'dana', 'linkaja', 'shopeepay', 'xendit', 'stripe', 'midtrans'], true);
    }

    public function methodLabel(string $code): string
    {
        return match ($code) {
            'cash' => 'Tunai',
            'qris' => 'QRIS',
            'bank_transfer' => 'Transfer Bank',
            'e_wallet' => 'E-Wallet',
            'card' => 'Kartu Debit/Kredit',
            'gopay' => 'GoPay',
            'ovo' => 'OVO',
            'dana' => 'DANA',
            'linkaja' => 'LinkAja',
            'shopeepay' => 'ShopeePay',
            'xendit' => 'Xendit',
            'stripe' => 'Stripe',
            'midtrans' => 'Midtrans',
            default => ucwords(str_replace('_', ' ', $code)),
        };
    }

    /**
     * Simpan keranjang sebagai hold order.
     */
    public function holdOrder(array $cart, string $name, float $extraDiscount = 0): PosHoldOrder
    {
        if (empty($cart)) {
            throw new \InvalidArgumentException('Keranjang kosong.');
        }

        $companyId = $this->resolveCompanyId();
        $totals = $this->calculateTotals($cart, $extraDiscount);

        return PosHoldOrder::create([
            'company_id' => $companyId,
            'branch_id' => $this->resolveBranchId(),
            'cashier_id' => $this->resolveCashierId(),
            'name' => $name ?: ('Hold ' . now()->format('H:i')),
            'items' => $cart,
            'subtotal' => $totals['subtotal'],
            'discount_total' => $totals['discount_total'],
            'tax_total' => $totals['tax_total'],
            'grand_total' => $totals['grand_total'],
            'status' => 'open',
            'held_at' => now(),
        ]);
    }

    public function listHolds(?int $companyId = null): array
    {
        $companyId = $companyId ?: $this->resolveCompanyId();

        return PosHoldOrder::where('company_id', $companyId)
            ->where('status', 'open')
            ->latest('held_at')
            ->get()
            ->map(fn ($hold) => [
                'id' => $hold->id,
                'name' => $hold->name,
                'grand_total' => (float) $hold->grand_total,
                'held_at' => $hold->held_at?->format('d M Y H:i'),
                'item_count' => count($hold->items ?? []),
            ])
            ->toArray();
    }

    public function recallHold(int $holdId): array
    {
        $companyId = $this->resolveCompanyId();

        $hold = PosHoldOrder::where('company_id', $companyId)
            ->where('id', $holdId)
            ->where('status', 'open')
            ->first();

        if (!$hold) {
            throw new \InvalidArgumentException('Hold order tidak ditemukan.');
        }

        $hold->update(['status' => 'recalled']);

        return $hold->items ?? [];
    }
}
