<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Attendance;
use App\Models\Budget;
use App\Models\BudgetItem;
use App\Models\Candidate;
use App\Models\CanteenMenu;
use App\Models\CanteenOrder;
use App\Models\CanteenOrderItem;
use App\Models\Client;
use App\Models\ClientContact;
use App\Models\Coa;
use App\Models\CoaCategory;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\CourseLesson;
use App\Models\CourseModule;
use App\Models\Deal;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\FeedbackAnswer;
use App\Models\FeedbackCycle;
use App\Models\FeedbackQuestion;
use App\Models\FeedbackReviewer;
use App\Models\Grade;
use App\Models\Interview;
use App\Models\InterviewResult;
use App\Models\Interviewer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoicePayment;
use App\Models\JobPosting;
use App\Models\Journal;
use App\Models\JournalEntry;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\Meeting;
use App\Models\MeetingAttendee;
use App\Models\Notification;
use App\Models\Overtime;
use App\Models\PaySlip;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Payroll;
use App\Models\PayrollItem;
use App\Models\PayrollPeriod;
use App\Models\PipelineStage;
use App\Models\PosMember;
use App\Models\PosPayment;
use App\Models\PosTransaction;
use App\Models\PosTransactionItem;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\ProjectPhase;
use App\Models\Reimbursement;
use App\Models\ReimbursementCategory;
use App\Models\Role;
use App\Models\SalaryComponent;
use App\Models\Shift;
use App\Models\ShiftEmployee;
use App\Models\Task;
use App\Models\User;
use App\Models\Visit;
use App\Models\WaBlastCampaign;
use App\Models\WaBlastLog;
use App\Models\WaTemplate;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Faker\Factory as Faker;

class DemoDataSeeder extends Seeder
{
    private $companyId;
    private $branchIds = [];
    private $departmentIds = [];
    private $positionIds = [];
    private $employeeIds = [];
    private $employeeCodeCounter;
    private $designationIds = [];
    private $gradeIds = [];
    private $roleIds = [];
    private $shiftIds = [];
    private $leaveTypeIds = [];
    private $salaryComponentIds = [];
    private $coaCategoryIds = [];
    private $coaIds = [];
    private $coaNonHeaderIds = [];
    private $paymentMethodIds = [];
    private $productCategoryIds = [];
    private $productIds = [];
    private $clientIds = [];
    private $pipelineStageIds = [];
    private $leadSourceIds = [];
    private $reimbursementCategoryIds = [];
    private $faker;

    private $firstNames = [
        'Aditya','Agung','Agus','Aldo','Angga','Arif','Arman','Bagas','Bayu','Bima',
        'Candra','Dani','Denny','Dika','Dimas','Eko','Fahmi','Fajar','Fikri','Gilang',
        'Hadi','Hendra','Iqbal','Irfan','Joko','Kevin','Lukman','Nanda','Nico','Putra',
        'Rama','Reza','Rio','Rizky','Sandi','Satria','Teguh','Tio','Udin','Yoga',
        'Yusuf','Zaki','Anita','Ayu','Bella','Cindy','Dina','Elvira','Fitri','Gita',
        'Indah','Intan','Kartika','Laras','Mega','Mira','Nadia','Niken','Olivia','Putri',
        'Rani','Rina','Rosa','Sari','Silvia','Tania','Vera','Vina','Winda','Yuni',
    ];

    private $lastNames = [
        'Santoso','Wijaya','Kusuma','Pratama','Hidayat','Saputra','Hermawan','Nugroho',
        'Setiawan','Gunawan','Kurniawan','Susanto','Mahendra','Wibowo','Hartono',
        'Purnomo','Utama','Yulianto','Hartanto','Halim','Lesmana','Purwanto','Budiman',
        'Irawan','Kusnadi','Suryadi','Hendrawan','Firmansyah','Ramadhan','Iskandar',
        'Anggraini','Wulandari','Puspita','Agustina','Oktaviani','Handayani','Kusumawati',
        'Rahayu','Melati','Permata',
    ];

    private $cities = [
        'Jakarta Selatan','Jakarta Pusat','Jakarta Timur','Jakarta Barat','Bandung',
        'Surabaya','Semarang','Tangerang','Medan','Pekanbaru','Balikpapan','Makassar','Denpasar',
    ];

    public function run(): void
    {
        $this->faker = Faker::create('id_ID');
        $this->command->info('=== BizOS Demo Data Seeder (~30,000 records) ===');
        Schema::disableForeignKeyConstraints();
        $this->loadExisting();
        $this->seedReimbursementCategories();
        $this->seedLeadSources();
        $this->seedPipelineStages();
        $this->seedAssetCategories();
        $this->seedEmployees(95);
        $this->seedUsers();
        $this->seedShiftEmployees();
        $this->seedAttendance();
        $this->seedLeaves(200);
        $this->seedOvertimes(150);
        $this->seedReimbursements(200);
        $this->seedVisits(100);
        $this->seedJobPostings(50);
        $this->seedCandidates(200);
        $this->seedInterviews(100);
        $this->seedFeedbackCycles();
        $this->seedCanteenMenus();
        $this->seedCanteenOrders(500);
        $this->seedAnnouncements(30);
        $this->seedPayrolls();
        $this->seedMoreCoa();
        $this->seedJournals();
        $this->seedInvoices(300, 200);
        $this->seedPayments(400);
        $this->seedBudgets();
        $this->seedAssets(50);
        $this->seedMoreClients(50);
        $this->seedLeads(200);
        $this->seedDeals(100);
        $this->seedWaTemplates(20);
        $this->seedWaCampaigns(10);
        $this->seedWaBlastLogs(500);
        $this->seedProjects(15);
        $this->seedMoreProducts(50);
        $this->seedMorePosTransactions(1000);
        $this->seedMeetings(100);
        $this->seedCourses();
        $this->seedNotifications(500);
        Schema::enableForeignKeyConstraints();
        $this->printSummary();
    }

    private function loadExisting(): void
    {
        $company = \App\Models\Company::first();
        $this->companyId = $company ? $company->id : 1;
        $this->branchIds = DB::table('branches')->pluck('id')->toArray();
        $this->departmentIds = \App\Models\Department::pluck('id')->toArray();
        $this->positionIds = \App\Models\Position::pluck('id')->toArray();
        $this->designationIds = Designation::pluck('id')->toArray();
        $this->gradeIds = Grade::pluck('id')->toArray();
        $this->roleIds = Role::pluck('id', 'slug')->toArray();
        $this->shiftIds = Shift::pluck('id')->toArray();
        $this->paymentMethodIds = PaymentMethod::pluck('id')->toArray();
        $this->productCategoryIds = ProductCategory::pluck('id')->toArray();
        $this->clientIds = Client::pluck('id')->toArray();
        $existingEmployees = DB::table('employees')->pluck('id')->toArray();
        $this->employeeIds = $existingEmployees;
        $maxCode = DB::table('employees')->where('employee_code','like','EMP-%')->orderByRaw('CAST(SUBSTRING(employee_code,5) AS UNSIGNED) DESC')->value('employee_code');
        $this->employeeCodeCounter = $maxCode ? (int)substr($maxCode,4) + 1 : 16;
        $this->salaryComponentIds = SalaryComponent::pluck('id','code')->toArray();
        $this->coaCategoryIds = CoaCategory::pluck('id','code')->toArray();
        $this->coaNonHeaderIds = Coa::where('is_header',false)->pluck('id')->toArray();
        $this->productIds = Product::pluck('id')->toArray();
    }

    private function seedReimbursementCategories(): void
    {
        if (ReimbursementCategory::count() > 0) {
            $this->reimbursementCategoryIds = ReimbursementCategory::pluck('id')->toArray();
            return;
        }
        $cats = [
            ['name'=>'Transportasi','max_amount'=>500000,'require_receipt'=>true],
            ['name'=>'Makan & Minum','max_amount'=>300000,'require_receipt'=>true],
            ['name'=>'Kesehatan','max_amount'=>2000000,'require_receipt'=>true],
            ['name'=>'Akomodasi','max_amount'=>1500000,'require_receipt'=>true],
            ['name'=>'Internet & Pulsa','max_amount'=>500000,'require_receipt'=>true],
            ['name'=>'Bahan Bakar','max_amount'=>400000,'require_receipt'=>true],
        ];
        foreach ($cats as $c) {
            $rc = ReimbursementCategory::create(['company_id'=>$this->companyId,'name'=>$c['name'],'description'=>'Kategori '.$c['name'],'max_amount'=>$c['max_amount'],'require_receipt'=>$c['require_receipt'],'is_active'=>true]);
            $this->reimbursementCategoryIds[] = $rc->id;
        }
    }

    private function seedLeadSources(): void
    {
        if (LeadSource::count() > 0) { $this->leadSourceIds = LeadSource::pluck('id')->toArray(); return; }
        $sources = ['Website','Instagram','Facebook','LinkedIn','Referral','Event','Cold Call','Email Marketing','Google Ads','WhatsApp'];
        foreach ($sources as $s) { $ls = LeadSource::create(['company_id'=>$this->companyId,'name'=>$s,'is_active'=>true]); $this->leadSourceIds[] = $ls->id; }
    }

    private function seedPipelineStages(): void
    {
        if (PipelineStage::count() > 0) { $this->pipelineStageIds = PipelineStage::pluck('id')->toArray(); return; }
        $stages = [
            ['name'=>'Prospek','probability_percent'=>10,'color'=>'#94a3b8','sort_order'=>1],
            ['name'=>'Kualifikasi','probability_percent'=>25,'color'=>'#3b82f6','sort_order'=>2],
            ['name'=>'Presentasi','probability_percent'=>45,'color'=>'#f59e0b','sort_order'=>3],
            ['name'=>'Negosiasi','probability_percent'=>70,'color'=>'#8b5cf6','sort_order'=>4],
            ['name'=>'Menang','probability_percent'=>100,'color'=>'#10b981','sort_order'=>5],
            ['name'=>'Kalah','probability_percent'=>0,'color'=>'#ef4444','sort_order'=>6],
        ];
        foreach ($stages as $s) { $ps = PipelineStage::create(array_merge($s,['company_id'=>$this->companyId,'is_active'=>true])); $this->pipelineStageIds[$s['name']] = $ps->id; }
    }

    private function seedAssetCategories(): void
    {
        if (AssetCategory::count() > 0) return;
        $cats = [
            ['code'=>'OFFICE','name'=>'Peralatan Kantor','depreciation_method'=>'straight_line','useful_life_years'=>5,'salvage_value_percent'=>10],
            ['code'=>'VEHICLE','name'=>'Kendaraan','depreciation_method'=>'straight_line','useful_life_years'=>8,'salvage_value_percent'=>15],
            ['code'=>'IT','name'=>'Komputer & Elektronik','depreciation_method'=>'straight_line','useful_life_years'=>4,'salvage_value_percent'=>5],
            ['code'=>'FURN','name'=>'Perabotan','depreciation_method'=>'straight_line','useful_life_years'=>10,'salvage_value_percent'=>5],
        ];
        foreach ($cats as $c) { AssetCategory::create(array_merge($c,['company_id'=>$this->companyId])); }
    }


    private function seedEmployees(int $count): void
    {
        $this->command->info("[Employees] Adding {$count} employees...");
        $existingCount = DB::table('employees')->count();
        if ($existingCount >= 110) { $this->command->info("  Already 110+ employees."); return; }
        $remaining = 110 - $existingCount;
        if ($remaining <= 0) { $this->command->info("  No new employees needed."); return; }
        $this->command->info("  Need {$remaining} more employees for 110 total.");
        $branches = $this->branchIds;
        $departments = $this->departmentIds;
        $positions = $this->positionIds;
        $designations = $this->designationIds;
        $grades = $this->gradeIds;
        $existingEmails = DB::table('employees')->pluck('email')->map(fn($e) => strtolower($e))->toArray();
        $existingCodes = DB::table('employees')->pluck('employee_code')->toArray();
        $emps = [];
        $batchSize = 50;
        $created = 0;
        for ($i = 0; $i < $remaining; $i++) {
            do { $fn = $this->faker->randomElement($this->firstNames); } while (in_array(strtolower($fn), $existingEmails));
            $ln = $this->faker->randomElement($this->lastNames);
            $gender = $this->faker->randomElement(['male','female']);
            $branchId = $this->faker->randomElement($branches);
            $deptId = $this->faker->randomElement($departments);
            $posId = $this->faker->randomElement($positions);
            $desId = $this->faker->randomElement($designations);
            $gradeId = $this->faker->randomElement($grades);
            $empType = $this->faker->randomElement(['permanent','contract','probation','intern']);
            $salary = round($this->faker->numberBetween(3000000,20000000), -4);
            do { $code = sprintf('EMP-%03d', $this->employeeCodeCounter++); } while (in_array($code, $existingCodes));
            $existingCodes[] = $code;
            do { $email = strtolower($fn).$this->faker->numberBetween(100,999).'@maju.test'; } while (in_array($email, $existingEmails));
            $existingEmails[] = $email;
            $branch = DB::table('branches')->find($branchId);
            $city = $branch ? ($branch->code === 'BDG' ? 'Bandung' : ($branch->code === 'SBY' ? 'Surabaya' : 'Jakarta Selatan')) : 'Jakarta Selatan';
            $province = $branch ? ($branch->code === 'BDG' ? 'Jawa Barat' : ($branch->code === 'SBY' ? 'Jawa Timur' : 'DKI Jakarta')) : 'DKI Jakarta';
            $joinDate = $this->faker->dateTimeBetween('-3 years','-1 month')->format('Y-m-d');
            $today = now();
            $emps[] = [
                'company_id'=>$this->companyId,'branch_id'=>$branchId,'department_id'=>$deptId,'position_id'=>$posId,
                'designation_id'=>$desId,'grade_id'=>$gradeId,'employee_code'=>$code,'first_name'=>$fn,'last_name'=>$ln,
                'email'=>$email,'phone'=>'08'.$this->faker->numerify('##########'),'gender'=>$gender,
                'birth_date'=>sprintf('%04d-%02d-%02d',$this->faker->numberBetween(1988,2002),$this->faker->numberBetween(1,12),$this->faker->numberBetween(1,28)),
                'birth_place'=>$this->faker->randomElement(['Jakarta','Bandung','Surabaya','Medan','Semarang']),
                'religion'=>$this->faker->randomElement(['Islam','Kristen','Katolik','Hindu','Buddha']),
                'marital_status'=>$this->faker->randomElement(['single','married']),'nationality'=>'Indonesia',
                'id_number'=>'31'.$this->faker->numerify('##############'),'tax_number'=>$this->faker->numerify('##.###.###.#-###.###'),
                'bpjs_kesehatan'=>$this->faker->numerify('0000###########'),'bpjs_ketenagakerjaan'=>$this->faker->numerify('0000###########'),
                'address'=>'Jl. ' . $this->faker->streetName . ' No. '.$this->faker->numberBetween(1,300),
                'city'=>$city,'province'=>$province,'postal_code'=>$this->faker->numerify('#####'),
                'join_date'=>$joinDate,'contract_start'=>$joinDate,
                'contract_end'=>$empType==='permanent' ? null : Carbon::parse($joinDate)->addYear()->format('Y-m-d'),
                'employee_type'=>$empType,'status'=>'active','basic_salary'=>$salary,
                'bank_name'=>$this->faker->randomElement(['BCA','Mandiri','BNI','BRI','CIMB Niaga','BTN']),
                'bank_account_number'=>$this->faker->numerify('##########'),
                'bank_account_name'=>$fn.' '.$ln,'created_at'=>$today,'updated_at'=>$today,
            ];
            $created++;
            if (count($emps) >= $batchSize) { DB::table('employees')->insert($emps); $emps = []; }
        }
        if (!empty($emps)) { DB::table('employees')->insert($emps); }
        $this->employeeIds = DB::table('employees')->pluck('id')->toArray();
        $this->command->info("  Total employees now: " . count($this->employeeIds));
    }


    private function seedUsers(): void
    {
        $this->command->info("[Users] Creating user accounts...");
        $existingEmails = User::pluck('email')->map(fn($e) => strtolower($e))->toArray();
        $allEmployees = DB::table('employees')->get();
        $defaultRoleId = $this->roleIds['staff'] ?? reset($this->roleIds);
        $users = []; $batchSize = 50; $created = 0;
        foreach ($allEmployees as $emp) {
            $email = strtolower($emp->first_name).'@maju.test';
            if (in_array($email, $existingEmails)) continue;
            $roleSlug = 'staff';
            $roleId = $this->roleIds[$roleSlug] ?? $defaultRoleId;
            $users[] = [
                'name'=>$emp->first_name.' '.$emp->last_name,'email'=>$email,
                'password'=>Hash::make('password'),'email_verified_at'=>now(),
                'is_active'=>true,'employee_id'=>$emp->id,'company_id'=>$this->companyId,
                'role_id'=>$roleId,'created_at'=>now(),'updated_at'=>now(),
            ];
            $existingEmails[] = $email; $created++;
            if (count($users) >= $batchSize) { DB::table('users')->insert($users); $users = []; }
        }
        if (!empty($users)) { DB::table('users')->insert($users); }
        $this->command->info("  Total users now: " . User::count());
    }

    private function seedShiftEmployees(): void
    {
        $this->command->info("[ShiftEmployees] Assigning employees to shifts...");
        $existingAssignments = DB::table('shift_employees')->pluck('employee_id')->toArray();
        $allEmployees = array_diff($this->employeeIds, $existingAssignments);
        if (empty($allEmployees)) { $this->command->info("  All already assigned."); return; }
        $shifts = $this->shiftIds; $assignments = []; $now = now();
        foreach ($allEmployees as $empId) {
            $assignments[] = ['shift_id'=>$this->faker->randomElement($shifts),'employee_id'=>$empId,'effective_date'=>'2026-01-01','end_date'=>null,'created_at'=>$now,'updated_at'=>$now];
        }
        DB::table('shift_employees')->insert($assignments);
        $this->command->info("  " . count($assignments) . " shift assignments created.");
    }

    private function seedAttendance(): void
    {
        $this->command->info("[Attendance] Generating last 90 days...");
        $existingDates = DB::table('attendances')->select('employee_id','date')->get()->mapWithKeys(fn($r) => [$r->employee_id.'_'.$r->date=>true])->toArray();
        $startDate = Carbon::now()->subDays(90); $endDate = Carbon::now()->subDay();
        $allEmployees = $this->employeeIds;
        $shiftAssignments = DB::table('shift_employees')->select('employee_id','shift_id')->get()->keyBy('employee_id');
        $locs = [[-6.2088,106.8456],[-6.9147,107.6098],[-7.2575,112.7521]];
        $attendances = []; $batchSize = 500; $totalCreated = 0; $now = now();
        foreach ($allEmployees as $empId) {
            $shiftId = $shiftAssignments->has($empId) ? $shiftAssignments->get($empId)->shift_id : ($this->shiftIds[0] ?? 1);
            $emp = DB::table('employees')->find($empId);
            $branchId = $emp ? $emp->branch_id : ($this->branchIds[0] ?? 1);
            $locIdx = $branchId % 3;
            $lat = $locs[$locIdx][0] + (mt_rand(-50,50) / 10000);
            $lng = $locs[$locIdx][1] + (mt_rand(-50,50) / 10000);
            for ($d = clone $startDate; $d->lte($endDate); $d->addDay()) {
                $date = $d->format('Y-m-d');
                $key = $empId.'_'.$date;
                if (isset($existingDates[$key])) continue;
                if ($d->isWeekend()) { if (mt_rand(1,100) <= 30) { $roll = mt_rand(1,100); $status = $roll <= 10 ? 'late' : 'present'; } else continue; }
                else { $roll = mt_rand(1,100); if ($roll<=5) $status='absent'; elseif ($roll<=10) $status='leave'; elseif ($roll<=20) $status='late'; else $status='present'; }
                $clockIn = null; $clockOut = null; $lateMin = 0; $overtimeMin = 0;
                if ($status === 'present') { $clockIn = Carbon::parse($date.' 06:'.mt_rand(45,59).':'.mt_rand(0,59)); $clockOut = Carbon::parse($date.' 16:'.mt_rand(0,30).':'.mt_rand(0,59)); $overtimeMin = mt_rand(0,120); }
                elseif ($status === 'late') { $lateMin = mt_rand(5,60); $clockIn = Carbon::parse($date.' 07:00:00')->addMinutes($lateMin); $clockOut = Carbon::parse($date.' 16:'.mt_rand(0,30).':'.mt_rand(0,59)); $overtimeMin = mt_rand(0,60); }
                $workType = mt_rand(1,10) <= 2 ? 'wfh' : 'office';
                $attendances[] = ['employee_id'=>$empId,'shift_id'=>$shiftId,'date'=>$date,'clock_in'=>$clockIn,'clock_out'=>$clockOut,'clock_in_lat'=>$clockIn?$lat:null,'clock_in_lng'=>$clockIn?$lng:null,'clock_out_lat'=>$clockOut?$lat:null,'clock_out_lng'=>$clockOut?$lng:null,'status'=>$status,'late_minutes'=>$lateMin,'early_departure_minutes'=>0,'overtime_minutes'=>$overtimeMin,'work_type'=>$workType,'created_at'=>$now,'updated_at'=>$now];
                $totalCreated++;
                if (count($attendances) >= $batchSize) { DB::table('attendances')->insert($attendances); $attendances = []; }
            }
        }
        if (!empty($attendances)) { DB::table('attendances')->insert($attendances); }
        $this->command->info("  {$totalCreated} attendance records created.");
        $this->generatedAttendanceCount = $totalCreated;
    }


    private function seedLeaves(int $count): void
    {
        $this->command->info("[Leaves] Creating {$count} leave records...");
        $existing = Leave::count();
        $remaining = $count - $existing;
        if ($remaining <= 0) { $this->command->info("  Already enough."); return; }
        $leaveTypeIds = LeaveType::pluck('id')->toArray();
        if (empty($leaveTypeIds)) {
            LeaveType::create(['company_id'=>$this->companyId,'code'=>'TAHUNAN','name'=>'Cuti Tahunan','default_days'=>12,'max_days'=>12,'is_annual'=>true,'is_paid'=>true,'is_active'=>true,'require_approval'=>true,'min_approval_level'=>1,'applicable_gender'=>'all','applicable_marital'=>'all','color'=>'#4f46e5']);
            LeaveType::create(['company_id'=>$this->companyId,'code'=>'SAKIT','name'=>'Cuti Sakit','default_days'=>14,'max_days'=>90,'is_annual'=>false,'is_paid'=>true,'is_active'=>true,'require_approval'=>true,'min_approval_level'=>1,'applicable_gender'=>'all','applicable_marital'=>'all','color'=>'#ef4444']);
            $leaveTypeIds = LeaveType::pluck('id')->toArray();
        }
        $emps = $this->employeeIds;
        $statuses = ['pending','approved','approved','approved','rejected'];
        $leaves = []; $batchSize = 100; $created = 0;
        for ($i = 0; $i < $remaining; $i++) {
            $startDate = Carbon::now()->subDays(mt_rand(1,180));
            $days = mt_rand(1,3); $endDate = (clone $startDate)->addDays($days - 1);
            $status = $statuses[array_rand($statuses)];
            $leaves[] = ['employee_id'=>$this->faker->randomElement($emps),'leave_type_id'=>$this->faker->randomElement($leaveTypeIds),'start_date'=>$startDate->format('Y-m-d'),'end_date'=>$endDate->format('Y-m-d'),'total_days'=>$days,'reason'=>$this->faker->randomElement(['Keperluan keluarga','Sakit','Acara keluarga','Istirahat','Urusan pribadi']),'status'=>$status,'rejection_reason'=>$status==='rejected'?'Jadwal bentrok':null,'created_at'=>$startDate,'updated_at'=>$startDate];
            $created++;
            if (count($leaves) >= $batchSize) { Leave::insert($leaves); $leaves = []; }
        }
        if (!empty($leaves)) Leave::insert($leaves);
        $this->command->info("  {$created} leave records created.");
    }

    private function seedOvertimes(int $count): void
    {
        $this->command->info("[Overtimes] Creating {$count} records...");
        $existing = Overtime::count();
        $remaining = $count - $existing;
        if ($remaining <= 0) { $this->command->info("  Already enough."); return; }
        $emps = $this->employeeIds; $overtimes = []; $created = 0;
        for ($i = 0; $i < $remaining; $i++) {
            $date = Carbon::now()->subDays(mt_rand(1,90));
            $duration = mt_rand(1,4)*60;
            $startTime = Carbon::parse($date->format('Y-m-d').' 17:00:00');
            $endTime = (clone $startTime)->addMinutes($duration);
            $overtimes[] = ['employee_id'=>$this->faker->randomElement($emps),'date'=>$date->format('Y-m-d'),'start_time'=>$startTime,'end_time'=>$endTime,'duration_minutes'=>$duration,'rate_multiplier'=>$this->faker->randomElement([1.5,2.0,3.0]),'reason'=>$this->faker->randomElement(['Deadline project','Support maintenance','Laporan akhir bulan']),'status'=>$this->faker->randomElement(['approved','approved','pending','rejected']),'approved_by'=>$this->faker->randomElement($emps),'approved_at'=>$date,'created_at'=>$date,'updated_at'=>$date];
            $created++;
        }
        Overtime::insert($overtimes);
        $this->command->info("  {$created} overtime records created.");
    }

    private function seedReimbursements(int $count): void
    {
        $this->command->info("[Reimbursements] Creating {$count} records...");
        $existing = Reimbursement::count();
        $remaining = $count - $existing;
        if ($remaining <= 0) { $this->command->info("  Already enough."); return; }
        $emps = $this->employeeIds;
        if (empty($this->reimbursementCategoryIds)) { $this->reimbursementCategoryIds = ReimbursementCategory::pluck('id')->toArray(); }
        $cats = $this->reimbursementCategoryIds ?: [1];
        $reimbursements = []; $created = 0;
        for ($i = 0; $i < $remaining; $i++) {
            $date = Carbon::now()->subDays(mt_rand(1,180));
            $amount = mt_rand(50000,1500000);
            $status = $this->faker->randomElement(['approved','approved','pending','paid','rejected']);
            $reimbursements[] = ['employee_id'=>$this->faker->randomElement($emps),'category_id'=>$this->faker->randomElement($cats),'date'=>$date->format('Y-m-d'),'amount'=>$amount,'description'=>$this->faker->randomElement(['Transport client meeting','Makan siang','Beli obat','Tiket kereta','Pulsa','Bensin','Parkir','Tol']),'status'=>$status,'rejection_reason'=>$status==='rejected'?'Bukti tidak lengkap':null,'paid_date'=>$status==='paid'?$date->addDays(mt_rand(1,14))->format('Y-m-d'):null,'paid_amount'=>$status==='paid'?$amount:null,'created_at'=>$date,'updated_at'=>$date];
            $created++;
        }
        Reimbursement::insert($reimbursements);
        $this->command->info("  {$created} reimbursement records created.");
    }

    private function seedVisits(int $count): void
    {
        $this->command->info("[Visits] Creating {$count} records...");
        $existing = Visit::count();
        $remaining = $count - $existing;
        if ($remaining <= 0) { $this->command->info("  Already enough."); return; }
        $emps = $this->employeeIds; $visits = []; $created = 0;
        for ($i = 0; $i < $remaining; $i++) {
            $date = Carbon::now()->subDays(mt_rand(1,90));
            $startHour = mt_rand(8,15); $duration = mt_rand(1,4);
            $lat = -6.2 + (mt_rand(-500,500)/10000); $lng = 106.8 + (mt_rand(-500,500)/10000);
            $visits[] = ['employee_id'=>$this->faker->randomElement($emps),'date'=>$date->format('Y-m-d'),'visit_type'=>$this->faker->randomElement(['field','customer','vendor','other']),'location'=>$this->faker->randomElement($this->cities),'purpose'=>$this->faker->randomElement(['Meeting client','Survey','Audit cabang','Training']),'start_time'=>Carbon::parse($date->format('Y-m-d')." {$startHour}:00:00"),'end_time'=>Carbon::parse($date->format('Y-m-d').' '.($startHour+$duration).':00:00'),'check_in_lat'=>$lat,'check_in_lng'=>$lng,'check_out_lat'=>$lat+(mt_rand(-10,10)/10000),'check_out_lng'=>$lng+(mt_rand(-10,10)/10000),'status'=>'completed','report'=>'Laporan kunjungan '.$date->format('d/m/Y'),'created_at'=>$date,'updated_at'=>$date];
            $created++;
        }
        Visit::insert($visits);
        $this->command->info("  {$created} visit records created.");
    }


    private function seedJobPostings(int $count): void
    {
        $this->command->info("[JobPostings] Creating {$count}...");
        $existing = JobPosting::count();
        $remaining = $count - $existing;
        if ($remaining <= 0) { $this->command->info("  Already enough."); return; }
        $depts = $this->departmentIds; $positions = $this->positionIds;
        $titles = ['Staff Administrasi','Teknisi IT','Graphic Designer','Content Writer','Digital Marketing','Business Analyst','Data Analyst','UI/UX Designer','Mobile Developer','Backend Developer','Frontend Developer','DevOps Engineer','Quality Assurance','HR Officer','Recruitment Specialist','Account Executive','Customer Service','Admin Gudang','Driver','Security'];
        $postings = []; $created = 0;
        for ($i = 0; $i < $remaining; $i++) {
            $publishedAt = Carbon::now()->subDays(mt_rand(5,120));
            $isClosed = mt_rand(1,100) <= 40;
            $salaryMin = mt_rand(3000000,10000000); $salaryMax = $salaryMin + mt_rand(1000000,8000000);
            $postings[] = ['company_id'=>$this->companyId,'department_id'=>$this->faker->randomElement($depts),'position_id'=>$this->faker->randomElement($positions),'title'=>$this->faker->randomElement($titles),'description'=>'Deskripsi pekerjaan: '.$this->faker->paragraph,'requirements'=>"Persyaratan:\n- Pendidikan minimal ".$this->faker->randomElement(['SMA/SMK','D3','S1'])."\n- Pengalaman ".mt_rand(1,5)." tahun",'responsibilities'=>"Tanggung jawab sesuai job desk",'employee_type'=>$this->faker->randomElement(['permanent','contract','probation']),'min_salary'=>$salaryMin,'max_salary'=>$salaryMax,'location'=>$this->faker->randomElement($this->cities),'is_remote'=>mt_rand(1,10)<=2,'quota'=>mt_rand(1,5),'status'=>$isClosed?'closed':'published','published_at'=>$publishedAt,'closed_at'=>$isClosed?(clone $publishedAt)->addDays(mt_rand(30,60)):null,'created_at'=>$publishedAt,'updated_at'=>$publishedAt];
            $created++;
        }
        JobPosting::insert($postings);
        $this->command->info("  {$created} job postings created.");
    }

    private function seedCandidates(int $count): void
    {
        $this->command->info("[Candidates] Creating {$count}...");
        $existing = Candidate::count();
        $remaining = $count - $existing;
        if ($remaining <= 0) { $this->command->info("  Already enough."); return; }
        $jobIds = JobPosting::pluck('id')->toArray();
        if (empty($jobIds)) return;
        $stages = ['applied','screening','hr_interview','user_interview','technical_test','offering','hired','rejected','withdrawn'];
        $candidates = []; $created = 0;
        for ($i = 0; $i < $remaining; $i++) {
            $fn = $this->faker->randomElement($this->firstNames);
            $ln = $this->faker->randomElement($this->lastNames);
            $stage = $stages[array_rand($stages)];
            $candidates[] = ['job_posting_id'=>$this->faker->randomElement($jobIds),'first_name'=>$fn,'last_name'=>$ln,'email'=>strtolower($fn.'.'.$ln.mt_rand(10,99)).'@gmail.com','phone'=>'08'.$this->faker->numerify('##########'),'source'=>$this->faker->randomElement(['jobstreet','linkedin','glints','kalibrr','referral','website']),'expected_salary'=>mt_rand(3000000,15000000),'available_date'=>Carbon::now()->addDays(mt_rand(7,60))->format('Y-m-d'),'pipeline_stage'=>$stage,'notes'=>$stage==='rejected'?'Tidak memenuhi kualifikasi':null,'rejection_reason'=>$stage==='rejected'?'Skill kurang sesuai':null,'created_at'=>now()->subDays(mt_rand(1,90)),'updated_at'=>now()];
            $created++;
            if (count($candidates)>=100) { Candidate::insert($candidates); $candidates=[]; }
        }
        if (!empty($candidates)) Candidate::insert($candidates);
        $this->command->info("  {$created} candidates created.");
    }

    private function seedInterviews(int $count): void
    {
        $this->command->info("[Interviews] Creating...");
        $existing = Interview::count();
        if ($existing >= $count) { $this->command->info("  Already enough."); return; }
        $candidateIds = Candidate::whereIn('pipeline_stage',['hr_interview','user_interview','technical_test','offering','hired'])->pluck('id')->toArray();
        if (empty($candidateIds)) $candidateIds = Candidate::pluck('id')->toArray();
        if (empty($candidateIds)) return;
        $emps = $this->employeeIds; $remaining = min($count - $existing, count($candidateIds)); $created = 0;
        for ($i = 0; $i < $remaining; $i++) {
            $scheduledAt = Carbon::now()->subDays(mt_rand(1,60))->setTime(mt_rand(9,16),mt_rand(0,59));
$interviewType = $this->faker->randomElement(['phone','video','onsite','technical_test']);
            $status = $this->faker->randomElement(['scheduled','completed','cancelled']);
            $interview = Interview::create(['candidate_id'=>$candidateIds[$i],'interview_type'=>$interviewType,'scheduled_at'=>$scheduledAt,'duration_minutes'=>mt_rand(30,90),'location'=>$interviewType==='video'?'Zoom/Google Meet':$this->faker->randomElement($this->cities),'meeting_link'=>$interviewType==='video'?'https://meet.google.com/'.$this->faker->regexify('[a-z]{3}-[a-z]{4}-[a-z]{3}'):null,'status'=>$status]);
            $ic = mt_rand(1,2);
            for ($j = 0; $j < $ic; $j++) {
                $interviewer = Interviewer::create(['interview_id'=>$interview->id,'employee_id'=>$this->faker->randomElement($emps),'role'=>$j===0?'lead':'panel']);
                if ($status==='completed') InterviewResult::create(['interview_id'=>$interview->id,'interviewer_id'=>$interviewer->id,'rating'=>mt_rand(30,50)/10,'comments'=>$this->faker->sentence,'recommendation'=>$this->faker->randomElement(['strong_hire','hire','maybe','reject'])]);
            }
            $created++;
        }
        $this->command->info("  {$created} interviews created.");
    }

    private function seedFeedbackCycles(): void
    {
        $this->command->info("[FeedbackCycles] Creating review cycles...");
        $existing = FeedbackCycle::count();
        if ($existing >= 5) { $this->command->info("  Already enough."); return; }
        $cycleNames = ['Review Kinerja Q1 2026','Review Kinerja Q2 2026','Review Kinerja Q3 2025','Review Kinerja Q4 2025','Review Tahunan 2025'];
        $questions = [
            ['q'=>'Kualitas pekerjaan yang dihasilkan','cat'=>'technical','type'=>'rating'],
            ['q'=>'Ketepatan waktu menyelesaikan tugas','cat'=>'initiative','type'=>'rating'],
            ['q'=>'Kemampuan komunikasi dan kolaborasi','cat'=>'communication','type'=>'rating'],
            ['q'=>'Inisiatif dan proaktif dalam bekerja','cat'=>'initiative','type'=>'rating'],
            ['q'=>'Kehadiran dan disiplin waktu','cat'=>'teamwork','type'=>'rating'],
            ['q'=>'Kemampuan problem solving','cat'=>'technical','type'=>'rating'],
            ['q'=>'Apa kekuatan utama karyawan ini?','cat'=>'leadership','type'=>'text'],
            ['q'=>'Apa yang perlu ditingkatkan?','cat'=>'leadership','type'=>'text'],
        ];
        $emps = array_slice($this->employeeIds,0,min(50,count($this->employeeIds)));
        foreach ($cycleNames as $idx => $name) {
            $startDate = Carbon::now()->subMonths((5-$idx)*3)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
            $isActive = $idx === 0;
            $cycle = FeedbackCycle::create(['company_id'=>$this->companyId,'name'=>$name,'start_date'=>$startDate->format('Y-m-d'),'end_date'=>$endDate->format('Y-m-d'),'status'=>$isActive?'active':'completed']);
            foreach ($questions as $qi => $q) {
                FeedbackQuestion::create(['cycle_id'=>$cycle->id,'question'=>$q['q'],'category'=>$q['cat'],'question_type'=>$q['type'],'options'=>$q['type']==='rating'?['Sangat Baik','Baik','Cukup','Kurang','Sangat Kurang']:null,'sort_order'=>$qi+1]);
            }
            $questionIds = FeedbackQuestion::where('cycle_id',$cycle->id)->pluck('id')->toArray();
            $cycleEmps = array_slice($emps,0,mt_rand(10,count($emps)));
            foreach ($cycleEmps as $revieweeId) {
                $fbReviewer = FeedbackReviewer::create(['cycle_id'=>$cycle->id,'reviewee_id'=>$revieweeId,'reviewer_id'=>$revieweeId,'reviewer_type'=>'self','status'=>$isActive?'pending':'completed','completed_at'=>$isActive?null:$endDate]);
                if (!$isActive) {
                    foreach ($questionIds as $qId) {
                        $qType = FeedbackQuestion::find($qId)->question_type;
                        FeedbackAnswer::create(['reviewer_id'=>$fbReviewer->id,'question_id'=>$qId,'rating'=>$qType==='rating'?mt_rand(30,50)/10:null,'text_answer'=>$qType==='text'?$this->faker->sentence:null,'selected_options'=>null]);
                    }
                }
            }
        }
        $this->command->info("  Feedback cycles: ".FeedbackCycle::count()." cycles, ".FeedbackAnswer::count()." answers.");
    }


    private function seedCanteenMenus(): void
    {
        $this->command->info("[Canteen] Creating menus...");
        if (CanteenMenu::count() > 0) return;
        $menus = [
            ['name'=>'Nasi Goreng','category'=>'Makanan','price'=>15000],
            ['name'=>'Mie Goreng','category'=>'Makanan','price'=>12000],
            ['name'=>'Ayam Goreng + Nasi','category'=>'Makanan','price'=>20000],
            ['name'=>'Soto Ayam','category'=>'Makanan','price'=>18000],
            ['name'=>'Bakso + Mie','category'=>'Makanan','price'=>14000],
            ['name'=>'Gado-gado','category'=>'Makanan','price'=>16000],
            ['name'=>'Sate Ayam','category'=>'Makanan','price'=>22000],
            ['name'=>'Nasi Padang','category'=>'Makanan','price'=>25000],
            ['name'=>'Nasi Uduk','category'=>'Makanan','price'=>13000],
            ['name'=>'Indomie Telor','category'=>'Makanan','price'=>10000],
            ['name'=>'Es Teh Manis','category'=>'Minuman','price'=>5000],
            ['name'=>'Es Jeruk','category'=>'Minuman','price'=>7000],
            ['name'=>'Kopi Hitam','category'=>'Minuman','price'=>6000],
            ['name'=>'Kopi Susu','category'=>'Minuman','price'=>8000],
            ['name'=>'Teh Botol','category'=>'Minuman','price'=>5000],
            ['name'=>'Air Mineral','category'=>'Minuman','price'=>4000],
            ['name'=>'Jus Alpukat','category'=>'Minuman','price'=>12000],
            ['name'=>'Gorengan (5pcs)','category'=>'Snack','price'=>5000],
            ['name'=>'Roti Bakar','category'=>'Snack','price'=>8000],
            ['name'=>'Pisang Goreng','category'=>'Snack','price'=>6000],
        ];
        foreach ($menus as $m) {
            CanteenMenu::create(['company_id'=>$this->companyId,'name'=>$m['name'],'description'=>$m['name'],'category'=>$m['category'],'price'=>$m['price'],'stock'=>mt_rand(20,100),'is_available'=>true]);
        }
        $this->command->info("  20 canteen menus created.");
    }

    private function seedCanteenOrders(int $count): void
    {
        $this->command->info("[Canteen] Creating {$count} orders...");
        $existing = CanteenOrder::count();
        $remaining = $count - $existing;
        if ($remaining <= 0) { $this->command->info("  Already enough."); return; }
        $menuIds = CanteenMenu::pluck('id','id')->toArray();
        $menuPrices = CanteenMenu::pluck('price','id')->toArray();
        if (empty($menuIds)) return;
        $emps = $this->employeeIds; $created = 0;
        for ($i = 0; $i < $remaining; $i++) {
            $orderDate = Carbon::now()->subDays(mt_rand(1,60));
            $itemCount = mt_rand(1,3); $totalAmount = 0; $items = [];
            for ($j = 0; $j < $itemCount; $j++) {
                $mid = array_rand($menuIds); $qty = mt_rand(1,3); $price = $menuPrices[$mid]; $sub = $price * $qty; $totalAmount += $sub;
                $items[] = ['menu_id'=>$mid,'quantity'=>$qty,'unit_price'=>$price,'subtotal'=>$sub];
            }
            $order = CanteenOrder::create(['employee_id'=>$this->faker->randomElement($emps),'order_date'=>$orderDate->format('Y-m-d'),'status'=>$this->faker->randomElement(['served','served','served','cancelled']),'total_amount'=>$totalAmount,'notes'=>null]);
            foreach ($items as $it) CanteenOrderItem::create(array_merge($it,['order_id'=>$order->id]));
            $created++;
        }
        $this->command->info("  {$created} canteen orders created.");
    }

    private function seedAnnouncements(int $count): void
    {
        $this->command->info("[Announcements] Creating {$count}...");
        $existing = Announcement::count();
        $remaining = $count - $existing;
        if ($remaining <= 0) { $this->command->info("  Already enough."); return; }
        $titles = ['Pengumuman Libur Nasional','Perubahan Jam Kerja','Info THR 2026','Jadwal Maintenance Server','Peluncuran Produk Baru','Program Employee of The Month','Sosialisasi BPJS','Update Kebijakan Cuti','Kegiatan Team Building','Info Vaksinasi','Perubahan Struktur','Lomba 17 Agustus','Pengumuman Bonus','Training Wajib K3','Info Parkir Baru','Jadwal Audit Internal','Listrik Padam','Program Referral','Info Gathering','Update Aplikasi'];
        for ($i = 0; $i < $remaining; $i++) {
            $publishedAt = Carbon::now()->subDays(mt_rand(1,180));
            $expiresAt = (clone $publishedAt)->addDays(mt_rand(30,90));
            $title = $titles[$i % count($titles)];
            Announcement::create(['company_id'=>$this->companyId,'title'=>$title,'content'=>$this->faker->paragraphs(2,true),'priority'=>$this->faker->randomElement(['low','normal','high','urgent']),'target_type'=>'all','target_department_ids'=>null,'target_position_ids'=>null,'expires_at'=>$expiresAt,'published_at'=>$publishedAt,'published_by'=>$this->faker->randomElement($this->employeeIds)]);
        }
        $this->command->info("  {$remaining} announcements created.");
    }

    private function seedPayrolls(): void
    {
        $this->command->info("[Payrolls] Creating 3 payroll periods...");
        $existingPeriods = PayrollPeriod::count();
        if ($existingPeriods >= 4) { $this->command->info("  Already enough."); return; }
        $emps = $this->employeeIds;
        $salaryComponents = SalaryComponent::pluck('id','code')->toArray();
        if (empty($salaryComponents)) {
            $salaryComponents['GP'] = SalaryComponent::create(['company_id'=>$this->companyId,'code'=>'GP','name'=>'Gaji Pokok','type'=>'income','calculation_type'=>'fixed','amount'=>0,'is_taxable'=>true,'is_mandatory'=>true,'sort_order'=>1,'is_active'=>true])->id;
            $salaryComponents['BPJS_TK'] = SalaryComponent::create(['company_id'=>$this->companyId,'code'=>'BPJS_TK','name'=>'BPJS Ketenagakerjaan','type'=>'deduction','calculation_type'=>'percentage','amount'=>2,'is_taxable'=>false,'is_mandatory'=>true,'sort_order'=>10,'is_active'=>true])->id;
            $salaryComponents['PPH21'] = SalaryComponent::create(['company_id'=>$this->companyId,'code'=>'PPH21','name'=>'PPh 21','type'=>'deduction','calculation_type'=>'percentage','amount'=>5,'is_taxable'=>false,'is_mandatory'=>true,'sort_order'=>12,'is_active'=>true])->id;
        }
        for ($m = 1; $m <= 3; $m++) {
            $monthStart = Carbon::now()->subMonths($m);
            $periodStart = $monthStart->copy()->startOfMonth();
            $periodEnd = $monthStart->copy()->endOfMonth();
            $periodCode = 'PAY-'.$periodStart->format('Ym');
            if (PayrollPeriod::where('period_code',$periodCode)->exists()) continue;
            $period = PayrollPeriod::create(['company_id'=>$this->companyId,'period_code'=>$periodCode,'start_date'=>$periodStart->format('Y-m-d'),'end_date'=>$periodEnd->format('Y-m-d'),'payment_date'=>$periodEnd->copy()->subDay()->format('Y-m-d'),'status'=>'completed','total_gross'=>0,'total_deductions'=>0,'total_net'=>0,'total_employees'=>0]);
            $tg = 0; $td = 0; $tn = 0; $pe = 0;
            foreach ($emps as $empId) {
                $emp = DB::table('employees')->find($empId);
                if (!$emp) continue;
                $baseSalary = $emp->basic_salary;
                $inc = $baseSalary + mt_rand(300000,1500000);
                $ded = round($baseSalary * 0.10) + mt_rand(50000,300000);
                $net = $inc - $ded;
                $payroll = Payroll::create(['period_id'=>$period->id,'employee_id'=>$empId,'gross_salary'=>$inc,'total_income_components'=>$inc,'total_deduction_components'=>$ded,'pph21_amount'=>round($baseSalary*0.05),'bpjs_tk_jht'=>round($baseSalary*0.02),'bpjs_tk_jp'=>round($baseSalary*0.003),'bpjs_tk_jkk'=>round($baseSalary*0.0024),'bpjs_tk_jkm'=>round($baseSalary*0.003),'bpjs_kes'=>round($baseSalary*0.01),'net_salary'=>$net,'attendance_days'=>mt_rand(18,22),'leave_days'=>mt_rand(0,3),'overtime_hours'=>mt_rand(0,15),'overtime_pay'=>mt_rand(0,300000),'status'=>'finalized','notes'=>null]);
                if (isset($salaryComponents['GP'])) PayrollItem::create(['payroll_id'=>$payroll->id,'salary_component_id'=>$salaryComponents['GP'],'name'=>'Gaji Pokok','type'=>'income','amount'=>$baseSalary]);
                if (isset($salaryComponents['BPJS_TK'])) PayrollItem::create(['payroll_id'=>$payroll->id,'salary_component_id'=>$salaryComponents['BPJS_TK'],'name'=>'BPJS Ketenagakerjaan','type'=>'deduction','amount'=>round($baseSalary*0.02)]);
                if (isset($salaryComponents['PPH21'])) PayrollItem::create(['payroll_id'=>$payroll->id,'salary_component_id'=>$salaryComponents['PPH21'],'name'=>'PPh 21','type'=>'deduction','amount'=>round($baseSalary*0.05)]);
                PaySlip::create(['payroll_id'=>$payroll->id,'slip_number'=>'SLIP-'.$periodCode.'-'.str_pad($empId,4,'0',STR_PAD_LEFT),'file_path'=>null,'sent_at'=>null]);
                $tg += $inc; $td += $ded; $tn += $net; $pe++;
            }
            $period->update(['total_gross'=>$tg,'total_deductions'=>$td,'total_net'=>$tn,'total_employees'=>$pe]);
        }
        $this->command->info("  Payroll periods: " . PayrollPeriod::count() . ", payrolls: " . Payroll::count());
    }


    private function seedMoreCoa(): void
    {
        $this->command->info("[COA] Adding more accounts...");
        $existing = Coa::count();
        if ($existing >= 35) { $this->command->info("  Already enough."); return; }
        if (empty($this->coaCategoryIds)) { $this->coaCategoryIds = CoaCategory::pluck('id','code')->toArray(); }
        $additional = [
            ['cat'=>'ASET','code'=>'1-1110','name'=>'Kas Kecil','is_header'=>false,'balance'=>5000000,'parent'=>'1-1000'],
            ['cat'=>'ASET','code'=>'1-1400','name'=>'Bank BNI','is_header'=>false,'balance'=>75000000,'parent'=>'1-1000'],
            ['cat'=>'ASET','code'=>'1-5400','name'=>'Peralatan IT','is_header'=>false,'balance'=>150000000,'parent'=>'1-5000'],
            ['cat'=>'ASET','code'=>'1-2100','name'=>'Piutang Karyawan','is_header'=>false,'balance'=>5000000,'parent'=>null],
            ['cat'=>'LIAB','code'=>'2-1100','name'=>'Utang Supplier','is_header'=>false,'balance'=>25000000,'parent'=>null],
            ['cat'=>'LIAB','code'=>'2-1200','name'=>'Kartu Kredit','is_header'=>false,'balance'=>15000000,'parent'=>null],
            ['cat'=>'PENDAPATAN','code'=>'4-1100','name'=>'Pendapatan Produk','is_header'=>false,'balance'=>0,'parent'=>null],
            ['cat'=>'PENDAPATAN','code'=>'4-1200','name'=>'Pendapatan Konsultasi','is_header'=>false,'balance'=>0,'parent'=>null],
            ['cat'=>'BEBAN','code'=>'5-2100','name'=>'Beban Sewa Kantor','is_header'=>false,'balance'=>0,'parent'=>null],
            ['cat'=>'BEBAN','code'=>'5-4100','name'=>'Beban Internet','is_header'=>false,'balance'=>0,'parent'=>null],
            ['cat'=>'BEBAN','code'=>'5-6100','name'=>'Beban Parkir & Tol','is_header'=>false,'balance'=>0,'parent'=>null],
            ['cat'=>'BEBAN','code'=>'5-9000','name'=>'Beban Lain-lain','is_header'=>false,'balance'=>0,'parent'=>null],
        ];
        $created = 0;
        foreach ($additional as $a) {
            if (Coa::where('code',$a['code'])->exists()) continue;
            $parentId = $a['parent'] ? Coa::where('code',$a['parent'])->value('id') : null;
            Coa::create(['company_id'=>$this->companyId,'category_id'=>$this->coaCategoryIds[$a['cat']] ?? reset($this->coaCategoryIds),'parent_id'=>$parentId,'code'=>$a['code'],'name'=>$a['name'],'description'=>$a['name'],'is_header'=>$a['is_header'],'opening_balance'=>$a['balance']??0,'balance_type'=>in_array($a['cat'],['ASET','BEBAN'])?'debit':'credit','is_active'=>true]);
            $created++;
        }
        $this->coaNonHeaderIds = Coa::where('is_header',false)->pluck('id')->toArray();
        $this->command->info("  {$created} accounts added.");
    }

    private function seedJournals(): void
    {
        $this->command->info("[Journals] Creating 1000+ journal entries...");
        if (Journal::count() >= 500) { $this->command->info("  Already enough."); return; }
        if (empty($this->coaNonHeaderIds)) { $this->coaNonHeaderIds = Coa::where('is_header',false)->pluck('id')->toArray(); }
        if (empty($this->coaNonHeaderIds)) return;
        $userId = User::first()?->id ?? 1;
        $coaIds = $this->coaNonHeaderIds;
        $types = [['type'=>'general','count'=>500],['type'=>'sales','count'=>200],['type'=>'purchase','count'=>200],['type'=>'bank','count'=>100]];
        $tj = 0; $te = 0;
        foreach ($types as $jt) {
            for ($i = 0; $i < $jt['count']; $i++) {
                $jd = Carbon::now()->subDays(mt_rand(1,180));
                $amount = mt_rand(50000,5000000);
                if($jt['type']==='sales')$desc='Penjualan produk';elseif($jt['type']==='purchase')$desc='Pembelian ATK';elseif($jt['type']==='bank')$desc='Setoran tunai';else $desc='Jurnal umum';
                $journal = Journal::create(['company_id'=>$this->companyId,'journal_number'=>'JRN-'.$jt['type'].'-'.$jd->format('Ymd').'-'.str_pad($i+1,4,'0',STR_PAD_LEFT),'journal_date'=>$jd->format('Y-m-d'),'journal_type'=>$jt['type'],'description'=>$desc,'total_debit'=>$amount,'total_credit'=>$amount,'reference_type'=>null,'reference_id'=>null,'status'=>'posted','posted_by'=>$userId,'posted_at'=>$jd]);
                JournalEntry::create(['journal_id'=>$journal->id,'coa_id'=>$this->faker->randomElement($coaIds),'description'=>$desc,'debit'=>$amount,'credit'=>0]);
                JournalEntry::create(['journal_id'=>$journal->id,'coa_id'=>$this->faker->randomElement($coaIds),'description'=>$desc,'debit'=>0,'credit'=>$amount]);
                $tj++; $te+=2;
            }
        }
        $this->command->info("  {$tj} journals, {$te} entries.");
    }

    private function seedInvoices(int $salesCount, int $purchaseCount): void
    {
        $this->command->info("[Invoices] Creating {$salesCount} sales + {$purchaseCount} purchase...");
        $existing = Invoice::count();
        if ($existing >= ($salesCount + $purchaseCount)) { $this->command->info("  Already enough."); return; }
        $clients = Client::pluck('id')->toArray();
        if (empty($clients)) $clients = [1];
        $ti = 0;
        for ($i = 0; $i < $salesCount; $i++) {
            $invoiceDate = Carbon::now()->subDays(mt_rand(1,180)); $dueDate = (clone $invoiceDate)->addDays(30);
            $ic = mt_rand(2,5); $subtotal = 0; $items = [];
            for ($j = 0; $j < $ic; $j++) { $qty = mt_rand(1,10); $up = mt_rand(10000,500000); $amt = $qty*$up; $subtotal+=$amt; $items[]=['description'=>$this->faker->randomElement(['Jasa Konsultasi','Biaya Langganan','Biaya Training','Pengiriman Barang']),'quantity'=>$qty,'unit_price'=>$up,'tax_rate'=>11,'amount'=>$amt]; }
            $disc = $subtotal>500000?round($subtotal*mt_rand(5,15)/100):0; $tax = round(($subtotal-$disc)*0.11); $total = $subtotal-$disc+$tax;
            $paid = mt_rand(0,100)<=70?$total:round($total*mt_rand(30,90)/100); $rem = $total-$paid;
            $invoice = Invoice::create(['company_id'=>$this->companyId,'invoice_number'=>'INV-MAJU-'.$invoiceDate->format('Ym').'-'.str_pad($i+1,4,'0',STR_PAD_LEFT),'invoice_type'=>'sales','invoice_date'=>$invoiceDate->format('Y-m-d'),'due_date'=>$dueDate->format('Y-m-d'),'reference_entity'=>'client','reference_id'=>$this->faker->randomElement($clients),'subtotal'=>$subtotal,'discount_amount'=>$disc,'tax_amount'=>$tax,'total'=>$total,'paid_amount'=>$paid,'remaining_amount'=>$rem,'status'=>$paid>=$total?'paid':($paid>0?'partial':'sent'),'notes'=>null]);
            foreach ($items as $it) InvoiceItem::create(array_merge($it,['invoice_id'=>$invoice->id]));
            $ti++;
        }
        for ($i = 0; $i < $purchaseCount; $i++) {
            $invoiceDate = Carbon::now()->subDays(mt_rand(1,180)); $dueDate = (clone $invoiceDate)->addDays(14);
            $ic = mt_rand(2,4); $subtotal = 0; $items = [];
            for ($j = 0; $j < $ic; $j++) { $qty = mt_rand(1,20); $up = mt_rand(5000,2000000); $amt = $qty*$up; $subtotal+=$amt; $items[]=['description'=>$this->faker->randomElement(['ATK Kantor','Peralatan IT','Software License','Alat Tulis']),'quantity'=>$qty,'unit_price'=>$up,'tax_rate'=>11,'amount'=>$amt]; }
            $tax = round($subtotal*0.11); $total = $subtotal+$tax;
            $paid = mt_rand(0,100)<=75?$total:round($total*mt_rand(40,80)/100); $rem = $total-$paid;
            $invoice = Invoice::create(['company_id'=>$this->companyId,'invoice_number'=>'PO-MAJU-'.$invoiceDate->format('Ym').'-'.str_pad($i+1,4,'0',STR_PAD_LEFT),'invoice_type'=>'purchase','invoice_date'=>$invoiceDate->format('Y-m-d'),'due_date'=>$dueDate->format('Y-m-d'),'reference_entity'=>'vendor','reference_id'=>1,'subtotal'=>$subtotal,'discount_amount'=>0,'tax_amount'=>$tax,'total'=>$total,'paid_amount'=>$paid,'remaining_amount'=>$rem,'status'=>$paid>=$total?'paid':($paid>0?'partial':'sent'),'notes'=>null]);
            foreach ($items as $it) InvoiceItem::create(array_merge($it,['invoice_id'=>$invoice->id]));
            $ti++;
        }
        $this->command->info("  {$ti} invoices created.");
    }

    private function seedPayments(int $count): void
    {
        $this->command->info("[Payments] Creating {$count} payments...");
        $existing = Payment::count();
        $remaining = $count - $existing;
        if ($remaining <= 0) { $this->command->info("  Already enough."); return; }
        $pmIds = $this->paymentMethodIds ?: PaymentMethod::pluck('id')->toArray() ?: [1];
        $invIds = Invoice::pluck('id')->toArray();
        $userId = User::first()?->id ?? 1;
        for ($i = 0; $i < $remaining; $i++) {
            $pd = Carbon::now()->subDays(mt_rand(1,90)); $amount = mt_rand(50000,10000000);
            $status = $this->faker->randomElement(['confirmed','confirmed','confirmed','pending']);
            $payment = Payment::create(['company_id'=>$this->companyId,'payment_number'=>'PAY-'.$pd->format('Ymd').'-'.str_pad($i+1,4,'0',STR_PAD_LEFT),'payment_date'=>$pd->format('Y-m-d'),'payment_method_id'=>$this->faker->randomElement($pmIds),'amount'=>$amount,'reference_number'=>'REF-'.strtoupper($this->faker->regexify('[A-Z0-9]{10}')),'notes'=>null,'status'=>$status,'confirmed_by'=>$status==='confirmed'?$userId:null,'confirmed_at'=>$status==='confirmed'?$pd:null]);
            if (!empty($invIds) && mt_rand(1,100)<=70) {
                $invId = $this->faker->randomElement($invIds);
                InvoicePayment::create(['invoice_id'=>$invId,'payment_id'=>$payment->id,'amount'=>min($amount,Invoice::find($invId)->total ?? $amount)]);
            }
        }
        $this->command->info("  {$remaining} payments created.");
    }


    private function seedBudgets(): void
    {
        $this->command->info("[Budgets] Creating 10 budgets...");
        if (Budget::count() >= 10) { $this->command->info("  Already enough."); return; }
        $depts = $this->departmentIds;
        $coaIds = Coa::where('is_header',false)->pluck('id')->toArray() ?: [1];
        $names = ['Anggaran Operasional 2026','Anggaran IT 2026','Anggaran Marketing 2026','Anggaran HR 2026','Anggaran Produksi 2026','Anggaran Kantor Pusat','Anggaran Cabang Bandung','Anggaran Cabang Surabaya','Anggaran Training','Anggaran Event Tahunan'];
        foreach ($names as $name) {
            $sd = Carbon::create(2026,1,1); $ed = Carbon::create(2026,12,31);
            $budget = Budget::create(['company_id'=>$this->companyId,'name'=>$name,'fiscal_year'=>2026,'start_date'=>$sd,'end_date'=>$ed,'department_id'=>$this->faker->randomElement($depts),'project_id'=>null,'status'=>$this->faker->randomElement(['draft','active','active']),'approved_by'=>null,'approved_at'=>null]);
            for ($j = 0; $j < 5; $j++) {
                $planned = mt_rand(5000000,100000000); $actual = round($planned * mt_rand(20,95)/100);
                BudgetItem::create(['budget_id'=>$budget->id,'coa_id'=>$this->faker->randomElement($coaIds),'description'=>$this->faker->randomElement(['Biaya gaji','Biaya operasional','Biaya peralatan','Biaya pemasaran','Biaya perjalanan dinas']),'planned_amount'=>$planned,'actual_amount'=>$actual,'variance'=>$planned-$actual,'period_start'=>$sd,'period_end'=>$ed]);
            }
        }
        $this->command->info("  Budgets: ".Budget::count().", items: ".BudgetItem::count());
    }

    private function seedAssets(int $count): void
    {
        $this->command->info("[Assets] Creating {$count} assets...");
        $existing = Asset::count();
        $remaining = $count - $existing;
        if ($remaining <= 0) { $this->command->info("  Already enough."); return; }
        if (AssetCategory::count()==0) { AssetCategory::create(['company_id'=>$this->companyId,'code'=>'MISC','name'=>'Peralatan Kantor','depreciation_method'=>'straight_line','useful_life_years'=>5,'salvage_value_percent'=>10]); }
        $catIds = AssetCategory::pluck('id')->toArray();
        $emps = $this->employeeIds;
        $types = [
            ['name'=>'Laptop Dell','cost'=>15000000,'life'=>4,'salvage'=>1000000],
            ['name'=>'Monitor 24"','cost'=>3500000,'life'=>4,'salvage'=>200000],
            ['name'=>'Printer Canon','cost'=>4000000,'life'=>5,'salvage'=>300000],
            ['name'=>'Meja Kantor','cost'=>2500000,'life'=>10,'salvage'=>100000],
            ['name'=>'Kursi Ergonomis','cost'=>1800000,'life'=>10,'salvage'=>80000],
            ['name'=>'AC Split 1PK','cost'=>5000000,'life'=>8,'salvage'=>400000],
            ['name'=>'Proyektor','cost'=>9000000,'life'=>5,'salvage'=>500000],
            ['name'=>'Avanza','cost'=>230000000,'life'=>8,'salvage'=>30000000],
            ['name'=>'Server Dell','cost'=>45000000,'life'=>5,'salvage'=>2000000],
        ];
        $nextCode = Asset::max('id') ? Asset::max('id')+1 : 1;
        for ($i = 0; $i < $remaining; $i++) {
            $t = $types[$i % count($types)];
            $acqDate = Carbon::now()->subMonths(mt_rand(1,36));
            $yearsUsed = Carbon::now()->diffInYears($acqDate);
            $dep = round($t['cost'] * $yearsUsed / $t['life']);
            Asset::create(['company_id'=>$this->companyId,'category_id'=>$catIds[array_rand($catIds)],'asset_code'=>'AST-'.str_pad($nextCode++,4,'0',STR_PAD_LEFT),'name'=>$t['name'].' '.($i+1),'description'=>$t['name'],'acquisition_date'=>$acqDate->format('Y-m-d'),'acquisition_cost'=>$t['cost'],'useful_life_years'=>$t['life'],'salvage_value'=>$t['salvage'],'current_value'=>$t['cost']-$dep,'accumulated_depreciation'=>$dep,'location'=>$this->faker->randomElement(['Kantor Pusat','Cabang Bandung','Cabang Surabaya']),'current_employee_id'=>mt_rand(1,100)<=60?$this->faker->randomElement($emps):null,'status'=>$this->faker->randomElement(['active','active','active','maintenance']),'purchase_invoice_id'=>null,'warranty_expiry'=>(clone $acqDate)->addYears(mt_rand(1,3))->format('Y-m-d')]);
        }
        $this->command->info("  {$remaining} assets created.");
    }

    private function seedMoreClients(int $count): void
    {
        $this->command->info("[Clients] Adding {$count} more...");
        $existing = Client::count();
        $remaining = $count - $existing;
        if ($remaining <= 0) { $this->command->info("  Already enough."); return; }
        $industries = ['Teknologi','Keuangan','Distribusi','Konstruksi','Retail','Manufaktur','Pendidikan','Kesehatan','Logistik','F&B','Media'];
        for ($i = 0; $i < $remaining; $i++) {
            $companyName = 'PT '.$this->faker->company;
            $client = Client::create(['company_id'=>$this->companyId,'client_code'=>'CL-'.str_pad(Client::max('id')+$i+1,3,'0',STR_PAD_LEFT),'name'=>$companyName,'client_type'=>mt_rand(1,100)<=80?'company':'individual','industry'=>$this->faker->randomElement($industries),'tax_id'=>$this->faker->numerify('##.###.###.#-###.###'),'address'=>'Jl. '.$this->faker->streetName.' No. '.mt_rand(1,500),'city'=>$this->faker->randomElement($this->cities),'province'=>$this->faker->randomElement(['DKI Jakarta','Jawa Barat','Jawa Timur','Banten']),'postal_code'=>$this->faker->numerify('#####'),'phone'=>'021-'.$this->faker->numerify('####-####'),'email'=>'info@'.strtolower(str_replace(['PT ','CV ',' ','.','-'],'',$companyName)).'.co.id','status'=>mt_rand(1,100)<=90?'active':'inactive']);
            $cc = mt_rand(1,2);
            for ($j = 0; $j < $cc; $j++) {
                ClientContact::create(['client_id'=>$client->id,'first_name'=>$this->faker->firstName,'last_name'=>$this->faker->lastName,'position'=>$this->faker->randomElement(['Manager','Direktur','Purchasing','Finance']),'email'=>strtolower($this->faker->firstName).'@'.strtolower(str_replace(['PT ',' ','.','-'],'',$companyName)).'.co.id','phone'=>'08'.$this->faker->numerify('##########'),'is_primary'=>$j===0]);
            }
        }
        $this->clientIds = Client::pluck('id')->toArray();
        $this->command->info("  {$remaining} clients created.");
    }

    private function seedLeads(int $count): void
    {
        $this->command->info("[Leads] Creating {$count} leads...");
        $existing = Lead::count();
        $remaining = $count - $existing;
        if ($remaining <= 0) { $this->command->info("  Already enough."); return; }
        $sourceIds = LeadSource::pluck('id')->toArray() ?: array_map(fn($p)=>LeadSource::create(['company_id'=>$this->companyId,'name'=>$p,'is_active'=>true])->id,['Website','Instagram']);
        $emps = $this->employeeIds;
        $statuses = ['new','contacted','qualified','proposal','negotiation','won','lost'];
        $weights = [15,20,20,15,10,10,10];
        for ($i = 0; $i < $remaining; $i++) {
            $idx = rand(0,array_sum($weights)-1); $cum=0; $status='new';
            foreach($statuses as $si=>$sn) { $cum+=$weights[$si]; if($idx<$cum){$status=$sn;break;} }
            Lead::create(['company_id'=>$this->companyId,'source_id'=>$this->faker->randomElement($sourceIds),'assigned_to'=>$this->faker->randomElement($emps),'first_name'=>$this->faker->randomElement($this->firstNames),'last_name'=>$this->faker->randomElement($this->lastNames),'email'=>strtolower($this->faker->firstName).mt_rand(10,99).'@gmail.com','phone'=>'08'.$this->faker->numerify('##########'),'company_name'=>$this->faker->randomElement(['PT','CV','UD']).' '.$this->faker->company,'industry'=>$this->faker->randomElement(['Teknologi','Retail','Keuangan']),'score'=>mt_rand(10,100),'status'=>$status,'lost_reason'=>$status==='lost'?'Harga mahal':null,'converted_client_id'=>$status==='won'&&mt_rand(1,100)<=30?$this->faker->randomElement($this->clientIds):null,'notes'=>null,'next_follow_up'=>in_array($status,['new','contacted','qualified'])?Carbon::now()->addDays(mt_rand(1,14)):null]);
        }
        $this->command->info("  {$remaining} leads created.");
    }

    private function seedDeals(int $count): void
    {
        $this->command->info("[Deals] Creating {$count} deals...");
        $existing = Deal::count();
        $remaining = $count - $existing;
        if ($remaining <= 0) { $this->command->info("  Already enough."); return; }
        $leadIds = Lead::pluck('id')->toArray() ?: [1];
        $stageIds = PipelineStage::pluck('id')->toArray() ?: [1];
        $emps = $this->employeeIds;
        $statuses = ['open','open','open','won','won','lost'];
        for ($i = 0; $i < $remaining; $i++) {
            $status = $statuses[array_rand($statuses)];
            $ec = Carbon::now()->subDays(mt_rand(1,90))->addDays(mt_rand(14,90));
            Deal::create(['company_id'=>$this->companyId,'lead_id'=>$this->faker->randomElement($leadIds),'client_id'=>$status==='won'?$this->faker->randomElement($this->clientIds):null,'stage_id'=>$this->faker->randomElement($stageIds),'assigned_to'=>$this->faker->randomElement($emps),'title'=>$this->faker->randomElement(['Implementasi ERP','Website Development','Mobile App','SEO Campaign','Software License','Konsultasi IT']),'expected_value'=>mt_rand(10000000,500000000),'expected_close_date'=>$ec->format('Y-m-d'),'actual_close_date'=>$status!=='open'?$ec->addDays(mt_rand(-10,10)):null,'status'=>$status,'lost_reason'=>$status==='lost'?'Harga tidak cocok':null,'notes'=>null]);
        }
        $this->command->info("  {$remaining} deals created.");
    }


    private function seedWaTemplates(int $count): void
    {
        $this->command->info("[WA] Creating {$count} templates...");
        $existing = WaTemplate::count();
        $remaining = $count - $existing;
        if ($remaining <= 0) { $this->command->info("  Already enough."); return; }
        $cats = ['promosi','notifikasi','reminder','ucapan','followup'];
        for ($i = 0; $i < $remaining; $i++) {
            $cat = $cats[$i % 5];
            $content = match($cat) {
                'promosi'=>"Halo {nama},\n\nJangan lewatkan promo spesial kami! Diskon hingga {diskon}%.\nInfo: {link}",
                'notifikasi'=>"Halo {nama},\n\nStatus pesanan Anda #{order_id} sudah {status}. Terima kasih.",
                'reminder'=>"Halo {nama},\n\nPengingat untuk {agenda} pada {tanggal}. Jangan lupa!",
                'ucapan'=>"Halo {nama},\n\n{messages}",
                'followup'=>"Halo {nama},\n\nTerima kasih sudah menghubungi kami. Ada yang bisa dibantu?\n{link}"
            };
            WaTemplate::create(['company_id'=>$this->companyId,'name'=>'Template '.ucfirst($cat).' '.($i+1),'content'=>$content,'category'=>$cat,'language'=>'id','status'=>$this->faker->randomElement(['active','active','active','draft'])]);
        }
        $this->command->info("  {$remaining} WA templates created.");
    }

    private function seedWaCampaigns(int $count): void
    {
        $this->command->info("[WA] Creating {$count} campaigns...");
        $existing = WaBlastCampaign::count();
        $remaining = $count - $existing;
        if ($remaining <= 0) { $this->command->info("  Already enough."); return; }
        $templateIds = WaTemplate::pluck('id')->toArray() ?: [1];
        for ($i = 0; $i < $remaining; $i++) {
            $sa = Carbon::now()->subDays(mt_rand(1,60));
            $tt = mt_rand(50,500); $ts = mt_rand(round($tt*0.7),$tt); $tf = $tt-$ts;
            WaBlastCampaign::create(['company_id'=>$this->companyId,'template_id'=>$this->faker->randomElement($templateIds),'name'=>'Kampanye '.($this->faker->randomElement(['Promo Ramadhan','Pengumuman THR','Info Produk Baru','Newsletter'])) . ' #'.($i+1),'target_type'=>'all_clients','target_segment_id'=>null,'target_clients'=>null,'scheduled_at'=>$sa,'sent_at'=>$sa->addMinutes(mt_rand(1,30)),'total_targets'=>$tt,'total_sent'=>$ts,'total_failed'=>$tf,'status'=>$this->faker->randomElement(['completed','completed','completed','processing'])]);
        }
        $this->command->info("  {$remaining} WA campaigns created.");
    }

    private function seedWaBlastLogs(int $count): void
    {
        $this->command->info("[WA] Creating {$count} blast logs...");
        $campaignIds = WaBlastCampaign::pluck('id')->toArray();
        if (empty($campaignIds)) return;
        $statuses = ['sent','delivered','read','failed'];
        for ($i = 0; $i < $count; $i++) {
            $r = mt_rand(1,100);
            if ($r <= 10) $status = 'sent';
            elseif ($r <= 60) $status = 'delivered';
            elseif ($r <= 95) $status = 'read';
            else $status = 'failed';
            $sa = Carbon::now()->subDays(mt_rand(1,60));
            WaBlastLog::create(['campaign_id'=>$this->faker->randomElement($campaignIds),'contact_phone'=>'08'.$this->faker->numerify('##########'),'contact_name'=>$this->faker->name,'message'=>'Halo, ini pesan broadcast kampanye.','status'=>$status,'error_message'=>$status==='failed'?'Invalid phone':null,'sent_at'=>$sa,'delivered_at'=>in_array($status,['delivered','read'])?$sa->addMinutes(mt_rand(1,5)):null,'read_at'=>$status==='read'?$sa->addMinutes(mt_rand(5,60)):null]);
        }
        $this->command->info("  {$count} WA blast logs created.");
    }

    private function seedProjects(int $count): void
    {
        $this->command->info("[Projects] Creating {$count} projects...");
        $existing = Project::count();
        $remaining = $count - $existing;
        if ($remaining <= 0) { $this->command->info("  Already enough."); return; }
        $depts = $this->departmentIds; $emps = $this->employeeIds; $clients = $this->clientIds;
        $names = ['Pengembangan Web Dashboard','Migrasi Database','Security Audit','Implementasi CRM','Revamp Website','Integrasi Payment','API Gateway','Data Warehouse','Digital Transformation','Mobile App','Chatbot AI','Cloud Migration','Process Automation','E-Learning Platform','KPI Management'];
        $totalTasks = 0;
        for ($i = 0; $i < $remaining; $i++) {
            $sd = Carbon::now()->subDays(mt_rand(30,180)); $ed = (clone $sd)->addDays(mt_rand(60,180));
            $budget = mt_rand(50000000,300000000); $progress = mt_rand(0,100);
            $project = Project::create(['company_id'=>$this->companyId,'department_id'=>$this->faker->randomElement($depts),'client_id'=>mt_rand(1,100)<=50?$this->faker->randomElement($clients):null,'manager_id'=>$this->faker->randomElement($emps),'code'=>'PRJ-'.str_pad($i+4,3,'0',STR_PAD_LEFT),'name'=>$names[$i],'description'=>'Proyek '.$names[$i],'start_date'=>$sd->format('Y-m-d'),'end_date'=>$ed->format('Y-m-d'),'budget'=>$budget,'actual_cost'=>round($budget*$progress/100),'status'=>$progress>=100?'completed':($progress>0?'active':'planning'),'priority'=>$this->faker->randomElement(['low','medium','high','urgent']),'progress_percent'=>$progress,'color'=>$this->faker->randomElement(['#4f46e5','#10b981','#f59e0b','#ef4444','#8b5cf6'])]);
            $pc = mt_rand(2,3);
            for ($p = 0; $p < $pc; $p++) {
                $ps = (clone $sd)->addDays($p*round($sd->diffInDays($ed)/($pc?:1)));
                $pe = $p<$pc-1?(clone $sd)->addDays(($p+1)*round($sd->diffInDays($ed)/($pc?:1))):$ed;
                $phase = ProjectPhase::create(['project_id'=>$project->id,'name'=>$this->faker->randomElement(['Analisis','Desain','Pengembangan','Testing','Deployment']),'description'=>'Fase '.($p+1),'start_date'=>$ps->format('Y-m-d'),'end_date'=>$pe->format('Y-m-d'),'sort_order'=>$p+1,'status'=>'active']);
                $tc = mt_rand(2,3);
                for ($t = 0; $t < $tc; $t++) {
                    $task = Task::create(['project_id'=>$project->id,'phase_id'=>$phase->id,'parent_id'=>null,'milestone_id'=>null,'title'=>$this->faker->randomElement(['Rapat kickoff','Desain UI','Setup env','Coding','Testing','Dokumentasi','Review','Deploy']),'description'=>'Task description','status'=>$this->faker->randomElement(['todo','in_progress','done']),'priority'=>$this->faker->randomElement(['low','medium','high']),'type'=>'task','estimated_hours'=>mt_rand(4,40),'actual_hours'=>mt_rand(2,35),'start_date'=>$ps->format('Y-m-d'),'due_date'=>$pe->format('Y-m-d'),'completed_at'=>null,'sort_order'=>$t+1,'created_by'=>$this->faker->randomElement($emps)]);
                    $task->assignees()->attach($this->faker->randomElement($emps));
                    $totalTasks++;
                }
            }
            ProjectMember::create(['project_id'=>$project->id,'employee_id'=>$project->manager_id,'role'=>'manager','joined_at'=>$sd->format('Y-m-d')]);
        }
        $this->command->info("  {$remaining} projects, {$totalTasks} tasks created.");
    }


    private function seedMoreProducts(int $count): void
    {
        $this->command->info("[Products] Adding {$count} products...");
        $existing = Product::count();
        $remaining = $count - $existing;
        if ($remaining <= 0) { $this->command->info("  Already enough."); return; }
        $cats = ProductCategory::pluck('id')->toArray() ?: [1];
        $plist = [
            ['name'=>'Notebook A5','unit'=>'pcs','buy'=>8000,'sell'=>12000],['name'=>'Amplop Coklat','unit'=>'pcs','buy'=>500,'sell'=>1000],
            ['name'=>'Stapler','unit'=>'pcs','buy'=>15000,'sell'=>25000],['name'=>'Isi Stapler','unit'=>'box','buy'=>5000,'sell'=>8000],
            ['name'=>'USB Cable','unit'=>'pcs','buy'=>25000,'sell'=>40000],['name'=>'Keyboard Wireless','unit'=>'pcs','buy'=>120000,'sell'=>180000],
            ['name'=>'Webcam HD','unit'=>'pcs','buy'=>200000,'sell'=>350000],['name'=>'Headset BT','unit'=>'pcs','buy'=>150000,'sell'=>250000],
            ['name'=>'Power Bank','unit'=>'pcs','buy'=>120000,'sell'=>180000],['name'=>'Tempat Pensil','unit'=>'pcs','buy'=>15000,'sell'=>25000],
            ['name'=>'Gunting','unit'=>'pcs','buy'=>10000,'sell'=>18000],['name'=>'Double Tape','unit'=>'roll','buy'=>5000,'sell'=>10000],
            ['name'=>'Hand Sanitizer','unit'=>'botol','buy'=>12000,'sell'=>20000],['name'=>'Masker Kain','unit'=>'pcs','buy'=>3000,'sell'=>6000],
            ['name'=>'Baterai AA','unit'=>'pack','buy'=>15000,'sell'=>25000],['name'=>'Kabel LAN 5m','unit'=>'pcs','buy'=>25000,'sell'=>40000],
            ['name'=>'Mousepad','unit'=>'pcs','buy'=>20000,'sell'=>35000],['name'=>'Kalkulator','unit'=>'pcs','buy'=>35000,'sell'=>60000],
            ['name'=>'Stopmap','unit'=>'pcs','buy'=>2000,'sell'=>4000],['name'=>'Lakban','unit'=>'roll','buy'=>7000,'sell'=>12000],
            ['name'=>'Gelas Plastik','unit'=>'pack','buy'=>8000,'sell'=>15000],['name'=>'Sendok Plastik','unit'=>'pack','buy'=>5000,'sell'=>10000],
            ['name'=>'Tisu Basah','unit'=>'pack','buy'=>10000,'sell'=>18000],['name'=>'Sabun Cuci Tangan','unit'=>'botol','buy'=>15000,'sell'=>25000],
            ['name'=>'Pengharum Ruangan','unit'=>'botol','buy'=>20000,'sell'=>35000],
        ];
        $cs = Product::max('id') ? Product::max('id')+1 : 11; $cr = 0;
        foreach ($plist as $p) {
            if ($cr >= $remaining) break;
            Product::create(['company_id'=>$this->companyId,'category_id'=>$this->faker->randomElement($cats),'code'=>'PRD-'.str_pad($cs++,3,'0',STR_PAD_LEFT),'name'=>$p['name'],'description'=>$p['name'],'unit'=>$p['unit'],'purchase_price'=>$p['buy'],'selling_price'=>$p['sell'],'stock'=>mt_rand(10,300),'min_stock'=>10,'max_stock'=>500,'is_taxable'=>true,'tax_rate'=>11,'is_active'=>true]);
            $cr++;
        }
        $this->productIds = Product::pluck('id')->toArray();
        $this->command->info("  {$cr} products created.");
    }

    private function seedMorePosTransactions(int $count): void
    {
        $this->command->info("[POS] Creating {$count} transactions...");
        $existing = PosTransaction::count();
        $remaining = $count - $existing;
        if ($remaining <= 0) { $this->command->info("  Already enough."); return; }
        $products = Product::pluck('id','id')->toArray();
        $prices = Product::pluck('selling_price','id')->toArray();
        if (empty($products) || empty($prices)) return;
        $cashierIds = $this->employeeIds;
        $memberIds = PosMember::pluck('id')->toArray();
        // Ensure a cashier shift exists
        $shift = \App\Models\CashierShift::first();
        if (!$shift) {
            $shift = \App\Models\CashierShift::create([
                'employee_id' => $cashierIds[0] ?? 1,
                'branch_id' => $this->branchIds[0] ?? 1,
                'shift_date' => now()->format('Y-m-d'),
                'opening_time' => now()->setTime(7, 0),
                'opening_balance' => 500000,
                'closing_time' => now()->setTime(16, 0),
                'closing_balance' => 500000,
                'expected_cash' => 0,
                'actual_cash' => 0,
                'difference' => 0,
                'total_transactions' => 0,
                'total_sales' => 0,
                'status' => 'open',
            ]);
        }
        $defaultShiftId = $shift->id;

        for ($i = 0; $i < $remaining; $i++) {
            $txDate = Carbon::now()->subDays(mt_rand(1,90))->setTime(mt_rand(7,21),mt_rand(0,59));
            $ic = mt_rand(1,5); $sub = 0; $its = [];
            $selPids = (array)array_rand($products, min($ic, count($products)));
            if (empty($selPids)) $selPids = [array_rand($products)];
            foreach ($selPids as $pid) {
                $qty = mt_rand(1,10); $price = $prices[$pid] ?? 10000;
                $disc = mt_rand(1,30)<=5 ? round($price*$qty*mt_rand(5,15)/100) : 0;
                $ls = ($price*$qty)-$disc; $sub += $ls;
                $its[] = ['product_id'=>$pid,'variant_id'=>null,'quantity'=>$qty,'unit_price'=>$price,'discount_amount'=>$disc,'tax_amount'=>round($ls*0.11),'subtotal'=>$ls];
            }
            $dt = array_sum(array_column($its,'discount_amount'));
            $tt = array_sum(array_column($its,'tax_amount'));
            $gt = $sub + $tt;
            $tx = PosTransaction::create(['company_id'=>$this->companyId,'shift_id'=>$defaultShiftId,'receipt_number'=>'INV-'.$txDate->format('Ym').'-'.str_pad($i+21,5,'0',STR_PAD_LEFT),'member_id'=>!empty($memberIds)&&mt_rand(1,10)<=3?$this->faker->randomElement($memberIds):null,'cashier_id'=>$this->faker->randomElement($cashierIds),'transaction_date'=>$txDate,'subtotal'=>$sub,'discount_total'=>$dt,'tax_total'=>$tt,'grand_total'=>$gt,'payment_status'=>'paid','notes'=>null]);
            foreach ($its as $it) PosTransactionItem::create(array_merge($it,['transaction_id'=>$tx->id]));
            PosPayment::create(['transaction_id'=>$tx->id,'payment_method'=>$this->faker->randomElement(['Tunai','Transfer','QRIS']),'amount'=>$gt,'reference_number'=>'POS-'.strtoupper($this->faker->regexify('[A-Z0-9]{10}')),'paid_at'=>$txDate]);
        }
        $this->command->info("  {$remaining} POS transactions created.");
    }

    private function seedMeetings(int $count): void
    {
        $this->command->info("[Meetings] Creating {$count} meetings...");
        $existing = Meeting::count();
        $remaining = $count - $existing;
        if ($remaining <= 0) { $this->command->info("  Already enough."); return; }
        $emps = $this->employeeIds;
        $titles = ['Rapat Koordinasi','Sprint Planning','Daily Standup','Retrospective','Review Progress','Rapat Direksi','Presentasi Hasil','Training Internal','Town Hall','One-on-One','Brainstorming','Budgeting','Evaluasi Tahunan','Kickoff','Client Update'];
        for ($i = 0; $i < $remaining; $i++) {
            $st = Carbon::now()->subDays(mt_rand(1,60))->setTime(mt_rand(8,16),mt_rand(0,59));
            $dur = mt_rand(30,120); $et = (clone $st)->addMinutes($dur);
$mt = $this->faker->randomElement(['online','onsite','hybrid']);
            $meeting = Meeting::create(['company_id'=>$this->companyId,'organized_by'=>$this->faker->randomElement($emps),'title'=>$this->faker->randomElement($titles),'description'=>'Agenda: '.$this->faker->sentence,'start_time'=>$st,'end_time'=>$et,'location'=>$mt==='online'?null:$this->faker->randomElement(['Ruang Rapat 1','Ruang Rapat 2']),'meeting_link'=>$mt!=='onsite'?'https://meet.google.com/'.$this->faker->regexify('[a-z]{3}-[a-z]{4}-[a-z]{3}'):null,'meeting_type'=>$mt,'status'=>$st->isPast()?'completed':'scheduled']);
            $ac = mt_rand(3,8);
            $selected = $this->faker->randomElements($emps, min($ac, count($emps)));
            foreach ($selected as $eid) {
                MeetingAttendee::create(['meeting_id'=>$meeting->id,'employee_id'=>$eid,'response'=>$this->faker->randomElement(['accepted','accepted','accepted','tentative','declined']),'attended_at'=>$meeting->status==='completed'?$st:null,'left_at'=>$meeting->status==='completed'?$et:null]);
            }
        }
        $this->command->info("  {$remaining} meetings created.");
    }

    private function seedCourses(): void
    {
        $this->command->info("[Courses] Creating courses...");
        if (Course::count() >= 10) { $this->command->info("  Already enough."); return; }
        $emps = $this->employeeIds;
        $cdata = [
            ['title'=>'Keselamatan Kerja K3','category'=>'Safety','duration'=>120],
            ['title'=>'Perlindungan Data Pribadi','category'=>'Compliance','duration'=>90],
            ['title'=>'Excel Advanced','category'=>'Skill','duration'=>180],
            ['title'=>'Leadership 101','category'=>'Soft Skill','duration'=>60],
            ['title'=>'Komunikasi Efektif','category'=>'Soft Skill','duration'=>45],
            ['title'=>'Python for Data','category'=>'Technical','duration'=>240],
            ['title'=>'Manajemen Waktu','category'=>'Soft Skill','duration'=>30],
            ['title'=>'Customer Service Excellence','category'=>'Service','duration'=>90],
            ['title'=>'Project Management Dasar','category'=>'Management','duration'=>150],
            ['title'=>'Cybersecurity Awareness','category'=>'Technical','duration'=>60],
        ];
        foreach ($cdata as $cd) {
            $course = Course::create(['company_id'=>$this->companyId,'category'=>$cd['category'],'title'=>$cd['title'],'description'=>'Kursus '.$cd['title'].' untuk peningkatan kompetensi','cover_image'=>null,'duration_minutes'=>$cd['duration'],'is_published'=>true,'enrollment_start'=>'2026-01-01','enrollment_end'=>'2026-12-31','created_by'=>$this->faker->randomElement($emps)]);
            $mc = mt_rand(2,3);
            for ($m = 0; $m < $mc; $m++) {
                $module = CourseModule::create(['course_id'=>$course->id,'title'=>'Modul '.($m+1).': '.$this->faker->randomElement(['Pengenalan','Dasar-dasar','Implementasi','Studi Kasus','Evaluasi']),'description'=>'Deskripsi modul '.($m+1),'sort_order'=>$m+1]);
                $lc = mt_rand(2,3);
                for ($l = 0; $l < $lc; $l++) {
                    CourseLesson::create(['module_id'=>$module->id,'title'=>'Lesson '.($m+1).'.'.($l+1).': '.$this->faker->randomElement(['Teori','Praktek','Quiz','Diskusi']),'content_type'=>'text','content'=>$this->faker->paragraphs(2,true),'duration_minutes'=>mt_rand(10,45),'sort_order'=>$l+1,'is_preview'=>false]);
                }
            }
            $enrollCount = mt_rand(5,15);
            $sampleEmps = $this->faker->randomElements($emps, min($enrollCount, count($emps)));
            foreach ($sampleEmps as $eid) {
                CourseEnrollment::create(['course_id'=>$course->id,'employee_id'=>$eid,'enrolled_at'=>now()->subDays(mt_rand(7,60)),'completed_at'=>mt_rand(1,100)<=60?now()->subDays(mt_rand(1,30)):null,'progress_percent'=>mt_rand(0,100),'status'=>mt_rand(1,100)<=60?'completed':'enrolled']);
            }
        }
        $this->command->info("  Courses: ".Course::count().", lessons: ".CourseLesson::count().", enrollments: ".CourseEnrollment::count());
    }


    private function seedNotifications(int $count): void
    {
        $this->command->info("[Notifications] Creating {$count} notifications...");
        $existing = Notification::count();
        $remaining = $count - $existing;
        if ($remaining <= 0) { $this->command->info("  Already enough."); return; }
        $userIds = User::pluck('id')->toArray();
        if (empty($userIds)) return;
        $types = ['info','warning','success','error'];
        $titles = ['Pengajuan Cuti Disetujui','Reimbursement Diproses','Absensi Hari Ini','Reminder Deadline','Jadwal Meeting','Info Payroll','Notifikasi Sistem','Update Status'];
        $notifs = []; $batchSize = 200; $created = 0; $now = now();
        for ($i = 0; $i < $remaining; $i++) {
            $notifs[] = ['user_id'=>$this->faker->randomElement($userIds),'notification_type'=>$this->faker->randomElement($types),'title'=>$this->faker->randomElement($titles),'body'=>$this->faker->sentence,'data'=>'{"link":"/admin"}','channel'=>'in_app','is_read'=>mt_rand(1,100)<=60,'read_at'=>mt_rand(1,100)<=60?$now->subDays(mt_rand(1,30)):null,'sent_at'=>$now->subDays(mt_rand(1,30)),'created_at'=>$now->subDays(mt_rand(1,30))];
            $created++;
            if (count($notifs) >= $batchSize) { Notification::insert($notifs); $notifs = []; }
        }
        if (!empty($notifs)) { Notification::insert($notifs); }
        $this->command->info("  {$created} notifications created.");
    }

    private function printSummary(): void
    {
        $this->command->info('');
        $this->command->info('===========================================');
        $this->command->info('    BIZOS DEMO DATA SEEDER - SUMMARY');
        $this->command->info('===========================================');
        $this->command->info('Employees:         ' . Employee::count());
        $this->command->info('Users:             ' . User::count());
        $this->command->info('Attendances:       ' . Attendance::count());
        $this->command->info('Leaves:            ' . Leave::count());
        $this->command->info('Overtimes:         ' . Overtime::count());
        $this->command->info('Reimbursements:    ' . Reimbursement::count());
        $this->command->info('Visits:            ' . Visit::count());
        $this->command->info('Job Postings:      ' . JobPosting::count());
        $this->command->info('Candidates:        ' . Candidate::count());
        $this->command->info('Interviews:        ' . Interview::count());
        $this->command->info('Feedback Cycles:   ' . FeedbackCycle::count());
        $this->command->info('Feedback Answers:  ' . FeedbackAnswer::count());
        $this->command->info('Canteen Menus:     ' . CanteenMenu::count());
        $this->command->info('Canteen Orders:    ' . CanteenOrder::count());
        $this->command->info('Announcements:     ' . Announcement::count());
        $this->command->info('Payroll Periods:   ' . PayrollPeriod::count());
        $this->command->info('Payroll Records:   ' . Payroll::count());
        $this->command->info('COA Accounts:      ' . \App\Models\Coa::count());
        $this->command->info('Journals:          ' . Journal::count());
        $this->command->info('Journal Entries:   ' . JournalEntry::count());
        $this->command->info('Invoices:          ' . Invoice::count());
        $this->command->info('Invoice Items:     ' . InvoiceItem::count());
        $this->command->info('Payments:          ' . Payment::count());
        $this->command->info('Budgets:           ' . Budget::count());
        $this->command->info('Budget Items:      ' . BudgetItem::count());
        $this->command->info('Assets:            ' . Asset::count());
        $this->command->info('Clients:           ' . Client::count());
        $this->command->info('Leads:             ' . Lead::count());
        $this->command->info('Deals:             ' . Deal::count());
        $this->command->info('WA Templates:      ' . WaTemplate::count());
        $this->command->info('WA Campaigns:      ' . WaBlastCampaign::count());
        $this->command->info('WA Blast Logs:     ' . WaBlastLog::count());
        $this->command->info('Projects:          ' . Project::count());
        $this->command->info('Tasks:             ' . Task::count());
        $this->command->info('Products:          ' . Product::count());
        $this->command->info('POS Transactions:  ' . PosTransaction::count());
        $this->command->info('Meetings:          ' . Meeting::count());
        $this->command->info('Courses:           ' . Course::count());
        $this->command->info('Course Lessons:    ' . CourseLesson::count());
        $this->command->info('Notifications:     ' . Notification::count());
        $this->command->info('');
        $totalCounts = [
            Employee::count(), User::count(), Attendance::count(), Leave::count(),
            Overtime::count(), Reimbursement::count(), Visit::count(), JobPosting::count(),
            Candidate::count(), Interview::count(), FeedbackAnswer::count(), CanteenOrder::count(),
            CanteenOrderItem::count(), Announcement::count(), Payroll::count(), PayrollItem::count(),
            \App\Models\Coa::count(), Journal::count(), JournalEntry::count(), Invoice::count(),
            InvoiceItem::count(), Payment::count(), BudgetItem::count(), Asset::count(),
            Client::count(), Lead::count(), Deal::count(), WaTemplate::count(),
            WaBlastCampaign::count(), WaBlastLog::count(), Project::count(), Task::count(),
            Product::count(), PosTransaction::count(), PosTransactionItem::count(),
            Meeting::count(), MeetingAttendee::count(), Course::count(), CourseLesson::count(),
            CourseEnrollment::count(), Notification::count(),
        ];
        $grandTotal = array_sum($totalCounts);
        $this->command->info('GRAND TOTAL RECORDS: ~' . number_format($grandTotal));
        $this->command->info('===========================================');
        $this->command->info('');
        $this->command->info('Demo accounts preserved: admin@bizos.id, owner@bizos.id, etc.');
        $this->command->info('Employee logins: firstname@maju.test / password');
    }
}
