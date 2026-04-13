<?php

namespace Tests\Feature\Request\Auth;

use PHPUnit\Framework\Attributes\{Group, Test};
use Tests\TestCase;

#[Group('request')]
#[Group('auth')]
class LoginRequestTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }

    #[Test]
    public function email_is_required(): void
    {
        $this->postJson(route('auth.login'))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email' => 'The email field is required.']);
    }

    #[Test]
    public function email_must_be_a_valid_email(): void
    {
        $this->postJson(route('auth.login'), ['email' => 'invalid-email'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email' => 'The email field must be a valid email address.']);
    }

    #[Test]
    public function password_is_required(): void
    {
        $this->postJson(route('auth.login'))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['password' => 'The password field is required.']);
    }

    #[Test]
    public function password_must_be_a_string(): void
    {
        $this->postJson(route('auth.login'), [
            'password' => 12345,
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['password' => 'The password field must be a string.']);
    }

    #[Test]
    public function remember_me_must_be_bool(): void
    {
        $this->postJson(route('auth.login'), [
            'remember_me' => 8930,
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['remember_me' => 'The remember me field must be true or false.']);
    }
}
