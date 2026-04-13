<?php

namespace Tests\Feature\Route;

use PHPUnit\Framework\Attributes\{Group, Test};

/**
 * Class LocationRouteTest
 */
#[Group('route')]
#[Group('location')]
class LocationRouteTest extends BaseRouteTest
{
    #[Test]
    public function locations_route_exists(): void
    {
        $this->assertRouteExists('locations', 'locations.index');
    }
}
