<?php

namespace Tests\Feature\Response\Auth;

use App\Models\PasswordResetToken;
use App\Models\User;
use PHPUnit\Framework\Attributes\{Group, Test};
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Class PasswordResetConfirmResponseTest
 */
#[Group('response')]
#[Group('auth')]
class PasswordResetConfirmResponseTest extends TestCase
{
    #[Test]
    public function successful_password_reset_response()
    {
        $user = User::factory()->create();

        $token = Str::random(60);
        PasswordResetToken::create([
            'email' => $user->email,
            'token' => $token,
        ]);

        $response = $this->postJson(route('auth.password-reset.confirm'), [
            'new_password' => 'newpassword',
            'new_password_confirmation' => 'newpassword',
            'token' => $token,
        ]);

        $response->assertOk();
    }

    #[Test]
    public function validation_error_response()
    {
        $response = $this->postJson(route('auth.password-reset.confirm'), [
            'new_password' => 'short',
            'new_password_confirmation' => 'short',
            'token' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'new_password',
                    'token',
                ]
            ]);
    }

    #[Test]
    public function invalid_token_response()
    {
        $response = $this->postJson(route('auth.password-reset.confirm'), [
            'new_password' => 'newpassword',
            'new_password_confirmation' => 'newpassword',
            'token' => 'invalidtoken',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'The selected token is invalid.',
                'errors' => ['token' => ['The selected token is invalid.']]
            ]);
    }
}
