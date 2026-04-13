<?php

namespace Tests\Feature\Action\Auth;

use App\Models\User;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\{Group, Test};
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * Class RegisterActionTest
 */
#[Group('action')]
#[Group('auth')]
class RegisterActionTest extends TestCase
{
    #[Test]
    public function register_endpoint_returns_valid_token(): void
    {
        $password = Str::random(8);
        $userData = User::factory()->withPasswordConfirmation($password)->raw();

        $result = $this->postJson(route('auth.register'), $userData);

        $this->assertTrue(JWTAuth::setToken($result['token'])->check());
    }

    #[Test]
    public function register_endpoint_creates_new_user()
    {
        $password = Str::random(8);
        $userData = User::factory()->withPasswordConfirmation($password)->raw();
        $response = $this->postJson(route('auth.register'), $userData);

        $this->assertDatabaseHas('users', [
            'email' => $userData['email'],
        ]);

        $user = User::where('email', $userData['email'])->first();

        $this->assertAuthenticatedAs($user);
    }
}
