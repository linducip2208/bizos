<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Branch;
use App\Models\CashierShift;
use App\Models\Client;
use App\Models\ClientContact;
use App\Models\Coa;
use App\Models\CoaCategory;
use App\Models\Company;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\EmployeeSalaryComponent;
use App\Models\Grade;
use App\Models\Leave;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\Milestone;
use App\Models\PaymentMethod;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\PayrollPeriod;
use App\Models\PaySlip;
use App\Models\Permission;
use App\Models\Position;
use App\Models\PosMember;
use App\Models\PosPayment;
use App\Models\PosTransaction;
use App\Models\PosTransactionItem;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\ProjectPhase;
use App\Models\Role;
use App\Models\SalaryComponent;
use App\Models\Shift;
use App\Models\ShiftEmployee;
use App\Models\SystemSetting;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    private $companyId;
    private $branches = [];
    private $departments = [];
    private $positions = [];
    private $employees = [];
    private $shifts = [];
    private $roles = [];
    private $jobTitleMap = [];

    public function run(): void
    {
        $this->command->info('=== BizOS Comprehensive Database Seeder ===');

        $this->disableForeignKeys();

        $this->seedCompany();
        $this->seedBranches();
        $this->seedDesignations();
        $this->seedGrades();
        $this->seedDepartments();
        $this->seedPositions();
        $this->seedEmployees();
        $this->seedRolesAndPermissions();
        $this->seedUsers();
        $this->seedShifts();
        $this->seedShiftEmployees();
        $this->seedAttendance();
        $this->seedLeaveTypes();
        $this->seedLeaveBalances();
        $this->seedLeaves();
        $this->seedSalaryComponents();
        $this->seedEmployeeSalaryComponents();
        $this->seedPayrollPeriod();
        $this->seedPaymentMethods();
        $this->seedCoaCategories();
        $this->seedCoa();
        $this->seedClients();
        $this->seedProductCategories();
        $this->seedProducts();
        $this->seedPosMembers();
        $this->seedPosTransactions();
        $this->seedProjects();
        $this->seedSystemSettings();

        $this->enableForeignKeys();

        $this->call(DemoDataSeeder::class);
        $this->call(SystemReportSeeder::class);

        $this->command->info('');
        $this->command->info('=== Seeding Complete! ===');
        $this->command->info('Login: budi@maju.test / password');
        $this->command->info('All employee accounts: firstname@maju.test / password');
    }

    private function disableForeignKeys(): void
    {
        Schema::disableForeignKeyConstraints();
    }

    private function enableForeignKeys(): void
    {
        Schema::enableForeignKeyConstraints();
    }

    // ============================================================
    // 1. COMPANY
    // ============================================================
    private function seedCompany(): void
    {
        Company::truncate();
        Company::create([
            'code' => 'MAJU',
            'name' => 'PT Maju Bersama',
            'slug' => 'pt-maju-bersama',
            'address' => 'Jl. Jenderal Sudirman No. 123, Kawasan Bisnis SCBD Lot 7',
            'phone' => '021-5555-6789',
            'email' => 'info@majubersama.co.id',
            'website' => 'https://majubersama.co.id',
            'tax_id' => '01.234.567.8-012.000',
            'is_active' => true,
            'subscription_start' => '2026-01-01',
            'subscription_end' => '2027-01-01',
        ]);
        $this->companyId = 1;
        $this->command->info('  [1/18] Company: PT Maju Bersama created.');
    }

    // ============================================================
    // 2. BRANCHES
    // ============================================================
    private function seedBranches(): void
    {
        Branch::truncate();
        $this->branches['pusat'] = Branch::create([
            'company_id' => $this->companyId,
            'code' => 'PST',
            'name' => 'Kantor Pusat Jakarta',
            'address' => 'Jl. Jenderal Sudirman No. 123, Jakarta Selatan',
            'phone' => '021-5555-6789',
            'timezone' => 'Asia/Jakarta',
            'is_headquarters' => true,
            'is_active' => true,
        ])->id;

        $this->branches['bandung'] = Branch::create([
            'company_id' => $this->companyId,
            'code' => 'BDG',
            'name' => 'Cabang Bandung',
            'address' => 'Jl. Asia Afrika No. 45, Bandung',
            'phone' => '022-8888-1234',
            'timezone' => 'Asia/Jakarta',
            'is_headquarters' => false,
            'is_active' => true,
        ])->id;

        $this->branches['surabaya'] = Branch::create([
            'company_id' => $this->companyId,
            'code' => 'SBY',
            'name' => 'Cabang Surabaya',
            'address' => 'Jl. Tunjungan No. 78, Surabaya',
            'phone' => '031-7777-4321',
            'timezone' => 'Asia/Jakarta',
            'is_headquarters' => false,
            'is_active' => true,
        ])->id;

        $this->command->info('  [2/18] Branches: 3 created.');
    }

    // ============================================================
    // 3. DESIGNATIONS
    // ============================================================
    private function seedDesignations(): void
    {
        Designation::truncate();
        $titles = [
            ['name' => 'Direktur Utama', 'level' => 1],
            ['name' => 'Manager', 'level' => 2],
            ['name' => 'Supervisor', 'level' => 3],
            ['name' => 'Staff Senior', 'level' => 4],
            ['name' => 'Staff', 'level' => 5],
            ['name' => 'Operator', 'level' => 6],
        ];
        foreach ($titles as $t) {
            Designation::create([
                'company_id' => $this->companyId,
                'name' => $t['name'],
                'level' => $t['level'],
                'is_active' => true,
            ]);
        }
        $this->command->info('  [3/18] Designations: 6 created.');
    }

    // ============================================================
    // 4. GRADES
    // ============================================================
    private function seedGrades(): void
    {
        Grade::truncate();
        $grades = [
            ['code' => 'G1', 'name' => 'Grade 1 - Eksekutif', 'min_salary' => 15000000, 'max_salary' => 50000000],
            ['code' => 'G2', 'name' => 'Grade 2 - Manajerial', 'min_salary' => 10000000, 'max_salary' => 25000000],
            ['code' => 'G3', 'name' => 'Grade 3 - Senior', 'min_salary' => 7000000, 'max_salary' => 12000000],
            ['code' => 'G4', 'name' => 'Grade 4 - Junior', 'min_salary' => 4500000, 'max_salary' => 8000000],
            ['code' => 'G5', 'name' => 'Grade 5 - Entry', 'min_salary' => 3000000, 'max_salary' => 5000000],
        ];
        foreach ($grades as $g) {
            Grade::create(array_merge($g, [
                'company_id' => $this->companyId,
                'is_active' => true,
            ]));
        }
        $this->command->info('  [4/18] Grades: 5 created.');
    }

    // ============================================================
    // 5. DEPARTMENTS
    // ============================================================
    private function seedDepartments(): void
    {
        Department::truncate();
        $depts = [
            ['code' => 'DIR', 'name' => 'Direksi', 'description' => 'Dewan Direksi', 'sort_order' => 1],
            ['code' => 'HRGA', 'name' => 'HR & GA', 'description' => 'Human Resources & General Affairs', 'sort_order' => 2],
            ['code' => 'FA', 'name' => 'Finance & Accounting', 'description' => 'Keuangan & Akuntansi', 'sort_order' => 3],
            ['code' => 'SM', 'name' => 'Sales & Marketing', 'description' => 'Penjualan & Pemasaran', 'sort_order' => 4],
            ['code' => 'IT', 'name' => 'IT', 'description' => 'Teknologi Informasi', 'sort_order' => 5],
            ['code' => 'OPS', 'name' => 'Operasional', 'description' => 'Operasional Perusahaan', 'sort_order' => 6],
            ['code' => 'PRD', 'name' => 'Produksi', 'description' => 'Produksi & Manufaktur', 'sort_order' => 7],
        ];
        foreach ($depts as $d) {
            $this->departments[$d['code']] = Department::create(array_merge($d, [
                'company_id' => $this->companyId,
                'parent_id' => null,
                'is_active' => true,
            ]))->id;
        }
        $this->command->info('  [5/18] Departments: 7 created.');
    }

    // ============================================================
    // 6. POSITIONS
    // ============================================================
    private function seedPositions(): void
    {
        Position::truncate();
        $positions = [
            ['dept' => 'DIR', 'code' => 'DIR-UTAMA', 'name' => 'Direktur Utama', 'sort_order' => 1],
            ['dept' => 'HRGA', 'code' => 'HR-MGR', 'name' => 'HR Manager', 'sort_order' => 2],
            ['dept' => 'HRGA', 'code' => 'HR-STF', 'name' => 'Staff HR', 'sort_order' => 3],
            ['dept' => 'FA', 'code' => 'FA-MGR', 'name' => 'Finance Manager', 'sort_order' => 4],
            ['dept' => 'FA', 'code' => 'FA-STF', 'name' => 'Staff Finance', 'sort_order' => 5],
            ['dept' => 'SM', 'code' => 'SM-MGR', 'name' => 'Sales Manager', 'sort_order' => 6],
            ['dept' => 'SM', 'code' => 'SM-SALES', 'name' => 'Sales', 'sort_order' => 7],
            ['dept' => 'SM', 'code' => 'SM-MKT', 'name' => 'Marketing', 'sort_order' => 8],
            ['dept' => 'IT', 'code' => 'IT-MGR', 'name' => 'IT Manager', 'sort_order' => 9],
            ['dept' => 'IT', 'code' => 'IT-STF', 'name' => 'Staff IT', 'sort_order' => 10],
            ['dept' => 'OPS', 'code' => 'OPS-KASIR', 'name' => 'Kasir', 'sort_order' => 11],
            ['dept' => 'OPS', 'code' => 'OPS-STF', 'name' => 'Operator', 'sort_order' => 12],
        ];
        foreach ($positions as $p) {
            $this->positions[$p['code']] = Position::create([
                'company_id' => $this->companyId,
                'department_id' => $this->departments[$p['dept']],
                'code' => $p['code'],
                'name' => $p['name'],
                'description' => $p['name'],
                'sort_order' => $p['sort_order'],
                'is_active' => true,
            ])->id;
        }
        $this->command->info('  [6/18] Positions: 12 created.');
    }

    // ============================================================
    // 7. EMPLOYEES
    // ============================================================
    private function seedEmployees(): void
    {
        DB::table('employees')->truncate();

        $staff = [
            [
                'code' => 'EMP-001', 'first_name' => 'Budi', 'last_name' => 'Santoso',
                'dept' => 'DIR', 'pos' => 'DIR-UTAMA', 'branch' => 'pusat',
                'gender' => 'male', 'salary' => 35000000, 'type' => 'permanent',
                'join' => '2020-01-15', 'designation' => 'Direktur Utama', 'grade' => 'G1',
            ],
            [
                'code' => 'EMP-002', 'first_name' => 'Siti', 'last_name' => 'Rahmawati',
                'dept' => 'HRGA', 'pos' => 'HR-MGR', 'branch' => 'pusat',
                'gender' => 'female', 'salary' => 18000000, 'type' => 'permanent',
                'join' => '2021-03-01', 'designation' => 'Manager', 'grade' => 'G2',
            ],
            [
                'code' => 'EMP-003', 'first_name' => 'Ahmad', 'last_name' => 'Fauzi',
                'dept' => 'FA', 'pos' => 'FA-MGR', 'branch' => 'pusat',
                'gender' => 'male', 'salary' => 20000000, 'type' => 'permanent',
                'join' => '2021-06-10', 'designation' => 'Manager', 'grade' => 'G2',
            ],
            [
                'code' => 'EMP-004', 'first_name' => 'Dewi', 'last_name' => 'Lestari',
                'dept' => 'HRGA', 'pos' => 'HR-STF', 'branch' => 'pusat',
                'gender' => 'female', 'salary' => 6500000, 'type' => 'permanent',
                'join' => '2022-02-15', 'designation' => 'Staff', 'grade' => 'G4',
            ],
            [
                'code' => 'EMP-005', 'first_name' => 'Rudi', 'last_name' => 'Hartono',
                'dept' => 'FA', 'pos' => 'FA-STF', 'branch' => 'pusat',
                'gender' => 'male', 'salary' => 7000000, 'type' => 'permanent',
                'join' => '2022-05-01', 'designation' => 'Staff', 'grade' => 'G4',
            ],
            [
                'code' => 'EMP-006', 'first_name' => 'Andi', 'last_name' => 'Pratama',
                'dept' => 'SM', 'pos' => 'SM-MGR', 'branch' => 'pusat',
                'gender' => 'male', 'salary' => 17000000, 'type' => 'permanent',
                'join' => '2021-08-15', 'designation' => 'Manager', 'grade' => 'G2',
            ],
            [
                'code' => 'EMP-007', 'first_name' => 'Maya', 'last_name' => 'Indah',
                'dept' => 'SM', 'pos' => 'SM-SALES', 'branch' => 'bandung',
                'gender' => 'female', 'salary' => 5500000, 'type' => 'contract',
                'join' => '2023-01-10', 'designation' => 'Staff', 'grade' => 'G5',
            ],
            [
                'code' => 'EMP-008', 'first_name' => 'Doni', 'last_name' => 'Kusuma',
                'dept' => 'IT', 'pos' => 'IT-MGR', 'branch' => 'pusat',
                'gender' => 'male', 'salary' => 19000000, 'type' => 'permanent',
                'join' => '2021-04-01', 'designation' => 'Manager', 'grade' => 'G2',
            ],
            [
                'code' => 'EMP-009', 'first_name' => 'Eka', 'last_name' => 'Putri',
                'dept' => 'IT', 'pos' => 'IT-STF', 'branch' => 'pusat',
                'gender' => 'female', 'salary' => 8000000, 'type' => 'permanent',
                'join' => '2022-08-01', 'designation' => 'Staff', 'grade' => 'G3',
            ],
            [
                'code' => 'EMP-010', 'first_name' => 'Hasan', 'last_name' => 'Basri',
                'dept' => 'OPS', 'pos' => 'OPS-STF', 'branch' => 'surabaya',
                'gender' => 'male', 'salary' => 6000000, 'type' => 'permanent',
                'join' => '2022-11-01', 'designation' => 'Staff', 'grade' => 'G4',
            ],
            [
                'code' => 'EMP-011', 'first_name' => 'Fitriani', 'last_name' => '',
                'dept' => 'OPS', 'pos' => 'OPS-KASIR', 'branch' => 'pusat',
                'gender' => 'female', 'salary' => 5000000, 'type' => 'permanent',
                'join' => '2023-03-01', 'designation' => 'Staff', 'grade' => 'G5',
            ],
            [
                'code' => 'EMP-012', 'first_name' => 'Anton', 'last_name' => 'Wijaya',
                'dept' => 'PRD', 'pos' => 'OPS-STF', 'branch' => 'surabaya',
                'gender' => 'male', 'salary' => 5500000, 'type' => 'contract',
                'join' => '2023-06-15', 'designation' => 'Operator', 'grade' => 'G5',
            ],
            [
                'code' => 'EMP-013', 'first_name' => 'Ratna', 'last_name' => 'Sari',
                'dept' => 'HRGA', 'pos' => 'HR-STF', 'branch' => 'bandung',
                'gender' => 'female', 'salary' => 6000000, 'type' => 'permanent',
                'join' => '2022-09-01', 'designation' => 'Staff', 'grade' => 'G4',
            ],
            [
                'code' => 'EMP-014', 'first_name' => 'Bambang', 'last_name' => '',
                'dept' => 'FA', 'pos' => 'FA-STF', 'branch' => 'bandung',
                'gender' => 'male', 'salary' => 6500000, 'type' => 'permanent',
                'join' => '2023-02-01', 'designation' => 'Staff', 'grade' => 'G4',
            ],
            [
                'code' => 'EMP-015', 'first_name' => 'Citra', 'last_name' => 'Dewi',
                'dept' => 'SM', 'pos' => 'SM-MKT', 'branch' => 'pusat',
                'gender' => 'female', 'salary' => 7500000, 'type' => 'permanent',
                'join' => '2022-07-15', 'designation' => 'Staff Senior', 'grade' => 'G3',
            ],
        ];

        $designations = Designation::where('company_id', $this->companyId)->get()->keyBy('name');
        $grades = Grade::where('company_id', $this->companyId)->get()->keyBy('code');

        foreach ($staff as $s) {
            $emp = Employee::create([
                'company_id' => $this->companyId,
                'branch_id' => $this->branches[$s['branch']],
                'department_id' => $this->departments[$s['dept']],
                'position_id' => $this->positions[$s['pos']],
                'designation_id' => $designations[$s['designation']]->id ?? null,
                'grade_id' => $grades[$s['grade']]->id ?? null,
                'employee_code' => $s['code'],
                'first_name' => $s['first_name'],
                'last_name' => $s['last_name'],
                'email' => strtolower($s['first_name']) . '@maju.test',
                'phone' => '08' . rand(100000000, 999999999),
                'gender' => $s['gender'],
                'birth_date' => '1990-' . rand(1, 12) . '-' . rand(1, 28),
                'birth_place' => $s['gender'] === 'male' ? 'Jakarta' : 'Bandung',
                'religion' => 'Islam',
                'marital_status' => collect(['single', 'married'])->random(),
                'nationality' => 'Indonesia',
                'id_number' => '3174' . rand(100000000000, 999999999999),
                'tax_number' => '12.345.' . rand(100, 999) . '.0-012.000',
                'bpjs_kesehatan' => '0000' . rand(10000000000, 99999999999),
                'bpjs_ketenagakerjaan' => '0000' . rand(10000000000, 99999999999),
                'address' => 'Jl. Contoh No. ' . rand(1, 200),
                'city' => $s['branch'] === 'bandung' ? 'Bandung' : ($s['branch'] === 'surabaya' ? 'Surabaya' : 'Jakarta Selatan'),
                'province' => $s['branch'] === 'bandung' ? 'Jawa Barat' : ($s['branch'] === 'surabaya' ? 'Jawa Timur' : 'DKI Jakarta'),
                'postal_code' => rand(10000, 99999),
                'join_date' => $s['join'],
                'contract_start' => $s['join'],
                'contract_end' => $s['type'] === 'permanent' ? null : Carbon::parse($s['join'])->addYear()->format('Y-m-d'),
                'employee_type' => $s['type'],
                'status' => 'active',
                'basic_salary' => $s['salary'],
                'bank_name' => collect(['BCA', 'Mandiri', 'BNI', 'BRI'])->random(),
                'bank_account_number' => rand(1000000000, 9999999999),
                'bank_account_name' => $s['first_name'] . ' ' . $s['last_name'],
            ]);
            $this->employees[$s['first_name']] = $emp->id;
            $this->jobTitleMap[$s['first_name']] = $s['pos'];
        }

        $this->command->info('  [7/18] Employees: 15 created.');
    }

    // ============================================================
    // 8. ROLES & PERMISSIONS
    // ============================================================
    private function seedRolesAndPermissions(): void
    {
        // Always truncate permission-related tables so data is fresh and predictable
        DB::table('role_permissions')->truncate();
        Permission::truncate();
        Role::truncate();

        $perms = [
            ['name' => 'Dashboard', 'slug' => 'dashboard', 'group' => 'Umum'],

            ['name' => 'Lihat Employee', 'slug' => 'employee.view', 'group' => 'Karyawan'],
            ['name' => 'Tambah Employee', 'slug' => 'employee.create', 'group' => 'Karyawan'],
            ['name' => 'Edit Employee', 'slug' => 'employee.edit', 'group' => 'Karyawan'],
            ['name' => 'Hapus Employee', 'slug' => 'employee.delete', 'group' => 'Karyawan'],

            ['name' => 'Lihat Attendance', 'slug' => 'attendance.view', 'group' => 'Kehadiran'],
            ['name' => 'Kelola Attendance', 'slug' => 'attendance.manage', 'group' => 'Kehadiran'],

            ['name' => 'Lihat Leave', 'slug' => 'leave.view', 'group' => 'Cuti'],
            ['name' => 'Approve Leave', 'slug' => 'leave.approve', 'group' => 'Cuti'],

            ['name' => 'Lihat Payroll', 'slug' => 'payroll.view', 'group' => 'Penggajian'],
            ['name' => 'Kelola Payroll', 'slug' => 'payroll.manage', 'group' => 'Penggajian'],

            ['name' => 'Lihat Client', 'slug' => 'client.view', 'group' => 'Klien'],
            ['name' => 'Kelola Client', 'slug' => 'client.manage', 'group' => 'Klien'],

            ['name' => 'Lihat POS', 'slug' => 'pos.view', 'group' => 'POS'],
            ['name' => 'Transaksi POS', 'slug' => 'pos.transact', 'group' => 'POS'],

            ['name' => 'Lihat Project', 'slug' => 'project.view', 'group' => 'Proyek'],
            ['name' => 'Kelola Project', 'slug' => 'project.manage', 'group' => 'Proyek'],

            ['name' => 'Lihat Finance', 'slug' => 'finance.view', 'group' => 'Keuangan'],
            ['name' => 'Kelola Finance', 'slug' => 'finance.manage', 'group' => 'Keuangan'],

            ['name' => 'Lihat Laporan', 'slug' => 'report.view', 'group' => 'Laporan'],

            ['name' => 'Pengaturan Sistem', 'slug' => 'settings.manage', 'group' => 'Sistem'],
        ];

        foreach ($perms as $p) {
            Permission::create($p);
        }

        $roles = [
            ['name' => 'Super Admin', 'slug' => 'super-admin', 'description' => 'Full access to everything', 'is_system' => true],
            ['name' => 'Admin', 'slug' => 'admin', 'description' => 'Administrator with most privileges', 'is_system' => true],
            ['name' => 'Manager', 'slug' => 'manager', 'description' => 'Department manager', 'is_system' => false],
            ['name' => 'Staff', 'slug' => 'staff', 'description' => 'Regular staff', 'is_system' => false],
            ['name' => 'Kasir', 'slug' => 'kasir', 'description' => 'Cashier POS access', 'is_system' => false],
        ];

        foreach ($roles as $r) {
            $this->roles[$r['slug']] = Role::create(array_merge($r, ['company_id' => $this->companyId]))->id;
        }

        $allPermIds = Permission::pluck('id')->toArray();

        // Super Admin & Admin: all permissions
        DB::table('role_permissions')->insert(
            array_map(fn($p) => ['role_id' => $this->roles['super-admin'], 'permission_id' => $p, 'created_at' => now(), 'updated_at' => now()], $allPermIds)
        );
        DB::table('role_permissions')->insert(
            array_map(fn($p) => ['role_id' => $this->roles['admin'], 'permission_id' => $p, 'created_at' => now(), 'updated_at' => now()], $allPermIds)
        );

        // Manager
        $managerPerms = Permission::whereIn('slug', [
            'dashboard', 'employee.view', 'attendance.view', 'attendance.manage',
            'leave.view', 'leave.approve', 'payroll.view',
            'client.view', 'client.manage', 'project.view', 'project.manage',
            'finance.view', 'report.view', 'pos.view',
        ])->pluck('id')->toArray();
        DB::table('role_permissions')->insert(
            array_map(fn($p) => ['role_id' => $this->roles['manager'], 'permission_id' => $p, 'created_at' => now(), 'updated_at' => now()], $managerPerms)
        );

        // Staff
        $staffPerms = Permission::whereIn('slug', [
            'dashboard', 'employee.view', 'attendance.view',
            'leave.view', 'client.view', 'project.view',
            'pos.view', 'report.view',
        ])->pluck('id')->toArray();
        DB::table('role_permissions')->insert(
            array_map(fn($p) => ['role_id' => $this->roles['staff'], 'permission_id' => $p, 'created_at' => now(), 'updated_at' => now()], $staffPerms)
        );

        // Kasir
        $kasirPerms = Permission::whereIn('slug', [
            'dashboard', 'pos.view', 'pos.transact', 'client.view',
        ])->pluck('id')->toArray();
        DB::table('role_permissions')->insert(
            array_map(fn($p) => ['role_id' => $this->roles['kasir'], 'permission_id' => $p, 'created_at' => now(), 'updated_at' => now()], $kasirPerms)
        );

        $this->command->info('  [8/18] Roles & Permissions: seeded.');
    }

    // ============================================================
    // 9. USERS (linked to employees)
    // ============================================================
    private function seedUsers(): void
    {
        DB::table('users')->truncate();

        $roleMap = [
            'DIR-UTAMA' => 'super-admin',
            'HR-MGR' => 'admin',
            'FA-MGR' => 'admin',
            'SM-MGR' => 'manager',
            'IT-MGR' => 'admin',
            'HR-STF' => 'staff',
            'FA-STF' => 'staff',
            'SM-SALES' => 'staff',
            'SM-MKT' => 'staff',
            'IT-STF' => 'staff',
            'OPS-KASIR' => 'kasir',
            'OPS-STF' => 'staff',
        ];

        foreach ($this->employees as $firstName => $empId) {
            $pos = $this->jobTitleMap[$firstName] ?? 'HR-STF';
            $slug = $roleMap[$pos] ?? 'staff';
            User::create([
                'name' => $firstName,
                'email' => strtolower($firstName) . '@maju.test',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_active' => true,
                'employee_id' => $empId,
                'company_id' => $this->companyId,
                'role_id' => $this->roles[$slug] ?? $this->roles['staff'],
            ]);
        }

        $this->command->info('  [9/18] Users: 15 created (login: firstname@maju.test / password).');
    }

    // ============================================================
    // 10. SHIFTS
    // ============================================================
    private function seedShifts(): void
    {
        Shift::truncate();

        $shifts = [
            [
                'name' => 'Pagi',
                'start_time' => '07:00:00',
                'end_time' => '15:00:00',
                'grace_period_minutes' => 15,
                'break_start' => '12:00:00',
                'break_end' => '13:00:00',
                'is_overnight' => false,
            ],
            [
                'name' => 'Siang',
                'start_time' => '15:00:00',
                'end_time' => '23:00:00',
                'grace_period_minutes' => 15,
                'break_start' => '19:00:00',
                'break_end' => '20:00:00',
                'is_overnight' => false,
            ],
            [
                'name' => 'Malam',
                'start_time' => '23:00:00',
                'end_time' => '07:00:00',
                'grace_period_minutes' => 15,
                'break_start' => '03:00:00',
                'break_end' => '04:00:00',
                'is_overnight' => true,
            ],
        ];

        $idx = 0;
        foreach ($shifts as $s) {
            $this->shifts[$s['name']] = Shift::create(array_merge($s, [
                'company_id' => $this->companyId,
                'is_active' => true,
            ]))->id;
            $idx++;
        }

        $this->command->info('  [10/18] Shifts: 3 created.');
    }

    // ============================================================
    // 11. SHIFT EMPLOYEES
    // ============================================================
    private function seedShiftEmployees(): void
    {
        DB::table('shift_employees')->truncate();

        $shiftPagi = $this->shifts['Pagi'];
        $shiftSiang = $this->shifts['Siang'];
        $shiftMalam = $this->shifts['Malam'];

        $pagiStaff = ['Budi', 'Siti', 'Ahmad', 'Dewi', 'Rudi', 'Andi', 'Donni', 'Eka', 'Citra', 'Fitriani'];
        $siangStaff = ['Hasan', 'Anton', 'Bambang', 'Ratna'];
        $malamStaff = ['Maya', 'Ratna'];

        foreach ($pagiStaff as $name) {
            if (isset($this->employees[$name])) {
                ShiftEmployee::create([
                    'shift_id' => $shiftPagi,
                    'employee_id' => $this->employees[$name],
                    'effective_date' => '2026-01-01',
                    'end_date' => null,
                ]);
            }
        }
        foreach ($siangStaff as $name) {
            if (isset($this->employees[$name])) {
                ShiftEmployee::create([
                    'shift_id' => $shiftSiang,
                    'employee_id' => $this->employees[$name],
                    'effective_date' => '2026-01-01',
                    'end_date' => null,
                ]);
            }
        }
        // Malam shift for a few employees
        // Note: Anton is PRD in Surabaya, use malam
        // Also add Hasan for malam rotation
        foreach (['Anton', 'Hasan'] as $name) {
            if (isset($this->employees[$name]) && !ShiftEmployee::where('employee_id', $this->employees[$name])->exists()) {
                ShiftEmployee::create([
                    'shift_id' => $shiftMalam,
                    'employee_id' => $this->employees[$name],
                    'effective_date' => '2026-01-01',
                    'end_date' => null,
                ]);
            }
        }

        $this->command->info('  [11/18] Shift Employees: assigned.');
    }

    // ============================================================
    // 12. ATTENDANCE (30 days)
    // ============================================================
    private function seedAttendance(): void
    {
        DB::table('attendances')->truncate();

        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now()->subDay(); // yesterday - today is running day

        $officeLocations = [
            [-6.2088, 106.8456],   // Jakarta Pusat
            [-6.9147, 107.6098],   // Bandung
            [-7.2575, 112.7521],   // Surabaya
        ];

        $totalRecords = 0;

        foreach ($this->employees as $firstName => $empId) {
            $shiftId = $this->shifts['Pagi']; // default
            $shiftEmployee = ShiftEmployee::where('employee_id', $empId)->first();
            if ($shiftEmployee) {
                $shiftId = $shiftEmployee->shift_id;
            }

            $employeeBranch = Employee::find($empId)->branch_id ?? $this->branches['pusat'];
            $lat = $officeLocations[0][0] + (mt_rand(-50, 50) / 10000);
            $lng = $officeLocations[0][1] + (mt_rand(-50, 50) / 10000);
            if ($employeeBranch == $this->branches['bandung']) {
                $lat = $officeLocations[1][0] + (mt_rand(-50, 50) / 10000);
                $lng = $officeLocations[1][1] + (mt_rand(-50, 50) / 10000);
            } elseif ($employeeBranch == $this->branches['surabaya']) {
                $lat = $officeLocations[2][0] + (mt_rand(-50, 50) / 10000);
                $lng = $officeLocations[2][1] + (mt_rand(-50, 50) / 10000);
            }

            for ($d = clone $startDate; $d->lte($endDate); $d->addDay()) {
                if ($d->isWeekend()) {
                    continue; // skip weekends
                }

                $date = $d->format('Y-m-d');
                $roll = mt_rand(1, 100);

                if ($roll <= 5) {
                    // 5% absent
                    Attendance::create([
                        'employee_id' => $empId,
                        'shift_id' => $shiftId,
                        'date' => $date,
                        'clock_in' => null,
                        'clock_out' => null,
                        'status' => 'absent',
                        'late_minutes' => 0,
                        'early_departure_minutes' => 0,
                        'overtime_minutes' => 0,
                        'work_type' => 'office',
                    ]);
                } elseif ($roll <= 10) {
                    // 5% leave
                    Attendance::create([
                        'employee_id' => $empId,
                        'shift_id' => $shiftId,
                        'date' => $date,
                        'clock_in' => null,
                        'clock_out' => null,
                        'status' => 'leave',
                        'late_minutes' => 0,
                        'early_departure_minutes' => 0,
                        'overtime_minutes' => 0,
                        'work_type' => 'office',
                    ]);
                } elseif ($roll <= 20) {
                    // 10% late
                    $lateMin = rand(5, 60);
                    $clockInTime = Carbon::parse($date . ' 07:00:00')->addMinutes($lateMin);
                    $clockOutTime = Carbon::parse($date . ' 16:00:00');
                    Attendance::create([
                        'employee_id' => $empId,
                        'shift_id' => $shiftId,
                        'date' => $date,
                        'clock_in' => $clockInTime,
                        'clock_out' => $clockOutTime,
                        'clock_in_lat' => $lat,
                        'clock_in_lng' => $lng,
                        'clock_out_lat' => $lat,
                        'clock_out_lng' => $lng,
                        'status' => 'late',
                        'late_minutes' => $lateMin,
                        'early_departure_minutes' => 0,
                        'overtime_minutes' => rand(0, 60),
                        'work_type' => 'office',
                    ]);
                } else {
                    // 80% present
                    $clockIn = Carbon::parse($date . ' 06:' . rand(45, 59) . ':' . rand(0, 59));
                    $clockOut = Carbon::parse($date . ' 16:' . rand(0, 30) . ':' . rand(0, 59));
                    Attendance::create([
                        'employee_id' => $empId,
                        'shift_id' => $shiftId,
                        'date' => $date,
                        'clock_in' => $clockIn,
                        'clock_out' => $clockOut,
                        'clock_in_lat' => $lat,
                        'clock_in_lng' => $lng,
                        'clock_out_lat' => $lat,
                        'clock_out_lng' => $lng,
                        'status' => 'present',
                        'late_minutes' => 0,
                        'early_departure_minutes' => 0,
                        'overtime_minutes' => rand(0, 120),
                        'work_type' => collect(['office', 'wfh'])->random(),
                    ]);
                }
                $totalRecords++;
            }
        }

        $this->command->info("  [12/18] Attendance: {$totalRecords} records (30 days) created.");
    }

    // ============================================================
    // 13. LEAVE TYPES
    // ============================================================
    private function seedLeaveTypes(): void
    {
        LeaveType::truncate();

        $types = [
            [
                'code' => 'TAHUNAN', 'name' => 'Cuti Tahunan',
                'description' => 'Cuti tahunan untuk karyawan tetap',
                'default_days' => 12, 'max_days' => 12, 'is_annual' => true,
                'is_paid' => true, 'require_attachment' => false, 'require_approval' => true,
                'min_approval_level' => 1, 'applicable_gender' => 'all', 'applicable_marital' => 'all',
                'color' => '#4f46e5',
            ],
            [
                'code' => 'SAKIT', 'name' => 'Cuti Sakit',
                'description' => 'Cuti karena sakit dengan surat dokter',
                'default_days' => 14, 'max_days' => 90, 'is_annual' => false,
                'is_paid' => true, 'require_attachment' => true, 'require_approval' => true,
                'min_approval_level' => 1, 'applicable_gender' => 'all', 'applicable_marital' => 'all',
                'color' => '#ef4444',
            ],
            [
                'code' => 'MELAHIRKAN', 'name' => 'Cuti Melahirkan',
                'description' => 'Cuti melahirkan untuk karyawati',
                'default_days' => 90, 'max_days' => 90, 'is_annual' => false,
                'is_paid' => true, 'require_attachment' => false, 'require_approval' => true,
                'min_approval_level' => 2, 'applicable_gender' => 'female', 'applicable_marital' => 'married',
                'color' => '#ec4899',
            ],
            [
                'code' => 'MENIKAH', 'name' => 'Cuti Menikah',
                'description' => 'Cuti pernikahan untuk karyawan',
                'default_days' => 3, 'max_days' => 3, 'is_annual' => false,
                'is_paid' => true, 'require_attachment' => false, 'require_approval' => true,
                'min_approval_level' => 1, 'applicable_gender' => 'all', 'applicable_marital' => 'single',
                'color' => '#f59e0b',
            ],
        ];

        $leaveTypeIds = [];
        foreach ($types as $t) {
            $lt = LeaveType::create(array_merge($t, [
                'company_id' => $this->companyId,
                'is_active' => true,
            ]));
            $leaveTypeIds[$t['code']] = $lt->id;
        }

        $this->leaveTypeIds = $leaveTypeIds;
        $this->command->info('  [13/18] Leave Types: 4 created.');
    }

    // ============================================================
    // 14. LEAVE BALANCES
    // ============================================================
    private function seedLeaveBalances(): void
    {
        LeaveBalance::truncate();

        foreach ($this->employees as $firstName => $empId) {
            LeaveBalance::create([
                'employee_id' => $empId,
                'leave_type_id' => $this->leaveTypeIds['TAHUNAN'],
                'year' => 2026,
                'total_days' => 12,
                'used_days' => rand(0, 5),
                'remaining_days' => 0,
                'carry_forward' => 0,
            ]);
            LeaveBalance::create([
                'employee_id' => $empId,
                'leave_type_id' => $this->leaveTypeIds['SAKIT'],
                'year' => 2026,
                'total_days' => 14,
                'used_days' => rand(0, 3),
                'remaining_days' => 0,
                'carry_forward' => 0,
            ]);
        }

        // Update remaining_days after used_days
        LeaveBalance::query()->update(['remaining_days' => DB::raw('total_days - used_days')]);

        $this->command->info('  [14/18] Leave Balances: created for all employees.');
    }

    // ============================================================
    // 15. LEAVES (sample leave requests)
    // ============================================================
    private function seedLeaves(): void
    {
        Leave::truncate();

        $leaveRequests = [
            ['emp' => 'Dewi', 'type' => 'TAHUNAN', 'start' => '2026-05-20', 'end' => '2026-05-22', 'status' => 'approved'],
            ['emp' => 'Rudi', 'type' => 'TAHUNAN', 'start' => '2026-05-15', 'end' => '2026-05-16', 'status' => 'approved'],
            ['emp' => 'Eka', 'type' => 'SAKIT', 'start' => '2026-05-10', 'end' => '2026-05-11', 'status' => 'approved'],
            ['emp' => 'Maya', 'type' => 'TAHUNAN', 'start' => '2026-06-01', 'end' => '2026-06-03', 'status' => 'pending'],
            ['emp' => 'Fitriani', 'type' => 'SAKIT', 'start' => '2026-05-25', 'end' => '2026-05-25', 'status' => 'approved'],
            ['emp' => 'Bambang', 'type' => 'TAHUNAN', 'start' => '2026-06-10', 'end' => '2026-06-12', 'status' => 'pending'],
            ['emp' => 'Citra', 'type' => 'TAHUNAN', 'start' => '2026-05-05', 'end' => '2026-05-05', 'status' => 'approved'],
        ];

        foreach ($leaveRequests as $lr) {
            $start = Carbon::parse($lr['start']);
            $end = Carbon::parse($lr['end']);
            Leave::create([
                'employee_id' => $this->employees[$lr['emp']],
                'leave_type_id' => $this->leaveTypeIds[$lr['type']],
                'start_date' => $lr['start'],
                'end_date' => $lr['end'],
                'total_days' => $start->diffInDays($end) + 1,
                'reason' => $lr['type'] === 'SAKIT' ? 'Sakit demam berdarah' : 'Keperluan keluarga',
                'status' => $lr['status'],
            ]);
        }

        $this->command->info('  [15/18] Leaves: 7 sample leave requests created.');
    }

    // ============================================================
    // 16. SALARY COMPONENTS
    // ============================================================
    private function seedSalaryComponents(): void
    {
        SalaryComponent::truncate();

        $components = [
            ['code' => 'GP', 'name' => 'Gaji Pokok', 'type' => 'income', 'calculation_type' => 'fixed', 'amount' => 0, 'is_taxable' => true, 'is_mandatory' => true, 'sort_order' => 1],
            ['code' => 'TJ', 'name' => 'Tunjangan Jabatan', 'type' => 'income', 'calculation_type' => 'fixed', 'amount' => 0, 'is_taxable' => true, 'is_mandatory' => false, 'sort_order' => 2],
            ['code' => 'TT', 'name' => 'Tunjangan Transport', 'type' => 'income', 'calculation_type' => 'fixed', 'amount' => 0, 'is_taxable' => false, 'is_mandatory' => false, 'sort_order' => 3],
            ['code' => 'TM', 'name' => 'Tunjangan Makan', 'type' => 'income', 'calculation_type' => 'fixed', 'amount' => 0, 'is_taxable' => false, 'is_mandatory' => false, 'sort_order' => 4],
            ['code' => 'BPJS_TK', 'name' => 'BPJS Ketenagakerjaan', 'type' => 'deduction', 'calculation_type' => 'percentage', 'amount' => 2, 'is_taxable' => false, 'is_mandatory' => true, 'sort_order' => 10],
            ['code' => 'BPJS_KES', 'name' => 'BPJS Kesehatan', 'type' => 'deduction', 'calculation_type' => 'percentage', 'amount' => 1, 'is_taxable' => false, 'is_mandatory' => true, 'sort_order' => 11],
            ['code' => 'PPH21', 'name' => 'PPh 21', 'type' => 'deduction', 'calculation_type' => 'percentage', 'amount' => 5, 'is_taxable' => false, 'is_mandatory' => true, 'sort_order' => 12],
        ];

        $this->salaryComponentIds = [];
        foreach ($components as $c) {
            $sc = SalaryComponent::create(array_merge($c, [
                'company_id' => $this->companyId,
                'is_active' => true,
            ]));
            $this->salaryComponentIds[$c['code']] = $sc->id;
        }

        $this->command->info('  [16/18] Salary Components: 7 created.');
    }

    // ============================================================
    // 17. EMPLOYEE SALARY COMPONENTS
    // ============================================================
    private function seedEmployeeSalaryComponents(): void
    {
        DB::table('employee_salary_components')->truncate();

        foreach ($this->employees as $firstName => $empId) {
            $emp = Employee::find($empId);
            $baseSalary = $emp->basic_salary;

            // Gaji Pokok
            EmployeeSalaryComponent::create([
                'employee_id' => $empId,
                'salary_component_id' => $this->salaryComponentIds['GP'],
                'amount' => $baseSalary,
                'effective_date' => $emp->join_date,
                'end_date' => null,
            ]);

            // Tunjangan Jabatan (only managers & directors)
            if (in_array($this->jobTitleMap[$firstName] ?? '', ['DIR-UTAMA', 'HR-MGR', 'FA-MGR', 'SM-MGR', 'IT-MGR'])) {
                EmployeeSalaryComponent::create([
                    'employee_id' => $empId,
                    'salary_component_id' => $this->salaryComponentIds['TJ'],
                    'amount' => round($baseSalary * 0.2),
                    'effective_date' => $emp->join_date,
                    'end_date' => null,
                ]);
            }

            // Tunjangan Transport
            EmployeeSalaryComponent::create([
                'employee_id' => $empId,
                'salary_component_id' => $this->salaryComponentIds['TT'],
                'amount' => rand(300000, 1000000),
                'effective_date' => $emp->join_date,
                'end_date' => null,
            ]);

            // Tunjangan Makan
            EmployeeSalaryComponent::create([
                'employee_id' => $empId,
                'salary_component_id' => $this->salaryComponentIds['TM'],
                'amount' => rand(200000, 600000),
                'effective_date' => $emp->join_date,
                'end_date' => null,
            ]);

            // BPJS TK (2% of basic salary)
            EmployeeSalaryComponent::create([
                'employee_id' => $empId,
                'salary_component_id' => $this->salaryComponentIds['BPJS_TK'],
                'amount' => round($baseSalary * 0.02),
                'effective_date' => $emp->join_date,
                'end_date' => null,
            ]);

            // BPJS Kesehatan (1% of basic salary)
            EmployeeSalaryComponent::create([
                'employee_id' => $empId,
                'salary_component_id' => $this->salaryComponentIds['BPJS_KES'],
                'amount' => round($baseSalary * 0.01),
                'effective_date' => $emp->join_date,
                'end_date' => null,
            ]);

            // PPh 21 (5% of basic salary)
            EmployeeSalaryComponent::create([
                'employee_id' => $empId,
                'salary_component_id' => $this->salaryComponentIds['PPH21'],
                'amount' => round($baseSalary * 0.05),
                'effective_date' => $emp->join_date,
                'end_date' => null,
            ]);
        }

        $this->command->info('  [17/18] Employee Salary Components: created for all employees.');
    }

    // ============================================================
    // 18. PAYROLL PERIOD + PAYROLL
    // ============================================================
    private function seedPayrollPeriod(): void
    {
        PayrollPeriod::truncate();
        Payroll::truncate();
        PayrollItem::truncate();
        PaySlip::truncate();

        $today = Carbon::now();
        $periodStart = $today->copy()->startOfMonth();
        $periodEnd = $today->copy()->endOfMonth();

        $period = PayrollPeriod::create([
            'company_id' => $this->companyId,
            'period_code' => 'PAY-' . $today->format('Ym'),
            'start_date' => $periodStart->format('Y-m-d'),
            'end_date' => $periodEnd->format('Y-m-d'),
            'payment_date' => $periodEnd->copy()->subDay()->format('Y-m-d'),
            'status' => 'completed',
            'total_gross' => 0,
            'total_deductions' => 0,
            'total_net' => 0,
            'total_employees' => 0,
        ]);

        $totalGross = 0;
        $totalDeductions = 0;
        $totalNet = 0;
        $count = 0;

        foreach ($this->employees as $empId) {
            $emp = Employee::find($empId);
            $components = EmployeeSalaryComponent::where('employee_id', $empId)->with('salaryComponent')->get();

            $grossSalary = 0;
            $deductions = 0;
            $incomeTotal = 0;
            $deductionTotal = 0;

            $incomeComponents = $components->filter(fn($c) => $c->salaryComponent->type === 'income');
            $deductionComponents = $components->filter(fn($c) => $c->salaryComponent->type === 'deduction');

            foreach ($incomeComponents as $ic) {
                $incomeTotal += $ic->amount;
            }
            foreach ($deductionComponents as $dc) {
                $deductionTotal += $dc->amount;
            }

            $grossSalary = $incomeTotal;
            $deductions = $deductionTotal;

            // Count actual attendances for the employee
            $attendanceDays = Attendance::where('employee_id', $empId)
                ->whereBetween('date', [$periodStart->format('Y-m-d'), $periodEnd->format('Y-m-d')])
                ->whereIn('status', ['present', 'late', 'leave'])
                ->count();

            $leaveDays = Attendance::where('employee_id', $empId)
                ->whereBetween('date', [$periodStart->format('Y-m-d'), $periodEnd->format('Y-m-d')])
                ->where('status', 'leave')
                ->count();

            $overtimeHours = Attendance::where('employee_id', $empId)
                ->whereBetween('date', [$periodStart->format('Y-m-d'), $periodEnd->format('Y-m-d')])
                ->sum('overtime_minutes') / 60;

            $overtimePay = round($overtimeHours * ($emp->basic_salary / 173) * 1.5);

            $netSalary = $grossSalary - $deductions + $overtimePay;

            $payroll = Payroll::create([
                'period_id' => $period->id,
                'employee_id' => $empId,
                'gross_salary' => $grossSalary,
                'total_income_components' => $incomeTotal,
                'total_deduction_components' => $deductionTotal,
                'pph21_amount' => round($emp->basic_salary * 0.05),
                'bpjs_tk_jht' => round($emp->basic_salary * 0.02),
                'bpjs_tk_jp' => round($emp->basic_salary * 0.003),
                'bpjs_tk_jkk' => round($emp->basic_salary * 0.0024),
                'bpjs_tk_jkm' => round($emp->basic_salary * 0.003),
                'bpjs_kes' => round($emp->basic_salary * 0.01),
                'net_salary' => $netSalary,
                'attendance_days' => $attendanceDays,
                'leave_days' => $leaveDays,
                'overtime_hours' => round($overtimeHours, 1),
                'overtime_pay' => $overtimePay,
                'status' => 'finalized',
            ]);

            // Payroll Items
            foreach ($incomeComponents as $ic) {
                PayrollItem::create([
                    'payroll_id' => $payroll->id,
                    'salary_component_id' => $ic->salary_component_id,
                    'name' => $ic->salaryComponent->name,
                    'type' => 'income',
                    'amount' => $ic->amount,
                ]);
            }
            foreach ($deductionComponents as $dc) {
                PayrollItem::create([
                    'payroll_id' => $payroll->id,
                    'salary_component_id' => $dc->salary_component_id,
                    'name' => $dc->salaryComponent->name,
                    'type' => 'deduction',
                    'amount' => $dc->amount,
                ]);
            }

            // Pay Slip
            PaySlip::create([
                'payroll_id' => $payroll->id,
                'slip_number' => 'SLIP-' . $today->format('Ym') . '-' . str_pad($empId, 4, '0', STR_PAD_LEFT),
                'file_path' => null,
                'sent_at' => null,
            ]);

            $totalGross += $grossSalary;
            $totalDeductions += $deductions;
            $totalNet += $netSalary;
            $count++;
        }

        // Update period totals
        $period->update([
            'total_gross' => $totalGross,
            'total_deductions' => $totalDeductions,
            'total_net' => $totalNet,
            'total_employees' => $count,
        ]);

        $this->command->info('  [18/18] Payroll Period + Payrolls + PaySlips: created for all employees.');
    }

    // ============================================================
    // PAYMENT METHODS
    // ============================================================
    private function seedPaymentMethods(): void
    {
        PaymentMethod::truncate();

        $methods = ['Tunai', 'Transfer Bank', 'Kartu Debit', 'Kartu Kredit', 'QRIS', 'GoPay/OVO/Dana'];
        foreach ($methods as $m) {
            PaymentMethod::create([
                'company_id' => $this->companyId,
                'name' => $m,
                'code' => strtoupper(str_replace(['/', ' '], '_', $m)),
                'is_active' => true,
            ]);
        }
        $this->command->info('       Payment Methods: 6 created.');
    }

    // ============================================================
    // COA CATEGORIES
    // ============================================================
    private function seedCoaCategories(): void
    {
        CoaCategory::truncate();
        $cats = [
            ['code' => 'ASET', 'name' => 'Aset / Asset', 'normal_balance' => 'debit'],
            ['code' => 'LIAB', 'name' => 'Kewajiban / Liability', 'normal_balance' => 'credit'],
            ['code' => 'EKUITAS', 'name' => 'Ekuitas / Equity', 'normal_balance' => 'credit'],
            ['code' => 'PENDAPATAN', 'name' => 'Pendapatan / Revenue', 'normal_balance' => 'credit'],
            ['code' => 'BEBAN', 'name' => 'Beban / Expense', 'normal_balance' => 'debit'],
        ];
        $this->coaCategoryIds = [];
        foreach ($cats as $c) {
            $cc = CoaCategory::create(array_merge($c, [
                'company_id' => $this->companyId,
                'is_active' => true,
            ]));
            $this->coaCategoryIds[$c['code']] = $cc->id;
        }
        $this->command->info('       COA Categories: 5 created.');
    }

    // ============================================================
    // CHART OF ACCOUNTS
    // ============================================================
    private function seedCoa(): void
    {
        DB::table('coa')->truncate();

        $accounts = [
            // ASSET HEADERS (1-0000)
            ['cat' => 'ASET', 'code' => '1-0000', 'name' => 'Aset Lancar', 'is_header' => true],
            ['cat' => 'ASET', 'code' => '1-1000', 'name' => 'Kas & Bank', 'is_header' => true],
            ['cat' => 'ASET', 'code' => '1-1100', 'name' => 'Kas Besar', 'is_header' => false, 'balance' => 50000000, 'parent_code' => '1-1000'],
            ['cat' => 'ASET', 'code' => '1-1200', 'name' => 'Bank BCA', 'is_header' => false, 'balance' => 250000000, 'parent_code' => '1-1000'],
            ['cat' => 'ASET', 'code' => '1-1300', 'name' => 'Bank Mandiri', 'is_header' => false, 'balance' => 150000000, 'parent_code' => '1-1000'],
            ['cat' => 'ASET', 'code' => '1-2000', 'name' => 'Piutang Usaha', 'is_header' => false, 'balance' => 75000000],
            ['cat' => 'ASET', 'code' => '1-3000', 'name' => 'Persediaan', 'is_header' => true],
            ['cat' => 'ASET', 'code' => '1-3100', 'name' => 'Persediaan Barang Dagang', 'is_header' => false, 'balance' => 120000000, 'parent_code' => '1-3000'],
            ['cat' => 'ASET', 'code' => '1-4000', 'name' => 'Perlengkapan Kantor', 'is_header' => false, 'balance' => 15000000],

            // ASSET NON-LANCAR
            ['cat' => 'ASET', 'code' => '1-5000', 'name' => 'Aset Tetap', 'is_header' => true],
            ['cat' => 'ASET', 'code' => '1-5100', 'name' => 'Peralatan Kantor', 'is_header' => false, 'balance' => 200000000, 'parent_code' => '1-5000'],
            ['cat' => 'ASET', 'code' => '1-5200', 'name' => 'Kendaraan', 'is_header' => false, 'balance' => 500000000, 'parent_code' => '1-5000'],
            ['cat' => 'ASET', 'code' => '1-5300', 'name' => 'Akumulasi Penyusutan', 'is_header' => false, 'balance' => -75000000, 'parent_code' => '1-5000'],

            // LIABILITY (2-0000)
            ['cat' => 'LIAB', 'code' => '2-0000', 'name' => 'Kewajiban Lancar', 'is_header' => true],
            ['cat' => 'LIAB', 'code' => '2-1000', 'name' => 'Utang Usaha', 'is_header' => false, 'balance' => 45000000],
            ['cat' => 'LIAB', 'code' => '2-2000', 'name' => 'Utang Pajak', 'is_header' => false, 'balance' => 15000000],
            ['cat' => 'LIAB', 'code' => '2-3000', 'name' => 'Utang Gaji', 'is_header' => false, 'balance' => 0],
            ['cat' => 'LIAB', 'code' => '2-4000', 'name' => 'Utang Bank Jangka Panjang', 'is_header' => false, 'balance' => 300000000],

            // EQUITY (3-0000)
            ['cat' => 'EKUITAS', 'code' => '3-0000', 'name' => 'Modal', 'is_header' => true],
            ['cat' => 'EKUITAS', 'code' => '3-1000', 'name' => 'Modal Disetor', 'is_header' => false, 'balance' => 1000000000],
            ['cat' => 'EKUITAS', 'code' => '3-2000', 'name' => 'Laba Ditahan', 'is_header' => false, 'balance' => 250000000],
            ['cat' => 'EKUITAS', 'code' => '3-3000', 'name' => 'Laba Tahun Berjalan', 'is_header' => false, 'balance' => 0],

            // REVENUE (4-0000)
            ['cat' => 'PENDAPATAN', 'code' => '4-0000', 'name' => 'Pendapatan Operasional', 'is_header' => true],
            ['cat' => 'PENDAPATAN', 'code' => '4-1000', 'name' => 'Pendapatan Penjualan', 'is_header' => false, 'balance' => 0],
            ['cat' => 'PENDAPATAN', 'code' => '4-2000', 'name' => 'Pendapatan Jasa', 'is_header' => false, 'balance' => 0],
            ['cat' => 'PENDAPATAN', 'code' => '4-3000', 'name' => 'Pendapatan Lain-lain', 'is_header' => false, 'balance' => 0],

            // EXPENSE (5-0000)
            ['cat' => 'BEBAN', 'code' => '5-0000', 'name' => 'Beban Operasional', 'is_header' => true],
            ['cat' => 'BEBAN', 'code' => '5-1000', 'name' => 'Beban Gaji & Upah', 'is_header' => false, 'balance' => 0],
            ['cat' => 'BEBAN', 'code' => '5-2000', 'name' => 'Beban Sewa', 'is_header' => false, 'balance' => 0],
            ['cat' => 'BEBAN', 'code' => '5-3000', 'name' => 'Beban Listrik & Air', 'is_header' => false, 'balance' => 0],
            ['cat' => 'BEBAN', 'code' => '5-4000', 'name' => 'Beban Internet & Telepon', 'is_header' => false, 'balance' => 0],
            ['cat' => 'BEBAN', 'code' => '5-5000', 'name' => 'Beban ATK & Perlengkapan', 'is_header' => false, 'balance' => 0],
            ['cat' => 'BEBAN', 'code' => '5-6000', 'name' => 'Beban Transportasi', 'is_header' => false, 'balance' => 0],
            ['cat' => 'BEBAN', 'code' => '5-7000', 'name' => 'Beban Penyusutan', 'is_header' => false, 'balance' => 0],
            ['cat' => 'BEBAN', 'code' => '5-8000', 'name' => 'Beban Pajak', 'is_header' => false, 'balance' => 0],
        ];

        $coaMap = [];
        // Pass 1: create header accounts
        foreach ($accounts as $a) {
            $coa = Coa::create([
                'company_id' => $this->companyId,
                'category_id' => $this->coaCategoryIds[$a['cat']],
                'parent_id' => null,
                'code' => $a['code'],
                'name' => $a['name'],
                'description' => $a['name'],
                'is_header' => $a['is_header'],
                'opening_balance' => $a['balance'] ?? 0,
                'balance_type' => $a['cat'] === 'ASET' || $a['cat'] === 'BEBAN' ? 'debit' : 'credit',
                'is_active' => true,
            ]);
            $coaMap[$a['code']] = $coa->id;
        }

        // Pass 2: set parent relationships
        foreach ($accounts as $a) {
            if (!empty($a['parent_code']) && isset($coaMap[$a['parent_code']], $coaMap[$a['code']])) {
                Coa::where('id', $coaMap[$a['code']])->update(['parent_id' => $coaMap[$a['parent_code']]]);
            }
        }

        $this->command->info('       COA: ' . count($accounts) . ' accounts created.');
    }

    // ============================================================
    // CLIENTS
    // ============================================================
    private function seedClients(): void
    {
        Client::truncate();
        ClientContact::truncate();

        $clients = [
            [
                'code' => 'CL-001', 'name' => 'PT Sumber Makmur', 'type' => 'company', 'industry' => 'Distribusi',
                'tax_id' => '02.345.678.9-011.000', 'city' => 'Jakarta Pusat', 'province' => 'DKI Jakarta',
                'status' => 'active', 'contacts' => [
                    ['first' => 'Hendra', 'last' => 'Gunawan', 'position' => 'Purchasing Manager', 'email' => 'hendra@sumbermakmur.co.id', 'phone' => '081234567890', 'primary' => true],
                ],
            ],
            [
                'code' => 'CL-002', 'name' => 'CV Karya Utama', 'type' => 'company', 'industry' => 'Konstruksi',
                'tax_id' => '03.456.789.0-012.000', 'city' => 'Bandung', 'province' => 'Jawa Barat',
                'status' => 'active', 'contacts' => [
                    ['first' => 'Dian', 'last' => 'Permata', 'position' => 'Direktur', 'email' => 'dian@karyautama.co.id', 'phone' => '082345678901', 'primary' => true],
                ],
            ],
            [
                'code' => 'CL-003', 'name' => 'PT Nusantara Teknologi', 'type' => 'company', 'industry' => 'Teknologi',
                'tax_id' => '04.567.890.1-013.000', 'city' => 'Surabaya', 'province' => 'Jawa Timur',
                'status' => 'active', 'contacts' => [
                    ['first' => 'Rizky', 'last' => 'Fadhilah', 'position' => 'CTO', 'email' => 'rizky@nusantara.co.id', 'phone' => '083456789012', 'primary' => true],
                    ['first' => 'Nina', 'last' => 'Agustina', 'position' => 'Finance Manager', 'email' => 'nina@nusantara.co.id', 'phone' => '083456789013', 'primary' => false],
                ],
            ],
            [
                'code' => 'CL-004', 'name' => 'UD Berkah Abadi', 'type' => 'individual', 'industry' => 'Retail',
                'tax_id' => null, 'city' => 'Jakarta Timur', 'province' => 'DKI Jakarta',
                'status' => 'active', 'contacts' => [
                    ['first' => 'Suparno', 'last' => '', 'position' => 'Pemilik', 'email' => 'suparno@berkahabadi.com', 'phone' => '084567890123', 'primary' => true],
                ],
            ],
            [
                'code' => 'CL-005', 'name' => 'PT Global Investama', 'type' => 'company', 'industry' => 'Keuangan',
                'tax_id' => '05.678.901.2-014.000', 'city' => 'Jakarta Selatan', 'province' => 'DKI Jakarta',
                'status' => 'inactive', 'contacts' => [
                    ['first' => 'Wahyudi', 'last' => 'Prasetyo', 'position' => 'CEO', 'email' => 'wahyudi@globalinvestama.com', 'phone' => '085678901234', 'primary' => true],
                ],
            ],
        ];

        foreach ($clients as $cl) {
            $client = Client::create([
                'company_id' => $this->companyId,
                'client_code' => $cl['code'],
                'name' => $cl['name'],
                'client_type' => $cl['type'],
                'industry' => $cl['industry'],
                'tax_id' => $cl['tax_id'],
                'address' => 'Jl. Merdeka No. ' . rand(1, 200),
                'city' => $cl['city'],
                'province' => $cl['province'],
                'postal_code' => rand(10000, 99999),
                'phone' => '021-' . rand(1000, 9999) . rand(1000, 9999),
                'email' => 'info@' . strtolower(str_replace(['PT ', 'CV ', 'UD ', '.', ' '], '', $cl['name'])) . '.com',
                'status' => $cl['status'],
            ]);

            foreach ($cl['contacts'] as $ct) {
                ClientContact::create([
                    'client_id' => $client->id,
                    'first_name' => $ct['first'],
                    'last_name' => $ct['last'],
                    'position' => $ct['position'],
                    'email' => $ct['email'],
                    'phone' => $ct['phone'],
                    'is_primary' => $ct['primary'],
                ]);
            }
        }

        $this->clientIds = Client::pluck('id')->toArray();
        $this->command->info('       Clients: 5 created with contacts.');
    }

    // ============================================================
    // PRODUCT CATEGORIES
    // ============================================================
    private function seedProductCategories(): void
    {
        ProductCategory::truncate();
        $cats = ['Makanan & Minuman', 'Alat Tulis Kantor', 'Elektronik', 'Perlengkapan Kantor', 'Bahan Baku'];
        $this->productCategoryIds = [];
        foreach ($cats as $i => $c) {
            $pc = ProductCategory::create([
                'company_id' => $this->companyId,
                'parent_id' => null,
                'name' => $c,
                'description' => 'Kategori ' . $c,
                'is_active' => true,
            ]);
            $this->productCategoryIds[] = $pc->id;
        }
        $this->command->info('       Product Categories: 5 created.');
    }

    // ============================================================
    // PRODUCTS
    // ============================================================
    private function seedProducts(): void
    {
        Product::truncate();

        $products = [
            ['cat' => 0, 'code' => 'PRD-001', 'name' => 'Air Mineral 600ml', 'unit' => 'botol', 'buy' => 2500, 'sell' => 4000, 'stock' => 200],
            ['cat' => 0, 'code' => 'PRD-002', 'name' => 'Kopi Sachet', 'unit' => 'pcs', 'buy' => 1500, 'sell' => 3000, 'stock' => 500],
            ['cat' => 1, 'code' => 'PRD-003', 'name' => 'Kertas A4 70gsm', 'unit' => 'rim', 'buy' => 45000, 'sell' => 55000, 'stock' => 100],
            ['cat' => 1, 'code' => 'PRD-004', 'name' => 'Pulpen Pilot', 'unit' => 'pcs', 'buy' => 5000, 'sell' => 8000, 'stock' => 300],
            ['cat' => 1, 'code' => 'PRD-005', 'name' => 'Spidol Whiteboard', 'unit' => 'pcs', 'buy' => 7000, 'sell' => 12000, 'stock' => 80],
            ['cat' => 2, 'code' => 'PRD-006', 'name' => 'Mouse Wireless', 'unit' => 'pcs', 'buy' => 85000, 'sell' => 120000, 'stock' => 25],
            ['cat' => 2, 'code' => 'PRD-007', 'name' => 'Flashdisk 32GB', 'unit' => 'pcs', 'buy' => 55000, 'sell' => 80000, 'stock' => 40],
            ['cat' => 3, 'code' => 'PRD-008', 'name' => 'Map Folder', 'unit' => 'pcs', 'buy' => 3000, 'sell' => 5000, 'stock' => 150],
            ['cat' => 3, 'code' => 'PRD-009', 'name' => 'Sticky Notes', 'unit' => 'pack', 'buy' => 8000, 'sell' => 15000, 'stock' => 60],
            ['cat' => 4, 'code' => 'PRD-010', 'name' => 'Karton Box', 'unit' => 'pcs', 'buy' => 5000, 'sell' => 8000, 'stock' => 200],
        ];

        $this->productIds = [];
        foreach ($products as $p) {
            $prod = Product::create([
                'company_id' => $this->companyId,
                'category_id' => $this->productCategoryIds[$p['cat']],
                'code' => $p['code'],
                'name' => $p['name'],
                'description' => $p['name'],
                'unit' => $p['unit'],
                'purchase_price' => $p['buy'],
                'selling_price' => $p['sell'],
                'stock' => $p['stock'],
                'min_stock' => 10,
                'max_stock' => 500,
                'is_taxable' => true,
                'tax_rate' => 11,
                'is_active' => true,
            ]);
            $this->productIds[] = $prod->id;
        }
        $this->command->info('       Products: 10 created.');
    }

    // ============================================================
    // POS MEMBERS
    // ============================================================
    private function seedPosMembers(): void
    {
        PosMember::truncate();

        $members = [
            ['code' => 'M-001', 'name' => 'Rina Yulianti', 'phone' => '081111111111', 'email' => 'rina@email.com', 'points' => 250],
            ['code' => 'M-002', 'name' => 'Budi Setiawan', 'phone' => '082222222222', 'email' => 'budi.s@email.com', 'points' => 150],
            ['code' => 'M-003', 'name' => 'Anisa Rahma', 'phone' => '083333333333', 'email' => 'anisa.r@email.com', 'points' => 400],
            ['code' => 'M-004', 'name' => 'Dedi Irawan', 'phone' => '084444444444', 'email' => 'dedi@email.com', 'points' => 80],
            ['code' => 'M-005', 'name' => 'Linda Kusuma', 'phone' => '085555555555', 'email' => 'linda@email.com', 'points' => 320],
        ];

        $this->memberIds = [];
        foreach ($members as $m) {
            $mem = PosMember::create([
                'company_id' => $this->companyId,
                'member_code' => $m['code'],
                'name' => $m['name'],
                'phone' => $m['phone'],
                'email' => $m['email'],
                'points' => $m['points'],
                'total_spent' => $m['points'] * 1000, // rough estimate
                'join_date' => '2025-' . rand(1, 12) . '-' . rand(1, 28),
                'is_active' => true,
            ]);
            $this->memberIds[] = $mem->id;
        }
        $this->command->info('       POS Members: 5 created.');
    }

    // ============================================================
    // POS TRANSACTIONS
    // ============================================================
    private function seedPosTransactions(): void
    {
        PosTransaction::truncate();
        PosTransactionItem::truncate();
        PosPayment::truncate();
        CashierShift::truncate();

        $cashierId = $this->employees['Fitriani'] ?? collect($this->employees)->first();
        $paymentMethods = PaymentMethod::pluck('id')->toArray();

        // Create a cashier shift for today
        $shift = CashierShift::create([
            'employee_id' => $cashierId,
            'branch_id' => $this->branches['pusat'],
            'shift_date' => Carbon::now()->format('Y-m-d'),
            'opening_time' => Carbon::now()->setTime(7, 0),
            'opening_balance' => 500000,
            'closing_time' => Carbon::now()->setTime(16, 0),
            'closing_balance' => 500000,
            'expected_cash' => 0,
            'actual_cash' => 0,
            'difference' => 0,
            'total_transactions' => 0,
            'total_sales' => 0,
            'status' => 'open',
        ]);

        $totalSales = 0;
        $txCount = 0;

        for ($i = 1; $i <= 20; $i++) {
            $transactionDate = Carbon::now()->subDays(rand(0, 28))->setTime(rand(7, 20), rand(0, 59), rand(0, 59));

            $items = [];
            $itemCount = rand(1, 4);
            $subtotal = 0;

            $selectedProducts = array_rand(array_flip($this->productIds), $itemCount);
            if (!is_array($selectedProducts)) {
                $selectedProducts = [$selectedProducts];
            }

            foreach ($selectedProducts as $productId) {
                $product = Product::find($productId);
                $qty = rand(1, 5);
                $price = $product->selling_price;
                $discount = rand(0, 1) ? 0 : round($price * $qty * (rand(5, 15) / 100));
                $tax = round(($price * $qty - $discount) * 0.11);
                $lineSubtotal = round($price * $qty - $discount);
                $subtotal += $lineSubtotal;

                $items[] = [
                    'product_id' => $productId,
                    'variant_id' => null,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'discount_amount' => $discount,
                    'tax_amount' => $tax,
                    'subtotal' => $lineSubtotal,
                ];
            }

            $discountTotal = array_sum(array_column($items, 'discount_amount'));
            $taxTotal = array_sum(array_column($items, 'tax_amount'));
            $grandTotal = $subtotal + $taxTotal;

            $memberId = rand(0, 1) ? ($this->memberIds[array_rand($this->memberIds)] ?? null) : null;

            $tx = PosTransaction::create([
                'company_id' => $this->companyId,
                'shift_id' => $shift->id,
                'receipt_number' => 'INV-' . date('Ym', $transactionDate->timestamp) . '-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'member_id' => $memberId,
                'cashier_id' => $cashierId,
                'transaction_date' => $transactionDate,
                'subtotal' => $subtotal,
                'discount_total' => $discountTotal,
                'tax_total' => $taxTotal,
                'grand_total' => $grandTotal,
                'payment_status' => 'paid',
                'notes' => null,
            ]);

            foreach ($items as $item) {
                PosTransactionItem::create(array_merge($item, [
                    'transaction_id' => $tx->id,
                ]));
            }

            PosPayment::create([
                'transaction_id' => $tx->id,
                'payment_method' => collect(['Tunai', 'Transfer', 'QRIS'])->random(),
                'amount' => $grandTotal,
                'reference_number' => 'PAY-' . strtoupper(substr(md5($i . time()), 0, 10)),
                'paid_at' => $transactionDate,
            ]);

            $totalSales += $grandTotal;
            $txCount++;
        }

        // Update cashier shift totals
        $shift->update([
            'closing_balance' => 500000 + $totalSales,
            'expected_cash' => 500000 + $totalSales,
            'actual_cash' => 500000 + $totalSales,
            'total_transactions' => $txCount,
            'total_sales' => $totalSales,
        ]);

        $this->command->info('       POS Transactions: 20 created with items and payments.');
    }

    // ============================================================
    // PROJECTS
    // ============================================================
    private function seedProjects(): void
    {
        Project::truncate();
        ProjectPhase::truncate();
        Task::truncate();
        ProjectMember::truncate();
        Milestone::truncate();

        $itManagerId = $this->employees['Donni'] ?? null;
        $salesManagerId = $this->employees['Andi'] ?? null;
        $staffItId = $this->employees['Eka'] ?? null;

        $projects = [
            [
                'code' => 'PRJ-001', 'name' => 'Implementasi ERP Modul Finance',
                'description' => 'Implementasi modul keuangan ERP untuk klien PT Sumber Makmur',
                'client_idx' => 0, 'manager' => 'Donni', 'dept' => 'IT',
                'start' => '2026-04-01', 'end' => '2026-07-30',
                'budget' => 150000000, 'actual_cost' => 45000000,
                'status' => 'active', 'priority' => 'high', 'progress' => 45,
                'color' => '#4f46e5',
                'phases' => [
                    ['name' => 'Analisis Kebutuhan', 'status' => 'completed', 'start' => '2026-04-01', 'end' => '2026-04-15', 'sort' => 1],
                    ['name' => 'Desain Sistem', 'status' => 'completed', 'start' => '2026-04-16', 'end' => '2026-05-10', 'sort' => 2],
                    ['name' => 'Pengembangan', 'status' => 'active', 'start' => '2026-05-11', 'end' => '2026-06-30', 'sort' => 3],
                    ['name' => 'Testing & UAT', 'status' => 'pending', 'start' => '2026-07-01', 'end' => '2026-07-15', 'sort' => 4],
                    ['name' => 'Go-Live', 'status' => 'pending', 'start' => '2026-07-16', 'end' => '2026-07-30', 'sort' => 5],
                ],
            ],
            [
                'code' => 'PRJ-002', 'name' => 'Kampanye Digital Marketing Q2',
                'description' => 'Kampanye pemasaran digital untuk meningkatkan brand awareness Q2 2026',
                'client_idx' => 1, 'manager' => 'Andi', 'dept' => 'SM',
                'start' => '2026-04-15', 'end' => '2026-06-30',
                'budget' => 50000000, 'actual_cost' => 20000000,
                'status' => 'active', 'priority' => 'medium', 'progress' => 60,
                'color' => '#f59e0b',
                'phases' => [
                    ['name' => 'Perencanaan Konten', 'status' => 'completed', 'start' => '2026-04-15', 'end' => '2026-04-30', 'sort' => 1],
                    ['name' => 'Produksi Konten', 'status' => 'completed', 'start' => '2026-05-01', 'end' => '2026-05-20', 'sort' => 2],
                    ['name' => 'Pelaksanaan Kampanye', 'status' => 'active', 'start' => '2026-05-21', 'end' => '2026-06-15', 'sort' => 3],
                    ['name' => 'Evaluasi & Laporan', 'status' => 'pending', 'start' => '2026-06-16', 'end' => '2026-06-30', 'sort' => 4],
                ],
            ],
            [
                'code' => 'PRJ-003', 'name' => 'Pengembangan Aplikasi Mobile POS',
                'description' => 'Pengembangan aplikasi POS mobile untuk tim sales lapangan',
                'client_idx' => 2, 'manager' => 'Donni', 'dept' => 'IT',
                'start' => '2026-05-01', 'end' => '2026-08-30',
                'budget' => 200000000, 'actual_cost' => 15000000,
                'status' => 'active', 'priority' => 'high', 'progress' => 15,
                'color' => '#10b981',
                'phases' => [
                    ['name' => 'Research & Discovery', 'status' => 'completed', 'start' => '2026-05-01', 'end' => '2026-05-15', 'sort' => 1],
                    ['name' => 'UI/UX Design', 'status' => 'active', 'start' => '2026-05-16', 'end' => '2026-06-15', 'sort' => 2],
                    ['name' => 'Development Sprint 1', 'status' => 'pending', 'start' => '2026-06-16', 'end' => '2026-07-15', 'sort' => 3],
                    ['name' => 'Development Sprint 2', 'status' => 'pending', 'start' => '2026-07-16', 'end' => '2026-08-15', 'sort' => 4],
                    ['name' => 'Testing & Release', 'status' => 'pending', 'start' => '2026-08-16', 'end' => '2026-08-30', 'sort' => 5],
                ],
            ],
        ];

        foreach ($projects as $pj) {
            $managerId = $this->employees[$pj['manager']] ?? null;
            $clientId = $this->clientIds[$pj['client_idx']] ?? null;

            $project = Project::create([
                'company_id' => $this->companyId,
                'department_id' => $this->departments[$pj['dept']],
                'client_id' => $clientId,
                'manager_id' => $managerId,
                'code' => $pj['code'],
                'name' => $pj['name'],
                'description' => $pj['description'],
                'start_date' => $pj['start'],
                'end_date' => $pj['end'],
                'budget' => $pj['budget'],
                'actual_cost' => $pj['actual_cost'],
                'status' => $pj['status'],
                'priority' => $pj['priority'],
                'progress_percent' => $pj['progress'],
                'color' => $pj['color'],
            ]);

            // Add project manager as member
            if ($managerId) {
                ProjectMember::create([
                    'project_id' => $project->id,
                    'employee_id' => $managerId,
                    'role' => 'manager',
                    'joined_at' => $pj['start'],
                ]);
            }

            // Add staff IT as member for IT projects
            if ($pj['dept'] === 'IT' && $staffItId) {
                ProjectMember::create([
                    'project_id' => $project->id,
                    'employee_id' => $staffItId,
                    'role' => 'member',
                    'joined_at' => $pj['start'],
                ]);
            }

            // Add Citra for marketing project
            if ($pj['dept'] === 'SM' && isset($this->employees['Citra'])) {
                ProjectMember::create([
                    'project_id' => $project->id,
                    'employee_id' => $this->employees['Citra'],
                    'role' => 'member',
                    'joined_at' => $pj['start'],
                ]);
            }

            // Phases
            foreach ($pj['phases'] as $phase) {
                $pp = ProjectPhase::create([
                    'project_id' => $project->id,
                    'name' => $phase['name'],
                    'description' => 'Fase: ' . $phase['name'],
                    'start_date' => $phase['start'],
                    'end_date' => $phase['end'],
                    'sort_order' => $phase['sort'],
                    'status' => $phase['status'],
                ]);

                // Create 1-2 tasks per phase
                $taskNames = [
                    'Rapat koordinasi ' . $phase['name'],
                    'Dokumentasi ' . $phase['name'],
                ];
                foreach ($taskNames as $ti => $taskName) {
                    Task::create([
                        'project_id' => $project->id,
                        'phase_id' => $pp->id,
                        'parent_id' => null,
                        'milestone_id' => null,
                        'title' => $taskName,
                        'description' => 'Deskripsi task: ' . $taskName,
                        'status' => $phase['status'] === 'completed' ? 'done' : ($phase['status'] === 'active' && $ti === 0 ? 'in_progress' : 'todo'),
                        'priority' => collect(['low', 'medium', 'high'])->random(),
                        'type' => 'task',
                        'estimated_hours' => rand(4, 16),
                        'actual_hours' => $phase['status'] === 'completed' ? rand(2, 12) : 0,
                        'start_date' => $phase['start'],
                        'due_date' => $phase['end'],
                        'completed_at' => $phase['status'] === 'completed' ? $phase['end'] : null,
                        'sort_order' => $ti + 1,
                        'created_by' => $managerId ?? 1,
                    ]);
                }
            }

            // Milestones
            $milestoneNames = ['Kickoff', 'Review 50%', 'Final Review'];
            foreach ($milestoneNames as $mi => $ms) {
                $msDate = Carbon::parse($pj['start'])->addDays(($mi + 1) * (Carbon::parse($pj['start'])->diffInDays(Carbon::parse($pj['end'])) / 4));
                Milestone::create([
                    'project_id' => $project->id,
                    'name' => $ms,
                    'target_date' => $msDate->format('Y-m-d'),
                    'completed_date' => $mi === 0 && $pj['progress'] > 10 ? $msDate->subDays(rand(1, 3))->format('Y-m-d') : null,
                    'status' => $mi === 0 && $pj['progress'] > 10 ? 'completed' : 'pending',
                    'sort_order' => $mi + 1,
                ]);
            }
        }

        $this->command->info('       Projects: 3 created with phases, tasks, milestones, and members.');
    }

    // ============================================================
    // SYSTEM SETTINGS
    // ============================================================
    private function seedSystemSettings(): void
    {
        SystemSetting::where('company_id', $this->companyId)->delete();

        $settings = [
            ['key' => 'company_name', 'value' => 'PT Maju Bersama', 'type' => 'string', 'group' => 'general'],
            ['key' => 'company_address', 'value' => 'Jl. Jenderal Sudirman No. 123, Jakarta Selatan', 'type' => 'string', 'group' => 'general'],
            ['key' => 'company_phone', 'value' => '021-5555-6789', 'type' => 'string', 'group' => 'general'],
            ['key' => 'company_email', 'value' => 'info@majubersama.co.id', 'type' => 'string', 'group' => 'general'],
            ['key' => 'attendance_gps_radius', 'value' => '100', 'type' => 'integer', 'group' => 'attendance'],
            ['key' => 'attendance_wifi_required', 'value' => 'false', 'type' => 'boolean', 'group' => 'attendance'],
            ['key' => 'attendance_photo_required', 'value' => 'true', 'type' => 'boolean', 'group' => 'attendance'],
            ['key' => 'attendance_grace_period', 'value' => '15', 'type' => 'integer', 'group' => 'attendance'],
            ['key' => 'work_start_time', 'value' => '07:00', 'type' => 'string', 'group' => 'attendance'],
            ['key' => 'work_end_time', 'value' => '16:00', 'type' => 'string', 'group' => 'attendance'],
            ['key' => 'payroll_cutoff_date', 'value' => '25', 'type' => 'integer', 'group' => 'payroll'],
            ['key' => 'pph21_threshold', 'value' => '54000000', 'type' => 'integer', 'group' => 'payroll'],
            ['key' => 'bpjs_tk_rate_company', 'value' => '3.7', 'type' => 'string', 'group' => 'payroll'],
            ['key' => 'bpjs_kes_rate_company', 'value' => '4', 'type' => 'string', 'group' => 'payroll'],
            ['key' => 'invoice_prefix', 'value' => 'INV-MAJU', 'type' => 'string', 'group' => 'invoice'],
            ['key' => 'invoice_tax_rate', 'value' => '11', 'type' => 'string', 'group' => 'invoice'],
            ['key' => 'default_language', 'value' => 'id', 'type' => 'string', 'group' => 'general'],
            ['key' => 'default_timezone', 'value' => 'Asia/Jakarta', 'type' => 'string', 'group' => 'general'],
            ['key' => 'notification_email', 'value' => 'true', 'type' => 'boolean', 'group' => 'notification'],
            ['key' => 'notification_wa', 'value' => 'false', 'type' => 'boolean', 'group' => 'notification'],
        ];

        foreach ($settings as $s) {
            SystemSetting::create(array_merge($s, [
                'company_id' => $this->companyId,
                'description' => 'Setting: ' . $s['key'],
            ]));
        }

        $this->command->info('       System Settings: ' . count($settings) . ' created.');
    }
}
