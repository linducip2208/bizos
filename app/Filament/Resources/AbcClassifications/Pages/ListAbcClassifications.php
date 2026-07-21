<?php

namespace App\Filament\Resources\AbcClassifications\Pages;

use App\Filament\Concerns\HasBulkActions;
use App\Filament\Concerns\HasExcelExport;
use App\Filament\Resources\AbcClassifications\AbcClassificationResource;
use App\Models\AbcClassification;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;

class ListAbcClassifications extends ListRecords
{
    use HasExcelExport, HasBulkActions;

    protected static string $resource = AbcClassificationResource::class;

    protected function getCustomHeaderActions(): array
    {
        return [
            Action::make('recalculate_abc')
                ->label('Hitung Ulang ABC')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->action(function () {
                    $this->recalculateAbc();

                    Notification::make()
                        ->title('Klasifikasi ABC berhasil dihitung ulang')
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function recalculateAbc(): void
    {
        $products = Product::with(['transactionItems'])
            ->where('is_active', true)
            ->get();

        $classifications = [];
        $totalValue = 0;

        foreach ($products as $product) {
            $annualValue = $product->transactionItems()
                ->sum(DB::raw('quantity * unit_price'));

            if ($annualValue > 0) {
                $classifications[] = [
                    'product_id' => $product->id,
                    'company_id' => $product->company_id,
                    'annual_consumption_value' => $annualValue,
                ];
                $totalValue += $annualValue;
            }
        }

        usort($classifications, function ($a, $b) {
            return $b['annual_consumption_value'] <=> $a['annual_consumption_value'];
        });

        $cumulative = 0;
        foreach ($classifications as &$item) {
            $cumulative += $item['annual_consumption_value'];
            $item['cumulative_percent'] = $totalValue > 0
                ? ($cumulative / $totalValue) * 100
                : 0;

            if ($item['cumulative_percent'] <= 70) {
                $item['classification'] = 'A';
            } elseif ($item['cumulative_percent'] <= 90) {
                $item['classification'] = 'B';
            } else {
                $item['classification'] = 'C';
            }

            $item['calculated_at'] = now();
        }

        foreach ($classifications as $data) {
            AbcClassification::updateOrCreate(
                [
                    'company_id' => $data['company_id'],
                    'product_id' => $data['product_id'],
                ],
                [
                    'classification' => $data['classification'],
                    'annual_consumption_value' => $data['annual_consumption_value'],
                    'cumulative_percent' => $data['cumulative_percent'],
                    'calculated_at' => $data['calculated_at'],
                ]
            );
        }
    }
}
