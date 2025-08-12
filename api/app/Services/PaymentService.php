<?php

namespace App\Services;

use App\Events\BalanceUpdated;
use App\Events\PaymentStatusChanged;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function processPayment(Payment $payment): void
    {
        DB::transaction(function () use ($payment) {
            // Create debit transaction
            $debitTransaction = $payment->user->transactions()->create([
                'type' => Transaction::TYPE_DEBIT,
                'amount' => $payment->amount,
                'description' => 'Payment to '.$payment->recipient,
                'payment_id' => $payment->id,
            ]);

            // Update sender balance
            $payment->user->balance -= $payment->amount;
            $payment->user->save();

            // Update payment status
            $payment->status = 'processing';
            $payment->save();

            // Broadcast events
            event(new BalanceUpdated($payment->user, $debitTransaction));
            event(new PaymentStatusChanged($payment));

            // Step 3: Payment Completion (simulated)
            $this->completePayment($payment);
        });
    }

    public function completePayment(Payment $payment): void
    {

        DB::transaction(function () use ($payment) {
            $payment->status = 'completed';
            $payment->save();

            // If recipient is internal user, create credit transaction
            $recipient = User::where('email', $payment->recipient)->first();

            if ($recipient) {
                $creditTransaction = $recipient->transactions()->create([
                    'type' => Transaction::TYPE_CREDIT,
                    'amount' => $payment->amount,
                    'description' => 'Payment from '.$payment->user->name,
                    'payment_id' => $payment->id,
                ]);

                $recipient->balance += $payment->amount;
                $recipient->save();

                event(new BalanceUpdated($recipient, $creditTransaction));
            }

            event(new PaymentStatusChanged($payment));
        });
    }
}
