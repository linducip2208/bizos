<?php

namespace App\Filament\Resources\TimesheetEntries\Pages;

use App\Filament\Resources\TimesheetEntries\TimesheetEntryResource;
use App\Models\Project;
use App\Models\TimesheetEntry;
use App\Services\ProjectFinanceService;
use Filament\Actions\CreateAction;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class ListTimesheetEntries extends ListRecords
{
    protected static string $resource = TimesheetEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('createInvoice')
                ->label('Buat Invoice')
                ->icon('heroicon-o-document-currency-dollar')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Buat Invoice dari Timesheet')
                ->modalDescription('Buat invoice dari entri timesheet yang dapat ditagihkan.')
                ->modalSubmitActionLabel('Buat Invoice')
                ->form([
                    Select::make('project_id')
                        ->label('Project')
                        ->options(
                            Project::whereHas('tasks.timesheetEntries', function ($q) {
                                $q->where('is_billable', true)->where('is_billed', false);
                            })
                            ->get()
                            ->mapWithKeys(fn ($p) => [$p->id => $p->code . ' - ' . $p->name])
                        )
                        ->searchable()
                        ->required(),
                    DatePicker::make('period_start')
                        ->label('Periode Mulai')
                        ->required()
                        ->default(now()->startOfMonth()),
                    DatePicker::make('period_end')
                        ->label('Periode Selesai')
                        ->required()
                        ->default(now()->endOfMonth())
                        ->afterOrEqual('period_start'),
                ])
                ->action(function (array $data): void {
                    $project = Project::findOrFail($data['project_id']);
                    $service = app(ProjectFinanceService::class);

                    try {
                        $invoice = $service->createInvoiceFromTimesheet(
                            $project,
                            $data['period_start'],
                            $data['period_end']
                        );

                        Notification::make()
                            ->title('Invoice berhasil dibuat')
                            ->body("Invoice #{$invoice->invoice_number} — Total: Rp " . number_format($invoice->total, 0, ',', '.'))
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Gagal membuat invoice')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }

    protected function getTableFilters(): array
    {
        return [
            SelectFilter::make('is_billed')
                ->label('Status Tagihan')
                ->options([
                    '0' => 'Belum Ditagih',
                    '1' => 'Sudah Ditagih',
                ])
                ->attribute('is_billed'),

            Filter::make('is_billable')
                ->label('Dapat Ditagihkan')
                ->query(fn (Builder $query): Builder => $query->where('is_billable', true))
                ->default(),

            Filter::make('unbilled_only')
                ->label('Hanya Belum Ditagih')
                ->query(fn (Builder $query): Builder => $query->where('is_billable', true)->where('is_billed', false))
                ->toggle(),
        ];
    }

    protected function getTableBulkActions(): array
    {
        return [
            \Filament\Actions\BulkActionGroup::make([
                \Filament\Actions\BulkAction::make('markAsBilled')
                    ->label('Tandai Sudah Ditagih')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (\Illuminate\Support\Collection $records): void {
                        TimesheetEntry::whereIn('id', $records->pluck('id'))
                            ->update(['is_billed' => true]);

                        Notification::make()
                            ->title('Entri ditandai sudah ditagih')
                            ->body(count($records) . ' entri berhasil diperbarui.')
                            ->success()
                            ->send();
                    }),

                \Filament\Actions\BulkAction::make('markAsUnbilled')
                    ->label('Tandai Belum Ditagih')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->action(function (\Illuminate\Support\Collection $records): void {
                        TimesheetEntry::whereIn('id', $records->pluck('id'))
                            ->update(['is_billed' => false, 'invoice_id' => null]);

                        Notification::make()
                            ->title('Entri ditandai belum ditagih')
                            ->body(count($records) . ' entri berhasil diperbarui.')
                            ->success()
                            ->send();
                    }),

                \Filament\Actions\DeleteBulkAction::make(),
            ]),
        ];
    }
}