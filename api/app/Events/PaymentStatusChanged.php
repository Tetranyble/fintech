<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentStatusChanged implements ShouldBroadcast, ShouldQueue
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $paymentId;
    public $status;
    public $timestamp;
    public $customerId;

    public function __construct($paymentId, $status, $timestamp, $customerId)
    {
        $this->paymentId = $paymentId;
        $this->status = $status;
        $this->timestamp = $timestamp;
        $this->customerId = $customerId;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('customer.' . $this->customerId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'payment.status.changed';
    }
}
