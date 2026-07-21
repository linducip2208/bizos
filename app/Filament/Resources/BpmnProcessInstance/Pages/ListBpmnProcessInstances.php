<?php

namespace App\Filament\Resources\BpmnProcessInstance\Pages;

use App\Filament\Resources\BpmnProcessInstance\BpmnProcessInstanceResource;
use Filament\Resources\Pages\ListRecords;

class ListBpmnProcessInstances extends ListRecords
{
    protected static string $resource = BpmnProcessInstanceResource::class;
}