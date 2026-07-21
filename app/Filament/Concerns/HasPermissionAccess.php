<?php

namespace App\Filament\Concerns;

trait HasPermissionAccess
{
    protected static ?string $permissionGroup = null;

    public static function canViewAny(): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        if (in_array($user->role?->slug, ['super-admin', 'admin'])) return true;

        $slug = static::$permissionGroup ?? static::getPermissionGroup();
        return $user->role?->permissions()->whereIn('slug', ["{$slug}.view", "{$slug}.manage"])->exists();
    }

    public static function canCreate(): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        if (in_array($user->role?->slug, ['super-admin', 'admin'])) return true;

        $slug = static::$permissionGroup ?? static::getPermissionGroup();
        return $user->role?->permissions()->whereIn('slug', ["{$slug}.create", "{$slug}.manage"])->exists();
    }

    public static function canEdit($record): bool
    {
        return static::canViewAny();
    }

    public static function canDelete($record): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        if (in_array($user->role?->slug, ['super-admin', 'admin'])) return true;

        $slug = static::$permissionGroup ?? static::getPermissionGroup();
        return $user->role?->permissions()->whereIn('slug', ["{$slug}.delete", "{$slug}.manage"])->exists();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }

    protected static function getPermissionGroup(): string
    {
        $group = static::getNavigationGroup();
        if (!$group) {
            $class = class_basename(static::class);
            $name = preg_replace('/Resource$/', '', $class);
            return strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', lcfirst($name)));
        }
        return strtolower(str_replace(' ', '-', $group));
    }
}
