<?php

namespace App\Filament\Resources\LabResults\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class LabResultTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('labOrder.order_date')
                    ->label('Tgl Order')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('labOrder.patient.full_name')
                    ->label('Pasien')
                    ->searchable(),
                TextColumn::make('test_name')
                    ->label('Nama Tes')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('result_value')
                    ->label('Hasil')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('unit')
                    ->label('Satuan')
                    ->placeholder('-'),
                TextColumn::make('normal_range')
                    ->label('Nilai Normal')
                    ->placeholder('-'),
                IconColumn::make('is_abnormal')
                    ->label('Abnormal')
                    ->boolean()
                    ->trueColor('danger')
                    ->falseColor('success'),
                TextColumn::make('performed_by')
                    ->label('Petugas')
                    ->placeholder('-'),
                TextColumn::make('performed_at')
                    ->label('Tgl Periksa')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-'),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
