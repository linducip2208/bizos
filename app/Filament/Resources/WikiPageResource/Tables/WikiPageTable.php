<?php

namespace App\Filament\Resources\WikiPageResource\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WikiPageTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('title')->label('Judul')->searchable()->sortable(),
            TextColumn::make('category.name')->label('Kategori')->sortable(),
            TextColumn::make('status')->label('Status')->badge()->color(fn($s)=>match($s){'published'=>'success','draft'=>'warning','archived'=>'gray',default=>'gray'})->formatStateUsing(fn($s)=>match($s){'published'=>'Publikasi','draft'=>'Draft','archived'=>'Arsip',default=>$s}),
            TextColumn::make('view_count')->label('Dilihat')->numeric()->sortable(),
            TextColumn::make('author.name')->label('Penulis'),
            TextColumn::make('published_at')->label('Dipublikasi')->dateTime('d M Y'),
            TextColumn::make('last_edited_at')->label('Terakhir Diubah')->dateTime('d M Y H:i')->sortable(),
        ])->recordActions([EditAction::make()])->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
