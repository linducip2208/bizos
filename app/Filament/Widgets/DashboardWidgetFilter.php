<?php

namespace App\Filament\Widgets;

trait DashboardWidgetFilter
{
    public static function canView(): bool
    {
        return static::isVisibleToRole(auth()->user()?->role?->slug);
    }

    protected static function isVisibleToRole(?string $role): bool
    {
        return true;
    }
}
