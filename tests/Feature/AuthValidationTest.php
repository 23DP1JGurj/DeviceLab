<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_requires_valid_phone_and_surname(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Client User',
            'email' => 'client@example.test',
            'phone' => 'phone123',
            'password' => 'ValidPass123!',
            'password_confirmation' => 'ValidPass123!',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['last_name', 'phone']);
    }

    public function test_registration_creates_client_with_strong_password(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Client User',
            'last_name' => 'User',
            'email' => 'client@example.test',
            'phone' => '+37120000000',
            'password' => 'ValidPass123!',
            'password_confirmation' => 'ValidPass123!',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('user.role', User::ROLE_CLIENT);

        $this->assertDatabaseHas('users', [
            'email' => 'client@example.test',
            'role' => User::ROLE_CLIENT,
            'phone' => '+37120000000',
        ]);

        $this->assertAuthenticated();
    }

    public function test_registration_reports_password_confirmation_on_mismatch(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Client User',
            'last_name' => 'User',
            'email' => 'client@example.test',
            'phone' => '37120000000',
            'password' => 'ValidPass123!',
            'password_confirmation' => 'ValidPass123',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['password_confirmation'])
            ->assertJsonMissingValidationErrors(['password']);
    }
}
