<?php

namespace App\Filament\Navigation;

enum NavigationGroup: string
{
    case DASHBOARD = 'Dashboard';
    case ORGANIZATION = 'Organization';
    case HUMAN_CAPITAL = 'Human Capital';
    case PAYROLL = 'Payroll';
    case FINANCE = 'Finance & Accounting';
    case SALES = 'Sales & CRM';
    case PROCUREMENT = 'Procurement';
    case INVENTORY = 'Inventory & Warehouse';
    case OPERATIONS = 'Operations';
    case PROJECTS = 'Projects';
    case COMMERCE = 'POS & Commerce';
    case COLLABORATION = 'Collaboration & Service';
    case AUTOMATION = 'Automation & AI';
    case REPORTS = 'Reports & Compliance';
    case SYSTEM = 'System Settings';

    public function icon(): string
    {
        return match ($this) {
            self::DASHBOARD => 'heroicon-o-home',
            self::ORGANIZATION => 'heroicon-o-building-office-2',
            self::HUMAN_CAPITAL => 'heroicon-o-users',
            self::PAYROLL => 'heroicon-o-banknotes',
            self::FINANCE => 'heroicon-o-calculator',
            self::SALES => 'heroicon-o-chart-bar',
            self::PROCUREMENT => 'heroicon-o-shopping-cart',
            self::INVENTORY => 'heroicon-o-cube',
            self::OPERATIONS => 'heroicon-o-cog-6-tooth',
            self::PROJECTS => 'heroicon-o-clipboard-document-list',
            self::COMMERCE => 'heroicon-o-shopping-bag',
            self::COLLABORATION => 'heroicon-o-chat-bubble-left-right',
            self::AUTOMATION => 'heroicon-o-bolt',
            self::REPORTS => 'heroicon-o-chart-pie',
            self::SYSTEM => 'heroicon-o-adjustments-horizontal',
        };
    }

    /** @return list<self> */
    public static function ordered(): array
    {
        return self::cases();
    }
}
