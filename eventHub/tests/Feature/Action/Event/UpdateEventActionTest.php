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
class UpdateEventActionTest extends TestCase
{
    #[Test]
    public function can_update_event(): void
    {
        $user  = User::factory()->create();
        $event = Event::factory()->create(['type' => 'free']);

        $this->mock(AuthService::class, function ($mock) use ($user) {
            $mock->shouldReceive('getUserByToken')->andReturn($user);
        });

        $response = $this->withHeader('Authorization', 'Bearer fake-token')
            ->putJson(route('events.update', $event), [
                'title' => 'Updated Title',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('events', [
            'id'    => $event->id,
            'title' => 'Updated Title',
        ]);
    }

    #[Test]
    public function returns_404_for_missing_event(): void
    {
        $user = User::factory()->create();

        $this->mock(AuthService::class, function ($mock) use ($user) {
            $mock->shouldReceive('getUserByToken')->andReturn($user);
        });

        $response = $this->withHeader('Authorization', 'Bearer fake-token')
            ->putJson(route('events.update', 99999), [
                'title' => 'Updated Title',
            ]);

        $response->assertStatus(404);
    }
}
