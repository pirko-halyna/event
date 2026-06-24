<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'first_name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => 'admin',
        ]);

        Event::factory()->count(10)->create();

        $this->call([
            CategoriesSeeder::class,
            EventTicketTypesSeeder::class,
        ]);
    }
}
