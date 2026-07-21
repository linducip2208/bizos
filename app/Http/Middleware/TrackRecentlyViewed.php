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

            $expectedRoute = 'filament.admin.resources.' . $resource::getSlug() . '.index';
            $editRoute = 'filament.admin.resources.' . $resource::getSlug() . '.edit';
            $createRoute = 'filament.admin.resources.' . $resource::getSlug() . '.create';

            if ($routeName === $expectedRoute || $routeName === $editRoute || $routeName === $createRoute) {
                $label = method_exists($resource, 'getPluralModelLabel')
                    ? $resource::getPluralModelLabel()
                    : (method_exists($resource, 'getModelLabel') ? $resource::getModelLabel() : class_basename($resource));

                $icon = method_exists($resource, 'getNavigationIcon')
                    ? $this->resolveIconName($resource::getNavigationIcon())
                    : 'heroicon-o-rectangle-stack';

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
                $label = method_exists($page, 'getTitle') ? $page::getTitle() : class_basename($page);
                $icon = method_exists($page, 'getNavigationIcon')
                    ? $this->resolveIconName($page::getNavigationIcon())
                    : 'heroicon-o-document';

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
