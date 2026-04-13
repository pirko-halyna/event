<?php

namespace Tests\Feature\Route;

use PHPUnit\Framework\Attributes\{Group, Test};
use Illuminate\Support\Facades\Route;

/**
 * Class EventRouteTest
 */
#[Group('route')]
#[Group('event')]
class EventRouteTest extends BaseRouteTest
{
    #[Test]
    public function events_route_exists(): void
    {
        $this->assertRouteExists('events', 'events.index');
    }

    #[Test]
    public function add_favourite_event_route_exists(): void
    {
        $this->assertTrue(
            Route::has('events.add-favourite'),
            'Route "events.add-favourite" does not exist.'
        );
    }

    #[Test]
    public function remove_favourite_event_route_exists(): void
    {
        $this->assertTrue(
            Route::has('events.remove-favourite'),
            'Route "events.remove-favourite" does not exist.'
        );
    }
}
