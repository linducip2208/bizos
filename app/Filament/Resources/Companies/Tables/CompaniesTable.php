<?php

namespace App\Filament\Resources\Companies\Tables;

use App\Models\Company;
use App\Services\TenantService;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CompaniesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nama Perusahaan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->label('Telepon')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
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
                TextColumn::make('subscription_start')
                    ->label('Mulai Langganan')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('subscription_end')
                    ->label('Akhir Langganan')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),

                \Filament\Tables\Actions\Action::make('suspend')
                    ->label('Suspend')
                    ->icon('heroicon-o-lock-closed')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Suspend Perusahaan')
                    ->modalDescription(fn (Company $record) => "Apakah Anda yakin ingin men-suspend {$record->name}?")
                    ->modalSubmitActionLabel('Ya, Suspend')
                    ->form([
                        \Filament\Forms\Components\Textarea::make('reason')
                            ->label('Alasan Suspensi')
                            ->required()
                            ->maxLength(500),
                    ])
                    ->action(function (Company $record, array $data) {
                        app(TenantService::class)->suspendTenant($record, $data['reason']);
                        Notification::make()->title('Perusahaan berhasil disuspend')->success()->send();
                    })
                    ->visible(fn (Company $record) => !$record->is_suspended),

                \Filament\Tables\Actions\Action::make('reactivate')
                    ->label('Aktifkan')
                    ->icon('heroicon-o-lock-open')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Aktifkan Perusahaan')
                    ->modalDescription(fn (Company $record) => "Aktifkan kembali {$record->name}?")
                    ->modalSubmitActionLabel('Ya, Aktifkan')
                    ->action(function (Company $record) {
                        app(TenantService::class)->reactivateTenant($record);
                        Notification::make()->title('Perusahaan berhasil diaktifkan')->success()->send();
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
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    \Filament\Actions\ForceDeleteBulkAction::make(),
                    \Filament\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }
}
