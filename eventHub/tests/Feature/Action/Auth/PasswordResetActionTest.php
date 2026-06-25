<?php

namespace Tests\Feature\Action\Auth;

use App\Mail\PasswordResetMail;
use App\Models\PasswordResetCode;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\{Group, Test};
use Tests\TestCase;

#[Group('action')]
#[Group('auth')]
class PasswordResetActionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Http::fake(['https://api.pwnedpasswords.com/*' => Http::response('', 200)]);
    }

    #[Test]
    public function it_sends_mail_when_user_exists(): void
    {
        $user = User::factory()->create();

        $this->postJson(route('auth.password-reset.request'), ['email' => $user->email]);

        Mail::assertQueued(PasswordResetMail::class, fn ($mail) => $mail->hasTo($user->email));
    }

    #[Test]
    public function it_does_not_send_mail_for_nonexistent_email(): void
    {
        $this->postJson(route('auth.password-reset.request'), ['email' => 'ghost@example.com']);

        Mail::assertNothingQueued();
    }

    #[Test]
    public function it_stores_hashed_code_in_database(): void
    {
        $user = User::factory()->create();

        $this->postJson(route('auth.password-reset.request'), ['email' => $user->email]);

        $this->assertDatabaseHas('password_reset_codes', [
            'email'   => $user->email,
            'used_at' => null,
        ]);
    }

    #[Test]
    public function it_invalidates_previous_unused_code_on_new_request(): void
    {
        $user = User::factory()->create();

        $this->postJson(route('auth.password-reset.request'), ['email' => $user->email]);
        $this->postJson(route('auth.password-reset.request'), ['email' => $user->email]);

        $this->assertSame(1, PasswordResetCode::where('email', $user->email)->whereNull('used_at')->count());
        $this->assertSame(2, PasswordResetCode::where('email', $user->email)->count());
    }

    #[Test]
    public function it_resets_password_with_valid_code(): void
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
        ]);

        $this->assertTrue(Hash::check('NewPassword1', $user->fresh()->password));
    }

    #[Test]
    public function it_marks_code_as_used_after_successful_reset(): void
    {
        $user = User::factory()->create();
        $code = '654321';

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
        ]);

        $this->assertNotNull(
            PasswordResetCode::where('email', $user->email)->first()->used_at
        );
    }

    #[Test]
    public function it_rejects_already_used_code(): void
    {
        $user = User::factory()->create();
        $code = '111111';

        PasswordResetCode::create([
            'email'      => $user->email,
            'code'       => Hash::make($code),
            'expires_at' => now()->addMinutes(15),
            'used_at'    => now(),
        ]);

        $this->postJson(route('auth.password-reset.confirm'), [
            'email'                 => $user->email,
            'code'                  => $code,
            'password'              => 'NewPassword1',
            'password_confirmation' => 'NewPassword1',
        ])->assertUnprocessable();
    }

    #[Test]
    public function it_rejects_expired_code(): void
    {
        $user = User::factory()->create();
        $code = '222222';

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
        ])->assertUnprocessable();
    }
}
