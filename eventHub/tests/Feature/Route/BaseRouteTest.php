<?php

namespace Tests\Feature\Route;

use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('route')]
#[Group('auth')]
class BaseRouteTest extends TestCase
{
    protected function assertRouteExists(string $path, string $routeName, array $parameters = []): void
    {
        $this->assertTrue(Route::has($routeName));

        $url = route($routeName, $parameters);

        $this->assertSame(config('app.url') . '/v1/' . $path, $url);
    }
}
