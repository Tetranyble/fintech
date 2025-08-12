<?php

namespace App\Events;

use App\Models\Payment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentStatusChanged implements ShouldBroadcast, ShouldQueue
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Payment $payment) {}

    public function broadcastOn(): Channel
    {
        return new Channel('payment.'.$this->payment->id);
    }

    public function broadcastAs(): string
    {
        return 'payment.status.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'payment_id' => $this->payment->id,
            'status' => $this->payment->status,
            'message' => $this->getStatusMessage(),
        ];
    }

    protected function getStatusMessage(): string
    {
        return match ($this->payment->status) {
            'pending' => 'Payment initiated',
            'processing' => 'Payment being processed',
            'completed' => 'Payment processed successfully',
            default => 'Payment status updated',
        };
    }
}
