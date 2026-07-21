<?php

namespace App\Services;

use App\Models\ConsentRecord;
use App\Models\DataBreach;
use App\Models\DataErasureLog;
use App\Models\DataRectificationLog;
use App\Models\DpiaAssessment;
use App\Models\Employee;
use App\Models\Client;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use Carbon\Carbon;

class PdpComplianceService
{
    /**
     * Export all personal data for a given employee as a ZIP file.
     */
    public function exportPersonalData(int $employeeId): string
    {
        $employee = Employee::with([
            'user', 'familyMembers', 'documents', 'salaries', 'leaves', 'attendances',
            'overtimes', 'reimbursements', 'competencies',
        ])->findOrFail($employeeId);

        $data = [
            'exported_at' => now()->toISOString(),
            'purpose' => 'Data Subject Access Request (Hak Akses) — UU PDP No. 27/2022',
            'subject' => [
                'type' => 'employee',
                'id' => $employee->id,
                'employee_code' => $employee->employee_code,
                'first_name' => $employee->first_name,
                'last_name' => $employee->last_name,
                'email' => $employee->email,
                'phone' => $employee->phone,
                'gender' => $employee->gender,
                'birth_date' => $employee->birth_date,
                'birth_place' => $employee->birth_place,
                'religion' => $employee->religion,
                'marital_status' => $employee->marital_status,
                'nationality' => $employee->nationality,
                'id_number' => $employee->id_number,
                'tax_number' => $employee->tax_number,
                'bpjs_kesehatan' => $employee->bpjs_kesehatan,
                'bpjs_ketenagakerjaan' => $employee->bpjs_ketenagakerjaan,
                'address' => $employee->address,
                'city' => $employee->city,
                'province' => $employee->province,
                'postal_code' => $employee->postal_code,
                'join_date' => $employee->join_date,
                'employee_type' => $employee->employee_type,
                'status' => $employee->status,
                'bank_account' => $employee->bank_account,
                'bank_name' => $employee->bank_name,
            ],
            'family_members' => $employee->familyMembers->map(fn($f) => [
                'name' => $f->name,
                'relationship' => $f->relationship,
                'birth_date' => $f->birth_date,
                'phone' => $f->phone,
                'occupation' => $f->occupation,
            ])->toArray(),
            'documents' => $employee->documents->map(fn($d) => [
                'type' => $d->document_type,
                'number' => $d->document_number,
                'file' => $d->file_path,
            ])->toArray(),
            'payroll_history' => $employee->salaries?->map(fn($s) => [
                'period' => $s->payroll->period ?? null,
                'basic_salary' => $s->basic_salary,
                'take_home_pay' => $s->take_home_pay,
            ])->toArray() ?? [],
            'leave_history' => $employee->leaves->map(fn($l) => [
                'type' => $l->leaveType?->name,
                'start_date' => $l->start_date,
                'end_date' => $l->end_date,
                'status' => $l->status,
            ])->toArray(),
            'attendance_summary' => $employee->attendances->count() . ' records',
            'consents' => $this->getActiveConsentsForModel('employee', $employeeId)->toArray(),
        ];

        $filename = "pdp-export-employee-{$employeeId}-" . now()->format('YmdHis') . '.zip';
        $tempDir = storage_path("app/temp/pdp-exports/{$filename}-tmp");

        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        file_put_contents("{$tempDir}/data.json", json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        foreach ($employee->documents as $doc) {
            if ($doc->file_path && Storage::exists($doc->file_path)) {
                $ext = pathinfo($doc->file_path, PATHINFO_EXTENSION);
                copy(
                    Storage::path($doc->file_path),
                    "{$tempDir}/dokumen-{$doc->document_type}.{$ext}"
                );
            }
        }

        $zipPath = storage_path("app/temp/pdp-exports/{$filename}");
        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE) === true) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($tempDir),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );
            foreach ($files as $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($tempDir) + 1);
                    $zip->addFile($filePath, $relativePath);
                }
            }
            $zip->close();
        }

        $this->deleteDirectory($tempDir);

        return $zipPath;
    }

    /**
     * Export all personal data for a given client as a ZIP file.
     */
    public function exportClientData(int $clientId): string
    {
        $client = Client::with(['contacts', 'deals', 'invoices'])->findOrFail($clientId);

        $data = [
            'exported_at' => now()->toISOString(),
            'purpose' => 'Data Subject Access Request (Hak Akses) — UU PDP No. 27/2022',
            'subject' => [
                'type' => 'client',
                'id' => $client->id,
                'name' => $client->name,
                'company_name' => $client->company_name ?? null,
                'email' => $client->email,
                'phone' => $client->phone,
                'address' => $client->address,
                'city' => $client->city,
                'tax_number' => $client->tax_number ?? null,
            ],
            'contacts' => $client->contacts->map(fn($c) => [
                'name' => $c->name,
                'email' => $c->email,
                'phone' => $c->phone,
                'position' => $c->position,
            ])->toArray(),
            'consents' => $this->getActiveConsentsForModel('client', $clientId)->toArray(),
        ];

        $filename = "pdp-export-client-{$clientId}-" . now()->format('YmdHis') . '.zip';
        $zipPath = storage_path("app/temp/pdp-exports/{$filename}");

        if (!is_dir(dirname($zipPath))) {
            mkdir(dirname($zipPath), 0755, true);
        }

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE) === true) {
            $zip->addFromString('data.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $zip->close();
        }

        return $zipPath;
    }

    /**
     * Full erasure of personal data per Right to Erasure.
     */
    public function erasePersonalData(int $employeeId, string $reason): void
    {
        $employee = Employee::findOrFail($employeeId);

        DB::transaction(function () use ($employee, $reason, $employeeId) {
            $piiFields = [
                'first_name', 'last_name', 'email', 'phone', 'id_number', 'tax_number',
                'bpjs_kesehatan', 'bpjs_ketenagakerjaan', 'address', 'photo',
                'birth_date', 'birth_place', 'bank_account', 'bank_name',
                'emergency_contact_name', 'emergency_contact_phone',
            ];

            $erasedFields = [];
            $retainedFields = [];

            foreach ($piiFields as $field) {
                if (isset($employee->{$field}) && $employee->{$field} !== null) {
                    $erasedFields[$field] = $employee->{$field};
                    $employee->{$field} = null;
                }
            }

            $retainedFields['employee_code'] = $employee->employee_code;
            $retainedFields['join_date'] = $employee->join_date;

            $employee->first_name = '[DIHAPUS]';
            $employee->last_name = "ID{$employeeId}";
            $employee->email = "erased_{$employeeId}@deleted.local";
            $employee->phone = null;
            $employee->status = 'terminated';
            $employee->save();

            if ($employee->user) {
                $employee->user->update([
                    'name' => "[DIHAPUS] ID{$employeeId}",
                    'email' => "erased_{$employeeId}@deleted.local",
                    'is_active' => false,
                ]);
            }

            DataErasureLog::create([
                'company_id' => $employee->company_id,
                'subject_type' => 'employee',
                'subject_id' => $employeeId,
                'requested_by_name' => $employee->first_name ?? 'System',
                'requested_by_email' => $employee->email ?? null,
                'request_channel' => 'system',
                'requested_at' => now(),
                'action' => 'full_erasure',
                'reason' => $reason,
                'erased_fields' => $erasedFields,
                'retained_fields' => $retainedFields,
                'retention_justification' => 'Kewajiban hukum: data pajak & payroll minimal 5 tahun per UU KUP',
                'processed_at' => now(),
                'processed_by' => auth()->id(),
                'status' => 'completed',
            ]);
        });
    }

    /**
     * Anonymize data while keeping aggregates. Keeps employee_code for payroll audit.
     */
    public function anonymizeData(int $employeeId): void
    {
        $employee = Employee::findOrFail($employeeId);

        DB::transaction(function () use ($employee, $employeeId) {
            $erasedFields = [
                'first_name', 'last_name', 'email', 'phone', 'id_number',
                'tax_number', 'bpjs_kesehatan', 'bpjs_ketenagakerjaan',
                'address', 'city', 'province', 'postal_code', 'photo',
                'birth_date', 'birth_place', 'religion', 'bank_account', 'bank_name',
            ];

            $data = [];
            foreach ($erasedFields as $field) {
                $data[$field] = $employee->{$field};
                $employee->{$field} = null;
            }

            $employee->first_name = '[ANONIM]';
            $employee->last_name = hash('sha256', $employeeId . $employee->employee_code);
            $employee->email = "anonim_{$employeeId}@anonymous.local";
            $employee->save();

            if ($employee->user) {
                $employee->user->update([
                    'name' => "[ANONIM] {$employeeId}",
                    'email' => "anonim_{$employeeId}@anonymous.local",
                ]);
            }

            DataErasureLog::create([
                'company_id' => $employee->company_id,
                'subject_type' => 'employee',
                'subject_id' => $employeeId,
                'requested_by_name' => 'System',
                'request_channel' => 'system',
                'requested_at' => now(),
                'action' => 'anonymization',
                'reason' => 'Retention period expired — data dianonimkan',
                'erased_fields' => $data,
                'processed_at' => now(),
                'processed_by' => auth()->id(),
                'status' => 'completed',
            ]);
        });
    }

    /**
     * Right to Rectification — correct inaccurate personal data.
     */
    public function rectifyData(string $model, int $id, array $corrections): void
    {
        $entity = $model::findOrFail($id);

        $correctionLog = [];

        DB::transaction(function () use ($entity, $corrections, &$correctionLog) {
            foreach ($corrections as $field => $newValue) {
                $oldValue = $entity->{$field} ?? null;
                if ($oldValue !== $newValue) {
                    $correctionLog[] = [
                        'field' => $field,
                        'old' => $oldValue,
                        'new' => $newValue,
                    ];
                    $entity->{$field} = $newValue;
                }
            }

            if (!empty($correctionLog)) {
                $entity->save();

                DataRectificationLog::create([
                    'company_id' => $entity->company_id ?? auth()->user()?->company_id,
                    'entity_type' => $model,
                    'entity_id' => $id,
                    'requested_by_name' => auth()->user()?->name ?? 'System',
                    'requested_by_email' => auth()->user()?->email,
                    'request_channel' => 'system',
                    'corrections' => $correctionLog,
                    'reason' => 'Data rectification request',
                    'requested_at' => now(),
                    'processed_at' => now(),
                    'processed_by' => auth()->id(),
                    'status' => 'completed',
                ]);
            }
        });
    }

    /**
     * Restrict processing for a data subject.
     */
    public function restrictProcessing(int $employeeId, string $reason): void
    {
        Employee::where('id', $employeeId)->update([
            'data_processing_restricted' => true,
            'restriction_reason' => $reason,
            'restricted_at' => now(),
        ]);
    }

    /**
     * Lift processing restriction.
     */
    public function liftRestriction(int $employeeId): void
    {
        Employee::where('id', $employeeId)->update([
            'data_processing_restricted' => false,
            'restriction_reason' => null,
            'restricted_at' => null,
        ]);
    }

    /**
     * Record explicit consent.
     */
    public function recordConsent(int $personId, string $purpose, string $method, ?Carbon $expiresAt = null): ConsentRecord
    {
        return ConsentRecord::create([
            'company_id' => auth()->user()?->company_id ?? 1,
            'person_type' => 'employee',
            'person_id' => $personId,
            'purpose' => $purpose,
            'method' => $method,
            'consented_at' => now(),
            'expires_at' => $expiresAt,
            'status' => 'active',
            'metadata' => [
                'recorded_by' => auth()->id(),
                'ip_address' => request()->ip(),
            ],
        ]);
    }

    /**
     * Withdraw a consent record.
     */
    public function withdrawConsent(int $consentId): void
    {
        $consent = ConsentRecord::findOrFail($consentId);
        $consent->update([
            'status' => 'withdrawn',
            'withdrawn_at' => now(),
            'withdrawal_reason' => 'Data subject withdrawal request',
        ]);
    }

    /**
     * Get all active consents for a person.
     */
    public function getActiveConsents(int $personId): Collection
    {
        return ConsentRecord::where('person_type', 'employee')
            ->where('person_id', $personId)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->get();
    }

    /**
     * Get all active consents for any model type.
     */
    private function getActiveConsentsForModel(string $type, int $id): Collection
    {
        return ConsentRecord::where('person_type', $type)
            ->where('person_id', $id)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->get();
    }

    /**
     * Check if a person has active consent for a given purpose.
     */
    public function checkConsent(int $personId, string $purpose): bool
    {
        return ConsentRecord::where('person_type', 'employee')
            ->where('person_id', $personId)
            ->where('purpose', $purpose)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->exists();
    }

    /**
     * Report a data breach to the internal register.
     */
    public function reportBreach(array $data): DataBreach
    {
        return DataBreach::create([
            'company_id' => auth()->user()?->company_id ?? 1,
            'breach_type' => $data['type'] ?? 'unauthorized_access',
            'severity' => $data['severity'] ?? 'medium',
            'description' => $data['description'],
            'discovered_at' => $data['discovered_at'] ?? now(),
            'affected_records_count' => $data['affected_records_count'] ?? 0,
            'affected_data_types' => $data['affected_data_types'] ?? [],
            'immediate_actions' => $data['immediate_actions'] ?? null,
            'status' => 'open',
            'reported_by' => auth()->id(),
        ]);
    }

    /**
     * Notify all affected subjects of a data breach.
     * In real implementation would send WA/email via notification service.
     */
    public function notifyAffectedSubjects(DataBreach $breach): void
    {
        $notificationService = app(NotificationService::class);

        $message = "PEMBERITAHUAN PELANGGARAN DATA\n\n"
            . "Perusahaan kami mendeteksi insiden keamanan data pada "
            . $breach->discovered_at->format('d F Y H:i') . ".\n\n"
            . "Jenis insiden: {$breach->breach_type}\n"
            . "Data yang mungkin terdampak: " . implode(', ', $breach->affected_data_types ?? []) . "\n\n"
            . "Kami sedang melakukan investigasi dan akan memberikan informasi lebih lanjut.\n"
            . "Hubungi DPO kami untuk pertanyaan lebih lanjut.";

        $breach->update([
            'notified_subjects_at' => now(),
            'status' => 'investigating',
        ]);
    }

    /**
     * Notify Indonesia's Data Protection Authority (simulated).
     * PDP Law: notification within 3×24 hours from discovery.
     */
    public function notifyDpa(DataBreach $breach): void
    {
        $breach->update([
            'notified_dpa_at' => now(),
            'dpa_report_number' => 'PDP-' . date('Ymd') . '-' . str_pad($breach->id, 6, '0', STR_PAD_LEFT),
        ]);
    }

    /**
     * Check if breach notification has exceeded the 72-hour deadline.
     */
    public function isLateBreachNotification(DataBreach $breach): bool
    {
        if ($breach->notified_dpa_at) {
            return $breach->notified_dpa_at->diffInHours($breach->discovered_at) > 72;
        }

        return $breach->discovered_at->diffInHours(now()) > 72;
    }

    /**
     * Apply data retention policy — auto-delete or anonymize expired data.
     */
    public function applyRetentionPolicy(): void
    {
        $retentionYears = [
            'employee_data' => 2,    // 2 years after employment ends
            'payroll_data' => 10,    // 10 years per tax regulations
            'attendance_data' => 5,  // 5 years
            'leave_data' => 5,
            'reimbursement_data' => 8, // 8 years per UU KUP
            'client_data' => 5,       // 5 years after last transaction
            'lead_data' => 2,         // 2 years
        ];

        $cutoff = now()->subYears(2);

        $terminatedEmployees = Employee::where('status', 'terminated')
            ->whereNotNull('termination_date')
            ->where('termination_date', '<', $cutoff)
            ->whereNull('data_processing_restricted')
            ->get();

        foreach ($terminatedEmployees as $employee) {
            $this->anonymizeData($employee->id);
        }
    }

    /**
     * Get retention compliance status per category.
     */
    public function getRetentionStatus(): array
    {
        $totalEmployees = Employee::count();
        $anonymized = Employee::where('first_name', '[ANONIM]')->count();
        $terminated = Employee::where('status', 'terminated')->count();
        $pendingErasure = DataErasureLog::where('status', 'pending')->count();

        return [
            'total_employees' => $totalEmployees,
            'active_employees' => $totalEmployees - $terminated,
            'anonymized_records' => $anonymized,
            'terminated_records' => $terminated,
            'pending_erasure_requests' => $pendingErasure,
            'policy_compliance' => $terminated > 0 && $anonymized > 0 ? 'partial' : 'compliant',
        ];
    }

    /**
     * Create a Data Protection Impact Assessment.
     */
    public function createDpia(array $data): DpiaAssessment
    {
        return DpiaAssessment::create([
            'company_id' => auth()->user()?->company_id ?? 1,
            'title' => $data['title'],
            'processing_activity' => $data['processing_activity'],
            'description' => $data['description'] ?? null,
            'data_controller' => $data['data_controller'] ?? null,
            'data_processor' => $data['data_processor'] ?? null,
            'data_types' => $data['data_types'] ?? [],
            'data_subjects' => $data['data_subjects'] ?? [],
            'risks' => $data['risks'] ?? [],
            'mitigations' => $data['mitigations'] ?? [],
            'necessity_proportionality' => $data['necessity_proportionality'] ?? null,
            'status' => 'draft',
            'risk_level' => $this->calculateDpiaRiskLevel($data['risks'] ?? []),
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * Get all pending DPIAs.
     */
    public function getPendingDpias(): Collection
    {
        return DpiaAssessment::whereIn('status', ['draft', 'in_review'])->get();
    }

    /**
     * Generate overall PDP compliance report.
     */
    public function generateComplianceReport(): array
    {
        $consentCoverage = Employee::where('status', 'active')->count();
        $totalConsents = ConsentRecord::where('status', 'active')->count();

        $totalBreaches = DataBreach::count();
        $unresolvedBreaches = DataBreach::whereNotIn('status', ['resolved', 'closed'])->count();

        $totalDpias = DpiaAssessment::count();
        $pendingDpias = DpiaAssessment::whereIn('status', ['draft', 'in_review'])->count();

        $retention = $this->getRetentionStatus();

        $erasureRequests = DataErasureLog::where('status', 'pending')->count();
        $rectificationRequests = DataRectificationLog::where('status', 'pending')->count();

        $score = 100;
        if ($totalConsents < $consentCoverage) $score -= 15;
        if ($unresolvedBreaches > 0) $score -= 20;
        if ($pendingDpias > 0) $score -= 10;
        if ($retention['policy_compliance'] === 'partial') $score -= 10;
        if ($erasureRequests > 0) $score -= 5;

        return [
            'overall_score' => max(0, $score),
            'consent_coverage' => [
                'total_active_employees' => $consentCoverage,
                'total_active_consents' => $totalConsents,
                'coverage_percentage' => $consentCoverage > 0
                    ? round(($totalConsents / $consentCoverage) * 100, 1)
                    : 0,
            ],
            'breach_history' => [
                'total_breaches' => $totalBreaches,
                'unresolved_breaches' => $unresolvedBreaches,
                'lates_notifications' => DataBreach::where('status', '!=', 'closed')
                    ->get()
                    ->filter(fn($b) => $this->isLateBreachNotification($b))
                    ->count(),
            ],
            'dpia_status' => [
                'total' => $totalDpias,
                'draft' => DpiaAssessment::where('status', 'draft')->count(),
                'in_review' => DpiaAssessment::where('status', 'in_review')->count(),
                'approved' => DpiaAssessment::where('status', 'approved')->count(),
                'pending' => $pendingDpias,
            ],
            'retention_compliance' => $retention,
            'subject_rights' => [
                'pending_erasure' => $erasureRequests,
                'pending_rectification' => $rectificationRequests,
            ],
            'generated_at' => now()->toISOString(),
        ];
    }

    /**
     * Calculate DPIA risk level from risks array.
     */
    private function calculateDpiaRiskLevel(array $risks): string
    {
        if (empty($risks)) return 'low';

        $levels = array_column($risks, 'severity');
        $scoreMap = ['low' => 1, 'medium' => 2, 'high' => 3, 'critical' => 4];
        $maxScore = 0;

        foreach ($levels as $level) {
            $maxScore = max($maxScore, $scoreMap[$level] ?? 1);
        }

        return match(true) {
            $maxScore >= 4 => 'critical',
            $maxScore >= 3 => 'high',
            $maxScore >= 2 => 'medium',
            default => 'low',
        };
    }

    private function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) return;
        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($items as $item) {
            $item->isDir() ? rmdir($item->getRealPath()) : unlink($item->getRealPath());
        }
        rmdir($dir);
    }
}
