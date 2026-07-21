<?php

namespace App\Http\Middleware;

use App\Services\BillingService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FeatureGate
{
    public function __construct(private BillingService $billingService) {}

    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $user = auth()->user();

        if (!$user) {
            abort(403, 'Unauthenticated.');
        }

        $company = $user->company;

        if (!$company) {
            abort(403, 'No company associated with this account.');
        }

        $features = $this->billingService->getFeatureAccess($company);

        if (!in_array($feature, $features) && !in_array('all_features', $features)) {
            abort(403, "Fitur '{$feature}' tidak tersedia di paket langganan Anda. Silakan upgrade untuk mengakses fitur ini.");
        }

        return $next($request);
    }
}
