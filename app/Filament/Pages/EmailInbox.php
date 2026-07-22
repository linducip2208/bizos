<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class EmailInbox extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-envelope';

    protected static ?int $navigationSort = 601;

    protected static string $view = 'filament.pages.email-inbox';

    protected static ?string $title = 'Email';

    protected static ?string $navigationLabel = 'Email';

    protected static ?string $slug = 'email';

    public static function getNavigationGroup(): ?string
    {
        return 'Kolaborasi';
    }

    public static function canView(): bool
    {
        return auth()->check();
    }
}
