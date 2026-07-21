<?php

namespace App\Filament\Resources\FormSubmissions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FormSubmissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('form.name')
                    ->label('Formulir')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('submitter_email')
                    ->label('Email Pengirim')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('submittedBy.name')
                    ->label('Dikirim Oleh')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('submitted_at')
                    ->label('Waktu Kirim')
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
