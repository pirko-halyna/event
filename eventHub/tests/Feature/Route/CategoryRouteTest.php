<?php

namespace Tests\Feature\Route;

use PHPUnit\Framework\Attributes\{Group, Test};

/**
 * Class CategoryRouteTest
 */
#[Group('route')]
#[Group('category')]
class CategoryRouteTest extends BaseRouteTest
{
    #[Test]
    public function categories_route_exists(): void
    {
        $this->assertRouteExists('categories', 'categories.index');
    }
}
