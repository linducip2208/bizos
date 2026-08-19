<?php

namespace App\Services\Dashboard;

use App\Models\ApprovalRequest;
use App\Models\Attendance;
use App\Models\Notification;
use App\Models\Task;
use App\Models\User;

final class MyWorkDashboardService extends AbstractDashboardService
{
    protected function build(DashboardFilter $filter, User $user): array
    {
        $employeeId = $user->employee_id;

        if (! $employeeId) {
            return ['cards' => [], 'signals' => [], 'empty_message' => 'Akun belum terhubung dengan data karyawan.', 'last_updated' => now()->toIso8601String()];
        }

        $tasks = Task::query()
            ->whereHas('project', fn ($query) => $query->where('company_id', $filter->companyId))
            ->whereHas('assignees', fn ($query) => $query->whereKey($employeeId));
        $openTasks = (clone $tasks)->whereNotIn('status', ['done', 'cancelled'])->count();
        $overdueTasks = (clone $tasks)->whereNotIn('status', ['done', 'cancelled'])->whereDate('due_date', '<', today())->count();
        $approvals = ApprovalRequest::query()->where('company_id', $filter->companyId)->where('requester_id', $employeeId)->where('status', 'pending')->count();
        $attendance = Attendance::query()->where('employee_id', $employeeId)->whereBetween('date', [$filter->dateFrom->toDateString(), $filter->dateTo->toDateString()]);
        $unreadNotifications = Notification::query()->where('user_id', $user->id)->where('is_read', false)->count();

        return [
            'cards' => [
                ['label' => 'Tugas Aktif Saya', 'value' => $openTasks, 'format' => 'number', 'url' => '/admin/tasks'],
                ['label' => 'Tugas Terlambat', 'value' => $overdueTasks, 'format' => 'number', 'url' => '/admin/tasks'],
                ['label' => 'Pengajuan Menunggu', 'value' => $approvals, 'format' => 'number', 'url' => '/admin/approval-requests'],
                ['label' => 'Hari Kehadiran', 'value' => (clone $attendance)->where('status', 'present')->count(), 'format' => 'number', 'url' => '/admin/attendances'],
                ['label' => 'Notifikasi Belum Dibaca', 'value' => $unreadNotifications, 'format' => 'number', 'url' => '/admin/notifications'],
            ],
            'signals' => [
                ['label' => 'Tugas jatuh tempo hari ini', 'value' => (clone $tasks)->whereDate('due_date', today())->whereNotIn('status', ['done', 'cancelled'])->count(), 'format' => 'number'],
                ['label' => 'Tugas selesai periode ini', 'value' => (clone $tasks)->where('status', 'done')->whereBetween('completed_at', [$filter->dateFrom, $filter->dateTo])->count(), 'format' => 'number'],
            ],
            'last_updated' => now()->toIso8601String(),
        ];
    }
}
