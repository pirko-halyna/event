<?php

namespace Tests\Feature\Response\EventRegistration;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

#[Group('response')]
#[Group('event-registration')]
class EventRegistrationResponseTest extends TestCase
{
    #[Test]
    public function register_event_success()
    {
        $user = User::factory()->create();

        $event = Event::factory()->create();

        $token = auth()->login($user);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson(route('events.register', ['event' => $event->id]));

        $response->assertCreated()
            ->assertJson(['message' => 'User successfully registered for the event'])
            ->assertJsonStructure([
                'message',
                'registration_id',
            ]);
    }
}
