<?php

namespace Feature\Response\User;

use App\Models\Event;
use App\Models\User;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

/**
 * Class GetFavouriteEventsResponseTest
 */
#[Group('response')]
#[Group('users')]
class GetFavouriteEventsResponseTest extends TestCase
{
    public function test_get_favourite_events_successfully()
    {
        $user = User::factory()->create();

        $events = Event::factory()->count(2)->create();

        $user->favouriteEvents()->attach($events->pluck('id'));

        $token = auth()->login($user);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson(route('users.favourite-events'));

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['id' => $events[0]->id])
            ->assertJsonFragment(['is_favourite' => true]);
    }
}
