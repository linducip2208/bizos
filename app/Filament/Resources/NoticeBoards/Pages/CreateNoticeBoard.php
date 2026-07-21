<?php

namespace App\Filament\Resources\NoticeBoards\Pages;

use App\Filament\Resources\NoticeBoards\NoticeBoardResource;
use Filament\Resources\Pages\CreateRecord;

class CreateNoticeBoard extends CreateRecord
{
    protected static string $resource = NoticeBoardResource::class;
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}