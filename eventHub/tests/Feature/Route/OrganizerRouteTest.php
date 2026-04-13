<?php

namespace Tests\Feature\Route;

use PHPUnit\Framework\Attributes\{Group, Test};
use Illuminate\Support\Facades\Route;

/**
 * Class OrganizerRouteTest
 */
#[Group('route')]
#[Group('organizer')]
class OrganizerRouteTest extends BaseRouteTest
{
    #[Test]
    public function get_organizers_route_exists(): void
    {
        $this->assertRouteExists('organizers', 'organizers.index');
    }

    #[Test]
    public function view_organizer_route_exists(): void
    {
        $this->assertRouteExists('organizers/1', 'organizers.show', ['organizer' => 1]);
    }
}
