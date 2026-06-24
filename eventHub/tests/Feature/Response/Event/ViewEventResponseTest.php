<?php

namespace Tests\Feature\Response\Event;

use App\Models\Event;
use PHPUnit\Framework\Attributes\{Group, Test};
use Tests\TestCase;

/**
 * Class ViewEventResponseTest
 */
#[Group('response')]
#[Group('event')]
class ViewEventResponseTest extends TestCase
{
    #[Test]
    public function view_event_success()
    {
        $event = Event::factory()->withRelations()->create();

        $response = $this->getJson(route('events.show', $event));
        $response->assertStatus(200);
        $response->assertJson([
            'id' => $event->id,
            'title' => $event->title,
            'description' => $event->description,
            'author' => [
                'id' => $event->author->id,
                'email' => $event->author->email,
                'first_name' => $event->author->first_name,
                'last_name' => $event->author->last_name,
                'phone' => $event->author->phone,
                'created_at' => $event->author->created_at->toJSON(),
                'updated_at' => $event->author->updated_at->toJSON(),
            ],
            'category' => [
                'id' => $event->category->id,
                'name' => $event->category->name,
                'order' => $event->category->order,
                'created_at' => $event->category->created_at->format('Y-m-d\TH:i:s.u\Z'),
                'updated_at' => $event->category->updated_at->format('Y-m-d\TH:i:s.u\Z'),
            ],
            'location' => [
                'id' => $event->location->id,
                'name' => $event->location->name,
                'address' => $event->location->address,
                'created_at' => $event->location->created_at->format('Y-m-d\TH:i:s.u\Z'),
                'updated_at' => $event->location->updated_at->format('Y-m-d\TH:i:s.u\Z'),
            ],
            'organizer' => [
                'id' => $event->organizer->id,
                'title' => $event->organizer->title,
                'description' => $event->organizer->description,
                'image' => $event->organizer->image,
                'created_at' => $event->organizer->created_at->format('Y-m-d\TH:i:s.u\Z'),
                'updated_at' => $event->organizer->updated_at->format('Y-m-d\TH:i:s.u\Z'),
            ],
            'is_online' => $event->is_online,
            'created_at' => $event->created_at->format('Y-m-d\TH:i:s.u\Z'),
            'updated_at' => $event->updated_at->format('Y-m-d\TH:i:s.u\Z'),
        ]);
    }

    #[Test]
    public function view_event_not_found()
    {
        $response = $this->getJson(route('events.show', 999));

        $response->assertNotFound();

        $response->assertJson([
            'message' => 'Not found',
        ]);
    }
}
