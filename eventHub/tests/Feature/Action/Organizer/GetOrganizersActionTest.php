<?php

namespace Tests\Feature\Action\Organizer;

use App\Models\Organizer;
use App\Models\User;
use Carbon\Carbon;
use Database\Factories\OrganizerFactory;
use Illuminate\Support\{Facades\Hash, Str};
use PHPUnit\Framework\Attributes\{Group, Test};
use Tests\TestCase;

/**
 * Class GetOrganizersActionTest
 */
#[Group('action')]
#[Group('organizer')]
class GetOrganizersActionTest extends TestCase
{
    #[Test]
    public function get_organizers_has_pagination()
    {
        Organizer::factory()->count(40)->create();
        $response = $this->getJson(route('organizers.index', ['page' => 1, 'per_page' => 15]));
        $data = $response->getData(true);

        $this->assertEquals(1, $data['meta']['current_page']);
        $this->assertCount(15, $data['data']);
    }
}
