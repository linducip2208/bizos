<?php

namespace App\Filament\Resources\Attendances\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AttendancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.first_name')
                    ->label('Karyawan')
                    ->state(fn ($record) => trim(($record->employee?->first_name ?? '') . ' ' . ($record->employee?->last_name ?? '')))
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(),
                TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('clock_in')
                    ->label('Jam Masuk')
                    ->dateTime('H:i')
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('clock_out')
                    ->label('Jam Keluar')
                    ->dateTime('H:i')
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'present' => 'Hadir',
                        'late' => 'Terlambat',
                        'absent' => 'Tidak Hadir',
                        'half_day' => 'Setengah Hari',
                        'wfh' => 'WFH',
                        'leave' => 'Cuti',
                        'holiday' => 'Libur',
                        'sick' => 'Sakit',
                        default => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'present' => 'success',
                        'late' => 'warning',
                        'absent' => 'danger',
                        'half_day' => 'info',
                        'wfh' => 'primary',
                        'leave' => 'gray',
                        'holiday' => 'gray',
                        'sick' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('late_minutes')
                    ->label('Terlambat')
                    ->suffix(' mnt')
                    ->sortable()
                    ->numeric(),
                TextColumn::make('work_type')
                    ->label('Tipe Kerja')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'wfo' => 'WFO',
                        'wfh' => 'WFH',
                        'field' => 'Lapangan',
                        default => $state,
                    }),
            ])
            ->defaultSort('date', 'desc')
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
