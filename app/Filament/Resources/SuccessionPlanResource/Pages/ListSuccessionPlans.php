<?php

namespace App\Filament\Resources\SuccessionPlanResource\Pages;

use App\Filament\Resources\SuccessionPlanResource\SuccessionPlanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSuccessionPlans extends ListRecords
{
    protected static string $resource = SuccessionPlanResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()->label('Tambah Rencana Suksesi')]; }
}
