<?php

namespace App\Filament\Resources\Objectives\Tables;

use App\Models\Objective;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ObjectivesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Objective')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Objective $record) => $record->parent?->title, position: 'above'),

                TextColumn::make('objective_type')
                    ->label('Tipe')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'company' => 'Perusahaan',
                        'department' => 'Departemen',
                        'team' => 'Tim',
                        'individual' => 'Individu',
                        default => $state,
                    })
                    ->colors([
                        'primary' => 'company',
                        'info' => 'department',
                        'warning' => 'team',
                        'gray' => 'individual',
                    ])
                    ->sortable(),

                TextColumn::make('owner.name')
                    ->label('Pemilik')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('progress_percent')
                    ->label('Progress')
                    ->html()
                    ->alignCenter()
                    ->formatStateUsing(function (?string $state): string {
                        $value = max((float) $state, 0);
                        $color = $value >= 70 ? '#10b981' : ($value >= 40 ? '#f59e0b' : '#ef4444');

                        return '<div class="flex items-center gap-2 justify-center">'
                            . '<div class="w-24 bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">'
                            . '<div class="h-2.5 rounded-full" style="width:' . max($value, 4) . '%;background-color:' . $color . '"></div>'
                            . '</div>'
                            . '<span class="text-xs text-gray-500">' . $value . '%</span>'
                            . '</div>';
                    })
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => Objective::statusOptions()[$state] ?? $state)
                    ->colors(Objective::statusColors())
                    ->sortable(),

                TextColumn::make('cycle.name')
                    ->label('Siklus')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('start_date')
                    ->label('Mulai')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('end_date')
                    ->label('Selesai')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('key_results_count')
                    ->label('Key Results')
                    ->counts('keyResults')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(Objective::statusOptions()),

                SelectFilter::make('objective_type')
                    ->label('Tipe')
                    ->options([
                        'company' => 'Perusahaan',
                        'department' => 'Departemen',
                        'team' => 'Tim',
                        'individual' => 'Individu',
                    ]),

                SelectFilter::make('cycle_id')
                    ->label('Siklus')
                    ->relationship('cycle', 'name'),

                SelectFilter::make('parent_objective_id')
                    ->label('Objective Induk')
                    ->relationship('parent', 'title')
                    ->searchable(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
