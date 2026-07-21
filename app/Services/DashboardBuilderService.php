<?php

namespace App\Services;

use App\Models\DashboardLayout;
use App\Models\DashboardWidget;
use Illuminate\Support\Collection;

class DashboardBuilderService
{
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
        }

        return $layout;
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
