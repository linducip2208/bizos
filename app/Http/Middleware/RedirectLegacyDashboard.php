<?php

namespace App\Http\Middleware;

use App\Filament\Pages\CommandCenter;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectLegacyDashboard
{
    public function handle(Request $request, Closure $next): Response
    {
        $action = $request->route()?->getActionName();
        $class = is_string($action) ? class_basename($action) : '';

        if ($class === '' || in_array($class, ['CommandCenter', 'DashboardBuilder'], true) || ! str_contains($class, 'Dashboard')) {
            return $next($request);
        }

        $tab = match (true) {
            str_contains($class, 'CashFlow'), str_contains($class, 'Treasury'), str_contains($class, 'Cfo') => 'finance',
            str_contains($class, 'Sales'), str_contains($class, 'Marketing'), str_contains($class, 'Funnel'), str_contains($class, 'Rfm') => 'sales',
            str_contains($class, 'Performance'), str_contains($class, 'Okr'), str_contains($class, 'FlightRisk') => 'hr-payroll',
            str_contains($class, 'Mrp'), str_contains($class, 'Logistics'), str_contains($class, 'FieldService') => 'operations',
            str_contains($class, 'Iso'), str_contains($class, 'Esg'), str_contains($class, 'Fraud'), str_contains($class, 'Anomaly') => 'risk-compliance',
            default => 'overview',
        };

        return redirect()->to(CommandCenter::getUrl(['tab' => $tab]));
    }
}
