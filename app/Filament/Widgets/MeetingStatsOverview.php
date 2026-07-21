<?php

namespace App\Filament\Widgets;

use App\Models\Meeting;
use App\Models\BpmnProcessInstance;
use App\Models\BpmnTaskInstance;
use App\Models\BlockchainBlock;
use App\Models\BlockchainTransaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class MeetingStatsOverview extends BaseWidget
{
    use DashboardWidgetFilter;

    protected static ?int $sort = 20;

    protected function getStats(): array
    {
        $today = Meeting::whereDate('start_time', today())->count();
        $thisWeek = Meeting::whereBetween('start_time', [now()->startOfWeek(), now()->endOfWeek()])->count();
        $completed = Meeting::where('status', 'completed')->count();
        $withRecording = Meeting::whereNotNull('recording_path')->count();

        return [
            Stat::make('Rapat Hari Ini', Number::format($today))
                ->description('Total rapat hari ini')
                ->descriptionIcon('heroicon-m-video-camera')
                ->color('primary'),

            Stat::make('Rapat Minggu Ini', Number::format($thisWeek))
                ->description('Total rapat minggu ini')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),

            Stat::make('Rapat Selesai', Number::format($completed))
                ->description('Total rapat completed')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Dengan Rekaman', Number::format($withRecording))
                ->description('Rapat dengan rekaman')
                ->descriptionIcon('heroicon-m-film')
                ->color('warning'),
        ];
    }
}
