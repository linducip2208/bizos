<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\JournalEntry;
use App\Models\Overtime;
use App\Models\Payment;
use App\Models\Payroll;
use App\Models\PurchaseOrder;
use App\Models\GoodsReceipt;
use App\Models\StockMovement;
use App\Models\StockBalance;
use App\Models\StockOpname;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Number;

class AnomalyDetectionService
{
    protected array $anomalies = [];

    public function scanAll(int $companyId): array
    {
        $this->anomalies = [];

        $this->scanPayroll($companyId);
        $this->scanFinance($companyId);
        $this->scanInventory($companyId);
        $this->scanAttendance($companyId);

        return $this->anomalies;
    }

    public function scanPayroll(int $companyId): array
    {
        $payrollAnomalies = [];
        $now = now();

        $doubledSalaries = Payroll::where('company_id', $companyId)
            ->whereMonth('period_start', $now->month)
            ->whereYear('period_start', $now->year)
            ->select('employee_id', DB::raw('COUNT(*) as payroll_count'), DB::raw('SUM(net_salary) as total_net'))
            ->groupBy('employee_id')
            ->having('payroll_count', '>', 1)
            ->get();

        foreach ($doubledSalaries as $row) {
            $employee = Employee::find($row->employee_id);
            if ($employee) {
                $payrollAnomalies[] = [
                    'module' => 'Payroll',
                    'type' => 'gaji_ganda',
                    'severity' => 'high',
                    'title' => 'Gaji Ganda Terdeteksi',
                    'description' => "Karyawan {$employee->first_name} {$employee->last_name} menerima gaji {$row->payroll_count}x bulan ini (total Rp " . Number::format($row->total_net, 0, ',', '.') . ").",
                    'employee_id' => $row->employee_id,
                    'employee_name' => "{$employee->first_name} {$employee->last_name}",
                    'link' => "/admin/employees/{$row->employee_id}/edit",
                    'detected_at' => $now->toDateTimeString(),
                ];
            }
        }

        $zeroAttendanceFullPay = Payroll::where('company_id', $companyId)
            ->whereMonth('period_start', $now->month)
            ->whereYear('period_start', $now->year)
            ->where('net_salary', '>', 0)
            ->whereDoesntHave('employee.attendances', function ($q) use ($now) {
                $q->whereMonth('date', $now->month)->whereYear('date', $now->year);
            })
            ->with('employee')
            ->limit(20)
            ->get();

        foreach ($zeroAttendanceFullPay as $payroll) {
            if ($payroll->employee) {
                $payrollAnomalies[] = [
                    'module' => 'Payroll',
                    'type' => 'gaji_tanpa_absensi',
                    'severity' => 'high',
                    'title' => 'Gaji Tanpa Absensi',
                    'description' => "Karyawan {$payroll->employee->first_name} {$payroll->employee->last_name} menerima gaji Rp " . Number::format($payroll->net_salary, 0, ',', '.') . " tanpa ada catatan absensi bulan ini.",
                    'employee_id' => $payroll->employee_id,
                    'employee_name' => "{$payroll->employee->first_name} {$payroll->employee->last_name}",
                    'link' => "/admin/employees/{$payroll->employee_id}/edit",
                    'detected_at' => $now->toDateTimeString(),
                ];
            }
        }

        $newEmployeeHighSalary = Employee::where('company_id', $companyId)
            ->where('join_date', '>=', $now->copy()->subMonths(2))
            ->where('basic_salary', '>', DB::raw('(SELECT AVG(basic_salary) * 2 FROM employees WHERE company_id = ' . (int)$companyId . ' AND join_date < ' . $now->copy()->subMonths(2)->format('Y-m-d') . ')'))
            ->get();

        foreach ($newEmployeeHighSalary as $employee) {
            $payrollAnomalies[] = [
                'module' => 'Payroll',
                'type' => 'karyawan_baru_gaji_tinggi',
                'severity' => 'medium',
                'title' => 'Karyawan Baru Gaji Tidak Wajar',
                'description' => "{$employee->first_name} {$employee->last_name} (bergabung {$employee->join_date->format('d M Y')}) memiliki gaji pokok Rp " . Number::format($employee->basic_salary, 0, ',', '.') . " (>2x rata-rata perusahaan).",
                'employee_id' => $employee->id,
                'employee_name' => "{$employee->first_name} {$employee->last_name}",
                'link' => "/admin/employees/{$employee->id}/edit",
                'detected_at' => $now->toDateTimeString(),
            ];
        }

        $overtimeSpikes = Overtime::where('company_id', $companyId)
            ->whereMonth('overtime_date', $now->month)
            ->select('employee_id', DB::raw('SUM(total_hours) as total_hours'))
            ->groupBy('employee_id')
            ->having('total_hours', '>', 60)
            ->with('employee')
            ->get();

        foreach ($overtimeSpikes as $row) {
            if ($row->employee) {
                $payrollAnomalies[] = [
                    'module' => 'Payroll',
                    'type' => 'lembur_berlebihan',
                    'severity' => 'medium',
                    'title' => 'Lembur Berlebihan',
                    'description' => "{$row->employee->first_name} {$row->employee->last_name} memiliki {$row->total_hours} jam lembur bulan ini (>60 jam).",
                    'employee_id' => $row->employee_id,
                    'employee_name' => "{$row->employee->first_name} {$row->employee->last_name}",
                    'link' => "/admin/overtimes?tableFilters[employee_id][value]={$row->employee_id}",
                    'detected_at' => $now->toDateTimeString(),
                ];
            }
        }

        return $payrollAnomalies;
    }

    public function scanFinance(int $companyId): array
    {
        $financeAnomalies = [];
        $now = now();

        $duplicatePayments = Payment::where('company_id', $companyId)
            ->whereMonth('payment_date', $now->month)
            ->select('reference_number', DB::raw('COUNT(*) as cnt'))
            ->groupBy('reference_number')
            ->having('cnt', '>', 1)
            ->whereNotNull('reference_number')
            ->get();

        foreach ($duplicatePayments as $dup) {
            $payments = Payment::where('reference_number', $dup->reference_number)->get();
            $total = $payments->sum('amount');
            $financeAnomalies[] = [
                'module' => 'Finance',
                'type' => 'pembayaran_ganda',
                'severity' => 'high',
                'title' => 'Pembayaran Ganda Terdeteksi',
                'description' => "Nomor referensi {$dup->reference_number} muncul {$dup->cnt}x dengan total Rp " . Number::format($total, 0, ',', '.') . ".",
                'link' => "/admin/payments",
                'detected_at' => $now->toDateTimeString(),
            ];
        }

        $largeExpenses = JournalEntry::where('company_id', $companyId)
            ->where('entry_type', 'credit')
            ->whereMonth('journal_date', $now->month)
            ->where('amount', '>', DB::raw('(SELECT COALESCE(AVG(amount) * 5, 10000000) FROM journal_entries WHERE company_id = ' . (int)$companyId . ' AND entry_type = \'credit\')'))
            ->with(['journal', 'coa'])
            ->limit(10)
            ->get();

        foreach ($largeExpenses as $entry) {
            $financeAnomalies[] = [
                'module' => 'Finance',
                'type' => 'pengeluaran_besar',
                'severity' => 'medium',
                'title' => 'Pengeluaran Tidak Biasa',
                'description' => "Pengeluaran Rp " . Number::format($entry->amount, 0, ',', '.') . " pada akun {$entry->coa?->name} tanggal {$entry->journal_date?->format('d M Y')}.",
                'link' => "/admin/journals/{$entry->journal_id}/edit",
                'detected_at' => $now->toDateTimeString(),
            ];
        }

        $roundNumberTransactions = JournalEntry::where('company_id', $companyId)
            ->whereMonth('journal_date', $now->month)
            ->where('amount', '>', 5000000)
            ->where(function ($q) {
                $q->whereRaw('amount % 1000000 = 0')
                    ->orWhereRaw('amount % 5000000 = 0');
            })
            ->with(['coa'])
            ->limit(10)
            ->get();

        foreach ($roundNumberTransactions as $entry) {
            $financeAnomalies[] = [
                'module' => 'Finance',
                'type' => 'transaksi_angka_bulat',
                'severity' => 'low',
                'title' => 'Transaksi Angka Bulat',
                'description' => "Transaksi Rp " . Number::format($entry->amount, 0, ',', '.') . " bernilai bulat mencurigakan pada {$entry->journal_date?->format('d M Y')}.",
                'link' => "/admin/journals/{$entry->journal_id}/edit",
                'detected_at' => $now->toDateTimeString(),
            ];
        }

        $afterHoursEntries = JournalEntry::where('company_id', $companyId)
            ->whereMonth('journal_date', $now->month)
            ->where(function ($q) {
                $q->whereTime('created_at', '>=', '22:00:00')
                    ->orWhereTime('created_at', '<', '05:00:00');
            })
            ->count();

        if ($afterHoursEntries > 0) {
            $financeAnomalies[] = [
                'module' => 'Finance',
                'type' => 'jurnal_diluar_jam_kerja',
                'severity' => 'low',
                'title' => 'Jurnal di Luar Jam Kerja',
                'description' => "Terdeteksi {$afterHoursEntries} entri jurnal dibuat di luar jam kerja (22:00-05:00) bulan ini.",
                'link' => "/admin/journals",
                'detected_at' => $now->toDateTimeString(),
            ];
        }

        return $financeAnomalies;
    }

    public function scanInventory(int $companyId): array
    {
        $inventoryAnomalies = [];
        $now = now();

        $negativeStock = StockBalance::where('company_id', $companyId)
            ->where('quantity_on_hand', '<', 0)
            ->with('product')
            ->limit(20)
            ->get();

        foreach ($negativeStock as $stock) {
            $productName = $stock->product?->name ?? "Produk #{$stock->product_id}";
            $inventoryAnomalies[] = [
                'module' => 'Inventory',
                'type' => 'stok_negatif',
                'severity' => 'high',
                'title' => 'Stok Negatif',
                'description' => "{$productName} memiliki stok {$stock->quantity_on_hand} unit (negatif). Periksa segera.",
                'link' => "/admin/products/{$stock->product_id}/edit",
                'detected_at' => $now->toDateTimeString(),
            ];
        }

        $suddenDrops = StockMovement::where('company_id', $companyId)
            ->where('movement_type', 'out')
            ->whereMonth('movement_date', $now->month)
            ->where('quantity', '>', DB::raw('(SELECT COALESCE(AVG(sm2.quantity) * 3, 0) FROM stock_movements sm2 WHERE sm2.product_id = stock_movements.product_id AND sm2.movement_type = \'out\' AND sm2.movement_date < ' . $now->copy()->startOfMonth()->format('Y-m-d') . ')'))
            ->with('product')
            ->limit(10)
            ->get();

        foreach ($suddenDrops as $movement) {
            $productName = $movement->product?->name ?? "Produk #{$movement->product_id}";
            $inventoryAnomalies[] = [
                'module' => 'Inventory',
                'type' => 'stok_turun_drastis',
                'severity' => 'high',
                'title' => 'Penurunan Stok Drastis',
                'description' => "{$productName} keluar {$movement->quantity} unit pada {$movement->movement_date?->format('d M Y')} (>3x rata-rata keluar).",
                'link' => "/admin/products/{$movement->product_id}/edit",
                'detected_at' => $now->toDateTimeString(),
            ];
        }

        $stagnantStock = StockMovement::where('company_id', $companyId)
            ->whereIn('product_id', function ($q) use ($companyId, $now) {
                $q->select('product_id')
                    ->from('stock_movements')
                    ->where('company_id', $companyId)
                    ->groupBy('product_id')
                    ->having(DB::raw('MAX(movement_date)'), '<', $now->copy()->subDays(90)->format('Y-m-d'));
            })
            ->select('product_id', DB::raw('MAX(movement_date) as last_movement'))
            ->groupBy('product_id')
            ->with('product')
            ->limit(10)
            ->get();

        foreach ($stagnantStock as $item) {
            $productName = $item->product?->name ?? "Produk #{$item->product_id}";
            $days = $now->diffInDays(Carbon::parse($item->last_movement));
            $inventoryAnomalies[] = [
                'module' => 'Inventory',
                'type' => 'stok_tidak_bergerak',
                'severity' => 'medium',
                'title' => 'Stok Tidak Bergerak >90 Hari',
                'description' => "{$productName} tidak ada pergerakan selama {$days} hari (terakhir: {$item->last_movement}).",
                'link' => "/admin/products/{$item->product_id}/edit",
                'detected_at' => $now->toDateTimeString(),
            ];
        }

        $grnWithoutPo = GoodsReceipt::where('company_id', $companyId)
            ->whereNull('purchase_order_id')
            ->whereMonth('received_date', $now->month)
            ->count();

        if ($grnWithoutPo > 0) {
            $inventoryAnomalies[] = [
                'module' => 'Inventory',
                'type' => 'grn_tanpa_po',
                'severity' => 'medium',
                'title' => 'Penerimaan Barang Tanpa PO',
                'description' => "Terdeteksi {$grnWithoutPo} penerimaan barang tanpa Purchase Order bulan ini.",
                'link' => "/admin/goods-receipts",
                'detected_at' => $now->toDateTimeString(),
            ];
        }

        $frequentAdjusters = StockOpname::where('company_id', $companyId)
            ->whereMonth('opname_date', $now->month)
            ->select('created_by', DB::raw('COUNT(*) as adj_count'))
            ->groupBy('created_by')
            ->having('adj_count', '>', 10)
            ->with('creator')
            ->get();

        foreach ($frequentAdjusters as $row) {
            $name = $row->creator?->first_name ?? "User #{$row->created_by}";
            $inventoryAnomalies[] = [
                'module' => 'Inventory',
                'type' => 'sering_stock_opname',
                'severity' => 'low',
                'title' => 'Frekuensi Stock Opname Tinggi',
                'description' => "{$name} melakukan {$row->adj_count}x stock opname bulan ini. Periksa apakah wajar.",
                'link' => "/admin/stock-opnames",
                'detected_at' => $now->toDateTimeString(),
            ];
        }

        return $inventoryAnomalies;
    }

    public function scanAttendance(int $companyId): array
    {
        $attendanceAnomalies = [];
        $now = now();

        $alwaysLateMonday = DB::table('attendances')
            ->where('company_id', $companyId)
            ->whereMonth('date', $now->month)
            ->whereYear('date', $now->year)
            ->whereRaw('DAYOFWEEK(date) = 2')
            ->where('status', 'late')
            ->select('employee_id', DB::raw('COUNT(*) as late_mondays'))
            ->groupBy('employee_id')
            ->having('late_mondays', '=', DB::raw('(SELECT COUNT(*) FROM (SELECT DISTINCT DATE(date) as d FROM attendances WHERE company_id = ' . (int)$companyId . ' AND MONTH(date) = ' . $now->month . ' AND YEAR(date) = ' . $now->year . ' AND DAYOFWEEK(date) = 2) AS mondays)'))
            ->limit(10)
            ->get();

        foreach ($alwaysLateMonday as $row) {
            $employee = Employee::find($row->employee_id);
            if ($employee) {
                $attendanceAnomalies[] = [
                    'module' => 'Attendance',
                    'type' => 'selalu_telat_senin',
                    'severity' => 'medium',
                    'title' => 'Selalu Terlambat Hari Senin',
                    'description' => "{$employee->first_name} {$employee->last_name} terlambat SETIAP hari Senin bulan ini ({$row->late_mondays}x).",
                    'employee_id' => $row->employee_id,
                    'employee_name' => "{$employee->first_name} {$employee->last_name}",
                    'link' => "/admin/employees/{$row->employee_id}/edit",
                    'detected_at' => $now->toDateTimeString(),
                ];
            }
        }

        $rapidClockInOut = DB::table('attendances')
            ->where('company_id', $companyId)
            ->whereMonth('date', $now->month)
            ->whereNotNull('clock_in')
            ->whereNotNull('clock_out')
            ->whereRaw('TIMESTAMPDIFF(MINUTE, clock_in, clock_out) < 5')
            ->join('employees', 'employees.id', '=', 'attendances.employee_id')
            ->select('attendances.*', 'employees.first_name', 'employees.last_name')
            ->limit(10)
            ->get();

        foreach ($rapidClockInOut as $attendance) {
            $attendanceAnomalies[] = [
                'module' => 'Attendance',
                'type' => 'clock_in_out_cepat',
                'severity' => 'medium',
                'title' => 'Clock-In / Clock-Out Terlalu Cepat',
                'description' => "{$attendance->first_name} {$attendance->last_name} clock-in dan clock-out dalam <5 menit pada {$attendance->date}.",
                'employee_id' => $attendance->employee_id,
                'employee_name' => "{$attendance->first_name} {$attendance->last_name}",
                'link' => "/admin/attendances",
                'detected_at' => $now->toDateTimeString(),
            ];
        }

        $sameDeviceMultipleEmployees = DB::table('attendance_logs')
            ->where('company_id', $companyId)
            ->whereMonth('created_at', $now->month)
            ->whereNotNull('device_id')
            ->select('device_id', DB::raw('COUNT(DISTINCT employee_id) as employee_count'))
            ->groupBy('device_id')
            ->having('employee_count', '>', 2)
            ->limit(10)
            ->get();

        foreach ($sameDeviceMultipleEmployees as $row) {
            $attendanceAnomalies[] = [
                'module' => 'Attendance',
                'type' => 'satu_device_banyak_karyawan',
                'severity' => 'medium',
                'title' => 'Satu Device Digunakan Banyak Karyawan',
                'description' => "Device {$row->device_id} digunakan oleh {$row->employee_count} karyawan berbeda bulan ini. Kemungkinan titip absen.",
                'link' => "/admin/attendance-logs",
                'detected_at' => $now->toDateTimeString(),
            ];
        }

        $weekendClockInWithoutOvertime = DB::table('attendances')
            ->where('company_id', $companyId)
            ->whereMonth('date', $now->month)
            ->where(function ($q) {
                $q->whereRaw('DAYOFWEEK(date) = 1')
                    ->orWhereRaw('DAYOFWEEK(date) = 7');
            })
            ->whereNotNull('clock_in')
            ->whereNotExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('overtimes')
                    ->whereColumn('overtimes.employee_id', 'attendances.employee_id')
                    ->whereColumn('overtimes.overtime_date', 'attendances.date');
            })
            ->join('employees', 'employees.id', '=', 'attendances.employee_id')
            ->select('attendances.*', 'employees.first_name', 'employees.last_name')
            ->limit(10)
            ->get();

        foreach ($weekendClockInWithoutOvertime as $attendance) {
            $attendanceAnomalies[] = [
                'module' => 'Attendance',
                'type' => 'absen_weekend_tanpa_lembur',
                'severity' => 'low',
                'title' => 'Absen Weekend Tanpa Lembur',
                'description' => "{$attendance->first_name} {$attendance->last_name} clock-in di hari libur ({$attendance->date}) tanpa pengajuan lembur.",
                'employee_id' => $attendance->employee_id,
                'employee_name' => "{$attendance->first_name} {$attendance->last_name}",
                'link' => "/admin/attendances",
                'detected_at' => $now->toDateTimeString(),
            ];
        }

        return $attendanceAnomalies;
    }

    public function generateWeeklyReport(int $companyId): string
    {
        $anomalies = $this->scanAll($companyId);
        $company = \App\Models\Company::find($companyId);
        $companyName = $company?->name ?? 'Perusahaan';

        $totalAnomalies = count($anomalies);
        $highCount = count(array_filter($anomalies, fn($a) => $a['severity'] === 'high'));
        $mediumCount = count(array_filter($anomalies, fn($a) => $a['severity'] === 'medium'));
        $lowCount = count(array_filter($anomalies, fn($a) => $a['severity'] === 'low'));

        $byModule = [];
        foreach ($anomalies as $a) {
            $byModule[$a['module']][] = $a;
        }

        $report = "*LAPORAN ANOMALI MINGGUAN - {$companyName}*\n";
        $report .= "Periode: " . now()->startOfWeek()->format('d M') . ' - ' . now()->format('d M Y') . "\n\n";
        $report .= "Total Anomali: *{$totalAnomalies}*\n";
        $report .= "🔴 High: {$highCount} | 🟡 Medium: {$mediumCount} | 🔵 Low: {$lowCount}\n\n";

        foreach ($byModule as $module => $items) {
            $report .= "*{$module}* ({$this->countAnomaliesByModule($items)})\n";
            foreach (array_slice($items, 0, 5) as $item) {
                $severityIcon = match ($item['severity']) {
                    'high' => '🔴',
                    'medium' => '🟡',
                    'low' => '🔵',
                    default => '⚪',
                };
                $report .= "  {$severityIcon} {$item['title']}\n";
                $report .= "    {$item['description']}\n";
            }
            if (count($items) > 5) {
                $report .= "  ... dan " . (count($items) - 5) . " lainnya\n";
            }
            $report .= "\n";
        }

        if ($totalAnomalies === 0) {
            $report .= "Tidak ada anomali terdeteksi minggu ini. Semua sistem berjalan normal.\n";
        }

        $report .= "_Dikirim otomatis oleh BizOS Anomaly Detection System_";

        return $report;
    }

    public function countAnomaliesByModule(array $anomalies): int
    {
        return count($anomalies);
    }

    public function getAnomalyTrend(int $companyId, int $days = 30): array
    {
        $data = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $data[] = [
                'date' => $date,
                'count' => 0,
            ];
        }
        return $data;
    }
}
