<?php

namespace App\Filament\Resources\DocumentTemplates\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DocumentTemplateTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'contract' => 'Kontrak',
                        'offer_letter' => 'Surat Penawaran',
                        'warning_letter' => 'SP',
                        'certificate' => 'Sertifikat',
                        'invoice_custom' => 'Invoice',
                        'custom' => 'Custom',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'contract' => 'primary',
                        'offer_letter' => 'success',
                        'warning_letter' => 'danger',
                        'certificate' => 'warning',
                        'invoice_custom' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('module')
                    ->label('Module')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'employee' => 'Karyawan',
                        'invoice' => 'Invoice',
                        'project' => 'Proyek',
                        'deal' => 'Deal',
                        'course' => 'Pelatihan',
                        'warning' => 'Peringatan',
                        'custom' => 'Custom',
                        default => $state,
                    }),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipe')
                    ->options([
                        'contract' => 'Kontrak',
                        'offer_letter' => 'Surat Penawaran',
                        'warning_letter' => 'SP',
                        'certificate' => 'Sertifikat',
                        'invoice_custom' => 'Invoice',
                        'custom' => 'Custom',
                    ]),
                SelectFilter::make('module')
                    ->label('Module')
                    ->options([
                        'employee' => 'Karyawan',
                        'invoice' => 'Invoice',
                        'project' => 'Proyek',
                        'deal' => 'Deal',
                        'course' => 'Pelatihan',
                        'warning' => 'Peringatan',
                        'custom' => 'Custom',
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
