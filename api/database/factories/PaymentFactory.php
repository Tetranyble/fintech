<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => User::factory(),
            'amount' => $this->faker->randomFloat(2, 1, 1000),
            'currency' => $this->faker->currencyCode(),
            'recipient' => $this->faker->name(),
            'description' => $this->faker->sentence(),
            'status' => $this->faker->randomElement(Payment::statuses()),
        ];
    }

    public function pending(): self
    {
        return $this->state([
            'status' => Payment::STATUS_PENDING,
        ]);
    }

    public function successful(): self
    {
        return $this->state([
            'status' => Payment::STATUS_SUCCESSFUL,
        ]);
    }

    public function failed(): self
    {
        return $this->state([
            'status' => Payment::STATUS_FAILED,
        ]);
    }

    public function refunded(): self
    {
        return $this->state([
            'status' => Payment::STATUS_REFUNDED,
        ]);
    }

    public function withCustomer(User $customer): self
    {
        return $this->state([
            'customer_id' => $customer->id,
        ]);
    }

    public function withAmount(float $amount): self
    {
        return $this->state([
            'amount' => $amount,
        ]);
    }
}
