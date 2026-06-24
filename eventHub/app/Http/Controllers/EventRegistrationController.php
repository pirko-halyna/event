<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\JsonResponse;
use App\Services\EventRegistrationService;

class EventRegistrationController extends Controller
{
    public function __construct(protected EventRegistrationService $registrationService)
    {
    }

    /**
     * User registration for a free event
     *
     * @param Event $event
     * @return JsonResponse
     */
    public function register(Event $event): JsonResponse
    {
        $registration = $this->registrationService->registerUser(
            auth()->id(),
            $event->id
        );

        return response()->json([
            'message' => 'User successfully registered for the event',
            'registration_id' => $registration->id,
        ], 201);
    }
}
