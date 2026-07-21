<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeDocument;
use Illuminate\Support\Collection;

class DocumentVerificationService
{
    public function requestVerification(EmployeeDocument $doc): void
    {
        $doc->update([
            'verification_status' => 'pending',
            'verified_by' => null,
            'verified_at' => null,
            'rejection_reason' => null,
        ]);
    }

    public function verifyDocument(EmployeeDocument $doc, int $verifierId): void
    {
        $doc->update([
            'verification_status' => 'verified',
            'verified_by' => $verifierId,
            'verified_at' => now(),
            'rejection_reason' => null,
        ]);
    }

    public function rejectDocument(EmployeeDocument $doc, int $verifierId, string $reason): void
    {
        $doc->update([
            'verification_status' => 'rejected',
            'verified_by' => $verifierId,
            'verified_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }

    public function batchVerifyDocuments(array $documentIds, int $verifierId): array
    {
        $results = ['verified' => 0, 'failed' => 0];

        foreach ($documentIds as $id) {
            $doc = EmployeeDocument::find($id);
            if ($doc) {
                try {
                    $this->verifyDocument($doc, $verifierId);
                    $results['verified']++;
                } catch (\Exception $e) {
                    $results['failed']++;
                }
            }
        }

        return $results;
    }

    public function autoVerify(EmployeeDocument $doc): array
    {
        $issues = [];
        $extractedData = [];

        if (!file_exists(public_path($doc->file_path))) {
            $issues[] = 'File tidak ditemukan di server.';
        }

        if ($doc->expiry_date && $doc->expiry_date->isPast()) {
            $issues[] = 'Dokumen sudah kedaluwarsa (' . $doc->expiry_date->format('d M Y') . ').';
        }

        $requiredFields = $this->getDocumentTypeRequirements($doc->document_type);
        $employee = $doc->employee;

        foreach ($requiredFields as $field) {
            match ($field) {
                'name' => $extractedData['name'] = ($employee->first_name ?? '') . ' ' . ($employee->last_name ?? ''),
                'id_number' => $extractedData['id_number'] = $employee->id_number ?? 'Tidak tersedia',
                'birth_date' => $extractedData['birth_date'] = $employee->birth_date?->format('Y-m-d'),
                'tax_number' => $extractedData['tax_number'] = $employee->tax_number ?? 'Tidak tersedia',
                default => null,
            };
        }

        $isVerified = empty($issues);
        $confidence = $isVerified ? 0.85 : 0.3;

        return [
            'is_verified' => $isVerified,
            'confidence' => $confidence,
            'extracted_data' => $extractedData,
            'issues' => $issues,
            'warnings' => $isVerified ? [] : ['Dokumen perlu diverifikasi manual.'],
        ];
    }

    public function getPendingVerifications(?int $departmentId = null): Collection
    {
        $query = EmployeeDocument::where('verification_status', 'pending')
            ->with(['employee.department']);

        if ($departmentId) {
            $query->whereHas('employee', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        return $query->get();
    }

    public function getVerificationSummary(?int $companyId = null): array
    {
        $query = EmployeeDocument::query();

        if ($companyId) {
            $query->whereHas('employee', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            });
        }

        $total = $query->count();
        $verified = (clone $query)->where('verification_status', 'verified')->count();
        $pending = (clone $query)->where('verification_status', 'pending')->count();
        $rejected = (clone $query)->where('verification_status', 'rejected')->count();

        return [
            'total_documents' => $total,
            'verified' => $verified,
            'pending' => $pending,
            'rejected' => $rejected,
            'completion_percent' => $total > 0 ? round(($verified / $total) * 100, 2) : 0,
        ];
    }

    public function getExpiringDocuments(int $daysThreshold = 30): Collection
    {
        return EmployeeDocument::whereNotNull('expiry_date')
            ->where('expiry_date', '>=', now()->toDateString())
            ->where('expiry_date', '<=', now()->addDays($daysThreshold)->toDateString())
            ->where('verification_status', '!=', 'rejected')
            ->with(['employee.department'])
            ->orderBy('expiry_date')
            ->get();
    }

    protected function getDocumentTypeRequirements(string $documentType): array
    {
        return match ($documentType) {
            'KTP' => ['name', 'id_number', 'birth_date'],
            'KK' => ['name'],
            'NPWP' => ['name', 'tax_number'],
            'Ijazah' => ['name'],
            'SKCK' => ['name'],
            'Sertifikat' => ['name'],
            'BPJS Kesehatan' => ['name', 'id_number'],
            'BPJS Ketenagakerjaan' => ['name', 'id_number'],
            default => ['name'],
        };
    }
}
