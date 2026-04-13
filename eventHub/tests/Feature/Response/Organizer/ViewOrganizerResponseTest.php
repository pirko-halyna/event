<?php

namespace Tests\Feature\Response\Organizer;

use App\Models\Organizer;
use PHPUnit\Framework\Attributes\{Group, Test};
use Tests\TestCase;

/**
 * Class ViewOrganizerResponseTest
 */
#[Group('response')]
#[Group('organizer')]
class ViewOrganizerResponseTest extends TestCase
{
    #[Test]
    public function view_organizer_success()
    {
        $organizer = Organizer::factory()->create();
        $response = $this->getJson(route('organizers.show', $organizer));
        $response->assertStatus(200);
        $response->assertJson([
            'id' => $organizer->id,
            'title' => $organizer->title,
            'description' => $organizer->description,
            'image' => $organizer->image,
            'created_at' => $organizer->created_at->format('Y-m-d\TH:i:s.u\Z'),
            'updated_at' => $organizer->updated_at->format('Y-m-d\TH:i:s.u\Z'),
        ]);
    }

    #[Test]
    public function view_organizer_not_found()
    {
        $response = $this->getJson(route('organizers.show', 999));

        $response->assertNotFound();

        $response->assertJson([
            'message' => 'Not found',
        ]);
    }
}
