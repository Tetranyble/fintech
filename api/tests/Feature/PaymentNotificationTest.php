<?php

namespace Tests\Feature;

use App\Events\PaymentStatusChanged;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class PaymentNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_event_dispatched_on_status_change()
    {
        Event::fake();
        $user = User::factory()->create();
        $payment = Payment::factory()->create(['customer_id' => $user->id, 'status' => 'pending']);

        // Simulate status change
        $payment->update(['status' => 'successful']);

        event(new PaymentStatusChanged($payment->id, 'successful', now(), $user->id));

        Event::assertDispatched(PaymentStatusChanged::class, function ($event) use ($payment, $user) {
            return $event->paymentId === $payment->id && $event->customerId === $user->id;
        });
    }

    public function test_channel_authentication()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/broadcasting/auth', [
            'channel_name' => 'customer.'.$user->id,
        ]);

        $response->assertStatus(200);
    }

    public function test_unauthorized_channel_access()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/broadcasting/auth', [
            'channel_name' => 'customer.'.($user->id + 1),
        ]);

        $response->assertStatus(403);
    }
}
