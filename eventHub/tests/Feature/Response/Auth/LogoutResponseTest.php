<?php

namespace Tests\Feature\Response\Auth;

use App\Models\User;
use PHPUnit\Framework\Attributes\{Group, Test};
use Tests\TestCase;

/**
 *  Class LogoutResponseTest
 */
#[Group('response')]
#[Group('auth')]
class LogoutResponseTest extends TestCase
{
    #[Test]
    public function logout_successfully()
    {
        $user = User::factory()->create();

        $token = auth('api')->login($user);

        $response = $this->withToken($token)->postJson(route('auth.logout'));

        $response->assertNoContent();
    }

    #[Test]
    public function logout_with_invalid_token()
    {
        $invalidToken = 'invalidToken';

        $response = $this->withToken($invalidToken)->postJson(route('auth.logout'));

        $response->assertUnauthorized();
    }

    #[Test]
    public function logout_with_expired_token()
    {
        // Set a short expiration time for testing
        config(['jwt.ttl' => 1]);

        $user = User::factory()->create();
        $token = auth('api')->login($user);

        $this->travel(65)->seconds();

        $response = $this->withToken($token)->postJson(route('auth.logout'));

        $response->assertUnauthorized();
    }

    #[Test]
    public function logout_without_token()
    {
        $response = $this->postJson(route('auth.logout'));

        $response->assertUnauthorized();
    }

    #[Test]
    public function logout_with_blacklisted_token()
    {
        $user = User::factory()->create();
        $token = auth('api')->login($user);

        $this->withToken($token)->postJson(route('auth.logout'));
        $response = $this->withToken($token)->postJson(route('auth.logout'));

        $response->assertUnauthorized();
    }
}
