<?php

namespace Feature\Request\Auth;

use App\Models\PasswordResetToken;
use PHPUnit\Framework\Attributes\{Group, Test};
use Illuminate\Support\Str;
use Tests\TestCase;

#[Group('request')]
#[Group('auth')]
class NewPasswordRequestTest extends TestCase
{
    #[Test]
    public function new_password_is_required(): void
    {
        $this->postJson(route('auth.password-reset.confirm'))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['new_password' => 'The new password field is required.']);
    }

    #[Test]
    public function new_password_must_be_a_string(): void
    {
        $this->postJson(route('auth.password-reset.confirm'), ['new_password' => 46893938])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['new_password' => 'The new password field must be a string.']);
    }

    #[Test]
    public function new_password_is_rejected_if_too_small(): void
    {
        $this->postJson(route('auth.password-reset.confirm'), ['new_password' => '123'])
            ->assertJsonValidationErrors(['new_password' => 'The new password field must be at least 8 characters.']);
    }

    #[Test]
    public function token_is_required(): void
    {
        $this->postJson(route('auth.password-reset.confirm'))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['token' => 'The token field is required.']);
    }

    #[Test]
    public function token_must_be_a_string(): void
    {
        $this->postJson(route('auth.password-reset.confirm'), [
            'token' => 12345,
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['token' => 'The token field must be a string.']);
    }

    #[Test]
    public function token_must_exist_in_password_reset_tokens_table(): void
    {
        $this->postJson(route('auth.password-reset.confirm'), [
            'token' => PasswordResetToken::factory()->make()->token
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['token' => 'The selected token is invalid.']);
    }
}
