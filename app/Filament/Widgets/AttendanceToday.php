<?php

namespace App\Filament\Widgets;

use App\Models\Attendance;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class AttendanceToday extends BaseWidget
{
    use DashboardWidgetFilter;

    protected static ?int $sort = 3;

    protected static function isVisibleToRole(?string $role): bool
    {
        return in_array($role, ['hr-manager', 'admin', 'super-admin']);
    }

    protected function getStats(): array
    {
        $today = now()->toDateString();

        $hadir = Attendance::whereDate('date', $today)
            ->whereIn('status', ['hadir', 'present'])
            ->count();
        $terlambat = Attendance::whereDate('date', $today)
            ->where('late_minutes', '>', 0)
            ->count();
        $tidakHadir = Attendance::whereDate('date', $today)
            ->where('status', 'tidak_hadir')
            ->count();
        $cuti = Attendance::whereDate('date', $today)
            ->where('status', 'cuti')
            ->count();

        return [
            Stat::make('Hadir', Number::format($hadir))
                ->description('Karyawan yang hadir hari ini')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Terlambat', Number::format($terlambat))
                ->description('Karyawan yang terlambat')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Tidak Hadir', Number::format($tidakHadir))
                ->description('Karyawan tidak hadir')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),

            Stat::make('Cuti', Number::format($cuti))
                ->description('Karyawan sedang cuti')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('gray'),
        ];
    }
}
