<?php

namespace Tests\Feature\Request;

use App\Http\Requests\IndexEventRequest;
use App\Models\Event;
use PHPUnit\Framework\Attributes\{Group, Test};
use Tests\TestCase;

#[Group('request')]
#[Group('event')]
class EventRequestTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();


        Event::factory()->count(5)->create();
    }

    #[Test]
    public function locations_must_be_array_with_integer()
    {
        $response = $this->getJson(route('events.index', [
            'locations' => ['not_number']
        ]));
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('locations.0');
    }

    #[Test]
    public function is_online_must_be_boolean()
    {
        $response = $this->getJson(route('events.index', [
            'is_online' => 'not_a_boolean',
        ]));
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('is_online');
    }

    #[Test]
    public function datetime_from_and_datetime_to_must_be_a_datetime()
    {
        $response = $this->getJson(route('events.index', [
            'datetime_from' => 'invalid_date',
            'datetime_to' => 'invalid_date',
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['datetime_from', 'datetime_to']);
    }

    #[Test]
    public function organizer_id_must_be_a_integer()
    {
        $response = $this->getJson(route('events.index', [
            'organizer_id' => 'not_an_integer',
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('organizer_id');
    }

    #[Test]
    public function categories_must_be_array_with_integer()
    {
        $response = $this->getJson(route('events.index', [
            'categories' => [1, 'two', 3],
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('categories.1');
    }

    #[Test]
    public function search_must_be_a_string()
    {
        $response = $this->getJson(route('events.index', [
            'search' => ['not-a-string'],
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('search');
    }
}
