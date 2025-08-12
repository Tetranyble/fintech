<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'payment_id' => Payment::factory(),
            'type' => $this->faker->randomElement(Transaction::types()),
            'amount' => $this->faker->randomFloat(2, 1, 1000),
            'description' => $this->faker->sentence(),
        ];
    }

    public function credit(): self
    {
        return $this->state([
            'type' => Transaction::TYPE_CREDIT,
        ]);
    }

    public function debit(): self
    {
        return $this->state([
            'type' => Transaction::TYPE_DEBIT,
        ]);
    }

    public function withUser(User $user): self
    {
        return $this->state([
            'user_id' => $user->id,
        ]);
    }

    public function withPayment(Payment $payment): self
    {
        return $this->state([
            'payment_id' => $payment->id,
        ]);
    }

    public function withAmount(float $amount): self
    {
        return $this->state([
            'amount' => $amount,
        ]);
    }
}
