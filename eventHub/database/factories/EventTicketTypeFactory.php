<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventTicketType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventTicketType>
 */
class EventTicketTypeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'event_id'    => Event::factory(),
            'name'        => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'price'       => $this->faker->randomFloat(2, 0, 500),
            'quantity'    => $this->faker->numberBetween(0, 1000),
        ];
    }
}
