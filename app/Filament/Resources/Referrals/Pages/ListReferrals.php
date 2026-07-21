<?php

namespace App\Filament\Resources\Referrals\Pages;

use App\Filament\Concerns\HasExcelExport;
use App\Filament\Resources\Referrals\ReferralResource;
use Filament\Resources\Pages\ListRecords;

class ListReferrals extends ListRecords
{
    use HasExcelExport;

    protected static string $resource = ReferralResource::class;
}
