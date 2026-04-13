<?php

namespace Feature\Route;

use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\Feature\Route\BaseRouteTest;

#[Group('route')]
#[Group('user')]
class UserRouteTest extends BaseRouteTest
{
    #[Test]
    public function favourite_events_route_exists(): void
    {
        $this->assertTrue(
            Route::has('users.favourite-events'),
            'Route "users.favourite-events" does not exist.'
        );
    }
}
