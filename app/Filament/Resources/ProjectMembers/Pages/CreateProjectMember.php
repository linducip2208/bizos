<?php

namespace App\Filament\Resources\ProjectMembers\Pages;

use App\Filament\Resources\ProjectMembers\ProjectMemberResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProjectMember extends CreateRecord
{
    protected static string $resource = ProjectMemberResource::class;
}
