<?php

namespace Tests\Feature\ApiAuth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class ApiAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_login_request_requires_an_email()
    {
        $attributes = User::factory()->raw(['email' => '']);

        $this->postJson(route('v1.login'), $attributes)->assertJsonValidationErrors('email');
    }

    public function test_a_user_login_request_requires_a_password()
    {
        $attributes = User::factory()->raw(['password' => '']);
        $this->postJson(route('v1.login'), $attributes)->assertJsonValidationErrors('password');
    }

    public function test_users_can_authenticate_using_the_api(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson(route('v1.login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertOk();

        $response->assertJson(fn (AssertableJson $json) => $json
            ->where('status', true)
            ->where('message', 'success')
            ->has('data.access_token')
            ->has('data.expires_in')
            ->has('data.token_type')
            ->etc()
        );

    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson(route('v1.login'), [
            'email' => $user->email,
            'password' => 'passwor',
        ])->assertStatus(422);

        $response->assertJson(fn (AssertableJson $json) => $json
            // ->where('status', false)
            ->where('message', 'These credentials do not match our records.')
            // ->has('data', 0)
            ->etc()
        );

    }
}
