<?php

namespace Feature\Action\User;

use App\Models\Event;
use App\Models\Favourite;
use App\Models\User;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[Group('action')]
#[Group('users')]
class GetFavouriteEventsActionTest extends TestCase
{
    #[Test]
    public function get_favourite_events_has_pagination()
    {
        $user = User::factory()->create();
        $token = auth()->login($user);

        Favourite::factory()->count(50)->create([
            'user_id' => $user->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson(route('users.favourite-events'));

        $data = $response->getData(true);
        $this->assertEquals(1, $data['meta']['current_page']);
        $this->assertCount(10, $data['data']);
    }
}
