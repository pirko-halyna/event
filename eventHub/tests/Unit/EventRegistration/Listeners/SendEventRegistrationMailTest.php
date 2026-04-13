<?php

namespace Tests\Unit\EventRegistration\Listeners;

use App\Events\EventRegistered;
use App\Listeners\SendEventRegistrationEmail;
use App\Mail\EventRegistrationMail;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SendEventRegistrationMailTest extends TestCase
{
    #[Test]
    public function listener_sends_email(): void
    {
        $user = User::factory()->create();
        $event = Event::factory()->create();

        $registration = new EventRegistration([
            'user_id' => $user->id,
            'event_id' => $event->id,
        ]);

        $registration->setRelation('user', $user);
        $registration->setRelation('event', $event);

        $eventMock = Mockery::mock(EventRegistered::class);
        $eventMock->registrationOrOrder = $registration;

        Mail::fake();

        $listener = new SendEventRegistrationEMail();
        $listener->handle($eventMock);

        Mail::assertSent(EventRegistrationMail::class, fn($mail) =>
            $mail->hasTo($user->email));
    }
}
