<?php

namespace Database\Factories;

use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        $date = fake()->dateTimeBetween('-30 days', 'now')->format('Y-m-d');
        $clockInHour = fake()->numberBetween(6, 10);
        $clockInMinute = fake()->numberBetween(0, 59);
        $clockIn = sprintf('%s %02d:%02d:00', $date, $clockInHour, $clockInMinute);
        $clockOut = sprintf('%s %02d:%02d:00', $date, fake()->numberBetween(16, 20), fake()->numberBetween(0, 59));

        return [
            'date' => $date,
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'status' => 'present',
            'late_minutes' => 0,
            'early_departure_minutes' => 0,
            'overtime_minutes' => 0,
            'work_type' => 'office',
        ];
    }

    public function late(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'late',
            'late_minutes' => fake()->numberBetween(1, 120),
        ]);
    }

    public function absent(): static
    {
        return $this->state(fn (array $attributes) => [
            'clock_in' => null,
            'clock_out' => null,
            'status' => 'absent',
            'late_minutes' => 0,
        ]);
    }

    public function wfh(): static
    {
        return $this->state(fn (array $attributes) => [
            'work_type' => 'wfh',
            'clock_in_lat' => null,
            'clock_in_lng' => null,
        ]);
    }
}
