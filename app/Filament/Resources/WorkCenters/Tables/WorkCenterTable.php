<?php

namespace App\Filament\Resources\WorkCenters\Tables;

use App\Models\WorkCenter;
use App\Services\ManufacturingService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;

class WorkCenterTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'machine' => 'warning',
                        'manual' => 'success',
                        'assembly' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn($state) => match ($state) {
                        'machine' => 'Mesin',
                        'manual' => 'Manual',
                        'assembly' => 'Assembly',
                        default => $state,
                    })
                    ->sortable(),
                TextColumn::make('capacity_per_day')
                    ->label('Kapasitas/Hari')
                    ->sortable(),
                TextColumn::make('hourly_cost')
                    ->label('Biaya/Jam')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('overhead_rate_percent')
                    ->label('Overhead (%)')
                    ->suffix('%'),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('view_oee')
                    ->label('Lihat OEE')
                    ->icon(Heroicon::OutlinedChartBar)
                    ->color('warning')
                    ->modalHeading('Overall Equipment Effectiveness')
                    ->modalContent(function (WorkCenter $record) {
                        $svc = app(ManufacturingService::class);
                        $oee = $svc->calculateOee($record, 'weekly');
                        $c = fn($label, $value, $color) => "<div class='text-center p-3'><div class='text-3xl font-bold' style='color:{$color}'>{$value}%</div><div class='text-xs text-gray-500 mt-1'>{$label}</div></div>";
                        return view('filament.hooks.body-end')->with('content', "<div class='p-4'><h3 class='font-bold mb-4'>OEE: {$record->name}</h3><div class='grid grid-cols-4 gap-2'>{$c('Availability', $oee['availability_percent'], '#22c55e')}{$c('Performance', $oee['performance_percent'], '#3b82f6')}{$c('Quality', $oee['quality_percent'], '#f59e0b')}{$c('OEE', $oee['oee_percent'], '#ef4444')}</div></div>")->render();
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),
                Action::make('view_capacity')
                    ->label('Kapasitas')
                    ->icon(Heroicon::OutlinedArrowTrendingUp)
                    ->color('info')
                    ->modalHeading('Utilisasi Kapasitas')
                    ->modalContent(function (WorkCenter $record) {
                        $svc = app(ManufacturingService::class);
                        $util = $svc->getCapacityUtilization($record, 'weekly');
                        return view('filament.hooks.body-end')->with('content', "<div class='p-4'><h3 class='font-bold mb-4'>{$record->name}</h3><div class='space-y-2'><div class='flex justify-between'><span>Kapasitas</span><span class='font-bold'>{$util['capacity_hours']} jam</span></div><div class='flex justify-between'><span>Planned</span><span class='font-bold'>{$util['planned_hours']} jam</span></div><div class='flex justify-between'><span>Aktual</span><span class='font-bold'>{$util['actual_hours']} jam</span></div><div class='flex justify-between text-lg'><span>Utilisasi</span><span class='font-bold " . ($util['utilization_percent'] > 80 ? "text-red-500" : "text-green-500") . "'>{$util['utilization_percent']}%</span></div></div></div>")->render();
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}