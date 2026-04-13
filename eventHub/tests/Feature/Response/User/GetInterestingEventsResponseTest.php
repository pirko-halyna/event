<?php

namespace Feature\Response\User;

use App\Models\Category;
use App\Models\Event;
use App\Models\Location;
use App\Models\Organizer;
use App\Models\User;
use App\Services\AuthService;
use PHPUnit\Framework\Attributes\{Group, Test};
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Class GetInterestingEventsResponseTest
 */
#[Group('response')]
#[Group('users')]
class GetInterestingEventsResponseTest extends TestCase
{
    public function get_interesting_events_successfully()
    {
        $user = User::factory()->create();

        $events = Event::factory()->count(2)->create();

        $user->interestedEvents()->attach($events->pluck('id'));

        $token = auth()->login($user);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson(route('users.interesting-events'));

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['id' => $events[0]->id]);
    }
}
