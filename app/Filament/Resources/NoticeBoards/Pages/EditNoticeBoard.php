<?php

namespace App\Filament\Resources\NoticeBoards\Pages;

use App\Filament\Resources\NoticeBoards\NoticeBoardResource;
use Filament\Resources\Pages\EditRecord;

class EditNoticeBoard extends EditRecord
{
    protected static string $resource = NoticeBoardResource::class;
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}