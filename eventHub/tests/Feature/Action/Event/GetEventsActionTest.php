<?php

namespace Tests\Feature\Action\Event;

use App\Models\Event;
use PHPUnit\Framework\Attributes\{Group, Test};
use Tests\TestCase;

/**
 * Class GetEventsActionTest
 */
#[Group('action')]
#[Group('events')]
class GetEventsActionTest extends TestCase
{
    #[Test]
    public function get_events_has_pagination()
    {
        Event::factory()->count(50)->create();
        $response = $this->getJson(route('events.index', ['page' => 1, 'per_page' => 15]));

        $data = $response->getData(true);

        $this->assertEquals(1, $data['meta']['current_page']);
        $this->assertCount(15, $data['data']);
    }
}
