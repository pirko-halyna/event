<?php

namespace Feature\Response\Event;

use App\Models\Event;
use App\Models\User;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[Group('response')]
#[Group('event')]
class AddFavouriteEventResponseTest extends TestCase
{
    #[Test]
    public function add_event_to_favourite_successfully()
    {
        $user = User::factory()->create();

        $event = Event::factory()->create();

        $token = auth()->login($user);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson(route('events.add-favourite', ['event' => $event->id]));

        $response->assertOk()
            ->assertJson(['message' => 'Event added to favourite']);

        $this->assertDatabaseHas('favourites', [
            'user_id' => $user->id,
            'event_id' => $event->id,
        ]);
    }
}
