<?php

namespace App\Filament\Resources\ProductionOrders\Pages;

use App\Filament\Resources\ProductionOrders\ProductionOrderResource;
use App\Services\ManufacturingService;
use Filament\Resources\Pages\CreateRecord;

class CreateProductionOrder extends CreateRecord
{
    protected static string $resource = ProductionOrderResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['po_number'] = \App\Models\ProductionOrder::where('company_id', $data['company_id'])
            ->orderBy('id', 'desc')
            ->value('id')
            ? 'PO-' . date('Ymd') . '-' . str_pad(
                (int) substr(
                    \App\Models\ProductionOrder::where('company_id', $data['company_id'])
                        ->orderBy('id', 'desc')
                        ->value('po_number') ?? 'PO-' . date('Ymd') . '-0000',
                    -4
                ) + 1,
                4,
                '0',
                STR_PAD_LEFT
            )
            : 'PO-' . date('Ymd') . '-0001';

        return $data;
    }

    protected function afterCreate(): void
    {
        if ($this->record->bom_id && $this->record->status === 'planned') {
            app(ManufacturingService::class)->generateProductionMaterials($this->record);
        }
    }
}