<?php

namespace Tests\Feature\Response\Organizer;

use App\Models\Organizer;
use PHPUnit\Framework\Attributes\{Group, Test};
use Tests\TestCase;

/**
 * Class GetOrganizersResponseTest
 */
#[Group('response')]
#[Group('organizer')]
class GetOrganizersResponseTest extends TestCase
{
    #[Test]
    public function get_organizers_successfully()
    {
        Organizer::factory()->count(50)->create();

        $response = $this->getJson(route('organizers.index', ['page' => 1, 'per_page' => 15]));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'description',
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
