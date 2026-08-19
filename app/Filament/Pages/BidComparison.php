<?php

namespace App\Filament\Pages;

use App\Models\Bid;
use App\Models\Rfq;
use Filament\Pages\Page;

class BidComparison extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-table-cells';

    protected static ?int $navigationSort = 109;

    protected string $view = 'filament.pages.bid-comparison';

    protected static ?string $title = 'Perbandingan Penawaran';

    public static function getNavigationGroup(): ?string
    {
        return 'Procurement';
    }

    public ?int $rfqId = null;

    public ?Rfq $rfq = null;

    public array $bids = [];

    public array $rfqItems = [];

    public array $comparisonMatrix = [];

    public function mount(): void
    {
        $this->rfqId = request('rfq_id');

        if ($this->rfqId) {
            $this->loadComparison();
        }
    }

    public function loadComparison(): void
    {
        $this->rfq = Rfq::with(['items', 'bids' => function ($q) {
            $q->whereIn('status', ['submitted', 'shortlisted', 'accepted'])
                ->with(['supplier', 'items.rfqItem']);
        }])->find($this->rfqId);

        if (!$this->rfq) {
            return;
        }

        $this->rfqItems = $this->rfq->items->toArray();
        $this->bids = $this->rfq->bids->toArray();

        $matrix = [];
        foreach ($this->rfq->bids as $bid) {
            $row = [
                'bid_id' => $bid->id,
                'bid_number' => $bid->bid_number,
                'supplier_name' => $bid->supplier->name,
                'total_amount' => $bid->total_amount,
                'delivery_lead_time_days' => $bid->delivery_lead_time_days,
                'evaluation_score' => $bid->evaluation_score,
                'status' => $bid->status,
                'notes' => $bid->notes,
                'items' => [],
            ];

            foreach ($this->rfq->items as $rfqItem) {
                $bidItem = $bid->items->firstWhere('rfq_item_id', $rfqItem->id);
                $row['items'][$rfqItem->id] = [
                    'rfq_description' => $rfqItem->description,
                    'rfq_quantity' => $rfqItem->quantity,
                    'unit_price' => $bidItem ? $bidItem->unit_price : null,
                    'total_price' => $bidItem ? $bidItem->total_price : null,
                    'delivery_days' => $bidItem ? $bidItem->delivery_days : null,
                    'notes' => $bidItem ? $bidItem->notes : null,
                ];
            }

            $matrix[] = $row;
        }

        $this->comparisonMatrix = $matrix;
    }

    public function getRfqOptions(): array
    {
        return Rfq::whereIn('status', ['open', 'closed', 'awarded'])
            ->orderBy('created_at', 'desc')
            ->pluck('rfq_number', 'id')
            ->toArray();
    }
}
