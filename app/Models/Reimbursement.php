<?php

namespace App\Models;

use App\Concerns\HasApprovalWorkflow;
use App\Concerns\HasBranchScope;
use App\Contracts\Approvalable;
use Illuminate\Database\Eloquent\Model;

class Reimbursement extends Model implements Approvalable
{
    use HasBranchScope, HasApprovalWorkflow;

    public function getApprovalModule(): string { return 'reimbursement'; }
    public function getApprovalTitle(): string { $emp = $this->employee; $cat = $this->category; return "Reimbursement: " . ($emp ? trim($emp->first_name . ' ' . $emp->last_name) : 'Unknown') . ' — ' . ($cat?->name ?? 'Tanpa Kategori'); }
    public function getApprovalRequesterId(): int { return $this->employee_id ?? 0; }
    public function getApprovalWorkflowName(): string { return 'Reimbursement'; }
    public function onApproved(): void { $this->update(['status' => 'approved']); }
    public function onRejected(string $reason): void { $this->update(['status' => 'rejected', 'rejection_reason' => $reason]); }

    protected $fillable = [
        'employee_id',
        'category_id',
        'date',
        'amount',
        'description',
        'status',
        'rejection_reason',
        'paid_date',
        'paid_amount',
        'receipt_image_path',
        'ocr_data',
        'ocr_confidence',
        'ocr_status',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'status' => 'string',
        'paid_date' => 'date',
        'paid_amount' => 'decimal:2',
        'ocr_data' => 'array',
        'ocr_confidence' => 'decimal:2',
        'ocr_status' => 'string',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function category()
    {
        return $this->belongsTo(ReimbursementCategory::class, 'category_id');
    }

    public function reimbursementAttachments()
    {
        return $this->hasMany(ReimbursementAttachment::class);
    }

    public function autoFill(): void
    {
        $ocrData = $this->ocr_data;

        if (empty($ocrData)) {
            return;
        }

        $updateData = [];

        if (empty($this->date) && !empty($ocrData['transaction_date'])) {
            $updateData['date'] = $ocrData['transaction_date'];
        }

        if (empty($this->amount) && !empty($ocrData['total_amount'])) {
            $updateData['amount'] = (float) $ocrData['total_amount'];
        }

        if (empty($this->description)) {
            $merchant = $ocrData['merchant_name'] ?? '';
            $receipt = $ocrData['receipt_number'] ?? '';
            $items = collect($ocrData['line_items'] ?? [])->pluck('description')->take(3)->implode(', ');

            $parts = array_filter([$merchant, $receipt, $items]);
            if (!empty($parts)) {
                $updateData['description'] = implode(' - ', $parts);
            }
        }

        if (empty($this->category_id) && !empty($ocrData['category_id'])) {
            $updateData['category_id'] = $ocrData['category_id'];
        }

        if (!empty($updateData)) {
            $this->update($updateData);
        }
    }
}
