<?php

namespace App\Filament\Resources\MaintenanceRequests\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MaintenanceRequestTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('propertyUnit.unit_number')
                    ->label('Unit')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('requested_by')
                    ->label('Peminta')
                    ->searchable()
                    ->sortable(),
                BadgeColumn::make('category')
                    ->label('Kategori')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'plumbing' => 'Pipa/Air',
                        'electrical' => 'Listrik',
                        'ac' => 'AC',
                        'structural' => 'Struktural',
                        'pest' => 'Hama',
                        'other' => 'Lainnya',
                        default => $state,
                    }),
                BadgeColumn::make('priority')
                    ->label('Prioritas')
                    ->colors([
                        'gray' => 'low',
                        'info' => 'medium',
                        'warning' => 'high',
                        'danger' => 'emergency',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'low' => 'Rendah',
                        'medium' => 'Sedang',
                        'high' => 'Tinggi',
                        'emergency' => 'Darurat',
                        default => $state,
                    }),
                TextColumn::make('description')
                    ->label('Deskripsi')
                    ->limit(50)
                    ->wrap(),
                TextColumn::make('assignedTo.first_name')
                    ->label('Teknisi')
                    ->formatStateUsing(fn ($record) => $record->assignedTo ? "{$record->assignedTo->first_name} {$record->assignedTo->last_name}" : '-')
                    ->placeholder('-'),
                TextColumn::make('cost')
                    ->label('Biaya')
                    ->money('IDR')
                    ->sortable()
                    ->placeholder('-'),
                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'danger' => 'reported',
                        'info' => 'assigned',
                        'warning' => 'in_progress',
                        'success' => 'completed',
                        'primary' => 'verified',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'reported' => 'Dilaporkan',
                        'assigned' => 'Ditugaskan',
                        'in_progress' => 'Dikerjakan',
                        'completed' => 'Selesai',
                        'verified' => 'Terverifikasi',
                        default => $state,
                    }),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
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