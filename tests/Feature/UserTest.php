<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_show_returns_user()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/user');

        $response->assertOk();
        $response->assertJsonStructure(['id', 'name', 'email', 'created_at', 'updated_at']);
        $response->assertJson(['id' => $user->id, 'name' => $user->name, 'email' => $user->email]);
    }

    public function test_show_returns_unauthorized()
    {
        $response = $this->getJson('/api/user');

        $response->assertUnauthorized();
    }

    public function test_update_user_name()
    {
        $user = User::factory()->create([
            'name' => 'Gabriel Miranda',
            'email' => 'gabriel@example.com',
        ]);

        $response = $this->actingAs($user)->putJson('/api/user', [
            'name' => 'Gabriel Miranda Test',
        ]);

        $response->assertJsonStructure(['name', 'email', 'updated_at', 'created_at', 'id']);
        $response->assertOk();
    }

    public function test_update_user_with_existing_email()
    {
        User::factory()->create([
            'email' => 'existent@email.com',
        ]);

        $user = User::factory()->create([
            'email' => 'gabriel@example.com',
        ]);

        $response = $this->actingAs($user)->putJson('/api/user', [
            'email' => 'existent@email.com',
        ]);

        $response->assertInvalid('email');
        $response->assertUnprocessable();
    }

    public function test_update_user_with_invalid_email()
    {
        $user = User::factory()->create([
            'email' => 'gabriel@example.com',
        ]);

        $response = $this->actingAs($user)->putJson('/api/user', [
            'email' => 'existent',
        ]);

        $response->assertInvalid('email');
        $response->assertUnprocessable();
    }

    public function test_user_can_delete_your_account()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->deleteJson('/api/user');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);

        $response->assertJson(['message' => 'Successfully deleted account']);
        $response->assertOk();
    }
}
