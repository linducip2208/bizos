<?php

namespace App\Filament\Resources\SubcontractorContracts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SubcontractorContractTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('contract_number')
                    ->label('Nomor Kontrak')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('project.name')
                    ->label('Proyek')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('supplier.name')
                    ->label('Subkontraktor')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('contract_amount')
                    ->label('Nilai Kontrak')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('retention_percent')
                    ->label('Retensi')
                    ->suffix('%')
                    ->sortable(),
                TextColumn::make('start_date')
                    ->label('Mulai')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('Selesai')
                    ->date('d M Y')
                    ->sortable(),
                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'gray' => 'draft',
                        'success' => 'active',
                        'primary' => 'completed',
                        'danger' => 'terminated',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'active' => 'Aktif',
                        'completed' => 'Selesai',
                        'terminated' => 'Dihentikan',
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
