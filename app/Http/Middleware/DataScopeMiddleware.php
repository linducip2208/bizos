<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DataScopeMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();

            if ($user && !in_array($user->role?->slug, ['super-admin', 'admin'])) {
                $branchId = null;

                if ($user->employee?->branch_id) {
                    $branchId = $user->employee->branch_id;
                }

                session(['data_scope_branch_id' => $branchId]);
            } else {
                session()->forget('data_scope_branch_id');
            }
        }

        return $next($request);
    }
}
