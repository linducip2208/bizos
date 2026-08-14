<?php

namespace App\Filament\Resources\Contracts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ContractTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('contract_number')
                    ->label('Nomor Kontrak')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('contract_type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'service' => 'Layanan',
                        'procurement' => 'Pengadaan',
                        'tenancy' => 'Sewa',
                        'employment' => 'Karyawan',
                        'project' => 'Proyek',
                        'subcontractor' => 'Subkontraktor',
                        'partnership' => 'Kemitraan',
                        'other' => 'Lainnya',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'service' => 'info',
                        'procurement' => 'warning',
                        'tenancy' => 'success',
                        'employment' => 'primary',
                        'project' => 'danger',
                        'subcontractor' => 'gray',
                        'partnership' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'pending_approval' => 'Menunggu Approval',
                        'active' => 'Aktif',
                        'amended' => 'Amandemen',
                        'expired' => 'Kadaluarsa',
                        'terminated' => 'Dihentikan',
                        'renewed' => 'Diperpanjang',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'pending_approval' => 'warning',
                        'active' => 'success',
                        'amended' => 'info',
                        'expired' => 'danger',
                        'terminated' => 'danger',
                        'renewed' => 'primary',
                        default => 'gray',
                    }),
                TextColumn::make('value')
                    ->label('Nilai')
                    ->money('IDR')
                    ->sortable()
                    ->alignEnd(),
                TextColumn::make('start_date')
                    ->label('Mulai')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('Berakhir')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('contract_type')
                    ->label('Tipe')
                    ->options([
                        'service' => 'Layanan',
                        'procurement' => 'Pengadaan',
                        'tenancy' => 'Sewa',
                        'employment' => 'Karyawan',
                        'project' => 'Proyek',
                        'subcontractor' => 'Subkontraktor',
                        'partnership' => 'Kemitraan',
                        'other' => 'Lainnya',
                    ]),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'pending_approval' => 'Menunggu Approval',
                        'active' => 'Aktif',
                        'amended' => 'Amandemen',
                        'expired' => 'Kadaluarsa',
                        'terminated' => 'Dihentikan',
                        'renewed' => 'Diperpanjang',
                    ]),
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
