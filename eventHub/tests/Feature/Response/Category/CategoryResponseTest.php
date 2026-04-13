<?php

namespace Tests\Feature\Response\Category;

use App\Models\Category;
use PHPUnit\Framework\Attributes\{Group, Test};
use Tests\TestCase;

/**
 *  Class CategoryResponseTest
 */
#[Group('response')]
#[Group('category')]
class CategoryResponseTest extends TestCase
{
    #[Test]
    public function category_successfully()
    {
        Category::factory()->count(50)->create();

        $response = $this->getJson(route('categories.index', ['page' => 1, 'per_page' => 15]));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'order',
                    'created_at',
                    'updated_at',
                ],
            ],
            'links' => [
                'first',
                'last',
                'prev',
                'next',
            ],
            'meta' => [
                'current_page',
                'from',
                'last_page',
                'links',
                'path',
                'per_page',
                'to',
                'total',
            ],
        ]);
    }

    #[Test]
    public function get_categories_sorted_by_order_asc(): void
    {
        Category::factory()->count(50)->sequence(
            fn ($sequence) => ['order' => $sequence->index]
        )->create();

        $response =  $this->getJson(
            route('categories.index', [
                'sort_by' => 'order',
                'sort_order' => 'asc',
                'per_page' => 15,
            ])
        );
        $data = $response->json();

        $orders = collect($data['data'])->pluck('order');

        $sortedOrders = $orders->sort()->values();
        $this->assertEquals($sortedOrders->toArray(), $orders->toArray());
    }
}
