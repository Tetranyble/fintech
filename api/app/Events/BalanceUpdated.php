<?php

namespace App\Events;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BalanceUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public User $user, public Transaction $transaction) {}

    public function broadcastOn(): Channel
    {
        return new Channel('customer.'.$this->user->id);
    }

    public function broadcastAs(): string
    {
        return 'balance.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'balance' => $this->user->balance,
            'change' => $this->transaction->type === Transaction::TYPE_CREDIT
                ? $this->transaction->amount
                : -$this->transaction->amount,
            'transaction' => [
                'id' => $this->transaction->id,
                'type' => $this->transaction->type,
                'amount' => $this->transaction->amount,
                'description' => $this->transaction->description,
            ],
        ];
    }
}
