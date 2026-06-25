<?php

namespace Feature\Action\EventRegistration;

use App\Mail\EventRegistrationMail;
use App\Models\Event;
use App\Models\User;
use App\Services\AuthService;
use PHPUnit\Framework\Attributes\{Group, Test};
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Class EventRegistrationActionTest
 */
#[Group('action')]
#[Group('event-registration')]
class EventRegistrationActionTest extends TestCase
{
    #[Test]
    public function user_can_register_for_event()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create();

        $this->mock(AuthService::class, function ($mock) use ($user) {
            $mock->shouldReceive('authenticateToken')
                ->andReturn($user);
        });

        $response =  $this->withHeader('Authorization', 'Bearer fake-token')
            ->postJson(route('events.register', $event));

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'registration_id',
            ])
            ->assertJson([
                'message' => 'User successfully registered for the event',
            ]);

        $registrationId = $response->json('registration_id');

        $this->assertDatabaseHas('event_registrations', [
            'id' => $registrationId,
            'user_id' => $user->id,
            'event_id' => $event->id,
        ]);
    }

    #[Test]
    public function event_registration_mail_is_sent(): void
    {
        Mail::fake();

        $user = User::factory()->create();
        $event = Event::factory()->create();
        $this->mock(AuthService::class, function ($mock) use ($user) {
            $mock->shouldReceive('authenticateToken')
                ->andReturn($user);
        });

        $this->withHeader('Authorization', 'Bearer fake-token')->postJson(route('events.register', $event))
            ->assertCreated();

        Mail::assertSent(EventRegistrationMail::class, fn ($mail) =>
            $mail->hasTo($user->email));
    }
}
