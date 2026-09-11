<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_task_routes_are_protected_from_public()
    {
        $response = $this->getJson('/api/user/task');
        $response->assertUnauthorized();

        $response = $this->getJson('/api/user/task/{id}');
        $response->assertUnauthorized();

        $response = $this->postJson('/api/user/task');
        $response->assertUnauthorized();

        $response = $this->putJson('/api/user/task/{id}');
        $response->assertUnauthorized();

        $response = $this->deleteJson('/api/user/task/{id}');
        $response->assertUnauthorized();
    }

    public function test_list_public_tasks()
    {
        $response = $this->getJson('/api/task');

        $response->assertJsonStructure(['data', 'links', 'meta']);

        $response->assertOk();
    }

    public function test_list_private_tasks()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/user/task');

        $response->assertJsonStructure(['data', 'links', 'meta']);

        $response->assertOk();
    }

    public function test_show_private_tasks()
    {
        $user = User::factory()->create();

        $task = Task::factory()->create();

        $response = $this->actingAs($user)->getJson("/api/user/task/{$task->id}");

        $response->assertJsonStructure(['data' => ['name', 'description', 'completed']]);

        $response->assertOk();
    }

    public function test_create_tasks()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/user/task', [
            'name' => 'Created Task',
            'description' => 'Created task description',
            'completed' => false,
        ]);

        $this->assertDatabaseHas('tasks', ['name' => 'Created Task']);

        $response->assertJsonStructure(['data']);
        $response->assertCreated();
    }

    public function test_create_tasks_without_required_fields()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/user/task', [
            'description' => 'Created task description',
            'completed' => false,
        ]);

        $response->assertInvalid('name');
        $response->assertUnprocessable();

        $response = $this->actingAs($user)->postJson('/api/user/task', [
            'name' => 'Created Task',
            'completed' => false,
        ]);

        $response->assertInvalid('description');
        $response->assertUnprocessable();

        $response = $this->actingAs($user)->postJson('/api/user/task', [
            'name' => 'Created Task',
            'description' => 'Created task description',
        ]);

        $response->assertInvalid('completed');
        $response->assertUnprocessable();
    }

    public function test_update_tasks()
    {
        $user = User::factory()->create();

        $task = Task::factory()->create();

        $response = $this->actingAs($user)->putJson("/api/user/task/{$task->id}", [
            'name' => 'Updated Task',
            'description' => 'Updated description',
            'completed' => false,
        ]);

        $this->assertDatabaseHas('tasks', ['id' => $task->id]);

        $response->assertJsonStructure(['data']);
        $response->assertOk();
    }

    public function test_update_tasks_without_required_fields()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create();

        $response = $this->actingAs($user)->putJson("/api/user/task/{$task->id}", [
            'description' => 'Created task description',
            'completed' => false,
        ]);

        $response->assertInvalid('name');
        $response->assertUnprocessable();

        $response = $this->actingAs($user)->putJson("/api/user/task/{$task->id}", [
            'name' => 'Created Task',
            'completed' => false,
        ]);

        $response->assertInvalid('description');
        $response->assertUnprocessable();

        $response = $this->actingAs($user)->putJson("/api/user/task/{$task->id}", [
            'name' => 'Created Task',
            'description' => 'Created task description',
        ]);

        $response->assertInvalid('completed');
        $response->assertUnprocessable();
    }

    public function test_user_can_delete_tasks()
    {
        $user = User::factory()->create();
        $task = Task::factory()->create();

        $response = $this->actingAs($user)->deleteJson("/api/user/task/{$task->id}");

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);

        $response->assertJson(['message' => 'Task deleted successfully']);
        $response->assertOk();
    }
}
