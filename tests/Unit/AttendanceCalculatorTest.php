<?php

namespace Tests\Unit;

use App\Models\Attendance;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Shift;
use App\Services\AttendanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceCalculatorTest extends TestCase
{
    use RefreshDatabase;

    protected AttendanceService $service;
    protected Shift $shift;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new AttendanceService();

        $company = Company::factory()->create(['name' => 'Test', 'code' => 'T', 'slug' => 't']);
        $department = Department::factory()->create(['company_id' => $company->id, 'name' => 'IT', 'code' => 'IT']);

        $this->shift = Shift::factory()->create([
            'company_id' => $company->id,
            'name' => 'Morning Shift',
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'grace_period_minutes' => 15,
        ]);
    }

    protected function makeAttendance(string $clockIn, ?string $clockOut = null): Attendance
    {
        $company = Company::first();
        $department = Department::first();

        $employee = Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
        ]);

        return Attendance::factory()->make([
            'employee_id' => $employee->id,
            'shift_id' => $this->shift->id,
            'date' => '2026-05-30',
            'clock_in' => "2026-05-30 {$clockIn}",
            'clock_out' => $clockOut ? "2026-05-30 {$clockOut}" : null,
        ]);
    }

    public function test_on_time_arrival_returns_zero_late_minutes(): void
    {
        $attendance = $this->makeAttendance('07:50:00');
        $lateMinutes = $this->service->calculateLateMinutes($attendance);

        $this->assertEquals(0, $lateMinutes);
    }

    public function test_arrival_within_grace_period_returns_zero_late_minutes(): void
    {
        $attendance = $this->makeAttendance('08:10:00');
        $lateMinutes = $this->service->calculateLateMinutes($attendance);

        $this->assertEquals(0, $lateMinutes);
    }

    public function test_arrival_exactly_at_grace_period_limit_returns_zero(): void
    {
        $attendance = $this->makeAttendance('08:15:00');
        $lateMinutes = $this->service->calculateLateMinutes($attendance);

        $this->assertEquals(0, $lateMinutes);
    }

    public function test_late_arrival_calculates_correct_late_minutes(): void
    {
        $attendance = $this->makeAttendance('08:30:00');
        $lateMinutes = $this->service->calculateLateMinutes($attendance);

        $this->assertEquals(30, $lateMinutes);
    }

    public function test_very_late_arrival_calculates_late_minutes(): void
    {
        $attendance = $this->makeAttendance('10:00:00');
        $lateMinutes = $this->service->calculateLateMinutes($attendance);

        $this->assertEquals(120, $lateMinutes);
    }

    public function test_no_clock_in_returns_zero_late_minutes(): void
    {
        $attendance = $this->makeAttendance('08:00:00');
        $attendance->clock_in = null;

        $lateMinutes = $this->service->calculateLateMinutes($attendance);

        $this->assertEquals(0, $lateMinutes);
    }

    public function test_no_shift_returns_zero_late_minutes(): void
    {
        $attendance = $this->makeAttendance('08:30:00');
        $attendance->shift_id = null;
        $attendance->setRelation('shift', null);

        $lateMinutes = $this->service->calculateLateMinutes($attendance);

        $this->assertEquals(0, $lateMinutes);
    }

    public function test_shift_with_zero_grace_period(): void
    {
        $this->shift->update(['grace_period_minutes' => 0]);

        $attendance = $this->makeAttendance('08:01:00');
        $lateMinutes = $this->service->calculateLateMinutes($attendance);

        $this->assertEquals(1, $lateMinutes);
    }

    public function test_on_time_departure_returns_zero_early_minutes(): void
    {
        $attendance = $this->makeAttendance('08:00:00', '17:00:00');
        $earlyMinutes = $this->service->calculateEarlyDepartureMinutes($attendance);

        $this->assertEquals(0, $earlyMinutes);
    }

    public function test_early_departure_returns_correct_minutes(): void
    {
        $attendance = $this->makeAttendance('08:00:00', '16:30:00');
        $earlyMinutes = $this->service->calculateEarlyDepartureMinutes($attendance);

        $this->assertEquals(30, $earlyMinutes);
    }

    public function test_overtime_departure_returns_overtime_minutes(): void
    {
        $attendance = $this->makeAttendance('08:00:00', '18:00:00');
        $overtimeMinutes = $this->service->calculateOvertimeMinutes($attendance);

        $this->assertEquals(60, $overtimeMinutes);
    }

    public function test_no_clock_out_returns_zero_overtime(): void
    {
        $attendance = $this->makeAttendance('08:00:00');
        $overtimeMinutes = $this->service->calculateOvertimeMinutes($attendance);

        $this->assertEquals(0, $overtimeMinutes);
    }

    public function test_overnight_shift_late_calculation(): void
    {
        $this->shift->update([
            'start_time' => '22:00:00',
            'end_time' => '06:00:00',
            'is_overnight' => true,
            'grace_period_minutes' => 15,
        ]);

        $attendance = $this->makeAttendanceRaw('2026-05-30 22:20:00', null);
        $lateMinutes = $this->service->calculateLateMinutes($attendance);

        $this->assertEquals(20, $lateMinutes);
    }

    public function test_overnight_shift_early_departure(): void
    {
        $this->shift->update([
            'start_time' => '22:00:00',
            'end_time' => '06:00:00',
            'is_overnight' => true,
        ]);

        $attendance = $this->makeAttendanceRaw('2026-05-30 22:00:00', '2026-05-31 05:00:00');
        $earlyMinutes = $this->service->calculateEarlyDepartureMinutes($attendance);

        $this->assertEquals(60, $earlyMinutes);
    }

    protected function makeAttendanceRaw(string $clockIn, ?string $clockOut = null): Attendance
    {
        $company = Company::first();
        $department = Department::first();

        $employee = Employee::factory()->create([
            'company_id' => $company->id,
            'department_id' => $department->id,
        ]);

        return Attendance::factory()->make([
            'employee_id' => $employee->id,
            'shift_id' => $this->shift->id,
            'date' => '2026-05-30',
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
        ]);
    }
}
