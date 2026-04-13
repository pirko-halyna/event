<?php

namespace Tests\Feature\Response\Auth;

use App\Models\User;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\{Group, Test};
use Tests\TestCase;

/**
 * Class LoginResponseTest
 */
#[Group('response')]
#[Group('auth')]
class LoginResponseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }

    #[test]
    public function login_response_is_ok__and_has_token(): void
    {
        $password = Str::random(10);
        $user = User::factory()->create(['password' => $password]);

        $response = $this->postJson(route('auth.login'), [
            'email' => $user->email,
            'password' => $password,
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['token']);
    }

    #[test]
    public function login_response_is_unauthenticated_with_wrong_password(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson(route('auth.login'), [
            'email' => $user->email,
            'password' => 'wrong_password',
        ]);

        $response->assertUnauthorized();
    }
}
