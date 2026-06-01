<?php

namespace Tests\Feature\Response\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Password;
use PHPUnit\Framework\Attributes\{Group, Test};
use Tests\TestCase;

#[Group('response')]
#[Group('auth')]
class PasswordResetConfirmResponseTest extends TestCase
{
    #[Test]
    public function successful_password_reset_response(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $response = $this->postJson(route('auth.password-reset.confirm'), [
            'email'                    => $user->email,
            'new_password'             => 'newpassword',
            'new_password_confirmation' => 'newpassword',
            'token'                    => $token,
        ]);

        $response->assertOk();
    }

    #[Test]
    public function validation_error_response(): void
    {
        $response = $this->postJson(route('auth.password-reset.confirm'), [
            'new_password'             => 'short',
            'new_password_confirmation' => 'short',
            'token'                    => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'new_password',
                    'token',
                ],
            ]);
    }

    #[Test]
    public function invalid_token_response(): void
    {
        $response = $this->postJson(route('auth.password-reset.confirm'), [
            'email'                    => 'test@example.com',
            'new_password'             => 'newpassword',
            'new_password_confirmation' => 'newpassword',
            'token'                    => 'invalidtoken',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['token']);
    }
}
