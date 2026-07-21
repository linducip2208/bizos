<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\BankReconciliation;
use App\Models\BankTransaction;
use App\Models\JournalEntry;
use App\Models\Journal;
use App\Models\ReconciliationItem;
use Illuminate\Support\Collection;

class BankReconciliationService
{
    protected int $dateTolerance = 5;

    public function __construct(protected CurrencyService $currencyService) {}

    public function autoMatch(BankReconciliation $reconciliation): array
    {
        $matchedCount = 0;
        $unmatchedJournalCount = 0;
        $unmatchedBankCount = 0;

        $bankTransactions = BankTransaction::where('bank_account_id', $reconciliation->bank_account_id)
            ->where('is_reconciled', false)
            ->whereBetween('transaction_date', [$reconciliation->period_start, $reconciliation->period_end])
            ->get();

        $journalEntries = JournalEntry::with(['journal' => function ($q) {
            $q->where('status', 'posted');
        }])->whereHas('journal', function ($q) {
            $q->where('status', 'posted');
        })->get();

        $usedJournalIds = [];
        $usedBankIds = [];

        foreach ($bankTransactions as $bankTx) {
            $matched = false;

            foreach ($journalEntries as $journalEntry) {
                if (in_array($journalEntry->id, $usedJournalIds)) {
                    continue;
                }

                $journalAmount = max($journalEntry->debit, $journalEntry->credit);
                $dateDiff = abs(strtotime($bankTx->transaction_date->toDateString()) - strtotime($journalEntry->journal->journal_date->toDateString())) / 86400;

                if (
                    abs((float) $bankTx->amount - (float) $journalAmount) < 0.01
                    && $dateDiff <= $this->dateTolerance
                ) {
                    ReconciliationItem::create([
                        'reconciliation_id' => $reconciliation->id,
                        'journal_entry_id' => $journalEntry->id,
                        'bank_transaction_id' => $bankTx->id,
                        'matched_amount' => $bankTx->amount,
                        'type' => 'matched',
                    ]);

                    $bankTx->update(['is_reconciled' => true, 'reconciliation_id' => $reconciliation->id]);

                    $usedJournalIds[] = $journalEntry->id;
                    $usedBankIds[] = $bankTx->id;
                    $matchedCount++;
                    $matched = true;
                    break;
                }
            }

            if (!$matched) {
                ReconciliationItem::create([
                    'reconciliation_id' => $reconciliation->id,
                    'bank_transaction_id' => $bankTx->id,
                    'matched_amount' => $bankTx->amount,
                    'type' => 'unmatched_bank',
                    'notes' => 'Transaksi bank belum tercatat di jurnal',
                ]);

                $unmatchedBankCount++;
            }
        }

        foreach ($journalEntries as $journalEntry) {
            if (!in_array($journalEntry->id, $usedJournalIds) && $journalEntry->journal) {
                $journalAmount = max($journalEntry->debit, $journalEntry->credit);

                ReconciliationItem::create([
                    'reconciliation_id' => $reconciliation->id,
                    'journal_entry_id' => $journalEntry->id,
                    'matched_amount' => $journalAmount,
                    'type' => 'unmatched_journal',
                    'notes' => 'Jurnal belum tercatat di bank',
                ]);

                $unmatchedJournalCount++;
            }
        }

        $this->calculateDifference($reconciliation);

        return [
            'matched' => $matchedCount,
            'unmatched_journal' => $unmatchedJournalCount,
            'unmatched_bank' => $unmatchedBankCount,
        ];
    }

    public function calculateDifference(BankReconciliation $reconciliation): void
    {
        $bankCredits = BankTransaction::where('bank_account_id', $reconciliation->bank_account_id)
            ->where('is_reconciled', true)
            ->where('reconciliation_id', $reconciliation->id)
            ->where('transaction_type', 'credit')
            ->sum('amount');

        $bankDebits = BankTransaction::where('bank_account_id', $reconciliation->bank_account_id)
            ->where('is_reconciled', true)
            ->where('reconciliation_id', $reconciliation->id)
            ->where('transaction_type', 'debit')
            ->sum('amount');

        $reconciliation->closing_balance = $reconciliation->opening_balance + $bankCredits - $bankDebits;
        $reconciliation->calculateDifference();
        $reconciliation->save();
    }

    public function completeReconciliation(BankReconciliation $reconciliation, ?string $adjustmentCoaDebit = null, ?string $adjustmentCoaCredit = null): void
    {
        if ((float) $reconciliation->difference !== 0.0) {
            $adjustmentAmount = abs((float) $reconciliation->difference);
            $isPositive = (float) $reconciliation->difference > 0;

            $journal = Journal::create([
                'company_id' => $reconciliation->company_id,
                'journal_number' => 'ADJ-RECON-' . $reconciliation->id,
                'journal_date' => $reconciliation->period_end,
                'journal_type' => 'adjustment',
                'description' => 'Penyesuaian rekonsiliasi bank ' . $reconciliation->bankAccount?->bank_name . ' periode ' . $reconciliation->period_start->format('d/m/Y') . ' - ' . $reconciliation->period_end->format('d/m/Y'),
                'total_debit' => $isPositive ? 0 : $adjustmentAmount,
                'total_credit' => $isPositive ? $adjustmentAmount : 0,
                'status' => 'posted',
                'posted_at' => now(),
                'posted_by' => auth()->id(),
            ]);

            $bankAccount = $reconciliation->bankAccount;
            $coaName = $bankAccount?->bank_name . ' - ' . $bankAccount?->account_number;
            $cashCoa = \App\Models\Coa::where('name', 'like', '%' . $coaName . '%')
                ->orWhere('code', 'like', '1-1%')
                ->first();

            if ($cashCoa) {
                JournalEntry::create([
                    'journal_id' => $journal->id,
                    'coa_id' => $cashCoa->id,
                    'description' => 'Penyesuaian rekonsiliasi',
                    'debit' => $isPositive ? $adjustmentAmount : 0,
                    'credit' => $isPositive ? 0 : $adjustmentAmount,
                ]);
            }

            if ($adjustmentCoaDebit) {
                $adjCoa = \App\Models\Coa::where('code', $adjustmentCoaDebit)->orWhere('id', $adjustmentCoaDebit)->first();
                if ($adjCoa) {
                    JournalEntry::create([
                        'journal_id' => $journal->id,
                        'coa_id' => $adjCoa->id,
                        'description' => 'Penyesuaian rekonsiliasi',
                        'debit' => $isPositive ? 0 : $adjustmentAmount,
                        'credit' => $isPositive ? $adjustmentAmount : 0,
                    ]);
                }
            }

            ReconciliationItem::create([
                'reconciliation_id' => $reconciliation->id,
                'journal_entry_id' => $journal->journalEntries()->first()?->id,
                'matched_amount' => $adjustmentAmount,
                'type' => 'adjustment',
                'notes' => 'Jurnal penyesuaian otomatis selisih rekonsiliasi',
            ]);
        }

        $reconciliation->userCompleted();
    }

    public function getUnreconciledTransactions(int $bankAccountId, ?string $fromDate = null, ?string $toDate = null): Collection
    {
        $query = BankTransaction::where('bank_account_id', $bankAccountId)
            ->where('is_reconciled', false);

        if ($fromDate) {
            $query->where('transaction_date', '>=', $fromDate);
        }

        if ($toDate) {
            $query->where('transaction_date', '<=', $toDate);
        }

        return $query->orderBy('transaction_date')->get();
    }
}
