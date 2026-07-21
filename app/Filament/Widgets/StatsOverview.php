<?php

namespace App\Filament\Widgets;

use App\Models\Company;
use App\Models\Employee;
use App\Models\PosTransaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class StatsOverview extends BaseWidget
{
    use DashboardWidgetFilter;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalEmployees = Employee::count();
        $totalCompanies = Company::count();
        $transactionsThisMonth = PosTransaction::whereYear('transaction_date', now()->year)
            ->whereMonth('transaction_date', now()->month)
            ->count();
        $revenueThisMonth = PosTransaction::whereYear('transaction_date', now()->year)
            ->whereMonth('transaction_date', now()->month)
            ->sum('grand_total');

        return [
            Stat::make('Total Karyawan', Number::format($totalEmployees))
                ->description('Total seluruh karyawan')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),

            Stat::make('Total Perusahaan', Number::format($totalCompanies))
                ->description('Perusahaan terdaftar')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('success'),

            Stat::make('Total Transaksi Bulan Ini', Number::format($transactionsThisMonth))
                ->description('Transaksi POS bulan ' . now()->translatedFormat('F Y'))
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('warning'),

            Stat::make('Revenue Bulan Ini', Number::currency($revenueThisMonth, 'IDR', 'id'))
                ->description('Total pendapatan bulan ' . now()->translatedFormat('F Y'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('info'),
        ];
    }
}
