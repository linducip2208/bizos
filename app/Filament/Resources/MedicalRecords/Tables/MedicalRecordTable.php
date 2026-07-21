<?php

namespace App\Filament\Resources\MedicalRecords\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class MedicalRecordTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('visit_date', 'desc')
            ->columns([
                TextColumn::make('visit_date')
                    ->label('Tgl Kunjungan')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('patient.full_name')
                    ->label('Pasien')
                    ->searchable(),
                TextColumn::make('doctor.first_name')
                    ->label('Dokter')
                    ->formatStateUsing(fn ($record) => $record->doctor?->first_name . ' ' . $record->doctor?->last_name)
                    ->searchable(),
                TextColumn::make('diagnosis_name')
                    ->label('Diagnosis')
                    ->searchable()
                    ->limit(40)
                    ->placeholder('-'),
                TextColumn::make('diagnosis_code')
                    ->label('ICD-10')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('subjective')
                    ->label('Keluhan')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('assessment')
                    ->label('Penilaian')
                    ->limit(50)
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_final')
                    ->label('Final')
                    ->boolean()
                    ->sortable(),
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