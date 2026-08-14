<?php

namespace App\Services;

use App\Models\PosTransaction;
use App\Models\Printer;
use App\Models\ReceiptLayout;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Collection;

class ReceiptPrintService
{
    /**
     * Build HTML receipt (untuk preview / print via browser & A4).
     */
    public function generateReceiptHtml(PosTransaction $transaction, ?ReceiptLayout $layout = null): string
    {
        $transaction->loadMissing(['items.product', 'items.variant', 'payments', 'company', 'branch', 'member', 'cashier']);

        $layout = $layout ?? $this->defaultLayout($transaction->company_id, 'pos_receipt');
        $company = $transaction->company;
        $branch = $transaction->branch;
        $fontClass = match ($layout?->font_size ?? 'medium') {
            'small' => 'text-[11px]',
            'large' => 'text-[15px]',
            default => 'text-[13px]',
        };

        $qr = $layout?->show_qr
            ? $this->generateQrSvg($this->receiptQrPayload($transaction))
            : null;

        $items = $this->normalizeItems($transaction);
        $payments = $this->normalizePayments($transaction);

        $headerText = $layout?->header_text ? nl2br(e($layout->header_text)) : null;
        $footerText = $layout?->footer_text ? nl2br(e($layout->footer_text)) : null;
        $logoUrl = ($layout?->show_logo && $company?->logo)
            ? \Illuminate\Support\Facades\Storage::url($company->logo)
            : null;

        return view('filament.pages.receipt.receipt-html', [
            'transaction' => $transaction,
            'layout' => $layout,
            'company' => $company,
            'branch' => $branch,
            'fontClass' => $fontClass,
            'qr' => $qr,
            'items' => $items,
            'payments' => $payments,
            'headerText' => $headerText,
            'footerText' => $footerText,
            'logoUrl' => $logoUrl,
        ])->render();
    }

    /**
     * Build ESC/POS raw bytes untuk thermal printer.
     */
    public function generateEscPos(PosTransaction $transaction, ?Printer $printer = null): string
    {
        $transaction->loadMissing(['items.product', 'items.variant', 'payments', 'company', 'branch', 'member', 'cashier']);

        $printer = $printer ?? $this->defaultPrinter($transaction->company_id, $transaction->branch_id);
        $layout = $this->defaultLayout($transaction->company_id, 'pos_receipt');

        $cpl = $printer?->character_per_line ?: ($printer?->paper_width == 80 ? 48 : 32);

        $out = $this->escInit();
        $out .= $this->escAlign(1);

        if ($layout?->show_logo && $transaction->company?->name) {
            $out .= $this->escFont(true, true);
            $out .= $this->escLines($this->wrap($transaction->company->name, $cpl), $cpl, true);
            $out .= $this->escFont(false, false);
        }

        if ($transaction->branch?->name) {
            $out .= $this->escLine($transaction->branch->name, $cpl, true);
        }

        if ($transaction->company?->address) {
            $out .= $this->escLines($this->wrap($transaction->company->address, $cpl), $cpl, true);
        }
        if ($transaction->company?->phone) {
            $out .= $this->escLine('Telp: ' . $transaction->company->phone, $cpl, true);
        }

        if ($layout?->header_text) {
            $out .= $this->escLine(str_repeat('-', $cpl), $cpl);
            $out .= $this->escLines($this->wrap($layout->header_text, $cpl), $cpl, true);
        }

        $out .= $this->escLine(str_repeat('-', $cpl), $cpl);

        $out .= $this->escTwoCol('No', $transaction->receipt_number, $cpl);
        $out .= $this->escTwoCol('Tgl', $transaction->transaction_date?->format('d-m-Y H:i'), $cpl);
        $out .= $this->escTwoCol('Kasir', $this->cashierName($transaction), $cpl);
        if ($transaction->member) {
            $out .= $this->escTwoCol('Member', $transaction->member->name, $cpl);
        }

        $out .= $this->escLine(str_repeat('-', $cpl), $cpl);

        foreach ($this->normalizeItems($transaction) as $item) {
            $out .= $this->escLine($item['name'], $cpl);
            $out .= $this->escLine(
                $this->qtyFormat($item['quantity']) . ' x ' . $this->money($item['unit_price'])
                . '  ' . $this->money($item['line_total']),
                $cpl
            );
        }

        $out .= $this->escLine(str_repeat('-', $cpl), $cpl);

        $out .= $this->escTwoCol('Subtotal', $this->money($transaction->subtotal), $cpl);
        if ((float) $transaction->discount_total > 0) {
            $out .= $this->escTwoCol('Diskon', '-' . $this->money($transaction->discount_total), $cpl);
        }
        if ($layout?->show_tax_summary && (float) $transaction->tax_total > 0) {
            $out .= $this->escTwoCol('Pajak', $this->money($transaction->tax_total), $cpl);
        }
        $out .= $this->escFont(true, true);
        $out .= $this->escTwoCol('TOTAL', $this->money($transaction->grand_total), $cpl);
        $out .= $this->escFont(false, false);

        if ($layout?->show_payment_summary) {
            $out .= $this->escLine(str_repeat('-', $cpl), $cpl);
            foreach ($this->normalizePayments($transaction) as $payment) {
                $out .= $this->escTwoCol($payment['method'], $this->money($payment['amount']), $cpl);
            }
            $change = (float) $transaction->payments->sum('amount') - (float) $transaction->grand_total;
            if ($change > 0) {
                $out .= $this->escTwoCol('Kembalian', $this->money($change), $cpl);
            }
        }

        if ($layout?->footer_text) {
            $out .= $this->escLine(str_repeat('-', $cpl), $cpl);
            $out .= $this->escLines($this->wrap($layout->footer_text, $cpl), $cpl, true);
        }

        $out .= $this->escFeed(3);
        $out .= $this->escCut();

        return $out;
    }

    /**
     * Build plain-text receipt (58mm/80mm) — untuk preview monospace / non-thermal.
     */
    public function generateReceiptText(PosTransaction $transaction, ?ReceiptLayout $layout = null): string
    {
        $transaction->loadMissing(['items.product', 'items.variant', 'payments', 'company', 'branch', 'member', 'cashier']);

        $layout = $layout ?? $this->defaultLayout($transaction->company_id, 'pos_receipt');
        $cpl = $layout?->type === 'invoice' ? 48 : 32;

        $lines = [];
        $lines[] = $this->center($transaction->company?->name ?? '', $cpl);
        $lines[] = $this->center($transaction->branch?->name ?? '', $cpl);
        if ($transaction->company?->address) {
            foreach ($this->wrap($transaction->company->address, $cpl) as $l) {
                $lines[] = $this->center($l, $cpl);
            }
        }
        if ($transaction->company?->phone) {
            $lines[] = $this->center('Telp: ' . $transaction->company->phone, $cpl);
        }

        if ($layout?->header_text) {
            $lines[] = str_repeat('-', $cpl);
            foreach ($this->wrap($layout->header_text, $cpl) as $l) {
                $lines[] = $this->center($l, $cpl);
            }
        }

        $lines[] = str_repeat('-', $cpl);
        $lines[] = $this->twoCol('No', $transaction->receipt_number, $cpl);
        $lines[] = $this->twoCol('Tgl', $transaction->transaction_date?->format('d-m-Y H:i'), $cpl);
        $lines[] = $this->twoCol('Kasir', $this->cashierName($transaction), $cpl);
        if ($transaction->member) {
            $lines[] = $this->twoCol('Member', $transaction->member->name, $cpl);
        }

        $lines[] = str_repeat('-', $cpl);
        foreach ($this->normalizeItems($transaction) as $item) {
            $lines[] = $item['name'];
            $lines[] = $this->qtyFormat($item['quantity']) . ' x ' . $this->money($item['unit_price'])
                . '  ' . $this->money($item['line_total']);
        }

        $lines[] = str_repeat('-', $cpl);
        $lines[] = $this->twoCol('Subtotal', $this->money($transaction->subtotal), $cpl);
        if ((float) $transaction->discount_total > 0) {
            $lines[] = $this->twoCol('Diskon', '-' . $this->money($transaction->discount_total), $cpl);
        }
        if ($layout?->show_tax_summary && (float) $transaction->tax_total > 0) {
            $lines[] = $this->twoCol('Pajak', $this->money($transaction->tax_total), $cpl);
        }
        $lines[] = $this->twoCol('TOTAL', $this->money($transaction->grand_total), $cpl);

        if ($layout?->show_payment_summary) {
            $lines[] = str_repeat('-', $cpl);
            foreach ($this->normalizePayments($transaction) as $payment) {
                $lines[] = $this->twoCol($payment['method'], $this->money($payment['amount']), $cpl);
            }
            $change = (float) $transaction->payments->sum('amount') - (float) $transaction->grand_total;
            if ($change > 0) {
                $lines[] = $this->twoCol('Kembalian', $this->money($change), $cpl);
            }
        }

        if ($layout?->footer_text) {
            $lines[] = str_repeat('-', $cpl);
            foreach ($this->wrap($layout->footer_text, $cpl) as $l) {
                $lines[] = $this->center($l, $cpl);
            }
        }

        $lines[] = str_repeat('=', $cpl);

        return implode("\n", $lines);
    }

    /**
     * Kirim print job ke printer. Network dikirim via raw socket,
     * USB/cloud mengembalikan payload untuk local print client.
     */
    public function printReceipt(PosTransaction $transaction, int $printerId): array
    {
        $printer = Printer::find($printerId);

        if (!$printer) {
            return [
                'success' => false,
                'message' => 'Printer tidak ditemukan.',
            ];
        }

        $payload = $this->generateEscPos($transaction, $printer);

        $result = [
            'success' => true,
            'printer_id' => $printer->id,
            'printer_name' => $printer->name,
            'connection_type' => $printer->connection_type,
            'receipt_number' => $transaction->receipt_number,
            'bytes' => strlen($payload),
            'payload_base64' => base64_encode($payload),
        ];

        if ($printer->connection_type === 'network' && $printer->ip_address) {
            $port = $printer->port ?: 9100;

            try {
                $socket = @fsockopen($printer->ip_address, $port, $errno, $errstr, 3);

                if ($socket) {
                    fwrite($socket, $payload);
                    fclose($socket);
                    $result['message'] = "Struk terkirim ke {$printer->name} ({$printer->ip_address}:{$port}).";
                } else {
                    $result['success'] = false;
                    $result['message'] = "Gagal terhubung ke printer: {$errstr} ({$errno}).";
                }
            } catch (\Throwable $e) {
                $result['success'] = false;
                $result['message'] = 'Gagal mengirim ke printer: ' . $e->getMessage();
            }

            return $result;
        }

        if ($printer->connection_type === 'cloud') {
            $result['message'] = "Print job ({$result['bytes']} bytes) di-queue untuk cloud print client {$printer->name}.";
        } else {
            $result['message'] = "Print job ({$result['bytes']} bytes) siap dikirim ke {$printer->name} via local print client (USB).";
        }

        return $result;
    }

    public function getPrinters(?int $branchId = null): Collection
    {
        $companyId = session('current_company_id') ?? auth()->user()?->company_id;

        return Printer::where('company_id', $companyId)
            ->where('status', 'active')
            ->when($branchId, fn ($q) => $q->where(function ($sub) use ($branchId) {
                $sub->where('branch_id', $branchId)->orWhereNull('branch_id');
            }))
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    protected function defaultPrinter(?int $companyId, ?int $branchId): ?Printer
    {
        return Printer::where('company_id', $companyId)
            ->where('status', 'active')
            ->where(function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)->orWhereNull('branch_id');
            })
            ->orderByDesc('is_default')
            ->first();
    }

    protected function defaultLayout(?int $companyId, string $type): ?ReceiptLayout
    {
        return ReceiptLayout::where('company_id', $companyId)
            ->where('type', $type)
            ->orderByDesc('is_default')
            ->first();
    }

    protected function normalizeItems(PosTransaction $transaction): array
    {
        return $transaction->items->map(fn ($item) => [
            'name' => $item->product?->name . ($item->variant?->name ? ' - ' . $item->variant->name : ''),
            'quantity' => (float) $item->quantity,
            'unit_price' => (float) $item->unit_price,
            'discount_amount' => (float) $item->discount_amount,
            'tax_amount' => (float) $item->tax_amount,
            'line_total' => round(((float) $item->quantity * (float) $item->unit_price) - (float) $item->discount_amount, 2),
        ])->toArray();
    }

    protected function normalizePayments(PosTransaction $transaction): array
    {
        return $transaction->payments->map(fn ($payment) => [
            'method' => app(PosCheckoutService::class)->methodLabel($payment->payment_method),
            'amount' => (float) $payment->amount,
        ])->toArray();
    }

    protected function cashierName(PosTransaction $transaction): string
    {
        $cashier = $transaction->cashier;

        return $cashier
            ? trim($cashier->first_name . ' ' . ($cashier->last_name ?? ''))
            : '-';
    }

    protected function money(float $value): string
    {
        return number_format($value, 0, ',', '.');
    }

    protected function qtyFormat(float $qty): string
    {
        return rtrim(rtrim(number_format($qty, 2, '.', ''), '0'), '.');
    }

    protected function width(string $str): int
    {
        return function_exists('mb_strwidth') ? mb_strwidth($str, 'UTF-8') : strlen($str);
    }

    protected function pad(string $str, int $len): string
    {
        $current = $this->width($str);

        return $str . str_repeat(' ', max(0, $len - $current));
    }

    protected function center(string $str, int $len): string
    {
        $current = $this->width($str);
        $left = max(0, intdiv($len - $current, 2));

        return str_repeat(' ', $left) . $str;
    }

    protected function twoCol(string $left, string $right, int $len): string
    {
        $rightStr = (string) $right;
        $leftWidth = min($this->width($left), $len - $this->width($rightStr) - 1);

        return $this->pad(mb_substr($left, 0, max(0, $leftWidth)), $len - $this->width($rightStr)) . $rightStr;
    }

    protected function wrap(string $str, int $len): array
    {
        $str = trim($str);
        if ($str === '') {
            return [];
        }

        $lines = [];
        $current = '';

        foreach (explode(' ', $str) as $word) {
            if ($this->width($current . ' ' . $word) > $len && $current !== '') {
                $lines[] = $current;
                $current = $word;
            } else {
                $current = trim($current . ' ' . $word);
            }
        }
        $lines[] = $current;

        return $lines;
    }

    protected function receiptQrPayload(PosTransaction $transaction): string
    {
        return json_encode([
            'receipt' => $transaction->receipt_number,
            'total' => (float) $transaction->grand_total,
            'company' => $transaction->company?->name,
        ], JSON_UNESCAPED_UNICODE);
    }

    protected function generateQrSvg(string $data): string
    {
        try {
            $options = new QROptions([
                'outputType' => QRCode::OUTPUT_MARKUP_SVG,
                'eccLevel' => QRCode::ECC_M,
            ]);

            return (new QRCode($options))->render($data);
        } catch (\Throwable $e) {
            return '';
        }
    }

    // ─── ESC/POS low-level commands ──────────────────────────────────────────

    protected function escInit(): string
    {
        return "\x1B\x40";
    }

    protected function escAlign(int $n): string
    {
        return "\x1B\x61" . chr(min(max($n, 0), 2));
    }

    protected function escFont(bool $doubleWidth, bool $doubleHeight): string
    {
        $byte = 0;

        if ($doubleWidth) {
            $byte |= 0x01; // horizontal magnification (low nibble) -> 2x lebar
        }
        if ($doubleHeight) {
            $byte |= 0x10; // vertical magnification (high nibble) -> 2x tinggi
        }

        return "\x1D\x21" . chr($byte);
    }

    protected function escFeed(int $n = 1): string
    {
        return "\x1B\x64" . chr(max(0, $n));
    }

    protected function escCut(): string
    {
        return "\x1D\x56\x42\x00";
    }

    protected function escLine(string $text, int $cpl, bool $center = false): string
    {
        $line = $center ? $this->center($text, $cpl) : $this->pad($text, $cpl);

        return $line . "\n";
    }

    protected function escLines(array $lines, int $cpl, bool $center = false): string
    {
        $out = '';
        foreach ($lines as $line) {
            $out .= $this->escLine($line, $cpl, $center);
        }

        return $out;
    }

    protected function escTwoCol(string $left, string $right, int $cpl): string
    {
        return $this->twoCol($left, $right, $cpl) . "\n";
    }
}
