<?php

namespace Tests\Feature\Action\Auth;

use App\Models\User;
use PHPUnit\Framework\Attributes\{Group, Test};
use Tests\TestCase;

/**
 * Class LogoutActionTest
 */
#[Group('action')]
#[Group('auth')]
class LogoutActionTest extends TestCase
{
    #[Test]
    public function logout_works()
    {
        $user = User::factory()->create();

        $token = auth('api')->login($user);

        $this->withToken($token)->postJson(route('auth.logout'));

        $this->assertGuest();
    }
}
