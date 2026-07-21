<?php

namespace App\Filament\Widgets;

use App\Models\Deal;
use App\Models\Lead;
use App\Models\Quotation;
use App\Models\SalesOrder;
use App\Models\SalesInvoice;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class SalesStatsOverview extends BaseWidget
{
    use DashboardWidgetFilter;

    protected static ?int $sort = 6;

    protected static function isVisibleToRole(?string $role): bool
    {
        return in_array($role, ['super-admin', 'admin', 'manager', 'owner']);
    }

    protected function getStats(): array
    {
        $totalLeads = Lead::count();
        $activeDeals = Deal::where('status', 'terbuka')->count();
        $pendingQuotations = Quotation::where('status', 'draft')->count();
        $ordersThisMonth = SalesOrder::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $revenueThisMonth = SalesInvoice::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->whereIn('status', ['paid', 'partial'])
            ->sum('total');

        return [
            Stat::make('Total Leads', Number::format($totalLeads))
                ->description('Leads aktif')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('primary'),

            Stat::make('Deal Aktif', Number::format($activeDeals))
                ->description('Sedang berjalan')
                ->descriptionIcon('heroicon-m-hand-raised')
                ->color('warning'),

            Stat::make('Quotation Draft', Number::format($pendingQuotations))
                ->description('Belum dikirim')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('gray'),

            Stat::make('Order Bulan Ini', Number::format($ordersThisMonth))
                ->description('Sales order baru')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('success'),

            Stat::make('Revenue Bulan Ini', Number::currency($revenueThisMonth, 'IDR', 'id'))
                ->description('Invoice terbayar')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('info'),
        ];
    }
}
