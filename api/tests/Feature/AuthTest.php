<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected string $baseUrl = '/api/v1';

    // ================================================
    // Register
    // ================================================

    public function test_user_can_register_with_valid_data(): void
    {
        $payload = [
            'name'                  => 'John Doe',
            'email'                 => 'john@example.com',
            'password'              => 'secret123',
            'password_confirmation' => 'secret123',
        ];

        $response = $this->postJson("{$this->baseUrl}/register/users", $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'user' => ['id', 'name', 'email'],
                    'token' => ['access_token', 'refresh_token', 'token_type', 'expires_in'],
                    'permissions',
                    'roles',
                ],
            ]);
    }

    public function test_register_fails_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'john@example.com']);

        $payload = [
            'name'                  => 'John Clone',
            'email'                 => 'john@example.com',
            'password'              => 'secret123',
            'password_confirmation' => 'secret123',
        ];

        $response = $this->postJson("{$this->baseUrl}/register/users", $payload);

        $response->assertStatus(422);
    }

    public function test_register_fails_with_missing_fields(): void
    {
        $response = $this->postJson("{$this->baseUrl}/register/users", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_register_fails_when_password_confirmation_mismatch(): void
    {
        $payload = [
            'name'                  => 'Jane Doe',
            'email'                 => 'jane@example.com',
            'password'              => 'secret123',
            'password_confirmation' => 'wrongpassword',
        ];

        $response = $this->postJson("{$this->baseUrl}/register/users", $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    // ================================================
    // Login
    // ================================================

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email'    => 'test@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->postJson("{$this->baseUrl}/login", [
            'email'    => 'test@example.com',
            'password' => 'secret123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'user'  => ['id', 'name', 'email'],
                    'token' => ['access_token', 'refresh_token', 'token_type', 'expires_in'],
                ],
            ]);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create([
            'email'    => 'test@example.com',
            'password' => bcrypt('correct'),
        ]);

        $response = $this->postJson("{$this->baseUrl}/login", [
            'email'    => 'test@example.com',
            'password' => 'wrong',
        ]);

        $response->assertStatus(401)
            ->assertJson(['status' => 'error']);
    }

    public function test_login_fails_with_nonexistent_email(): void
    {
        $response = $this->postJson("{$this->baseUrl}/login", [
            'email'    => 'nobody@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(401);
    }

    public function test_login_fails_with_missing_fields(): void
    {
        $response = $this->postJson("{$this->baseUrl}/login", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    // ================================================
    // Me (Authenticated)
    // ================================================

    public function test_authenticated_user_can_get_their_info(): void
    {
        $user  = User::factory()->create();
        $token = auth('api')->login($user);

        $response = $this->withToken($token)->getJson("{$this->baseUrl}/me");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.user.email', $user->email);
    }

    public function test_unauthenticated_user_cannot_get_me(): void
    {
        $response = $this->getJson("{$this->baseUrl}/me");

        $response->assertStatus(401);
    }

    // ================================================
    // Logout
    // ================================================

    public function test_authenticated_user_can_logout(): void
    {
        $user  = User::factory()->create();
        $token = auth('api')->login($user);

        $response = $this->withToken($token)->postJson("{$this->baseUrl}/logout");

        $response->assertStatus(200)
            ->assertJson(['status' => 'success']);
    }
}
