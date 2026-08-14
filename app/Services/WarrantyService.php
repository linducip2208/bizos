<?php

namespace App\Services;

use App\Models\SerialNumber;
use App\Models\Warranty;
use App\Models\WarrantyClaim;
use App\Models\WarrantyRegistration;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class WarrantyService
{
    /**
     * Resolve company id aktif (dari session atau user).
     */
    public function resolveCompanyId(): int
    {
        $companyId = session('current_company_id') ?? auth()->user()?->company_id;

        if (!$companyId) {
            throw new \RuntimeException('Perusahaan tidak ditemukan untuk user aktif.');
        }

        return (int) $companyId;
    }

    /**
     * Generate nomor klaim garansi: WC-YYYYMMDD-XXXX
     */
    public function generateClaimNumber(?int $companyId = null): string
    {
        $companyId = $companyId ?: $this->resolveCompanyId();
        $prefix = 'WC-' . date('Ymd');

        $last = WarrantyClaim::withTrashed()
            ->where('company_id', $companyId)
            ->where('claim_number', 'like', $prefix . '%')
            ->orderBy('claim_number', 'desc')
            ->first();

        $lastNum = 0;
        if ($last) {
            $lastNum = (int) substr($last->claim_number, -4);
        }

        return $prefix . '-' . str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Hitung tanggal berakhir garansi berdasarkan durasi warranty.
     */
    public function resolveEndDate(Warranty $warranty, ?Carbon $start = null): Carbon
    {
        $start = $start ?: now();

        return match ($warranty->duration_type) {
            'days' => $start->copy()->addDays((int) $warranty->duration_value),
            'years' => $start->copy()->addYears((int) $warranty->duration_value),
            default => $start->copy()->addMonths((int) $warranty->duration_value),
        };
    }

    /**
     * Daftarkan garansi (otomatis saat produk bergaransi terjual, atau manual).
     */
    public function registerWarranty(array $data): WarrantyRegistration
    {
        $companyId = $data['company_id'] ?? $this->resolveCompanyId();

        $warranty = Warranty::findOrFail($data['warranty_id']);

        $startDate = isset($data['start_date'])
            ? Carbon::parse($data['start_date'])
            : now();

        $endDate = isset($data['end_date'])
            ? Carbon::parse($data['end_date'])
            : $this->resolveEndDate($warranty, $startDate);

        return WarrantyRegistration::create([
            'company_id' => $companyId,
            'warranty_id' => $warranty->id,
            'product_id' => $data['product_id'],
            'serial_number_id' => $data['serial_number_id'] ?? null,
            'pos_transaction_id' => $data['pos_transaction_id'] ?? null,
            'client_id' => $data['client_id'] ?? null,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'status' => $data['status'] ?? 'active',
            'notes' => $data['notes'] ?? null,
        ]);
    }

    /**
     * Cari garansi berdasarkan nomor seri.
     */
    public function checkWarranty(string $serialNumber): ?WarrantyRegistration
    {
        $serialNumber = trim($serialNumber);

        if ($serialNumber === '') {
            return null;
        }

        $companyId = $this->resolveCompanyId();

        $serial = SerialNumber::where('company_id', $companyId)
            ->where('serial_number', $serialNumber)
            ->first();

        if (!$serial) {
            return null;
        }

        return WarrantyRegistration::where('company_id', $companyId)
            ->where('serial_number_id', $serial->id)
            ->latest('id')
            ->first();
    }

    /**
     * Buat klaim garansi baru.
     */
    public function createClaim(array $data): WarrantyClaim
    {
        $companyId = $data['company_id'] ?? $this->resolveCompanyId();

        return WarrantyClaim::create([
            'company_id' => $companyId,
            'warranty_registration_id' => $data['warranty_registration_id'],
            'claim_number' => $data['claim_number'] ?? $this->generateClaimNumber($companyId),
            'claim_date' => isset($data['claim_date']) ? Carbon::parse($data['claim_date'])->toDateString() : now()->toDateString(),
            'issue_description' => $data['issue_description'],
            'diagnosis' => $data['diagnosis'] ?? null,
            'resolution' => $data['resolution'] ?? null,
            'status' => $data['status'] ?? 'submitted',
            'resolution_type' => $data['resolution_type'] ?? null,
            'cost' => $data['cost'] ?? null,
            'approved_by' => $data['approved_by'] ?? null,
            'resolved_at' => $data['resolved_at'] ?? null,
        ]);
    }

    /**
     * Proses klaim: approve / reject / in_progress / resolve.
     */
    public function processClaim(int $claimId, string $action, ?int $userId = null): void
    {
        $claim = WarrantyClaim::findOrFail($claimId);

        $userId = $userId ?? auth()->id();

        DB::transaction(function () use ($claim, $action, $userId) {
            switch ($action) {
                case 'approve':
                    $claim->update([
                        'status' => 'approved',
                        'approved_by' => $userId,
                    ]);
                    break;

                case 'reject':
                    $claim->update([
                        'status' => 'rejected',
                        'approved_by' => $userId,
                    ]);
                    break;

                case 'in_progress':
                    $claim->update([
                        'status' => 'in_progress',
                        'approved_by' => $claim->approved_by ?? $userId,
                    ]);
                    break;

                case 'resolve':
                    $claim->update([
                        'status' => 'resolved',
                        'approved_by' => $claim->approved_by ?? $userId,
                        'resolved_at' => now(),
                    ]);
                    break;

                default:
                    throw new \InvalidArgumentException("Aksi klaim tidak dikenal: {$action}");
            }
        });
    }

    /**
     * Garansi yang akan berakhir dalam N hari ke depan.
     */
    public function getExpiringWarranties(int $days = 30): Collection
    {
        $companyId = $this->resolveCompanyId();

        return WarrantyRegistration::where('company_id', $companyId)
            ->where('status', 'active')
            ->whereDate('end_date', '>=', now()->toDateString())
            ->whereDate('end_date', '<=', now()->addDays($days)->toDateString())
            ->with(['product', 'warranty', 'serialNumber', 'client'])
            ->orderBy('end_date')
            ->get();
    }

    /**
     * Klaim garansi yang masih aktif ditangani (submitted / in_progress / approved).
     */
    public function getActiveClaims(): Collection
    {
        $companyId = $this->resolveCompanyId();

        return WarrantyClaim::where('company_id', $companyId)
            ->whereIn('status', ['submitted', 'in_progress', 'approved'])
            ->with(['registration.product', 'registration.warranty', 'registration.serialNumber'])
            ->orderBy('claim_date')
            ->get();
    }
}
