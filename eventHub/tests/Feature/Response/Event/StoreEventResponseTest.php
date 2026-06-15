<?php

namespace Tests\Feature\Response\Event;

use App\Models\Category;
use App\Models\Organizer;
use App\Models\User;
use App\Services\AuthService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[Group('response')]
#[Group('event')]
class StoreEventResponseTest extends TestCase
{
    #[Test]
    public function store_event_returns_created_event_payload(): void
    {
        $user      = User::factory()->create();
        $organizer = Organizer::factory()->create();
        $category  = Category::factory()->create();

        $this->mock(AuthService::class, function ($mock) use ($user) {
            $mock->shouldReceive('getUserByToken')->andReturn($user);
        });

        $response = $this->withHeader('Authorization', 'Bearer fake-token')
            ->postJson(route('events.store'), [
                'title'         => 'Conference 2025',
                'type'          => 'free',
                'datetime_from' => now()->addDay()->toDateTimeString(),
                'datetime_to'   => now()->addDays(2)->toDateTimeString(),
                'organizer_id'  => $organizer->id,
                'category_id'   => $category->id,
                'capacity'      => 50,
            ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'description',
                'type',
                'datetime_from',
                'datetime_to',
                'is_online',
                'capacity',
                'created_at',
                'updated_at',
            ],
        ]);
        $response->assertJsonFragment(['title' => 'Conference 2025']);
        $response->assertJsonFragment(['type' => 'free']);
        $response->assertJsonFragment(['capacity' => 50]);
    }
}
