<?php

namespace App\Contracts;

use App\Models\Payment;
use App\Models\Tenant;
use App\Services\Payments\PaymentResult;

interface PaymentGateway
{
    /**
     * Charge the tenant for a pending payment and return the outcome.
     */
    public function charge(Payment $payment, Tenant $tenant): PaymentResult;
}
