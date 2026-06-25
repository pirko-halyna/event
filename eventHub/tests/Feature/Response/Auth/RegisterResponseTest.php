<?php

namespace Tests\Feature\Response\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\{Group, Test};
use Tests\TestCase;

/**
 * Class RegisterResponseTest
 */
#[Group('response')]
#[Group('auth')]
class RegisterResponseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::fake(['https://api.pwnedpasswords.com/*' => Http::response('', 200)]);
    }

    #[Test]
    public function registration_response_format_is_correct()
    {
        $password = 'ValidPass1';
        $userData = User::factory()->withPasswordConfirmation($password)->withoutPhone()->raw();

        $response = $this->postJson(route('auth.register'), $userData);

        $response->assertOk();

        $response->assertJson([
            'token' => $response->json('token'),
        ]);
    }
}
