<?php

namespace Tests\Feature\Route;

use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('route')]
#[Group('ticket-type')]
class EventTicketTypeRouteTest extends BaseRouteTest
{
    #[Test]
    public function index_ticket_types_route_exists(): void
    {
        $this->assertTrue(Route::has('events.ticket-types.index'));
    }

    #[Test]
    public function store_ticket_type_route_exists(): void
    {
        $this->assertTrue(Route::has('events.ticket-types.store'));
    }

    #[Test]
    public function update_ticket_type_route_exists(): void
    {
        $this->assertTrue(Route::has('events.ticket-types.update'));
    }

    #[Test]
    public function destroy_ticket_type_route_exists(): void
    {
        $this->assertTrue(Route::has('events.ticket-types.destroy'));
    }
}
