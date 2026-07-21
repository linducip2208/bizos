<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\OfflineAction;
use App\Models\Product;
use App\Models\StockOpnameItem;
use App\Models\Task;
use App\Models\Visit;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OfflineService
{
    public function queueAction(string $action, array $data, int $userId): OfflineAction
    {
        $validActions = [
            'clock_in', 'clock_out', 'visit_checkin', 'visit_checkout',
            'task_update', 'task_create', 'stock_opname_scan',
            'goods_receipt_scan', 'delivery_confirmation',
        ];

        if (!in_array($action, $validActions)) {
            throw new \InvalidArgumentException("Action type '{$action}' tidak valid.");
        }

        return OfflineAction::create([
            'user_id' => $userId,
            'action_type' => $action,
            'action_data' => $data,
            'status' => 'pending',
        ]);
    }

    public function syncAll(int $userId): array
    {
        $actions = OfflineAction::where('user_id', $userId)
            ->pending()
            ->orderBy('created_at')
            ->get();

        $syncedCount = 0;
        $failedCount = 0;
        $errors = [];

        foreach ($actions as $action) {
            try {
                $this->processAction($action);
                $action->markSynced(['processed_at' => now()->toIso8601String()]);
                $syncedCount++;
            } catch (\Exception $e) {
                $action->markFailed($e->getMessage());
                $failedCount++;
                $errors[] = [
                    'action_id' => $action->id,
                    'action_type' => $action->action_type,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'synced_count' => $syncedCount,
            'failed_count' => $failedCount,
            'errors' => $errors,
        ];
    }

    protected function processAction(OfflineAction $action): void
    {
        $data = $action->action_data;
        $userId = $action->user_id;

        switch ($action->action_type) {
            case 'clock_in':
                $this->processClockIn($userId, $data);
                break;
            case 'clock_out':
                $this->processClockOut($userId, $data);
                break;
            case 'visit_checkin':
                $this->processVisitCheckin($userId, $data);
                break;
            case 'visit_checkout':
                $this->processVisitCheckout($userId, $data);
                break;
            case 'task_update':
                $this->processTaskUpdate($userId, $data);
                break;
            case 'task_create':
                $this->processTaskCreate($userId, $data);
                break;
            case 'stock_opname_scan':
                $this->processOpnameScan($userId, $data);
                break;
            case 'goods_receipt_scan':
                $this->processGoodsReceiptScan($userId, $data);
                break;
            case 'delivery_confirmation':
                $this->processDeliveryConfirmation($userId, $data);
                break;
            default:
                throw new \Exception("Action type '{$action->action_type}' belum diimplementasikan.");
        }
    }

    protected function processClockIn(int $userId, array $data): void
    {
        $employee = Employee::whereHas('user', fn($q) => $q->where('id', $userId))->first();
        if (!$employee) {
            throw new \Exception('Data karyawan tidak ditemukan.');
        }

        $today = Carbon::today()->toDateString();
        $existing = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->whereNotNull('clock_in')
            ->first();

        if ($existing) {
            throw new \Exception('Sudah clock-in hari ini. Konflik terdeteksi.');
        }

        $now = Carbon::now();
        if (isset($data['timestamp'])) {
            $now = Carbon::parse($data['timestamp']);
        }

        Attendance::create([
            'employee_id' => $employee->id,
            'date' => $today,
            'clock_in' => $now,
            'clock_in_lat' => $data['latitude'] ?? null,
            'clock_in_lng' => $data['longitude'] ?? null,
            'clock_in_wifi_bssid' => $data['wifi_bssid'] ?? null,
            'status' => ($data['late_minutes'] ?? 0) > 0 ? 'late' : 'present',
            'late_minutes' => $data['late_minutes'] ?? 0,
            'work_type' => $data['work_type'] ?? 'office',
            'notes' => $data['notes'] ?? null,
        ]);
    }

    protected function processClockOut(int $userId, array $data): void
    {
        $employee = Employee::whereHas('user', fn($q) => $q->where('id', $userId))->first();
        if (!$employee) {
            throw new \Exception('Data karyawan tidak ditemukan.');
        }

        $today = Carbon::today()->toDateString();
        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('date', $today)
            ->whereNotNull('clock_in')
            ->first();

        if (!$attendance) {
            throw new \Exception('Belum clock-in hari ini.');
        }

        if ($attendance->clock_out) {
            throw new \Exception('Sudah clock-out hari ini. Konflik terdeteksi.');
        }

        $now = Carbon::now();
        if (isset($data['timestamp'])) {
            $now = Carbon::parse($data['timestamp']);
        }

        $attendance->update([
            'clock_out' => $now,
            'clock_out_lat' => $data['latitude'] ?? null,
            'clock_out_lng' => $data['longitude'] ?? null,
            'clock_out_wifi_bssid' => $data['wifi_bssid'] ?? null,
            'overtime_minutes' => $data['overtime_minutes'] ?? 0,
            'notes' => $data['notes'] ?? $attendance->notes,
        ]);
    }

    protected function processVisitCheckin(int $userId, array $data): void
    {
        $employee = Employee::whereHas('user', fn($q) => $q->where('id', $userId))->first();
        if (!$employee) {
            throw new \Exception('Data karyawan tidak ditemukan.');
        }

        $now = Carbon::now();
        if (isset($data['timestamp'])) {
            $now = Carbon::parse($data['timestamp']);
        }

        Visit::create([
            'employee_id' => $employee->id,
            'date' => $now->toDateString(),
            'visit_type' => $data['visit_type'] ?? 'sales',
            'location' => $data['location'] ?? null,
            'purpose' => $data['purpose'] ?? null,
            'start_time' => $now,
            'check_in_lat' => $data['latitude'] ?? null,
            'check_in_lng' => $data['longitude'] ?? null,
            'status' => 'in_progress',
        ]);
    }

    protected function processVisitCheckout(int $userId, array $data): void
    {
        $employee = Employee::whereHas('user', fn($q) => $q->where('id', $userId))->first();
        if (!$employee) {
            throw new \Exception('Data karyawan tidak ditemukan.');
        }

        $visit = Visit::where('employee_id', $employee->id)
            ->where('status', 'in_progress')
            ->whereNull('end_time')
            ->latest('start_time')
            ->first();

        if (!$visit) {
            throw new \Exception('Tidak ada kunjungan aktif.');
        }

        $now = Carbon::now();
        if (isset($data['timestamp'])) {
            $now = Carbon::parse($data['timestamp']);
        }

        $visit->update([
            'end_time' => $now,
            'check_out_lat' => $data['latitude'] ?? null,
            'check_out_lng' => $data['longitude'] ?? null,
            'status' => $data['status'] ?? 'completed',
            'report' => $data['report'] ?? null,
        ]);
    }

    protected function processTaskUpdate(int $userId, array $data): void
    {
        $task = Task::find($data['task_id']);
        if (!$task) {
            throw new \Exception('Tugas tidak ditemukan.');
        }

        $updates = [];
        if (isset($data['status'])) {
            $updates['status'] = $data['status'];
            if ($data['status'] === 'completed') {
                $updates['completed_at'] = Carbon::now();
            }
        }
        if (isset($data['actual_hours'])) {
            $updates['actual_hours'] = $data['actual_hours'];
        }
        if (isset($data['notes'])) {
            $task->activities()->create([
                'user_id' => $userId,
                'description' => $data['notes'],
                'type' => 'note',
            ]);
        }

        if (!empty($updates)) {
            $task->update($updates);
        }
    }

    protected function processTaskCreate(int $userId, array $data): void
    {
        $employee = Employee::whereHas('user', fn($q) => $q->where('id', $userId))->first();

        Task::create([
            'project_id' => $data['project_id'] ?? null,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 'todo',
            'priority' => $data['priority'] ?? 'normal',
            'start_date' => $data['start_date'] ?? now()->toDateString(),
            'due_date' => $data['due_date'] ?? null,
            'created_by' => $employee?->id,
        ]);
    }

    protected function processOpnameScan(int $userId, array $data): void
    {
        $opnameItem = StockOpnameItem::where('stock_opname_id', $data['stock_opname_id'])
            ->where('product_id', $data['product_id'])
            ->first();

        if (!$opnameItem) {
            throw new \Exception('Item stock opname tidak ditemukan.');
        }

        $physicalQty = (float) $data['physical_quantity'];
        $difference = $physicalQty - $opnameItem->system_quantity;

        $opnameItem->update([
            'physical_quantity' => $physicalQty,
            'difference' => $difference,
            'notes' => isset($data['notes']) ? trim($opnameItem->notes . "\n" . $data['notes']) : $opnameItem->notes,
        ]);
    }

    protected function processGoodsReceiptScan(int $userId, array $data): void
    {
        $grItem = \App\Models\GoodsReceiptItem::find($data['gr_item_id']);
        if (!$grItem) {
            throw new \Exception('Item penerimaan tidak ditemukan.');
        }

        $grItem->update([
            'quantity_received' => ($grItem->quantity_received ?? 0) + (float) ($data['quantity'] ?? 1),
            'notes' => isset($data['notes']) ? trim(($grItem->notes ?? '') . "\n" . $data['notes']) : $grItem->notes,
        ]);
    }

    protected function processDeliveryConfirmation(int $userId, array $data): void
    {
        $delivery = \App\Models\DeliveryOrder::find($data['delivery_order_id']);
        if (!$delivery) {
            throw new \Exception('Delivery order tidak ditemukan.');
        }

        $delivery->update([
            'status' => $data['status'] ?? 'delivered',
            'actual_arrival' => Carbon::now(),
            'receiver_name' => $data['receiver_name'] ?? $delivery->receiver_name,
            'gps_lat' => $data['latitude'] ?? $delivery->gps_lat,
            'gps_lng' => $data['longitude'] ?? $delivery->gps_lng,
            'notes' => isset($data['notes']) ? trim(($delivery->notes ?? '') . "\n" . $data['notes']) : $delivery->notes,
        ]);
    }

    public function needsRefresh(int $userId, string $dataType, string $lastSyncAt): bool
    {
        $lastSync = Carbon::parse($lastSyncAt);
        $user = \App\Models\User::find($userId);

        if (!$user) {
            return true;
        }

        $tables = [
            'employees' => \App\Models\Employee::class,
            'products' => Product::class,
            'warehouses' => Warehouse::class,
            'tasks' => Task::class,
            'shifts' => \App\Models\Shift::class,
            'settings' => \App\Models\SystemSetting::class,
        ];

        if (!isset($tables[$dataType])) {
            return true;
        }

        return $tables[$dataType]::where('updated_at', '>', $lastSync)->exists();
    }

    public function getOfflineData(int $userId): array
    {
        $user = \App\Models\User::with(['employee.branch', 'employee.department'])->find($userId);

        if (!$user) {
            return [];
        }

        $companyId = $user->company_id;
        $branchId = $user->employee?->branch_id;
        $employeeId = $user->employee?->id;

        $employees = Employee::query()
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->select(['id', 'employee_code', 'first_name', 'last_name', 'department_id', 'branch_id', 'photo'])
            ->get();

        $products = Product::query()
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->where('is_active', true)
            ->with(['variants' => fn($q) => $q->select('id', 'product_id', 'name', 'sku', 'stock')])
            ->get()
            ->makeHidden(['purchase_price', 'created_at', 'updated_at', 'deleted_at']);

        $warehouses = Warehouse::query()
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->where('is_active', true)
            ->select(['id', 'code', 'name', 'branch_id'])
            ->get();

        $tasks = Task::query()
            ->when($employeeId, fn($q) => $q->whereHas('assignees', fn($sub) => $sub->where('employee_id', $employeeId)))
            ->whereIn('status', ['todo', 'in_progress'])
            ->with(['project:id,name'])
            ->select(['id', 'project_id', 'title', 'status', 'priority', 'start_date', 'due_date'])
            ->limit(100)
            ->get();

        $shifts = \App\Models\Shift::query()
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->where('is_active', true)
            ->select(['id', 'name', 'start_time', 'end_time'])
            ->get();

        $settings = \App\Models\SystemSetting::query()
            ->when($companyId, fn($q) => $q->where('company_id', $companyId))
            ->whereIn('key', ['company_name', 'company_address', 'company_phone', 'attendance_methods', 'gps_radius_meters'])
            ->get()
            ->pluck('value', 'key');

        return [
            'employees' => $employees,
            'products' => $products,
            'warehouses' => $warehouses,
            'tasks' => $tasks,
            'shifts' => $shifts,
            'settings' => $settings,
            'generated_at' => now()->toIso8601String(),
            'total_size_items' => $employees->count() + $products->count() + $warehouses->count() + $tasks->count(),
        ];
    }

    public function resolveConflict(OfflineAction $action, string $resolution): void
    {
        if ($resolution === 'use_local') {
            $this->processAction($action);
            $action->markSynced(['resolution' => 'use_local', 'processed_at' => now()->toIso8601String()]);
        } elseif ($resolution === 'use_server') {
            $action->update([
                'status' => 'synced',
                'server_response' => ['resolution' => 'use_server', 'action' => 'discarded'],
                'synced_at' => now(),
            ]);
        } elseif ($resolution === 'merge') {
            $this->processAction($action);
            $action->markSynced(['resolution' => 'merge', 'processed_at' => now()->toIso8601String()]);
        } else {
            throw new \InvalidArgumentException("Resolution '{$resolution}' tidak valid. Gunakan: use_local, use_server, merge.");
        }
    }
}
