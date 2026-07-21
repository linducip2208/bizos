<?php

namespace App\Services;

use App\Models\EnergyMeter;
use App\Models\EnergyReading;
use App\Models\IotAlert;
use App\Models\IotDevice;
use App\Models\IotReading;
use App\Models\RfidTag;
use App\Models\ScaleReading;
use App\Models\SmartScale;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IotService
{
    public function registerDevice(array $data): IotDevice
    {
        $data['device_token'] = $data['device_token'] ?? bin2hex(random_bytes(32));
        $data['status'] = $data['status'] ?? 'offline';
        $data['is_active'] = $data['is_active'] ?? true;

        return IotDevice::create($data);
    }

    public function updateDeviceStatus(IotDevice $device, string $status, ?Carbon $lastSeen = null, ?float $battery = null): void
    {
        $device->update([
            'status' => $status,
            'last_seen_at' => $lastSeen ?? now(),
            'battery_level' => $battery ?? $device->battery_level,
        ]);
    }

    public function getDeviceReadings(IotDevice $device, ?Carbon $from = null, ?Carbon $to = null): Collection
    {
        $query = $device->readings()->recent();

        if ($from) {
            $query->where('recorded_at', '>=', $from);
        }
        if ($to) {
            $query->where('recorded_at', '<=', $to);
        }

        return $query->get();
    }

    public function getDeviceStats(IotDevice $device, ?int $hours = 24): array
    {
        $since = now()->subHours($hours);
        $readings = $device->readings()->where('recorded_at', '>=', $since)->get();

        if ($readings->isEmpty()) {
            return ['count' => 0, 'min' => null, 'max' => null, 'avg' => null, 'latest' => null];
        }

        $temps = $readings->filter(fn($r) => $r->temperature_celsius !== null)->pluck('temperature_celsius');
        $humidities = $readings->filter(fn($r) => $r->humidity_percent !== null)->pluck('humidity_percent');
        $vibrations = $readings->filter(fn($r) => $r->vibration_mm_s !== null)->pluck('vibration_mm_s');

        return [
            'count' => $readings->count(),
            'temperature' => [
                'min' => $temps->isNotEmpty() ? round($temps->min(), 2) : null,
                'max' => $temps->isNotEmpty() ? round($temps->max(), 2) : null,
                'avg' => $temps->isNotEmpty() ? round($temps->avg(), 2) : null,
                'latest' => $readings->first()?->temperature_celsius,
            ],
            'humidity' => [
                'min' => $humidities->isNotEmpty() ? round($humidities->min(), 2) : null,
                'max' => $humidities->isNotEmpty() ? round($humidities->max(), 2) : null,
                'avg' => $humidities->isNotEmpty() ? round($humidities->avg(), 2) : null,
                'latest' => $readings->first()?->humidity_percent,
            ],
            'vibration' => [
                'min' => $vibrations->isNotEmpty() ? round($vibrations->min(), 4) : null,
                'max' => $vibrations->isNotEmpty() ? round($vibrations->max(), 4) : null,
                'avg' => $vibrations->isNotEmpty() ? round($vibrations->avg(), 4) : null,
                'latest' => $readings->first()?->vibration_mm_s,
            ],
            'latest' => [
                'battery' => $readings->first()?->battery_level,
                'signal' => $readings->first()?->signal_strength_dbm,
                'recorded_at' => $readings->first()?->recorded_at?->toISOString(),
            ],
        ];
    }

    public function handleMessage(string $deviceToken, array $payload): void
    {
        $device = IotDevice::where('device_token', $deviceToken)->first();

        if (!$device) {
            Log::warning('IoT: Pesan dari device tidak dikenal', ['token' => $deviceToken]);
            return;
        }

        $reading = IotReading::create([
            'company_id' => $device->company_id,
            'iot_device_id' => $device->id,
            'temperature_celsius' => $payload['temperature'] ?? null,
            'humidity_percent' => $payload['humidity'] ?? null,
            'vibration_mm_s' => $payload['vibration'] ?? null,
            'pressure_hpa' => $payload['pressure'] ?? null,
            'battery_level' => $payload['battery'] ?? null,
            'signal_strength_dbm' => $payload['signal'] ?? null,
            'raw_payload' => $payload,
            'recorded_at' => $payload['timestamp'] ?? now(),
        ]);

        $this->updateDeviceStatus(
            $device,
            'online',
            $payload['timestamp'] ?? now(),
            $payload['battery'] ?? null
        );

        $this->checkAlerts($device, $payload);
    }

    public function trainPredictiveModel(IotDevice $device): void
    {
        $readings = $device->readings()
            ->where('recorded_at', '>=', now()->subDays(90))
            ->orderBy('recorded_at')
            ->get();

        if ($readings->count() < 30) {
            return;
        }

        $temps = $readings->pluck('temperature_celsius')->filter()->values();
        $vibs = $readings->pluck('vibration_mm_s')->filter()->values();

        $config = $device->config ?? [];

        if ($temps->count() >= 30) {
            $meanTemp = $temps->avg();
            $stdTemp = $this->standardDeviation($temps->toArray());

            $config['temperature_model'] = [
                'mean' => round($meanTemp, 2),
                'std_dev' => round($stdTemp, 2),
                'upper_bound' => round($meanTemp + (3 * $stdTemp), 2),
                'lower_bound' => round($meanTemp - (3 * $stdTemp), 2),
                'trained_at' => now()->toISOString(),
                'sample_count' => $temps->count(),
            ];
        }

        if ($vibs->count() >= 30) {
            $meanVib = $vibs->avg();
            $stdVib = $this->standardDeviation($vibs->toArray());
            $windowSize = min(10, (int) floor($vibs->count() / 3));
            $movingAvg = $this->movingAverage($vibs->toArray(), $windowSize);

            $config['vibration_model'] = [
                'mean' => round($meanVib, 4),
                'std_dev' => round($stdVib, 4),
                'upper_bound' => round($meanVib + (3 * $stdVib), 4),
                'moving_avg_window' => $windowSize,
                'recent_moving_avg' => round(end($movingAvg) ?: $meanVib, 4),
                'trained_at' => now()->toISOString(),
                'sample_count' => $vibs->count(),
            ];
        }

        $device->update(['config' => $config]);
    }

    public function predictFailure(IotDevice $device): array
    {
        $config = $device->config ?? [];
        $vibModel = $config['vibration_model'] ?? null;
        $tempModel = $config['temperature_model'] ?? null;

        $latestReadings = $device->readings()
            ->where('recorded_at', '>=', now()->subDays(7))
            ->orderByDesc('recorded_at')
            ->take(24)
            ->get();

        if ($latestReadings->isEmpty()) {
            return [
                'failure_probability_percent' => 0,
                'estimated_days_until_failure' => null,
                'risk_level' => 'unknown',
                'contributing_factors' => [],
                'message' => 'Data tidak cukup untuk prediksi',
            ];
        }

        $factors = [];
        $riskScore = 0;
        $maxScore = 0;

        if ($vibModel && $latestReadings->pluck('vibration_mm_s')->filter()->isNotEmpty()) {
            $maxScore += 40;
            $recentVibs = $latestReadings->pluck('vibration_mm_s')->filter()->values();
            $avgRecentVib = $recentVibs->avg();

            if ($avgRecentVib > $vibModel['upper_bound']) {
                $ratio = min(1, ($avgRecentVib - $vibModel['upper_bound']) / ($vibModel['upper_bound'] * 0.5));
                $riskScore += 40 * $ratio;
                $factors[] = "Getaran di atas ambang batas (rata-rata " . round($avgRecentVib, 4) . " mm/s vs batas " . $vibModel['upper_bound'] . " mm/s)";
            }

            $trendVibs = $latestReadings->pluck('vibration_mm_s')->filter()->reverse()->values();
            if ($trendVibs->count() >= 6) {
                $slope = $this->linearTrendSlope($trendVibs->toArray());
                if ($slope > 0.0005) {
                    $riskScore += 10;
                    $factors[] = "Tren getaran meningkat";
                }
            }
        }

        if ($tempModel && $latestReadings->pluck('temperature_celsius')->filter()->isNotEmpty()) {
            $maxScore += 30;
            $recentTemps = $latestReadings->pluck('temperature_celsius')->filter()->values();
            $avgRecentTemp = $recentTemps->avg();
            $exceedances = $recentTemps->filter(fn($t) => $t > $tempModel['upper_bound'])->count();

            if ($exceedances > 3) {
                $riskScore += 20;
                $factors[] = "Suhu melebihi batas: {$exceedances} kali dalam 7 hari";
            } elseif ($avgRecentTemp > $tempModel['upper_bound']) {
                $riskScore += 15;
                $factors[] = "Suhu rata-rata di atas ambang batas (" . round($avgRecentTemp, 2) . "C vs batas " . $tempModel['upper_bound'] . "C)";
            }

            $trendTemps = $latestReadings->pluck('temperature_celsius')->filter()->reverse()->values();
            if ($trendTemps->count() >= 6) {
                $slope = $this->linearTrendSlope($trendTemps->toArray());
                if ($slope > 0.1) {
                    $riskScore += 10;
                    $factors[] = "Tren suhu meningkat";
                }
            }
        }

        $batteryLevels = $latestReadings->pluck('battery_level')->filter();
        if ($batteryLevels->isNotEmpty()) {
            $maxScore += 15;
            $avgBattery = $batteryLevels->avg();
            if ($avgBattery < 20) {
                $riskScore += 15;
                $factors[] = "Baterai sangat rendah ({$avgBattery}%)";
            } elseif ($avgBattery < 30) {
                $riskScore += 8;
                $factors[] = "Baterai rendah ({$avgBattery}%)";
            }
        }

        $maxScore += 15;
        $daysSinceLastSeen = $device->last_seen_at ? now()->diffInHours($device->last_seen_at) : 999;
        if ($daysSinceLastSeen > 24) {
            $riskScore += 15;
            $factors[] = "Device tidak terdeteksi selama " . round($daysSinceLastSeen, 1) . " jam";
        }

        $probability = $maxScore > 0 ? min(100, round(($riskScore / $maxScore) * 100, 1)) : 0;

        if ($probability >= 70) {
            $riskLevel = 'high';
            $daysUntil = 7;
        } elseif ($probability >= 40) {
            $riskLevel = 'medium';
            $daysUntil = 30;
        } elseif ($probability >= 15) {
            $riskLevel = 'low';
            $daysUntil = 90;
        } else {
            $riskLevel = 'normal';
            $daysUntil = null;
        }

        return [
            'failure_probability_percent' => $probability,
            'estimated_days_until_failure' => $daysUntil,
            'risk_level' => $riskLevel,
            'contributing_factors' => $factors,
            'message' => $factors ? implode('; ', $factors) : 'Device beroperasi normal',
        ];
    }

    public function shouldTriggerMaintenance(IotDevice $device): bool
    {
        $prediction = $this->predictFailure($device);

        return $prediction['failure_probability_percent'] > 70
            && $prediction['estimated_days_until_failure'] !== null
            && $prediction['estimated_days_until_failure'] < 7;
    }

    public function checkAlerts(IotDevice $device, array $currentReading): array
    {
        $triggered = [];
        $config = $device->config ?? [];

        $thresholds = $config['alert_thresholds'] ?? [
            'temperature_max' => 35.0,
            'temperature_min' => -5.0,
            'humidity_max' => 85.0,
            'humidity_min' => 10.0,
            'vibration_max' => 5.0,
            'battery_min' => 15.0,
        ];

        if (isset($currentReading['temperature'])) {
            if ($currentReading['temperature'] > $thresholds['temperature_max']) {
                $alert = $this->createAlert($device, 'threshold_breach', 'warning', [
                    'metric' => 'temperature',
                    'value' => $currentReading['temperature'],
                    'threshold' => $thresholds['temperature_max'],
                    'unit' => 'C',
                ]);
                $triggered[] = $alert;
            }
            if ($currentReading['temperature'] < $thresholds['temperature_min']) {
                $alert = $this->createAlert($device, 'threshold_breach', 'warning', [
                    'metric' => 'temperature',
                    'value' => $currentReading['temperature'],
                    'threshold' => $thresholds['temperature_min'],
                    'unit' => 'C',
                ]);
                $triggered[] = $alert;
            }
        }

        if (isset($currentReading['humidity'])) {
            if ($currentReading['humidity'] > $thresholds['humidity_max']) {
                $alert = $this->createAlert($device, 'threshold_breach', 'info', [
                    'metric' => 'humidity',
                    'value' => $currentReading['humidity'],
                    'threshold' => $thresholds['humidity_max'],
                    'unit' => '%',
                ]);
                $triggered[] = $alert;
            }
        }

        if (isset($currentReading['vibration']) && $currentReading['vibration'] > $thresholds['vibration_max']) {
            $alert = $this->createAlert($device, 'threshold_breach', 'critical', [
                'metric' => 'vibration',
                'value' => $currentReading['vibration'],
                'threshold' => $thresholds['vibration_max'],
                'unit' => 'mm/s',
            ]);
            $triggered[] = $alert;
        }

        if (isset($currentReading['battery']) && $currentReading['battery'] < $thresholds['battery_min']) {
            $alert = $this->createAlert($device, 'battery_low', 'warning', [
                'metric' => 'battery',
                'value' => $currentReading['battery'],
                'threshold' => $thresholds['battery_min'],
                'unit' => '%',
            ]);
            $triggered[] = $alert;
        }

        // Rate of change check
        $lastReading = $device->readings()->recent()->first();
        if ($lastReading && isset($currentReading['temperature'])) {
            $tempDiff = abs(($currentReading['temperature'] ?? 0) - ($lastReading->temperature_celsius ?? 0));
            if ($tempDiff > 5.0) {
                $alert = $this->createAlert($device, 'rate_of_change', 'warning', [
                    'metric' => 'temperature',
                    'current' => $currentReading['temperature'],
                    'previous' => $lastReading->temperature_celsius,
                    'change' => round($tempDiff, 1),
                    'unit' => 'C',
                ]);
                $triggered[] = $alert;
            }
        }

        // Anomaly check using ML model
        if ($device->config && isset($device->config['temperature_model'])) {
            $tempModel = $device->config['temperature_model'];
            if (isset($currentReading['temperature'])) {
                $zScore = ($currentReading['temperature'] - $tempModel['mean']) / max($tempModel['std_dev'], 0.01);
                if (abs($zScore) > 3) {
                    $alert = $this->createAlert($device, 'anomaly', 'warning', [
                        'metric' => 'temperature',
                        'value' => $currentReading['temperature'],
                        'z_score' => round($zScore, 2),
                        'mean' => $tempModel['mean'],
                    ]);
                    $triggered[] = $alert;
                }
            }
        }

        // Predictive maintenance alert
        if ($this->shouldTriggerMaintenance($device)) {
            $prediction = $this->predictFailure($device);
            $alert = $this->createAlert($device, 'predictive_maintenance', 'critical', [
                'failure_probability' => $prediction['failure_probability_percent'],
                'estimated_days' => $prediction['estimated_days_until_failure'],
                'factors' => $prediction['contributing_factors'],
            ]);
            $triggered[] = $alert;
        }

        return $triggered;
    }

    public function createAlert(IotDevice $device, string $type, string $severity, array $details): IotAlert
    {
        $titleMap = [
            'threshold_breach' => 'Ambang Batas Terlampaui',
            'rate_of_change' => 'Perubahan Cepat Terdeteksi',
            'anomaly' => 'Anomali Terdeteksi',
            'battery_low' => 'Baterai Lemah',
            'offline' => 'Device Offline',
            'predictive_maintenance' => 'Prediksi Perlu Maintenance',
        ];

        $metric = $details['metric'] ?? '';
        $metricLabels = [
            'temperature' => 'Suhu',
            'humidity' => 'Kelembaban',
            'vibration' => 'Getaran',
            'battery' => 'Baterai',
        ];

        $label = $metricLabels[$metric] ?? $metric;
        $title = ($titleMap[$type] ?? 'Alert') . ($label ? " - {$label}" : '');

        return IotAlert::create([
            'company_id' => $device->company_id,
            'iot_device_id' => $device->id,
            'type' => $type,
            'severity' => $severity,
            'title' => $title,
            'message' => "{$device->name}: {$title}. Detail: " . json_encode($details),
            'details' => $details,
            'status' => 'active',
        ]);
    }

    public function checkOfflineDevices(): array
    {
        $alerts = [];
        $devices = IotDevice::active()->where('status', '!=', 'maintenance')
            ->where(function ($q) {
                $q->whereNull('last_seen_at')
                    ->orWhere('last_seen_at', '<', now()->subMinutes(30));
            })->get();

        foreach ($devices as $device) {
            $device->update(['status' => 'offline']);
            $alert = $this->createAlert($device, 'offline', 'warning', [
                'last_seen' => $device->last_seen_at?->toISOString(),
                'offline_duration_minutes' => $device->last_seen_at
                    ? now()->diffInMinutes($device->last_seen_at)
                    : null,
            ]);
            $alerts[] = $alert;
        }

        return $alerts;
    }

    public function recordEnergyReading(EnergyMeter $meter, float $kwh, float $voltage, float $current): EnergyReading
    {
        $reading = EnergyReading::create([
            'company_id' => $meter->company_id,
            'energy_meter_id' => $meter->id,
            'kwh' => $kwh,
            'voltage' => $voltage,
            'current_amps' => $current,
            'recorded_at' => now(),
        ]);

        $meter->update([
            'total_kwh_lifetime' => $meter->total_kwh_lifetime + $kwh,
        ]);

        return $reading;
    }

    public function getEnergyConsumption(int $companyId, string $period = 'monthly'): array
    {
        $now = now();
        $startMap = [
            'daily' => $now->copy()->startOfDay(),
            'weekly' => $now->copy()->startOfWeek(),
            'monthly' => $now->copy()->startOfMonth(),
            'yearly' => $now->copy()->startOfYear(),
        ];

        $start = $startMap[$period] ?? $startMap['monthly'];
        $previousStart = match ($period) {
            'daily' => $now->copy()->subDay()->startOfDay(),
            'weekly' => $now->copy()->subWeek()->startOfWeek(),
            'monthly' => $now->copy()->subMonth()->startOfMonth(),
            'yearly' => $now->copy()->subYear()->startOfYear(),
            default => $now->copy()->subMonth()->startOfMonth(),
        };
        $previousEnd = $start->copy()->subSecond();

        $currentKwh = EnergyReading::where('company_id', $companyId)
            ->where('recorded_at', '>=', $start)
            ->sum('kwh');

        $previousKwh = EnergyReading::where('company_id', $companyId)
            ->whereBetween('recorded_at', [$previousStart, $previousEnd])
            ->sum('kwh');

        $trendPercent = $previousKwh > 0
            ? round((($currentKwh - $previousKwh) / $previousKwh) * 100, 1)
            : null;

        $meters = EnergyMeter::where('company_id', $companyId)->active()->get();
        $totalRate = $meters->sum('rate_per_kwh');
        $avgRate = $meters->count() > 0 ? $totalRate / $meters->count() : 1500;
        $costEstimate = $currentKwh * $avgRate;

        // Peak demand: max hourly consumption
        $peakDemand = EnergyReading::where('company_id', $companyId)
            ->where('recorded_at', '>=', $start)
            ->selectRaw('DATE_FORMAT(recorded_at, "%Y-%m-%d %H:00:00") as hour_bucket, SUM(kwh) as total_kwh')
            ->groupBy('hour_bucket')
            ->orderByDesc('total_kwh')
            ->first();

        // Carbon estimate: 0.85 kg CO2 per kWh (Indonesia grid average)
        $carbonKg = round($currentKwh * 0.85, 2);

        return [
            'period' => $period,
            'start_date' => $start->format('Y-m-d'),
            'total_kwh' => round($currentKwh, 2),
            'cost_estimate' => round($costEstimate, 0),
            'cost_estimate_formatted' => 'Rp ' . number_format($costEstimate, 0, ',', '.'),
            'avg_rate_per_kwh' => round($avgRate, 0),
            'peak_demand_kwh' => $peakDemand ? round($peakDemand->total_kwh, 2) : null,
            'peak_demand_hour' => $peakDemand ? $peakDemand->hour_bucket : null,
            'trend_percent' => $trendPercent,
            'previous_period_kwh' => round($previousKwh, 2),
            'carbon_kg' => $carbonKg,
            'meter_count' => $meters->count(),
        ];
    }

    public function detectEnergyAnomaly(EnergyMeter $meter): array
    {
        $readings = $meter->readings()
            ->where('recorded_at', '>=', now()->subDays(30))
            ->orderBy('recorded_at')
            ->get();

        if ($readings->count() < 30) {
            return ['has_anomaly' => false, 'message' => 'Data tidak cukup'];
        }

        $dailyConsumption = [];
        foreach ($readings as $reading) {
            $day = $reading->recorded_at->format('Y-m-d');
            if (!isset($dailyConsumption[$day])) {
                $dailyConsumption[$day] = 0;
            }
            $dailyConsumption[$day] += $reading->kwh;
        }

        $values = array_values($dailyConsumption);
        $mean = array_sum($values) / count($values);
        $stdDev = $this->standardDeviation($values);

        $anomalies = [];
        foreach ($dailyConsumption as $day => $kwh) {
            $zScore = abs(($kwh - $mean) / max($stdDev, 0.01));
            if ($zScore > 2.5) {
                $anomalies[] = [
                    'date' => $day,
                    'kwh' => round($kwh, 2),
                    'z_score' => round($zScore, 2),
                    'deviation_percent' => round((($kwh - $mean) / $mean) * 100, 1),
                ];
            }
        }

        return [
            'has_anomaly' => count($anomalies) > 0,
            'mean_daily_kwh' => round($mean, 2),
            'std_dev' => round($stdDev, 2),
            'anomaly_count' => count($anomalies),
            'anomalies' => $anomalies,
        ];
    }

    public function processRfidScan(string $rfidCode, int $warehouseId): array
    {
        $tag = RfidTag::where('rfid_code', $rfidCode)->first();

        if (!$tag) {
            return [
                'found' => false,
                'rfid_code' => $rfidCode,
                'message' => 'Tag RFID tidak terdaftar',
            ];
        }

        $tag->update([
            'last_scanned_at' => now(),
            'last_scanned_by' => auth()->id(),
            'last_known_location' => Warehouse::find($warehouseId)?->name ?? "Warehouse #{$warehouseId}",
        ]);

        $product = $tag->product;
        $stock = null;
        $lastMovement = null;

        if ($product) {
            $stock = StockBalance::where('product_id', $product->id)
                ->where('warehouse_id', $warehouseId)
                ->first();

            $lastMovement = StockMovement::where('product_id', $product->id)
                ->orderByDesc('created_at')
                ->first();
        }

        return [
            'found' => true,
            'rfid_code' => $rfidCode,
            'epc' => $tag->epc,
            'product' => $product ? [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
            ] : null,
            'current_stock' => $stock ? [
                'quantity' => $stock->quantity,
                'warehouse' => $stock->warehouse?->name,
            ] : null,
            'last_movement' => $lastMovement ? [
                'type' => $lastMovement->type,
                'quantity' => $lastMovement->quantity,
                'date' => $lastMovement->created_at->format('Y-m-d H:i'),
            ] : null,
            'batch_number' => $tag->batch_number,
            'expiry_date' => $tag->expiry_date?->format('Y-m-d'),
            'status' => $tag->status,
        ];
    }

    public function processWeightReading(int $scaleId, float $weightKg): void
    {
        $scale = SmartScale::findOrFail($scaleId);
        $netWeight = max(0, $weightKg - $scale->tare_weight_kg);
        $isLowStock = $scale->low_stock_threshold_kg !== null && $netWeight <= $scale->low_stock_threshold_kg;

        $reading = ScaleReading::create([
            'company_id' => $scale->company_id,
            'smart_scale_id' => $scale->id,
            'weight_kg' => $weightKg,
            'weight_net_kg' => $netWeight,
            'is_stable' => true,
            'is_low_stock' => $isLowStock,
            'recorded_at' => now(),
        ]);

        $scale->update([
            'current_weight_kg' => $netWeight,
            'last_reading_at' => now(),
        ]);

        if ($isLowStock && $scale->linked_product_id) {
            Log::info('IoT: Stok rendah terdeteksi oleh smart scale', [
                'scale_id' => $scale->id,
                'product_id' => $scale->linked_product_id,
                'current_weight' => $netWeight,
                'threshold' => $scale->low_stock_threshold_kg,
            ]);
        }
    }

    private function standardDeviation(array $values): float
    {
        if (count($values) < 2) return 0;
        $mean = array_sum($values) / count($values);
        $variance = array_sum(array_map(fn($v) => pow($v - $mean, 2), $values)) / (count($values) - 1);
        return sqrt($variance);
    }

    private function movingAverage(array $values, int $window): array
    {
        $result = [];
        for ($i = 0; $i < count($values); $i++) {
            $slice = array_slice($values, max(0, $i - $window + 1), min($window, $i + 1));
            $result[] = array_sum($slice) / count($slice);
        }
        return $result;
    }

    private function linearTrendSlope(array $values): float
    {
        $n = count($values);
        if ($n < 2) return 0;

        $sumX = 0;
        $sumY = 0;
        $sumXY = 0;
        $sumX2 = 0;

        for ($i = 0; $i < $n; $i++) {
            $sumX += $i;
            $sumY += $values[$i];
            $sumXY += $i * $values[$i];
            $sumX2 += $i * $i;
        }

        $denominator = ($n * $sumX2) - ($sumX * $sumX);
        if ($denominator == 0) return 0;

        return (($n * $sumXY) - ($sumX * $sumY)) / $denominator;
    }
}
