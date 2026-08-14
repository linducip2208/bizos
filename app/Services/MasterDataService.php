<?php

namespace App\Services;

use App\Models\Client;
use App\Models\DataMergeLog;
use App\Models\Employee;
use App\Models\Lead;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MasterDataService
{
    protected array $entityConfig = [
        'client' => [
            'model' => Client::class,
            'label' => 'Klien',
            'nameField' => 'name',
            'fields' => ['name', 'email', 'phone', 'tax_id', 'client_code'],
            'requiredFields' => ['name', 'email', 'phone'],
            'matchRules' => ['name_similar', 'email_exact', 'phone_normalized'],
        ],
        'supplier' => [
            'model' => Supplier::class,
            'label' => 'Pemasok',
            'nameField' => 'name',
            'fields' => ['name', 'tax_number', 'email', 'phone', 'code'],
            'requiredFields' => ['name', 'phone'],
            'matchRules' => ['name_similar', 'tax_id_exact', 'email_exact'],
        ],
        'product' => [
            'model' => Product::class,
            'label' => 'Produk',
            'nameField' => 'name',
            'fields' => ['name', 'code', 'product_type', 'description'],
            'requiredFields' => ['name', 'code'],
            'matchRules' => ['name_similar', 'code_exact'],
        ],
        'employee' => [
            'model' => Employee::class,
            'label' => 'Karyawan',
            'nameField' => 'first_name',
            'fields' => ['first_name', 'last_name', 'email', 'phone', 'employee_code', 'birth_date'],
            'requiredFields' => ['first_name', 'email', 'employee_code'],
            'matchRules' => ['name_birth_date', 'email_exact', 'phone_normalized'],
        ],
        'lead' => [
            'model' => Lead::class,
            'label' => 'Prospek',
            'nameField' => 'first_name',
            'fields' => ['first_name', 'last_name', 'email', 'phone', 'company_name'],
            'requiredFields' => ['first_name', 'email'],
            'matchRules' => ['email_exact', 'phone_normalized', 'name_similar'],
        ],
    ];

    public function detectDuplicates(string $entityType, array $options = []): Collection
    {
        $config = $this->entityConfig[$entityType] ?? null;
        if (!$config) {
            return collect();
        }

        $model = $config['model'];
        $companyId = $options['company_id'] ?? null;
        $threshold = $options['threshold'] ?? 80;

        $query = $model::query();
        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        $records = $query->get()->keyBy('id');
        $groups = [];
        $processed = [];
        $recordsIndexed = $records->values();

        foreach ($recordsIndexed as $i => $recordA) {
            if (in_array($recordA->id, $processed)) {
                continue;
            }

            $group = [$recordA];
            $processed[] = $recordA->id;

            foreach ($recordsIndexed as $j => $recordB) {
                if ($i === $j || in_array($recordB->id, $processed)) {
                    continue;
                }

                $score = $this->calculateMatchScore($entityType, $recordA, $recordB);

                if ($score >= $threshold) {
                    $group[] = $recordB;
                    $processed[] = $recordB->id;
                }
            }

            if (count($group) > 1) {
                $groups[] = [
                    'entity_type' => $entityType,
                    'records' => $group,
                    'match_scores' => $this->calculatePairwiseScores($group, $entityType),
                    'primary_candidate' => $this->selectPrimary($group, $entityType),
                ];
            }
        }

        return collect($groups);
    }

    public function getDuplicateReport(): array
    {
        $report = [];
        $companyId = auth()->user()?->company_id;

        foreach (array_keys($this->entityConfig) as $entityType) {
            $duplicates = $this->detectDuplicates($entityType, ['company_id' => $companyId]);
            $config = $this->entityConfig[$entityType];
            $model = $config['model'];

            $totalRecords = $model::when($companyId, fn($q) => $q->where('company_id', $companyId))->count();
            $duplicateRecords = $duplicates->sum(fn($g) => count($g['records']));

            $report[$entityType] = [
                'label' => $config['label'],
                'total_records' => $totalRecords,
                'duplicate_groups' => $duplicates->count(),
                'duplicate_records' => $duplicateRecords,
                'details' => $duplicates->values()->toArray(),
            ];
        }

        return $report;
    }

    public function getDataQualityScore(): array
    {
        $scores = [];
        $companyId = auth()->user()?->company_id;

        foreach ($this->entityConfig as $entityType => $config) {
            $model = $config['model'];
            $query = $model::query();
            if ($companyId) {
                $query->where('company_id', $companyId);
            }

            $records = $query->get();
            $totalRecords = $records->count();

            if ($totalRecords === 0) {
                $scores[$entityType] = [
                    'label' => $config['label'],
                    'total_records' => 0,
                    'completeness_percent' => 0,
                    'duplicate_percent' => 0,
                    'missing_required_percent' => 0,
                    'quality_score' => 0,
                ];
                continue;
            }

            $requiredFields = $config['requiredFields'];
            $completeRecords = 0;
            $missingRequiredRecords = 0;

            foreach ($records as $record) {
                $allFilled = true;
                foreach ($requiredFields as $field) {
                    if (empty($record->{$field})) {
                        $allFilled = false;
                        break;
                    }
                }
                if ($allFilled) {
                    $completeRecords++;
                }
            }

            $duplicates = $this->detectDuplicates($entityType, ['company_id' => $companyId]);
            $duplicateRecordCount = $duplicates->sum(fn($g) => count($g['records']));

            $completenessPercent = round(($completeRecords / $totalRecords) * 100, 1);
            $duplicatePercent = round(($duplicateRecordCount / $totalRecords) * 100, 1);
            $missingRequiredPercent = round(($this->countMissingRequired($records, $requiredFields) / ($totalRecords * count($requiredFields))) * 100, 1);

            $qualityScore = round(
                ($completenessPercent * 0.5) + ((100 - $duplicatePercent) * 0.3) + ((100 - $missingRequiredPercent) * 0.2),
                1
            );

            $scores[$entityType] = [
                'label' => $config['label'],
                'total_records' => $totalRecords,
                'completeness_percent' => $completenessPercent,
                'duplicate_percent' => $duplicatePercent,
                'missing_required_percent' => $missingRequiredPercent,
                'quality_score' => $qualityScore,
            ];
        }

        return $scores;
    }

    public function mergeEntities(string $entityType, int $targetId, array $sourceIds): mixed
    {
        $config = $this->entityConfig[$entityType] ?? null;
        if (!$config) {
            throw new \InvalidArgumentException("Tipe entitas '$entityType' tidak dikenal.");
        }

        $model = $config['model'];
        $target = $model::findOrFail($targetId);
        $sources = $model::whereIn('id', $sourceIds)->get();

        if ($sources->isEmpty()) {
            throw new \InvalidArgumentException('Tidak ada sumber yang dipilih untuk digabungkan.');
        }

        $mergedFields = [];

        DB::transaction(function () use ($entityType, $target, $sources, &$mergedFields) {
            foreach ($sources as $source) {
                $this->reassignRelationships($entityType, $source->id, $target->id);

                foreach (($this->entityConfig[$entityType]['fields'] ?? []) as $field) {
                    if (empty($target->{$field}) && !empty($source->{$field})) {
                        $mergedFields[$field] = $source->{$field};
                        $target->{$field} = $source->{$field};
                    }
                }

                $source->delete();
            }

            $target->save();

            DataMergeLog::create([
                'company_id' => auth()->user()?->company_id,
                'entity_type' => $entityType,
                'target_id' => $target->id,
                'source_ids' => $sourceIds,
                'merged_fields' => $mergedFields,
                'merged_by' => auth()->id(),
            ]);
        });

        return $target->fresh();
    }

    protected function calculateMatchScore(string $entityType, $recordA, $recordB): float
    {
        $scores = [];
        $weights = [];

        switch ($entityType) {
            case 'client':
                $nameA = $this->normalizeName($recordA->name ?? '');
                $nameB = $this->normalizeName($recordB->name ?? '');
                if (!empty($nameA) && !empty($nameB)) {
                    similar_text($nameA, $nameB, $nameScore);
                    $scores[] = $nameScore;
                    $weights[] = 0.5;
                }
                if (!empty($recordA->email) && !empty($recordB->email)) {
                    $scores[] = strtolower(trim($recordA->email)) === strtolower(trim($recordB->email)) ? 100 : 0;
                    $weights[] = 0.3;
                }
                if (!empty($recordA->phone) && !empty($recordB->phone)) {
                    $scores[] = $this->normalizePhone($recordA->phone) === $this->normalizePhone($recordB->phone) ? 100 : 0;
                    $weights[] = 0.2;
                }
                break;

            case 'supplier':
                $nameA = $this->normalizeName($recordA->name ?? '');
                $nameB = $this->normalizeName($recordB->name ?? '');
                if (!empty($nameA) && !empty($nameB)) {
                    similar_text($nameA, $nameB, $nameScore);
                    $scores[] = $nameScore;
                    $weights[] = 0.5;
                }
                if (!empty($recordA->tax_number) && !empty($recordB->tax_number)) {
                    $scores[] = $this->normalizeTaxId($recordA->tax_number) === $this->normalizeTaxId($recordB->tax_number) ? 100 : 0;
                    $weights[] = 0.3;
                }
                if (!empty($recordA->email) && !empty($recordB->email)) {
                    $scores[] = strtolower(trim($recordA->email)) === strtolower(trim($recordB->email)) ? 100 : 0;
                    $weights[] = 0.2;
                }
                break;

            case 'product':
                $nameA = $this->normalizeName($recordA->name ?? '');
                $nameB = $this->normalizeName($recordB->name ?? '');
                if (!empty($nameA) && !empty($nameB)) {
                    similar_text($nameA, $nameB, $nameScore);
                    $scores[] = $nameScore;
                    $weights[] = 0.4;
                }
                if (!empty($recordA->code) && !empty($recordB->code)) {
                    $scores[] = strtolower(trim($recordA->code)) === strtolower(trim($recordB->code)) ? 100 : 0;
                    $weights[] = 0.6;
                }
                break;

            case 'employee':
                $nameA = $this->normalizeName(($recordA->first_name ?? '') . ' ' . ($recordA->last_name ?? ''));
                $nameB = $this->normalizeName(($recordB->first_name ?? '') . ' ' . ($recordB->last_name ?? ''));
                $nameScore = 0;
                if (!empty(trim($nameA)) && !empty(trim($nameB))) {
                    similar_text($nameA, $nameB, $nameScore);
                }
                $birthScore = 0;
                if (!empty($recordA->birth_date) && !empty($recordB->birth_date)) {
                    $birthScore = $recordA->birth_date == $recordB->birth_date ? 100 : 0;
                }
                $scores[] = ($nameScore * 0.7 + $birthScore * 0.3);
                $weights[] = 0.4;

                if (!empty($recordA->email) && !empty($recordB->email)) {
                    $scores[] = strtolower(trim($recordA->email)) === strtolower(trim($recordB->email)) ? 100 : 0;
                    $weights[] = 0.35;
                }
                if (!empty($recordA->phone) && !empty($recordB->phone)) {
                    $scores[] = $this->normalizePhone($recordA->phone) === $this->normalizePhone($recordB->phone) ? 100 : 0;
                    $weights[] = 0.25;
                }
                break;

            case 'lead':
                $scores = [];
                $weights = [];
                if (!empty($recordA->email) && !empty($recordB->email)) {
                    $scores[] = strtolower(trim($recordA->email)) === strtolower(trim($recordB->email)) ? 100 : 0;
                    $weights[] = 0.4;
                }
                if (!empty($recordA->phone) && !empty($recordB->phone)) {
                    $scores[] = $this->normalizePhone($recordA->phone) === $this->normalizePhone($recordB->phone) ? 100 : 0;
                    $weights[] = 0.3;
                }
                $nameA = $this->normalizeName(($recordA->first_name ?? '') . ' ' . ($recordA->last_name ?? ''));
                $nameB = $this->normalizeName(($recordB->first_name ?? '') . ' ' . ($recordB->last_name ?? ''));
                if (!empty(trim($nameA)) && !empty(trim($nameB))) {
                    similar_text($nameA, $nameB, $nameScore);
                    $scores[] = $nameScore;
                    $weights[] = 0.3;
                }
                break;
        }

        if (empty($scores) || empty($weights)) {
            return 0;
        }

        $totalWeight = array_sum($weights);
        if ($totalWeight === 0) {
            return 0;
        }

        $weightedSum = 0;
        foreach ($scores as $i => $score) {
            $weightedSum += $score * $weights[$i];
        }

        return round($weightedSum / $totalWeight, 1);
    }

    protected function calculatePairwiseScores(array $records, string $entityType): array
    {
        $scores = [];
        for ($i = 0; $i < count($records); $i++) {
            for ($j = $i + 1; $j < count($records); $j++) {
                $scores[] = [
                    'record_a_id' => $records[$i]->id,
                    'record_b_id' => $records[$j]->id,
                    'score' => $this->calculateMatchScore($entityType, $records[$i], $records[$j]),
                ];
            }
        }
        return $scores;
    }

    protected function selectPrimary(array $records, string $entityType): mixed
    {
        $config = $this->entityConfig[$entityType];
        $nameField = $config['nameField'] ?? 'name';

        usort($records, function ($a, $b) {
            $aCreated = $a->created_at ?? now();
            $bCreated = $b->created_at ?? now();
            return $aCreated <=> $bCreated;
        });

        return $records[0];
    }

    protected function reassignRelationships(string $entityType, int $sourceId, int $targetId): void
    {
        switch ($entityType) {
            case 'client':
                \App\Models\Deal::where('client_id', $sourceId)->update(['client_id' => $targetId]);
                \App\Models\Lead::where('converted_client_id', $sourceId)->update(['converted_client_id' => $targetId]);
                \App\Models\Project::where('client_id', $sourceId)->update(['client_id' => $targetId]);
                \App\Models\Quotation::where('client_id', $sourceId)->update(['client_id' => $targetId]);
                \App\Models\SalesOrder::where('client_id', $sourceId)->update(['client_id' => $targetId]);
                \App\Models\Referral::where('referrer_client_id', $sourceId)->update(['referrer_client_id' => $targetId]);
                \App\Models\ClientContact::where('client_id', $sourceId)->update(['client_id' => $targetId]);
                DB::table('client_segment_members')->where('client_id', $sourceId)
                    ->updateOrInsert(['client_id' => $targetId]);
                DB::table('client_segment_members')->where('client_id', $sourceId)->where('client_id', '!=', $targetId)->delete();
                break;

            case 'supplier':
                \App\Models\PurchaseOrder::where('supplier_id', $sourceId)->update(['supplier_id' => $targetId]);
                \App\Models\RfqSupplier::where('supplier_id', $sourceId)->update(['supplier_id' => $targetId]);
                \App\Models\Bid::where('supplier_id', $sourceId)->update(['supplier_id' => $targetId]);
                break;

            case 'product':
                \App\Models\ProductVariant::where('product_id', $sourceId)->update(['product_id' => $targetId]);
                \App\Models\PosTransactionItem::where('product_id', $sourceId)->update(['product_id' => $targetId]);
                \App\Models\PrescriptionItem::where('product_id', $sourceId)->update(['product_id' => $targetId]);
                \App\Models\ProductBarcode::where('product_id', $sourceId)->update(['product_id' => $targetId]);
                \App\Models\Batch::where('product_id', $sourceId)->update(['product_id' => $targetId]);
                \App\Models\SerialNumber::where('product_id', $sourceId)->update(['product_id' => $targetId]);
                \App\Models\BillOfMaterial::where('product_id', $sourceId)->update(['product_id' => $targetId]);
                \App\Models\ProductionOrder::where('product_id', $sourceId)->update(['product_id' => $targetId]);
                break;

            case 'employee':
                \App\Models\User::where('employee_id', $sourceId)->update(['employee_id' => $targetId]);
                \App\Models\FamilyMember::where('employee_id', $sourceId)->update(['employee_id' => $targetId]);
                \App\Models\EmployeeDocument::where('employee_id', $sourceId)->update(['employee_id' => $targetId]);
                \App\Models\Attendance::where('employee_id', $sourceId)->update(['employee_id' => $targetId]);
                \App\Models\Attendance::where('approved_by', $sourceId)->update(['approved_by' => $targetId]);
                \App\Models\AttendanceLog::where('employee_id', $sourceId)->update(['employee_id' => $targetId]);
                \App\Models\Leave::where('employee_id', $sourceId)->update(['employee_id' => $targetId]);
                \App\Models\LeaveBalance::where('employee_id', $sourceId)->update(['employee_id' => $targetId]);
                \App\Models\Overtime::where('employee_id', $sourceId)->update(['employee_id' => $targetId]);
                \App\Models\Overtime::where('approved_by', $sourceId)->update(['approved_by' => $targetId]);
                \App\Models\Reimbursement::where('employee_id', $sourceId)->update(['employee_id' => $targetId]);
                \App\Models\Payroll::where('employee_id', $sourceId)->update(['employee_id' => $targetId]);
                \App\Models\TaskAssignee::where('employee_id', $sourceId)->update(['employee_id' => $targetId]);
                \App\Models\Timesheet::where('employee_id', $sourceId)->update(['employee_id' => $targetId]);
                \App\Models\Timesheet::where('approved_by', $sourceId)->update(['approved_by' => $targetId]);
                \App\Models\CourseEnrollment::where('employee_id', $sourceId)->update(['employee_id' => $targetId]);
                \App\Models\ShiftEmployee::where('employee_id', $sourceId)->update(['employee_id' => $targetId]);
                \App\Models\EmployeeSalaryComponent::where('employee_id', $sourceId)->update(['employee_id' => $targetId]);
                \App\Models\MeetingAttendee::where('employee_id', $sourceId)->update(['employee_id' => $targetId]);
                \App\Models\OnboardingProgress::where('employee_id', $sourceId)->update(['employee_id' => $targetId]);
                \App\Models\OffboardingProgress::where('employee_id', $sourceId)->update(['employee_id' => $targetId]);
                \App\Models\PerformanceReview::where('employee_id', $sourceId)->update(['employee_id' => $targetId]);
                \App\Models\PerformanceReview::where('reviewer_id', $sourceId)->update(['reviewer_id' => $targetId]);
                \App\Models\Appointment::where('doctor_id', $sourceId)->update(['doctor_id' => $targetId]);
                \App\Models\MedicalRecord::where('doctor_id', $sourceId)->update(['doctor_id' => $targetId]);
                \App\Models\Prescription::where('doctor_id', $sourceId)->update(['doctor_id' => $targetId]);
                \App\Models\LabOrder::where('doctor_id', $sourceId)->update(['doctor_id' => $targetId]);
                \App\Models\BusinessUnit::where('manager_id', $sourceId)->update(['manager_id' => $targetId]);
                \App\Models\Division::where('manager_id', $sourceId)->update(['manager_id' => $targetId]);
                \App\Models\Section::where('manager_id', $sourceId)->update(['manager_id' => $targetId]);
                break;

            case 'lead':
                \App\Models\LeadActivity::where('lead_id', $sourceId)->update(['lead_id' => $targetId]);
                \App\Models\Deal::where('lead_id', $sourceId)->update(['lead_id' => $targetId]);
                break;
        }
    }

    protected function countMissingRequired(Collection $records, array $requiredFields): int
    {
        $count = 0;
        foreach ($records as $record) {
            foreach ($requiredFields as $field) {
                if (empty($record->{$field})) {
                    $count++;
                }
            }
        }
        return $count;
    }

    protected function normalizeName(string $name): string
    {
        $name = strtolower(trim($name));
        $name = preg_replace('/\s+/', ' ', $name);
        $name = preg_replace('/[^\w\s]/u', '', $name);
        return trim($name);
    }

    protected function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        if (str_starts_with($phone, '0')) {
            $phone = '+62' . substr($phone, 1);
        }
        return $phone;
    }

    protected function normalizeTaxId(string $taxId): string
    {
        return strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $taxId));
    }
}
