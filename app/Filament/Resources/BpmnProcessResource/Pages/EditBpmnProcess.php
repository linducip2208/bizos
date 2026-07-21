<?php

namespace App\Filament\Resources\BpmnProcessResource\Pages;

use App\Filament\Resources\BpmnProcessResource\BpmnProcessResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBpmnProcess extends EditRecord
{
    protected static string $resource = BpmnProcessResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('designer')
                ->label('Buka Designer')
                ->icon('heroicon-o-pencil-square')
                ->url(fn() => route('filament.admin.pages.bpmn-designer', ['process' => $this->getRecord()]))
                ->color('primary'),
            Actions\DeleteAction::make(),
        ];
    }
}
