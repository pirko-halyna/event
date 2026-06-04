<?php

namespace Tests\Feature\Action\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\{Group, Test};
use Tests\TestCase;

#[Group('action')]
#[Group('auth')]
class PasswordResetActionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    #[Test]
    public function successful_password_reset(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $this->postJson(route('auth.password-reset.request'), [
            'email' => $user->email,
        ]);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    #[Test]
    public function it_allows_multiple_password_reset_requests_for_same_email(): void
    {
        $user = User::factory()->create();

        $this->postJson(route('auth.password-reset.request'), ['email' => $user->email])
            ->assertOk();

        $this->postJson(route('auth.password-reset.request'), ['email' => $user->email])
            ->assertOk();

        $this->assertDatabaseCount('password_reset_tokens', 1);
    }

    #[Test]
    public function invalid_email_password_reset_request(): void
    {
        $this->postJson(route('auth.password-reset.request'), [
            'email' => 'nonexistent@example.com',
        ]);

        Notification::assertNothingSent();
    }

    #[Test]
    public function user_password_changed_after_reset(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);
        $newPassword = Str::random(16);

        $this->postJson(route('auth.password-reset.confirm'), [
            'email'                    => $user->email,
            'new_password'             => $newPassword,
            'new_password_confirmation' => $newPassword,
            'token'                    => $token,
        ]);

        $user->refresh();
        $this->assertTrue(Hash::check($newPassword, $user->password));
    }

    #[Test]
    public function password_reset_token_deleted_after_usage(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);
        $newPassword = Str::random(16);

        $this->postJson(route('auth.password-reset.confirm'), [
            'email'                    => $user->email,
            'new_password'             => $newPassword,
            'new_password_confirmation' => $newPassword,
            'token'                    => $token,
        ]);

        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
    }
}
