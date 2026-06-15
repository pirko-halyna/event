<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\EventTicketType;
use Illuminate\Database\Seeder;

class EventTicketTypesSeeder extends Seeder
{
    public function run(): void
    {
        Event::all()->each(function (Event $event) {
            $alreadyExists = $event->ticketTypes()
                ->where('name', 'Free')
                ->exists();

            if (!$alreadyExists) {
                $event->ticketTypes()->create([
                    'name' => 'Free',
                    'price' => 0.00,
                ]);
            }
        });
    }
}
