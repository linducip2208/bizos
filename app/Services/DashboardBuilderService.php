<?php

namespace App\Services;

use App\Models\DashboardLayout;
use App\Models\DashboardWidget;
use App\Models\User;
use Illuminate\Support\Collection;

class DashboardBuilderService
{
    protected array $widgetLibrary = [];

    public function __construct()
    {
        $this->widgetLibrary = [
            'revenue_chart' => [
                'type' => 'chart',
                'label' => 'Grafik Pendapatan',
                'description' => 'Menampilkan grafik pendapatan per bulan',
                'icon' => 'heroicon-o-chart-bar',
                'default_size' => ['width' => 6, 'height' => 3],
                'config' => [
                    'chart_type' => 'line',
                    'data_source' => 'invoices',
                    'aggregation' => 'monthly',
                ],
            ],
            'attendance_stats' => [
                'type' => 'stats',
                'label' => 'Statistik Kehadiran',
                'description' => 'Ringkasan kehadiran hari ini',
                'icon' => 'heroicon-o-clipboard-document-list',
                'default_size' => ['width' => 6, 'height' => 2],
                'config' => [
                    'data_source' => 'attendances',
                    'metrics' => ['hadir', 'izin', 'sakit', 'alpha', 'terlambat'],
                ],
            ],
            'project_health' => [
                'type' => 'chart',
                'label' => 'Kesehatan Proyek',
                'description' => 'Status proyek: on-track, at-risk, delayed',
                'icon' => 'heroicon-o-rocket-launch',
                'default_size' => ['width' => 4, 'height' => 3],
                'config' => [
                    'chart_type' => 'doughnut',
                    'data_source' => 'projects',
                    'group_by' => 'status',
                ],
            ],
            'ticket_queue' => [
                'type' => 'stats',
                'label' => 'Antrian Tiket',
                'description' => 'Jumlah tiket berdasarkan status',
                'icon' => 'heroicon-o-ticket',
                'default_size' => ['width' => 3, 'height' => 2],
                'config' => [
                    'data_source' => 'tickets',
                    'metrics' => ['open', 'in_progress', 'resolved', 'closed'],
                ],
            ],
            'payroll_summary' => [
                'type' => 'stats',
                'label' => 'Ringkasan Penggajian',
                'description' => 'Total gaji bulan ini',
                'icon' => 'heroicon-o-banknotes',
                'default_size' => ['width' => 3, 'height' => 2],
                'config' => [
                    'data_source' => 'payrolls',
                    'period' => 'current_month',
                ],
            ],
            'sales_pipeline' => [
                'type' => 'funnel',
                'label' => 'Pipeline Penjualan',
                'description' => 'Deal per stage dalam pipeline',
                'icon' => 'heroicon-o-funnel',
                'default_size' => ['width' => 6, 'height' => 3],
                'config' => [
                    'data_source' => 'deals',
                    'group_by' => 'stage',
                ],
            ],
            'lead_conversion' => [
                'type' => 'chart',
                'label' => 'Konversi Lead',
                'description' => 'Tingkat konversi lead ke deal',
                'icon' => 'heroicon-o-user-plus',
                'default_size' => ['width' => 4, 'height' => 3],
                'config' => [
                    'chart_type' => 'bar',
                    'data_source' => 'leads',
                ],
            ],
            'task_overview' => [
                'type' => 'stats',
                'label' => 'Overview Tugas',
                'description' => 'Tugas berdasarkan status',
                'icon' => 'heroicon-o-clipboard-document-check',
                'default_size' => ['width' => 4, 'height' => 2],
                'config' => [
                    'data_source' => 'tasks',
                    'metrics' => ['todo', 'in_progress', 'review', 'done'],
                ],
            ],
            'recent_activities' => [
                'type' => 'table',
                'label' => 'Aktivitas Terbaru',
                'description' => 'Log aktivitas terkini',
                'icon' => 'heroicon-o-clock',
                'default_size' => ['width' => 6, 'height' => 3],
                'config' => [
                    'data_source' => 'audit_logs',
                    'limit' => 10,
                ],
            ],
            'my_tasks' => [
                'type' => 'table',
                'label' => 'Tugas Saya',
                'description' => 'Daftar tugas yang ditugaskan',
                'icon' => 'heroicon-o-list-bullet',
                'default_size' => ['width' => 8, 'height' => 3],
                'config' => [
                    'data_source' => 'my_tasks',
                    'filter' => 'assigned_to_me',
                ],
            ],
            'my_attendance' => [
                'type' => 'stats',
                'label' => 'Kehadiran Saya',
                'description' => 'Rekap kehadiran pribadi bulan ini',
                'icon' => 'heroicon-o-calendar-days',
                'default_size' => ['width' => 3, 'height' => 2],
                'config' => [
                    'data_source' => 'my_attendance',
                    'period' => 'current_month',
                ],
            ],
            'approval_pending' => [
                'type' => 'table',
                'label' => 'Menunggu Persetujuan',
                'description' => 'Daftar item yang perlu disetujui',
                'icon' => 'heroicon-o-check-badge',
                'default_size' => ['width' => 6, 'height' => 3],
                'config' => [
                    'data_source' => 'approval_requests',
                    'filter' => 'pending',
                ],
            ],
        ];
    }

    public function getWidgetLibrary(): array
    {
        return $this->widgetLibrary;
    }

    public function getWidgetDefinition(string $type): ?array
    {
        return $this->widgetLibrary[$type] ?? null;
    }

    public function getLayout(?int $userId): ?DashboardLayout
    {
        if (!$userId) {
            return null;
        }

        $layout = DashboardLayout::where('user_id', $userId)
            ->where('is_default', true)
            ->first();

        if (!$layout) {
            $layout = $this->createDefaultLayout($userId);
            $this->seedRoleBasedWidgets($userId);
        }

        return $layout;
    }

    public function getRoleBasedLayout(string $roleSlug): array
    {
        return match ($roleSlug) {
            'super-admin', 'admin' => [
                ['type' => 'revenue_chart', 'position' => ['x' => 0, 'y' => 0, 'width' => 6, 'height' => 3]],
                ['type' => 'attendance_stats', 'position' => ['x' => 6, 'y' => 0, 'width' => 6, 'height' => 2]],
                ['type' => 'sales_pipeline', 'position' => ['x' => 0, 'y' => 3, 'width' => 6, 'height' => 3]],
                ['type' => 'ticket_queue', 'position' => ['x' => 6, 'y' => 2, 'width' => 3, 'height' => 2]],
                ['type' => 'project_health', 'position' => ['x' => 6, 'y' => 4, 'width' => 3, 'height' => 3]],
                ['type' => 'payroll_summary', 'position' => ['x' => 9, 'y' => 2, 'width' => 3, 'height' => 2]],
                ['type' => 'lead_conversion', 'position' => ['x' => 9, 'y' => 4, 'width' => 3, 'height' => 3]],
                ['type' => 'recent_activities', 'position' => ['x' => 0, 'y' => 6, 'width' => 6, 'height' => 3]],
                ['type' => 'approval_pending', 'position' => ['x' => 6, 'y' => 6, 'width' => 6, 'height' => 3]],
            ],
            'manager', 'hr-manager', 'finance-manager' => [
                ['type' => 'revenue_chart', 'position' => ['x' => 0, 'y' => 0, 'width' => 8, 'height' => 3]],
                ['type' => 'ticket_queue', 'position' => ['x' => 8, 'y' => 0, 'width' => 4, 'height' => 2]],
                ['type' => 'attendance_stats', 'position' => ['x' => 0, 'y' => 3, 'width' => 6, 'height' => 2]],
                ['type' => 'task_overview', 'position' => ['x' => 6, 'y' => 3, 'width' => 6, 'height' => 2]],
                ['type' => 'project_health', 'position' => ['x' => 0, 'y' => 5, 'width' => 5, 'height' => 3]],
                ['type' => 'approval_pending', 'position' => ['x' => 5, 'y' => 5, 'width' => 7, 'height' => 3]],
            ],
            'employee', 'staff' => [
                ['type' => 'my_attendance', 'position' => ['x' => 0, 'y' => 0, 'width' => 3, 'height' => 2]],
                ['type' => 'my_tasks', 'position' => ['x' => 3, 'y' => 0, 'width' => 9, 'height' => 3]],
                ['type' => 'ticket_queue', 'position' => ['x' => 0, 'y' => 2, 'width' => 6, 'height' => 2]],
                ['type' => 'recent_activities', 'position' => ['x' => 6, 'y' => 2, 'width' => 6, 'height' => 3]],
            ],
            'kasir' => [
                ['type' => 'attendance_stats', 'position' => ['x' => 0, 'y' => 0, 'width' => 6, 'height' => 2]],
                ['type' => 'my_tasks', 'position' => ['x' => 6, 'y' => 0, 'width' => 6, 'height' => 3]],
            ],
            default => [
                ['type' => 'my_attendance', 'position' => ['x' => 0, 'y' => 0, 'width' => 3, 'height' => 2]],
                ['type' => 'my_tasks', 'position' => ['x' => 3, 'y' => 0, 'width' => 9, 'height' => 3]],
                ['type' => 'recent_activities', 'position' => ['x' => 0, 'y' => 2, 'width' => 12, 'height' => 3]],
            ],
        };
    }

    public function seedRoleBasedWidgets(int $userId): void
    {
        $user = User::find($userId);
        if (!$user || !$user->role) {
            return;
        }

        $roleSlug = $user->role->slug;
        $widgetConfigs = $this->getRoleBasedLayout($roleSlug);

        $sortOrder = 1;
        foreach ($widgetConfigs as $config) {
            $widgetDef = $this->getWidgetDefinition($config['type']);
            if (!$widgetDef) {
                continue;
            }

            $position = $config['position'];
            $defaultSize = $widgetDef['default_size'];

            DashboardWidget::create([
                'company_id' => $user->company_id,
                'user_id' => $userId,
                'widget_type' => $config['type'],
                'title' => $widgetDef['label'],
                'config' => $widgetDef['config'],
                'position' => [
                    'x' => $position['x'] ?? 0,
                    'y' => $position['y'] ?? 0,
                    'width' => $position['width'] ?? $defaultSize['width'],
                    'height' => $position['height'] ?? $defaultSize['height'],
                ],
                'is_pinned' => true,
                'sort_order' => $sortOrder++,
            ]);
        }
    }

    public function shareLayoutWithRole(int $userId, string $roleSlug): void
    {
        $sourceWidgets = DashboardWidget::where('user_id', $userId)->get();
        $sourceLayout = DashboardLayout::where('user_id', $userId)->where('is_default', true)->first();

        if (!$sourceLayout) {
            return;
        }

        $targetUsers = User::whereHas('role', fn ($q) => $q->where('slug', $roleSlug))
            ->where('id', '!=', $userId)
            ->where('is_active', true)
            ->get();

        foreach ($targetUsers as $targetUser) {
            DashboardWidget::where('user_id', $targetUser->id)->delete();

            DashboardLayout::where('user_id', $targetUser->id)
                ->where('is_default', true)
                ->delete();

            $newLayout = DashboardLayout::create([
                'user_id' => $targetUser->id,
                'name' => $sourceLayout->name . ' (Shared)',
                'layout_config' => $sourceLayout->layout_config,
                'is_default' => true,
            ]);

            foreach ($sourceWidgets as $widget) {
                DashboardWidget::create([
                    'company_id' => $widget->company_id,
                    'user_id' => $targetUser->id,
                    'widget_type' => $widget->widget_type,
                    'title' => $widget->title,
                    'config' => $widget->config,
                    'position' => $widget->position,
                    'is_pinned' => $widget->is_pinned,
                    'sort_order' => $widget->sort_order,
                ]);
            }
        }
    }

    public function saveLayout(int $userId, array $widgetPositions): void
    {
        $layout = DashboardLayout::where('user_id', $userId)
            ->where('is_default', true)
            ->first();

        if (!$layout) {
            $layout = $this->createDefaultLayout($userId);
        }

        $layoutConfig = $layout->layout_config ?? [];

        foreach ($widgetPositions as $data) {
            $widgetId = $data['widget_id'] ?? null;
            if (!$widgetId) {
                continue;
            }

            $widget = DashboardWidget::find($widgetId);
            if (!$widget || $widget->user_id !== $userId) {
                continue;
            }

            $widget->update([
                'position' => [
                    'x' => $data['x'] ?? 0,
                    'y' => $data['y'] ?? 0,
                    'width' => $data['width'] ?? 1,
                    'height' => $data['height'] ?? 1,
                ],
                'sort_order' => $data['sort_order'] ?? 0,
            ]);
        }

        $layout->update([
            'layout_config' => [
                'columns' => $layoutConfig['columns'] ?? 12,
                'row_height' => $layoutConfig['row_height'] ?? 120,
                'updated_at' => now()->toISOString(),
            ],
        ]);
    }

    public function getWidgets(?int $userId): Collection
    {
        if (!$userId) {
            return collect();
        }

        return DashboardWidget::where('user_id', $userId)
            ->orderBy('sort_order')
            ->get();
    }

    public function addWidget(array $config): DashboardWidget
    {
        $maxSort = DashboardWidget::where('user_id', $config['user_id'] ?? 0)
            ->max('sort_order') ?? 0;

        return DashboardWidget::create([
            'company_id' => $config['company_id'] ?? null,
            'user_id' => $config['user_id'] ?? null,
            'widget_type' => $config['widget_type'] ?? 'stats',
            'title' => $config['title'] ?? 'New Widget',
            'config' => $config['config'] ?? [],
            'position' => $config['position'] ?? ['x' => 0, 'y' => 0, 'width' => 3, 'height' => 2],
            'is_pinned' => $config['is_pinned'] ?? false,
            'sort_order' => $maxSort + 1,
        ]);
    }

    public function removeWidget(DashboardWidget $widget): void
    {
        $widget->delete();
    }

    public function updateWidgetConfig(DashboardWidget $widget, array $config): void
    {
        $updateData = [];

        if (isset($config['title'])) {
            $updateData['title'] = $config['title'];
        }

        if (isset($config['widget_type'])) {
            $updateData['widget_type'] = $config['widget_type'];
        }

        if (isset($config['config'])) {
            $updateData['config'] = $config['config'];
        }

        if (isset($config['position'])) {
            $updateData['position'] = $config['position'];
        }

        if (isset($config['is_pinned'])) {
            $updateData['is_pinned'] = $config['is_pinned'];
        }

        if (isset($config['sort_order'])) {
            $updateData['sort_order'] = $config['sort_order'];
        }

        if (!empty($updateData)) {
            $widget->update($updateData);
        }
    }

    protected function createDefaultLayout(int $userId): DashboardLayout
    {
        return DashboardLayout::create([
            'user_id' => $userId,
            'name' => 'Default Layout',
            'layout_config' => [
                'columns' => 12,
                'row_height' => 120,
                'compact_type' => 'vertical',
                'breakpoints' => [
                    'lg' => 1200,
                    'md' => 996,
                    'sm' => 768,
                    'xs' => 480,
                ],
            ],
            'is_default' => true,
        ]);
    }
}
