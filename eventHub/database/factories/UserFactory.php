<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => $this->faker->password,
        ];
    }

    public function withPasswordConfirmation(string $password): Factory
    {
        return $this->state(function (array $attributes) use ($password) {
            return [
                'password' => $password,
                'password_confirmation' => $password,
            ];
        });
    }

    public function withoutPhone(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'phone' => null,
            ];
        });
    }
}
