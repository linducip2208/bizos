<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Services\HealthcareService;
use Illuminate\Console\Command;

class SendAppointmentReminders extends Command
{
    protected $signature = 'healthcare:send-reminders';
    protected $description = 'Kirim pengingat H-1 janji temu ke pasien';

    public function handle(HealthcareService $service): int
    {
        $tomorrow = now()->addDay()->toDateString();

        $appointments = Appointment::with(['patient', 'doctor'])
            ->whereDate('appointment_date', $tomorrow)
            ->whereIn('status', ['scheduled', 'confirmed'])
            ->get();

        $this->info("Ditemukan {$appointments->count()} janji temu untuk besok ({$tomorrow})");

        foreach ($appointments as $appointment) {
            $this->info("Mengirim pengingat: {$appointment->patient->full_name} — "
                . $appointment->doctor->first_name);
            $service->sendAppointmentReminder($appointment);
        }

        $this->info('Selesai mengirim pengingat.');

        return self::SUCCESS;
    }
}
