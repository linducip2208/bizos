<?php

namespace App\Filament\Resources\LeadActivities\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LeadActivitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('lead.email')
                    ->label('Lead')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('employee.first_name')
                    ->label('Karyawan')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('activity_type')
                    ->label('Tipe Aktivitas')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'panggilan' => 'Panggilan',
                        'email' => 'Email',
                        'pertemuan' => 'Pertemuan',
                        'presentasi' => 'Presentasi',
                        'follow_up' => 'Follow Up',
                        'demo' => 'Demo',
                        'proposal' => 'Proposal',
                        'negosiasi' => 'Negosiasi',
                        'lainnya' => 'Lainnya',
                        default => $state,
                    }),
                TextColumn::make('subject')
                    ->label('Subjek')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'direncanakan' => 'warning',
                        'selesai' => 'success',
                        'dibatalkan' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'direncanakan' => 'Direncanakan',
                        'selesai' => 'Selesai',
                        'dibatalkan' => 'Dibatalkan',
                        default => $state,
                    }),
                TextColumn::make('scheduled_at')
                    ->label('Jadwal')
                    ->date('d M Y H:i')
                    ->sortable(),
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
