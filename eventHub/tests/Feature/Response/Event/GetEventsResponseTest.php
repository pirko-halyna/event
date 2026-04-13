<?php

namespace Tests\Feature\Response\Event;

use App\Models\Category;
use App\Models\Event;
use App\Models\Location;
use App\Models\Organizer;
use App\Models\User;
use PHPUnit\Framework\Attributes\{Group, Test};
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Class GetEventsResponseTest
 */
#[Group('response')]
#[Group('event')]
class GetEventsResponseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $category1 = Category::factory()->create(['name' => 'Music']);
        $category2 = Category::factory()->create(['name' => 'Sports']);

        $locations = Location::factory(10)->create();
        $organizers = Organizer::factory(10)->create();

        $this->event1 = Event::factory()->create([
            'title' => 'Test Event',
            'description' => 'This is a test event description.',
            'is_online' => true,
            'location_id' => Arr::random($locations->all())->id,
            'organizer_id' => Arr::random($organizers->all())->id,
            'datetime_from' => Carbon::now()->subDays(1),
            'datetime_to' => Carbon::now()->addDays(1),
            'category_id' => $category1->id,
        ]);

        $this->event2 = Event::factory()->create([
            'title' => 'Another Event',
            'description' => 'This event has a different description.',
            'is_online' => false,
            'location_id' => Arr::random($locations->all())->id,
            'organizer_id' => Arr::random($organizers->all())->id,
            'datetime_from' => Carbon::now()->subDays(10),
            'datetime_to' => Carbon::now()->subDays(5),
            'category_id' => $category2->id
        ]);
    }


    #[Test]
    public function get_events_success()
    {
        Event::factory()->count(50)->create();

        $response = $this->getJson(route('events.index', ['page' => 1, 'per_page' => 15]));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'description',
                    'author' => [
                        'id',
                        'email',
                        'first_name',
                        'last_name',
                        'phone',
                        'created_at',
                        'updated_at',
                    ],
                    'organizer' => [
                        'id',
                        'title',
                        'description',
                        'image',
                        'created_at',
                        'updated_at',
                    ],
                    'datetime_from',
                    'datetime_to',
                    'location' => [
                        'id',
                        'name',
                        'address',
                        'created_at',
                        'updated_at',
                    ],
                    'category' => [
                        'id',
                        'name',
                        'order',
                        'created_at',
                        'updated_at',
                    ],
                    'is_online',
                    'price',
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

    public function it_filters_events_by_title()
    {
        $response = $this->json('GET', route('events.index', ['search' => $this->event1->title]));

        $response->assertOk()
            ->assertJsonFragment([
                'title' => $this->event1->title,
            ])
            ->assertJsonMissing([
                'title' => $this->event2->title, // Assuming $this->event2 is another event with a different title
            ]);
    }

    #[Test]
    public function it_filters_events_by_description()
    {
        $response = $this->json('GET', route('events.index', ['search' => 'test event description']));

        $response->assertOk()
            ->assertJsonFragment([
                'description' => $this->event1->description,
            ])
            ->assertJsonMissing([
                'description' => 'Another description' // Ensure this is not in the results
            ]);
    }

    #[Test]
    public function it_filters_events_by_is_online()
    {
        $response = $this->json('GET', route('events.index', ['is_online' => 1]));

        $response->assertOk()
            ->assertJsonFragment([
                'is_online' => true
            ])
            ->assertJsonMissing([
                'is_online' => false
            ]);
    }

    #[Test]
    public function it_filters_events_by_category()
    {
        $response = $this->json('GET', route('events.index', ['categories' => [$this->event1->category->id]]));

        $response->assertOk()
            ->assertJson([
                'data' => [
                    [
                        'category' => [
                            'id' => $this->event1->category->id
                        ]
                    ]
                ]
            ])
            ->assertJsonMissing([
                'data' => [
                    [
                        'category' => [
                            'id' => $this->event2->category->id
                        ]
                    ]
                ]
            ]);
    }

    #[Test]
    public function it_filters_events_by_date_range()
    {
        $datetimeFrom = Carbon::now()->subDays(2)->toDateTimeString();
        $datetimeTo = Carbon::now()->addDays(2)->toDateTimeString();

        $response = $this->json('GET', route('events.index', [
            'datetime_from' => $datetimeFrom,
            'datetime_to' => $datetimeTo,
        ]));

        $response->assertOk()
            ->assertJsonFragment([
                'title' => $this->event1->title
            ])
            ->assertJsonMissing([
                'title' => $this->event2->title
            ]);
    }

    #[Test]
    public function it_filters_events_by_location_id()
    {
        $response = $this->json('GET', route('events.index', ['locations' => [$this->event1->location->id]]));

        $response->assertOk()
            ->assertJson([
                'data' => [
                    [
                        'location' => [
                            'id' => $this->event1->location->id
                        ]
                    ]
                ]
            ])
            ->assertJsonMissing([
                'data' => [
                    [
                        'location' => [
                            'id' => $this->event2->location->id
                        ]
                    ]
                ]
            ]);
    }
}
