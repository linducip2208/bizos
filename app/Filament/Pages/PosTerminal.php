<?php

namespace App\Filament\Pages;

use App\Models\PaymentMethod;
use App\Models\PosMember;
use App\Models\PosTransaction;
use App\Models\PosVoucher;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ServiceType;
use App\Models\Employee;
use App\Services\PosCheckoutService;
use App\Services\ReceiptPrintService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Illuminate\Support\Collection;

class PosTerminal extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?int $navigationSort = 599;

    protected static ?string $title = 'Kasir POS';

    protected static ?string $slug = 'pos-terminal';

    protected string $view = 'filament.pages.pos-terminal';

    public string $search = '';

    public ?int $categoryId = null;

    public array $cart = [];

    public ?int $memberId = null;

    public string $memberSearch = '';

    public string $voucherCode = '';

    public float $voucherDiscount = 0;

    public ?array $appliedVoucher = null;

    public ?float $extraDiscount = null;

    public bool $autoApplyEnabled = true;

    public string $notes = '';

    public string $scannedCode = '';

    public ?int $serviceTypeId = null;

    public ?int $tableId = null;

    public ?int $serviceStaffId = null;

    public ?string $deliveryAddress = null;

    public ?float $deliveryFee = null;

    public bool $showPayment = false;

    public bool $showHold = false;

    public string $holdName = '';

    public bool $showReceipt = false;

    public ?int $variantProductId = null;

    public ?int $modifierProductId = null;

    public ?int $modifierVariantId = null;

    public array $selectedModifiers = [];

    public array $splitPayments = [];

    public string $quickMethod = 'cash';

    public ?float $tenderAmount = null;

    public ?array $receipt = null;

    public ?int $selectedPrinterId = null;

    public bool $showPrintDialog = false;

    public ?string $receiptPreviewHtml = null;

    public ?array $printResult = null;

    public ?string $error = null;

    public function mount(): void
    {
        $this->cart = [];
        $this->splitPayments = [];
    }

    public static function getNavigationGroup(): ?string
    {
        return 'POS & Retail';
    }

    public function getMaxContentWidth(): Width|string|null
    {
        return Width::Full;
    }

    protected function checkout(): PosCheckoutService
    {
        return app(PosCheckoutService::class);
    }

    // ─── Computed ───────────────────────────────────────────────────────────

    public function getProductsProperty(): Collection
    {
        return Product::query()
            ->where('company_id', $this->checkout()->resolveCompanyId())
            ->where('is_active', true)
            ->when($this->categoryId, fn ($q) => $q->where('category_id', $this->categoryId))
            ->when($this->search, fn ($q) => $q->where(function ($sub) {
                $sub->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('code', 'like', '%' . $this->search . '%');
            }))
            ->with([
                'variants' => fn ($q) => $q->where('is_active', true),
                'category',
                'modifierGroups' => fn ($q) => $q->where('is_active', true),
            ])
            ->orderBy('name')
            ->limit(120)
            ->get();
    }

    public function getCategoriesProperty(): Collection
    {
        return ProductCategory::query()
            ->where('company_id', $this->checkout()->resolveCompanyId())
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function getModifierGroupsProperty(): Collection
    {
        if (!$this->modifierProductId) {
            return collect();
        }

        return Product::query()
            ->with([
                'modifierGroups' => fn ($q) => $q
                    ->where('is_active', true)
                    ->with(['modifiers' => fn ($m) => $m->where('is_active', true)->orderBy('sort_order')])
                    ->orderBy('sort_order'),
            ])
            ->find($this->modifierProductId)?->modifierGroups ?? collect();
    }

    public function getAutoDiscountsProperty(): array
    {
        if (!$this->autoApplyEnabled || empty($this->cart)) {
            return [
                'applied_discounts' => [],
                'total_discount' => 0,
                'final_total' => 0,
                'auto_applied' => false,
                'subtotal' => 0,
            ];
        }

        return $this->checkout()->calculateAutoDiscounts($this->cart, $this->memberId);
    }

    public function getTotalsProperty(): array
    {
        $totals = $this->checkout()->calculateTotals(
            $this->cart,
            $this->voucherDiscount + (float) ($this->extraDiscount ?? 0) + (float) $this->autoDiscounts['total_discount']
        );

        $packCharge = $this->serviceType
            ? $this->serviceType->getPackCharge((float) $totals['subtotal'])
            : 0.0;

        $deliveryFee = (float) ($this->deliveryFee ?? 0);

        $totals['pack_charge'] = round($packCharge, 2);
        $totals['delivery_fee'] = round($deliveryFee, 2);
        $totals['grand_total'] = round((float) $totals['grand_total'] + $packCharge + $deliveryFee, 2);

        return $totals;
    }

    public function getServiceTypesProperty(): Collection
    {
        return ServiceType::query()
            ->where('company_id', $this->checkout()->resolveCompanyId())
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function getServiceTypeProperty(): ?ServiceType
    {
        return $this->serviceTypeId ? ServiceType::find($this->serviceTypeId) : null;
    }

    public function getTablesProperty(): Collection
    {
        return \App\Models\ResTable::query()
            ->where('company_id', $this->checkout()->resolveCompanyId())
            ->where('is_active', true)
            ->orderBy('table_number')
            ->orderBy('name')
            ->get();
    }

    public function getServiceStaffProperty(): Collection
    {
        return Employee::query()
            ->where('company_id', $this->checkout()->resolveCompanyId())
            ->orderBy('first_name')
            ->get();
    }

    public function getMemberProperty(): ?PosMember
    {
        return $this->memberId ? PosMember::find($this->memberId) : null;
    }

    public function getPaymentMethodsProperty(): Collection
    {
        $methods = PaymentMethod::where('company_id', $this->checkout()->resolveCompanyId())
            ->where('is_active', true)
            ->get();

        return $methods;
    }

    public function getHoldsProperty(): array
    {
        return $this->checkout()->listHolds();
    }

    public function getPrintersProperty(): Collection
    {
        $branchId = auth()->user()?->employee?->branch_id;

        return app(ReceiptPrintService::class)->getPrinters($branchId);
    }

    public function getChangeProperty(): float
    {
        $tender = (float) ($this->tenderAmount ?? 0);
        $grand = $this->totals['grand_total'];

        return max(0, $tender - $grand);
    }

    public function getMemberResultsProperty(): Collection
    {
        if (strlen($this->memberSearch) < 2) {
            return collect();
        }

        return PosMember::where('company_id', $this->checkout()->resolveCompanyId())
            ->where(function ($q) {
                $q->where('name', 'like', '%' . $this->memberSearch . '%')
                    ->orWhere('phone', 'like', '%' . $this->memberSearch . '%')
                    ->orWhere('member_code', 'like', '%' . $this->memberSearch . '%');
            })
            ->where('is_active', true)
            ->limit(8)
            ->get();
    }

    // ─── Cart actions ────────────────────────────────────────────────────────

    public function addToCart(int $productId, ?int $variantId = null, array $modifierIds = []): void
    {
        $product = Product::with(['variants', 'modifierGroups'])->find($productId);

        if (!$product) {
            return;
        }

        if ($product->variants->isNotEmpty() && !$variantId) {
            $this->variantProductId = $productId;
            return;
        }

        if (empty($modifierIds) && $product->modifierGroups->isNotEmpty()) {
            $this->modifierProductId = $productId;
            $this->modifierVariantId = $variantId;
            $this->selectedModifiers = [];
            return;
        }

        $this->pushItem($product, $variantId, $modifierIds);
    }

    public function addVariantToCart(int $variantId): void
    {
        $variant = \App\Models\ProductVariant::with(['product.variants', 'product.modifierGroups'])->find($variantId);

        if (!$variant) {
            return;
        }

        if ($variant->product->modifierGroups->isNotEmpty()) {
            $this->modifierProductId = $variant->product_id;
            $this->modifierVariantId = $variant->id;
            $this->selectedModifiers = [];
            return;
        }

        $this->pushItem($variant->product, $variant->id);
        $this->variantProductId = null;
    }

    public function addWithModifiers(): void
    {
        $product = Product::with('variants')->find($this->modifierProductId);
        $variantId = $this->modifierVariantId;
        $modifierIds = array_values(array_filter(array_map('intval', $this->selectedModifiers)));

        $this->modifierProductId = null;
        $this->modifierVariantId = null;
        $this->selectedModifiers = [];

        if (!$product) {
            return;
        }

        $this->pushItem($product, $variantId, $modifierIds);
        $this->variantProductId = null;
    }

    public function closeModifierModal(): void
    {
        $this->modifierProductId = null;
        $this->modifierVariantId = null;
        $this->selectedModifiers = [];
    }

    protected function pushItem(Product $product, ?int $variantId = null, array $modifierIds = []): void
    {
        $variant = $variantId ? $product->variants->firstWhere('id', $variantId) : null;
        $modifiers = $this->checkout()->resolveModifiers($modifierIds);
        $key = $variant ? "p{$product->id}v{$variant->id}" : "p{$product->id}";

        if (!empty($modifierIds)) {
            sort($modifierIds);
            $key .= 'm' . implode('-', $modifierIds);
        }

        $unitPrice = $this->checkout()->resolveUnitPrice($product, $variant, $this->member, $modifierIds);

        if (isset($this->cart[$key])) {
            $this->cart[$key]['quantity'] += 1;
        } else {
            $this->cart[$key] = [
                'product_id' => $product->id,
                'variant_id' => $variant?->id,
                'name' => $product->name . ($variant ? ' - ' . $variant->name : ''),
                'unit_price' => $unitPrice,
                'quantity' => 1,
                'discount_amount' => 0,
                'is_taxable' => (bool) $product->is_taxable,
                'tax_rate' => (float) $product->tax_rate,
                'stock' => $variant ? (float) $variant->stock : (float) $product->stock,
                'modifiers' => $modifiers,
            ];
        }

        $this->error = null;
    }

    public function incrementQty(string $key): void
    {
        if (isset($this->cart[$key])) {
            $this->cart[$key]['quantity'] += 1;
        }
    }

    public function decrementQty(string $key): void
    {
        if (isset($this->cart[$key])) {
            if ($this->cart[$key]['quantity'] <= 1) {
                unset($this->cart[$key]);
            } else {
                $this->cart[$key]['quantity'] -= 1;
            }
        }
    }

    public function removeItem(string $key): void
    {
        unset($this->cart[$key]);
    }

    public function clearCart(): void
    {
        $this->cart = [];
        $this->memberId = null;
        $this->voucherCode = '';
        $this->voucherDiscount = 0;
        $this->appliedVoucher = null;
        $this->extraDiscount = null;
        $this->notes = '';
        $this->splitPayments = [];
        $this->tenderAmount = null;
        $this->error = null;
        $this->serviceTypeId = null;
        $this->tableId = null;
        $this->serviceStaffId = null;
        $this->deliveryAddress = null;
        $this->deliveryFee = null;
    }

    // ─── Service type ─────────────────────────────────────────────────────────

    public function setServiceType(int $id): void
    {
        $serviceType = ServiceType::find($id);

        if (!$serviceType) {
            return;
        }

        $this->serviceTypeId = $serviceType->id;

        if (!$serviceType->isDelivery()) {
            $this->deliveryAddress = null;
            $this->deliveryFee = null;
        }

        if (!$serviceType->isDineIn()) {
            $this->serviceStaffId = null;
            $this->tableId = null;
        }
    }

    public function clearServiceType(): void
    {
        $this->serviceTypeId = null;
        $this->tableId = null;
        $this->serviceStaffId = null;
        $this->deliveryAddress = null;
        $this->deliveryFee = null;
    }

    public function updatedScannedCode(): void
    {
        if (!$this->scannedCode) {
            return;
        }

        $code = trim($this->scannedCode);

        $product = Product::where('company_id', $this->checkout()->resolveCompanyId())
            ->where('is_active', true)
            ->where(function ($q) use ($code) {
                $q->where('code', $code)
                    ->orWhereHas('barcodes', fn ($b) => $b->where('barcode', $code));
            })
            ->first();

        $this->scannedCode = '';

        if (!$product) {
            $this->error = "Produk dengan barcode/kode '{$code}' tidak ditemukan.";
            $this->notifyError('Produk tidak ditemukan', "Barcode '{$code}' tidak cocok dengan produk apapun.");
            return;
        }

        $this->addToCart($product->id);
    }

    // ─── Member / Voucher / Discount ─────────────────────────────────────────

    public function setMember(int $id): void
    {
        $member = PosMember::find($id);
        if ($member) {
            $this->memberId = $member->id;
            $this->memberSearch = '';
            $this->repriceCartForMember();
        }
    }

    public function clearMember(): void
    {
        $this->memberId = null;
        $this->repriceCartForMember();
    }

    protected function repriceCartForMember(): void
    {
        if (empty($this->cart)) {
            return;
        }

        foreach ($this->cart as $key => $item) {
            $product = Product::find($item['product_id']);
            if (!$product) {
                continue;
            }
            $variant = !empty($item['variant_id']) ? \App\Models\ProductVariant::find($item['variant_id']) : null;
            $modifierIds = array_column($item['modifiers'] ?? [], 'id');
            $this->cart[$key]['unit_price'] = $this->checkout()->resolveUnitPrice($product, $variant, $this->member, $modifierIds);
        }
    }

    public function applyVoucher(): void
    {
        $code = trim($this->voucherCode);
        if (!$code) {
            return;
        }

        try {
            $result = $this->checkout()->applyVoucher($code, $this->totals['subtotal']);
            $this->voucherDiscount = $result['discount_amount'];
            $this->appliedVoucher = [
                'id' => $result['voucher']->id,
                'code' => $result['voucher']->code,
                'name' => $result['voucher']->name,
                'discount_amount' => $result['discount_amount'],
            ];
            $this->error = null;

            Notification::make()
                ->title('Voucher diterapkan')
                ->body("Diskon Rp " . number_format($result['discount_amount'], 0, ',', '.') . " dari voucher {$result['voucher']->code}")
                ->success()
                ->send();
        } catch (\Throwable $e) {
            $this->voucherDiscount = 0;
            $this->appliedVoucher = null;
            $this->notifyError('Voucher gagal', $e->getMessage());
        }
    }

    public function removeVoucher(): void
    {
        $this->voucherCode = '';
        $this->voucherDiscount = 0;
        $this->appliedVoucher = null;
    }

    public function applyExtraDiscount(): void
    {
        $extra = (float) ($this->extraDiscount ?? 0);
        if ($extra < 0 || $extra > $this->totals['subtotal']) {
            $this->notifyError('Diskon tidak valid', 'Diskon harus antara 0 dan total belanja.');
            return;
        }
        $this->error = null;
    }

    public function toggleAutoApply(): void
    {
        $this->autoApplyEnabled = !$this->autoApplyEnabled;
    }

    // ─── Payment ─────────────────────────────────────────────────────────────

    public function openPayment(): void
    {
        if (empty($this->cart)) {
            $this->notifyError('Keranjang kosong', 'Tambahkan produk terlebih dahulu.');
            return;
        }

        $this->splitPayments = [];
        $this->tenderAmount = null;
        $this->showPayment = true;
    }

    public function closePayment(): void
    {
        $this->showPayment = false;
    }

    public function addPaymentLine(): void
    {
        $this->splitPayments[] = ['method' => 'cash', 'amount' => null];
    }

    public function removePaymentLine(int $index): void
    {
        unset($this->splitPayments[$index]);
        $this->splitPayments = array_values($this->splitPayments);
    }

    public function quickPay(string $method): void
    {
        $this->quickMethod = $method;
        $this->splitPayments = [['method' => $method, 'amount' => $this->totals['grand_total']]];
        $this->completeSale();
    }

    public function completeSale(): void
    {
        if (empty($this->cart)) {
            $this->notifyError('Keranjang kosong', 'Tambahkan produk terlebih dahulu.');
            return;
        }

        $payments = [];
        $total = 0.0;

        foreach ($this->splitPayments as $line) {
            $amount = (float) ($line['amount'] ?? 0);
            if ($amount <= 0) {
                continue;
            }
            $payments[] = [
                'method' => (string) ($line['method'] ?? 'cash'),
                'amount' => $amount,
                'reference' => (string) ($line['reference'] ?? ''),
            ];
            $total += $amount;
        }

        $grand = $this->totals['grand_total'];

        if ($total + 0.001 < $grand) {
            $this->notifyError('Pembayaran kurang', 'Total bayar Rp ' . number_format($total, 0, ',', '.') . ' kurang dari tagihan Rp ' . number_format($grand, 0, ',', '.') . '.');
            return;
        }

        try {
            $transaction = $this->checkout()->checkout($this->cart, [
                'member_id' => $this->memberId,
                'voucher' => $this->appliedVoucher ? PosVoucher::find($this->appliedVoucher['id']) : null,
                'voucher_discount' => $this->voucherDiscount,
                'extra_discount' => (float) ($this->extraDiscount ?? 0),
                'auto_discount' => (float) $this->autoDiscounts['total_discount'],
                'auto_discounts' => $this->autoDiscounts['applied_discounts'],
                'notes' => $this->notes,
                'payments' => $payments,
                'service_type_id' => $this->serviceTypeId,
                'service_staff_id' => $this->serviceStaffId,
                'table_id' => $this->tableId,
                'delivery_address' => $this->deliveryAddress,
                'delivery_fee' => (float) ($this->deliveryFee ?? 0),
            ]);

            $this->receipt = [
                'receipt_number' => $transaction->receipt_number,
                'transaction_id' => $transaction->id,
                'grand_total' => (float) $transaction->grand_total,
                'paid' => $total,
                'change' => max(0, $total - (float) $transaction->grand_total),
                'item_count' => $this->totals['item_count'],
                'member' => $this->member?->name,
                'service_type' => $transaction->serviceType?->name,
                'delivery_address' => $transaction->delivery_address,
                'delivery_fee' => (float) $transaction->delivery_fee,
                'payment_methods' => collect($payments)->pluck('method')->unique()->map(fn ($m) => $this->checkout()->methodLabel($m))->implode(', '),
            ];

            $this->showPayment = false;
            $this->showReceipt = true;
            $this->clearCart();
        } catch (\Throwable $e) {
            $this->notifyError('Transaksi gagal', $e->getMessage());
        }
    }

    public function closeReceipt(): void
    {
        $this->showReceipt = false;
        $this->receipt = null;
        $this->selectedPrinterId = null;
        $this->showPrintDialog = false;
        $this->receiptPreviewHtml = null;
        $this->printResult = null;
    }

    // ─── Print ───────────────────────────────────────────────────────────────

    public function openPrintDialog(): void
    {
        $transaction = $this->resolveReceiptTransaction();

        if (!$transaction) {
            $this->notifyError('Transaksi tidak ditemukan', 'Tidak ada transaksi untuk dicetak.');
            return;
        }

        $this->receiptPreviewHtml = app(ReceiptPrintService::class)->generateReceiptHtml($transaction);
        $this->printResult = null;
        $this->showPrintDialog = true;
    }

    public function closePrintDialog(): void
    {
        $this->showPrintDialog = false;
        $this->receiptPreviewHtml = null;
        $this->printResult = null;
    }

    public function printReceipt(): void
    {
        $transaction = $this->resolveReceiptTransaction();

        if (!$transaction) {
            $this->notifyError('Transaksi tidak ditemukan', 'Tidak ada transaksi untuk dicetak.');
            return;
        }

        $printerId = $this->selectedPrinterId ?? $this->printers->first()?->id;

        if (!$printerId) {
            $this->notifyError('Printer belum dipilih', 'Tambahkan printer terlebih dahulu di menu Printer.');
            return;
        }

        try {
            $this->printResult = app(ReceiptPrintService::class)->printReceipt($transaction, (int) $printerId);

            $success = (bool) ($this->printResult['success'] ?? false);
            $notification = Notification::make()
                ->title($success ? 'Struk dikirim' : 'Gagal mencetak')
                ->body($this->printResult['message'] ?? '');

            if ($success) {
                $notification->success();
            } else {
                $notification->danger();
            }

            $notification->send();
        } catch (\Throwable $e) {
            $this->notifyError('Gagal mencetak', $e->getMessage());
        }
    }

    protected function resolveReceiptTransaction(): ?PosTransaction
    {
        $transactionId = $this->receipt['transaction_id'] ?? null;

        if (!$transactionId) {
            return null;
        }

        return PosTransaction::find($transactionId);
    }

    // ─── Hold ────────────────────────────────────────────────────────────────

    public function openHoldModal(): void
    {
        if (empty($this->cart)) {
            $this->notifyError('Keranjang kosong', 'Tidak ada transaksi yang bisa di-hold.');
            return;
        }
        $this->holdName = '';
        $this->showHold = true;
    }

    public function saveHold(): void
    {
        try {
            $this->checkout()->holdOrder($this->cart, $this->holdName, $this->voucherDiscount + (float) ($this->extraDiscount ?? 0));
            $this->showHold = false;
            $this->holdName = '';
            $this->clearCart();

            Notification::make()
                ->title('Transaksi di-hold')
                ->body('Keranjang disimpan dan bisa di-recall kapan saja.')
                ->success()
                ->send();
        } catch (\Throwable $e) {
            $this->notifyError('Gagal hold', $e->getMessage());
        }
    }

    public function recallHold(int $id): void
    {
        try {
            $items = $this->checkout()->recallHold($id);
            $this->cart = $items;
            $this->error = null;
            $this->showHold = false;

            Notification::make()
                ->title('Hold di-recall')
                ->body('Keranjang berhasil dimuat ulang.')
                ->success()
                ->send();
        } catch (\Throwable $e) {
            $this->notifyError('Gagal recall', $e->getMessage());
        }
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    protected function notifyError(string $title, string $body): void
    {
        $this->error = $body;

        Notification::make()
            ->title($title)
            ->body($body)
            ->danger()
            ->send();
    }
}
