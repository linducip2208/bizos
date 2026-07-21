<?php

namespace App\Filament\Resources\LeadScore\Tables;

use App\Models\LeadScore;
use App\Services\LeadScoringService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LeadScoresTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('lead.first_name')
                    ->label('Nama Lead')
                    ->formatStateUsing(fn (LeadScore $record): string => trim($record->lead->first_name . ' ' . $record->lead->last_name))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('lead.email')
                    ->label('Email Lead')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('score')
                    ->label('Skor')
                    ->sortable()
                    ->formatStateUsing(fn (int $state): string => (string) $state),
                TextColumn::make('grade')
                    ->label('Grade')
                    ->state(function (LeadScore $record): string {
                        $service = app(LeadScoringService::class);
                        return $service->getLeadGrade($record->lead);
                    })
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'hot' => 'danger',
                        'warm' => 'warning',
                        'cold' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'hot' => 'Hot',
                        'warm' => 'Warm',
                        'cold' => 'Cold',
                        default => $state,
                    }),
                TextColumn::make('criteria')
                    ->label('Kriteria')
                    ->formatStateUsing(fn (?array $state): string => $state ? json_encode($state, JSON_PRETTY_PRINT) : '-')
                    ->limit(50),
                TextColumn::make('calculated_at')
                    ->label('Dihitung')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->headerActions([
                Action::make('recalculate_all')
                    ->label('Hitung Ulang Semua')
                    ->icon('heroicon-o-arrow-path')
                    ->color('primary')
                    ->action(function () {
                        $service = app(LeadScoringService::class);
                        $count = $service->recalculateAll();

                        Notification::make()
                            ->title('Skor berhasil dihitung ulang')
                            ->body("{$count} lead telah diskor ulang.")
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('calculated_at', 'desc');
    }
}