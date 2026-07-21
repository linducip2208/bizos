<?php

namespace App\Filament\Resources\DailySiteReports\Pages;

use App\Filament\Resources\DailySiteReports\DailySiteReportResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDailySiteReport extends EditRecord
{
    protected static string $resource = DailySiteReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}