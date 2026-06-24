<?php

namespace Tests\Feature\Request\Auth;

use PHPUnit\Framework\Attributes\{Group, Test};
use Tests\TestCase;

#[Group('request')]
#[Group('auth')]
class PasswordResetRequestTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }

    #[Test]
    public function email_is_required(): void
    {
        $this->postJson(route('auth.password-reset.request'))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email' => 'The email field is required.']);
    }

    #[Test]
    public function email_must_be_a_valid_email(): void
    {
        $this->postJson(route('auth.password-reset.request'), ['email' => 'invalid-email'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email' => 'The email field must be a valid email address.']);
    }
}
