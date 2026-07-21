<?php

namespace App\Http\Middleware;

use App\Services\FavoritesService;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;

class TrackRecentlyViewed
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (!auth()->check()) {
            return $response;
        }

        $user = auth()->user();

        try {
            $currentRoute = $request->route();
            if (!$currentRoute) return $response;

            $routeName = $currentRoute->getName();
            if (!$routeName) return $response;

            $service = app(FavoritesService::class);

            $matched = $this->matchFilamentResource($routeName);
            if ($matched) {
                $service->trackRecentlyViewed(
                    $user,
                    $matched['type'],
                    $matched['label'],
                    $matched['url'],
                    $matched['icon'],
                );
                return $response;
            }

            $matched = $this->matchFilamentPage($routeName);
            if ($matched) {
                $service->trackRecentlyViewed(
                    $user,
                    $matched['type'],
                    $matched['label'],
                    $matched['url'],
                    $matched['icon'],
                );
            }
        } catch (\Exception $e) {
        }

        return $response;
    }

    protected function matchFilamentResource(string $routeName): ?array
    {
        foreach (Filament::getResources() as $resource) {
            if (!method_exists($resource, 'canViewAny') || !$resource::canViewAny()) {
                continue;
            }

            $slug = '';
            try { $slug = $resource::getSlug(); } catch (\Throwable $e) { continue; }
            $expectedRoute = 'filament.admin.resources.' . $slug . '.index';
            $editRoute = 'filament.admin.resources.' . $slug . '.edit';
            $createRoute = 'filament.admin.resources.' . $slug . '.create';

            if ($routeName === $expectedRoute || $routeName === $editRoute || $routeName === $createRoute) {
                $label = class_basename($resource);
                try { $label = $resource::getPluralModelLabel(); } catch (\Throwable $e) {}
                try { if (!$label) $label = $resource::getModelLabel(); } catch (\Throwable $e) {}
                $icon = 'heroicon-o-rectangle-stack';
                try { $icon = $this->resolveIconName($resource::getNavigationIcon()); } catch (\Throwable $e) {}

                return [
                    'type' => $resource::getSlug(),
                    'label' => $label,
                    'url' => url()->current(),
                    'icon' => $icon,
                ];
            }
        }

        return null;
    }

    protected function matchFilamentPage(string $routeName): ?array
    {
        foreach (Filament::getPages() as $page) {
            if (!method_exists($page, 'canView') || !$page::canView()) {
                continue;
            }

            $expectedRoute = 'filament.admin.pages.' . $page::getSlug();

            if ($routeName === $expectedRoute) {
                $label = class_basename($page);
                try { $label = $page::getTitle(); } catch (\Throwable $e) {}
                $icon = 'heroicon-o-document';
                try { $icon = $this->resolveIconName($page::getNavigationIcon()); } catch (\Throwable $e) {}

                return [
                    'type' => $page::getSlug(),
                    'label' => $label,
                    'url' => url()->current(),
                    'icon' => $icon,
                ];
            }
        }

        return null;
    }

    protected function resolveIconName($icon): string
    {
        if ($icon instanceof \BackedEnum) {
            return $icon->value ?? 'heroicon-o-rectangle-stack';
        }
        if (is_string($icon)) {
            if (str_starts_with($icon, 'heroicon-')) {
                return $icon;
            }
        }
        return 'heroicon-o-rectangle-stack';
    }
}
