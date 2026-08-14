<?php

namespace App\Http\Controllers;

use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;

class PaymentGatewayTestController extends Controller
{
    public function index(Request $request, PaymentGatewayService $service)
    {
        $gateways = $service->getAvailableGateways();
        $results = $request->session()->get('gateway_test_result');

        return view('payment-gateway-test', compact('gateways', 'results'));
    }

    public function test(string $gateway, PaymentGatewayService $service)
    {
        $result = $service->testConnection($gateway);

        session()->flash('gateway_test_result', $result);

        return redirect()->route('payment-gateway.test');
    }
}
