<?php

namespace App\Filament\Widgets;

use App\Models\BlockchainBlock;
use App\Models\BlockchainTransaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class BlockchainStatsOverview extends BaseWidget
{
    use DashboardWidgetFilter;

    protected static ?int $sort = 22;

    protected function getStats(): array
    {
        $totalBlocks = BlockchainBlock::count();
        $totalTransactions = BlockchainTransaction::count();
        $latestBlock = BlockchainBlock::max('block_number') ?? 0;
        $chainValid = app(\App\Services\BlockchainService::class)->validateChain();

        return [
            Stat::make('Total Blocks', Number::format($totalBlocks))
                ->description('Blockchain ledger blocks')
                ->descriptionIcon('heroicon-m-cube')
                ->color('primary'),

            Stat::make('Total Transaksi', Number::format($totalTransactions))
                ->description('Blockchain transactions')
                ->descriptionIcon('heroicon-m-link')
                ->color('info'),

            Stat::make('Latest Block', '#' . Number::format($latestBlock))
                ->description('Block terakhir')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Chain Status', $chainValid ? 'Valid' : 'BROKEN')
                ->description($chainValid ? 'Integritas chain OK' : 'Integritas chain terganggu!')
                ->descriptionIcon($chainValid ? 'heroicon-m-shield-check' : 'heroicon-m-shield-exclamation')
                ->color($chainValid ? 'success' : 'danger'),
        ];
    }
}
