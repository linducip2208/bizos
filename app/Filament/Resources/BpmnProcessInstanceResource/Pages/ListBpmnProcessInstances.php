<?php

namespace App\Filament\Resources\BpmnProcessInstanceResource\Pages;

use App\Filament\Resources\BpmnProcessInstanceResource\BpmnProcessInstanceResource;
use Filament\Resources\Pages\ListRecords;

class ListBpmnProcessInstances extends ListRecords
{
    protected static string $resource = BpmnProcessInstanceResource::class;
}
