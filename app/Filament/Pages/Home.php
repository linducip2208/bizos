<?php

namespace App\Filament\Pages;

use App\Models\ApprovalRequest;
use App\Models\Invoice;
use App\Models\Notification;
use App\Models\Ticket;
use App\Models\Task;
use Filament\Pages\Page;

class Home extends Page
{
    protected static bool $shouldRegisterNavigation = false;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';

    protected static ?int $navigationSort = 0;

    protected static ?string $title = 'Dashboard';

    protected string $view = 'filament.pages.home';

    public array $stats = [];
    public array $quickActions = [];
    public array $pendingApprovals = [];
    public array $todayTasks = [];
    public array $recentlyViewed = [];
    public array $favorites = [];
    public bool $aiModuleActive = false;

    public static function getNavigationGroup(): ?string
    {
        return \App\Filament\Navigation\NavigationGroup::DASHBOARD->value;
    }

    public function mount(): void
    {
        $user = auth()->user();
        $companyId = (int) $user->company_id;
        $branchId = $user->employee?->branch_id;
        $employeeId = $user->employee_id;
        $myTasks = fn () => Task::query()
            ->whereHas('project', fn ($query) => $query->where('company_id', $companyId))
            ->when($employeeId, fn ($query) => $query->whereHas('assignees', fn ($assignees) => $assignees->whereKey($employeeId)), fn ($query) => $query->whereRaw('1 = 0'));

        $this->stats = [
            'pending_approvals' => ApprovalRequest::where('company_id', $companyId)->where('status', 'pending')->count(),
            'today_tasks' => $myTasks()->whereDate('due_date', today())->count(),
            'unread_notifications' => Notification::where('user_id', $user->id)->whereNull('read_at')->count(),
            'open_tickets' => Ticket::where('company_id', $companyId)->where('status', 'open')->count(),
            'today_revenue' => Invoice::where('company_id', $companyId)->when($branchId, fn ($query) => $query->where('branch_id', $branchId))->whereDate('invoice_date', today())->sum('total'),
            'pending_invoices' => Invoice::where('company_id', $companyId)->when($branchId, fn ($query) => $query->where('branch_id', $branchId))->whereIn('status', ['sent', 'overdue'])->count(),
        ];

        $this->quickActions = [
            ['url' => '/admin/employees/create', 'icon' => 'heroicon-o-user-plus', 'label' => 'Tambah Karyawan', 'description' => 'Daftarkan karyawan baru', 'color' => 'indigo'],
            ['url' => '/admin/attendances', 'icon' => 'heroicon-o-clock', 'label' => 'Absensi', 'description' => 'Catat kehadiran', 'color' => 'blue'],
            ['url' => '/admin/leaves', 'icon' => 'heroicon-o-calendar', 'label' => 'Cuti', 'description' => 'Ajukan cuti', 'color' => 'emerald'],
            ['url' => '/admin/tasks/create', 'icon' => 'heroicon-o-plus-circle', 'label' => 'Tugas Baru', 'description' => 'Buat tugas baru', 'color' => 'amber'],
            ['url' => '/admin/clients/create', 'icon' => 'heroicon-o-building-office', 'label' => 'Klien Baru', 'description' => 'Tambah klien', 'color' => 'violet'],
            ['url' => '/admin/invoices/create', 'icon' => 'heroicon-o-document-text', 'label' => 'Faktur Baru', 'description' => 'Buat faktur', 'color' => 'rose'],
        ];

        $this->pendingApprovals = ApprovalRequest::with('requester')
            ->where('company_id', $companyId)
            ->where('status', 'pending')
            ->latest()
            ->limit(5)
            ->get()
            ->toArray();

        $this->todayTasks = $myTasks()
            ->with('project')
            ->whereDate('due_date', today())
            ->latest()
            ->limit(5)
            ->get()
            ->toArray();

        $this->recentlyViewed = session()->get('recently_viewed', []);
        $this->favorites = session()->get('favorites', []);
    }

}
