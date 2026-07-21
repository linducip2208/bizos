<?php

namespace App\Filament\Widgets;

use App\Models\BpmnProcess;
use App\Models\BpmnProcessInstance;
use App\Models\BpmnTaskInstance;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class BpmnStatsOverview extends BaseWidget
{
    use DashboardWidgetFilter;

    protected static ?int $sort = 21;

    protected function getStats(): array
    {
        $activeProcesses = BpmnProcess::where('is_active', true)->count();
        $runningInstances = BpmnProcessInstance::where('status', 'running')->count();
        $pendingTasks = BpmnTaskInstance::where('status', 'pending')->count();
        $overdueTasks = BpmnTaskInstance::where('status', 'pending')
            ->whereNotNull('sla_deadline')
            ->where('sla_deadline', '<', now())
            ->count();

        return [
            Stat::make('Proses Aktif', Number::format($activeProcesses))
                ->description('BPMN processes aktif')
                ->descriptionIcon('heroicon-m-arrow-path-rounded-square')
                ->color('primary'),

            Stat::make('Instance Berjalan', Number::format($runningInstances))
                ->description('BPMN instances running')
                ->descriptionIcon('heroicon-m-play-circle')
                ->color('info'),

            Stat::make('Task Pending', Number::format($pendingTasks))
                ->description('Task menunggu eksekusi')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('SLA Overdue', Number::format($overdueTasks))
                ->description($overdueTasks > 0 ? 'Task melewati SLA!' : 'Semua task on-track')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($overdueTasks > 0 ? 'danger' : 'success'),
        ];
    }
}
