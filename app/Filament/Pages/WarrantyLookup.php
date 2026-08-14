<?php

namespace App\Filament\Pages;

use App\Models\WarrantyRegistration;
use App\Services\WarrantyService;
use Filament\Pages\Page;

class WarrantyLookup extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-magnifying-glass';

    protected static ?int $navigationSort = 7;

    protected static ?string $title = 'Cek Garansi';

    protected static ?string $slug = 'warranty-lookup';

    protected string $view = 'filament.pages.warranty-lookup';

    public string $serialNumber = '';

    public bool $searched = false;

    public static function getNavigationGroup(): ?string
    {
        return '🎫 Support';
    }

    public function search(): void
    {
        $this->searched = true;
    }

    public function resetSearch(): void
    {
        $this->serialNumber = '';
        $this->searched = false;
    }

    public function getResultProperty(): ?WarrantyRegistration
    {
        if (!$this->searched || trim($this->serialNumber) === '') {
            return null;
        }

        $registration = app(WarrantyService::class)->checkWarranty($this->serialNumber);

        if ($registration) {
            $registration->load([
                'product',
                'warranty',
                'serialNumber',
                'client',
                'claims' => fn ($q) => $q->latest('claim_date'),
            ]);
        }

        return $registration;
    }
}
