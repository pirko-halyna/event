<?php

namespace App\Services;

use App\Events\EventRegistered;
use App\Models\EventRegistration;

class EventRegistrationService
{
    /**
     * Create record in event registration table
     *
     * @param int $userId
     * @param int $eventId
     * @return EventRegistration
     */
    public function registerUser(int $userId, int $eventId): EventRegistration
    {
        $registration = EventRegistration::firstOrCreate(
            [
                'user_id' => $userId,
                'event_id' => $eventId,
            ]
        );

        event(new EventRegistered($registration));

        return $registration;
    }
}
