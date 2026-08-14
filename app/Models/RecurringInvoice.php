<?php

namespace App\Models;

use App\Concerns\HasCompanyScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class RecurringInvoice extends Model
{
    use HasCompanyScope;
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'client_id',
        'title',
        'description',
        'frequency',
        'interval',
        'start_date',
        'next_run_date',
        'end_date',
        'status',
        'amount',
        'tax_percent',
        'currency_id',
        'invoice_template_id',
        'items',
        'auto_send',
        'last_generated_at',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'next_run_date' => 'date',
        'end_date' => 'date',
        'amount' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'interval' => 'integer',
        'items' => 'array',
        'auto_send' => 'boolean',
        'last_generated_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function invoiceTemplate()
    {
        return $this->belongsTo(DocumentTemplate::class, 'invoice_template_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeDueForGeneration(Builder $query): Builder
    {
        return $query->where('status', 'active')
            ->whereNotNull('next_run_date')
            ->where('next_run_date', '<=', now()->toDateString());
    }

    public function shouldGenerate(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if (!$this->next_run_date) {
            return false;
        }

        return $this->next_run_date->lte(now());
    }

    public function generateInvoice(): Invoice
    {
        return DB::transaction(function () {
            $now = now();

            $taxPercent = (float) ($this->tax_percent ?? 0);
            $subtotal = (float) $this->amount;
            $taxAmount = round($subtotal * $taxPercent / 100, 2);
            $total = round($subtotal + $taxAmount, 2);

            $invoice = Invoice::create([
                'company_id' => $this->company_id,
                'invoice_number' => $this->generateInvoiceNumber(),
                'invoice_type' => 'sales',
                'invoice_date' => $now->toDateString(),
                'due_date' => $now->copy()->addDays(30)->toDateString(),
                'reference_entity' => 'recurring_invoice',
                'reference_id' => $this->id,
                'subtotal' => $subtotal,
                'discount_amount' => 0,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'paid_amount' => 0,
                'remaining_amount' => $total,
                'status' => 'draft',
                'notes' => $this->description ?? $this->title,
                'currency_id' => $this->currency_id,
            ]);

            $items = $this->items ?? [];

            if (empty($items)) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'description' => $this->title,
                    'quantity' => 1,
                    'unit_price' => $subtotal,
                    'tax_rate' => $taxPercent,
                    'amount' => $subtotal,
                ]);
            } else {
                foreach ($items as $item) {
                    $qty = (float) ($item['quantity'] ?? 1);
                    $price = (float) ($item['unit_price'] ?? 0);
                    $itemTaxRate = (float) ($item['tax_rate'] ?? $taxPercent);
                    $itemAmount = round($qty * $price, 2);

                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'description' => $item['description'] ?? $this->title,
                        'quantity' => $qty,
                        'unit_price' => $price,
                        'tax_rate' => $itemTaxRate,
                        'amount' => $itemAmount,
                    ]);
                }
            }

            $nextRunDate = $this->calculateNextRunDate($this->next_run_date);

            $this->update([
                'last_generated_at' => $now,
                'next_run_date' => $nextRunDate,
                'status' => $this->end_date && $nextRunDate->gt($this->end_date)
                    ? 'completed'
                    : $this->status,
            ]);

            return $invoice->fresh(['invoiceItems']);
        });
    }

    protected function calculateNextRunDate(Carbon $from): Carbon
    {
        $interval = max(1, (int) $this->interval);

        return match ($this->frequency) {
            'daily' => $from->copy()->addDays($interval),
            'weekly' => $from->copy()->addWeeks($interval),
            'monthly' => $from->copy()->addMonths($interval),
            'quarterly' => $from->copy()->addMonths($interval * 3),
            'yearly' => $from->copy()->addYears($interval),
            default => $from->copy()->addMonths($interval),
        };
    }

    protected function generateInvoiceNumber(): string
    {
        $prefix = 'INV-REC-' . strtoupper(substr($this->company?->code ?? 'XX', 0, 4));
        $date = now()->format('Ym');

        $last = Invoice::where('company_id', $this->company_id)
            ->where('invoice_number', 'like', "{$prefix}-{$date}-%")
            ->orderBy('invoice_number', 'desc')
            ->first();

        $seq = 1;
        if ($last && preg_match('/-(\d{5})$/', $last->invoice_number, $m)) {
            $seq = (int) $m[1] + 1;
        }

        return sprintf("{$prefix}-{$date}-%05d", $seq);
    }

    public function frequencyLabel(): string
    {
        return match ($this->frequency) {
            'daily' => 'Harian',
            'weekly' => 'Mingguan',
            'monthly' => 'Bulanan',
            'quarterly' => 'Kuartalan',
            'yearly' => 'Tahunan',
            default => $this->frequency,
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'active' => 'Aktif',
            'paused' => 'Ditunda',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => $this->status,
        };
    }
}
