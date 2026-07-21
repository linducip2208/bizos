<?php

namespace App\Filament\Resources\ProjectMembers\Pages;

use App\Filament\Resources\ProjectMembers\ProjectMemberResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProjectMember extends EditRecord
{
    protected static string $resource = ProjectMemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
