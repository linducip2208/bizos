<?php

namespace Database\Seeders;

use App\Models\BpjsConfig;
use Illuminate\Database\Seeder;

class BpjsConfigSeeder extends Seeder
{
    public function run(): void
    {
        $year = 2025;

        $configs = [
            [
                'bpjs_type'      => 'kesehatan',
                'company_rate'   => 0.04,
                'employee_rate'  => 0.01,
                'max_salary_cap' => 12000000,
                'effective_year' => $year,
                'is_active'      => true,
            ],
            [
                'bpjs_type'      => 'jkk_very_low',
                'company_rate'   => 0.0024,
                'employee_rate'  => 0.00,
                'max_salary_cap' => null,
                'effective_year' => $year,
                'is_active'      => true,
            ],
            [
                'bpjs_type'      => 'jkk_low',
                'company_rate'   => 0.0054,
                'employee_rate'  => 0.00,
                'max_salary_cap' => null,
                'effective_year' => $year,
                'is_active'      => true,
            ],
            [
                'bpjs_type'      => 'jkk_medium',
                'company_rate'   => 0.0089,
                'employee_rate'  => 0.00,
                'max_salary_cap' => null,
                'effective_year' => $year,
                'is_active'      => true,
            ],
            [
                'bpjs_type'      => 'jkk_high',
                'company_rate'   => 0.0127,
                'employee_rate'  => 0.00,
                'max_salary_cap' => null,
                'effective_year' => $year,
                'is_active'      => true,
            ],
            [
                'bpjs_type'      => 'jkk_very_high',
                'company_rate'   => 0.0174,
                'employee_rate'  => 0.00,
                'max_salary_cap' => null,
                'effective_year' => $year,
                'is_active'      => true,
            ],
            [
                'bpjs_type'      => 'jkm',
                'company_rate'   => 0.003,
                'employee_rate'  => 0.00,
                'max_salary_cap' => null,
                'effective_year' => $year,
                'is_active'      => true,
            ],
            [
                'bpjs_type'      => 'jht',
                'company_rate'   => 0.037,
                'employee_rate'  => 0.02,
                'max_salary_cap' => null,
                'effective_year' => $year,
                'is_active'      => true,
            ],
            [
                'bpjs_type'      => 'jp',
                'company_rate'   => 0.02,
                'employee_rate'  => 0.01,
                'max_salary_cap' => 12000000,
                'effective_year' => $year,
                'is_active'      => true,
            ],
        ];

        foreach ($configs as $config) {
            BpjsConfig::updateOrCreate(
                [
                    'company_id'     => 1,
                    'bpjs_type'      => $config['bpjs_type'],
                    'effective_year' => $config['effective_year'],
                ],
                array_merge(['company_id' => 1], $config)
            );
        }
    }
}
