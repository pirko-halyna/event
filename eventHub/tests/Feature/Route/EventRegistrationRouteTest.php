<?php

namespace Feature\Route;

use PHPUnit\Framework\Attributes\{Group, Test};
use Tests\Feature\Route\BaseRouteTest;

/**
 * Class EventRegistrationRouteTest
 */
#[Group('route')]
#[Group('event-registration')]
class EventRegistrationRouteTest extends BaseRouteTest
{
    #[Test]
    public function event_registration_route_exists(): void
    {
        $this->assertRouteExists('events/1/registrations', 'events.register', ['event' => 1]);
    }
}
