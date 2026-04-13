<?php

namespace Feature\Route;

use PHPUnit\Framework\Attributes\{Group, Test};
use Tests\Feature\Route\BaseRouteTest;

/**
 * Class OrderRouteTest
 */
#[Group('route')]
#[Group('order')]
class OrderRouteTest extends BaseRouteTest
{
    #[Test]
    public function order_route_exists(): void
    {
        $this->assertRouteExists('orders', 'orders.store');
    }
}
