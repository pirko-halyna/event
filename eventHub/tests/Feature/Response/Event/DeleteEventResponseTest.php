<?php

namespace Tests\Feature\Response\Event;

use App\Models\Event;
use App\Models\User;
use App\Services\AuthService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[Group('response')]
#[Group('event')]
class DeleteEventResponseTest extends TestCase
{
    #[Test]
    public function delete_event_returns_no_content(): void
    {
        $user  = User::factory()->create();
        $event = Event::factory()->create(['type' => 'free']);

        $this->mock(AuthService::class, function ($mock) use ($user) {
            $mock->shouldReceive('getUserByToken')->andReturn($user);
        });

        $response = $this->withHeader('Authorization', 'Bearer fake-token')
            ->deleteJson(route('events.destroy', $event));

        $response->assertStatus(204)->assertNoContent();
    }

    #[Test]
    public function delete_event_returns_not_found_for_missing_event(): void
    {
        $user = User::factory()->create();

        $this->mock(AuthService::class, function ($mock) use ($user) {
            $mock->shouldReceive('getUserByToken')->andReturn($user);
        });

        $response = $this->withHeader('Authorization', 'Bearer fake-token')
            ->deleteJson(route('events.destroy', 99999));

        $response->assertStatus(404);
        $response->assertJson(['message' => 'Not found']);
    }
}
