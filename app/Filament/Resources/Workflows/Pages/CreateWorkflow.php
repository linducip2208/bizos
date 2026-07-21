<?php

namespace App\Filament\Resources\Workflows\Pages;

use App\Filament\Resources\Workflows\WorkflowResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWorkflow extends CreateRecord
{
    protected static string $resource = WorkflowResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (isset($data['trigger_conditions'])) {
            $data['trigger_conditions'] = array_filter($data['trigger_conditions'] ?? [], fn ($c) => ! empty($c['field']));
        }

        $data['created_by'] = auth()->user()?->employee?->id;

        return $data;
    }
}