<?php

namespace App\Filament\Resources\AdvancedReports\Pages;

use App\Filament\Resources\AdvancedReports\AdvancedReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAdvancedReports extends ListRecords
{
    protected static string $resource = AdvancedReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('builder')
                ->label('Report Builder')
                ->icon('heroicon-o-presentation-chart-line')
                ->url(AdvancedReportResource::getUrl('builder')),
        ];
    }
}
