<?php

namespace App\Filament\Resources\DocumentGenerations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DocumentGenerationTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('template.name')
                    ->label('Template')
                    ->searchable()
                    ->sortable(),
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
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'generated' => 'info',
                        'signed' => 'success',
                        'sent' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'generated' => 'Tergenerate',
                        'signed' => 'Tertandatangani',
                        'sent' => 'Terkirim',
                        default => $state,
                    }),
                TextColumn::make('signature_provider')
                    ->label('Provider TTD')
                    ->placeholder('-'),
                TextColumn::make('generatedBy.name')
                    ->label('Dibuat Oleh')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'generated' => 'Tergenerate',
                        'signed' => 'Tertandatangani',
                        'sent' => 'Terkirim',
                    ]),
                SelectFilter::make('template_id')
                    ->label('Template')
                    ->relationship('template', 'name'),
            ])
            ->defaultSort('created_at', 'desc')
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
