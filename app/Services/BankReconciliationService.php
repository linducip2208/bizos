<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\BankReconciliation;
use App\Models\BankTransaction;
use App\Models\ReconciliationItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BankReconciliationService
{
    protected array $formatDetectors = [
        'bca' => ['tanggal', 'keterangan', 'cabang', 'jumlah', 'mutasi', 'saldo'],
        'mandiri' => ['tanggal', 'keterangan', 'debit', 'kredit', 'saldo'],
        'generic' => ['date', 'description', 'debit', 'credit', 'balance'],
        'generic_id' => ['tanggal', 'keterangan', 'debit', 'kredit', 'saldo'],
    ];

    public function uploadStatement(BankAccount $account, string $filePath): array
    {
        if (!Storage::disk('public')->exists($filePath)) {
            if (!file_exists($filePath)) {
                throw new \RuntimeException('File statement tidak ditemukan: ' . $filePath);
            }
            $content = file_get_contents($filePath);
        } else {
            $content = Storage::disk('public')->get($filePath);
        }

        $rows = $this->parseCsv($content);

        if (count($rows) < 2) {
            throw new \RuntimeException('File CSV kosong atau hanya berisi header.');
        }

        $header = array_map(fn($h) => Str::lower(trim($h)), $rows[0]);
        $format = $this->detectFormat($header);

        $parsed = [];
        for ($i = 1, $len = count($rows); $i < $len; $i++) {
            $row = $rows[$i];
            if (count($row) < count($header)) {
                continue;
            }

            $mapped = $this->mapRow($header, $row, $format);

            if (empty($mapped['date']) && empty($mapped['description'])) {
                continue;
            }

            $parsed[] = $mapped;
        }

        return [
            'format' => $format,
            'bank_name' => $account->bank_name,
            'account_number' => $account->account_number,
            'rows' => $parsed,
            'total_rows' => count($parsed),
        ];
    }

    public function autoMatch(array $statementRows, int $accountId): array
    {
        $existingTxs = BankTransaction::where('bank_account_id', $accountId)
            ->where('is_reconciled', false)
            ->get();

        $matched = [];
        $unmatched = [];
        $usedTxIds = [];

        foreach ($statementRows as $stmtRow) {
            $stmtAmount = abs((float) ($stmtRow['amount'] ?? 0));
            $stmtDate = $stmtRow['date'] ?? null;
            $stmtRef = $stmtRow['reference'] ?? null;
            $stmtDescription = $stmtRow['description'] ?? '';

            if ($stmtAmount <= 0) {
                $unmatched[] = [
                    'statement' => $stmtRow,
                    'reason' => 'amount_zero',
                ];
                continue;
            }

            $bestMatch = null;
            $bestScore = 0;

            foreach ($existingTxs as $tx) {
                if (in_array($tx->id, $usedTxIds)) {
                    continue;
                }

                $txAmount = abs((float) $tx->amount);
                $score = $this->calculateMatchScore($stmtRow, $tx, $stmtAmount, $txAmount, $stmtDate, $stmtRef, $stmtDescription);

                if ($score > $bestScore) {
                    $bestScore = $score;
                    $bestMatch = $tx;
                }
            }

            if ($bestMatch && $bestScore >= 60) {
                $matched[] = [
                    'statement' => $stmtRow,
                    'transaction' => $bestMatch->toArray(),
                    'score' => $bestScore,
                ];
                $usedTxIds[] = $bestMatch->id;
            } else {
                $unmatched[] = [
                    'statement' => $stmtRow,
                    'reason' => $bestMatch ? 'low_score_' . $bestScore : 'no_match',
                    'best_score' => $bestScore,
                ];
            }
        }

        return [
            'matched' => $matched,
            'unmatched' => $unmatched,
            'matched_count' => count($matched),
            'unmatched_count' => count($unmatched),
        ];
    }

    public function createReconciliation(BankAccount $account, array $matches, array $unmatched, array $meta = []): BankReconciliation
    {
        return DB::transaction(function () use ($account, $matches, $unmatched, $meta) {
            $openingBalance = (float) ($meta['opening_balance'] ?? 0);
            $closingBalance = (float) ($meta['closing_balance'] ?? 0);
            $statementBalance = (float) ($meta['statement_balance'] ?? 0);
            $difference = $statementBalance - $closingBalance;

            $firstTxDate = null;
            $lastTxDate = null;

            $reconciliation = BankReconciliation::create([
                'company_id' => $meta['company_id'] ?? auth()->user()->company_id,
                'bank_account_id' => $account->id,
                'period_start' => $meta['period_start'] ?? ($firstTxDate ?? now()->startOfMonth()->toDateString()),
                'period_end' => $meta['period_end'] ?? ($lastTxDate ?? now()->toDateString()),
                'opening_balance' => $openingBalance,
                'closing_balance' => $closingBalance,
                'statement_balance' => $statementBalance,
                'difference' => $difference,
                'status' => 'completed',
                'notes' => $meta['notes'] ?? null,
                'statement_file_path' => $meta['statement_file_path'] ?? null,
                'auto_matched_count' => count($matches),
                'manual_matched_count' => 0,
                'unmatched_count' => count($unmatched),
            ]);

            foreach ($matches as $match) {
                $tx = $match['transaction'] ?? null;
                $stmt = $match['statement'] ?? null;
                $txModel = $tx ? BankTransaction::find($tx['id']) : null;

                ReconciliationItem::create([
                    'reconciliation_id' => $reconciliation->id,
                    'bank_transaction_id' => $txModel?->id,
                    'matched_amount' => (float) ($stmt['amount'] ?? $tx['amount'] ?? 0),
                    'type' => 'matched',
                    'notes' => $stmt['description'] ?? null,
                ]);

                if ($txModel) {
                    $txModel->update([
                        'is_reconciled' => true,
                        'reconciliation_id' => $reconciliation->id,
                    ]);
                }

                $date = $stmt['date'] ?? null;
                if ($date) {
                    if (!$firstTxDate || $date < $firstTxDate) $firstTxDate = $date;
                    if (!$lastTxDate || $date > $lastTxDate) $lastTxDate = $date;
                }
            }

            foreach ($unmatched as $unmatchedItem) {
                $stmt = $unmatchedItem['statement'] ?? [];

                ReconciliationItem::create([
                    'reconciliation_id' => $reconciliation->id,
                    'matched_amount' => (float) ($stmt['amount'] ?? 0),
                    'type' => 'unmatched_bank',
                    'notes' => ($stmt['description'] ?? 'Unmatched') . ' | Reason: ' . ($unmatchedItem['reason'] ?? 'no_match'),
                ]);
            }

            if ($firstTxDate && $lastTxDate) {
                $reconciliation->update([
                    'period_start' => $firstTxDate,
                    'period_end' => $lastTxDate,
                ]);
            }

            $reconciliation->calculateDifference();

            return $reconciliation->fresh(['items', 'bankAccount']);
        });
    }

    public function suggestMatch(BankTransaction $tx): array
    {
        $reconciliation = $tx->reconciliation;

        if (!$reconciliation || !$reconciliation->statement_file_path) {
            return [
                'transaction' => $tx->toArray(),
                'suggestions' => [],
                'message' => 'Tidak ada statement yang diupload untuk rekonsiliasi ini.',
            ];
        }

        $filePath = $reconciliation->statement_file_path;
        if (Storage::disk('public')->exists($filePath)) {
            $filePath = Storage::disk('public')->path($filePath);
        }

        if (!file_exists($filePath)) {
            return [
                'transaction' => $tx->toArray(),
                'suggestions' => [],
                'message' => 'File statement tidak ditemukan.',
            ];
        }

        $content = file_get_contents($filePath);
        $rows = $this->parseCsv($content);
        $header = array_map(fn($h) => Str::lower(trim($h)), $rows[0]);
        $format = $this->detectFormat($header);

        $txAmount = abs((float) $tx->amount);
        $txDate = $tx->transaction_date?->format('Y-m-d');
        $txRef = $tx->reference_number;

        $candidates = [];
        for ($i = 1, $len = count($rows); $i < $len; $i++) {
            $row = $rows[$i];
            $mapped = $this->mapRow($header, $row, $format);
            $stmtAmount = abs((float) ($mapped['amount'] ?? 0));

            $score = 0;
            if ($stmtAmount === $txAmount) {
                $score += 50;
            } elseif ($stmtAmount > 0) {
                $pctDiff = abs($txAmount - $stmtAmount) / max($txAmount, $stmtAmount);
                if ($pctDiff < 0.05) $score += 30;
                elseif ($pctDiff < 0.10) $score += 15;
            }

            $stmtDate = $mapped['date'] ?? null;
            if ($txDate && $stmtDate) {
                $dayDiff = abs(Carbon::parse($txDate)->diffInDays(Carbon::parse($stmtDate)));
                if ($dayDiff === 0) $score += 40;
                elseif ($dayDiff <= 3) $score += 25;
                elseif ($dayDiff <= 7) $score += 10;
            }

            $stmtRef = $mapped['reference'] ?? null;
            if ($txRef && $stmtRef) {
                similar_text(Str::lower($txRef), Str::lower($stmtRef), $simPct);
                if ($simPct > 80) $score += 30;
                elseif ($simPct > 50) $score += 15;
            }

            if ($score > 0) {
                $candidates[] = array_merge($mapped, ['match_score' => $score]);
            }
        }

        usort($candidates, fn($a, $b) => $b['match_score'] <=> $a['match_score']);
        $candidates = array_slice($candidates, 0, 5);

        return [
            'transaction' => $tx->toArray(),
            'suggestions' => $candidates,
            'total_candidates' => count($candidates),
        ];
    }

    protected function parseCsv(string $content): array
    {
        $content = $this->normalizeEncoding($content);
        $lines = explode("\n", str_replace("\r\n", "\n", $content));
        $lines = array_filter($lines, fn($l) => !empty(trim($l)));

        $rows = [];
        foreach ($lines as $line) {
            $delimiter = $this->detectDelimiter($line, $lines);
            $row = str_getcsv($line, $delimiter);
            if (!empty(array_filter($row))) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    protected function detectDelimiter(string $line, array $allLines): string
    {
        $sampleLine = $line;
        foreach ($allLines as $l) {
            if (substr_count($l, ';') > 1) break;
            if (substr_count($l, ',') > 1) break;
        }

        $semicolonCount = substr_count($sampleLine, ';');
        $commaCount = substr_count($sampleLine, ',');
        $tabCount = substr_count($sampleLine, "\t");

        if ($tabCount > $commaCount && $tabCount > $semicolonCount) return "\t";
        if ($semicolonCount > $commaCount) return ';';
        return ',';
    }

    protected function normalizeEncoding(string $content): string
    {
        $encoding = mb_detect_encoding($content, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
        if ($encoding && $encoding !== 'UTF-8') {
            $content = mb_convert_encoding($content, 'UTF-8', $encoding);
        }

        if (str_starts_with($content, "\xEF\xBB\xBF")) {
            $content = substr($content, 3);
        }

        return $content;
    }

    protected function detectFormat(array $header): string
    {
        $bestFormat = 'generic';
        $bestScore = 0;

        foreach ($this->formatDetectors as $format => $expectedHeaders) {
            $score = 0;
            foreach ($expectedHeaders as $expected) {
                foreach ($header as $actual) {
                    $sim = similar_text($expected, $actual);
                    if ($actual === $expected || $sim >= strlen($expected) * 0.85) {
                        $score++;
                        break;
                    }
                    if (str_contains($actual, $expected)) {
                        $score += 0.5;
                    }
                }
            }
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestFormat = $format;
            }
        }

        return $bestFormat;
    }

    protected function mapRow(array $header, array $row, string $format): array
    {
        $data = [];
        foreach ($header as $i => $col) {
            if (isset($row[$i])) {
                $data[$col] = trim($row[$i]);
            }
        }

        $normalized = [];
        foreach ($header as $i => $col) {
            $normalized[Str::lower($col)] = $data[$col] ?? '';
        }

        $mapped = [
            'date' => $this->parseDate($normalized),
            'description' => $this->parseDescription($normalized),
            'amount' => $this->parseAmount($normalized, $format),
            'reference' => $this->parseReference($normalized, $format),
            'balance' => $this->parseBalance($normalized),
            'branch' => $normalized['cabang'] ?? $normalized['branch'] ?? null,
            'raw' => $data,
        ];

        return $mapped;
    }

    protected function parseDate(array $row): ?string
    {
        $dateKey = null;
        foreach (['tanggal', 'date', 'tgl', 'transaction_date'] as $key) {
            if (!empty($row[$key])) {
                $dateKey = $key;
                break;
            }
        }

        if (!$dateKey) return null;

        $value = trim($row[$dateKey]);
        $value = str_replace(['"', "'"], '', $value);

        foreach (['d/m/Y', 'd-m-Y', 'Y-m-d', 'm/d/Y', 'd M Y', 'd M Y H:i:s', 'd/m/Y H:i:s'] as $fmt) {
            try {
                $date = Carbon::createFromFormat($fmt, $value);
                if ($date && $date->year > 2000) {
                    return $date->format('Y-m-d');
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        try {
            $date = Carbon::parse($value);
            if ($date->year > 2000) return $date->format('Y-m-d');
        } catch (\Exception $e) {
        }

        return null;
    }

    protected function parseDescription(array $row): string
    {
        foreach (['keterangan', 'description', 'deskripsi', 'uraian', 'narasi'] as $key) {
            if (!empty($row[$key])) {
                $desc = trim($row[$key]);
                $desc = str_replace(['"', "'"], '', $desc);
                return $desc;
            }
        }
        return '';
    }

    protected function parseAmount(array $row, string $format): float
    {
        $rawAmount = null;

        if ($format === 'bca') {
            if (!empty($row['jumlah'])) {
                $rawAmount = trim(str_replace(['"', "'"], '', $row['jumlah']));
            }
        } elseif ($format === 'mandiri' || $format === 'generic_id') {
            $debit = !empty($row['debit']) ? $this->cleanNumeric($row['debit']) : 0;
            $kredit = !empty($row['kredit']) ? $this->cleanNumeric($row['kredit']) : 0;

            if ($kredit > 0) {
                $rawAmount = $kredit * -1;
            } elseif ($debit > 0) {
                $rawAmount = $debit;
            }
        } else {
            $debit = !empty($row['debit']) ? $this->cleanNumeric($row['debit']) : 0;
            $credit = !empty($row['credit']) ? $this->cleanNumeric($row['credit']) : 0;

            if ($credit > 0) {
                $rawAmount = $credit * -1;
            } elseif ($debit > 0) {
                $rawAmount = $debit;
            }
        }

        if ($rawAmount === null) {
            foreach (['jumlah', 'amount', 'nominal', 'nilai'] as $key) {
                if (!empty($row[$key])) {
                    $rawAmount = $this->cleanNumeric(trim(str_replace(['"', "'"], '', $row[$key])));
                    break;
                }
            }
        }

        if ($rawAmount === null) {
            $debit = !empty($row['debit']) ? $this->cleanNumeric($row['debit']) : 0;
            $credit = !empty($row['credit']) ? $this->cleanNumeric($row['credit']) : 0;
            $rawAmount = $credit > 0 ? $credit * -1 : $debit;
        }

        return (float) $rawAmount;
    }

    protected function parseReference(array $row, string $format): ?string
    {
        $desc = $this->parseDescription($row);

        if (preg_match('/\b(\d{5,30})\b/', $desc, $m)) {
            return $m[1];
        }

        if ($format === 'bca' && !empty($row['cabang'])) {
            return trim($row['cabang']);
        }

        return null;
    }

    protected function parseBalance(array $row): ?float
    {
        foreach (['saldo', 'balance'] as $key) {
            if (!empty($row[$key])) {
                return $this->cleanNumeric(trim(str_replace(['"', "'"], '', $row[$key])));
            }
        }
        return null;
    }

    protected function cleanNumeric(string $value): float
    {
        $value = trim($value);
        $negative = str_starts_with($value, '-') || str_ends_with($value, '-');

        $value = preg_replace('/[^\d.,\-]/', '', $value);

        if ($value === '' || $value === '-') {
            return 0.0;
        }

        $value = ltrim($value, '+');

        if (str_contains($value, ',') && str_contains($value, '.')) {
            if (strrpos($value, ',') > strrpos($value, '.')) {
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
            } else {
                $value = str_replace(',', '', $value);
            }
        } elseif (str_contains($value, ',')) {
            $parts = explode(',', $value);
            if (count($parts) === 2 && strlen($parts[1]) === 2 && strlen($parts[0]) > 3) {
                $value = str_replace(',', '.', $value);
            } else {
                $value = str_replace(',', '', $value);
            }
        } elseif (str_contains($value, '.')) {
            $parts = explode('.', $value);
            if (count($parts) === 2 && strlen($parts[1]) === 2 && strlen($parts[0]) > 3) {
                $value = str_replace('.', '', $parts[0]) . '.' . $parts[1];
            }
        }

        $cleaned = preg_replace('/[^0-9.\-]/', '', $value);
        $amount = (float) ($cleaned ?: 0);

        return $negative ? -abs($amount) : $amount;
    }

    protected function calculateMatchScore(array $stmtRow, BankTransaction $tx, float $stmtAmount, float $txAmount, ?string $stmtDate, ?string $stmtRef, string $stmtDescription): int
    {
        $score = 0;

        if ($stmtAmount === $txAmount) {
            $score += 60;
        } elseif (abs($stmtAmount - $txAmount) / max($stmtAmount, $txAmount) < 0.05) {
            $score += 30;
        }

        $txDate = $tx->transaction_date?->format('Y-m-d');
        if ($stmtDate && $txDate) {
            $dayDiff = abs(Carbon::parse($stmtDate)->diffInDays(Carbon::parse($txDate)));
            if ($dayDiff <= 3) $score += 30;
            elseif ($dayDiff <= 7) $score += 15;
        }

        $txRef = $tx->reference_number;
        if ($stmtRef && $txRef) {
            similar_text(Str::lower($stmtRef), Str::lower($txRef), $simPct);
            if ($simPct > 80) $score += 20;
            elseif ($simPct > 50) $score += 10;
        }

        $txDesc = Str::lower($tx->description);
        $stmtDesc = Str::lower($stmtDescription);
        similar_text($stmtDesc, $txDesc, $descSim);
        if ($descSim > 70) $score += 10;
        elseif ($descSim > 40) $score += 5;

        return $score;
    }
}
