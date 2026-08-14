<?php

namespace App\Services;

use App\Models\CashDenomination;
use App\Models\CashierShift;
use App\Models\PosPayment;
use App\Models\PosTransaction;
use App\Models\ShiftDenomination;
use Illuminate\Support\Facades\DB;

class CashDenominationService
{
    protected const DEFAULT_DENOMINATIONS = [
        [100000, 'Rp 100.000'],
        [50000, 'Rp 50.000'],
        [20000, 'Rp 20.000'],
        [10000, 'Rp 10.000'],
        [5000, 'Rp 5.000'],
        [2000, 'Rp 2.000'],
        [1000, 'Rp 1.000'],
        [500, 'Rp 500'],
        [200, 'Rp 200'],
        [100, 'Rp 100'],
    ];

    /**
     * Buat denominasi default Rupiah untuk perusahaan (dan opsional cabang).
     */
    public function seedDefaultDenominations(int $companyId, int $branchId = null): void
    {
        foreach (self::DEFAULT_DENOMINATIONS as $index => [$value, $label]) {
            CashDenomination::firstOrCreate(
                [
                    'company_id' => $companyId,
                    'branch_id' => $branchId,
                    'value' => $value,
                ],
                [
                    'label' => $label,
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ],
            );
        }
    }

    /**
     * Simpan hitungan pecahan uang saat tutup shift.
     * $counts menerima map [denomination_id => count] atau list
     * [['denomination_id' => 1, 'count' => 5], ...].
     */
    public function recordDenominations(int $shiftId, array $counts): void
    {
        CashierShift::findOrFail($shiftId);

        DB::transaction(function () use ($shiftId, $counts) {
            ShiftDenomination::where('cashier_shift_id', $shiftId)->delete();

            foreach ($this->normalizeCounts($counts) as $denominationId => $count) {
                $count = (int) $count;

                if ($count <= 0) {
                    continue;
                }

                $denomination = CashDenomination::find($denominationId);

                if (!$denomination) {
                    continue;
                }

                ShiftDenomination::create([
                    'cashier_shift_id' => $shiftId,
                    'denomination_id' => $denomination->id,
                    'count' => $count,
                    'subtotal' => round($count * (float) $denomination->value, 2),
                ]);
            }
        });
    }

    /**
     * Total pembayaran tunai pada shift (kas yang diharapkan).
     */
    public function calculateExpectedCash(int $shiftId): float
    {
        $transactionIds = PosTransaction::withoutGlobalScopes()
            ->where('shift_id', $shiftId)
            ->pluck('id');

        return round((float) PosPayment::whereIn('transaction_id', $transactionIds)
            ->where('payment_method', 'cash')
            ->sum('amount'), 2);
    }

    /**
     * Total kas aktual dari hitungan pecahan (jumlah × nilai).
     */
    public function calculateActualCash(int $shiftId): float
    {
        $shift = CashierShift::with('shiftDenominations.denomination')->find($shiftId);

        if (!$shift) {
            return 0.0;
        }

        return round((float) $shift->shiftDenominations->sum(function ($line) {
            return (int) ($line->count ?? 0) * (float) ($line->denomination?->value ?? 0);
        }), 2);
    }

    /**
     * Selisih kas: expected vs actual + breakdown.
     */
    public function calculateDifference(int $shiftId): array
    {
        $expected = $this->calculateExpectedCash($shiftId);
        $actual = $this->calculateActualCash($shiftId);

        return [
            'expected' => $expected,
            'actual' => $actual,
            'difference' => round($actual - $expected, 2),
            'breakdown' => $this->getBreakdown($shiftId),
        ];
    }

    /**
     * Rincian pecahan uang per shift.
     */
    public function getBreakdown(int $shiftId): array
    {
        $shift = CashierShift::with('shiftDenominations.denomination')->find($shiftId);

        if (!$shift) {
            return [];
        }

        return $shift->shiftDenominations
            ->sortBy(fn ($line) => $line->denomination?->sort_order ?? 0)
            ->map(fn ($line) => [
                'denomination_id' => $line->denomination_id,
                'label' => $line->denomination?->label,
                'value' => (float) ($line->denomination?->value ?? 0),
                'count' => (int) ($line->count ?? 0),
                'subtotal' => round((int) ($line->count ?? 0) * (float) ($line->denomination?->value ?? 0), 2),
            ])
            ->values()
            ->toArray();
    }

    protected function normalizeCounts(array $counts): array
    {
        if (empty($counts)) {
            return [];
        }

        if (array_is_list($counts)) {
            $normalized = [];

            foreach ($counts as $item) {
                $denominationId = $item['denomination_id'] ?? $item['id'] ?? null;

                if ($denominationId === null) {
                    continue;
                }

                $denominationId = (int) $denominationId;
                $normalized[$denominationId] = (int) ($normalized[$denominationId] ?? 0) + (int) ($item['count'] ?? 0);
            }

            return $normalized;
        }

        return array_map('intval', $counts);
    }
}
