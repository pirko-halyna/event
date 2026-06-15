<?php

namespace Tests\Feature\Action\Event;

use App\Models\Category;
use App\Models\Organizer;
use App\Models\User;
use App\Services\AuthService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[Group('action')]
#[Group('events')]
class StoreEventActionTest extends TestCase
{
    #[Test]
    public function can_create_free_event(): void
    {
        $user      = User::factory()->create();
        $organizer = Organizer::factory()->create();
        $category  = Category::factory()->create();

        $this->mock(AuthService::class, function ($mock) use ($user) {
            $mock->shouldReceive('getUserByToken')->andReturn($user);
        });

        $response = $this->withHeader('Authorization', 'Bearer fake-token')
            ->postJson(route('events.store'), [
                'title'         => 'Free Event',
                'type'          => 'free',
                'datetime_from' => now()->addDay()->toDateTimeString(),
                'datetime_to'   => now()->addDays(2)->toDateTimeString(),
                'organizer_id'  => $organizer->id,
                'category_id'   => $category->id,
                'capacity'      => 100,
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('events', [
            'title'     => 'Free Event',
            'type'      => 'free',
            'capacity'  => 100,
            'author_id' => $user->id,
        ]);
    }

    #[Test]
    public function can_create_paid_event(): void
    {
        $user      = User::factory()->create();
        $organizer = Organizer::factory()->create();
        $category  = Category::factory()->create();

        $this->mock(AuthService::class, function ($mock) use ($user) {
            $mock->shouldReceive('getUserByToken')->andReturn($user);
        });

        $response = $this->withHeader('Authorization', 'Bearer fake-token')
            ->postJson(route('events.store'), [
                'title'         => 'Paid Event',
                'type'          => 'paid',
                'datetime_from' => now()->addDay()->toDateTimeString(),
                'datetime_to'   => now()->addDays(2)->toDateTimeString(),
                'organizer_id'  => $organizer->id,
                'category_id'   => $category->id,
                'capacity'      => null,
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('events', [
            'title'    => 'Paid Event',
            'type'     => 'paid',
            'capacity' => null,
        ]);
    }
}
