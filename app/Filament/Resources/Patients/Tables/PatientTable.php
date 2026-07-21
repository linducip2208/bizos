<?php

namespace App\Filament\Resources\Patients\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class PatientTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('patient_number')
                    ->label('No. RM')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('first_name')
                    ->label('Nama Depan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('last_name')
                    ->label('Nama Belakang')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('gender')
                    ->label('JK')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'male' => 'L',
                        'female' => 'P',
                        default => '-',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'male' => 'info',
                        'female' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('birth_date')
                    ->label('Tgl Lahir')
                    ->date('d M Y')
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('phone')
                    ->label('Telepon')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('bpjs_number')
                    ->label('BPJS')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('blood_type')
                    ->label('Gol. Darah')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'unknown' => 'gray',
                        default => 'success',
                    }),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('registered_at')
                    ->label('Tgl Daftar')
                    ->date('d M Y')
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
