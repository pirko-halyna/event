<?php

namespace App\Listeners;

use App\Events\EventRegistered;
use App\Mail\EventRegistrationMail;
use Illuminate\Support\Facades\Mail;

class SendEventRegistrationEmail
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(EventRegistered $event): void
    {
        $data = $event->registrationOrOrder;

        // define the user's email address
        $email = $data->user->email;

        $eventModel = $data->event;

        Mail::to($email)->send(new EventRegistrationMail($data, $eventModel));
    }
}
