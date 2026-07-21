<?php

namespace App\Filament\Resources\TranslationResource\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TranslationTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('key')->label('Key')->searchable()->sortable(),
            TextColumn::make('locale')->label('Bahasa')->badge()->color(fn($s)=>$s==='id'?'success':'info')->formatStateUsing(fn($s)=>$s==='id'?'Bahasa Indonesia':'English'),
            TextColumn::make('value')->label('Nilai')->searchable()->limit(80),
            TextColumn::make('updated_at')->label('Diperbarui')->dateTime('d M Y H:i')->sortable(),
        ])->filters([
            SelectFilter::make('locale')->label('Bahasa')->options(['id'=>'Bahasa Indonesia','en'=>'English']),
        ])->recordActions([EditAction::make()])->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
