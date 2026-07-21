<?php

namespace App\Filament\Resources\NoticeBoardResource\Pages;

use App\Filament\Resources\NoticeBoardResource\NoticeBoardResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListNoticeBoards extends ListRecords
{
    protected static string $resource = NoticeBoardResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()->label('Tambah Pengumuman')]; }
}
