<?php

namespace Tests\Feature\Action\Category;

use App\Models\Category;
use PHPUnit\Framework\Attributes\{Group, Test};
use Tests\TestCase;

/**
 * Class GetCategoryActionTest
 */
#[Group('action')]
#[Group('category')]
class GetCategoryActionTest extends TestCase
{
    #[Test]
    public function get_categories_has_pagination(): void
    {
        Category::factory()->count(50)->create();

        $response = $this->getJson(route('categories.index', ['page' => 1, 'per_page' => 15]));
        $data = $response->getData(true);

        $this->assertEquals(1, $data['meta']['current_page']);
        $this->assertCount(15, $data['data']);
    }
}
