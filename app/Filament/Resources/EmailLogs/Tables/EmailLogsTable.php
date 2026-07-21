<?php

namespace App\Filament\Resources\EmailLogs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EmailLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('to_email')
                    ->label('Email Tujuan')
                    ->searchable(),
                TextColumn::make('subject')
                    ->label('Subjek')
                    ->searchable()
                    ->limit(40),
                TextColumn::make('lead.email')
                    ->label('Lead')
                    ->searchable(),
                TextColumn::make('client.name')
                    ->label('Klien')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'sent' => 'info', 'opened' => 'success', 'clicked' => 'primary',
                        'bounced' => 'danger', 'replied' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'sent' => 'Terkirim', 'opened' => 'Dibuka', 'clicked' => 'Diklik',
                        'bounced' => 'Bounced', 'replied' => 'Dibalas',
                        default => $state,
                    }),
                TextColumn::make('sent_at')
                    ->label('Dikirim')
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
