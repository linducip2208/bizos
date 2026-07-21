<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\FamilyMember;
use App\Models\BpjsConfig;

class FamilyBpjsService
{
    protected BpjsCalculatorService $bpjsCalculator;

    public function __construct()
    {
        $this->bpjsCalculator = app(BpjsCalculatorService::class);
    }

    public function calculateFamilyContribution(Employee $employee): array
    {
        $familyMembers = $employee->familyMembers()
            ->where('is_dependent', true)
            ->get();

        $familyCount = $familyMembers->count();
        $salary = (float) ($employee->basic_salary ?? 0);

        $ceiling = $this->bpjsCalculator->getCeiling('kesehatan');
        $baseSalary = min($salary, $ceiling);

        $bpjsKesTotal = $this->bpjsCalculator->calculateBpjsKesehatan($salary);

        $maxFamilyInBpjs = 4;
        $additionalFamily = max(0, $familyCount - 1);

        $additionalRatePerMember = 0.01;
        $additionalPremium = round($baseSalary * $additionalRatePerMember * $additionalFamily, 2);

        $employeeBpjsKes = $bpjsKesTotal['employee_amount'];
        $employerBpjsKes = $bpjsKesTotal['employer_amount'];
        $totalBpjsKes = $employeeBpjsKes + $employerBpjsKes + $additionalPremium;

        return [
            'employee_name' => ($employee->first_name ?? '') . ' ' . ($employee->last_name ?? ''),
            'salary' => $salary,
            'base_salary_for_bpjs' => $baseSalary,
            'is_capped' => $salary > $ceiling,
            'ceiling' => $ceiling,
            'family_members_count' => $familyCount,
            'additional_family_count' => $additionalFamily,
            'family_members' => $familyMembers->map(fn ($m) => [
                'name' => $m->name,
                'relationship' => $m->relationship,
                'nik' => $m->nik,
                'kk_number' => $m->kk_number,
                'is_dependent' => $m->is_dependent,
            ])->toArray(),
            'base_premium_employee' => $employeeBpjsKes,
            'base_premium_employer' => $employerBpjsKes,
            'additional_premium' => $additionalPremium,
            'additional_rate_per_member' => $additionalRatePerMember,
            'total_bpjs_kes' => $totalBpjsKes,
            'total_bpjs_kes_employee' => $employeeBpjsKes + $additionalPremium,
            'total_bpjs_kes_employer' => $employerBpjsKes,
        ];
    }

    public function syncFamilyToPayroll(Employee $employee): void
    {
        $contribution = $this->calculateFamilyContribution($employee);

        $employee->update([
            'bpjs_kes_tier' => $this->determineBpjsTier($contribution),
        ]);
    }

    public function syncAllEmployeesToPayroll(int $companyId): array
    {
        $employees = Employee::where('company_id', $companyId)
            ->where('status', 'active')
            ->get();

        $results = [];

        foreach ($employees as $employee) {
            try {
                $this->syncFamilyToPayroll($employee);
                $results[] = [
                    'employee_id' => $employee->id,
                    'status' => 'synced',
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'employee_id' => $employee->id,
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    public function validateBpjsData(Employee $employee): array
    {
        $errors = [];
        $warnings = [];

        $familyMembers = $employee->familyMembers()->where('is_dependent', true)->get();

        foreach ($familyMembers as $member) {
            if (empty($member->nik)) {
                $errors[] = "Anggota keluarga \"{$member->name}\" belum memiliki NIK.";
            }

            if (empty($member->kk_number)) {
                $warnings[] = "Anggota keluarga \"{$member->name}\" belum memiliki nomor KK.";
            }

            if (empty($member->birth_date)) {
                $warnings[] = "Anggota keluarga \"{$member->name}\" belum memiliki tanggal lahir.";
            }

            if (!empty($member->nik) && strlen($member->nik) !== 16) {
                $errors[] = "NIK anggota keluarga \"{$member->name}\" tidak valid (harus 16 digit).";
            }
        }

        if (empty($employee->bpjs_kesehatan)) {
            $warnings[] = "Karyawan belum memiliki nomor BPJS Kesehatan.";
        }

        if (empty($employee->bpjs_ketenagakerjaan)) {
            $warnings[] = "Karyawan belum memiliki nomor BPJS Ketenagakerjaan.";
        }

        if (empty($employee->id_number)) {
            $errors[] = "Karyawan belum memiliki NIK.";
        }

        return [
            'is_valid' => empty($errors),
            'employee_name' => ($employee->first_name ?? '') . ' ' . ($employee->last_name ?? ''),
            'family_members_count' => $familyMembers->count(),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    public function validateAllEmployeesBpjs(int $companyId): array
    {
        $employees = Employee::where('company_id', $companyId)
            ->where('status', 'active')
            ->with('familyMembers')
            ->get();

        $results = [];

        foreach ($employees as $employee) {
            $results[] = $this->validateBpjsData($employee);
        }

        $validCount = count(array_filter($results, fn ($r) => $r['is_valid']));
        $invalidCount = count($results) - $validCount;

        return [
            'total_employees' => count($results),
            'valid_count' => $validCount,
            'invalid_count' => $invalidCount,
            'details' => $results,
        ];
    }

    public function getFamilyMembersForBpjs(Employee $employee): array
    {
        $familyMembers = $employee->familyMembers()
            ->where('is_dependent', true)
            ->orderBy('relationship')
            ->get();

        $registerable = [];
        $maxBpjsFamily = 4;

        $covered = 0;

        foreach ($familyMembers as $member) {
            $covered++;
            $registerable[] = [
                'id' => $member->id,
                'name' => $member->name,
                'relationship' => $member->relationship,
                'nik' => $member->nik,
                'kk_number' => $member->kk_number,
                'birth_date' => $member->birth_date?->format('Y-m-d'),
                'covered' => $covered <= $maxBpjsFamily,
                'coverage_number' => $covered <= $maxBpjsFamily ? $covered : null,
            ];
        }

        return [
            'employee_name' => ($employee->first_name ?? '') . ' ' . ($employee->last_name ?? ''),
            'employee_nik' => $employee->id_number,
            'employee_bpjs_kesehatan' => $employee->bpjs_kesehatan,
            'employee_bpjs_ketenagakerjaan' => $employee->bpjs_ketenagakerjaan,
            'total_dependents' => $familyMembers->count(),
            'max_bpjs_cover' => $maxBpjsFamily,
            'registerable_members' => $registerable,
        ];
    }

    protected function determineBpjsTier(array $contribution): string
    {
        $salary = $contribution['salary'];

        if ($salary < 4000000) {
            return 'III';
        }
        if ($salary <= 8000000) {
            return 'II';
        }

        return 'I';
    }
}
