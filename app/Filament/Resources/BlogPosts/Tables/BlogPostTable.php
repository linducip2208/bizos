<?php

namespace App\Filament\Resources\BlogPosts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BlogPostTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            ImageColumn::make('featured_image')
                ->label('Gambar')
                ->circular()
                ->size(40),
            TextColumn::make('title')
                ->label('Judul')
                ->searchable()
                ->sortable()
                ->limit(60),
            TextColumn::make('category.name')
                ->label('Kategori')
                ->sortable()
                ->placeholder('-'),
            TextColumn::make('author.name')
                ->label('Penulis')
                ->sortable(),
            IconColumn::make('is_published')
                ->label('Publikasi')
                ->boolean(),
            TextColumn::make('published_at')
                ->label('Dipublikasi')
                ->dateTime('d M Y H:i')
                ->sortable()
                ->placeholder('-'),
            TextColumn::make('created_at')
                ->label('Dibuat')
                ->dateTime('d M Y H:i')
                ->sortable(),
        ])->defaultSort('created_at', 'desc')
        ->recordActions([EditAction::make()])
        ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
