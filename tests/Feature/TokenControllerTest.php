<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class TokenControllerTest extends TestCase
{
    public function testAcquiringToken(): void
    {
        $user = User::factory()->create();

        $this->postJson(route('auth.token.store'), ['email' => $user->email, 'password' => 'incorrect'])
            ->assertUnauthorized();

        $this->postJson(route('auth.token.store'), ['email' => $user->email, 'password' => 'password'])
            ->assertSuccessful()
            ->assertJsonStructure([
                'data' => ['token']
            ]);
    }
}
