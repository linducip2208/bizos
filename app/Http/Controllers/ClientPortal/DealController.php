<?php

namespace App\Http\Controllers\ClientPortal;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DealController extends Controller
{
    public function index(Request $request)
    {
        $clientUser = Auth::guard('client')->user();
        $clientId = $clientUser->client_id;

        $deals = Deal::where('client_id', $clientId)
            ->with(['stage', 'assignedTo'])
            ->latest()
            ->paginate(15);

        return view('client-portal.deals', compact('clientUser', 'deals'));
    }

    public function show($id)
    {
        $clientUser = Auth::guard('client')->user();
        $clientId = $clientUser->client_id;

        $deal = Deal::where('client_id', $clientId)
            ->with(['stage', 'assignedTo', 'lead'])
            ->findOrFail($id);

        return view('client-portal.deal-detail', compact('clientUser', 'deal'));
    }
}
