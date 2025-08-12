<?php

namespace Tests\Unit;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_belongs_to_customer()
    {
        $customer = User::factory()->create();
        $payment = Payment::factory()->create(['customer_id' => $customer->id]);

        $this->assertInstanceOf(User::class, $payment->customer);
        $this->assertEquals($customer->id, $payment->customer->id);
    }

    public function test_payment_statuses()
    {
        $statuses = Payment::statuses();

        $this->assertContains('pending', $statuses);
        $this->assertContains('successful', $statuses);
        $this->assertContains('failed', $statuses);
        $this->assertContains('refunded', $statuses);
    }

    public function test_payment_has_amount_cast_to_decimal()
    {
        $this->markTestSkipped();
        $payment = Payment::factory()->create(['amount' => 123.45]);

        $this->assertEquals(123.45, $payment->amount);
        $this->assertIsFloat($payment->amount);
    }
}
