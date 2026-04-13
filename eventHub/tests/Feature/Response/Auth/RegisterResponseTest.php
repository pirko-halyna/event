<?php

namespace Tests\Feature\Response\Auth;

use App\Models\User;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\{Group, Test};
use Tests\TestCase;

/**
 * Class RegisterResponseTest
 */
#[Group('response')]
#[Group('auth')]
class RegisterResponseTest extends TestCase
{
    #[Test]
    public function registration_response_format_is_correct()
    {
        $password = Str::random(8);
        $userData = User::factory()->withPasswordConfirmation($password)->withoutPhone()->raw();

        $response = $this->postJson(route('auth.register'), $userData);

        $response->assertOk();

        $response->assertJson([
            'token' => $response->json('token'),
        ]);
    }
}
