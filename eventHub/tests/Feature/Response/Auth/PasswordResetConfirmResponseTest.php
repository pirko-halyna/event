<?php

namespace Tests\Feature\Response\Auth;

use App\Models\PasswordResetCode;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\{Group, Test};
use Tests\TestCase;

#[Group('response')]
#[Group('auth')]
class PasswordResetConfirmResponseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::fake(['https://api.pwnedpasswords.com/*' => Http::response('', 200)]);
    }

    #[Test]
    public function it_returns_200_on_successful_reset(): void
    {
        $user = User::factory()->create();
        $code = '123456';

        PasswordResetCode::create([
            'email'      => $user->email,
            'code'       => Hash::make($code),
            'expires_at' => now()->addMinutes(15),
        ]);

        $this->postJson(route('auth.password-reset.confirm'), [
            'email'                 => $user->email,
            'code'                  => $code,
            'password'              => 'NewPassword1',
            'password_confirmation' => 'NewPassword1',
        ])->assertOk()->assertJson(['message' => 'Password has been reset successfully.']);
    }

    #[Test]
    public function it_returns_422_for_invalid_code(): void
    {
        $user = User::factory()->create();

        $this->postJson(route('auth.password-reset.confirm'), [
            'email'                 => $user->email,
            'code'                  => '000000',
            'password'              => 'NewPassword1',
            'password_confirmation' => 'NewPassword1',
        ])->assertUnprocessable()->assertJsonValidationErrors(['code']);
    }

    #[Test]
    public function it_returns_422_for_expired_code(): void
    {
        $user = User::factory()->create();
        $code = '999999';

        PasswordResetCode::create([
            'email'      => $user->email,
            'code'       => Hash::make($code),
            'expires_at' => now()->subMinutes(1),
        ]);

        $this->postJson(route('auth.password-reset.confirm'), [
            'email'                 => $user->email,
            'code'                  => $code,
            'password'              => 'NewPassword1',
            'password_confirmation' => 'NewPassword1',
        ])->assertUnprocessable()->assertJsonValidationErrors(['code']);
    }

    #[Test]
    public function it_returns_422_on_validation_error(): void
    {
        $this->postJson(route('auth.password-reset.confirm'), [
            'password'              => 'short',
            'password_confirmation' => 'short',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'code', 'password']);
    }
}
