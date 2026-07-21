<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\BpjsClaim;
use App\Models\Doctor;
use App\Models\Employee;
use App\Models\LabOrder;
use App\Models\LabResult;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\StockBalance;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class HealthcareService
{
    public function registerPatient(array $data): Patient
    {
        return Patient::create($data);
    }

    public function bookAppointment(array $data): Appointment
    {
        $doctorId = $data['doctor_id'];
        $appointmentDate = Carbon::parse($data['appointment_date']);

        $data['queue_number'] = $this->getQueueNumber($doctorId, $appointmentDate);
        $data['status'] = 'scheduled';

        return Appointment::create($data);
    }

    public function getQueueNumber(int $doctorId, Carbon $date): int
    {
        $maxQueue = Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $date->toDateString())
            ->whereNotNull('queue_number')
            ->max('queue_number');

        return ($maxQueue ?? 0) + 1;
    }

    public function createSoapRecord(array $data): MedicalRecord
    {
        $record = MedicalRecord::create($data);

        if (!empty($data['appointment_id'])) {
            Appointment::where('id', $data['appointment_id'])
                ->update(['status' => 'completed']);
        }

        return $record;
    }

    public function getPatientHistory(Patient $patient): Collection
    {
        return MedicalRecord::where('patient_id', $patient->id)
            ->with(['doctor', 'appointment', 'prescriptions.items.product'])
            ->orderBy('visit_date', 'desc')
            ->get();
    }

    public function getVitalTrend(Patient $patient, string $metric): array
    {
        return MedicalRecord::where('patient_id', $patient->id)
            ->whereNotNull('vital_signs')
            ->orderBy('visit_date', 'asc')
            ->get()
            ->map(fn ($record) => [
                'date' => $record->visit_date->format('Y-m-d'),
                'value' => data_get($record->vital_signs, $metric),
            ])
            ->filter(fn ($item) => !is_null($item['value']))
            ->values()
            ->toArray();
    }

    public function dispensePrescription(Prescription $prescription): void
    {
        $warehouseId = null;
        $warehouse = \App\Models\Warehouse::first();
        if ($warehouse) {
            $warehouseId = $warehouse->id;
        }

        foreach ($prescription->items as $item) {
            $balance = StockBalance::where('product_id', $item->product_id)
                ->when($warehouseId, fn ($q) => $q->where('warehouse_id', $warehouseId))
                ->first();

            if ($balance && $balance->quantity >= $item->quantity) {
                $balance->quantity -= $item->quantity;
                $balance->save();

                StockMovement::create([
                    'company_id' => $prescription->patient->company_id,
                    'product_id' => $item->product_id,
                    'warehouse_id' => $warehouseId,
                    'movement_type' => 'out',
                    'reference_type' => 'prescription',
                    'reference_id' => $prescription->id,
                    'quantity_out' => $item->quantity,
                    'quantity_in' => 0,
                    'unit_cost' => $balance->average_cost ?? 0,
                    'running_quantity' => $balance->quantity,
                    'running_cost' => $balance->average_cost ?? 0,
                    'notes' => 'Penjualan resep #' . $prescription->id,
                    'movement_date' => now(),
                ]);
            }
        }

        $prescription->update(['status' => 'dispensed']);
    }

    public function checkDrugInteraction(array $drugIds): array
    {
        $interactions = [];
        $drugInteractions = $this->getDrugInteractionDatabase();

        $products = \App\Models\Product::whereIn('id', $drugIds)->get()->keyBy('id');

        for ($i = 0; $i < count($drugIds); $i++) {
            for ($j = $i + 1; $j < count($drugIds); $j++) {
                $key = $drugIds[$i] . '-' . $drugIds[$j];
                $keyRev = $drugIds[$j] . '-' . $drugIds[$i];

                if (isset($drugInteractions[$key]) || isset($drugInteractions[$keyRev])) {
                    $interaction = $drugInteractions[$key] ?? $drugInteractions[$keyRev];
                    $interactions[] = [
                        'drug_a' => $products[$drugIds[$i]]->name ?? 'Obat A',
                        'drug_a_id' => $drugIds[$i],
                        'drug_b' => $products[$drugIds[$j]]->name ?? 'Obat B',
                        'drug_b_id' => $drugIds[$j],
                        'severity' => $interaction['severity'] ?? 'unknown',
                        'description' => $interaction['description'] ?? 'Potensi interaksi terdeteksi',
                        'recommendation' => $interaction['recommendation'] ?? 'Konsultasikan dengan dokter',
                    ];
                }
            }
        }

        return $interactions;
    }

    protected function getDrugInteractionDatabase(): array
    {
        return [
            'aspirin-warfarin' => [
                'severity' => 'high',
                'description' => 'Aspirin meningkatkan risiko perdarahan jika digunakan bersama warfarin',
                'recommendation' => 'Hindari kombinasi. Monitor INR secara ketat jika harus digunakan bersama',
            ],
            'aspirin-ibuprofen' => [
                'severity' => 'medium',
                'description' => 'Ibuprofen dapat mengurangi efek kardioprotektif aspirin',
                'recommendation' => 'Berikan ibuprofen minimal 2 jam setelah aspirin',
            ],
            'omeprazole-clopidogrel' => [
                'severity' => 'medium',
                'description' => 'Omeprazole dapat mengurangi efektivitas clopidogrel',
                'recommendation' => 'Pertimbangkan pantoprazole atau H2 antagonist sebagai alternatif',
            ],
            'simvastatin-amiodarone' => [
                'severity' => 'high',
                'description' => 'Amiodarone meningkatkan kadar simvastatin dan risiko rhabdomyolysis',
                'recommendation' => 'Batasi simvastatin maksimal 20 mg/hari atau ganti dengan atorvastatin',
            ],
            'ace_inhibitor-potassium_sparing' => [
                'severity' => 'high',
                'description' => 'Kombinasi ACE inhibitor dengan diuretik hemat kalium dapat menyebabkan hiperkalemia',
                'recommendation' => 'Monitor kadar kalium serum secara ketat',
            ],
            'metformin-contrast' => [
                'severity' => 'high',
                'description' => 'Kontras iodinasi meningkatkan risiko asidosis laktat pada pasien metformin',
                'recommendation' => 'Hentikan metformin 48 jam sebelum dan sesudah prosedur kontras',
            ],
            'rifampicin-oral_contraceptive' => [
                'severity' => 'high',
                'description' => 'Rifampicin menurunkan efektivitas kontrasepsi oral',
                'recommendation' => 'Gunakan metode kontrasepsi tambahan non-hormonal',
            ],
            'theophylline-ciprofloxacin' => [
                'severity' => 'medium',
                'description' => 'Ciprofloxacin meningkatkan kadar teofilin dan risiko toksisitas',
                'recommendation' => 'Monitor kadar teofilin dan sesuaikan dosis',
            ],
        ];
    }

    public function submitBpjsClaim(MedicalRecord $record): BpjsClaim
    {
        $inaCbgs = $this->lookupInaCbgs($record);

        $claim = BpjsClaim::create([
            'company_id' => $record->patient->company_id,
            'patient_id' => $record->patient_id,
            'medical_record_id' => $record->id,
            'sep_number' => $record->patient->bpjs_number
                ? 'SEP-' . now()->format('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT)
                : null,
            'ina_cbgs_code' => $inaCbgs['code'] ?? null,
            'ina_cbgs_description' => $inaCbgs['description'] ?? null,
            'claim_amount' => $inaCbgs['tariff'] ?? 0,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        return $claim;
    }

    protected function lookupInaCbgs(MedicalRecord $record): ?array
    {
        $diagnosisMapping = [
            'A09' => ['code' => 'A-4-14-I', 'description' => 'Gastroenteritis Akut Ringan', 'tariff' => 2800000],
            'I10' => ['code' => 'F-4-15-I', 'description' => 'Hipertensi Esensial Ringan', 'tariff' => 3200000],
            'E11' => ['code' => 'F-4-17-I', 'description' => 'Diabetes Melitus Tipe 2 Ringan', 'tariff' => 3500000],
            'J06' => ['code' => 'Q-4-12-I', 'description' => 'Infeksi Saluran Napas Atas Akut', 'tariff' => 2200000],
            'N39' => ['code' => 'L-4-16-I', 'description' => 'Infeksi Saluran Kemih', 'tariff' => 2600000],
            'M54' => ['code' => 'M-4-13-I', 'description' => 'Low Back Pain', 'tariff' => 1800000],
            'K30' => ['code' => 'A-4-13-I', 'description' => 'Dispepsia', 'tariff' => 2500000],
            'L30' => ['code' => 'H-4-11-I', 'description' => 'Dermatitis', 'tariff' => 2000000],
            'J45' => ['code' => 'Q-4-13-I', 'description' => 'Asma Ringan', 'tariff' => 3000000],
            'K29' => ['code' => 'A-4-12-I', 'description' => 'Gastritis', 'tariff' => 2400000],
        ];

        if ($record->diagnosis_code && isset($diagnosisMapping[$record->diagnosis_code])) {
            return $diagnosisMapping[$record->diagnosis_code];
        }

        $tariff = 2500000 + (rand(0, 20) * 100000);

        return [
            'code' => 'X-4-' . str_pad(rand(1, 99), 2, '0', STR_PAD_LEFT) . '-I',
            'description' => $record->diagnosis_name ?? 'Diagnosis Umum',
            'tariff' => $tariff,
        ];
    }

    public function checkBpjsClaimStatus(BpjsClaim $claim): string
    {
        if ($claim->status === 'draft') {
            return 'Draft — belum disubmit';
        }

        if ($claim->status === 'submitted') {
            $hoursSinceSubmit = now()->diffInHours($claim->submitted_at);
            if ($hoursSinceSubmit > 72) {
                $claim->update([
                    'status' => fake()->boolean(80) ? 'approved' : 'pending',
                    'approved_at' => $claim->status === 'approved' ? now() : null,
                    'approved_amount' => $claim->status === 'approved'
                        ? $claim->claim_amount * (fake()->boolean(90) ? 1 : fake()->randomFloat(2, 0.7, 0.95))
                        : null,
                ]);
            }
        }

        return $claim->status;
    }

    public function orderLabTest(array $data): LabOrder
    {
        return LabOrder::create($data);
    }

    public function recordLabResult(array $data): LabResult
    {
        $data['is_abnormal'] = $this->checkAbnormal($data);

        $result = LabResult::create($data);

        $labOrder = LabOrder::find($data['lab_order_id']);
        if ($labOrder && $labOrder->status !== 'completed') {
            $labOrder->update(['status' => 'in_progress']);
        }

        return $result;
    }

    protected function checkAbnormal(array $data): bool
    {
        if (empty($data['normal_range']) || empty($data['result_value'])) {
            return false;
        }

        $normalRange = str_replace(' ', '', $data['normal_range']);
        $resultValue = (float) $data['result_value'];

        if (preg_match('/^([\d.]+)-([\d.]+)$/', $normalRange, $matches)) {
            return $resultValue < (float) $matches[1] || $resultValue > (float) $matches[2];
        }

        if (str_starts_with($normalRange, '<') && $resultValue >= (float) substr($normalRange, 1)) {
            return true;
        }

        if (str_starts_with($normalRange, '>') && $resultValue <= (float) substr($normalRange, 1)) {
            return true;
        }

        return false;
    }

    public function sendAppointmentReminder(Appointment $appointment): void
    {
        $patient = $appointment->patient;
        $doctor = $appointment->doctor;

        $message = "Halo {$patient->full_name},\n\n"
            . "Ini pengingat janji temu Anda besok:\n"
            . "Tanggal: {$appointment->appointment_date->format('d M Y')}\n"
            . "Jam: " . Carbon::parse($appointment->start_time)->format('H:i') . " WIB\n"
            . "Dokter: {$doctor->first_name} {$doctor->last_name}\n"
            . "Nomor Antrian: {$appointment->queue_number}\n\n"
            . "Silakan datang 15 menit sebelum jadwal.\n"
            . "Terima kasih.";

        if ($patient->phone && class_exists(\App\Services\SmsService::class)) {
            try {
                app(\App\Services\SmsService::class)->send($patient->phone, $message);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Gagal kirim SMS pengingat appointment: ' . $e->getMessage());
            }
        }

        \Illuminate\Support\Facades\Log::info('Appointment reminder sent', [
            'appointment_id' => $appointment->id,
            'patient' => $patient->full_name,
            'doctor' => $doctor->first_name . ' ' . $doctor->last_name,
            'date' => $appointment->appointment_date->format('Y-m-d'),
        ]);
    }
}
