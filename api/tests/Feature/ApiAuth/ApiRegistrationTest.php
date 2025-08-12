<?php

namespace Tests\Feature\ApiAuth;


use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ApiRegistrationTest extends TestCase
{
    use RefreshDatabase;


    public function test_a_user_registration_request_requires_an_email()
    {
        $attributes = User::factory()->raw(['email' => '']);

        $this->postJson(route('v1.signup'), $attributes)
            ->assertJsonValidationErrors('email');
    }

    public function test_a_user_registration_request_requires_email_but_cannot_accept_duplicate_email()
    {
        $user = User::factory()->create();
        $attributes = User::factory()->raw(['email' => $user->email]);

        $this->postJson(route('v1.signup'), $attributes)
            ->assertJsonValidationErrors('email');
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->postJson(route('v1.signup'),
            $user = User::factory()->raw([
                'firstname' => 'Ugbanawaji',
                'lastname' => 'Ekenekiso',
                'password' => 'password',
                'password_confirmation' => 'password',
            ])
        )->assertStatus(201);

        $response->assertJson(fn (AssertableJson $json) => $json
            ->where('status', true)
            ->where('message', 'Please verify your email address')
            ->where('data.firstname', 'Ugbanawaji')
            ->where('data.lastname', 'Ekenekiso')
            ->etc()
        );

    }

    public function test_new_users_can_register_and_email_verification_notice_sent(): void
    {

        $response = $this->postJson(route('v1.signup'),
            $user = User::factory()->raw([
                'firstname' => 'Ugbanawaji',
                'lastname' => 'Ekenekiso',
                'password' => 'password',
                'password_confirmation' => 'password',
            ])
        )->assertStatus(201);

        $this->assertDatabaseCount('users', 1);

        $user = User::find($response->json('data.id'));


    }

}
