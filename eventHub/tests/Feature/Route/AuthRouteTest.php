<?php

namespace Tests\Feature\Route;

use PHPUnit\Framework\Attributes\{Group, Test};

/**
 * Class AuthRouteTest
 */
#[Group('route')]
#[Group('auth')]
class AuthRouteTest extends BaseRouteTest
{
    #[Test]
    public function login_route_exists(): void
    {
        $this->assertRouteExists('auth/login', 'auth.login');
    }

    #[Test]
    public function register_route_exists(): void
    {
        $this->assertRouteExists('auth/register', 'auth.register');
    }

    #[Test]
    public function logout_route_exists(): void
    {
        $this->assertRouteExists('auth/logout', 'auth.logout');
    }

    #[Test]
    public function password_reset_request_route_exists(): void
    {
        $this->assertRouteExists('auth/password-reset/request', 'auth.password-reset.request');
    }

    #[Test]
    public function password_reset_confirm_route_exists(): void
    {
        $this->assertRouteExists('auth/password-reset/confirm', 'auth.password-reset.confirm');
    }
}
