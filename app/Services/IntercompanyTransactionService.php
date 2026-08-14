<?php

namespace App\Services;

use App\Models\Coa;
use App\Models\CoaCategory;
use App\Models\IntercompanyTransaction;
use App\Models\Journal;
use App\Models\JournalEntry;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IntercompanyTransactionService
{
    const COA_INTERCO_RECEIVABLE = '1-1300';
    const COA_INTERCO_PAYABLE = '2-2100';
    const CATEGORY_INTERCO_RECEIVABLE = 'IC-RECV';
    const CATEGORY_INTERCO_PAYABLE = 'IC-PAY';
    const JOURNAL_PREFIX = 'ICO';

    public function processApproval(IntercompanyTransaction $transaction): void
    {
        if ($transaction->status === 'completed') {
            return;
        }

        if ($transaction->status !== 'approved') {
            throw new \RuntimeException('Transaksi antar perusahaan harus berstatus "approved" untuk diproses jurnal.');
        }

        if ($transaction->journal_entry_id_from && $transaction->journal_entry_id_to) {
            return;
        }

        $amount = (float) $transaction->amount;
        $exchangeRate = (float) ($transaction->exchange_rate ?? 1);

        $coaReceivableFrom = $this->findOrCreateIntercompanyReceivableCoa($transaction->from_company_id);
        $coaPayableTo = $this->findOrCreateIntercompanyPayableCoa($transaction->to_company_id);

        $creditCoaCode = $this->getCreditCoaCode($transaction->transaction_type);
        $debitCoaCode = $this->getDebitCoaCode($transaction->transaction_type);

        $coaCredit = $this->findOrCreateFallbackCoa($transaction->from_company_id, $creditCoaCode);
        $coaDebit = $this->findOrCreateFallbackCoa($transaction->to_company_id, $debitCoaCode);

        DB::transaction(function () use (
            $transaction, $amount, $exchangeRate,
            $coaReceivableFrom, $coaPayableTo, $coaCredit, $coaDebit
        ) {
            $fromJournal = $this->createFromCompanyJournal($transaction, $amount, $exchangeRate, $coaReceivableFrom, $coaCredit);
            $toJournal = $this->createToCompanyJournal($transaction, $amount, $exchangeRate, $coaDebit, $coaPayableTo);

            $transaction->update([
                'status' => 'completed',
                'journal_entry_id_from' => $fromJournal->journalEntries->first()?->id,
                'journal_entry_id_to' => $toJournal->journalEntries->first()?->id,
            ]);

            Log::info('Intercompany transaction journal posted.', [
                'transaction_id' => $transaction->id,
                'reference' => $transaction->reference_number,
                'from_journal_id' => $fromJournal->id,
                'to_journal_id' => $toJournal->id,
                'amount' => $amount,
            ]);
        });
    }

    public function createEliminationEntry(IntercompanyTransaction $transaction): void
    {
        if ($transaction->status !== 'completed') {
            throw new \RuntimeException('Transaksi antar perusahaan harus berstatus "completed" untuk membuat jurnal eliminasi.');
        }

        $amount = (float) $transaction->amount;

        DB::transaction(function () use ($transaction, $amount) {
            $coaReceivable = $this->findCoa($transaction->from_company_id, self::COA_INTERCO_RECEIVABLE);
            $coaPayable = $this->findCoa($transaction->to_company_id, self::COA_INTERCO_PAYABLE);

            $journalNumber = $this->generateJournalNumber($transaction->company_id, 'ELM');

            $journal = Journal::create([
                'company_id' => $transaction->company_id,
                'journal_number' => $journalNumber,
                'journal_date' => $transaction->transaction_date->format('Y-m-d'),
                'journal_type' => 'adjustment',
                'description' => "Jurnal Eliminasi Transaksi Antar Perusahaan - {$transaction->reference_number}",
                'total_debit' => $amount,
                'total_credit' => $amount,
                'reference_type' => IntercompanyTransaction::class,
                'reference_id' => $transaction->id,
                'status' => 'posted',
                'posted_by' => auth()->id(),
                'posted_at' => now(),
                'currency_id' => $transaction->currency_id,
                'exchange_rate' => $transaction->exchange_rate,
            ]);

            $entries = [
                [
                    'journal_id' => $journal->id,
                    'coa_id' => $coaPayable->id,
                    'description' => "Eliminasi Utang Antar Perusahaan - {$transaction->reference_number}",
                    'debit' => $amount,
                    'credit' => 0,
                    'currency_id' => $transaction->currency_id,
                    'exchange_rate' => $transaction->exchange_rate,
                ],
                [
                    'journal_id' => $journal->id,
                    'coa_id' => $coaReceivable->id,
                    'description' => "Eliminasi Piutang Antar Perusahaan - {$transaction->reference_number}",
                    'debit' => 0,
                    'credit' => $amount,
                    'currency_id' => $transaction->currency_id,
                    'exchange_rate' => $transaction->exchange_rate,
                ],
            ];

            JournalEntry::insert($entries);

            Log::info('Intercompany elimination journal posted.', [
                'transaction_id' => $transaction->id,
                'elimination_journal_id' => $journal->id,
            ]);
        });
    }

    public function getIntercompanyBalance(int $companyId, ?int $counterpartyId = null): array
    {
        $receivable = $this->calculateBalance($companyId, self::COA_INTERCO_RECEIVABLE, $counterpartyId);
        $payable = $this->calculateBalance($companyId, self::COA_INTERCO_PAYABLE, $counterpartyId);

        return [
            'company_id' => $companyId,
            'counterparty_id' => $counterpartyId,
            'total_receivable' => $receivable,
            'total_payable' => $payable,
            'net_position' => $receivable - $payable,
            'position_label' => ($receivable - $payable) >= 0 ? 'Piutang Bersih' : 'Utang Bersih',
        ];
    }

    // ─── JOURNAL CREATION ───────────────────────────────────────────────

    protected function createFromCompanyJournal(
        IntercompanyTransaction $transaction,
        float $amount,
        float $exchangeRate,
        Coa $coaReceivable,
        Coa $coaCredit
    ): Journal {
        $companyId = $transaction->from_company_id;
        $journalNumber = $this->generateJournalNumber($companyId, self::JOURNAL_PREFIX);

        $fromName = $transaction->fromCompany?->name ?? 'Perusahaan A';
        $toName = $transaction->toCompany?->name ?? 'Perusahaan B';

        $journal = Journal::create([
            'company_id' => $companyId,
            'journal_number' => $journalNumber,
            'journal_date' => $transaction->transaction_date->format('Y-m-d'),
            'journal_type' => 'general',
            'description' => "Transaksi Antar Perusahaan ({$fromName} → {$toName}) - {$transaction->reference_number}",
            'total_debit' => $amount,
            'total_credit' => $amount,
            'reference_type' => IntercompanyTransaction::class,
            'reference_id' => $transaction->id,
            'status' => 'posted',
            'posted_by' => auth()->id(),
            'posted_at' => now(),
            'currency_id' => $transaction->currency_id,
            'exchange_rate' => $exchangeRate,
        ]);

        $transactionTypeLabel = $this->transactionTypeLabel($transaction->transaction_type);

        $entries = [
            [
                'journal_id' => $journal->id,
                'coa_id' => $coaReceivable->id,
                'description' => "Piutang Antar Perusahaan - {$toName} ({$transactionTypeLabel})",
                'debit' => $amount,
                'credit' => 0,
                'currency_id' => $transaction->currency_id,
                'exchange_rate' => $exchangeRate,
            ],
            [
                'journal_id' => $journal->id,
                'coa_id' => $coaCredit->id,
                'description' => "{$transactionTypeLabel} Antar Perusahaan - {$transaction->reference_number}",
                'debit' => 0,
                'credit' => $amount,
                'currency_id' => $transaction->currency_id,
                'exchange_rate' => $exchangeRate,
            ],
        ];

        JournalEntry::insert($entries);

        return $journal->fresh(['journalEntries']);
    }

    protected function createToCompanyJournal(
        IntercompanyTransaction $transaction,
        float $amount,
        float $exchangeRate,
        Coa $coaDebit,
        Coa $coaPayable
    ): Journal {
        $companyId = $transaction->to_company_id;
        $journalNumber = $this->generateJournalNumber($companyId, self::JOURNAL_PREFIX);

        $fromName = $transaction->fromCompany?->name ?? 'Perusahaan A';
        $toName = $transaction->toCompany?->name ?? 'Perusahaan B';

        $journal = Journal::create([
            'company_id' => $companyId,
            'journal_number' => $journalNumber,
            'journal_date' => $transaction->transaction_date->format('Y-m-d'),
            'journal_type' => 'general',
            'description' => "Transaksi Antar Perusahaan ({$fromName} → {$toName}) - {$transaction->reference_number}",
            'total_debit' => $amount,
            'total_credit' => $amount,
            'reference_type' => IntercompanyTransaction::class,
            'reference_id' => $transaction->id,
            'status' => 'posted',
            'posted_by' => auth()->id(),
            'posted_at' => now(),
            'currency_id' => $transaction->currency_id,
            'exchange_rate' => $exchangeRate,
        ]);

        $transactionTypeLabel = $this->transactionTypeLabel($transaction->transaction_type);

        $entries = [
            [
                'journal_id' => $journal->id,
                'coa_id' => $coaDebit->id,
                'description' => "{$transactionTypeLabel} Antar Perusahaan - {$transaction->reference_number}",
                'debit' => $amount,
                'credit' => 0,
                'currency_id' => $transaction->currency_id,
                'exchange_rate' => $exchangeRate,
            ],
            [
                'journal_id' => $journal->id,
                'coa_id' => $coaPayable->id,
                'description' => "Utang Antar Perusahaan - {$fromName} ({$transactionTypeLabel})",
                'debit' => 0,
                'credit' => $amount,
                'currency_id' => $transaction->currency_id,
                'exchange_rate' => $exchangeRate,
            ],
        ];

        JournalEntry::insert($entries);

        return $journal->fresh(['journalEntries']);
    }

    // ─── COA HELPERS ────────────────────────────────────────────────────

    protected function findOrCreateIntercompanyReceivableCoa(int $companyId): Coa
    {
        return $this->findOrCreateCoa(
            $companyId,
            self::COA_INTERCO_RECEIVABLE,
            'Piutang Antar Perusahaan',
            self::CATEGORY_INTERCO_RECEIVABLE,
            'Piutang Antar Perusahaan',
            'debit'
        );
    }

    protected function findOrCreateIntercompanyPayableCoa(int $companyId): Coa
    {
        return $this->findOrCreateCoa(
            $companyId,
            self::COA_INTERCO_PAYABLE,
            'Utang Antar Perusahaan',
            self::CATEGORY_INTERCO_PAYABLE,
            'Utang Antar Perusahaan',
            'kredit'
        );
    }

    protected function findOrCreateFallbackCoa(int $companyId, string $code): Coa
    {
        $name = match ($code) {
            '4-1000' => 'Pendapatan Antar Perusahaan',
            '5-1000' => 'Beban Antar Perusahaan',
            '1-1200' => 'Kas/Bank Antar Perusahaan',
            '2-2100' => 'Utang Antar Perusahaan',
            default => 'Akun Antar Perusahaan',
        };

        $balanceType = in_array($code[0], ['1', '5']) ? 'debit' : 'kredit';

        $categoryCode = 'IC-' . $code;
        $categoryName = match (substr($code, 0, 1)) {
            '1' => 'Aset Antar Perusahaan',
            '2' => 'Kewajiban Antar Perusahaan',
            '4' => 'Pendapatan Antar Perusahaan',
            '5' => 'Beban Antar Perusahaan',
            default => 'Akun Antar Perusahaan',
        };

        return $this->findOrCreateCoa($companyId, $code, $name, $categoryCode, $categoryName, $balanceType);
    }

    protected function findOrCreateCoa(
        int $companyId,
        string $code,
        string $name,
        string $categoryCode,
        string $categoryName,
        string $normalBalance
    ): Coa {
        $coa = Coa::where('company_id', $companyId)
            ->where('code', $code)
            ->where('is_active', true)
            ->first();

        if ($coa) {
            return $coa;
        }

        $category = CoaCategory::firstOrCreate(
            ['company_id' => $companyId, 'code' => $categoryCode],
            ['name' => $categoryName, 'normal_balance' => $normalBalance, 'is_active' => true]
        );

        return Coa::create([
            'company_id' => $companyId,
            'category_id' => $category->id,
            'code' => $code,
            'name' => $name,
            'description' => $name,
            'balance_type' => $normalBalance,
            'is_header' => false,
            'opening_balance' => 0,
            'is_active' => true,
        ]);
    }

    protected function findCoa(int $companyId, string $code): Coa
    {
        $coa = Coa::where('company_id', $companyId)
            ->where('code', $code)
            ->where('is_active', true)
            ->first();

        if (!$coa) {
            throw new \RuntimeException("COA dengan kode '{$code}' tidak ditemukan untuk company_id={$companyId}. Pastikan Chart of Account sudah disiapkan.");
        }

        return $coa;
    }

    // ─── TRANSACTION TYPE MAPPING ───────────────────────────────────────

    protected function getCreditCoaCode(string $transactionType): string
    {
        return match ($transactionType) {
            'sale' => '4-1000',
            'purchase' => '4-1000',
            'transfer' => '1-1200',
            'payment' => '1-1200',
            'expense_allocation' => '4-1000',
            default => '4-1000',
        };
    }

    protected function getDebitCoaCode(string $transactionType): string
    {
        return match ($transactionType) {
            'sale' => '5-1000',
            'purchase' => '5-1000',
            'transfer' => '1-1200',
            'payment' => '2-2100',
            'expense_allocation' => '5-1000',
            default => '5-1000',
        };
    }

    protected function transactionTypeLabel(string $type): string
    {
        return match ($type) {
            'sale' => 'Penjualan',
            'purchase' => 'Pembelian',
            'transfer' => 'Transfer',
            'payment' => 'Pembayaran',
            'expense_allocation' => 'Alokasi Biaya',
            default => ucfirst($type),
        };
    }

    // ─── JOURNAL NUMBER GENERATOR ───────────────────────────────────────

    protected function generateJournalNumber(int $companyId, string $prefix): string
    {
        $date = Carbon::now()->format('Ym');
        $last = Journal::where('company_id', $companyId)
            ->where('journal_number', 'like', "JRN-{$prefix}-{$date}-%")
            ->orderBy('journal_number', 'desc')
            ->first();

        $seq = 1;
        if ($last && preg_match('/-(\d{5})$/', $last->journal_number, $m)) {
            $seq = (int) $m[1] + 1;
        }

        return sprintf("JRN-{$prefix}-{$date}-%05d", $seq);
    }

    // ─── BALANCE CALCULATION ────────────────────────────────────────────

    protected function calculateBalance(int $companyId, string $coaCode, ?int $counterpartyId = null): float
    {
        $coa = Coa::where('company_id', $companyId)
            ->where('code', $coaCode)
            ->where('is_active', true)
            ->first();

        if (!$coa) {
            return 0;
        }

        $query = JournalEntry::where('coa_id', $coa->id)
            ->whereHas('journal', function ($q) use ($companyId) {
                $q->where('company_id', $companyId)
                    ->where('status', 'posted');
            });

        if ($counterpartyId) {
            $query->whereHas('journal', function ($q) use ($counterpartyId) {
                $q->whereHas('reference', function ($rq) use ($counterpartyId) {
                    $rq->where(function ($sq) use ($counterpartyId) {
                        $sq->where('from_company_id', $counterpartyId)
                            ->orWhere('to_company_id', $counterpartyId);
                    });
                });
            });
        }

        $totalDebit = (float) (clone $query)->sum('debit');
        $totalCredit = (float) (clone $query)->sum('credit');

        if ($coa->balance_type === 'debit') {
            return $totalDebit - $totalCredit;
        }

        return $totalCredit - $totalDebit;
    }
}
