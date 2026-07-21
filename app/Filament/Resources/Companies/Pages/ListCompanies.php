<?php

namespace App\Filament\Resources\Companies\Pages;

use App\Filament\Concerns\HasExcelExport;
use App\Filament\Resources\Companies\CompanyResource;
use App\Models\Company;
use App\Services\TenantService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListCompanies extends ListRecords
{
    use HasExcelExport;

    protected static string $resource = CompanyResource::class;

    protected function getHeaderActions(): array
    {
        return array_merge(parent::getHeaderActions(), [
            Action::make('provision_tenant')
                ->label('Buat Tenant Baru')
                ->icon('heroicon-o-building-office-2')
                ->color('success')
                ->form([
                    TextInput::make('company_name')
                        ->label('Nama Perusahaan')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('admin_email')
                        ->label('Email Admin')
                        ->email()
                        ->required()
                        ->maxLength(255),
                    TextInput::make('admin_name')
                        ->label('Nama Admin')
                        ->required()
                        ->maxLength(255),
                    Select::make('plan')
                        ->label('Paket')
                        ->options([
                            'trial' => 'Trial (14 hari)',
                            'basic' => 'Basic',
                            'pro' => 'Professional',
                            'enterprise' => 'Enterprise',
                        ])
                        ->default('trial')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $service = app(TenantService::class);
                    $service->provisionTenant(
                        $data['company_name'],
                        $data['admin_email'],
                        $data['admin_name'],
                        $data['plan']
                    );

                    Notification::make()
                        ->title('Tenant berhasil dibuat')
                        ->success()
                        ->send();
                })
                ->modalWidth('lg')
                ->modalSubmitActionLabel('Buat Tenant'),
        ]);
    }
}