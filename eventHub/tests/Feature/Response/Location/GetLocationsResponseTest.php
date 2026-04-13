<?php

namespace Tests\Feature\Response\Location;

use App\Models\Location;
use PHPUnit\Framework\Attributes\{Group, Test};
use Tests\TestCase;

/**
 * Class LocationResponseTest
 */
#[Group('response')]
#[Group('location')]
class GetLocationsResponseTest extends TestCase
{
    #[Test]
    public function get_locations_successfully()
    {
        Location::factory()->count(50)->create();

        $response = $this->getJson(route('locations.index', ['page' => 1, 'per_page' => 15]));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'address',
                    'created_at',
                    'updated_at',
                ],
            ],
            'links' => [
                'first',
                'last',
                'prev',
                'next',
            ],
            'meta' => [
                'current_page',
                'from',
                'last_page',
                'links',
                'path',
                'per_page',
                'to',
                'total',
            ],
        ]);
    }
}
