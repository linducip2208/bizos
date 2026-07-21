<?php

namespace App\Services;

use App\Models\Company;
use App\Models\User;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Position;
use App\Models\Designation;
use App\Models\Grade;
use App\Models\Employee;
use App\Models\Coa;
use App\Models\CoaCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SandboxService
{
    public function createSandbox(Company $source): Company
    {
        $sandbox = Company::create([
            'code' => 'SANDBOX_' . Str::upper(Str::random(6)),
            'name' => $source->name . ' (Sandbox)',
            'slug' => $source->slug . '-sandbox',
            'address' => $source->address,
            'phone' => $source->phone,
            'email' => $source->email,
            'is_active' => true,
            'is_sandbox' => true,
            'sandbox_source_id' => $source->id,
        ]);

        DB::transaction(function () use ($source, $sandbox) {
            $this->cloneBranches($source->id, $sandbox->id);
            $this->cloneDepartments($source->id, $sandbox->id);
            $this->clonePositions($source->id, $sandbox->id);
            $this->cloneDesignations($source->id, $sandbox->id);
            $this->cloneGrades($source->id, $sandbox->id);
            $this->cloneCoa($source->id, $sandbox->id);
            $this->cloneUsers($source->id, $sandbox->id);

            $sandbox->update(['sandbox_source_id' => $source->id]);
        });

        return $sandbox;
    }

    public function resetSandbox(Company $sandbox): void
    {
        if (!$sandbox->is_sandbox) {
            throw new \RuntimeException('Bukan perusahaan sandbox.');
        }

        $sourceId = $sandbox->sandbox_source_id;
        if (!$sourceId) {
            throw new \RuntimeException('Tidak ada sumber untuk reset.');
        }

        $sandbox->employees()->forceDelete();
        $sandbox->users()->whereNotIn('id', function ($q) use ($sandbox) {
            $q->select('id')->from('users')->where('company_id', $sandbox->id);
        })->forceDelete();

        Company::where('id', $sourceId)->first();
        $this->cloneUsers($sourceId, $sandbox->id);
    }

    public function deleteSandbox(Company $sandbox): void
    {
        if (!$sandbox->is_sandbox) {
            throw new \RuntimeException('Bukan perusahaan sandbox.');
        }

        $sandbox->employees()->forceDelete();
        $sandbox->users()->forceDelete();
        $sandbox->branches()->forceDelete();
        $sandbox->departments()->forceDelete();
        $sandbox->positions()->forceDelete();
        $sandbox->designations()->forceDelete();
        $sandbox->grades()->forceDelete();
        $sandbox->coa()->forceDelete();
        $sandbox->forceDelete();
    }

    public function isSandbox(Company $company): bool
    {
        return (bool) $company->is_sandbox;
    }

    protected function cloneBranches(int $sourceId, int $targetId): void
    {
        Branch::where('company_id', $sourceId)->get()->each(function ($item) use ($targetId) {
            $new = $item->replicate();
            $new->company_id = $targetId;
            $new->save();
        });
    }

    protected function cloneDepartments(int $sourceId, int $targetId): void
    {
        Department::where('company_id', $sourceId)->get()->each(function ($item) use ($targetId) {
            $new = $item->replicate();
            $new->company_id = $targetId;
            $new->save();
        });
    }

    protected function clonePositions(int $sourceId, int $targetId): void
    {
        Position::where('company_id', $sourceId)->get()->each(function ($item) use ($targetId) {
            $new = $item->replicate();
            $new->company_id = $targetId;
            $new->save();
        });
    }

    protected function cloneDesignations(int $sourceId, int $targetId): void
    {
        Designation::where('company_id', $sourceId)->get()->each(function ($item) use ($targetId) {
            $new = $item->replicate();
            $new->company_id = $targetId;
            $new->save();
        });
    }

    protected function cloneGrades(int $sourceId, int $targetId): void
    {
        Grade::where('company_id', $sourceId)->get()->each(function ($item) use ($targetId) {
            $new = $item->replicate();
            $new->company_id = $targetId;
            $new->save();
        });
    }

    protected function cloneCoa(int $sourceId, int $targetId): void
    {
        CoaCategory::where('company_id', $sourceId)->get()->each(function ($item) use ($targetId) {
            $new = $item->replicate();
            $new->company_id = $targetId;
            $new->save();
        });

        Coa::where('company_id', $sourceId)->get()->each(function ($item) use ($targetId) {
            $new = $item->replicate();
            $new->company_id = $targetId;
            $new->save();
        });
    }

    protected function cloneUsers(int $sourceId, int $targetId): void
    {
        User::where('company_id', $sourceId)->get()->each(function ($item) use ($targetId) {
            $new = $item->replicate();
            $new->company_id = $targetId;
            $new->email = str_replace('@', '+sandbox@', $new->email);
            $new->save();
        });
    }
}
