<?php

namespace App\Filament\Widgets;

use App\Models\PosTransaction;
use Filament\Widgets\ChartWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class RevenueChart extends BaseWidget
{
    use DashboardWidgetFilter;

    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 'full';

    protected ?string $heading = 'Revenue 6 Bulan Terakhir';

    protected static function isVisibleToRole(?string $role): bool
    {
        return in_array($role, ['owner', 'finance', 'admin', 'super-admin']);
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $data = PosTransaction::select(
                DB::raw('YEAR(transaction_date) as year'),
                DB::raw('MONTH(transaction_date) as month'),
                DB::raw('SUM(grand_total) as total')
            )
            ->where('transaction_date', '>=', now()->subMonths(5)->startOfMonth())
            ->where('transaction_date', '<=', now()->endOfMonth())
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $months = collect();
        $revenues = collect();

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthLabel = $date->translatedFormat('M Y');
            $year = $date->year;
            $month = $date->month;

            $found = $data->first(fn ($item) => $item->year == $year && $item->month == $month);

            $months->push($monthLabel);
            $revenues->push((int) ($found->total ?? 0));
        }

        return [
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => $revenues->toArray(),
                    'backgroundColor' => '#6366f1',
                    'borderColor' => '#4f46e5',
                    'borderWidth' => 0,
                    'borderRadius' => 4,
                ],
            ],
            'labels' => $months->toArray(),
        ];
    }
}
