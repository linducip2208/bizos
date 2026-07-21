<?php

namespace App\Services;

use App\Models\TenancyContract;
use App\Models\ServiceChargeInvoice;
use App\Models\MaintenanceRequest;
use App\Models\PropertyUnit;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PropertyService
{
    public function generateMonthlyInvoices(int $companyId): array
    {
        $now = now();
        $periodStart = $now->copy()->startOfMonth();
        $periodEnd = $now->copy()->endOfMonth();

        $activeContracts = TenancyContract::where('company_id', $companyId)
            ->where('status', 'active')
            ->with('propertyUnit')
            ->get();

        $generated = [];

        foreach ($activeContracts as $contract) {
            $dueDate = $now->copy()->setDay(min($contract->payment_due_day, $now->daysInMonth));

            $exists = ServiceChargeInvoice::where('tenancy_contract_id', $contract->id)
                ->where('period_start', $periodStart->format('Y-m-d'))
                ->exists();

            if ($exists) {
                continue;
            }

            $totalAmount = $contract->monthly_rent
                + $contract->service_charge_monthly
                + $contract->sinking_fund_monthly;

            $invoice = ServiceChargeInvoice::create([
                'company_id' => $companyId,
                'property_unit_id' => $contract->property_unit_id,
                'tenancy_contract_id' => $contract->id,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'invoice_number' => 'SCI-' . date('Ym') . '-' . str_pad($contract->id, 5, '0', STR_PAD_LEFT),
                'rent_amount' => $contract->monthly_rent,
                'service_charge' => $contract->service_charge_monthly,
                'sinking_fund' => $contract->sinking_fund_monthly,
                'electricity' => 0,
                'water' => 0,
                'other_charges' => 0,
                'total_amount' => $totalAmount,
                'due_date' => $dueDate,
                'status' => 'unpaid',
            ]);

            $generated[] = $invoice;
        }

        return $generated;
    }

    public function calculateLateFee(ServiceChargeInvoice $invoice): float
    {
        if ($invoice->status === 'paid') {
            return 0;
        }

        $dueDate = Carbon::parse($invoice->due_date);
        if (!$dueDate->isPast()) {
            return 0;
        }

        $contract = $invoice->tenancyContract;
        $daysLate = $dueDate->diffInDays(now());
        $lateFeePercent = $contract->late_fee_percent ?? 5;

        return round(($invoice->total_amount * $lateFeePercent / 100) * ceil($daysLate / 30), 2);
    }

    public function assignMaintenance(MaintenanceRequest $request, int $employeeId): void
    {
        $request->update([
            'assigned_to' => $employeeId,
            'status' => 'assigned',
        ]);
    }

    public function completeMaintenance(MaintenanceRequest $request, float $cost): void
    {
        $request->update([
            'status' => 'completed',
            'completed_at' => now(),
            'cost' => $cost,
        ]);
    }

    public function getExpiringContracts(int $days = 90): Collection
    {
        $expiryDate = now()->addDays($days);

        return TenancyContract::where('status', 'active')
            ->where('end_date', '<=', $expiryDate)
            ->where('end_date', '>=', now())
            ->with(['propertyUnit', 'client'])
            ->orderBy('end_date')
            ->get();
    }

    public function getOccupancyRate(int $companyId): array
    {
        $totalUnits = PropertyUnit::where('company_id', $companyId)->count();
        $occupiedUnits = TenancyContract::where('company_id', $companyId)
            ->where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->distinct('property_unit_id')
            ->count();

        $vacantUnits = $totalUnits - $occupiedUnits;

        $byType = PropertyUnit::where('company_id', $companyId)
            ->get()
            ->groupBy('property_type')
            ->map(function ($units, $type) {
                $total = $units->count();
                $occupied = TenancyContract::whereIn('property_unit_id', $units->pluck('id'))
                    ->where('status', 'active')
                    ->where('start_date', '<=', now())
                    ->where('end_date', '>=', now())
                    ->distinct('property_unit_id')
                    ->count();

                return [
                    'type' => $type,
                    'total' => $total,
                    'occupied' => $occupied,
                    'vacant' => $total - $occupied,
                    'rate' => $total > 0 ? round(($occupied / $total) * 100, 1) : 0,
                ];
            })
            ->values()
            ->toArray();

        return [
            'total_units' => $totalUnits,
            'occupied_units' => $occupiedUnits,
            'vacant_units' => $vacantUnits,
            'occupancy_rate' => $totalUnits > 0 ? round(($occupiedUnits / $totalUnits) * 100, 1) : 0,
            'by_type' => $byType,
        ];
    }

    public function getRevenuePerUnit(int $companyId, string $period = 'monthly'): array
    {
        $query = ServiceChargeInvoice::where('company_id', $companyId)
            ->where('status', 'paid');

        if ($period === 'monthly') {
            $query->whereMonth('period_start', now()->month)
                ->whereYear('period_start', now()->year);
        } elseif ($period === 'yearly') {
            $query->whereYear('period_start', now()->year);
        }

        $data = $query->with('propertyUnit')
            ->get()
            ->groupBy('property_unit_id')
            ->map(function ($invoices) {
                $first = $invoices->first();
                return [
                    'unit_number' => $first->propertyUnit?->unit_number ?? '-',
                    'building_name' => $first->propertyUnit?->building_name ?? '-',
                    'total_revenue' => $invoices->sum('total_amount'),
                    'invoice_count' => $invoices->count(),
                ];
            })
            ->values()
            ->toArray();

        $totalRevenue = array_sum(array_column($data, 'total_revenue'));

        return [
            'period' => $period,
            'total_revenue' => $totalRevenue,
            'unit_count' => count($data),
            'data' => $data,
        ];
    }
}
