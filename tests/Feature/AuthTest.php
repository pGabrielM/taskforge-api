<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_authenticate()
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['user', 'token', 'expires_at']);
    }

    public function test_register_with_valid_data()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Gabriel Miranda',
            'email' => 'gabriel@example.com',
            'password' => 'password',
        ]);

        $response->assertJsonStructure(['name', 'email', 'updated_at', 'created_at', 'id']);
        $response->assertOk();
    }

    public function test_register_with_existing_email()
    {
        User::factory()->create([
            'name' => 'Gabriel Miranda',
            'email' => 'gabriel@example.com',
        ]);

        $response = $this->postJson('/api/register', [
            'name' => 'Gabriel Miranda',
            'email' => 'gabriel@example.com',
            'password' => 'password',
        ]);

        $response->assertInvalid('email');
        $response->assertUnprocessable();
    }

    public function test_register_with_invalid_email()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Gabriel Miranda',
            'email' => 'gabriel',
            'password' => 'password',
        ]);

        $response->assertInvalid('email');
        $response->assertUnprocessable();
    }

    public function test_register_with_short_password()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Gabriel Miranda',
            'email' => 'gabriel@example.com',
            'password' => 'passw',
        ]);

        $response->assertInvalid('password');
        $response->assertUnprocessable();
    }

    public function test_register_without_required_fields()
    {
        $response = $this->postJson('/api/register', [
            'email' => 'gabriel@example.com',
            'password' => 'passw',
        ]);

        $response->assertInvalid('name');
        $response->assertUnprocessable();

        $response = $this->postJson('/api/register', [
            'name' => 'Gabriel Miranda',
            'password' => 'passw',
        ]);

        $response->assertInvalid('email');
        $response->assertUnprocessable();

        $response = $this->postJson('/api/register', [
            'name' => 'Gabriel Miranda',
            'email' => 'gabriel@example.com',
        ]);

        $response->assertInvalid('password');
        $response->assertUnprocessable();
    }

    public function test_logout_with_valid_token()
    {
        $user = User::factory()->create();
        $token = $user->createToken('testToken')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/logout');

        $response->assertOk();
        $response->assertJson(['message' => 'Token revoked successfully']);
        $this->assertDatabaseMissing('personal_access_tokens', ['tokenable_id' => $user->id]);
    }

    public function test_logout_with_invalid_token()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalidToken',
        ])->postJson('/api/logout');

        $response->assertUnauthorized();
        $response->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_logout_without_token()
    {
        $response = $this->postJson('/api/logout');

        $response->assertUnauthorized();
        $response->assertJson(['message' => 'Unauthenticated.']);
    }

    public function test_logout_with_expired_token()
    {
        $user = User::factory()->create();
        $token = $user->createToken('testToken', ['*'], now()->subHour())->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/logout');

        $response->assertUnauthorized();
        $response->assertJson(['message' => 'Unauthenticated.']);
        $this->assertDatabaseHas('personal_access_tokens', ['tokenable_id' => $user->id]);
    }
}
