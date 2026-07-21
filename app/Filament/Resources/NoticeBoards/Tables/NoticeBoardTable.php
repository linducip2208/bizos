<?php

namespace App\Filament\Resources\NoticeBoards\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class NoticeBoardTable
{
    public static function configure(Table $table): Table
    {
        return $table->defaultSort('created_at','desc')->columns([
            IconColumn::make('is_pinned')->label('Pin')->boolean(),
            TextColumn::make('title')->label('Judul')->searchable()->sortable(),
            TextColumn::make('category')->label('Kategori')->badge()->color(fn($s)=>match($s){'urgent'=>'danger','general'=>'gray','hr'=>'primary','it'=>'info','event'=>'success',default=>'gray'})->formatStateUsing(fn($s)=>match($s){'general'=>'Umum','hr'=>'HR','it'=>'IT','urgent'=>'Darurat','event'=>'Acara',default=>$s}),
            TextColumn::make('priority')->label('Prioritas')->badge()->color(fn($s)=>match($s){'urgent'=>'danger','important'=>'warning','normal'=>'success',default=>'gray'})->formatStateUsing(fn($s)=>match($s){'normal'=>'Normal','important'=>'Penting','urgent'=>'Darurat',default=>$s}),
            TextColumn::make('postedBy.name')->label('Diposting Oleh'),
            TextColumn::make('view_count')->label('Dilihat')->numeric()->sortable(),
            TextColumn::make('expires_at')->label('Kedaluwarsa')->date('d M Y H:i'),
            TextColumn::make('created_at')->label('Diposting')->date('d M Y H:i')->sortable(),
        ])->recordActions([EditAction::make()])->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}