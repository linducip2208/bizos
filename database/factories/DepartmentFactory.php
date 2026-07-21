<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->lexify('???')),
            'name' => fake()->randomElement(['Engineering', 'Marketing', 'Finance', 'HR', 'Operations', 'Sales']),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
