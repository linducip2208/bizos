<?php

namespace App\Services;

use App\Models\Deal;
use App\Models\DealCommission;
use App\Models\CommissionSlab;
use App\Models\TeamTarget;
use Illuminate\Support\Facades\DB;

class CommissionService
{
    public function calculateCommission(Deal $deal): array
    {
        $expectedValue = (float) $deal->expected_value;
        $slabs = CommissionSlab::where('company_id', $deal->company_id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $ratePercent = $this->findSlabRate($expectedValue, $slabs);

        $employeeId = $deal->assigned_to;
        $commissionAmount = round($expectedValue * ($ratePercent / 100), 2);

        $existingCommissions = DealCommission::where('deal_id', $deal->id)->get();
        $splits = [];

        if ($existingCommissions->isNotEmpty()) {
            foreach ($existingCommissions as $ec) {
                $splits[] = [
                    'employee_id' => $ec->employee_id,
                    'split_percent' => $ec->split_percent,
                    'amount' => round($commissionAmount * ($ec->split_percent / 100), 2),
                ];
            }
        } elseif ($employeeId) {
            $splits[] = [
                'employee_id' => $employeeId,
                'split_percent' => 100,
                'amount' => $commissionAmount,
            ];
        }

        return [
            'deal_id' => $deal->id,
            'expected_value' => $expectedValue,
            'rate_percent' => $ratePercent,
            'total_commission' => $commissionAmount,
            'splits' => $splits,
            'slab_details' => $this->getSlabDetail($expectedValue, $slabs),
        ];
    }

    public function saveCommissions(Deal $deal): void
    {
        $calc = $this->calculateCommission($deal);

        DB::transaction(function () use ($deal, $calc) {
            DealCommission::where('deal_id', $deal->id)->delete();

            foreach ($calc['splits'] as $split) {
                if ($split['amount'] > 0) {
                    DealCommission::create([
                        'deal_id' => $deal->id,
                        'employee_id' => $split['employee_id'],
                        'commission_amount' => $split['amount'],
                        'rate_percent' => $calc['rate_percent'],
                        'split_percent' => $split['split_percent'],
                        'status' => 'pending',
                    ]);
                }
            }
        });
    }

    public function calculateSplitCommission(Deal $deal, array $employeeSplits): array
    {
        $calc = $this->calculateCommission($deal);
        $totalCommission = $calc['total_commission'];

        $result = [];
        foreach ($employeeSplits as $split) {
            $amount = round($totalCommission * ($split['split_percent'] / 100), 2);
            $result[] = [
                'employee_id' => $split['employee_id'],
                'split_percent' => $split['split_percent'],
                'amount' => $amount,
                'rate_percent' => $calc['rate_percent'],
            ];
        }

        return $result;
    }

    public function checkTeamTarget(int $companyId, ?int $departmentId, float $periodRevenue): ?float
    {
        $target = TeamTarget::where('company_id', $companyId)
            ->where('is_active', true)
            ->when($departmentId, fn($q) => $q->where('department_id', $departmentId))
            ->where('period_start', '<=', now()->toDateString())
            ->where('period_end', '>=', now()->toDateString())
            ->first();

        if ($target && $periodRevenue >= $target->target_amount) {
            return $target->bonus_amount;
        }

        return null;
    }

    public function generateReport(int $companyId, string $periodStart, string $periodEnd): array
    {
        $commissions = DealCommission::whereHas('deal', function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->with(['employee', 'deal'])
            ->get();

        $report = [];
        $totals = ['pending' => 0, 'approved' => 0, 'paid' => 0];

        foreach ($commissions as $commission) {
            $employeeId = $commission->employee_id;
            if (!isset($report[$employeeId])) {
                $report[$employeeId] = [
                    'employee_name' => $commission->employee?->first_name . ' ' . $commission->employee?->last_name,
                    'employee_code' => $commission->employee?->employee_code,
                    'total_pending' => 0,
                    'total_approved' => 0,
                    'total_paid' => 0,
                    'deals' => [],
                ];
            }

            $report[$employeeId]['total_' . $commission->status] += (float) $commission->commission_amount;
            $totals[$commission->status] += (float) $commission->commission_amount;

            $report[$employeeId]['deals'][] = [
                'deal_title' => $commission->deal?->title,
                'deal_value' => $commission->deal?->expected_value,
                'commission' => $commission->commission_amount,
                'rate' => $commission->rate_percent,
                'status' => $commission->status,
            ];
        }

        return [
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'summary' => $totals,
            'total' => array_sum($totals),
            'employees' => array_values($report),
        ];
    }

    public function approveCommission(int $dealCommissionId): void
    {
        DealCommission::where('id', $dealCommissionId)
            ->where('status', 'pending')
            ->update(['status' => 'approved']);
    }

    public function markAsPaid(array $commissionIds): void
    {
        DealCommission::whereIn('id', $commissionIds)
            ->where('status', 'approved')
            ->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);
    }

    protected function findSlabRate(float $amount, $slabs): float
    {
        foreach ($slabs as $slab) {
            $min = (float) $slab->min_amount;
            $max = $slab->max_amount ? (float) $slab->max_amount : PHP_FLOAT_MAX;

            if ($amount >= $min && $amount < $max) {
                return (float) $slab->rate_percent;
            }
        }

        return 0;
    }

    protected function getSlabDetail(float $amount, $slabs): array
    {
        $details = [];
        $remaining = $amount;
        $totalCommission = 0;

        foreach ($slabs as $slab) {
            $min = (float) $slab->min_amount;
            $max = $slab->max_amount ? (float) $slab->max_amount : PHP_FLOAT_MAX;
            $rangeSize = $max - $min;

            if ($remaining <= 0) break;

            $applicableAmount = min($remaining, $rangeSize);
            if ($applicableAmount > 0) {
                $commission = round($applicableAmount * ((float) $slab->rate_percent / 100), 2);
                $details[] = [
                    'slab' => 'Rp ' . number_format($min, 0, ',', '.') . ($slab->max_amount ? ' - Rp ' . number_format($max, 0, ',', '.') : '+'),
                    'rate' => (float) $slab->rate_percent,
                    'applicable_amount' => $applicableAmount,
                    'commission' => $commission,
                ];
                $totalCommission += $commission;
                $remaining -= $applicableAmount;
            }
        }

        return $details;
    }
}
