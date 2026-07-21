<?php

namespace App\Filament\Resources\IsoPolicies\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class IsoPolicyTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('policy_number')
                    ->label('No')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->formatStateUsing(fn($state) => match($state) {
                        'access_control' => 'Kontrol Akses',
                        'data_classification' => 'Klasifikasi Data',
                        'incident_response' => 'Respon Insiden',
                        'acceptable_use' => 'Penggunaan',
                        'password' => 'Password',
                        'remote_work' => 'Kerja Jarak Jauh',
                        'backup' => 'Backup',
                        'vendor_management' => 'Vendor',
                        'data_protection' => 'Perlindungan Data',
                        default => $state,
                    }),
                TextColumn::make('version')
                    ->label('Versi'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'active' => 'success',
                        'draft' => 'gray',
                        'under_review' => 'warning',
                        'archived' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('effective_date')
                    ->label('Efektif')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('review_due')
                    ->label('Review')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('approver.name')
                    ->label('Disetujui'),
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