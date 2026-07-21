<?php

namespace App\Filament\Resources\MarketingAutomations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MarketingAutomationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('trigger_type')
                    ->label('Trigger')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'lead_created' => 'Lead Baru', 'deal_stage_changed' => 'Deal Stage Berubah',
                        'form_submitted' => 'Form Disubmit', 'email_opened' => 'Email Dibuka',
                        'link_clicked' => 'Link Diklik', 'schedule' => 'Jadwal', 'webhook' => 'Webhook',
                        default => $state,
                    }),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'active' => 'success', 'paused' => 'warning', 'draft' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('execution_count')
                    ->label('Eksekusi')
                    ->sortable(),
                TextColumn::make('last_executed_at')
                    ->label('Terakhir')
                    ->dateTime('d M Y H:i')
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
