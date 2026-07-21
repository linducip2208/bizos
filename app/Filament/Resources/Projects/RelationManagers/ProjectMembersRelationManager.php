<?php

namespace App\Filament\Resources\Projects\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use BulkActionGroup;
use DeleteBulkAction;
use DeleteAction;
use EditAction;
use ViewAction;
use Filament\Tables;
use Filament\Tables\Table;

class ProjectMembersRelationManager extends RelationManager
{
    protected static string $relationship = 'projectMembers';
    protected static ?string $title = 'Anggota Proyek';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('employee.first_name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('role')
                    ->label('Peran')
                    ->sortable(),
                Tables\Columns\TextColumn::make('joined_at')
                    ->label('Bergabung')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}