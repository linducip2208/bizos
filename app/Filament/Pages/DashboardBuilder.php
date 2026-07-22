<?php

namespace App\Filament\Pages;

use App\Models\DashboardWidget;
use App\Models\ReportTemplate;
use App\Services\DashboardBuilderService;
use App\Services\ReportBuilderService;
use Filament\Pages\Page;

class DashboardBuilder extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-squares-plus';

    protected static ?int $navigationSort = 1113;

    protected static ?string $title = 'Dashboard Builder';

    protected static string $view = 'filament.pages.dashboard-builder';

    public static function getNavigationGroup(): ?string
    {
        return 'Laporan';
    }

    public array $dashboardLayout = [];
    public array $widgets = [];
    public array $widgetData = [];
    public array $availableTemplates = [];

    public function mount(): void
    {
        $userId = auth()->id();
        $service = app(DashboardBuilderService::class);

        $layout = $service->getLayout($userId);
        $this->dashboardLayout = $layout ? $layout->toArray() : [];

        $this->widgets = $service->getWidgets($userId)
            ->map(function ($widget) {
                return [
                    'id' => $widget->id,
                    'title' => $widget->title,
                    'widget_type' => $widget->widget_type,
                    'config' => $widget->config,
                    'position' => $widget->position,
                    'is_pinned' => $widget->is_pinned,
                    'sort_order' => $widget->sort_order,
                ];
            })
            ->toArray();

        $this->availableTemplates = ReportTemplate::where(function ($q) {
            $q->where('is_public', true)
                ->orWhere('company_id', auth()->user()->company_id);
        })
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->toArray();

        $this->loadWidgetData();
    }

    protected function loadWidgetData(): void
    {
        $reportService = app(ReportBuilderService::class);
        $this->widgetData = [];

        foreach ($this->widgets as $widget) {
            $config = $widget['config'] ?? [];

            if (!empty($config['report_template_id'])) {
                $template = ReportTemplate::find($config['report_template_id']);
                if ($template) {
                    try {
                        $params = $config['params'] ?? [];
                        $params['date_from'] = $params['date_from'] ?? now()->startOfYear()->format('Y-m-d');
                        $params['date_to'] = $params['date_to'] ?? now()->format('Y-m-d');

                        $data = $reportService->execute($template, $params);

                        if (in_array($widget['widget_type'], ['chart', 'metric'])) {
                            $chartData = $reportService->generateChartData($template, $params);
                            $this->widgetData[$widget['id']] = [
                                'type' => 'chart',
                                'chartData' => $chartData,
                                'summary' => $this->extractSummary($data),
                            ];
                        } elseif ($widget['widget_type'] === 'stats') {
                            $this->widgetData[$widget['id']] = [
                                'type' => 'stats',
                                'stats' => $this->extractSummary($data),
                            ];
                        } elseif ($widget['widget_type'] === 'kpi') {
                            $targetKey = $config['target_key'] ?? null;
                            $valueKey = $config['value_key'] ?? null;
                            $kpiValue = $data->isNotEmpty() ? ((float) ((array) $data->first())[$valueKey] ?? 0) : 0;
                            $kpiTarget = $config['target'] ?? 100;
                            $this->widgetData[$widget['id']] = [
                                'type' => 'kpi',
                                'value' => $kpiValue,
                                'target' => $kpiTarget,
                                'percentage' => $kpiTarget > 0 ? round(($kpiValue / $kpiTarget) * 100, 1) : 0,
                            ];
                        } else {
                            $this->widgetData[$widget['id']] = [
                                'type' => 'table',
                                'headers' => $data->isNotEmpty() ? array_keys((array) $data->first()) : [],
                                'rows' => $data->toArray(),
                            ];
                        }
                    } catch (\Exception $e) {
                        $this->widgetData[$widget['id']] = [
                            'type' => 'error',
                            'message' => $e->getMessage(),
                        ];
                    }
                }
            } elseif ($widget['widget_type'] === 'metric') {
                $this->widgetData[$widget['id']] = [
                    'type' => 'metric',
                    'label' => $config['label'] ?? $widget['title'],
                    'value' => $config['value'] ?? 0,
                    'prefix' => $config['prefix'] ?? '',
                    'suffix' => $config['suffix'] ?? '',
                    'color' => $config['color'] ?? '#4f46e5',
                ];
            }
        }
    }

    protected function extractSummary($data): array
    {
        $summary = [];
        foreach ($data as $row) {
            $row = (array) $row;
            foreach ($row as $key => $value) {
                if (is_numeric($value)) {
                    $summary[$key] = ($summary[$key] ?? 0) + (float) $value;
                }
            }
        }
        return $summary;
    }

    public function addWidget(): void
    {
        $data = request()->validate([
            'title' => 'required|string|max:255',
            'widget_type' => 'required|in:chart,stats,table,kpi,metric',
            'config.report_template_id' => 'nullable|exists:report_templates,id',
            'config.params' => 'nullable|array',
            'config.label' => 'nullable|string',
            'config.value' => 'nullable|string',
            'config.color' => 'nullable|string',
        ]);

        $service = app(DashboardBuilderService::class);
        $widget = $service->addWidget([
            'company_id' => auth()->user()->company_id,
            'user_id' => auth()->id(),
            'widget_type' => $data['widget_type'],
            'title' => $data['title'],
            'config' => $data['config'] ?? [],
            'position' => ['x' => 0, 'y' => 0, 'width' => 6, 'height' => 3],
            'is_pinned' => false,
        ]);

        $this->widgets = $service->getWidgets(auth()->id())
            ->map(fn($w) => $w->toArray())->toArray();

        $this->loadWidgetData();
    }

    public function removeWidget(int $widgetId): void
    {
        $widget = DashboardWidget::findOrFail($widgetId);

        if ($widget->user_id !== auth()->id()) {
            return;
        }

        $service = app(DashboardBuilderService::class);
        $service->removeWidget($widget);

        $this->widgets = $service->getWidgets(auth()->id())
            ->map(fn($w) => $w->toArray())->toArray();

        $this->loadWidgetData();
    }

    public function saveLayout(): void
    {
        $positions = request()->input('positions', []);

        $service = app(DashboardBuilderService::class);
        $service->saveLayout(auth()->id(), $positions);

        $this->dispatch('notify', type: 'success', message: 'Layout berhasil disimpan.');
    }
}
