<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\IsoAudit;
use App\Models\IsoAuditFinding;
use App\Models\IsoIncident;
use App\Models\IsoPolicy;
use App\Models\IsoRisk;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class IsoComplianceService
{
    /**
     * Get all information assets and their security classification.
     */
    public function getInformationAssets(): Collection
    {
        return Asset::with('category')->get()->map(function ($asset) {
            $classification = $this->classifyAsset($asset);
            return [
                'id' => $asset->id,
                'name' => $asset->name,
                'category' => $asset->category?->name,
                'type' => 'physical',
                'classification' => $classification,
                'value' => $asset->value ?? 0,
            ];
        });
    }

    /**
     * Classify an asset per information security level.
     */
    private function classifyAsset($asset): string
    {
        if ($asset->value > 100000000) return 'confidential'; // >100jt
        if ($asset->value > 10000000) return 'internal';      // 10jt-100jt
        return 'public';
    }

    /**
     * Create a new risk entry in the ISO 27001 risk register.
     */
    public function createRisk(array $data): IsoRisk
    {
        $riskLevel = $this->calculateRiskLevel($data['likelihood'] ?? 'possible', $data['impact'] ?? 'moderate');
        $riskScore = $this->calculateRiskScore($data['likelihood'] ?? 'possible', $data['impact'] ?? 'moderate');

        return IsoRisk::create([
            'company_id' => auth()->user()?->company_id ?? 1,
            'asset_name' => $data['asset_name'],
            'asset_type' => $data['asset_type'] ?? 'data',
            'asset_description' => $data['asset_description'] ?? null,
            'threat' => $data['threat'],
            'vulnerability' => $data['vulnerability'],
            'likelihood' => $data['likelihood'] ?? 'possible',
            'impact' => $data['impact'] ?? 'moderate',
            'risk_level' => $riskLevel,
            'risk_score' => $riskScore,
            'existing_controls' => $data['existing_controls'] ?? null,
            'treatment' => $data['treatment'] ?? 'mitigate',
            'treatment_plan' => $data['treatment_plan'] ?? null,
            'iso_control_ref' => $data['iso_control_ref'] ?? null,
            'status' => 'open',
            'owner_id' => $data['owner_id'] ?? auth()->id(),
            'review_due' => $data['review_due'] ?? null,
        ]);
    }

    /**
     * Calculate risk level from a 5×5 likelihood/impact matrix.
     * Risk = Likelihood × Impact
     */
    public function calculateRiskLevel(string $likelihood, string $impact): string
    {
        $score = $this->calculateRiskScore($likelihood, $impact);

        return match(true) {
            $score >= 20 => 'critical',
            $score >= 12 => 'high',
            $score >= 6 => 'medium',
            default => 'low',
        };
    }

    /**
     * Calculate numeric risk score (1-25).
     */
    public function calculateRiskScore(string $likelihood, string $impact): int
    {
        $likelihoodScore = match($likelihood) {
            'rare' => 1, 'unlikely' => 2, 'possible' => 3, 'likely' => 4, 'almost_certain' => 5,
            default => 3,
        };

        $impactScore = match($impact) {
            'insignificant' => 1, 'minor' => 2, 'moderate' => 3, 'major' => 4, 'catastrophic' => 5,
            default => 3,
        };

        return $likelihoodScore * $impactScore;
    }

    /**
     * Add risk treatment plan.
     */
    public function addRiskTreatment(IsoRisk $risk, string $treatment, array $controls): void
    {
        $risk->update([
            'treatment' => $treatment,
            'applied_controls' => json_encode($controls),
            'status' => 'in_treatment',
        ]);
    }

    /**
     * Get full risk register.
     */
    public function getRiskRegister(): Collection
    {
        return IsoRisk::with('owner')->orderBy('risk_score', 'desc')->get();
    }

    /**
     * Generate 5×5 risk heatmap data.
     */
    public function getRiskHeatmap(): array
    {
        $risks = IsoRisk::all();
        $heatmap = [];

        $likelihoodLabels = ['rare' => 1, 'unlikely' => 2, 'possible' => 3, 'likely' => 4, 'almost_certain' => 5];
        $impactLabels = ['insignificant' => 1, 'minor' => 2, 'moderate' => 3, 'major' => 4, 'catastrophic' => 5];

        foreach ($likelihoodLabels as $lLabel => $lVal) {
            foreach ($impactLabels as $iLabel => $iVal) {
                $key = "{$lVal}-{$iVal}";
                $heatmap[$key] = [
                    'likelihood' => $lLabel,
                    'impact' => $iLabel,
                    'count' => 0,
                    'risk_level' => $this->calculateRiskLevel($lLabel, $iLabel),
                ];
            }
        }

        foreach ($risks as $risk) {
            $lVal = $likelihoodLabels[$risk->likelihood] ?? 3;
            $iVal = $impactLabels[$risk->impact] ?? 3;
            $key = "{$lVal}-{$iVal}";
            if (isset($heatmap[$key])) {
                $heatmap[$key]['count']++;
            }
        }

        return array_values($heatmap);
    }

    /**
     * Generate Statement of Applicability (SOA) for all 114 Annex A controls.
     */
    public function generateSoa(): array
    {
        $controls = $this->getAnnexAControls();
        $appliedControls = IsoRisk::whereNotNull('iso_control_ref')
            ->pluck('iso_control_ref')
            ->toArray();

        $soa = [];
        foreach ($controls as $ref => $label) {
            $soa[] = [
                'control_ref' => $ref,
                'control_name' => $label,
                'applicable' => true,
                'implemented' => in_array($ref, $appliedControls) ? 'yes' : 'partial',
                'evidence' => in_array($ref, $appliedControls) ? 'Risk register entry exists' : null,
            ];
        }

        return $soa;
    }

    /**
     * ISO 27001:2022 Annex A controls (simplified — full 114 controls).
     */
    private function getAnnexAControls(): array
    {
        return [
            'A.5.1' => 'Kebijakan keamanan informasi',
            'A.5.2' => 'Peran dan tanggung jawab keamanan informasi',
            'A.5.3' => 'Pemisahan tugas',
            'A.5.4' => 'Tanggung jawab manajemen',
            'A.5.5' => 'Kontak dengan otoritas',
            'A.5.6' => 'Kontak dengan kelompok minat khusus',
            'A.5.7' => 'Intelijen ancaman',
            'A.5.8' => 'Keamanan informasi dalam manajemen proyek',
            'A.5.9' => 'Inventaris informasi dan aset terkait',
            'A.5.10' => 'Penggunaan aset yang dapat diterima',
            'A.5.11' => 'Pengembalian aset',
            'A.5.12' => 'Klasifikasi informasi',
            'A.5.13' => 'Pelabelan informasi',
            'A.5.14' => 'Transfer informasi',
            'A.5.15' => 'Kontrol akses',
            'A.5.16' => 'Manajemen identitas',
            'A.5.17' => 'Informasi otentikasi',
            'A.5.18' => 'Hak akses',
            'A.5.19' => 'Keamanan informasi dalam hubungan pemasok',
            'A.5.20' => 'Menangani keamanan informasi dalam perjanjian pemasok',
            'A.5.21' => 'Manajemen keamanan informasi dalam rantai pasokan TIK',
            'A.5.22' => 'Pemantauan, tinjauan, dan manajemen perubahan layanan pemasok',
            'A.5.23' => 'Keamanan informasi untuk penggunaan layanan cloud',
            'A.5.24' => 'Perencanaan dan persiapan manajemen insiden keamanan informasi',
            'A.5.25' => 'Penilaian dan keputusan tentang peristiwa keamanan informasi',
            'A.5.26' => 'Respons terhadap insiden keamanan informasi',
            'A.5.27' => 'Pembelajaran dari insiden keamanan informasi',
            'A.5.28' => 'Pengumpulan bukti',
            'A.5.29' => 'Keamanan informasi selama gangguan',
            'A.5.30' => 'Kesiapan TIK untuk kelangsungan bisnis',
            'A.5.31' => 'Persyaratan hukum, undang-undang, peraturan, dan kontrak',
            'A.5.32' => 'Hak kekayaan intelektual',
            'A.5.33' => 'Perlindungan catatan',
            'A.5.34' => 'Privasi dan perlindungan PII',
            'A.5.35' => 'Tinjauan independen terhadap keamanan informasi',
            'A.5.36' => 'Kepatuhan terhadap kebijakan, aturan, dan standar keamanan informasi',
            'A.5.37' => 'Prosedur operasi terdokumentasi',
            'A.6.1' => 'Penyaringan',
            'A.6.2' => 'Syarat dan ketentuan kerja',
            'A.6.3' => 'Kesadaran, pendidikan, dan pelatihan keamanan informasi',
            'A.6.4' => 'Proses disipliner',
            'A.6.5' => 'Tanggung jawab setelah penghentian atau perubahan kerja',
            'A.6.6' => 'Perjanjian kerahasiaan atau non-disclosure',
            'A.6.7' => 'Kerja jarak jauh',
            'A.6.8' => 'Pelaporan peristiwa keamanan informasi',
            'A.7.1' => 'Perimeter keamanan fisik',
            'A.7.2' => 'Masuk fisik',
            'A.7.3' => 'Mengamankan kantor, ruangan, dan fasilitas',
            'A.7.4' => 'Pemantauan keamanan fisik',
            'A.7.5' => 'Perlindungan terhadap ancaman fisik dan lingkungan',
            'A.7.6' => 'Bekerja di area aman',
            'A.7.7' => 'Meja bersih dan layar bersih',
            'A.7.8' => 'Penempatan dan perlindungan peralatan',
            'A.7.9' => 'Keamanan aset di luar lokasi',
            'A.7.10' => 'Media penyimpanan',
            'A.7.11' => 'Utilitas pendukung',
            'A.7.12' => 'Keamanan kabel',
            'A.7.13' => 'Pemeliharaan peralatan',
            'A.7.14' => 'Pembuangan atau penggunaan ulang peralatan yang aman',
            'A.8.1' => 'Endpoint pengguna',
            'A.8.2' => 'Hak akses istimewa',
            'A.8.3' => 'Pembatasan akses informasi',
            'A.8.4' => 'Akses ke kode sumber',
            'A.8.5' => 'Otentikasi yang aman',
            'A.8.6' => 'Manajemen kapasitas',
            'A.8.7' => 'Perlindungan terhadap malware',
            'A.8.8' => 'Manajemen kerentanan teknis',
            'A.8.9' => 'Manajemen konfigurasi',
            'A.8.10' => 'Penghapusan informasi',
            'A.8.11' => 'Penyembunyian data',
            'A.8.12' => 'Pencegahan kebocoran data',
            'A.8.13' => 'Pencadangan informasi',
            'A.8.14' => 'Redundansi fasilitas pemrosesan informasi',
            'A.8.15' => 'Pencatatan log',
            'A.8.16' => 'Kegiatan pemantauan',
            'A.8.17' => 'Sinkronisasi jam',
            'A.8.18' => 'Penggunaan program utilitas istimewa',
            'A.8.19' => 'Instalasi perangkat lunak pada sistem operasional',
            'A.8.20' => 'Keamanan jaringan',
            'A.8.21' => 'Keamanan layanan jaringan',
            'A.8.22' => 'Pemisahan dalam jaringan',
            'A.8.23' => 'Penyaringan web',
            'A.8.24' => 'Penggunaan kriptografi',
            'A.8.25' => 'Siklus hidup pengembangan yang aman',
            'A.8.26' => 'Persyaratan keamanan aplikasi',
            'A.8.27' => 'Prinsip arsitektur dan rekayasa sistem yang aman',
            'A.8.28' => 'Pengodean yang aman',
            'A.8.29' => 'Pengujian keamanan dalam pengembangan dan penerimaan',
            'A.8.30' => 'Pengembangan yang dialihdayakan',
            'A.8.31' => 'Pemisahan lingkungan pengembangan, pengujian, dan produksi',
            'A.8.32' => 'Manajemen perubahan',
            'A.8.33' => 'Informasi pengujian',
            'A.8.34' => 'Perlindungan sistem informasi selama pengujian audit',
        ];
    }

    /**
     * Create an ISO incident report.
     */
    public function createIncident(array $data): IsoIncident
    {
        $number = 'INC-' . date('Ymd') . '-' . str_pad(
            IsoIncident::whereDate('created_at', today())->count() + 1,
            4, '0', STR_PAD_LEFT
        );

        return IsoIncident::create([
            'company_id' => auth()->user()?->company_id ?? 1,
            'incident_number' => $number,
            'title' => $data['title'],
            'incident_type' => $data['incident_type'],
            'severity' => $data['severity'] ?? 'medium',
            'description' => $data['description'],
            'detected_at' => $data['detected_at'] ?? now(),
            'affected_assets' => $data['affected_assets'] ?? null,
            'affected_systems' => $data['affected_systems'] ?? null,
            'status' => 'open',
            'reported_by' => auth()->id(),
            'reportable_to_regulator' => $data['severity'] === 'critical' || $data['severity'] === 'high',
        ]);
    }

    /**
     * Investigate an incident.
     */
    public function investigateIncident(IsoIncident $incident, array $findings): void
    {
        $incident->update([
            'status' => 'investigating',
            'findings' => $findings['findings'] ?? null,
            'root_cause' => $findings['root_cause'] ?? null,
            'investigated_by' => auth()->id(),
        ]);
    }

    /**
     * Close an incident with corrective actions.
     */
    public function closeIncident(IsoIncident $incident, array $correctiveActions): void
    {
        $incident->update([
            'status' => 'closed',
            'resolved_at' => now(),
            'corrective_actions' => $correctiveActions['corrective_actions'] ?? null,
            'preventive_actions' => $correctiveActions['preventive_actions'] ?? null,
            'closed_by' => auth()->id(),
        ]);
    }

    /**
     * Get pre-built ISO 27001 policy templates.
     */
    public function getPolicyTemplates(): array
    {
        return [
            [
                'category' => 'access_control',
                'title' => 'Kebijakan Kontrol Akses',
                'description' => 'Mengatur prinsip need-to-know, least privilege, dan review akses berkala.',
            ],
            [
                'category' => 'data_classification',
                'title' => 'Kebijakan Klasifikasi Data',
                'description' => 'Klasifikasi data: publik, internal, rahasia, sangat rahasia.',
            ],
            [
                'category' => 'incident_response',
                'title' => 'Rencana Respons Insiden',
                'description' => 'Prosedur deteksi, respons, pemulihan, dan pelaporan insiden keamanan.',
            ],
            [
                'category' => 'acceptable_use',
                'title' => 'Kebijakan Penggunaan yang Dapat Diterima',
                'description' => 'Aturan penggunaan aset TI, email, internet, dan media sosial.',
            ],
            [
                'category' => 'password',
                'title' => 'Kebijakan Kata Sandi',
                'description' => 'Kompleksitas minimum, rotasi, MFA, dan larangan sharing password.',
            ],
            [
                'category' => 'remote_work',
                'title' => 'Kebijakan Kerja Jarak Jauh',
                'description' => 'Keamanan akses remote, VPN, perangkat pribadi (BYOD).',
            ],
            [
                'category' => 'backup',
                'title' => 'Kebijakan Pencadangan dan Pemulihan',
                'description' => 'Frekuensi backup, retention, offsite storage, dan pengujian restore.',
            ],
            [
                'category' => 'vendor_management',
                'title' => 'Kebijakan Manajemen Pemasok',
                'description' => 'Due diligence keamanan, SLA, right-to-audit, dan data processing agreement.',
            ],
        ];
    }

    /**
     * Record policy acknowledgment by employee.
     */
    public function acknowledgePolicy(int $policyId, int $employeeId): void
    {
        \App\Models\IsoPolicyAcknowledgment::updateOrCreate(
            [
                'iso_policy_id' => $policyId,
                'employee_id' => $employeeId,
            ],
            [
                'user_id' => auth()->id(),
                'acknowledged_at' => now(),
                'ip_address' => request()->ip(),
                'signature_type' => 'digital',
            ]
        );
    }

    /**
     * Get policy compliance: who has/hasn't acknowledged each policy.
     */
    public function getPolicyCompliance(): array
    {
        $policies = IsoPolicy::where('status', 'active')->get();
        $allEmployees = \App\Models\Employee::where('status', 'active')->count();
        $result = [];

        foreach ($policies as $policy) {
            $acked = $policy->acknowledgments()->count();
            $result[] = [
                'policy' => $policy->title,
                'policy_number' => $policy->policy_number,
                'total_employees' => $allEmployees,
                'acknowledged' => $acked,
                'not_acknowledged' => $allEmployees - $acked,
                'compliance_percentage' => $allEmployees > 0 ? round(($acked / $allEmployees) * 100, 1) : 0,
            ];
        }

        return $result;
    }

    /**
     * Schedule an internal audit.
     */
    public function scheduleInternalAudit(array $data): IsoAudit
    {
        $number = 'AUD-' . date('Y') . '-' . str_pad(
            IsoAudit::whereYear('created_at', date('Y'))->count() + 1,
            4, '0', STR_PAD_LEFT
        );

        return IsoAudit::create([
            'company_id' => auth()->user()?->company_id ?? 1,
            'audit_number' => $number,
            'title' => $data['title'],
            'audit_type' => $data['audit_type'] ?? 'internal',
            'scope' => $data['scope'],
            'criteria' => $data['criteria'] ?? null,
            'auditor_name' => $data['auditor_name'] ?? null,
            'planned_date' => $data['planned_date'],
            'status' => 'planned',
            'lead_auditor_id' => $data['lead_auditor_id'] ?? auth()->id(),
        ]);
    }

    /**
     * Record audit findings.
     */
    public function recordAuditFindings(IsoAudit $audit, array $findings): void
    {
        foreach ($findings as $finding) {
            $number = 'FND-' . $audit->audit_number . '-' . str_pad(
                $audit->findings()->count() + 1,
                3, '0', STR_PAD_LEFT
            );

            IsoAuditFinding::create([
                'iso_audit_id' => $audit->id,
                'finding_number' => $number,
                'classification' => $finding['classification'] ?? 'observation',
                'iso_clause' => $finding['iso_clause'] ?? null,
                'description' => $finding['description'],
                'evidence' => $finding['evidence'] ?? null,
                'corrective_action' => $finding['corrective_action'] ?? null,
                'responsible_person_id' => $finding['responsible_person_id'] ?? null,
                'target_date' => $finding['target_date'] ?? null,
                'status' => 'open',
            ]);
        }

        $audit->update(['status' => 'in_progress']);
    }

    /**
     * Track corrective actions for an audit.
     */
    public function trackCorrectiveActions(IsoAudit $audit): array
    {
        return $audit->findings()->with('responsiblePerson')->get()->map(function ($finding) {
            return [
                'finding_number' => $finding->finding_number,
                'classification' => $finding->classification,
                'status' => $finding->status,
                'responsible' => $finding->responsiblePerson?->name,
                'target_date' => $finding->target_date?->format('Y-m-d'),
                'closed_date' => $finding->closed_date?->format('Y-m-d'),
                'overdue' => $finding->target_date && $finding->status !== 'closed'
                    && now()->startOfDay()->gt($finding->target_date),
            ];
        })->toArray();
    }
}
