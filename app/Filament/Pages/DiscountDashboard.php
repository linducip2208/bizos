<?php

namespace App\Filament\Pages;

use App\Models\Coupon;
use App\Models\PosTransaction;
use App\Models\Promotion;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class DiscountDashboard extends Page
{
    protected static bool $shouldRegisterNavigation = false;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    protected static ?int $navigationSort = 145;

    protected static ?string $title = 'Diskon & Promosi';

    protected string $view = 'filament.pages.discount-dashboard';

    public static function getNavigationGroup(): ?string
    {
        return 'Dashboard';
    }

    public array $activePromotions = [];
    public array $expiringSoon = [];
    public array $couponStats = [];
    public array $couponList = [];
    public array $metrics = [];
    public array $recentActivity = [];

    public string $quickName = '';
    public string $quickType = 'discount_percent';
    public ?float $quickValue = null;
    public ?string $quickStart = null;
    public ?string $quickEnd = null;
    public bool $quickAutoApply = true;
    public bool $quickStacking = false;

    public function mount(): void
    {
        $this->loadActivePromotions();
        $this->loadExpiringSoon();
        $this->loadCouponStats();
        $this->loadMetrics();
        $this->loadRecentActivity();
    }

    public function togglePromotionAutoApply(int $id): void
    {
        $promotion = Promotion::find($id);

        if ($promotion) {
            $promotion->update(['auto_apply' => !$promotion->auto_apply]);
            $this->loadActivePromotions();

            Notification::make()
                ->title($promotion->auto_apply ? 'Auto apply diaktifkan' : 'Auto apply dinonaktifkan')
                ->body($promotion->name)
                ->success()
                ->send();
        }
    }

    public function toggleCouponAutoApply(int $id): void
    {
        $coupon = Coupon::find($id);

        if ($coupon) {
            $coupon->update(['auto_apply' => !$coupon->auto_apply]);
            $this->loadCouponStats();

            Notification::make()
                ->title($coupon->auto_apply ? 'Auto apply diaktifkan' : 'Auto apply dinonaktifkan')
                ->body('Kupon ' . $coupon->code)
                ->success()
                ->send();
        }
    }

    public function createQuickPromotion(): void
    {
        $this->validate([
            'quickName' => 'required|string|max:255',
            'quickValue' => 'required|numeric|min:0',
            'quickStart' => 'required|date',
            'quickEnd' => 'required|date|after:quickStart',
        ]);

        $discountType = $this->quickType === 'discount_percent' ? 'percentage' : 'fixed';

        Promotion::create([
            'name' => $this->quickName,
            'type' => $this->quickType,
            'discount_type' => $discountType,
            'discount_value' => $this->quickValue,
            'min_purchase' => 0,
            'applies_to' => 'all',
            'auto_apply' => $this->quickAutoApply,
            'stacking_allowed' => $this->quickStacking,
            'start_date' => $this->quickStart,
            'end_date' => $this->quickEnd,
            'is_active' => true,
        ]);

        $this->quickName = '';
        $this->quickValue = null;
        $this->quickStart = null;
        $this->quickEnd = null;
        $this->quickAutoApply = true;
        $this->quickStacking = false;

        Notification::make()
            ->title('Promosi dibuat')
            ->body('Promosi baru berhasil dibuat dan siap digunakan.')
            ->success()
            ->send();

        $this->loadActivePromotions();
        $this->loadExpiringSoon();
    }

    protected function loadActivePromotions(): void
    {
        $now = now();

        $this->activePromotions = Promotion::query()
            ->withCount('coupons')
            ->withSum('coupons', 'used_count')
            ->orderByDesc('is_active')
            ->orderBy('end_date')
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'type' => $p->type,
                'type_label' => $this->promotionTypeLabel($p->type),
                'discount_label' => $this->discountLabel($p),
                'start_date' => $p->start_date?->format('d M Y'),
                'end_date' => $p->end_date?->format('d M Y'),
                'is_active' => $p->is_active,
                'auto_apply' => $p->auto_apply,
                'stacking_allowed' => $p->stacking_allowed,
                'coupons_count' => $p->coupons_count ?? 0,
                'used_count' => (int) ($p->coupons_sum_used_count ?? 0),
                'status' => $this->promotionStatus($p, $now),
            ])->toArray();
    }

    protected function loadExpiringSoon(): void
    {
        $this->expiringSoon = Promotion::query()
            ->where('is_active', true)
            ->whereNotNull('end_date')
            ->whereBetween('end_date', [now()->startOfDay(), now()->addDays(14)->endOfDay()])
            ->orderBy('end_date')
            ->limit(10)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'end_date' => $p->end_date?->format('d M Y'),
                'days_left' => now()->startOfDay()->diffInDays($p->end_date),
                'auto_apply' => $p->auto_apply,
            ])->toArray();
    }

    protected function loadCouponStats(): void
    {
        $total = Coupon::count();
        $active = Coupon::where('is_active', true)->count();
        $autoApply = Coupon::where('is_active', true)->where('auto_apply', true)->count();
        $totalUses = (int) Coupon::sum('used_count');
        $maxUses = (int) Coupon::sum('max_uses');

        $this->couponStats = [
            'total' => $total,
            'active' => $active,
            'auto_apply' => $autoApply,
            'total_uses' => $totalUses,
            'usage_rate' => $maxUses > 0 ? round($totalUses / $maxUses * 100, 1) : 0,
        ];

        $this->couponList = Coupon::query()
            ->where('is_active', true)
            ->orderByDesc('used_count')
            ->limit(10)
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'code' => $c->code,
                'discount' => (float) $c->discount,
                'discount_type' => $c->discount_type,
                'auto_apply' => $c->auto_apply,
                'used_count' => $c->used_count,
                'max_uses' => $c->max_uses,
            ])->toArray();
    }

    protected function loadMetrics(): void
    {
        $paid = PosTransaction::where('payment_status', 'paid');

        $totalDiscount = (float) (clone $paid)->sum('discount_total');
        $totalSubtotal = (float) (clone $paid)->sum('subtotal');
        $totalGrand = (float) (clone $paid)->sum('grand_total');
        $txCount = (clone $paid)->count();
        $redeemed = (clone $paid)->where('discount_total', '>', 0)->count();

        $this->metrics = [
            'total_discount' => $totalDiscount,
            'redemptions' => $redeemed,
            'avg_with' => $txCount > 0 ? round($totalGrand / $txCount, 2) : 0,
            'avg_without' => $txCount > 0 ? round($totalSubtotal / $txCount, 2) : 0,
        ];
    }

    protected function loadRecentActivity(): void
    {
        $this->recentActivity = PosTransaction::where('payment_status', 'paid')
            ->where('discount_total', '>', 0)
            ->latest('transaction_date')
            ->limit(10)
            ->get()
            ->map(fn ($t) => [
                'receipt_number' => $t->receipt_number,
                'date' => $t->transaction_date?->format('d M Y H:i'),
                'subtotal' => (float) $t->subtotal,
                'discount' => (float) $t->discount_total,
                'grand_total' => (float) $t->grand_total,
            ])->toArray();
    }

    protected function promotionStatus(Promotion $p, $now): string
    {
        if (!$p->is_active) {
            return 'inactive';
        }

        if ($p->start_date && $now->lt($p->start_date)) {
            return 'scheduled';
        }

        if ($p->end_date && $now->gt($p->end_date)) {
            return 'expired';
        }

        return 'active';
    }

    protected function promotionTypeLabel(string $type): string
    {
        return match ($type) {
            'discount_percent' => 'Diskon %',
            'discount_amount' => 'Diskon Rp',
            'buy_x_get_y' => 'Beli X Gratis Y',
            'bundle' => 'Bundle',
            'free_shipping' => 'Gratis Ongkir',
            default => $type,
        };
    }

    protected function discountLabel(Promotion $p): string
    {
        if ($p->type === 'buy_x_get_y') {
            $c = $p->config ?? [];

            return 'Beli ' . ($c['buy_qty'] ?? 0) . ' Gratis ' . ($c['get_qty'] ?? 0);
        }

        if ($p->type === 'bundle') {
            return 'Bundle ' . count((array) ($p->config['product_ids'] ?? [])) . ' produk';
        }

        if ($p->discount_type === 'percentage' || $p->type === 'discount_percent') {
            return (float) ($p->discount_value ?? 0) . '%';
        }

        return 'Rp ' . number_format((float) ($p->discount_value ?? 0), 0, ',', '.');
    }
}
