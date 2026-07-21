<?php

namespace App\Filament\Resources\PosMembers\Pages;

use App\Filament\Resources\PosMembers\PosMemberResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPosMember extends EditRecord
{
    protected static string $resource = PosMemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}