<?php

namespace Tests\Feature\Request;

use App\Models\Event;
use App\Models\User;
use App\Services\AuthService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[Group('request')]
#[Group('event')]
class UpdateEventRequestTest extends TestCase
{
    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();

        $user        = User::factory()->create();
        $this->event = Event::factory()->create(['type' => 'free']);

        $this->mock(AuthService::class, function ($mock) use ($user) {
            $mock->shouldReceive('getUserByToken')->andReturn($user);
        });
    }

    #[Test]
    public function title_cannot_be_empty_string_when_provided(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer fake-token')
            ->putJson(route('events.update', $this->event), ['title' => '']);

        $response->assertStatus(422)->assertJsonValidationErrors('title');
    }

    #[Test]
    public function type_must_be_valid_when_provided(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer fake-token')
            ->putJson(route('events.update', $this->event), ['type' => 'invalid']);

        $response->assertStatus(422)->assertJsonValidationErrors('type');
    }

    #[Test]
    public function datetime_to_must_be_after_datetime_from_when_both_provided(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer fake-token')
            ->putJson(route('events.update', $this->event), [
                'datetime_from' => now()->addDays(3)->toDateTimeString(),
                'datetime_to'   => now()->addDay()->toDateTimeString(),
            ]);

        $response->assertStatus(422)->assertJsonValidationErrors('datetime_to');
    }
}
