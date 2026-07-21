<?php

namespace App\Services;

use App\Models\CarbonCalculation;
use App\Models\Employee;
use App\Models\EnergyReading;
use App\Models\EsgReport;
use App\Models\EsgTarget;
use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Models\VehicleFuelLog;
use App\Models\WasteRecord;
use App\Models\WaterUsage;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class EsgService
{
    // ──────────────────────────────────────────────
    //  EMISSION FACTORS (kg CO2e per unit)
    // ──────────────────────────────────────────────

    protected const FUEL_FACTORS = [
        'gasoline' => 2.31,       // kg CO2e/liter bensin
        'diesel' => 2.68,         // kg CO2e/liter solar
        'cng' => 2.75,           // kg CO2e/kg CNG
        'lpg' => 1.56,           // kg CO2e/kg LPG
        'biodiesel' => 1.42,     // kg CO2e/liter biodiesel
        'ethanol' => 1.25,       // kg CO2e/liter ethanol
        'jet_fuel' => 2.52,      // kg CO2e/liter avtur
        'coal' => 2.42,          // kg CO2e/kg batu bara
        'natural_gas' => 2.02,   // kg CO2e/m3 gas alam
    ];

    protected const GRID_EMISSION_FACTOR = 0.85; // kg CO2e/kWh (Indonesia, 2024)

    protected const PROCUREMENT_FACTOR = 0.42; // kg CO2e per $1 spend (industry avg)

    protected const TRAVEL_FACTORS = [
        'flight_domestic' => 0.15,   // kg CO2e/passenger-km
        'flight_international' => 0.12,
        'train' => 0.04,
        'bus' => 0.10,
        'taxi' => 0.17,
        'personal_car' => 0.19,
    ];

    protected const WASTE_FACTORS = [
        'landfill' => 0.58,      // kg CO2e/kg waste
        'incinerated' => 0.41,
        'composted' => 0.08,
        'recycled' => 0.02,
        'treated_offsite' => 0.15,
    ];

    // ──────────────────────────────────────────────
    //  SCOPE 1: DIRECT EMISSIONS
    // ──────────────────────────────────────────────

    public function calculateScope1(int $companyId, string $period): array
    {
        [$year, $month] = explode('-', $period);

        $fuelLogs = VehicleFuelLog::whereHas('vehicle', fn($q) => $q->where('company_id', $companyId))
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get();

        $totalEmissions = 0;
        $breakdown = [];

        foreach ($fuelLogs as $log) {
            $fuelType = $log->fuel_type;
            $factor = self::FUEL_FACTORS[$fuelType] ?? self::FUEL_FACTORS['gasoline'];
            $emission = $log->liters * $factor * 0.001; // convert kg to tCO2e
            $totalEmissions += $emission;

            $key = "fuel_{$fuelType}";
            if (!isset($breakdown[$key])) {
                $breakdown[$key] = ['label' => "Bahan Bakar " . ucfirst($fuelType), 'liters' => 0, 'tco2e' => 0, 'factor' => $factor];
            }
            $breakdown[$key]['liters'] += (float) $log->liters;
            $breakdown[$key]['tco2e'] += $emission;
        }

        // Also include stationary combustion (generators, manufacturing) — placeholder for now
        $breakdown['stationary'] = [
            'label' => 'Pembakaran Stasioner',
            'tco2e' => 0,
            'note' => 'Data dari mesin/manufaktur (jika ada)',
        ];

        $sourceData = [
            'fuel_logs_count' => $fuelLogs->count(),
            'vehicles_tracked' => $fuelLogs->pluck('vehicle_id')->unique()->count(),
            'period' => $period,
        ];

        $emissionFactorsUsed = array_intersect_key(self::FUEL_FACTORS, array_flip($fuelLogs->pluck('fuel_type')->unique()->toArray()));

        $this->saveCalculation($companyId, $period, 'scope1', $totalEmissions, $breakdown, $emissionFactorsUsed, $sourceData);

        return [
            'scope' => 'scope1',
            'label' => 'Cakupan 1 - Emisi Langsung',
            'total_tco2e' => round($totalEmissions, 4),
            'breakdown' => array_values($breakdown),
            'source_data' => $sourceData,
        ];
    }

    // ──────────────────────────────────────────────
    //  SCOPE 2: INDIRECT EMISSIONS (ELECTRICITY)
    // ──────────────────────────────────────────────

    public function calculateScope2(int $companyId, string $period): array
    {
        [$year, $month] = explode('-', $period);

        $readings = EnergyReading::where('company_id', $companyId)
            ->whereYear('recorded_at', $year)
            ->whereMonth('recorded_at', $month)
            ->get();

        $totalKwh = 0;
        $breakdown = [];
        $byLocation = [];

        foreach ($readings as $reading) {
            $meter = $reading->meter;
            $location = $meter?->location ?? 'Tidak Diketahui';

            if (!isset($byLocation[$location])) {
                $byLocation[$location] = ['kwh' => 0, 'tco2e' => 0, 'meter_count' => 0];
            }
            $byLocation[$location]['kwh'] += (float) $reading->kwh;
            $byLocation[$location]['meter_count']++;
        }

        foreach ($byLocation as $location => $data) {
            $emission = $data['kwh'] * self::GRID_EMISSION_FACTOR * 0.001;
            $totalKwh += $data['kwh'];
            $breakdown[] = [
                'location' => $location,
                'kwh' => round($data['kwh'], 2),
                'tco2e' => round($emission, 4),
                'grid_factor' => self::GRID_EMISSION_FACTOR,
            ];
        }

        $totalEmissions = $totalKwh * self::GRID_EMISSION_FACTOR * 0.001;

        $sourceData = [
            'readings_count' => $readings->count(),
            'meters_tracked' => $readings->pluck('energy_meter_id')->unique()->count(),
            'total_kwh' => round($totalKwh, 2),
            'period' => $period,
        ];

        $emissionFactorsUsed = ['grid_electricity_indonesia' => self::GRID_EMISSION_FACTOR];

        $this->saveCalculation($companyId, $period, 'scope2', $totalEmissions, $breakdown, $emissionFactorsUsed, $sourceData);

        return [
            'scope' => 'scope2',
            'label' => 'Cakupan 2 - Emisi Tidak Langsung (Listrik)',
            'total_tco2e' => round($totalEmissions, 4),
            'total_kwh' => round($totalKwh, 2),
            'breakdown' => $breakdown,
            'source_data' => $sourceData,
        ];
    }

    // ──────────────────────────────────────────────
    //  SCOPE 3: SUPPLY CHAIN & OTHER INDIRECT
    // ──────────────────────────────────────────────

    public function calculateScope3(int $companyId, string $period): array
    {
        [$year, $month] = explode('-', $period);

        $breakdown = [];
        $totalEmissions = 0;

        // 1. Procurement spend
        $poTotal = PurchaseOrder::whereHas('company', fn($q) => $q->where('id', $companyId))
            ->whereYear('order_date', $year)
            ->whereMonth('order_date', $month)
            ->whereIn('status', ['approved', 'received', 'completed'])
            ->sum('total');

        $procurementEmission = ($poTotal * 0.0000625) * self::PROCUREMENT_FACTOR; // assume ~$1 = Rp16,000, rough conversion
        $totalEmissions += $procurementEmission;
        $breakdown['procurement'] = [
            'label' => 'Pengadaan Barang & Jasa',
            'spend_idr' => round($poTotal, 2),
            'tco2e' => round($procurementEmission, 4),
            'factor' => self::PROCUREMENT_FACTOR . ' kg CO2e/USD',
        ];

        // 2. Waste disposal emissions
        $wasteRecords = WasteRecord::where('company_id', $companyId)
            ->whereYear('record_date', $year)
            ->whereMonth('record_date', $month)
            ->get();

        $wasteEmission = 0;
        foreach ($wasteRecords as $waste) {
            $factor = self::WASTE_FACTORS[$waste->disposal_method] ?? self::WASTE_FACTORS['landfill'];
            $wasteEmission += $waste->quantity_kg * $factor * 0.001;
        }
        $totalEmissions += $wasteEmission;
        $breakdown['waste'] = [
            'label' => 'Pembuangan Limbah',
            'total_kg' => round($wasteRecords->sum('quantity_kg'), 2),
            'tco2e' => round($wasteEmission, 4),
            'records' => $wasteRecords->count(),
        ];

        // 3. Water supply emissions
        $waterUsage = WaterUsage::where('company_id', $companyId)
            ->whereYear('record_date', $year)
            ->whereMonth('record_date', $month)
            ->sum('quantity_m3');

        $waterEmission = $waterUsage * 0.344 * 0.001; // 0.344 kg CO2e/m3 (water supply & treatment)
        $totalEmissions += $waterEmission;
        $breakdown['water_supply'] = [
            'label' => 'Pengadaan & Pengolahan Air',
            'total_m3' => round($waterUsage, 2),
            'tco2e' => round($waterEmission, 4),
        ];

        // 4. Employee commute (estimate)
        $employeeCount = Employee::where('company_id', $companyId)
            ->where('status', 'active')
            ->count();
        $workingDays = 22; // average per month
        $avgCommuteKm = 20; // round trip
        $commuteEmission = $employeeCount * $workingDays * $avgCommuteKm * self::TRAVEL_FACTORS['personal_car'] * 0.001;
        $totalEmissions += $commuteEmission;
        $breakdown['employee_commute'] = [
            'label' => 'Komuter Karyawan',
            'employees' => $employeeCount,
            'estimated_km' => $employeeCount * $workingDays * $avgCommuteKm,
            'tco2e' => round($commuteEmission, 4),
            'assumption' => 'Estimasi 20km PP, 22 hari kerja',
        ];

        $sourceData = [
            'period' => $period,
            'procurement_po_count' => PurchaseOrder::where('company_id', $companyId)->whereYear('order_date', $year)->whereMonth('order_date', $month)->count(),
            'waste_records' => $wasteRecords->count(),
            'employee_count' => $employeeCount,
        ];

        $emissionFactorsUsed = [
            'procurement_per_usd' => self::PROCUREMENT_FACTOR,
            'water_supply_per_m3' => 0.344,
            'employee_commute_per_km' => self::TRAVEL_FACTORS['personal_car'],
        ];

        $this->saveCalculation($companyId, $period, 'scope3', $totalEmissions, $breakdown, $emissionFactorsUsed, $sourceData);

        return [
            'scope' => 'scope3',
            'label' => 'Cakupan 3 - Emisi Rantai Nilai',
            'total_tco2e' => round($totalEmissions, 4),
            'breakdown' => array_values($breakdown),
            'source_data' => $sourceData,
        ];
    }

    // ──────────────────────────────────────────────
    //  TOTAL CARBON FOOTPRINT
    // ──────────────────────────────────────────────

    public function getTotalCarbonFootprint(int $companyId, string $period): array
    {
        $scope1 = $this->getStoredCalculation($companyId, $period, 'scope1');
        $scope2 = $this->getStoredCalculation($companyId, $period, 'scope2');
        $scope3 = $this->getStoredCalculation($companyId, $period, 'scope3');

        $s1 = $scope1?->emissions_tco2e ?? 0;
        $s2 = $scope2?->emissions_tco2e ?? 0;
        $s3 = $scope3?->emissions_tco2e ?? 0;

        $total = $s1 + $s2 + $s3;

        // Get previous period for trend
        $prevPeriod = $this->getPreviousPeriod($period);
        $prevTotal = CarbonCalculation::where('company_id', $companyId)
            ->where('period', $prevPeriod)
            ->where('scope', 'total')
            ->value('emissions_tco2e') ?? 0;

        $trend = $prevTotal > 0 ? (($total - $prevTotal) / $prevTotal) * 100 : 0;

        // Save total calculation
        $breakdown = [
            'scope1' => $s1,
            'scope2' => $s2,
            'scope3' => $s3,
        ];
        $this->saveCalculation($companyId, $period, 'total', $total, $breakdown, [], ['trend_vs_prev' => $trend]);

        return [
            'scope1_tco2e' => round($s1, 4),
            'scope2_tco2e' => round($s2, 4),
            'scope3_tco2e' => round($s3, 4),
            'total_tco2e' => round($total, 4),
            'offset_credits' => 0,
            'net_emissions' => round($total, 4),
            'trend_vs_last_period_percent' => round($trend, 2),
            'trend_direction' => $trend > 0 ? 'up' : ($trend < 0 ? 'down' : 'stable'),
            'period' => $period,
            'previous_period' => $prevPeriod,
            'intensity_per_employee' => $this->getEmissionIntensity($companyId, $total),
            'intensity_per_revenue' => $this->getEmissionIntensityByRevenue($companyId, $period, $total),
        ];
    }

    // ──────────────────────────────────────────────
    //  REDUCTION SUGGESTIONS
    // ──────────────────────────────────────────────

    public function suggestReduction(int $companyId): array
    {
        $latest = CarbonCalculation::where('company_id', $companyId)
            ->where('scope', 'total')
            ->latest('period')
            ->first();

        if (!$latest) {
            return [];
        }

        $breakdown = $latest->breakdown ?? [];
        $suggestions = [];

        // Solar panel suggestion
        $s2 = $breakdown['scope2'] ?? 0;
        if ($s2 > 0) {
            $suggestions[] = [
                'title' => 'Beralih ke Panel Surya',
                'description' => 'Pasang panel surya atap untuk mengurangi konsumsi listrik grid. Potensi reduksi 30-50% emisi Scope 2.',
                'current_tco2e' => round($s2, 4),
                'potential_reduction_tco2e' => round($s2 * 0.40, 4),
                'reduction_percent' => 40,
                'roi_estimate' => '3-7 tahun tergantung kapasitas',
                'difficulty' => 'medium',
                'category' => 'scope2',
            ];
        }

        // EV transition
        $s1 = $breakdown['scope1'] ?? 0;
        if ($s1 > 0) {
            $fuelLogs = VehicleFuelLog::whereHas('vehicle', fn($q) => $q->where('company_id', $companyId))
                ->whereYear('date', now()->year)
                ->get();

            if ($fuelLogs->isNotEmpty()) {
                $suggestions[] = [
                    'title' => 'Transisi ke Kendaraan Listrik (EV)',
                    'description' => 'Ganti kendaraan operasional bensin/solar dengan EV. Potensi reduksi 15-30% emisi Scope 1.',
                    'current_tco2e' => round($s1, 4),
                    'potential_reduction_tco2e' => round($s1 * 0.25, 4),
                    'reduction_percent' => 25,
                    'roi_estimate' => '4-8 tahun tergantung jarak tempuh',
                    'difficulty' => 'high',
                    'category' => 'scope1',
                ];

                $suggestions[] = [
                    'title' => 'Optimasi Rute & Eco-Driving',
                    'description' => 'Terapkan pelatihan eco-driving + optimasi rute untuk mengurangi konsumsi BBM 5-10%.',
                    'current_tco2e' => round($s1, 4),
                    'potential_reduction_tco2e' => round($s1 * 0.08, 4),
                    'reduction_percent' => 8,
                    'roi_estimate' => 'Segera - hanya biaya pelatihan',
                    'difficulty' => 'low',
                    'category' => 'scope1',
                ];
            }
        }

        // Waste recycling
        $suggestions[] = [
            'title' => 'Program Daur Ulang Limbah',
            'description' => 'Pilah dan daur ulang limbah kantor/produksi. Targetkan 50%+ recycling rate untuk mengurangi emisi TPA.',
            'current_tco2e' => 'Tergantung volume limbah',
            'potential_reduction_tco2e' => 'Bervariasi',
            'reduction_percent' => 15,
            'roi_estimate' => '1-6 bulan setelah implementasi',
            'difficulty' => 'low',
            'category' => 'scope3',
        ];

        // Digital transformation
        $suggestions[] = [
            'title' => 'Digitalisasi & Paperless',
            'description' => 'Kurangi konsumsi kertas, kurangi perjalanan bisnis dengan video conference. Reduksi Scope 3.',
            'current_tco2e' => 'Tergantung penggunaan',
            'potential_reduction_tco2e' => 'Bervariasi',
            'reduction_percent' => 10,
            'roi_estimate' => '1-3 bulan',
            'difficulty' => 'low',
            'category' => 'scope3',
        ];

        // Energy efficiency
        $suggestions[] = [
            'title' => 'Efisiensi Energi Gedung',
            'description' => 'Ganti lampu ke LED, pasang sensor gerak, optimasi AC. Potensi reduksi 10-20% konsumsi listrik.',
            'current_tco2e' => round($s2, 4),
            'potential_reduction_tco2e' => round($s2 * 0.15, 4),
            'reduction_percent' => 15,
            'roi_estimate' => '1-3 tahun',
            'difficulty' => 'medium',
            'category' => 'scope2',
        ];

        return $suggestions;
    }

    // ──────────────────────────────────────────────
    //  WASTE TRACKING
    // ──────────────────────────────────────────────

    public function recordWaste(array $data): WasteRecord
    {
        return WasteRecord::create($data);
    }

    public function getWasteStats(int $companyId, string $period): array
    {
        [$year, $month] = explode('-', $period);

        $records = WasteRecord::where('company_id', $companyId)
            ->whereYear('record_date', $year)
            ->whereMonth('record_date', $month)
            ->get();

        $totalWaste = $records->sum('quantity_kg');
        $recycled = $records->where('disposal_method', 'recycled')->sum('quantity_kg');
        $landfilled = $records->where('disposal_method', 'landfill')->sum('quantity_kg');
        $hazardous = $records->where('is_hazardous', true)->sum('quantity_kg');
        $hazardousTreated = $records->where('is_hazardous', true)
            ->where('disposal_method', 'treated_offsite')->sum('quantity_kg');

        // Previous period trend
        $prevPeriod = $this->getPreviousPeriod($period);
        [$prevYear, $prevMonth] = explode('-', $prevPeriod);
        $prevTotal = WasteRecord::where('company_id', $companyId)
            ->whereYear('record_date', $prevYear)
            ->whereMonth('record_date', $prevMonth)
            ->sum('quantity_kg');

        $trend = $prevTotal > 0 ? (($totalWaste - $prevTotal) / $prevTotal) * 100 : 0;

        return [
            'total_waste_kg' => round($totalWaste, 2),
            'recycled_kg' => round($recycled, 2),
            'recycled_percent' => $totalWaste > 0 ? round(($recycled / $totalWaste) * 100, 1) : 0,
            'landfilled_kg' => round($landfilled, 2),
            'landfilled_percent' => $totalWaste > 0 ? round(($landfilled / $totalWaste) * 100, 1) : 0,
            'hazardous_kg' => round($hazardous, 2),
            'hazardous_treated_percent' => $hazardous > 0 ? round(($hazardousTreated / $hazardous) * 100, 1) : 0,
            'trend_percent' => round($trend, 2),
            'records_count' => $records->count(),
        ];
    }

    // ──────────────────────────────────────────────
    //  WATER MANAGEMENT
    // ──────────────────────────────────────────────

    public function recordWaterUsage(array $data): WaterUsage
    {
        return WaterUsage::create($data);
    }

    public function getWaterStats(int $companyId, string $period): array
    {
        [$year, $month] = explode('-', $period);

        $records = WaterUsage::where('company_id', $companyId)
            ->whereYear('record_date', $year)
            ->whereMonth('record_date', $month)
            ->get();

        $totalM3 = $records->sum('quantity_m3');
        $recycledM3 = $records->where('is_recycled', true)->sum('quantity_m3');
        $municipalM3 = $records->where('source', 'municipal')->sum('quantity_m3');
        $wellM3 = $records->where('source', 'well')->sum('quantity_m3');
        $rainwaterM3 = $records->where('source', 'rainwater')->sum('quantity_m3');

        return [
            'total_water_m3' => round($totalM3, 2),
            'recycled_m3' => round($recycledM3, 2),
            'recycled_percent' => $totalM3 > 0 ? round(($recycledM3 / $totalM3) * 100, 1) : 0,
            'municipal_m3' => round($municipalM3, 2),
            'well_m3' => round($wellM3, 2),
            'rainwater_m3' => round($rainwaterM3, 2),
            'total_cost' => round($records->sum('cost'), 2),
            'records_count' => $records->count(),
        ];
    }

    // ──────────────────────────────────────────────
    //  SOCIAL IMPACT METRICS
    // ──────────────────────────────────────────────

    public function getSocialMetrics(int $companyId): array
    {
        $employees = Employee::where('company_id', $companyId)
            ->where('status', 'active')
            ->get();

        $total = $employees->count();

        if ($total === 0) {
            return $this->emptySocialMetrics();
        }

        // Gender diversity
        $male = $employees->where('gender', 'male')->count();
        $female = $employees->where('gender', 'female')->count();

        // Age groups
        $ageGroups = [
            'under_25' => $employees->filter(fn($e) => $e->birth_date && $e->birth_date->age < 25)->count(),
            '25_34' => $employees->filter(fn($e) => $e->birth_date && $e->birth_date->age >= 25 && $e->birth_date->age <= 34)->count(),
            '35_44' => $employees->filter(fn($e) => $e->birth_date && $e->birth_date->age >= 35 && $e->birth_date->age <= 44)->count(),
            '45_54' => $employees->filter(fn($e) => $e->birth_date && $e->birth_date->age >= 45 && $e->birth_date->age <= 54)->count(),
            '55_plus' => $employees->filter(fn($e) => $e->birth_date && $e->birth_date->age >= 55)->count(),
        ];

        // Employment type
        $permanent = $employees->where('employee_type', 'permanent')->count();
        $contract = $employees->where('employee_type', 'contract')->count();
        $probation = $employees->where('employee_type', 'probation')->count();

        // Turnover (this year)
        $thisYearTurnover = Employee::where('company_id', $companyId)
            ->whereYear('termination_date', now()->year)
            ->count();

        $startOfYear = Employee::where('company_id', $companyId)
            ->whereDate('join_date', '<=', now()->startOfYear())
            ->count();

        $turnoverRate = $startOfYear > 0 ? ($thisYearTurnover / (($startOfYear + $total) / 2)) * 100 : 0;

        return [
            'diversity' => [
                'gender' => [
                    'male_percent' => round(($male / $total) * 100, 1),
                    'female_percent' => round(($female / $total) * 100, 1),
                    'male_count' => $male,
                    'female_count' => $female,
                ],
                'age_groups' => $ageGroups,
                'employment_type' => [
                    'permanent_percent' => round(($permanent / $total) * 100, 1),
                    'contract_percent' => round(($contract / $total) * 100, 1),
                    'probation_percent' => round(($probation / $total) * 100, 1),
                ],
            ],
            'turnover' => [
                'ytd_terminations' => $thisYearTurnover,
                'annualized_rate_percent' => round($turnoverRate, 1),
            ],
            'training' => [
                'avg_hours_per_employee' => 0,
                'total_investment' => 0,
                'note' => 'Integrasi dengan modul LMS diperlukan untuk data pelatihan akurat',
            ],
            'compensation' => [
                'gender_pay_gap_percent' => round($this->calculateGenderPayGap($employees), 1),
                'avg_salary' => round($employees->avg('basic_salary') ?? 0, 2),
                'note' => 'Gap gaji gender dihitung dari basic_salary',
            ],
            'community' => [
                'csr_spend' => 0,
                'volunteer_hours' => 0,
                'note' => 'Data CSR dapat diinput melalui modul komunitas',
            ],
            'total_employees' => $total,
        ];
    }

    // ──────────────────────────────────────────────
    //  GOVERNANCE METRICS
    // ──────────────────────────────────────────────

    public function getGovernanceMetrics(int $companyId): array
    {
        return [
            'board' => [
                'size' => 0,
                'independent_percent' => 0,
                'female_percent' => 0,
                'note' => 'Data dewan direksi dapat diinput manual',
            ],
            'compliance' => [
                'policy_violations' => 0,
                'fines' => 0,
                'legal_cases' => 0,
                'regulatory_audits_passed' => 0,
            ],
            'ethics' => [
                'whistleblower_reports' => 0,
                'whistleblower_reports_resolved' => 0,
                'code_of_conduct_acknowledgment_percent' => 0,
            ],
            'data_privacy' => [
                'breaches_ytd' => \App\Models\DataBreach::where('company_id', $companyId)
                    ->whereYear('created_at', now()->year)->count(),
                'complaints_resolved_percent' => 0,
                'dpia_completed' => \App\Models\DpiaAssessment::where('company_id', $companyId)->count(),
            ],
            'risk_management' => [
                'risks_identified' => \App\Models\IsoRisk::where('company_id', $companyId)->count() ?? 0,
                'risks_mitigated' => 0,
            ],
        ];
    }

    // ──────────────────────────────────────────────
    //  ESG REPORT GENERATOR
    // ──────────────────────────────────────────────

    public function generateEsgReport(int $companyId, string $period, string $framework = 'gri'): string
    {
        $company = \App\Models\Company::find($companyId);

        $carbon = $this->getTotalCarbonFootprint($companyId, $period);
        $social = $this->getSocialMetrics($companyId);
        $governance = $this->getGovernanceMetrics($companyId);
        $waste = $this->getWasteStats($companyId, $period);
        $water = $this->getWaterStats($companyId, $period);
        $targets = EsgTarget::where('company_id', $companyId)->get();
        $suggestions = $this->suggestReduction($companyId);

        $reportData = [
            'company' => $company->toArray(),
            'framework' => $framework,
            'framework_label' => match ($framework) {
                'gri' => 'GRI Standards',
                'pojk_51' => 'POJK 51/2017',
                'ifrs_s1_s2' => 'IFRS S1-S2',
                'sasb' => 'SASB Standards',
                default => $framework,
            },
            'period' => $period,
            'period_label' => $this->getPeriodLabel($period),
            'generated_at' => now()->toDateTimeString(),
            'carbon' => $carbon,
            'social' => $social,
            'governance' => $governance,
            'waste' => $waste,
            'water' => $water,
            'esg_score' => $this->getEsgScore($companyId),
            'targets' => $targets->toArray(),
            'suggestions' => $suggestions,
        ];

        // Generate PDF
        $filename = "esg_report_{$company->slug}_{$period}.pdf";
        $path = "esg_reports/{$filename}";

        $pdf = Pdf::loadView('pdf.esg-report', $reportData);
        Storage::disk('public')->put($path, $pdf->output());

        // Save report record
        EsgReport::create([
            'company_id' => $companyId,
            'title' => "Laporan ESG {$this->getPeriodLabel($period)}",
            'period' => $period,
            'period_start' => Carbon::createFromFormat('Y-m', $period)->startOfMonth()->toDateString(),
            'period_end' => Carbon::createFromFormat('Y-m', $period)->endOfMonth()->toDateString(),
            'framework' => $framework,
            'status' => 'published',
            'file_path' => $path,
            'report_data' => $reportData,
            'scores' => $reportData['esg_score'],
            'executive_summary' => $this->generateExecutiveSummary($reportData),
            'prepared_by' => auth()->user()?->name,
            'published_at' => now(),
        ]);

        return Storage::disk('public')->path($path);
    }

    // ──────────────────────────────────────────────
    //  ESG SCORE
    // ──────────────────────────────────────────────

    public function getEsgScore(int $companyId): array
    {
        $latestPeriod = now()->format('Y-m');
        $carbon = CarbonCalculation::where('company_id', $companyId)
            ->where('period', $latestPeriod)
            ->where('scope', 'total')
            ->first();

        $social = $this->getSocialMetrics($companyId);
        $governance = $this->getGovernanceMetrics($companyId);

        // Environmental score (0-100)
        $envScore = 50;
        if ($carbon) {
            $envScore = 70; // Has data, default good
            $envScore -= min(20, $carbon->emissions_tco2e * 2); // Higher emissions = lower score
            $envScore = max(10, $envScore);
        }

        // Social score (0-100)
        $socialScore = 50;
        $femalePct = $social['diversity']['gender']['female_percent'] ?? 0;
        $socialScore += ($femalePct >= 30 ? 15 : ($femalePct >= 20 ? 10 : 0));
        $socialScore += ($social['turnover']['annualized_rate_percent'] < 10 ? 15 : 5);
        $socialScore = min(100, $socialScore);

        // Governance score (0-100)
        $govScore = 50;
        $breaches = $governance['data_privacy']['breaches_ytd'] ?? 0;
        $govScore -= min(20, $breaches * 10);
        $dpia = $governance['data_privacy']['dpia_completed'] ?? 0;
        $govScore += ($dpia > 0 ? 15 : 0);
        $govScore = max(10, min(100, $govScore));

        $total = round(($envScore + $socialScore + $govScore) / 3, 1);

        return [
            'total_score' => $total,
            'environmental_score' => round($envScore, 1),
            'social_score' => round($socialScore, 1),
            'governance_score' => round($govScore, 1),
            'grade' => $total >= 80 ? 'A' : ($total >= 60 ? 'B' : ($total >= 40 ? 'C' : 'D')),
            'peer_comparison_percentile' => min(95, max(5, round($total * 0.95))),
            'calculated_at' => now()->toDateTimeString(),
        ];
    }

    // ──────────────────────────────────────────────
    //  TARGET TRACKING
    // ──────────────────────────────────────────────

    public function setTarget(string $category, string $metric, float $targetValue, Carbon $deadline, array $extra = []): EsgTarget
    {
        return EsgTarget::create(array_merge([
            'category' => $category,
            'metric' => $metric,
            'target_value' => $targetValue,
            'deadline' => $deadline,
            'status' => 'on_track',
        ], $extra));
    }

    public function getTargetProgress(int $companyId): array
    {
        return EsgTarget::where('company_id', $companyId)
            ->get()
            ->map(fn($t) => [
                'id' => $t->id,
                'category' => $t->category,
                'metric' => $t->metric,
                'metric_label' => $t->metric_label,
                'unit' => $t->unit,
                'baseline' => (float) $t->baseline_value,
                'target' => (float) $t->target_value,
                'current' => (float) $t->current_value,
                'progress_percent' => round($t->progress_percent, 1),
                'on_track' => $t->on_track,
                'deadline' => $t->deadline->format('Y-m-d'),
                'status' => $t->status,
            ])
            ->toArray();
    }

    // ──────────────────────────────────────────────
    //  HELPERS
    // ──────────────────────────────────────────────

    protected function getStoredCalculation(int $companyId, string $period, string $scope): ?CarbonCalculation
    {
        return CarbonCalculation::where('company_id', $companyId)
            ->where('period', $period)
            ->where('scope', $scope)
            ->first();
    }

    protected function saveCalculation(int $companyId, string $period, string $scope, float $emissions, array $breakdown, array $factors, array $sourceData): void
    {
        CarbonCalculation::updateOrCreate(
            ['company_id' => $companyId, 'period' => $period, 'scope' => $scope],
            [
                'emissions_tco2e' => $emissions,
                'breakdown' => $breakdown,
                'emission_factors_used' => $factors,
                'source_data' => $sourceData,
            ]
        );
    }

    protected function getPreviousPeriod(string $period): string
    {
        [$year, $month] = explode('-', $period);
        $carbon = Carbon::createFromDate((int) $year, (int) $month, 1)->subMonth();
        return $carbon->format('Y-m');
    }

    protected function getPeriodLabel(string $period): string
    {
        [$year, $month] = explode('-', $period);
        $carbon = Carbon::createFromDate((int) $year, (int) $month, 1);
        return $carbon->translatedFormat('F Y');
    }

    protected function getEmissionIntensity(int $companyId, float $totalEmissions): array
    {
        $employeeCount = Employee::where('company_id', $companyId)->where('status', 'active')->count();
        return [
            'tco2e_per_employee' => $employeeCount > 0 ? round($totalEmissions / $employeeCount, 4) : 0,
            'employee_count' => $employeeCount,
        ];
    }

    protected function getEmissionIntensityByRevenue(int $companyId, string $period, float $totalEmissions): array
    {
        [$year, $month] = explode('-', $period);
        $revenue = Invoice::where('company_id', $companyId)
            ->where('status', 'paid')
            ->whereYear('invoice_date', $year)
            ->whereMonth('invoice_date', $month)
            ->sum('total');

        return [
            'tco2e_per_million_idr' => $revenue > 0 ? round(($totalEmissions / $revenue) * 1000000, 4) : 0,
            'revenue_idr' => round($revenue, 2),
        ];
    }

    protected function calculateGenderPayGap(Collection $employees): float
    {
        $maleAvg = $employees->where('gender', 'male')->avg('basic_salary') ?? 0;
        $femaleAvg = $employees->where('gender', 'female')->avg('basic_salary') ?? 0;

        if ($maleAvg == 0) return 0;

        return (($maleAvg - $femaleAvg) / $maleAvg) * 100;
    }

    protected function emptySocialMetrics(): array
    {
        return [
            'diversity' => [
                'gender' => ['male_percent' => 0, 'female_percent' => 0, 'male_count' => 0, 'female_count' => 0],
                'age_groups' => ['under_25' => 0, '25_34' => 0, '35_44' => 0, '45_54' => 0, '55_plus' => 0],
                'employment_type' => ['permanent_percent' => 0, 'contract_percent' => 0, 'probation_percent' => 0],
            ],
            'turnover' => ['ytd_terminations' => 0, 'annualized_rate_percent' => 0],
            'training' => ['avg_hours_per_employee' => 0, 'total_investment' => 0, 'note' => ''],
            'compensation' => ['gender_pay_gap_percent' => 0, 'avg_salary' => 0, 'note' => ''],
            'community' => ['csr_spend' => 0, 'volunteer_hours' => 0, 'note' => ''],
            'total_employees' => 0,
        ];
    }

    protected function generateExecutiveSummary(array $data): string
    {
        $carbon = $data['carbon'];
        $score = $data['esg_score'];
        $trendSymbol = $carbon['trend_direction'] === 'down' ? 'menurun' : 'meningkat';

        return "Laporan ESG {$data['period_label']} untuk {$data['company']['name']}. "
            . "Total jejak karbon: {$carbon['total_tco2e']} tCO2e ({$trendSymbol} {$carbon['trend_vs_last_period_percent']}% vs periode sebelumnya). "
            . "Skor ESG: {$score['total_score']}/100 (Grade {$score['grade']}). "
            . "Cakupan 1: {$carbon['scope1_tco2e']} tCO2e, Cakupan 2: {$carbon['scope2_tco2e']} tCO2e, Cakupan 3: {$carbon['scope3_tco2e']} tCO2e. "
            . "Kerangka: {$data['framework_label']}.";
    }
}
