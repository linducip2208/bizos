<?php

namespace App\Filament\Resources\BpjsClaims\Pages;

use App\Filament\Resources\BpjsClaims\BpjsClaimResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditBpjsClaim extends EditRecord
{
    protected static string $resource = BpjsClaimResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}