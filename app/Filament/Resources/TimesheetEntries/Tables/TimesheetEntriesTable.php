<?php

namespace App\Filament\Resources\TimesheetEntries\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TimesheetEntriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('timesheet.employee.first_name')
                    ->label('Karyawan')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('task.title')
                    ->label('Tugas')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('task.project.name')
                    ->label('Project')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('start_time')
                    ->label('Mulai')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('end_time')
                    ->label('Selesai')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('hours')
                    ->label('Jam')
                    ->suffix(' jam')
                    ->sortable()
                    ->placeholder('-'),
                IconColumn::make('is_billable')
                    ->label('Ditagihkan')
                    ->boolean(),
                IconColumn::make('is_billed')
                    ->label('Sudah Invoice')
                    ->boolean(),
                TextColumn::make('invoice.invoice_number')
                    ->label('No. Invoice')
                    ->searchable()
                    ->placeholder('-'),
            ])
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