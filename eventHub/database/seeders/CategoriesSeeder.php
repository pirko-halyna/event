<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Concerts',
            'Sports',
            'Theater',
            'Family',
            'For kids',
            'Stand-up',
            'Tour',
            'Excursions',
            'Exhibitions',
            'Clubs',
        ];
        foreach ($categories as $order => $name) {
            Category::create(compact('order', 'name'));
        }
    }
}
