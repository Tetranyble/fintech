<?php

namespace App\Services;

use App\Events\PaymentStatusChanged;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class PaymentService
{

    public function processPayment($paymentId, $customerId)
    {
        DB::transaction(function () use ($paymentId, $customerId) {
            $payment = Payment::findOrFail($paymentId);

            // Simulate randomized payment result (success ~70% chance)
            $isSuccess = rand(1, 10) > 3;
            $newStatus = $isSuccess ? 'successful' : 'failed';

            $payment->update(['status' => $newStatus]);

            // Dispatch the queued event
            event(new PaymentStatusChanged($payment->id, $newStatus, now(), $customerId));

            // If success, update balance (assume a Balance model)
            if ($isSuccess) {
                //$balance = Balance::where('customer_id', $customerId)->first();
                //$balance->update(['amount' => $balance->amount - $payment->amount]);
            }
        });
    }
}
