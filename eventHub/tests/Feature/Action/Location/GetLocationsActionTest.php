<?php

namespace Tests\Feature\Action\Location;

use App\Models\Location;
use PHPUnit\Framework\Attributes\{Group, Test};
use Tests\TestCase;

/**
 * Class GetLocationsActionTest
 */
#[Group('action')]
#[Group('location')]
class GetLocationsActionTest extends TestCase
{
    #[Test]
    public function get_locations_has_pagination()
    {
        Location::factory()->count(50)->create();
        $response = $this->getJson(route('locations.index', ['page' => 1, 'per_page' => 15]));
        $data = $response->getData(true);

        $this->assertEquals(1, $data['meta']['current_page']);
        $this->assertCount(15, $data['data']);
    }
}
