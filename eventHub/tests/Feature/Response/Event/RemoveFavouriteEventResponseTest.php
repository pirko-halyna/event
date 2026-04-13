<?php

namespace Feature\Response\Event;

use App\Models\Event;
use App\Models\User;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('response')]
#[Group('event')]
class RemoveFavouriteEventResponseTest extends TestCase
{
    #[Test]
    public function user_can_remove_event_from_favourite()
    {
        $user = User::factory()->create();

        $event = Event::factory()->create();

        $token = auth()->login($user);

        $user->favouriteEvents()->attach($event->id);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson(route('events.remove-favourite', ['event' => $event->id]));

        $response->assertOk()
            ->assertJson(['message' => 'Event removed from favourite list!']);

        $this->assertDatabaseMissing('favourites', [
            'user_id' => $user->id,
            'event_id' => $event->id,
        ]);
    }
}
