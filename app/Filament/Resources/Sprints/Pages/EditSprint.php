<?php

namespace App\Filament\Resources\Sprints\Pages;

use App\Filament\Resources\Sprints\SprintResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSprint extends EditRecord
{
    protected static string $resource = SprintResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sprint_board')
                ->label('Sprint Board')
                ->icon('heroicon-o-view-columns')
                ->color('primary')
                ->url(fn () => SprintResource::getUrl('index') . '?sprint_id=' . $this->getRecord()->id),
            DeleteAction::make(),
        ];
    }
}