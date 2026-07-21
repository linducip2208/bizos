<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\ComplianceDataAccessLog;
use App\Models\ConsentRecord;
use App\Models\DataErasureLog;
use App\Models\DataRectificationLog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ComplianceAuditService
{
    /**
     * Generate compliance audit report for regulator (Kominfo, OJK, BPKP, etc.).
     */
    public function generateAuditReport(string $dateFrom, string $dateTo): array
    {
        $from = Carbon::parse($dateFrom)->startOfDay();
        $to = Carbon::parse($dateTo)->endOfDay();

        return [
            'report_period' => [
                'from' => $from->toISOString(),
                'to' => $to->toISOString(),
                'generated_at' => now()->toISOString(),
            ],
            'user_access_changes' => AuditLog::whereBetween('created_at', [$from, $to])
                ->where('entity_type', 'App\Models\User')
                ->count(),
            'role_changes' => AuditLog::whereBetween('created_at', [$from, $to])
                ->whereIn('entity_type', ['App\Models\Role', 'App\Models\Permission'])
                ->count(),
            'data_access_summary' => $this->getDataAccessSummary($from, $to),
            'consent_changes' => $this->getConsentChanges($from, $to),
            'erasure_requests' => DataErasureLog::whereBetween('created_at', [$from, $to])->count(),
            'rectification_requests' => DataRectificationLog::whereBetween('created_at', [$from, $to])->count(),
            'critical_actions' => AuditLog::whereBetween('created_at', [$from, $to])
                ->whereIn('action', ['deleted', 'force_deleted'])
                ->with('user')
                ->get()
                ->map(fn($log) => [
                    'user' => $log->user?->name,
                    'action' => $log->action,
                    'entity' => class_basename($log->entity_type),
                    'entity_id' => $log->entity_id,
                    'timestamp' => $log->created_at->toISOString(),
                    'ip' => $log->ip_address,
                ]),
            'compliance_score' => app(PdpComplianceService::class)->generateComplianceReport(),
        ];
    }

    /**
     * Get full consent audit trail for a person.
     */
    public function getConsentAuditTrail(int $personId): array
    {
        $consents = ConsentRecord::where('person_id', $personId)
            ->orderBy('created_at', 'desc')
            ->get();

        return $consents->map(function ($c) {
            return [
                'purpose' => $c->purpose,
                'method' => $c->method,
                'consented_at' => $c->consented_at?->toISOString(),
                'expires_at' => $c->expires_at?->toISOString(),
                'withdrawn_at' => $c->withdrawn_at?->toISOString(),
                'status' => $c->status,
                'withdrawal_reason' => $c->withdrawal_reason,
            ];
        })->toArray();
    }

    /**
     * Log access to personal data (who accessed whose data, when, why).
     */
    public function logDataAccess(int $accessedBy, string $dataSubjectType, int $dataSubjectId, string $purpose): void
    {
        ComplianceDataAccessLog::create([
            'company_id' => auth()->user()?->company_id ?? 1,
            'accessed_by' => $accessedBy,
            'data_subject_type' => $dataSubjectType,
            'data_subject_id' => $dataSubjectId,
            'purpose' => $purpose,
            'legal_basis' => 'legitimate_interest',
            'access_method' => 'view',
            'accessed_fields' => null,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Get all data access logs for a subject.
     */
    public function getDataAccessLog(int $dataSubjectId): Collection
    {
        return ComplianceDataAccessLog::where('data_subject_id', $dataSubjectId)
            ->with('accessor')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Export audit log for external auditor in CSV or JSON format.
     */
    public function exportForExternalAudit(string $format = 'csv'): string
    {
        $logs = AuditLog::with('user')
            ->where('created_at', '>=', now()->subYear())
            ->orderBy('created_at')
            ->get();

        $data = $logs->map(fn($log) => [
            'timestamp' => $log->created_at->toISOString(),
            'user' => $log->user?->name ?? 'System',
            'user_email' => $log->user?->email ?? 'n/a',
            'action' => $log->action,
            'entity_type' => class_basename($log->entity_type),
            'entity_id' => $log->entity_id,
            'ip_address' => $log->ip_address,
            'user_agent' => $log->user_agent,
            'old_values' => json_encode($log->old_values),
            'new_values' => json_encode($log->new_values),
        ]);

        $filename = 'audit-export-' . now()->format('YmdHis') . '.' . ($format === 'csv' ? 'csv' : 'json');
        $path = "audit-exports/{$filename}";

        if ($format === 'csv') {
            $csv = $this->arrayToCsv($data->toArray());
            Storage::put($path, $csv);
        } else {
            Storage::put($path, $data->toJson(JSON_PRETTY_PRINT));
        }

        return Storage::path($path);
    }

    /**
     * Export all compliance data for DPO/Kominfo audit.
     */
    public function exportFullCompliancePack(): string
    {
        $pack = [
            'export_date' => now()->toISOString(),
            'company' => auth()->user()?->company?->name ?? 'BizOS Tenant',
            'pdp_compliance' => app(PdpComplianceService::class)->generateComplianceReport(),
            'iso_risk_register' => app(IsoComplianceService::class)->getRiskRegister()->toArray(),
            'iso_soa' => app(IsoComplianceService::class)->generateSoa(),
            'sod_conflicts' => app(SodService::class)->getActiveConflicts()->toArray(),
            'breach_reports' => \App\Models\DataBreach::orderBy('discovered_at', 'desc')->get()->toArray(),
            'dpias' => \App\Models\DpiaAssessment::orderBy('created_at', 'desc')->get()->toArray(),
        ];

        $filename = 'compliance-pack-' . now()->format('YmdHis') . '.json';
        $path = "audit-exports/{$filename}";

        Storage::put($path, json_encode($pack, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return Storage::path($path);
    }

    /**
     * Get data access summary for a period.
     */
    private function getDataAccessSummary(Carbon $from, Carbon $to): array
    {
        $logs = ComplianceDataAccessLog::whereBetween('created_at', [$from, $to]);

        return [
            'total_accesses' => $logs->count(),
            'by_subject_type' => [
                'employee' => (clone $logs)->where('data_subject_type', 'employee')->count(),
                'client' => (clone $logs)->where('data_subject_type', 'client')->count(),
                'other' => (clone $logs)->whereNotIn('data_subject_type', ['employee', 'client'])->count(),
            ],
            'by_purpose' => (clone $logs)->selectRaw('purpose, count(*) as count')
                ->groupBy('purpose')->pluck('count', 'purpose')->toArray(),
            'unique_accessors' => (clone $logs)->distinct('accessed_by')->count('accessed_by'),
        ];
    }

    /**
     * Get consent changes in a period.
     */
    private function getConsentChanges(Carbon $from, Carbon $to): array
    {
        return [
            'new_consents' => ConsentRecord::whereBetween('created_at', [$from, $to])->count(),
            'withdrawn_consents' => ConsentRecord::whereBetween('withdrawn_at', [$from, $to])->count(),
            'expired_consents' => ConsentRecord::where('status', 'expired')->whereBetween('expires_at', [$from, $to])->count(),
        ];
    }

    /**
     * Convert array to CSV string.
     */
    private function arrayToCsv(array $data): string
    {
        if (empty($data)) return '';

        $output = fopen('php://temp', 'r+');

        fputcsv($output, array_keys($data[0]));

        foreach ($data as $row) {
            fputcsv($output, $row);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}
