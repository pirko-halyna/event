<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Event;
use App\Models\Location;
use App\Models\Organizer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'author_id' => User::factory(),
            'organizer_id' => Organizer::factory(),
            'location_id' => Location::factory(),
            'category_id' => Category::factory(),
            'datetime_from' => $this->faker->dateTime(),
            'datetime_to' => $this->faker->dateTime(),
            'is_online' => $this->faker->boolean(),
            'price' => $this->faker->randomNumber(),
        ];
    }

    /**
     * @return EventFactory|Factory
     */
    public function withRelations(): EventFactory | Factory
    {
        return $this->afterCreating(function (Event $event) {
            $event->load(['author', 'organizer', 'location', 'category']);
        });
    }
}
