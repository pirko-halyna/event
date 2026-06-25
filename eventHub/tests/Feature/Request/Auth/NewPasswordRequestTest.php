<?php

namespace Tests\Feature\Request\Auth;

use PHPUnit\Framework\Attributes\{Group, Test};
use Tests\TestCase;

#[Group('request')]
#[Group('auth')]
class NewPasswordRequestTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }

    #[Test]
    public function code_is_required(): void
    {
        $this->postJson(route('auth.password-reset.confirm'))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['code']);
    }

    #[Test]
    public function code_must_be_6_digits(): void
    {
        $this->postJson(route('auth.password-reset.confirm'), ['code' => '12345'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['code']);
    }

    #[Test]
    public function code_must_be_numeric(): void
    {
        $this->postJson(route('auth.password-reset.confirm'), ['code' => 'abcdef'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['code']);
    }

    #[Test]
    public function password_is_required(): void
    {
        $this->postJson(route('auth.password-reset.confirm'))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    #[Test]
    public function password_must_be_at_least_8_characters(): void
    {
        $this->postJson(route('auth.password-reset.confirm'), [
            'password'              => 'short',
            'password_confirmation' => 'short',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    #[Test]
    public function password_must_be_confirmed(): void
    {
        $this->postJson(route('auth.password-reset.confirm'), [
            'password'              => 'validpassword',
            'password_confirmation' => 'differentpassword',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['password']);
    }

    #[Test]
    public function email_is_required(): void
    {
        $this->postJson(route('auth.password-reset.confirm'))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }
}
