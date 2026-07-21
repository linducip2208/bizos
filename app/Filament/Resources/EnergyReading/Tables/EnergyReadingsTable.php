<?php

namespace App\Filament\Resources\EnergyReading\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EnergyReadingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('meter.name')
                    ->label('Meter')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('kwh')
                    ->label('kWh')
                    ->numeric(3, '.', '')
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('voltage')
                    ->label('Tegangan (V)')
                    ->numeric(1, '.', '')
                    ->sortable(),
                TextColumn::make('current_amps')
                    ->label('Arus (A)')
                    ->numeric(1, '.', '')
                    ->sortable(),
                TextColumn::make('power_factor')
                    ->label('Power Factor')
                    ->numeric(2, '.', '')
                    ->sortable(),
                TextColumn::make('frequency_hz')
                    ->label('Frekuensi (Hz)')
                    ->numeric(2, '.', '')
                    ->sortable(),
                TextColumn::make('recorded_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('recorded_at', 'desc')
            ->filters([
                SelectFilter::make('energy_meter_id')
                    ->label('Meter')
                    ->relationship('meter', 'name')
                    ->searchable()
                    ->preload(),
                Filter::make('recorded_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('recorded_from')->label('Dari'),
                        \Filament\Forms\Components\DatePicker::make('recorded_to')->label('Sampai'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['recorded_from'], fn($q, $d) => $q->whereDate('recorded_at', '>=', $d))
                            ->when($data['recorded_to'], fn($q, $d) => $q->whereDate('recorded_at', '<=', $d));
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}