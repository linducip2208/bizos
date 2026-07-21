<?php

namespace App\Models;

use Database\Factories\EmployeeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    /** @use HasFactory<EmployeeFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'department_id',
        'position_id',
        'designation_id',
        'grade_id',
        'employee_code',
        'first_name',
        'last_name',
        'email',
        'phone',
        'gender',
        'birth_date',
        'birth_place',
        'religion',
        'marital_status',
        'nationality',
        'id_number',
        'tax_number',
        'bpjs_kesehatan',
        'bpjs_ketenagakerjaan',
        'address',
        'city',
        'province',
        'postal_code',
        'photo',
        'join_date',
        'contract_start',
        'contract_end',
        'employee_type',
        'status',
        'termination_date',
        'termination_reason',
        'basic_salary',
        'hourly_rate',
        'overtime_rate',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
    ];

    protected $casts = [
        'gender' => 'string',
        'birth_date' => 'date',
        'marital_status' => 'string',
        'join_date' => 'date',
        'contract_start' => 'date',
        'contract_end' => 'date',
        'employee_type' => 'string',
        'status' => 'string',
        'termination_date' => 'date',
        'basic_salary' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'overtime_rate' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function designation()
    {
        return $this->belongsTo(Designation::class);
    }

    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function familyMembers()
    {
        return $this->hasMany(FamilyMember::class);
    }

    public function documents()
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function attendanceLogs()
    {
        return $this->hasMany(AttendanceLog::class);
    }

    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }

    public function leaveBalances()
    {
        return $this->hasMany(LeaveBalance::class);
    }

    public function overtimes()
    {
        return $this->hasMany(Overtime::class);
    }

    public function approvedOvertimes()
    {
        return $this->hasMany(Overtime::class, 'approved_by');
    }

    public function reimbursements()
    {
        return $this->hasMany(Reimbursement::class);
    }

    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }

    public function taskAssignees()
    {
        return $this->hasMany(TaskAssignee::class);
    }

    public function timesheets()
    {
        return $this->hasMany(Timesheet::class);
    }

    public function approvedTimesheets()
    {
        return $this->hasMany(Timesheet::class, 'approved_by');
    }

    public function courseEnrollments()
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    public function shiftEmployees()
    {
        return $this->hasMany(ShiftEmployee::class);
    }

    public function employeeSalaryComponents()
    {
        return $this->hasMany(EmployeeSalaryComponent::class);
    }

    public function meetingAttendees()
    {
        return $this->hasMany(MeetingAttendee::class);
    }

    public function approvedAttendances()
    {
        return $this->hasMany(Attendance::class, 'approved_by');
    }
}
