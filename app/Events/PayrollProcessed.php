<?php

namespace App\Events;

use App\Models\Payroll;
use Illuminate\Foundation\Events\Dispatchable;

class PayrollProcessed
{
    use Dispatchable;

    public function __construct(public Payroll $payroll) {}
}
