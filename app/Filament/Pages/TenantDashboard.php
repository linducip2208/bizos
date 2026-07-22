<?php

namespace App\Filament\Pages;

use App\Models\Company;
use App\Models\TenantUsageLog;
use App\Services\TenantService;
use App\Services\TenantUsageService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class TenantDashboard extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office';
    protected static string|\UnitEnum|null $navigationGroup = 'Sistem';
    protected static ?int $navigationSort = 905;
    protected static ?string $title = 'Dashboard Tenant';
    protected static ?string $navigationLabel = 'Dashboard Tenant';
    protected static ?string $slug = 'tenant-dashboard';
    protected string $view = 'filament.pages.tenant-dashboard';

    protected static bool $shouldRegisterNavigation = true;

    public static function canAccess(): bool
    {
        return in_array(auth()->user()?->role?->slug, ['super-admin', 'admin']);
    }

    public function getTitle(): string
    {
        return 'Dashboard Tenant';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('provision_tenant')
                ->label('Buat Tenant Baru')
                ->icon('heroicon-o-plus-circle')
                ->color('success')
                ->form([
                    TextInput::make('company_name')
                        ->label('Nama Perusahaan')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('admin_name')
                        ->label('Nama Admin')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('admin_email')
                        ->label('Email Admin')
                        ->email()
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
                    Textarea::make('notes')
                        ->label('Catatan')
                        ->maxLength(500),
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

            Action::make('export_all_data')
                ->label('Export Semua Data')
                ->icon('heroicon-o-archive-box-arrow-down')
                ->color('gray')
                ->form([
                    Select::make('company_id')
                        ->label('Perusahaan')
                        ->options(Company::pluck('name', 'id'))
                        ->required()
                        ->searchable(),
                ])
                ->action(function (array $data) {
                    $service = app(TenantService::class);
                    $company = Company::findOrFail($data['company_id']);
                    $path = $service->exportTenantData($company);

                    Notification::make()
                        ->title('Data tenant berhasil diexport')
                        ->body('Path: ' . $path)
                        ->success()
                        ->send();
                })
                ->modalWidth('lg'),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Company::query()->withCount(['users', 'employees']))
            ->columns([
                TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                IconColumn::make('is_suspended')
                    ->label('Suspend')
                    ->boolean()
                    ->trueIcon('heroicon-o-x-circle')
                    ->trueColor('danger')
                    ->falseIcon('heroicon-o-check-circle')
                    ->falseColor('success'),
                TextColumn::make('users_count')
                    ->label('Pengguna')
                    ->counts('users')
                    ->sortable(),
                TextColumn::make('employees_count')
                    ->label('Karyawan')
                    ->counts('employees')
                    ->sortable(),
                TextColumn::make('subscription_end')
                    ->label('Langganan Akhir')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('suspended_reason')
                    ->label('Alasan Suspend')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('is_suspended')
                    ->label('Status')
                    ->options([
                        '0' => 'Aktif',
                        '1' => 'Disuspend',
                    ]),
            ])
            ->actions([
                \Filament\Tables\Actions\Action::make('suspend')
                    ->label('Suspend')
                    ->icon('heroicon-o-lock-closed')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->form([
                        Textarea::make('reason')
                            ->label('Alasan Suspend')
                            ->required()
                            ->maxLength(500),
                    ])
                    ->action(function (Company $record, array $data) {
                        app(TenantService::class)->suspendTenant($record, $data['reason']);
                        Notification::make()
                            ->title('Tenant berhasil disuspend')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Company $record) => !$record->is_suspended),

                \Filament\Tables\Actions\Action::make('reactivate')
                    ->label('Aktifkan')
                    ->icon('heroicon-o-lock-open')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Company $record) {
                        app(TenantService::class)->reactivateTenant($record);
                        Notification::make()
                            ->title('Tenant berhasil diaktifkan kembali')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Company $record) => $record->is_suspended),

                \Filament\Tables\Actions\Action::make('view_usage')
                    ->label('Usage')
                    ->icon('heroicon-o-chart-bar')
                    ->color('info')
                    ->modalHeading(fn (Company $record) => 'Usage: ' . $record->name)
                    ->modalContent(function (Company $record) {
                        $usage = app(TenantService::class)->getUsage($record);
                        return view('filament.components.tenant-usage-modal', [
                            'company' => $record,
                            'usage' => $usage,
                        ]);
                    })
                    ->modalWidth('2xl'),

                \Filament\Tables\Actions\Action::make('export')
                    ->label('Export')
                    ->icon('heroicon-o-archive-box-arrow-down')
                    ->color('gray')
                    ->action(function (Company $record) {
                        $path = app(TenantService::class)->exportTenantData($record);
                        Notification::make()
                            ->title('Data diexport')
                            ->body($path)
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                \Filament\Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
