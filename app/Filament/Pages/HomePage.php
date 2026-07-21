<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\ApprovalRequest;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\Notification;
use App\Models\PosTransaction;
use App\Models\UserFavorite;
use App\Models\RecentlyViewed;
use App\Models\Invoice;
use App\Models\Employee;
use App\Models\Lead;
use App\Models\Project;
use App\Models\Course;
use App\Models\AiConversation;

class HomePage extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-home';

    protected static ?int $navigationSort = 0;

    protected static ?string $title = 'Command Center';

    protected static ?string $navigationLabel = 'Home';

    protected static ?string $slug = '/';

    protected string $view = 'filament.pages.home';

    public array $stats = [];
    public array $favorites = [];
    public array $recentlyViewed = [];
    public array $pendingApprovals = [];
    public array $todayTasks = [];
    public array $openTickets = [];
    public array $quickActions = [];
    public bool $aiModuleActive = false;

    public static function getNavigationGroup(): ?string
    {
        return null;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public function mount(): void
    {
        $user = auth()->user();
        if (!$user) return;

        $this->loadStats($user);
        $this->loadFavorites($user);
        $this->loadRecent($user);
        $this->loadQuickActions();
        $this->checkAiModule();

        $this->dispatch('stats-loaded');
    }

    protected function loadStats($user): void
    {
        $this->stats = [
            'pending_approvals' => ApprovalRequest::where('status', 'pending')->count(),
            'today_tasks' => Task::whereHas('assignees', fn($q) => $q->where('user_id', $user->id))
                ->where('status', '!=', 'completed')
                ->whereDate('due_date', '<=', now())
                ->count(),
            'unread_notifications' => Notification::where('user_id', $user->id)
                ->whereNull('read_at')
                ->count(),
            'open_tickets' => Ticket::where('status', 'open')->count(),
            'today_revenue' => PosTransaction::whereDate('transaction_date', now())
                ->sum('grand_total'),
            'pending_invoices' => Invoice::where('status', 'unpaid')
                ->where('due_date', '<', now()->addDays(7))
                ->count(),
        ];

        $this->pendingApprovals = ApprovalRequest::with(['approvable', 'requester'])
            ->where('status', 'pending')
            ->latest()
            ->limit(5)
            ->get()
            ->toArray();

        $this->todayTasks = Task::whereHas('assignees', fn($q) => $q->where('user_id', $user->id))
            ->where('status', '!=', 'completed')
            ->whereDate('due_date', '<=', now())
            ->with('project')
            ->latest('due_date')
            ->limit(5)
            ->get()
            ->toArray();

        $this->openTickets = Ticket::with(['category', 'assignedTo'])
            ->where('status', 'open')
            ->latest()
            ->limit(5)
            ->get()
            ->toArray();
    }

    protected function loadFavorites($user): void
    {
        $this->favorites = UserFavorite::where('user_id', $user->id)
            ->orderBy('sort_order')
            ->get()
            ->toArray();
    }

    protected function loadRecent($user): void
    {
        $this->recentlyViewed = RecentlyViewed::where('user_id', $user->id)
            ->orderBy('viewed_at', 'desc')
            ->limit(12)
            ->get()
            ->toArray();
    }

    protected function loadQuickActions(): void
    {
        $this->quickActions = [
            [
                'label' => 'Buat Faktur',
                'icon' => 'heroicon-o-document-plus',
                'url' => url('/admin/invoices/create'),
                'color' => 'indigo',
                'description' => 'Buat faktur penjualan baru',
            ],
            [
                'label' => 'Tambah Karyawan',
                'icon' => 'heroicon-o-user-plus',
                'url' => url('/admin/employees/create'),
                'color' => 'blue',
                'description' => 'Daftarkan karyawan baru',
            ],
            [
                'label' => 'Buat Tiket',
                'icon' => 'heroicon-o-ticket',
                'url' => url('/admin/tickets/create'),
                'color' => 'amber',
                'description' => 'Buat tiket support baru',
            ],
            [
                'label' => 'Prospek Baru',
                'icon' => 'heroicon-o-user-circle',
                'url' => url('/admin/leads/create'),
                'color' => 'emerald',
                'description' => 'Tambah prospek/lead baru',
            ],
            [
                'label' => 'Proyek Baru',
                'icon' => 'heroicon-o-clipboard-document-check',
                'url' => url('/admin/projects/create'),
                'color' => 'violet',
                'description' => 'Buat proyek baru',
            ],
            [
                'label' => 'Laporan Bisnis',
                'icon' => 'heroicon-o-chart-bar',
                'url' => url('/admin/laporan-bisnis'),
                'color' => 'rose',
                'description' => 'Lihat laporan performa bisnis',
            ],
        ];
    }

    protected function checkAiModule(): void
    {
        $this->aiModuleActive = AiConversation::exists() || class_exists(AiConversation::class);
    }
}
