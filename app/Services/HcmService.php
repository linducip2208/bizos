<?php

namespace App\Services;

use App\Models\Candidate;
use App\Models\Employee;
use App\Models\EmployeeLetter;
use App\Models\EmployeeLoan;
use App\Models\EmployeeLoanInstallment;
use App\Models\JobPosting;
use App\Models\Okr;
use App\Models\OkrKeyResult;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HcmService
{
    public function calculateOkrProgress(Okr $okr): float
    {
        $keyResults = $okr->keyResults;

        if ($keyResults->isEmpty()) {
            return 0;
        }

        $totalWeight = $keyResults->sum('weight');
        if ($totalWeight <= 0) {
            return 0;
        }

        $weightedProgress = 0;

        foreach ($keyResults as $kr) {
            $krProgress = $this->calculateKeyResultProgress($kr);
            $weight = (float) $kr->weight;
            $weightedProgress += $krProgress * ($weight / $totalWeight);
        }

        $progress = round($weightedProgress, 2);

        $okr->update(['progress_percent' => $progress]);

        return $progress;
    }

    protected function calculateKeyResultProgress(OkrKeyResult $kr): float
    {
        $target = (float) $kr->target_value;
        $current = (float) $kr->current_value;

        if ($target <= 0) {
            return 0;
        }

        if ($kr->type === 'boolean') {
            return ($current >= 1) ? 100 : 0;
        }

        if ($kr->type === 'milestone') {
            if ($kr->status === 'completed') {
                return 100;
            }
            $milestones = ['not_started' => 0, 'on_track' => 25, 'at_risk' => 50, 'behind' => 75, 'completed' => 100];
            return $milestones[$kr->status] ?? 0;
        }

        if ($kr->type === 'percentage') {
            return min(round(($current / $target) * 100, 2), 100);
        }

        return min(round(($current / $target) * 100, 2), 100);
    }

    public function processLoanDeduction(EmployeeLoan $loan): void
    {
        $pendingInstallments = $loan->installments()
            ->where('status', 'pending')
            ->orderBy('due_date')
            ->get();

        foreach ($pendingInstallments as $installment) {
            if ($loan->remaining_balance <= 0) {
                break;
            }

            $payroll = \App\Models\Payroll::where('employee_id', $loan->employee_id)
                ->where('status', 'approved')
                ->whereDoesntHave('loanInstallments', function ($q) use ($installment) {
                    $q->where('id', $installment->id);
                })
                ->latest()
                ->first();

            if ($payroll) {
                $actualDeduction = min((float) $installment->amount, (float) $loan->remaining_balance);

                $installment->update([
                    'payroll_id' => $payroll->id,
                    'status' => 'paid',
                    'paid_date' => now(),
                    'amount' => $actualDeduction,
                ]);

                $loan->remaining_balance = max(0, (float) $loan->remaining_balance - $actualDeduction);
                $loan->save();

                $payroll->net_salary = (float) $payroll->net_salary - $actualDeduction;
                $payroll->total_deduction_components = (float) $payroll->total_deduction_components + $actualDeduction;
                $payroll->save();
            }
        }

        if ((float) $loan->remaining_balance <= 0) {
            $loan->update(['status' => 'completed']);
        }
    }

    public function autoScheduleInstallments(EmployeeLoan $loan): void
    {
        $loan->installments()->delete();

        $approvedAmount = (float) ($loan->approved_amount ?? $loan->amount);
        $interestRate = (float) $loan->interest_rate;
        $installmentCount = (int) $loan->installment_count;

        if ($installmentCount <= 0) {
            return;
        }

        $totalWithInterest = $approvedAmount * (1 + $interestRate / 100);
        $installmentAmount = round($totalWithInterest / $installmentCount, 2);

        $startDate = Carbon::parse($loan->start_date)->startOfMonth();

        $installments = [];
        for ($i = 1; $i <= $installmentCount; $i++) {
            $dueDate = $startDate->copy()->addMonths($i);
            $installments[] = [
                'employee_loan_id' => $loan->id,
                'installment_number' => $i,
                'amount' => $i === $installmentCount
                    ? round($totalWithInterest - ($installmentAmount * ($installmentCount - 1)), 2)
                    : $installmentAmount,
                'status' => 'pending',
                'due_date' => $dueDate->format('Y-m-d'),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('employee_loan_installments')->insert($installments);

        $loan->update([
            'installment_amount' => $installmentAmount,
            'remaining_balance' => $totalWithInterest,
            'status' => 'active',
        ]);
    }

    public function generateOfferLetter(JobPosting $job, Candidate $candidate, array $data): EmployeeLetter
    {
        $employee = Employee::find($candidate->hired_employee_id);

        $content = $this->buildOfferLetterContent($job, $candidate, $data);

        return EmployeeLetter::create([
            'company_id' => $job->company_id,
            'employee_id' => $employee ? $employee->id : null,
            'letter_type' => 'offer',
            'letter_number' => $data['letter_number'] ?? null,
            'subject' => 'Surat Penawaran Kerja - ' . ($data['position_title'] ?? $job->title),
            'content' => $content,
            'status' => 'draft',
            'issued_by' => $data['issued_by'] ?? null,
            'issued_at' => $data['issued_at'] ?? now()->toDateString(),
        ]);
    }

    protected function buildOfferLetterContent(JobPosting $job, Candidate $candidate, array $data): string
    {
        $candidateName = trim($candidate->first_name . ' ' . $candidate->last_name);
        $positionTitle = $data['position_title'] ?? $job->title;
        $startDate = $data['start_date'] ?? '';
        $salary = $data['salary'] ?? $candidate->expected_salary ?? 0;
        $department = $data['department'] ?? ($job->department ? $job->department->name : '');

        return <<<HTML
<h3>SURAT PENAWARAN KERJA</h3>

<p>Kepada Yth.<br>
<strong>{$candidateName}</strong><br>
di tempat</p>

<p>Dengan hormat,</p>

<p>Berdasarkan hasil proses rekrutmen dan seleksi yang telah dilaksanakan, dengan ini kami menyampaikan penawaran kerja kepada Saudara/Saudari untuk bergabung dengan perusahaan kami pada posisi:</p>

<table style="width:100%; border-collapse:collapse;">
    <tr><td style="padding:4px 12px; width:180px;"><strong>Posisi</strong></td><td>: {$positionTitle}</td></tr>
    <tr><td style="padding:4px 12px;"><strong>Departemen</strong></td><td>: {$department}</td></tr>
    <tr><td style="padding:4px 12px;"><strong>Gaji Pokok</strong></td><td>: Rp " . number_format($salary, 0, ',', '.') . "</td></tr>
    <tr><td style="padding:4px 12px;"><strong>Tanggal Mulai</strong></td><td>: {$startDate}</td></tr>
</table>

<p>Apabila Saudara/Saudari menyetujui penawaran ini, mohon untuk mengkonfirmasi dan menandatangani surat ini sebagai tanda persetujuan.</p>

<p>Kami menantikan kontribusi Saudara/Saudari untuk kemajuan perusahaan.</p>

<p>Hormat kami,<br>
<strong>HR Department</strong></p>
HTML;
    }
}
