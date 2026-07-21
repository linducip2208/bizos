<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected Department $department;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create(['name' => 'Test Company', 'code' => 'TC', 'slug' => 'tc']);
        $this->department = Department::factory()->create([
            'company_id' => $this->company->id,
            'name' => 'Engineering',
            'code' => 'ENG',
        ]);
        $this->user = User::factory()->create([
            'email' => 'admin@bizos.test',
            'password' => bcrypt('password'),
            'company_id' => $this->company->id,
            'is_active' => true,
        ]);
    }

    public function test_can_create_employee(): void
    {
        $this->actingAs($this->user);

        $data = [
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'employee_code' => 'EMP-001',
            'first_name' => 'Budi',
            'last_name' => 'Santoso',
            'email' => 'budi@example.com',
            'phone' => '081234567890',
            'gender' => 'male',
            'join_date' => '2026-01-15',
            'employee_type' => 'permanent',
            'status' => 'active',
            'basic_salary' => 5000000,
            'nationality' => 'Indonesia',
        ];

        $employee = Employee::create($data);

        $this->assertDatabaseHas('employees', [
            'employee_code' => 'EMP-001',
            'email' => 'budi@example.com',
            'first_name' => 'Budi',
        ]);

        $this->assertEquals('EMP-001', $employee->employee_code);
        $this->assertEquals(5000000, $employee->basic_salary);
    }

    public function test_can_read_employee(): void
    {
        $this->actingAs($this->user);

        $employee = Employee::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'first_name' => 'Siti',
            'last_name' => 'Nurhaliza',
            'email' => 'siti@example.com',
            'join_date' => '2026-02-01',
            'employee_type' => 'contract',
            'gender' => 'female',
            'basic_salary' => 7000000,
        ]);

        $found = Employee::find($employee->id);

        $this->assertNotNull($found);
        $this->assertEquals('Siti', $found->first_name);
        $this->assertEquals(7000000, $found->basic_salary);
    }

    public function test_can_update_employee(): void
    {
        $this->actingAs($this->user);

        $employee = Employee::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'first_name' => 'Ahmad',
            'email' => 'ahmad@example.com',
            'join_date' => '2026-03-01',
            'employee_type' => 'probation',
            'gender' => 'male',
            'basic_salary' => 4000000,
        ]);

        $employee->update([
            'first_name' => 'Ahmad Fauzi',
            'basic_salary' => 6000000,
            'status' => 'active',
            'phone' => '081111111111',
        ]);

        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'first_name' => 'Ahmad Fauzi',
            'basic_salary' => 6000000,
            'status' => 'active',
            'phone' => '081111111111',
        ]);
    }

    public function test_can_soft_delete_employee(): void
    {
        $this->actingAs($this->user);

        $employee = Employee::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'first_name' => 'Test',
            'email' => 'test@example.com',
            'join_date' => '2026-04-01',
            'employee_type' => 'intern',
            'gender' => 'male',
            'basic_salary' => 2000000,
        ]);

        $employee->delete();

        $this->assertSoftDeleted('employees', ['id' => $employee->id]);
        $this->assertNull(Employee::find($employee->id));

        $trashed = Employee::withTrashed()->find($employee->id);
        $this->assertNotNull($trashed);
    }

    public function test_employee_validation_requires_employee_code(): void
    {
        $this->actingAs($this->user);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Employee::create([
            'company_id' => $this->company->id,
            'first_name' => 'No Code',
            'email' => 'nocode@example.com',
            'join_date' => '2026-01-01',
            'employee_type' => 'permanent',
            'gender' => 'male',
            'basic_salary' => 3000000,
        ]);
    }

    public function test_employee_code_must_be_unique(): void
    {
        $this->actingAs($this->user);

        Employee::factory()->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'employee_code' => 'EMP-UNIQUE',
            'first_name' => 'First',
            'email' => 'first@example.com',
            'join_date' => '2026-01-01',
            'employee_type' => 'permanent',
            'gender' => 'male',
            'basic_salary' => 3000000,
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Employee::create([
            'company_id' => $this->company->id,
            'employee_code' => 'EMP-UNIQUE',
            'first_name' => 'Second',
            'email' => 'second@example.com',
            'join_date' => '2026-01-01',
            'employee_type' => 'permanent',
            'gender' => 'female',
            'basic_salary' => 3000000,
        ]);
    }

    public function test_can_list_employees_with_filters(): void
    {
        $this->actingAs($this->user);

        Employee::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'status' => 'active',
        ]);

        Employee::factory()->count(2)->create([
            'company_id' => $this->company->id,
            'department_id' => $this->department->id,
            'status' => 'inactive',
        ]);

        $active = Employee::where('status', 'active')->count();
        $inactive = Employee::where('status', 'inactive')->count();

        $this->assertEquals(3, $active);
        $this->assertEquals(2, $inactive);
        $this->assertEquals(5, Employee::count());
    }
}
