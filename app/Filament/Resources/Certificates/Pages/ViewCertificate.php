<?php

namespace App\Filament\Resources\Certificates\Pages;

use App\Filament\Resources\Certificates\CertificateResource;
use Filament\Actions\Action;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Storage;

class ViewCertificate extends ViewRecord
{
    protected static string $resource = CertificateResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Sertifikat')
                    ->schema([
                        TextEntry::make('certificate_number')
                            ->label('Nomor Sertifikat'),
                        TextEntry::make('uuid')
                            ->label('UUID')
                            ->copyable(),
                        TextEntry::make('issued_date')
                            ->label('Tanggal Terbit')
                            ->date('d M Y'),
                        TextEntry::make('enrollment.course.title')
                            ->label('Kursus'),
                        TextEntry::make('enrollment.employee.first_name')
                            ->label('Peserta')
                            ->formatStateUsing(fn ($record) => $record->enrollment?->employee
                                ? $record->enrollment->employee->first_name . ' ' . $record->enrollment->employee->last_name
                                : '-'),
                        TextEntry::make('enrollment.completed_at')
                            ->label('Selesai Kursus')
                            ->date('d M Y H:i'),
                    ])
                    ->columns(2),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download_pdf')
                ->label('Download PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function () {
                    $record = $this->getRecord();
                    if ($record->pdf_path && Storage::disk('public')->exists($record->pdf_path)) {
                        return Storage::disk('public')->download($record->pdf_path);
                    }
                    return null;
                })
                ->visible(fn () => $this->getRecord()->pdf_path && Storage::disk('public')->exists($this->getRecord()->pdf_path)),

            Action::make('verify')
                ->label('Verifikasi')
                ->icon('heroicon-o-check-badge')
                ->color('warning')
                ->action(function () {
                    $record = $this->getRecord();
                    $status = $record->uuid ? 'valid' : 'tidak valid';
                    \Filament\Notifications\Notification::make()
                        ->title('Hasil Verifikasi')
                        ->body("Sertifikat #{$record->certificate_number} {$status}. UUID: {$record->uuid}")
                        ->success()
                        ->send();
                }),
        ];
    }
}