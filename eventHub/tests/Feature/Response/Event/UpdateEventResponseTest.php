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
class UpdateEventResponseTest extends TestCase
{
    #[Test]
    public function update_event_returns_updated_event_payload(): void
    {
        $user  = User::factory()->create();
        $event = Event::factory()->create(['title' => 'Original Title', 'type' => 'free']);

        $this->mock(AuthService::class, function ($mock) use ($user) {
            $mock->shouldReceive('getUserByToken')->andReturn($user);
        });

        $response = $this->withHeader('Authorization', 'Bearer fake-token')
            ->putJson(route('events.update', $event), [
                'title' => 'Updated Title',
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'type',
                'datetime_from',
                'datetime_to',
                'is_online',
                'capacity',
                'created_at',
                'updated_at',
            ],
        ]);
        $response->assertJsonFragment(['title' => 'Updated Title']);
    }

    #[Test]
    public function update_event_returns_not_found_for_missing_event(): void
    {
        $user = User::factory()->create();

        $this->mock(AuthService::class, function ($mock) use ($user) {
            $mock->shouldReceive('getUserByToken')->andReturn($user);
        });

        $response = $this->withHeader('Authorization', 'Bearer fake-token')
            ->putJson(route('events.update', 99999), ['title' => 'Title']);

        $response->assertStatus(404);
        $response->assertJson(['message' => 'Not found']);
    }
}
