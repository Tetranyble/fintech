<?php

use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('customer.{userId}', function (User $user, int $userId) {
    return (int) $user->id === $userId;
});

// Private payment channel - equivalent to PrivatePaymentChannel
Broadcast::channel('payment.{paymentId}', function (User $user, int $paymentId) {
    $payment = Payment::findOrFail($paymentId);

    return $payment->user_id === $user->id;
});
