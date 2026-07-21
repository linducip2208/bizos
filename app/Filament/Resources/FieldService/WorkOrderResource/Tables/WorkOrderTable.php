<?php

namespace App\Filament\Resources\FieldService\WorkOrderResource\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WorkOrderTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('wo_number')
                    ->label('No WO')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('client.name')
                    ->label('Klien')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('service_type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'preventive' => 'Preventif',
                        'corrective' => 'Korektif',
                        'emergency' => 'Darurat',
                        'installation' => 'Instalasi',
                        'inspection' => 'Inspeksi',
                        default => $state,
                    }),
                TextColumn::make('priority')
                    ->label('Prioritas')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'low' => 'gray',
                        'medium' => 'info',
                        'high' => 'warning',
                        'critical' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'low' => 'Rendah',
                        'medium' => 'Sedang',
                        'high' => 'Tinggi',
                        'critical' => 'Kritis',
                        default => $state,
                    }),
                TextColumn::make('technician.first_name')
                    ->label('Teknisi')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'open' => 'gray',
                        'assigned' => 'info',
                        'en_route' => 'warning',
                        'in_progress' => 'primary',
                        'completed' => 'success',
                        'verified' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'open' => 'Open',
                        'assigned' => 'Ditugaskan',
                        'en_route' => 'Di Perjalanan',
                        'in_progress' => 'Dalam Pengerjaan',
                        'completed' => 'Selesai',
                        'verified' => 'Terverifikasi',
                        'cancelled' => 'Dibatalkan',
                        default => $state,
                    }),
                TextColumn::make('scheduled_start')
                    ->label('Jadwal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('total_cost')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('customer_rating')
                    ->label('Rating')
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
