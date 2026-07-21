<?php

namespace App\Filament\Resources\FieldService\WorkOrderResource\Pages;

use App\Filament\Resources\FieldService\WorkOrderResource\WorkOrderResource;
use App\Services\FieldServiceService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditWorkOrder extends EditRecord
{
    protected static string $resource = WorkOrderResource::class;

    protected function getHeaderActions(): array
    {
        $actions = [
            Action::make('dispatch')
                ->label('Dispatch Teknisi')
                ->icon('heroicon-o-user-plus')
                ->color('info')
                ->form([
                    \Filament\Forms\Components\Placeholder::make('dispatch_info')
                        ->label('Mencari teknisi terdekat...')
                        ->content('Klik submit untuk mencari dan menugaskan teknisi terbaik.'),
                ])
                ->action(function () {
                    $service = app(FieldServiceService::class);
                    $results = $service->dispatchWorkOrder($this->record);

                    if (isset($results['error'])) {
                        Notification::make()->title($results['error'])->danger()->send();
                        return;
                    }

                    $best = $results[0] ?? null;
                    if ($best) {
                        $this->record->update([
                            'technician_id' => $best['technician_id'],
                            'status' => 'assigned',
                        ]);

                        Notification::make()
                            ->title("Teknisi ditugaskan: {$best['name']}")
                            ->body("Jarak: {$best['distance_km']} km | Match Score: {$best['match_score']}")
                            ->success()
                            ->send();
                    }
                }),

            Action::make('checkin')
                ->label('Check-in GPS')
                ->icon('heroicon-o-map-pin')
                ->color('warning')
                ->form([
                    \Filament\Forms\Components\TextInput::make('lat')
                        ->label('Latitude')
                        ->numeric()
                        ->required(),
                    \Filament\Forms\Components\TextInput::make('lng')
                        ->label('Longitude')
                        ->numeric()
                        ->required(),
                ])
                ->action(function (array $data) {
                    $service = app(FieldServiceService::class);
                    $service->checkIn($this->record, (float) $data['lat'], (float) $data['lng']);
                    Notification::make()->title('Check-in berhasil')->success()->send();
                }),

            Action::make('checkout')
                ->label('Check-out GPS')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->form([
                    \Filament\Forms\Components\TextInput::make('lat')
                        ->label('Latitude')
                        ->numeric()
                        ->required(),
                    \Filament\Forms\Components\TextInput::make('lng')
                        ->label('Longitude')
                        ->numeric()
                        ->required(),
                ])
                ->action(function (array $data) {
                    $service = app(FieldServiceService::class);
                    $service->checkOut($this->record, (float) $data['lat'], (float) $data['lng']);
                    Notification::make()->title('Check-out berhasil, WO selesai')->success()->send();
                }),

            Action::make('complete')
                ->label('Selesaikan + Tanda Tangan')
                ->icon('heroicon-o-pencil-square')
                ->color('success')
                ->form([
                    \Filament\Forms\Components\Textarea::make('resolution')
                        ->label('Resolusi')
                        ->required()
                        ->rows(3),
                    \Filament\Forms\Components\Textarea::make('signature')
                        ->label('Tanda Tangan (base64)')
                        ->helperText('Tempel data base64 tanda tangan pelanggan')
                        ->required()
                        ->rows(2),
                    \Filament\Forms\Components\Textarea::make('photo')
                        ->label('Foto (base64)')
                        ->helperText('Tempel data base64 foto hasil pekerjaan')
                        ->required()
                        ->rows(2),
                ])
                ->action(function (array $data) {
                    $service = app(FieldServiceService::class);
                    $service->complete($this->record, $data['resolution'], $data['signature'], $data['photo']);
                    Notification::make()->title('Work Order selesai dengan tanda tangan pelanggan')->success()->send();
                }),

            Action::make('generate_invoice')
                ->label('Generate Invoice')
                ->icon('heroicon-o-document-currency-dollar')
                ->color('violet')
                ->action(function () {
                    $service = app(FieldServiceService::class);
                    $invoice = $service->generateInvoice($this->record);

                    if ($invoice) {
                        Notification::make()
                            ->title("Invoice dibuat: {$invoice->invoice_number}")
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Tidak ada biaya untuk ditagih')
                            ->warning()
                            ->send();
                    }
                }),
        ];

        return $actions;
    }
}
