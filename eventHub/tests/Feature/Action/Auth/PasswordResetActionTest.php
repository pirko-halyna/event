<?php

namespace Tests\Feature\Action\Auth;

use App\Mail\PasswordReset;
use App\Models\PasswordResetToken;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\{Group, Test};
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Class PasswordResetActionTest
 */
#[Group('action')]
#[Group('auth')]
class PasswordResetActionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    #[Test]
    public function successfulPasswordReset(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $this->postJson(route('auth.password-reset.request'), [
            'email' => $user->email,
        ]);

        Mail::assertQueued(PasswordReset::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    #[Test]
    public function invalidEmailPasswordResetRequest(): void
    {
        $this->postJson(route('auth.password-reset.request'), [
            'email' => 'nonexistent@example.com',
        ]);

        Mail::assertNothingQueued();
    }

    #[Test]
    public function user_password_changed_after_reset()
    {
        $user = User::factory()->create();

        $token = PasswordResetToken::factory()->for($user)->create();

        $newPassword = Str::random(16);

        $this->postJson(route('auth.password-reset.confirm'), [
            'new_password' => $newPassword,
            'new_password_confirmation' => $newPassword,
            'token' => $token->token,
        ]);

        $user->refresh();
        $this->assertTrue(Hash::check($newPassword, $user->password));
    }

    #[Test]
    public function password_reset_token_deleted_after_usage()
    {
        $user = User::factory()->create();

        $token = PasswordResetToken::factory()->for($user)->create();

        $newPassword = Str::random(16);

        $this->postJson(route('auth.password-reset.confirm'), [
            'new_password' => $newPassword,
            'new_password_confirmation' => $newPassword,
            'token' => $token->token,
        ]);

        $this->assertDatabaseMissing('password_reset_tokens', ['token' => $token]);
    }
}
