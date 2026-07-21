<?php

namespace App\Services;

class AppSwitcherService
{
    public function getApps(): array
    {
        return [
            [
                'key' => 'home',
                'name' => 'Home',
                'icon' => 'heroicon-o-home',
                'color' => 'indigo',
                'color_class' => 'from-indigo-500 to-indigo-700',
                'text_class' => 'text-indigo-600',
                'bg_class' => 'bg-indigo-50',
                'border_class' => 'border-indigo-200',
                'groups' => [],
            ],
            [
                'key' => 'people',
                'name' => 'SDM & People',
                'icon' => 'heroicon-o-users',
                'color' => 'blue',
                'color_class' => 'from-blue-500 to-blue-700',
                'text_class' => 'text-blue-600',
                'bg_class' => 'bg-blue-50',
                'border_class' => 'border-blue-200',
                'groups' => ['Master Data', 'HRM', 'Payroll'],
            ],
            [
                'key' => 'finance',
                'name' => 'Keuangan',
                'icon' => 'heroicon-o-banknotes',
                'color' => 'emerald',
                'color_class' => 'from-emerald-500 to-emerald-700',
                'text_class' => 'text-emerald-600',
                'bg_class' => 'bg-emerald-50',
                'border_class' => 'border-emerald-200',
                'groups' => ['Finance', 'Procurement & Inventory', 'Billing'],
            ],
            [
                'key' => 'growth',
                'name' => 'Pertumbuhan',
                'icon' => 'heroicon-o-chart-bar',
                'color' => 'amber',
                'color_class' => 'from-amber-500 to-amber-700',
                'text_class' => 'text-amber-600',
                'bg_class' => 'bg-amber-50',
                'border_class' => 'border-amber-200',
                'groups' => ['CRM', 'Marketing'],
            ],
            [
                'key' => 'work',
                'name' => 'Manajemen Kerja',
                'icon' => 'heroicon-o-clipboard-document-check',
                'color' => 'violet',
                'color_class' => 'from-violet-500 to-violet-700',
                'text_class' => 'text-violet-600',
                'bg_class' => 'bg-violet-50',
                'border_class' => 'border-violet-200',
                'groups' => ['Project', 'Kolaborasi', 'Helpdesk'],
            ],
            [
                'key' => 'retail',
                'name' => 'Ritel & POS',
                'icon' => 'heroicon-o-shopping-cart',
                'color' => 'rose',
                'color_class' => 'from-rose-500 to-rose-700',
                'text_class' => 'text-rose-600',
                'bg_class' => 'bg-rose-50',
                'border_class' => 'border-rose-200',
                'groups' => ['POS'],
            ],
            [
                'key' => 'learning',
                'name' => 'Pembelajaran',
                'icon' => 'heroicon-o-academic-cap',
                'color' => 'cyan',
                'color_class' => 'from-cyan-500 to-cyan-700',
                'text_class' => 'text-cyan-600',
                'bg_class' => 'bg-cyan-50',
                'border_class' => 'border-cyan-200',
                'groups' => ['LMS'],
            ],
            [
                'key' => 'intelligence',
                'name' => 'Intelijen & AI',
                'icon' => 'heroicon-o-cpu-chip',
                'color' => 'fuchsia',
                'color_class' => 'from-fuchsia-500 to-fuchsia-700',
                'text_class' => 'text-fuchsia-600',
                'bg_class' => 'bg-fuchsia-50',
                'border_class' => 'border-fuchsia-200',
                'groups' => ['AI Assistant', 'Laporan'],
            ],
        ];
    }

    public function getAppForGroup(string $groupName): ?string
    {
        foreach ($this->getApps() as $app) {
            if (in_array($groupName, $app['groups'])) {
                return $app['key'];
            }
        }

        if (in_array($groupName, ['Sistem', 'Core', 'Integrasi'])) {
            return 'home';
        }

        return null;
    }
}
