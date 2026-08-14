<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ThreeWayMatch extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'purchase_order_id',
        'goods_receipt_id',
        'invoice_id',
        'match_status',
        'po_total',
        'gr_total',
        'invoice_total',
        'quantity_match',
        'price_match',
        'total_match',
        'variance_amount',
        'variance_percent',
        'mismatch_details',
        'resolution_status',
        'resolution_notes',
        'matched_by',
        'matched_at',
        'approved_by',
        'approved_at',
        'created_by',
    ];

    protected $casts = [
        'po_total' => 'decimal:2',
        'gr_total' => 'decimal:2',
        'invoice_total' => 'decimal:2',
        'quantity_match' => 'boolean',
        'price_match' => 'boolean',
        'total_match' => 'boolean',
        'variance_amount' => 'decimal:2',
        'variance_percent' => 'decimal:4',
        'mismatch_details' => 'json',
        'matched_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function goodsReceipt()
    {
        return $this->belongsTo(GoodsReceipt::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function matchedBy()
    {
        return $this->belongsTo(Employee::class, 'matched_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }

    public function createdBy()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }

    public function isMatched(): bool
    {
        return $this->match_status === 'matched';
    }

    public function isPartialMatch(): bool
    {
        return $this->match_status === 'partial_match';
    }

    public function isMismatch(): bool
    {
        return $this->match_status === 'mismatch';
    }

    public function isPending(): bool
    {
        return $this->match_status === 'pending';
    }

    public function getMismatchSummary(): string
    {
        if ($this->isMatched()) {
            return 'Semua dokumen cocok — PO, GR, dan Invoice sesuai.';
        }

        if ($this->isPending()) {
            return 'Pencocokan belum dilakukan.';
        }

        $details = $this->mismatch_details ?? [];
        if (empty($details)) {
            if ($this->isPartialMatch()) {
                return 'Pencocokan sebagian — beberapa item sesuai, namun ada selisih kecil.';
            }
            return 'Terdapat ketidakcocokan antara PO, GR, dan Invoice.';
        }

        $lines = [];
        foreach ($details as $item) {
            $lines[] = sprintf(
                '%s: %s (diharapkan %s, aktual %s)',
                $item['item'] ?? '-',
                $item['field'] ?? '-',
                $item['expected'] ?? '-',
                $item['actual'] ?? '-'
            );
        }

        return implode(' | ', $lines);
    }
}
