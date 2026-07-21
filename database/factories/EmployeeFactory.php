<?php

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'company_id' => null,
            'employee_code' => 'EMP-' . fake()->unique()->numerify('####'),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'gender' => fake()->randomElement(['male', 'female']),
            'birth_date' => fake()->date('Y-m-d', '-25 years'),
            'nationality' => 'Indonesia',
            'join_date' => fake()->date('Y-m-d', '-2 years'),
            'employee_type' => fake()->randomElement(['permanent', 'contract', 'probation', 'intern']),
            'status' => 'active',
            'basic_salary' => fake()->numberBetween(3000000, 15000000),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    public function terminated(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'terminated',
            'termination_date' => now()->subMonth(),
            'termination_reason' => 'Contract ended',
        ]);
    }
}
