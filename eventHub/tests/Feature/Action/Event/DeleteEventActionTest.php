<?php

namespace Tests\Feature\Action\Event;

use App\Models\Event;
use App\Models\User;
use App\Services\AuthService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[Group('action')]
#[Group('events')]
class DeleteEventActionTest extends TestCase
{
    #[Test]
    public function can_delete_event(): void
    {
        $user  = User::factory()->create();
        $event = Event::factory()->create(['type' => 'free']);

        $this->mock(AuthService::class, function ($mock) use ($user) {
            $mock->shouldReceive('getUserByToken')->andReturn($user);
        });

        $response = $this->withHeader('Authorization', 'Bearer fake-token')
            ->deleteJson(route('events.destroy', $event));

        $response->assertStatus(204);

        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    }

    #[Test]
    public function returns_404_for_missing_event(): void
    {
        $user = User::factory()->create();

        $this->mock(AuthService::class, function ($mock) use ($user) {
            $mock->shouldReceive('getUserByToken')->andReturn($user);
        });

        $response = $this->withHeader('Authorization', 'Bearer fake-token')
            ->deleteJson(route('events.destroy', 99999));

        $response->assertStatus(404);
    }
}
