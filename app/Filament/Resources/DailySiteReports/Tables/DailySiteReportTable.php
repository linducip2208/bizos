<?php

namespace App\Filament\Resources\DailySiteReports\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DailySiteReportTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('project.name')
                    ->label('Proyek')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('report_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                BadgeColumn::make('weather')
                    ->label('Cuaca')
                    ->colors([
                        'warning' => 'cerah',
                        'gray' => 'mendung',
                        'info' => 'hujan',
                        'danger' => 'badai',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'cerah' => 'Cerah',
                        'mendung' => 'Mendung',
                        'hujan' => 'Hujan',
                        'badai' => 'Badai',
                        default => $state,
                    }),
                TextColumn::make('worker_count')
                    ->label('Pekerja')
                    ->sortable(),
                TextColumn::make('work_description')
                    ->label('Pekerjaan')
                    ->limit(50)
                    ->wrap(),
                TextColumn::make('issues')
                    ->label('Kendala')
                    ->limit(50)
                    ->wrap()
                    ->placeholder('-'),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('report_date', 'desc')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
