<?php

namespace Feature;

use App\Models\Task;
use App\Models\User;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    public function testListingUserTasks(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $this->actingAs($user);

        $incomplete = Task::factory()->count(3)->create(['completed_at' => null, 'user_id' => $user->id]);
        $complete = Task::factory()->count(3)->create(['completed_at' => now(), 'user_id' => $user->id]);
        $different = Task::factory()->count(4)->create(['completed_at' => null, 'user_id' => $otherUser->id]);


        $incomplete = $incomplete->sortBy('title')
            ->values()
            ->map(fn ($task) => ['id' => $task->id])
            ->all();

        $complete = $complete->sortBy('title')
            ->values()
            ->map(fn ($task) => ['id' => $task->id])
            ->all();

        $this->assertDatabaseCount(Task::class, 10);

        // order by completion, then alpha
        $this->getJson(route('tasks.index'))
            ->assertSuccessful()
            ->assertJsonCount(6, 'data')
            ->assertJson([
                'data' => [
                    ...$incomplete,
                    ...$complete,
                ]
            ])
            ->assertJsonMissing([
                'data' => $different->sortBy('title')
                    ->values()
                    ->map(fn ($task) => ['id' => $task->id])
                    ->all()
            ]);
    }

    public function testCreatingUpdatingAndDeletingTask(): void
    {
        $user = User::factory()->create();
        $payload = [
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
        ];

        $this->postJson(route('tasks.store'), $payload)->assertUnauthorized();
        $this->assertDatabaseMissing(Task::class, $payload);

        $this->actingAs($user);

        $this->postJson(route('tasks.store'), ['description' => 'we are omitting title.'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title']);

        $this->postJson(route('tasks.store'), $payload)
            ->assertSuccessful()
            ->assertJson([
                'data' => $payload,
            ]);

        $this->assertDatabaseHas(Task::class, [
            ...$payload,
            'user_id' => $user->id,
        ]);

        $created = Task::where('title', $payload['title'])->where('user_id', $user->id)->firstOrFail();

        $this->putJson(route('tasks.update', ['task' => $created->id]), ['description' => 'we are omitting title.'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title']);

        $payload['title'] = 'Updated title';

        $this->putJson(route('tasks.update', ['task' => $created->id]), $payload)
            ->assertSuccessful();

        $this->assertDatabaseHas(Task::class, [
            'id' => $created->id,
            'title' => 'Updated title',
            'user_id' => $user->id,
        ]);

        $otherUser = User::factory()->create();
        $otherTask = Task::factory()->create(['user_id' => $otherUser->id]);

        $this->assertDatabaseHas(Task::class, [
            'id' => $otherTask->id,
            'user_id' => $otherUser->id,
        ]);

        $this->putJson(route('tasks.update', ['task' => $otherTask->id]), ['title' => 'Editing outside user', 'description' => 'yes we are'])
            ->assertNotFound();

        $this->getJson(route('tasks.show', ['task' => $otherTask->id]))
            ->assertNotFound();

        $this->getJson(route('tasks.show', ['task' => $created->id]))
            ->assertSuccessful()
            ->assertJson([
                'data' => [
                    'id' => $created->id,
                    ...$payload,
                ]
            ]);

        $this->assertDatabaseHas(Task::class, [
            'id' => $otherTask->id,
            'user_id' => $otherUser->id,
        ]);

        $this->deleteJson(route('tasks.destroy', ['task' => $otherTask->id]))
            ->assertNotFound();

        $this->assertDatabaseHas(Task::class, [
            'id' => $otherTask->id,
            'user_id' => $otherUser->id,
        ]);

        $this->assertDatabaseHas(Task::class, [
            'id' => $created->id,
            'user_id' => $user->id,
        ]);

        $this->deleteJson(route('tasks.destroy', ['task' => $created->id]))
            ->assertSuccessful();

        $this->assertDatabaseMissing(Task::class, [
            'id' => $created->id,
            'user_id' => $user->id,
        ]);
    }
}
